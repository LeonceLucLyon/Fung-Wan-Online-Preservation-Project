#!/bin/bash
# gameadmin db config
gmadm_db_host=database.fungwan-online.com
gmadm_db_name=gmadm
gmadm_db_user=root
gmadm_db_pass=ejair0xx

/FWO13/script/wclog_to_db.pl

# one zoneserver one statement
# ssh -o 'StrictHostKeyChecking=no' -i /var/www/.ssh/id_rsa fwoadmin@<zs_ip> /<script-path>/set_logout_time.pl <logs/zs_crash_log> <gmadm_db_ip> <gmadm_db_name> <gmadm_db_user> <gmadm_db_pass>
# ssh -o 'StrictHostKeyChecking=no' -i /var/www/.ssh/id_rsa fwoadmin@192.168.100.20 /usr/local/FWOnline/set_logout_time.pl /usr/local/FWOnline/logs/zs_crash_log $gmadm_db_host $gmadm_db_name $gmadm_db_user $gmadm_db_pass

