#!/bin/sh

DB_HOST=$1
DB_USER=$2
DB_PASS=$3
DB_NAME=$4

if [ ! "$4" ]
then
	echo "Error: Expect 4 parameters."
	echo "Syntax: $0 <HOST> <USER> <PASSWD> <DBNAME>"
	exit
fi

MYSQL_CMD="mysql -h $DB_HOST -u $DB_USER -p$DB_PASS $DB_NAME"

$MYSQL_CMD -e "UPDATE subscription SET SvcLevel=1, SvcLevelChgDt=NOW(), LockOutTime=0 WHERE SvcLevel=-1 AND LockOutTime < UNIX_TIMESTAMP();"
