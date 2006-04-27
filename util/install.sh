#!/bin/sh --posix
#
#
# Script to setup website permissions and generate an alloc.inc file.
#


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
  local ERR="`$1 2>&1`"
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
  # ${1} "NAME_OF_VAR"
  # ${2} "Please enter this var, text..."
  # ${3} "Default value"
  # ${4} "If >0 chars long, does a read -s = silent mode (for passwords)"

  local NAME="${1}"
  [ -n "${4}" ] && local READ_SWITCH=" -s "

  # if variable hasn't already been set
  if [ -z "${!NAME}" ]; then
  
    # Print default
    [ -n "${3}" ] && local default=" [Default:${3}]"
    e_n "${2}${default}: "

    # Read user input into variable 
    read ${READ_SWITCH} "${NAME}"

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
  # $1 "Are you sure you want to do this?"
  # $2 "rm -rf /" (command)
  # $3 "no" (default)

  get_user_var "USER_INPUT" "${1} ($2)" "${3}"
  local USER_INPUT="${USER_INPUT:0:1}"


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


###########################
#
#
# Begin Installation
#

if [ "0" != "$(id -u)" ]; then
  die "Please run this script as user root."
fi

# Directory of this file
DIR="${0%/*}/"


# Source the config file
. ${DIR}install.cfg

# Quick check all the values are in the config file
for i in ${CONFIG_VARS}; do if [ -z "${!i}" ]; then die "Missing ${i} from config file ${DIR}install.cfg"; fi; done

# Print out config values
for i in ${CONFIG_VARS}; do echo "${i}: ${!i}"; done

# Determine whether to continue
get_user_var DO_INSTALL "Does the config in ${DIR}install.cfg look alright to you?" "yes"

# Bail out
[ "${DO_INSTALL:0:1}" != "y" ] && die

# Determine whether to install the db 
get_user_var DO_DB "Install the database?" "yes"

# Install the db
if [ "${DO_DB:0:1}" = "y" ]; then

  get_user_var DB_PASS "Enter the MySQL root password" "" "1"
  echo ""
 
  # MySQL administrative tables 
  mysql -v -u root -p${DB_PASS} mysql <<EOMYSQL
  DROP DATABASE IF EXISTS ${ALLOC_DB_NAME};
  CREATE DATABASE ${ALLOC_DB_NAME};
  DELETE FROM user WHERE User = "${ALLOC_DB_USER}";
  DELETE FROM db WHERE User = "${ALLOC_DB_USER}";
  INSERT INTO user (Host, User, Password) values ("${ALLOC_DB_HOST}","${ALLOC_DB_USER}",PASSWORD("${ALLOC_DB_PASS}"));
  INSERT INTO db (Host, Db, User, Select_priv, Insert_priv, Update_priv, Delete_priv) values ("${ALLOC_DB_HOST}","${ALLOC_DB_NAME}", "${ALLOC_DB_USER}","y","y","y","y");
  FLUSH PRIVILEGES;
EOMYSQL

  mysql -u root -p${DB_PASS} ${ALLOC_DB_NAME} < ${DIR}db_structure.sql
  mysql -u root -p${DB_PASS} ${ALLOC_DB_NAME} < ${DIR}db_data.sql
 
  e "If there were no errors printed above then the database should now be installed." 
fi


# If uploaded docs directory not set, then die
[ -z "${ALLOC_DOCS_DIR}" ] && die "Please set ALLOC_DOCS_DIR in ${DIR}install.cfg"; 

# Append a slash if need be
[ "${ALLOC_DOCS_DIR:(-1):1}" != "/" ] && ALLOC_DOCS_DIR=${ALLOC_DOCS_DIR}/; 

# Create the directories if need be
[ ! -d "${ALLOC_DOCS_DIR}" ]         && run "mkdir ${ALLOC_DOCS_DIR}"
[ ! -d "${ALLOC_DOCS_DIR}clients" ]  && run "mkdir ${ALLOC_DOCS_DIR}clients"
[ ! -d "${ALLOC_DOCS_DIR}projects" ] && run "mkdir ${ALLOC_DOCS_DIR}projects"

