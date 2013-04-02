#!/bin/bash -ex

USERNAME=root
#PASSWORD=
DATABASE=alloc
HOSTNAME=localhost
MYSQL="mysql -u ${USERNAME} -p${PASSWORD} -h ${HOSTNAME}"

echo "This thing will takes hours depending on the size of your database.";

if [ -z "${PASSWORD}" ]; then
  echo "Please set the database password.";
  exit 1;
fi

mkdir -p ./orig
mkdir -p ./tmp
mkdir -p ./done

cd ./orig;

# backup
for i in $(echo "SHOW TABLE STATUS WHERE collation LIKE '%latin1%'" | $MYSQL $DATABASE | cut -f1 | tail --lines=+2); do
  if [ ! -f "$i" ]; then
    mysqldump -u ${USERNAME} -p${PASSWORD} -h ${HOSTNAME} --opt --skip-set-charset --default-character-set=latin1 --skip-extended-insert ${DATABASE} --tables $i > $i;
  fi
done;

echo "DROP DATABASE IF EXISTS alloc;" | $MYSQL;
echo "CREATE DATABASE alloc;" | $MYSQL;

cd ../orig;

for i in *; do

  if [ ! -f ../done/$i ]; then
    cp $i ../tmp/$i
    perl -i -pe 's/DEFAULT CHARSET=latin1/DEFAULT CHARSET=utf8/' ../tmp/$i;
    echo "SET FOREIGN_KEY_CHECKS=0; SOURCE ../tmp/$i;" | $MYSQL $DATABASE;
    mv ../tmp/$i ../done/;
  fi
done;

