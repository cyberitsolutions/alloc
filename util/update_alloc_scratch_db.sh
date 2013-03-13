#!/bin/bash

function die {
  echo $1
  exit 1;
}

if [ -z "${1}" ]; then
  echo "To import a sanitized version of the prod alloc db to alloc-scratch. Do this:"
  echo "1. alamo: mysqldump -u root -p alloc --routines --single-transaction > db.sql"
  echo "2. alamo: scp db.sql cyber@alloc-scratch:"
  echo "3. alloc-scratch: ${0} db.sql"
  exit 1;
fi

read -p "Enter the local mysql root password: " -s password

[ "${?}" -ne "0" ] || [ -z "${password}" ] && die "Error reading password."

mysql -u root -p${password} <<END_OF_DOCUMENT
DROP DATABASE IF EXISTS alloc;
CREATE DATABASE alloc;
END_OF_DOCUMENT

[ "${?}" -ne "0" ] && die "Error dropping and creating alloc database."

mysql -u root -p${password} alloc < $1

[ "${?}" -ne "0" ] && die "Error importing production alloc database."

mysql -u root -p${password} alloc <<END_OF_DOCUMENT
-- 69 is Steve. He has perm to change data.
SET @personID=69;

-- Default user password is "password"
UPDATE person SET password = '/.iTV2iP8pLgs';
UPDATE transaction SET amount = FLOOR(RAND() * 50000);
UPDATE projectPerson SET rate = '2500';

-- for monkey patching v185
UPDATE timeSheetItem SET dateTimeSheetItem = NULL WHERE dateTimeSheetItem = '0000-00-00';
SOURCE /var/www/installation/db_triggers.sql;

UPDATE timeSheetItem SET rate = '2500';
END_OF_DOCUMENT

[ "${?}" -ne "0" ] && die "Error sanitizing alloc-scratch database."

echo "Don't forget to delete $1"
echo "Now go and work your way through http://alloc-scratch/installation/patch.php";
