#!/bin/bash
#
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
# To update database structure for db's which already have data in them.
#


# Directory of this file
DIR="${0%/*}/"

# Source functions
. ${DIR}functions.sh


function backup_db {

  if [ -z "${DONE_PATCHES}" ]; then

    # Welcome
    e "Beginning allocPSA update"

    # Back up database
    SUFFIX=$(date "+%F_%R")
    BACKUP_FILE="${ALLOC_BACKUP_DIR}allocdump.sql.${SUFFIX}.gz"
    ${ALLOC_BACKUP_DIR}cron_allocBackup.sh ${SUFFIX}

    # Check DB backed up ok
    if [ -f "${BACKUP_FILE}" ]; then
      e_ok "Created backup file: ${BACKUP_FILE}"

    # Else bail out
    else
      e_failed "Couldn't create backup file: ${BACKUP_FILE}"
      get_user_var "CONTINUE" "Unable to back up database. Continue anyway?" "no"
      [ "${CONTINUE:0:1}" = "n" ] && die "Bailing out."
    fi

    DONE_PATCHES=1;
  fi
}


# Compiled in vars
ALLOC_DB_NAME="CONFIG_VAR_ALLOC_DB_NAME"
ALLOC_BACKUP_DIR="CONFIG_VAR_ALLOC_BACKUP_DIR"
ALLOC_PATCH_DIR="CONFIG_VAR_ALLOC_PATCH_DIR"
ROOT_DB_PASS="CONFIG_VAR_ROOT_DB_PASS"

# Extra vars, just in case the patch.sh script needs to do internal patches sometime..
ALLOC_WEB_USER="CONFIG_VAR_ALLOC_WEB_USER"
ALLOC_DOCS_DIR="CONFIG_VAR_ALLOC_DOCS_DIR"
ALLOC_WEB_URL_PREFIX="CONFIG_VAR_ALLOC_WEB_URL_PREFIX"

# Whack a -p in front of db password for mysql command line
[ -n "${ROOT_DB_PASS}" ] && ROOT_DB_PASS="-p${ROOT_DB_PASS}"

# Loop through all possible patches
i=0; 
while [ "${i}" -lt 10000 ]; do 
  i=$((${i}+1)); 
  
  PATCH_SCRIPT="${DIR}../patches/patch-${i}.sh"
  PATCH_SQL="${DIR}../patches/patch-${i}.sql"

  PATCH_SCRIPT_OLD="${ALLOC_PATCH_DIR}patch-${i}.sh"
  PATCH_SQL_OLD="${ALLOC_PATCH_DIR}patch-${i}.sql"
 
  # If there's an executable and it hasn't already been applied (and thus moved to applied_patches/) 
  if [ -x "${PATCH_SCRIPT}" ] && [ ! -f "${PATCH_SCRIPT_OLD}" ]; then
    backup_db
    e "Running: ${PATCH_SCRIPT}"
    . ${PATCH_SCRIPT}
    if [ "${?}" -ne "0" ]; then
      e_failed "${PATCH_SCRIPT}"
    else
      e_ok "${PATCH_SCRIPT}"
      run "cp ${PATCH_SCRIPT} ${ALLOC_PATCH_DIR}"
    fi
    
  # If there's an SQL file and it hasn't already been applied (and thus moved to applied_patches/) 
  elif [ -f "${PATCH_SQL}" ] && [ ! -f "${PATCH_SQL_OLD}" ]; then
    backup_db
    e "Running: ${PATCH_SQL}"
    mysql -u root ${ROOT_DB_PASS} ${ALLOC_DB_NAME} < ${PATCH_SQL}
    if [ "${?}" -ne "0" ]; then
      e_failed "${PATCH_SQL}"
    else
      e_ok "${PATCH_SQL}"
      run "cp ${PATCH_SQL} ${ALLOC_PATCH_DIR}"
    fi
  fi

done;


# If any patch failed then prompt the user whether they want to restore the DB to the way it originally was
if [ -n "${FAILED}" ]; then
  get_user_var "UNDO_DB" "It looks like not all of the patches were successfully applied. Revert the database to its former state?" "yes"
  if [ "${UNDO_DB:0:1}" = "y" ]; then
    mysql -v -u root ${ROOT_DB_PASS} ${ALLOC_DB_NAME} <<EOMYSQL
    DROP DATABASE IF EXISTS ${ALLOC_DB_NAME};
    CREATE DATABASE ${ALLOC_DB_NAME};
EOMYSQL
    zcat ${BACKUP_FILE} | mysql -u root ${ROOT_DB_PASS} ${ALLOC_DB_NAME}
  fi
fi





