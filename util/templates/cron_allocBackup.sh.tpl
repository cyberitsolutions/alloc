#!/bin/bash

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
mysqldump -t -u root ${p} ${ALLOC_DB_NAME} >> ${BACKUP_FILE}
gzip -f ${BACKUP_FILE}
chmod 600 ${BACKUP_FILE}.gz

