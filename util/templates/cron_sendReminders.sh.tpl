#!/bin/sh
#
# This script will trigger allocs emailed reminder system.
#

# path to cron and log files
PREFIX="CONFIG_VAR_ALLOC_LOG_DIR"

# execute the sendreminder php script and save the results
wget -q -O ${PREFIX}temp.txt -P ${PREFIX} CONFIG_VAR_ALLOC_WEB_URL_PREFIXnotification/sendReminders.php

# if there were reminders sent (temp.txt > 0bytes) then log the date
[ -s ${PREFIX}temp.txt ] && echo "$(date)" >> ${PREFIX}sendReminders.log

# log results
cat ${PREFIX}temp.txt >> ${PREFIX}sendReminders.log

# nuke temporary log file
[ -f ${PREFIX}temp.txt ] && rm -f ${PREFIX}temp.txt

