#!/bin/bash

function quit {
  echo $1;
  exit;
}

[ -z "${1}" ] && die "usage: ${0} FILE.sql"

FILE="alloc_test.sql"
rm -f ${FILE}

echo "drop database if exists alloc_test; create database alloc_test; use alloc_test;" > ${FILE}

cat ${1} >> ${FILE}


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



# refresh alloc_dev database with this script.
echo "Enter root mysql pass: "
mysql -u root < ${FILE}
[ "${?}" -ne "0" ] && quit "Could not execute all of ${FILE}!"

[ -f "${FILE}" ] && rm ${FILE}
[ "${?}" -ne "0" ] && quit "Could not remove ${FILE}!"
