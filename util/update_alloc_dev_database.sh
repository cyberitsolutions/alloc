#!/bin/bash

function quit {
  echo $1;
  exit;
}

[ -f /tmp/person.sql ] && quit "Pls remove /tmp/person.sql"

FILE="alloc_dev.sql"
rm -f ${FILE}

echo "drop database if exists alloc_dev; create database alloc_dev; use alloc_dev;" > ${FILE}

echo "Enter user 'alloc' mysql pass: "
mysqldump -u alloc -p alloc | grep -v "INSERT INTO active_sessions" >> ${FILE}
[ "${?}" -ne "0" ] && quit "Could not dump out alloc database!"


# append updates to the SQL file.
echo "\
update person set personActive = '0';
update person set personActive = '1', password ='/.iTV2iP8pLgs'
where username = 'alla'
   or username = 'clancy'
   or username = 'conz'
   or username = 'ron'
   or username = 'djk'
   or username = 'steve'
   or username = 'andrew'
   or username = 'arik'
   or username = 'manju'
   or username = 'jeremyc'
   or username = 'anonymous'
;
update person set password = '/.lBw./3lMC2Q' where username = 'anonymous';
delete from eventFilter;
" >> ${FILE}


cat ../db_changelog.sql >> ${FILE}
[ "${?}" -ne "0" ] && quit "Trouble concatenating db_changelog.sql file"



# refresh alloc_dev database with this script.
echo "Enter root mysql pass: "
mysql -u root -p < ${FILE}
[ "${?}" -ne "0" ] && quit "Could not execute all of ${FILE}!"

[ -f "${FILE}" ] && rm ${FILE}
[ "${?}" -ne "0" ] && quit "Could not remove ${FILE}!"
