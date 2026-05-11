#!/bin/sh
# =============================================================================
# wait-for-db.sh
# Waits until the fwo-db MySQL container is accepting connections.
# Called by start-fwo.sh before launching supervisord.
# =============================================================================

DB_HOST="fwo-db"
DB_PORT="3306"
DB_USER="root"
DB_PASS="ejair0xx"
MAX_WAIT=120  # seconds

echo "[FWO] Waiting for database at ${DB_HOST}:${DB_PORT}..."

i=0
while ! mysqladmin ping -h"$DB_HOST" -P"$DB_PORT" -u"$DB_USER" -p"$DB_PASS" --silent 2>/dev/null; do
    i=$((i+1))
    if [ $i -ge $MAX_WAIT ]; then
        echo "[FWO] ERROR: Database did not become ready after ${MAX_WAIT}s. Check fwo-db container logs."
        exit 1
    fi
    echo "[FWO] Database not ready yet... (${i}s)"
    sleep 1
done

echo "[FWO] Database is ready!"
exit 0
