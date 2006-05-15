#!/bin/sh
#
#
# Script to setup database, website permissions, cronjobs and generate an alloc.inc file.
#


# Functions to help display messages 
# 

function e_red      { echo -en "\x1b[31;01m${1}\x1b[0m"; }
function e_green    { echo -en "\x1b[32;01m${1}\x1b[0m"; }
function e_yellow   { echo -en "\x1b[33;01m${1}\x1b[0m"; }
function e_blue     { echo -en "\x1b[34;01m${1}\x1b[0m"; }
function e_white_b  { echo -en "\x1b[1;37m${1}\x1b[0m"; }

function e_ok       { e_blue "  ["; e_green "  OK  "; e_blue "] "; echo -e ${1}; }           # Echo [  OK  ] $msg
function e_skip     { e_blue "  ["; e_yellow " SKIP "; e_blue "] "; echo -e ${1}; }          # Echo [ SKIP  ] $msg
function e_failed   { e_blue "  ["; e_red "FAILED"; e_blue "] "; echo -e ${1}; FAILED=1; }   # Echo [FAILED] $msg
function e_dead     { e_blue "  ["; e_red " DEAD "; e_blue "] "; echo -e ${1}; FAILED=1; }   # Echo [ DEAD ] $msg
function e          { e_blue "\n* "; e_white_b "${1}\n"; }
function e_n        { e_blue "\n* "; e_white_b "${1}"; }




# Execute command, catch errors.
#
function run {
  ERR="" # Can't make ERR a local var cause that operation sets the $? var
  ERR="`$1 2>&1`"
  if [ "${?}" -ne 0 ]; then
    e_failed "${1} -> ${ERR}"
    return 1 # This will set the $? variable
  else
    e_ok "${1}"
    [ -n "${ERR}" ] && echo -e "${ERR}"
    return 0
  fi
}


# Function to retrieve user input or plugin a default value for a VAR.
#
function get_user_var {
  # ${1} NAME_OF_VAR
  # ${2} Please enter this var, text...
  # ${3} Default value
  # ${4} If true, does a read -s = silent mode (for passwords)

  local NAME="${1}"
  [ -n "${4}" ] && local READ_SWITCH=" -s "


  # if variable hasn't already been set
  if [ -z "${!NAME}" ]; then
  
    # Print default
    [ -n "${3}" ] && local default=" "$(e_blue "[")${3}$(e_blue "]")
    e_n "${2}${default}: "

    # Read user input into variable 
    if [ -z "${DO_BATCH}" ]; then
      read ${READ_SWITCH} "${NAME}"
    fi

    # If no input then use default
    if [ -z "${!NAME}" ] && [ -n "${3}" ] ; then
      eval "${NAME}"="${3// /\ }"
    fi
  fi
  #echo "Using: ${!NAME}"
}


# Get confirmation from user before executing command
#
function confirm_run {
  # $1 Are you sure you want to do this?
  # $2 rm -rf / (a command to run)
  # $3 no (default)

  if [ -z "${DO_BATCH}" ]; then
    get_user_var "USER_INPUT" "${1} ($2)" "${3}"
    local USER_INPUT="${USER_INPUT:0:1}"
  else
    local USER_INPUT="y"
  fi

  if [ "${USER_INPUT}" = "y" ] || [ "${USER_INPUT}" = "Y" ]; then
    run "${2}"
    return "${?}"
  else 
    e_skip "Skipping command: ${2}"
    return 1 # This will set the $? variable
  fi

}

# Exit with an error message.
#
function die {
  e_dead "Exiting: ${1}"
  exit
}


###############################################################################################
#
e "Beginning allocPSA Installation\n"
#

USAGE="Usage: ${0} [-B] FILE\n\n\t-B\tbatch mode, no prompting\n\tFILE\tconfiguration file\n"


if [ "0" != "$(id -u)" ]; then
  die "Please run this script as user root."
fi

# Directory of this file
DIR="${0%/*}/"

if [ -f "${1}" ]; then
  CONFIG_FILE="${1}"
elif [ -f "${2}" ]; then
  CONFIG_FILE="${2}"
fi

