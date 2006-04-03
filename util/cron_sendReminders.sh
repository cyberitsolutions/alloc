#!/bin/sh
#
# This script will trigger allocs emailed reminder system.
# Alex Lance <alla@cyber.com.au>
#

# path to cron and log files
PREFIX=`dirname $0`"/../logs/"

# execute the sendreminder php script and save the results
wget -q -O ${PREFIX}sendReminders_log.new -P ${PREFIX} http://alloc/notification/sendReminders.php

# if there were reminders sent (sendReminders_log.new > 0bytes) then log the date
[ -s ${PREFIX}sendReminders_log.new ] && echo `date` >> ${PREFIX}sendReminders_log.txt

# log results
cat ${PREFIX}sendReminders_log.new >> ${PREFIX}sendReminders_log.txt

# nuke temporary log file
[ -f ${PREFIX}sendReminders_log.new ] && rm -f ${PREFIX}sendReminders_log.new
