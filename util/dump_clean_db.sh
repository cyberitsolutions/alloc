#!/bin/bash

while [ -z "${db}" ]; do
  select db in alloc alloc_dev; do break; done;
done;

mysqldump -d -u root -p ${db} > db_structure.sql 



rm db_data.sql

tables="permission config taskType timeUnit projectPersonRole"
mysqldump -c -t -u root -p ${db} ${tables} > db_data.sql



echo "insert into person (username,password,personActive) values ('alloc','/.N0BifPoPoZg',1); " >> db_data.sql



