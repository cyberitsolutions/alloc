#!/bin/sh
#
#  Copyright 2006, Alex Lance, Clancy Malcolm, Cybersource Pty. Ltd.
#  
#  This file is part of allocPSA <info@cyber.com.au>.
#  
#  allocPSA is free software; you can redistribute it and/or modify it under the
#  terms of the GNU General Public License as published by the Free Software
#  Foundation; either version 2 of the License, or (at your option) any later
#  version.
#  
#  allocPSA is distributed in the hope that it will be useful, but WITHOUT ANY
#  WARRANTY; without even the implied warranty of MERCHANTABILITY or FITNESS FOR
#  A PARTICULAR PURPOSE.  See the GNU General Public License for more details.
#  
#  You should have received a copy of the GNU General Public License along with
#  allocPSA; if not, write to the Free Software Foundation, Inc., 51 Franklin
#  St, Fifth Floor, Boston, MA 02110-1301 USA
# 
#
#  Script to setup database, website permissions, cronjobs and generate alloc.inc
#



# Directory of this file
DIR="${0%/*}/"

# Source functions
. ${DIR}functions.sh


e "Beginning allocPSA Installation\n"

USAGE="Usage: ${0} [-B] FILE\n\n\t-B\tbatch mode, no prompting\n\tFILE\tconfiguration file\n"

if [ "0" != "$(id -u)" ]; then
  die "Please run this script as user root."
fi

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

# Get config vars
while [ "${DO_INSTALL:0:1}" != "y" ]; do
  DO_INSTALL=""

  # Quick check all the values are in the config file (CONFIG_VARS is specifed in functions.sh)
  for i in ${CONFIG_VARS}; do get_user_var "${i}" "Please enter ${i}" "${!i}"; done

  # Print out config values
  echo
  echo
  for i in ${CONFIG_VARS}; do echo "  ${i}: ${!i}"; done

  # Determine whether to continue
  get_user_var "DO_INSTALL" "Does the config look correct to you?" "yes"

done

# Determine whether to install the db 
get_user_var DO_DB "Install the database?" "yes"

# Install the db
if [ "${DO_DB:0:1}" = "y" ]; then

  get_user_var ROOT_DB_PASS "Enter the MySQL root password" "" "1"
  echo ""

  [ -n "${ROOT_DB_PASS}" ] && ROOT_DB_PASS=" -p${ROOT_DB_PASS} "
 
  # MySQL administrative tables 
  mysql -v -u root ${ROOT_DB_PASS} mysql <<EOMYSQL
  DROP DATABASE IF EXISTS ${ALLOC_DB_NAME};
  CREATE DATABASE ${ALLOC_DB_NAME};
  DELETE FROM user WHERE User = "${ALLOC_DB_USER}";
  DELETE FROM db WHERE User = "${ALLOC_DB_USER}";
  INSERT INTO user (Host, User, Password) values ("${ALLOC_DB_HOST}","${ALLOC_DB_USER}",PASSWORD("${ALLOC_DB_PASS}"));
  INSERT INTO db (Host, Db, User, Select_priv, Insert_priv, Update_priv, Delete_priv) values ("${ALLOC_DB_HOST}","${ALLOC_DB_NAME}", "${ALLOC_DB_USER}","y","y","y","y");
  FLUSH PRIVILEGES;
EOMYSQL
  [ "${?}" -ne "0" ] && fucked=1
  mysql -u root ${ROOT_DB_PASS} ${ALLOC_DB_NAME} < ${DIR}sql/db_structure.sql
  [ "${?}" -ne "0" ] && fucked=1
  mysql -u root ${ROOT_DB_PASS} ${ALLOC_DB_NAME} < ${DIR}sql/db_data.sql
  [ "${?}" -ne "0" ] && fucked=1

  if [ "${fucked}" = 1 ]; then
    e_failed "There was a problem installing the database".
  else
    e_ok "Installed the database."
  fi
fi


# If need be, suffix the config dirs with a slash
make_config_dirs_end_in_slashes


# Create the directories if need be
[ ! -d "${ALLOC_BACKUP_DIR}" ]       && run "mkdir -p ${ALLOC_BACKUP_DIR}"
[ ! -d "${ALLOC_LOG_DIR}" ]          && run "mkdir -p ${ALLOC_LOG_DIR}"
[ ! -d "${ALLOC_PATCH_DIR}" ]        && run "mkdir -p ${ALLOC_PATCH_DIR}"
[ ! -d "${ALLOC_DOCS_DIR}" ]         && run "mkdir -p ${ALLOC_DOCS_DIR}"
[ ! -d "${ALLOC_DOCS_DIR}clients" ]  && run "mkdir ${ALLOC_DOCS_DIR}clients"
[ ! -d "${ALLOC_DOCS_DIR}projects" ] && run "mkdir ${ALLOC_DOCS_DIR}projects"

