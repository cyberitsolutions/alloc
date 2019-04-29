#!/bin/bash
set -euxo pipefail
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
    # check apache2 and mysqld are still running
    pidof apache2
    pidof mysqld
done
