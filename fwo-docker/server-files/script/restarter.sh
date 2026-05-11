#!/bin/sh
# exit # Comment this in if you are working on your server and don't want it to boot


/sbin/pidof authsys >/dev/null # Grap the process ID
PID1=$?
if [ $PID1 -eq 1 ] # If not running boot the server in a dedicated screen session and log
    then
    cd /FWO13/authsys/
    ./authsys
fi

/sbin/pidof wctrlr >/dev/null # Grap the process ID
PID1=$?
if [ $PID1 -eq 1 ] # If not running boot the server in a dedicated screen session and log
    then
    cd /FWO13/wctrlr/
    ./wctrlr
fi

/sbin/pidof logserver >/dev/null # Grap the process ID
PID1=$?
if [ $PID1 -eq 1 ] # If not running boot the server in a dedicated screen session and log
    then
    cd /FWO13/logging/
    ./logserver 202.75.60.50 root ejair0xx &
fi
