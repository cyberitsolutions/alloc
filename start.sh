#!/bin/bash
set -euxo pipefail
# https://docs.docker.com/config/containers/multi-service_container/

# start apache
/usr/sbin/apachectl start

# start mariadb
/etc/init.d/mysql start

# wait a min, then check apache2 and mysqld are running
sleep 60
pidof apache2
pidof mysqld

# follow the apache error log
tail -f /var/log/apache2/error.log