# If -B is passed on the command line, skip the prompts and just install
if [ "${1}" = "-B" ] || [ "${2}" = "-B" ]; then
  DO_BATCH=1
fi

# Source the config file
[ ! -r "${CONFIG_FILE}" ] && die "${USAGE}"
. ${CONFIG_FILE}

# A list of all the variable set in this file, as a form of internal checking in the install.sh script
CONFIG_VARS="ALLOC_DB_NAME ALLOC_DB_USER ALLOC_DB_PASS ALLOC_DB_HOST ALLOC_WEB_USER ALLOC_DOCS_DIR ALLOC_WEB_URL_PREFIX ALLOC_BACKUP_DIR"

# Quick check all the values are in the config file
for i in ${CONFIG_VARS}; do if [ -z "${!i}" ]; then die "Missing ${i} from config file ${CONFIG_FILE}"; fi; done

# Print out config values
for i in ${CONFIG_VARS}; do echo "${i}: ${!i}"; done

# Determine whether to continue
get_user_var DO_INSTALL "Does the config in ${CONFIG_FILE} look correct to you?" "yes"

# Bail out
[ "${DO_INSTALL:0:1}" != "y" ] && die

# Determine whether to install the db 
get_user_var DO_DB "Install the database?" "yes"

# Install the db
if [ "${DO_DB:0:1}" = "y" ]; then

  get_user_var DB_PASS "Enter the MySQL root password" "" "1"
  echo ""

  [ -n "${DB_PASS}" ] && DB_PASS=" -p${DB_PASS} "
 
  # MySQL administrative tables 
  mysql -v -u root ${DB_PASS} mysql <<EOMYSQL
  DROP DATABASE IF EXISTS ${ALLOC_DB_NAME};
  CREATE DATABASE ${ALLOC_DB_NAME};
  DELETE FROM user WHERE User = "${ALLOC_DB_USER}";
  DELETE FROM db WHERE User = "${ALLOC_DB_USER}";
  INSERT INTO user (Host, User, Password) values ("${ALLOC_DB_HOST}","${ALLOC_DB_USER}",PASSWORD("${ALLOC_DB_PASS}"));
  INSERT INTO db (Host, Db, User, Select_priv, Insert_priv, Update_priv, Delete_priv) values ("${ALLOC_DB_HOST}","${ALLOC_DB_NAME}", "${ALLOC_DB_USER}","y","y","y","y");
  FLUSH PRIVILEGES;
EOMYSQL

  mysql -u root ${DB_PASS} ${ALLOC_DB_NAME} < ${DIR}db_structure.sql
  mysql -u root ${DB_PASS} ${ALLOC_DB_NAME} < ${DIR}db_data.sql
 
  e "If there were no errors printed above then the database should now be installed." 
  echo
fi


# Append a slash if need be
[ "${ALLOC_DOCS_DIR:(-1):1}" != "/" ] && ALLOC_DOCS_DIR=${ALLOC_DOCS_DIR}/; 
[ "${ALLOC_BACKUP_DIR:(-1):1}" != "/" ] && ALLOC_BACKUP_DIR=${ALLOC_BACKUP_DIR}/; 

# Create the directories if need be
[ ! -d "${ALLOC_DOCS_DIR}" ]         && run "mkdir -p ${ALLOC_DOCS_DIR}"
[ ! -d "${ALLOC_BACKUP_DIR}" ]       && run "mkdir -p ${ALLOC_BACKUP_DIR}"
[ ! -d "${ALLOC_DOCS_DIR}clients" ]  && run "mkdir ${ALLOC_DOCS_DIR}clients"
[ ! -d "${ALLOC_DOCS_DIR}projects" ] && run "mkdir ${ALLOC_DOCS_DIR}projects"

# Fix group and perms
run "chgrp ${ALLOC_WEB_USER} ${ALLOC_DOCS_DIR}"
run "chgrp ${ALLOC_WEB_USER} ${ALLOC_DOCS_DIR}clients"
run "chgrp ${ALLOC_WEB_USER} ${ALLOC_DOCS_DIR}projects"
run "chmod 775 ${ALLOC_DOCS_DIR}"
run "chmod 775 ${ALLOC_BACKUP_DIR}"
run "chmod 775 ${ALLOC_DOCS_DIR}clients"
run "chmod 775 ${ALLOC_DOCS_DIR}projects"

