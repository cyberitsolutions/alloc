# the default php images from docker are werid...
FROM debian:buster-slim

# install apache, php, and mariadb (mysql)
RUN apt-get update && apt-get install --no-install-recommends -y \
    apache2 \
    php \
    php-mysql \
    php-mbstring \
    php-gd php-xml \
    mariadb-server \
    make \
    python \
    vim \
    mg \
    git \
    curl

# rm any files/folders created by apcahe
RUN rm -rvf /var/www/html/*

# use older broken utf8 implementation, we need to fix this
RUN sed -i -e 's/character-set-server \+= utf8mb4/character-set-server = utf8/' -e 's/collation-server/#collation-server/' /etc/mysql/mariadb.conf.d/50-server.cnf

ADD . /var/www/html/
WORKDIR /var/www/html/
RUN cd /var/www/html/; make patches; make css
RUN mkdir -p /var/local/alloc/; chown www-data /var/local/alloc/

RUN echo '\
UPDATE config SET value = "USD" WHERE name = "currency"; \n\
UPDATE currencyType SET currencyTypeActive = true, currencyTypeSeq = 1 WHERE currencyTypeID = "USD"; \n\
DELETE FROM exchangeRate; \n\
INSERT INTO exchangeRate (exchangeRateCreatedDate,exchangeRateCreatedTime,fromCurrency,toCurrency,exchangeRate) VALUES ("2020-03-23","2020-03-23 01:07:06","USD","USD",1); \n\
UPDATE config SET value = "http://localhost/" WHERE name = "allocURL"; \n\
UPDATE person SET password = "$2y$10$pyjwF/RYHZDMGgt19s6u8OUBWUAKyq7v9p1Ov.1Y5R/FvDKlcheWO" WHERE personID = 1; \n\
UPDATE person SET emailAddress = "test@example.com" WHERE personID = 1; \n\
UPDATE config SET value = "UTC" WHERE name = "allocTimezone";' > /var/local/alloc/db_config.sql
RUN cat /var/local/alloc/db_config.sql

RUN echo '<?php\n\
define("ALLOC_DB_NAME","alloc"); \n\
define("ALLOC_DB_USER","alloc"); \n\
define("ALLOC_DB_PASS","changeme"); \n\
define("ALLOC_DB_HOST","localhost"); \n\
define("ATTACHMENTS_DIR","/var/local/alloc/");' > /var/www/html/alloc_config.php
RUN cat /var/www/html/alloc_config.php

RUN /etc/init.d/mysql start && sleep 10 && pidof mysqld && \
  echo 'DROP DATABASE IF EXISTS alloc;'                        | mysql -u root && \
  echo 'CREATE DATABASE alloc;'                                | mysql -u root && \
  echo 'GRANT ALL ON alloc.* TO alloc@localhost IDENTIFIED BY "changeme";' | mysql -u root && \
  echo 'FLUSH PRIVILEGES;'                                     | mysql -u root && \
  echo 'SOURCE /var/www/html/installation/db_structure.sql;'   | mysql -u root alloc && \
  echo 'SOURCE /var/www/html/installation/db_patches.sql;'     | mysql -u root alloc && \
  echo 'SOURCE /var/www/html/installation/db_data.sql;'        | mysql -u root alloc && \
  echo 'SOURCE /var/www/html/installation/db_constraints.sql;' | mysql -u root alloc && \
  echo 'SOURCE /var/www/html/installation/db_triggers.sql;'    | mysql -u root alloc && \
  echo 'SOURCE /var/local/alloc/db_config.sql;'                | mysql -u root alloc

RUN for i in task client project invoice comment backups whatsnew wiki logos search tmp; do \
  mkdir /var/local/alloc/${i} && \
  chmod 775 /var/local/alloc/${i} && \
  chgrp www-data /var/local/alloc/${i}; \
done

# follow the apache error log
EXPOSE 80
ADD start.sh /
CMD ["/bin/bash", "/start.sh"]
