check_zs.pl

- called by ensure_zs_alive.sh to record zoneserver crash time and affected players

ensure_zs_alive.sh

- zoneserver startup script to avoid multiple run, called by crontab to auto-start crashed server

record_fwo_ac_login.sh

- run wclog_to_db.pl and set_logout_time.pl, called by crontab

snapshot_population.pl

- record connected users at a point of time

set_logout_time.pl

- record players' logout time due to zoneserver crash.

unsuspend_ac.sh

- unsuspend those suspended in gameadmin

wclog_to_db.pl

- record login activities in wctrlr.log to gameadmin db
