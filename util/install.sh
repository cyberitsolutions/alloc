#!/bin/sh 
#
#
## Script to setup website permissions and generate an alloc.inc file.


function e_bl     { echo -en " \x1b[34;01m [\x1b[0m"; }                               # Echo bracket left
function e_br     { echo -en "\x1b[34;01m] \x1b[0m"; }                                # Echo bracket right
function e_ok     { e_bl; echo -en "\x1b[32;01m  OK  \x1b[0m"; e_br; echo -e ${1}; }  # Echo [  OK  ] $msg
function e_skip   { e_bl; echo -en "\x1b[33;01m SKIP \x1b[0m"; e_br; echo -e ${1}; }  # Echo [ SKIP ] $msg
function e_failed { e_bl; echo -en "\x1b[31;01mFAILED\x1b[0m"; e_br; echo -e ${1}; }  # Echo [FAILED] $msg
function e_dead   { e_bl; echo -en "\x1b[31;01m DEAD \x1b[0m"; e_br; echo -e ${1}; }  # Echo [ DEAD ] $msg
function e        { echo -en "\n\x1b[34;01m* \x1b[0m"; echo -e ${1}; }
function e_n      { echo -en "\n\x1b[34;01m* \x1b[0m"; echo -en ${1}; }


# Execute command, trap errors.
#
function run {
  ERR=""
  ERR=`$1 2>&1`
  if [ "${?}" -ne 0 ]; then
    e_failed "${1} -> ${ERR}"
    return 1 # This is way cool, will set the $? variable
  else
    #[ -n "${ERR}" ] && ERR="\n---${ERR//\\\n/\\\n---}"
    e_ok "${1}"
    [ -n "${ERR}" ] && echo -e "${ERR}"
    return 0
  fi
}

# Function to retrieve user input or plugin a default value for a VAR.
#
function get_user_var {
  # ${1} == "NAME_OF_VAR"
  # ${2} == "Please enter this var, text..."
  # ${3} == "default value"

  NAME=""
  default=""

  NAME="${1}"
  [ -n "${3}" ] && default=" [Default:${3}]"
  e_n "${2}${default}: "
  read "${NAME}"

  # If no input then use default
  if [ -z "${!NAME}" ] && [ -n "${3}" ] ; then
    eval "${NAME}"="${3// /\ }"
  fi
  #echo "Using: ${!NAME}"
}

# Get confirmation from user before executing command
#
function confirm_run {
  # $1 == "Are you sure you want to do this?"
  # $2 == "rm -rf /" (command)
  # $3 == "no" (default)

  USER_INPUT=""
  get_user_var "USER_INPUT" "${1} ($2)" "${3}"
  USER_INPUT="${USER_INPUT:0:1}"


  if [ "${USER_INPUT}" = "y" ] || [ "${USER_INPUT}" = "Y" ]; then
    run "${2}"
    return "${?}"
  else 
    e_skip "Skipping command: ${2}"
    return 1 # This is way cool, will set the $? variable
  fi

}


DIR=`dirnam ${0}`/


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

# Nup 
# [ -f ${DIR}../logs/sendReminders_log.new          ] && run "rm -f ${DIR}../logs/sendReminders_log.new"
# [ -f ${DIR}../logs/sendReminders_log.txt          ] && run "rm -f ${DIR}../logs/sendReminders_log.txt"
# [ -f ${DIR}../logs/sendEmail_log.new              ] && run "rm -f ${DIR}../logs/sendEmail_log.new"
# [ -f ${DIR}../logs/checkRepeatingExpenses_log.new ] && run "rm -f ${DIR}../logs/checkRepeatingExpenses_log.new"
# sed -e "s/REPLACEME/${PWD//\//\\/}/" < cronjobs.txt


echo "Now install the cron jobs:";
cat <<EOF
*/5 * * * * /path/to/alloc/util/cron_sendReminders.sh
25  4 * * * /path/to/alloc/util/alloc_DB_backup.sh
35  4 * * * /path/to/alloc/util/cron_sendEmail.sh
45  4 * * * /path/to/alloc/util/cron_checkRepeatExpenses.sh
EOF




