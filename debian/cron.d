# /etc/cron.d/freemed crontab fragment
# Syntax: m h dom mon dow user command

# ----- FreeMED Fax import ----------------------------------------------------
# Examine files every 5 minutes
05,10,15,20,25,30,35,40,45,50,55 *	* * *	root	test -f /var/spool/hylafax/recvq/*.tif && /usr/share/freemed/scripts/fax_import/import_all_hylafax.sh