# Fix group and perms
run "chgrp ${ALLOC_WEB_USER} ${ALLOC_DOCS_DIR}"
run "chgrp ${ALLOC_WEB_USER} ${ALLOC_DOCS_DIR}clients"
run "chgrp ${ALLOC_WEB_USER} ${ALLOC_DOCS_DIR}projects"
run "chmod 755 ${ALLOC_DOCS_DIR}"
run "chmod 755 ${ALLOC_DOCS_DIR}clients"
run "chmod 755 ${ALLOC_DOCS_DIR}projects"

find ${DIR}.. -type f -path ${DIR}../.bzr -prune -exec chmod 664 {} \; # Files to rw-rw-r--
find ${DIR}.. -type d -path ${DIR}../.bzr -prune -exec chmod 775 {} \; # Dirs  to rwxrwxr-x

run "chmod 777 ${DIR}../images/"                          # php created images
run "chmod 777 ${DIR}../images/*"                         # php created images
run "chmod 777 ${DIR}../report/files/"                    # uploaded files
run "chmod 777 ${DIR}../stylesheets/*"                    # rwxrwxrwx
run "chmod 755 ${DIR}dump_clean_db.sh"                    # rwxr-xr-x
run "chmod 755 ${DIR}alloc_DB_backup.sh"                  # rwxr-xr-x
run "chmod 754 ${DIR}stylesheet_regen.py"                 # rwxr-xr--
run "chmod 754 ${DIR}update_alloc_dev_database.sh"        # rwxr-xr--
run "chmod 754 ${DIR}gpl_header.py"                       # rwxr-xr--
run "chmod 755 ${DIR}cron_sendReminders.sh"               # rwxr-xr-x 
run "chmod 755 ${DIR}cron_sendEmail.sh"                   # rwxr-xr-x
run "chmod 755 ${DIR}cron_checkRepeatExpenses.sh"         # rwxr-xr-x
run "chmod 600 ${DIR}install.cfg"                         # rw-------
run "chmod 700 ${DIR}install.sh"                          # rwx------
run "chmod 640 ${DIR}alloc.inc"                           # rw-r-----
run "chgrp ${ALLOC_WEB_USER} ${DIR}alloc.inc"             # chgrp apache alloc.inc

[ ! -f "${DIR}../logs/alloc_email.log" ] && run "touch ../logs/alloc_email.log"
run "chmod 777 ${DIR}../logs"                             # gonna need to write and delete
run "chmod 777 ${DIR}../logs/alloc_email.log"             # gonna need to write and delete


# Determine include_path

# Need to put db info and file upload location into alloc.inc

#if []
#cat ${DIR}alloc.inc \
#| sed -e "s/CONFIG_VAR_ALLOC_DB_NAME/${ALLOC_DB_NAME}/" \
#| sed -e "s/CONFIG_VAR_ALLOC_DB_USER/${ALLOC_DB_USER}/" \
#| sed -e "s/CONFIG_VAR_ALLOC_DB_PASS/${ALLOC_DB_PASS}/" \
#| sed -e "s/CONFIG_VAR_ALLOC_DB_HOST/${ALLOC_DB_HOST}/" \
#| sed -e "s/CONFIG_VAR_ALLOC_DOCS_DIR/${ALLOC_DOCS_DIR//\//\/}/" \
#> a.inc.finit


# Need to put alloc.inc into include_path

# Need to put database info into alloc_DB_backup.sh

e "To use all of allocPSA features you will need to install these into cron:";
 

DIR_FULL=${PWD}/${DIR}
DIR_FULL=${DIR_FULL/\.\//}

cat <<EOF
*/5 * * * * ${DIR_FULL}cron_sendReminders.sh
25  4 * * * ${DIR_FULL}alloc_DB_backup.sh
35  4 * * * ${DIR_FULL}cron_sendEmail.sh
45  4 * * * ${DIR_FULL}cron_checkRepeatExpenses.sh
EOF


e "Installation Complete"

