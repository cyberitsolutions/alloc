#!/bin/bash

# The reason we are using the root pass instead of the allocPSA user/pass,
# Is because different versions of mysql support different user permissions.
# With newer versions you need LOCK TABLES permission to dump out db. The allocPSA
# user doesn't (and can't have it, in order to be backward compatible), so we're
# stuck using user root.  This script should be chmod 700.

ROOT_DB_PASS="CONFIG_VAR_ROOT_DB_PASS"
ALLOC_DB_NAME="CONFIG_VAR_ALLOC_DB_NAME"
BACKUP_DIR="CONFIG_VAR_ALLOC_BACKUP_DIR"

[ -n "${ROOT_DB_PASS}" ] && p="-p${ROOT_DB_PASS}"

SUFFIX=`date +%a`;

  if [ -n "${1}" ]; then
    SUFFIX="${1}"
    
  elif [ "${SUFFIX}" = "Sat" ]; then
    SUFFIX=week`date +%U`;
  fi

BACKUP_FILE="${BACKUP_DIR}allocdump.sql.${SUFFIX}"


mysqldump -d -u root ${p} ${ALLOC_DB_NAME} > ${BACKUP_FILE}
[ "${?}" -ne "0" ] && fucked=1
mysqldump -t -u root ${p} ${ALLOC_DB_NAME} >> ${BACKUP_FILE}
[ "${?}" -ne "0" ] && fucked=1


if [ "${fucked}" = 1 ]; then
  e_failed "There was a problem backing up the ${ALLOC_DB_NAME} database".
else
  gzip -f ${BACKUP_FILE}
  chmod 600 ${BACKUP_FILE}.gz
fi



