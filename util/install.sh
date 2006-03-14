#!/bin/bash 

function run {
  #ERR=`$1 2>&-`
  STATUS="  OK  "
  ERR=`$1 2>&1`
  [ "${?}" -ne 0 ] && STATUS="FAILED"
  [ "${ERR}" != "" ] && ERR="\n            ${ERR}"
  echo -e " [${STATUS}]  ${1} ${ERR}"
}

find .. -type f -exec chmod 664 {} \;
find .. -type d -exec chmod 775 {} \;
find .. -type f -exec chgrp alloc {} \;

run "chmod 777 ../project/docs"                     # uploaded files
run "chmod 777 ../client/docs"                      # uploaded files
run "chmod 777 ../images/"                          # php created images
run "chmod 777 ../images/big*"                      # php created images
run "chmod 777 ../images/user*"                      # php created images
run "chmod 777 ../report/files/"                    # uploaded files
run "chmod 755 ./dump_structure.sh"                 # rwxr-xr-x
run "chmod 755 ./install.sh"                        # rwxr--r--
run "chmod 777 ../stylesheets/*"                    #
run "chmod 754 ./style_regen.py"                    # rwxr-xr-x

run "chown alloc ../logs"                           # gonna be run by user alloc
run "chmod 777 ../logs"                             # gonna need to write and delete
run "chmod 777 ../logs/alloc_email.log"             # gonna need to write and delete
run "chmod 755 ../logs/cron_sendReminders.sh"       # rwxr-xr-x 
run "chmod 755 ../logs/cron_sendEmail.sh"           # rwxr-xr-x
run "chmod 755 ../logs/cron_checkRepeatExpenses.sh" # rwxr-xr-x

[ -f ../logs/sendReminders_log.new          ] && run "rm -f ../logs/sendReminders_log.new"
[ -f ../logs/sendReminders_log.txt          ] && run "rm -f ../logs/sendReminders_log.txt"
[ -f ../logs/sendEmail_log.new              ] && run "rm -f ../logs/sendEmail_log.new"
[ -f ../logs/checkRepeatingExpenses_log.new ] && run "rm -f ../logs/checkRepeatingExpenses_log.new"


echo "NOW INSTALL THE CRON JOBS";
cat cron_jobs.txt
