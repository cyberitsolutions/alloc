#!/bin/sh

# https://docs.docker.com/config/containers/multi-service_container/

# start apache
/usr/sbin/apachectl start
status=$?
if [ $status -ne 0 ]; then
    echo "failed to start apache: $status"
    exit $status
fi

# start mariadb
/etc/init.d/mysql start
status=$?
if [ $status -ne 0 ]; then
    echo "failed to start mariadb: $status"
    exit $status
fi

while sleep 60; do
    ps aux | grep apache |grep -q -v grep
    PROCESS_1_STATUS=$?
    ps aux | grep mysql |grep -q -v grep
    PROCESS_2_STATUS=$?
    # If the greps above find anything, they exit with 0 status
    # If they are not both 0, then something is wrong
    if [ $PROCESS_1_STATUS -ne 0 -o $PROCESS_2_STATUS -ne 0 ]; then
        echo "a process exited."
        exit 1
    fi
done
