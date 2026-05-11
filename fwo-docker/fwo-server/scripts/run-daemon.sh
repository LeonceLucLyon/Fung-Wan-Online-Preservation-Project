#!/bin/bash
# run-daemon.sh - Launch a self-daemonizing binary and keep supervisord happy
# Usage: run-daemon.sh <name> <command> <pidfile>
#
# The game server binaries fork and exit the parent (ProcType daemon).
# This script launches the binary, finds the daemon's PID, then sleeps
# in a loop checking the process is still alive - keeping supervisord happy.

NAME=$1
CMD=$2
PIDFILE=$3

echo "[${NAME}] Starting..."

# Launch the binary - it will daemonize immediately
eval "$CMD"

# Give it a moment to fork and write its pidfile
sleep 1

# Try to find the daemon PID from pidfile, or find it by process name
if [ -f "$PIDFILE" ]; then
    DAEMON_PID=$(cat "$PIDFILE")
    echo "[${NAME}] Started via pidfile, PID: $DAEMON_PID"
else
    # Fall back to finding by process name
    BINARY=$(echo "$CMD" | awk '{print $1}')
    DAEMON_PID=$(pgrep -f "$BINARY" | head -1)
    echo "[${NAME}] Started (no pidfile), PID: $DAEMON_PID"
fi

if [ -z "$DAEMON_PID" ]; then
    echo "[${NAME}] ERROR: Could not find daemon PID after launch!"
    exit 1
fi

# Stay alive as long as the daemon process is running
echo "[${NAME}] Monitoring PID $DAEMON_PID..."
while kill -0 "$DAEMON_PID" 2>/dev/null; do
    sleep 5
done

echo "[${NAME}] Daemon PID $DAEMON_PID has exited."
exit 1