find ${DIR}.. -type f -path ${DIR}../.bzr -prune -exec chmod 664 {} \; # Files to rw-rw-r--
find ${DIR}.. -type d -path ${DIR}../.bzr -prune -exec chmod 775 {} \; # Dirs  to rwxrwxr-x

run "chmod 777 ${DIR}../images/"                          # php created images
run "chmod 777 ${DIR}../images/*"                         # php created images
run "chmod 777 ${DIR}../report/files/"                    # uploaded files
run "chmod 777 ${DIR}../stylesheets/*"                    # rwxrwxrwx
run "chmod 755 ${DIR}dump_clean_db.sh"                    # rwxr-xr-x
run "chmod 754 ${DIR}stylesheet_regen.py"                 # rwxr-xr--
run "chmod 754 ${DIR}update_alloc_dev_database.sh"        # rwxr-xr--
run "chmod 754 ${DIR}gpl_header.py"                       # rwxr-xr--
run "chmod 600 ${CONFIG_FILE}"                            # rw-------
run "chmod 700 ${DIR}install.sh"                          # rwx------

[ ! -f "${DIR}../logs/alloc_email.log" ] && run "touch ${DIR}../logs/alloc_email.log"
run "chmod 777 ${DIR}../logs"                             # gonna need to write and delete
run "chmod 777 ${DIR}../logs/alloc_email.log"             # gonna need to write and delete


# Make the alloc.inc file
e "Creating alloc.inc"
cat ${DIR}templates/alloc.inc.tpl \
| sed -e "s/CONFIG_VAR_ALLOC_DB_NAME/${ALLOC_DB_NAME}/" \
| sed -e "s/CONFIG_VAR_ALLOC_DB_USER/${ALLOC_DB_USER}/" \
| sed -e "s/CONFIG_VAR_ALLOC_DB_PASS/${ALLOC_DB_PASS}/" \
| sed -e "s/CONFIG_VAR_ALLOC_DB_HOST/${ALLOC_DB_HOST}/" \
| sed -e "s/CONFIG_VAR_ALLOC_DOCS_DIR/${ALLOC_DOCS_DIR//\//\/}/" \
> ${DIR}alloc.inc

if [ -f "${DIR}alloc.inc" ]; then 
  e_ok "Created alloc.inc"
  run "chmod 640 ${DIR}alloc.inc"                           
  run "chgrp ${ALLOC_WEB_USER} ${DIR}alloc.inc"             
else 
  e_failed "Could not create alloc.inc"; 
fi

e "Creating alloc_DB_backup.sh"
cat ${DIR}templates/alloc_DB_backup.sh.tpl \
| sed -e "s/CONFIG_VAR_ALLOC_DB_NAME/${ALLOC_DB_NAME}/" \
| sed -e "s/CONFIG_VAR_ALLOC_DB_USER/${ALLOC_DB_USER}/" \
| sed -e "s/CONFIG_VAR_ALLOC_DB_PASS/${ALLOC_DB_PASS}/" \
| sed -e "s/CONFIG_VAR_ALLOC_DB_HOST/${ALLOC_DB_HOST}/" \
| sed -e "s/CONFIG_VAR_ALLOC_DOCS_DIR/${ALLOC_DOCS_DIR//\//\/}/" \
| sed -e "s/CONFIG_VAR_ALLOC_BACKUP_DIR/${ALLOC_BACKUP_DIR//\//\/}/" \
> ${DIR}alloc_DB_backup.sh

if [ -f "${DIR}alloc_DB_backup.sh" ]; then 
  e_ok "Created alloc_DB_backup.sh"
  run "chmod 755 ${DIR}alloc_DB_backup.sh"            
  run "mv ${DIR}alloc_DB_backup.sh ${ALLOC_BACKUP_DIR}"
else 
  e_failed "Could not create alloc_DB_backup.sh"; 
fi

# Append a slash if need be
[ "${ALLOC_WEB_URL_PREFIX:(-1):1}" != "/" ] && ALLOC_WEB_URL_PREFIX="${ALLOC_WEB_URL_PREFIX}/"


