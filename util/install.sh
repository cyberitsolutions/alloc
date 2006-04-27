#!/bin/bash 

function run {
  #ERR=`$1 2>&-`
  STATUS="  OK  "
  ERR=`$1 2>&1`
  [ "${?}" -ne 0 ] && STATUS="FAILED"
  [ "${ERR}" != "" ] && ERR="\n            ${ERR}"
  echo -e " [${STATUS}]  ${1} ${ERR}"
}


DIR=`dirname ${0}`/


find ${DIR}.. -type f -exec chmod 664 {} \;
find ${DIR}.. -type d -exec chmod 775 {} \;
find ${DIR}.. -type f -exec chgrp alloc {} \;

run "chmod 777 ${DIR}../images/"                          # php created images
run "chmod 777 ${DIR}../images/big*"                      # php created images
run "chmod 777 ${DIR}../images/user*"                     # php created images
run "chmod 777 ${DIR}../report/files/"                    # uploaded files
run "chmod 777 ${DIR}../stylesheets/*"                    # rwxrwxrwx

run "chmod 755 ${DIR}dump_clean_db.sh"                  # rwxr-xr-x
run "chmod 755 ${DIR}alloc_DB_backup.sh"                # rwxr-xr-x
run "chmod 700 ${DIR}install.sh"                        # rwxr-----
run "chmod 754 ${DIR}stylesheet_regen.py"               # rwxr-xr--
run "chmod 754 ${DIR}update_alloc_dev_database.sh"      # rwxr-xr--
run "chmod 754 ${DIR}gpl_header.py"                     # rwxr-xr--
run "chmod 777 ${DIR}INSTALLER_LOCK"                    # rwxrwxrwx
run "chmod 755 ${DIR}cron_sendReminders.sh"             # rwxr-xr-x 
run "chmod 755 ${DIR}cron_sendEmail.sh"                 # rwxr-xr-x
run "chmod 755 ${DIR}cron_checkRepeatExpenses.sh"       # rwxr-xr-x

run "chown alloc ${DIR}../logs"                           # gonna be run by user alloc
[ ! -f "${DIR}../logs/alloc_email.log" ] && run "touch ../logs/alloc_email.log"
run "chmod 777 ${DIR}../logs"                             # gonna need to write and delete
run "chmod 777 ${DIR}../logs/alloc_email.log"             # gonna need to write and delete

# [ -f ${DIR}../logs/sendReminders_log.new          ] && run "rm -f ${DIR}../logs/sendReminders_log.new"
# [ -f ${DIR}../logs/sendReminders_log.txt          ] && run "rm -f ${DIR}../logs/sendReminders_log.txt"
# [ -f ${DIR}../logs/sendEmail_log.new              ] && run "rm -f ${DIR}../logs/sendEmail_log.new"
# [ -f ${DIR}../logs/checkRepeatingExpenses_log.new ] && run "rm -f ${DIR}../logs/checkRepeatingExpenses_log.new"


#sed -e "s/REPLACEME/${PWD//\//\\/}/" < cronjobs.txt




echo "Now install the cron jobs:";
cat <<EOF
*/5 * * * * /path/to/alloc/util/cron_sendReminders.sh
0   4 * * * /path/to/alloc/util/alloc_DB_backup.sh
5   4 * * * /path/to/alloc/util/cron_sendEmail.sh
10  4 * * * /path/to/alloc/util/cron_checkRepeatExpenses.sh
EOF




