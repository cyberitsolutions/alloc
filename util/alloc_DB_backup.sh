#!/bin/bash

BACKUP_DIR=/cyber/backup/alloc/alloc_new
TODAY=`date +%a`;

	if [ "${TODAY}" = "Sat" ]; then
  	TODAY=week`date +%U`;
	fi

BACKUP_FILE=$BACKUP_DIR/allocdump.sql.$TODAY

mysqldump -d -u alloc -pget1td0ne alloc > $BACKUP_FILE
mysqldump -t -u alloc -pget1td0ne alloc >> $BACKUP_FILE
gzip -f $BACKUP_FILE

