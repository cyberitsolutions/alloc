#!/bin/bash
#
# Script to patch alloc with the latest updates
# Mostly to do DB structure updates without losing data
#
# Welcome..



# Directory of this file
DIR="${0%/*}/"

# Source functions
.${DIR}functions.sh


e "Beginning DB Patch script"


# Running this file will 
# - Back up the database
# - Go through the db_changelog directory and for each file
# - Check whether there is a corresponding patch file in eg /var/local/allocPSA/db_changelog/
# - If not then apply any patches in order, be they shell scripts or sql
# - Copy or move file from /webroot/allocPSA/changelog to /var/local/allocPSA/db_changelog/

# Need vars: ALLOC_LOG_DIR, ALLOC_BACKUP_DIR, ALLOC_PATCH_DIR, The root DB pass, ALLOC_DB_NAME, 

## to be completed 
exit








# Check the config file has the database info we need
if [ -z "${DATABASE}" ] || [ -z "${USERNAME}" ]; then
  echo "Fatal Error: ${DIR_HOME}${CONFIG_FILE} does not contain all the required variables!"
  exit;
fi

# Backup filename
DIR_BACKUPS="${DIR_HOME}../db_backups/"
BACKUP_FILE="${DIR_BACKUPS}${DATABASE}_struc_and_data_$(date '+%F_%H%M').tar"

# Check we have a backups directory
if [ ! -d "${DIR_BACKUPS}" ]; then
  echo "No db_backups directory found!(${DIR_BACKUPS})"
  exit;
fi

# Back up the database
pg_dump -U ${USERNAME} ${DATABASE} -F t > ${BACKUP_FILE}

# Comment out pg_dump line above and uncomment this to speed up debug time..
# touch ${BACKUP_FILE}

# Compress
gzip ${BACKUP_FILE}

# Sanity check
if [ -f ${BACKUP_FILE}.gz ]; then 
  echo "Created database backup file: ${BACKUP_FILE}.gz"
else
  echo "Problem creating database backup file: ${BACKUP_FILE}.gz"
  exit;
fi

# Get most recent successfully applied patch
MOST_RECENT_PATCH=$(psql -U ${USERNAME} ${DATABASE} -A -t -c "SELECT patch_name FROM patch_log ORDER BY patch_name DESC LIMIT 1;")
if [ -z "${MOST_RECENT_PATCH}" ]; then
  echo "No MOST_RECENT_PATCH!: ${MOST_RECENT_PATCH}"
  exit
fi

# Echo the previous patch that was applied.
echo "Most recent successful patch: ${MOST_RECENT_PATCH}"

# iterator
i=0
# flag
start=0

# Loop through the patch-XXX.sql files
while [ ${i} -lt 2000 ]; do
 
  # Filename
  file="${DIR_HOME}changelog/patch-${i}.sql"

  # If file exists and we have moved past the previously applied patch
  if [ -f "${file}" ] && [ "${start}" -eq 1 ]; then

    # Log this patch's date and comment
    datetime=$(date "+%F %X")
    comment="$(grep \\-\\- ${file} | sed -e 's/-- /+ /g')"
    comment="$(echo ${comment//\'/})"
    insert="INSERT INTO patch_log (patch_name,patch_desc,patch_date) VALUES ('patch-${i}.sql','${comment}','${datetime}')"

    # Apply patch file to database in a single transaction
    patch="$(cat ${file})"
    str="START TRANSACTION;\n${patch};\n${insert};\nCOMMIT;"
    echo -e "\nExecuting: ${file}"
    echo -e "${comment}"

    # Run patch
    psql -U ${USERNAME} ${DATABASE} -c "$(echo -e ${str})"

    # Check return value for problems
    rtn=${?}
    echo "Returns: '${rtn}'"
    if [ "${rtn}" -eq 1 ]; then
      echo "!!! Problem applying file: ${file} !!!"
      exit;
    fi
  fi
 
  # This patch file was applied previously, so we can start examining new patch files from now on.
  if [ "patch-${i}.sql" = "${MOST_RECENT_PATCH}" ]; then 
    start=1;
  fi
  
  let i++;
done

# Finit.
echo "Post-installation complete.";