# Fix group and perms
run "chgrp ${ALLOC_WEB_USER} ${ALLOC_DOCS_DIR}"
run "chgrp ${ALLOC_WEB_USER} ${ALLOC_DOCS_DIR}clients"
run "chgrp ${ALLOC_WEB_USER} ${ALLOC_DOCS_DIR}projects"
run "chgrp ${ALLOC_WEB_USER} ${ALLOC_LOG_DIR}"
run "chmod 700 ${ALLOC_BACKUP_DIR}"
run "chown root ${ALLOC_BACKUP_DIR}"
run "chmod 775 ${ALLOC_DOCS_DIR}"
run "chmod 775 ${ALLOC_DOCS_DIR}clients"
run "chmod 775 ${ALLOC_DOCS_DIR}projects"
run "chmod 775 ${ALLOC_LOG_DIR}"
[ ! -f "${ALLOC_LOG_DIR}alloc_email.log" ] && run "touch ${ALLOC_LOG_DIR}alloc_email.log"
run "chgrp ${ALLOC_WEB_USER} ${ALLOC_LOG_DIR}alloc_email.log"
run "chmod 775 ${ALLOC_LOG_DIR}alloc_email.log"

find ${DIR}.. -type f -path ${DIR}../.bzr -prune -exec chmod 664 {} \; # Files to rw-rw-r--
find ${DIR}.. -type d -path ${DIR}../.bzr -prune -exec chmod 775 {} \; # Dirs  to rwxrwxr-x

run "chmod 755 ${DIR}sql/dump_clean_db.sh"                # rwxr-xr-x
run "chmod 754 ${DIR}stylesheet_regen.py"                 # rwxr-xr--
run "chmod 754 ${DIR}misc/gpl_header.py"                  # rwxr-xr--
run "chmod 600 ${CONFIG_FILE}"                            # rw-------
run "chmod 700 ${DIR}install.sh"                          # rwx------
run "chmod 700 ${DIR}make_executables.sh"                 # rwx------
run "chmod 700 ${DIR}make_clean.sh"                       # rwx------
run "chmod 700 ${DIR}../patches/*.sh"                     # rwx------


# Create executables from templates
. ${DIR}make_executables.sh

# Loop through all possible patches and move all patches to applied_patches
# We are presuming that fresh installations already have all patches applied.
i=0;
while [ "${i}" -lt 10000 ]; do
  i=$((${i}+1));

  PATCH_SCRIPT="${DIR}../patches/patch-${i}.sh"
  PATCH_SQL="${DIR}../patches/patch-${i}.sql"

  PATCH_SCRIPT_OLD="${ALLOC_PATCH_DIR}patch-${i}.sh"
  PATCH_SQL_OLD="${ALLOC_PATCH_DIR}patch-${i}.sql"

  if [ -x "${PATCH_SCRIPT}" ] && [ ! -f "${PATCH_SCRIPT_OLD}" ]; then
    run "cp ${PATCH_SCRIPT} ${ALLOC_PATCH_DIR}"
  elif [ -f "${PATCH_SQL}" ] && [ ! -f "${PATCH_SQL_OLD}" ]; then
    run "cp ${PATCH_SQL} ${ALLOC_PATCH_DIR}"
  fi
done




if [ -z "${FAILED}" ]; then
  
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
  e_good "Installation Successful!"

  DIR_FULL=${PWD}/${DIR}
  DIR_FULL=${DIR_FULL/\.\//}

  echo
  echo " To complete the installation:                                  " 
  echo "                                                                " 
  echo "   1) Move the ${DIR}alloc.inc file into the PHP include_path.  "
  echo "                                                                "
  echo "   2) Install these into cron to be run as root:                "
  echo "                                                                "
  echo "     25  4 * * * ${ALLOC_BACKUP_DIR}cron_allocBackup.sh         "
  echo "     */5 * * * * ${DIR_FULL}cron_sendReminders.sh               "
  echo "     35  4 * * * ${DIR_FULL}cron_sendEmail.sh                   "
  echo "     45  4 * * * ${DIR_FULL}cron_checkRepeatExpenses.sh         "
  echo

else 
  e_bad "Installation has not completed successfully!"
  echo 
fi


