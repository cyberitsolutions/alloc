#!/bin/bash
set -euxo pipefail
# https://docs.docker.com/config/containers/multi-service_container/

# start apache
/usr/sbin/apachectl start

# start mariadb
/etc/init.d/mysql start

tail -f /var/log/apache2/error.log