e "Creating cron_checkRepeatExpenses.sh"
cat ${DIR}templates/cron_checkRepeatExpenses.sh.tpl \
| sed -e "s/CONFIG_VAR_ALLOC_WEB_URL_PREFIX/${ALLOC_WEB_URL_PREFIX//\//\/}/" \
> ${DIR}cron_checkRepeatExpenses.sh

if [ -f "${DIR}cron_checkRepeatExpenses.sh" ]; then 
  e_ok "Created cron_checkRepeatExpenses.sh"
  run "chmod 755 ${DIR}cron_checkRepeatExpenses.sh"     
else 
  e_failed "Could not create cron_checkRepeatExpenses.sh"; 
fi


e "Creating cron_sendEmail.sh"
cat ${DIR}templates/cron_sendEmail.sh.tpl \
| sed -e "s/CONFIG_VAR_ALLOC_WEB_URL_PREFIX/${ALLOC_WEB_URL_PREFIX//\//\/}/" \
> ${DIR}cron_sendEmail.sh

if [ -f "${DIR}cron_sendEmail.sh" ]; then 
  e_ok "Created cron_sendEmail.sh"
  run "chmod 755 ${DIR}cron_sendEmail.sh"     
else 
  e_failed "Could not create cron_sendEmail.sh"; 
fi


e "Creating cron_sendReminders.sh"
cat ${DIR}templates/cron_sendReminders.sh.tpl \
| sed -e "s/CONFIG_VAR_ALLOC_WEB_URL_PREFIX/${ALLOC_WEB_URL_PREFIX//\//\/}/" \
> ${DIR}cron_sendReminders.sh

if [ -f "${DIR}cron_sendReminders.sh" ]; then 
  e_ok "Created cron_sendReminders.sh"
  run "chmod 755 ${DIR}cron_sendReminders.sh"     
else 
  e_failed "Could not create cron_sendReminders.sh"; 
fi







if [ -z "${FAILED}" ]; then
  e "Installation Successful."
  
  f="$(basename ${CONFIG_FILE})"
  if [ ! -f "${ALLOC_BACKUP_DIR}${f}" ] || ([ -f "${ALLOC_BACKUP_DIR}${f}" ] && [ -n "$(diff ${CONFIG_FILE} ${ALLOC_BACKUP_DIR}${f})" ]); then

    get_user_var MOVE_FILE "Move ${CONFIG_FILE} to ${ALLOC_BACKUP_DIR}?" "yes"

    if [ "${MOVE_FILE:0:1}" = "y" ]; then
      [ -f "${ALLOC_BACKUP_DIR}${f}" ] && run "mv ${ALLOC_BACKUP_DIR}${f} ${ALLOC_BACKUP_DIR}${f}.bak"
      run "mv ${CONFIG_FILE} ${ALLOC_BACKUP_DIR}" "yes"
      CONFIG_FILE="${ALLOC_BACKUP_DIR}${CONFIG_FILE}"
    fi

  fi
  e "To repeat this installation run ${0} ${CONFIG_FILE/\.\//}"

else 
  e "Installation has not completed successfully!"
fi


DIR_FULL=${PWD}/${DIR}
DIR_FULL=${DIR_FULL/\.\//}

echo
echo " # # # # # # # # # # # # # # # # # # # # # # # # # # # # # # # # # # # # #"
echo "                                                                          " 
echo " To complete the installation:                                            " 
echo "                                                                          " 
echo "   1) Move ${DIR}alloc.inc into the PHP include_path. Do not store it in  "
echo "      the web root, as it contains your database password.                " 
echo "                                                                          "
echo "   2) Install these into cron to be run as root:                          "
echo "                                                                          "
echo "     25  4 * * * ${ALLOC_BACKUP_DIR}alloc_DB_backup.sh                    "
echo "     */5 * * * * ${DIR_FULL}cron_sendReminders.sh                         "
echo "     35  4 * * * ${DIR_FULL}cron_sendEmail.sh                             "
echo "     45  4 * * * ${DIR_FULL}cron_checkRepeatExpenses.sh                   "
echo "                                                                          " 
echo " # # # # # # # # # # # # # # # # # # # # # # # # # # # # # # # # # # # # #"


