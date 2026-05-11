#!/bin/sh

## configuration ##

PUB_IP=127.0.0.1

ADMIN_MAIL=root@localhost
CMD_CHK_ZS=/var/www/dragon/gmadm_app/check_zs.pl
CRASH_LOG=/var/log/zs_crash.log
ZS_CONF=/FWO13/zoneserver/ZS.conf
ZS_DIR=/FWO13/zoneserver

## end of configuration ##

HOST=$(hostname)
DT=$(date)

$CMD_CHK_ZS $PUB_IP $ZS_CONF $CRASH_LOG

/sbin/pidof zoneserver > /dev/null
if [ $? -ne 0 ]
then
        echo "(${DT}) ${HOST}: zs stopped! starting it..." | mail "${ADMIN_MAIL}"
        cd $ZS_DIR
        ulimit -c unlimited
        ./zoneserver
        exit 3
fi

exit 0
