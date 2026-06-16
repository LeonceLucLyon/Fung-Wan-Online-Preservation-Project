#!/bin/sh
# =============================================================================
# start-fwo.sh - FWO Server Container Entrypoint
# =============================================================================
# 1. Ensures binary permissions are correct
# 2. Waits for the database container to be ready
# 3. Runs idempotent E*Load scripts to reclaim orphaned Legendary Items
# 4. Creates required zoneserver scene symlinks
# 5. Starts supervisord which manages all 4 game server processes
#
# NOTE: One-shot event registration scripts (e5load, E15Load, e20load, e22load,
# e80load) are intentionally EXCLUDED from this loop. They contain INSERTs and
# CREATE TABLE statements that fail on subsequent runs. They were run once
# manually on 2026-05-04 and their state is now baked into the database.
# =============================================================================

set -e

echo "========================================="
echo "  FWO Offline - Game Server Starting"
echo "========================================="

# -----------------------------------------------------------------------------
# Ensure server binaries are executable
# (in case they lost execute permission when copied to Windows and back)
# -----------------------------------------------------------------------------
for BIN in /FWO13/authsys/authsys \
           /FWO13/wctrlr/wctrlr \
           /FWO13/zoneserver/zoneserver \
           /FWO13/logging/logserver; do
    if [ -f "$BIN" ]; then
        chmod +x "$BIN"
        echo "[FWO] Binary ready: $BIN"
    else
        echo "[FWO] WARNING: Binary not found: $BIN"
        echo "[FWO] Make sure you have copied your server files into the server-files/ folder."
    fi
done

# -----------------------------------------------------------------------------
# Make scripts executable too
# -----------------------------------------------------------------------------
find /FWO13/script -type f -exec chmod +x {} \; 2>/dev/null || true

# -----------------------------------------------------------------------------
# Wait for MySQL to be ready
# -----------------------------------------------------------------------------
/wait-for-db.sh
if [ $? -ne 0 ]; then
    echo "[FWO] Cannot start without database. Exiting."
    exit 1
fi

# -----------------------------------------------------------------------------
# Run idempotent E*Load LI reclaim scripts
# These reclaim orphaned Legendary Items back to the quest-giver pool.
# All scripts in this list use UPDATE...WHERE CharID=0 — safe to run every start.
# Failures are logged but don't abort startup.
# -----------------------------------------------------------------------------
echo "[FWO] Running event load scripts..."

DBSCRIPTS_DIR="/FWO13/zoneserver/DBScripts"
EVENT_SCRIPTS="E1Load.sql E2Load.sql E3Load.sql e4mud.sql E6Load.sql E7Load.sql E8Load.sql E9Load.sql E10Load.sql E11Load.sql E13Load.sql E14Load.sql E16Load.sql e21load.sql e23load.sql"

for SCRIPT in $EVENT_SCRIPTS; do
    SCRIPT_PATH="$DBSCRIPTS_DIR/$SCRIPT"
    if [ -f "$SCRIPT_PATH" ]; then
        echo "[FWO]   Running: $SCRIPT"
        mysql -h fwo-db -u root -pejair0xx fwworlddevdb < "$SCRIPT_PATH" 2>&1 | grep -v "Using a password" || true
    else
        echo "[FWO]   SKIP (not found): $SCRIPT"
    fi
done

echo "[FWO] Event scripts done."

# -----------------------------------------------------------------------------
# Auto-heal clan NPC leaders  (v2 - clan refactor)
# Per-clan split-field restore based on whether the clan has PC members:
#   Empty clan      -> clan.Type = NPC AttribID, npcattribdyn.IsDead = 0  (NPC leads)
#   Player-occupied -> clan.Type = 0,            npcattribdyn.IsDead = 1  (player leads)
# v1 forced Type = NPC AttribID for every clan, which kept the recruiter working
# but broke the Clan Advisor (it requires Type=0) and blocked RANK_LEADER
# promotion on takeover. The recruiter is now patched (script 75024) to enroll
# members at Type=0, so player-led clans correctly stay at Type=0.
# Idempotent; no-op when state is already healthy.
# -----------------------------------------------------------------------------
echo "[FWO] Running clan auto-heal..."
mysql -h fwo-db -u root -pejair0xx < /FWO13/database/auto_heal_clans_v2.sql 2>&1 | grep -v "Using a password" || true
echo "[FWO] Clan auto-heal done."

# -----------------------------------------------------------------------------
# Create zoneserver scene symlinks
# Zoneserver looks for extensionless filenames (e.g. "Starter") but the
# actual files on disk are "Starter.lks". These symlinks bridge the gap.
# They must be recreated on every start because the Scenes folder is
# volume-mounted from the host (Windows), which doesn't preserve Linux symlinks.
# -----------------------------------------------------------------------------
echo "[FWO] Creating zoneserver scene symlinks..."
cd /FWO13/zoneserver/Scenes
for i in "" 1 2 3 4 5 6 7 8 9; do
    if [ -f "Starter${i}.lks" ]; then
        ln -sf "Starter${i}.lks" "Starter${i}"
        echo "[FWO]   Linked: Starter${i} -> Starter${i}.lks"
    else
        echo "[FWO]   WARNING: Starter${i}.lks not found, skipping."
    fi
done
echo "[FWO] Scene symlinks done."

# -----------------------------------------------------------------------------
# Small delay to let authsys register with DB before wctrlr tries to connect
# supervisord handles ordering via priority, but an extra buffer helps
# -----------------------------------------------------------------------------
echo "[FWO] Starting game server processes via supervisord..."
sleep 2

# -----------------------------------------------------------------------------
# Start supervisord in foreground - this is the main Docker process
# supervisord manages: authsys, wctrlr, zoneserver, logserver, apache2
# NOTE: logserver must be launched with DB args: ./logserver 127.0.0.1 root ejair0xx
#       This is configured in /etc/supervisor/conf.d/fwo.conf
# -----------------------------------------------------------------------------
exec /usr/bin/supervisord -c /etc/supervisor/conf.d/fwo.conf
