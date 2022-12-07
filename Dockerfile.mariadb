FROM mariadb:latest

RUN sed -i -e 's/character-set-server \+= utf8mb4/character-set-server = utf8/' -e 's/collation-server/#collation-server/' /etc/mysql/mariadb.conf.d/50-server.cnf

# IMPORTANT: DON'T DO THIS IN PRODUCTION!!
ENV MARIADB_ALLOW_EMPTY_ROOT_PASSWORD yes

# alloc user and password, make sure you change the password!
ENV MARIADB_USER alloc
ENV MARIADB_PASSWORD changeme

RUN mkdir -p /installation/

RUN echo '\
UPDATE config SET value = "USD" WHERE name = "currency"; \n\
UPDATE currencyType SET currencyTypeActive = true, currencyTypeSeq = 1 WHERE currencyTypeID = "USD"; \n\
DELETE FROM exchangeRate; \n\
INSERT INTO exchangeRate (exchangeRateCreatedDate,exchangeRateCreatedTime,fromCurrency,toCurrency,exchangeRate) VALUES ("2020-03-23","2020-03-23 01:07:06","USD","USD",1); \n\
UPDATE config SET value = "http://localhost/" WHERE name = "allocURL"; \n\
UPDATE person SET password = "$2y$10$pyjwF/RYHZDMGgt19s6u8OUBWUAKyq7v9p1Ov.1Y5R/FvDKlcheWO" WHERE personID = 1; \n\
UPDATE person SET emailAddress = "test@example.com" WHERE personID = 1; \n\
UPDATE config SET value = "UTC" WHERE name = "allocTimezone";' > /installation/alloc-config.sql

RUN echo 'CREATE DATABASE alloc;' > /docker-entrypoint-initdb.d/000-alloc.sql && \
    echo 'GRANT ALL ON alloc.* TO alloc IDENTIFIED BY "changeme";' >> /docker-entrypoint-initdb.d/000-alloc.sql && \
    echo 'FLUSH PRIVILEGES;' >> /docker-entrypoint-initdb.d/000-alloc.sql && \
    echo 'USE alloc;' >> /docker-entrypoint-initdb.d/000-alloc.sql && \
    echo 'SOURCE /installation/db_structure.sql;' >> /docker-entrypoint-initdb.d/000-alloc.sql && \
    echo 'SOURCE /installation/db_data.sql;' >> /docker-entrypoint-initdb.d/000-alloc.sql && \
    echo 'SOURCE /installation/db_constraints.sql;' >> /docker-entrypoint-initdb.d/000-alloc.sql && \
    echo 'SOURCE /installation/db_triggers.sql;' >> /docker-entrypoint-initdb.d/000-alloc.sql && \
    echo 'SOURCE /installation/alloc-config.sql;' >> /docker-entrypoint-initdb.d/000-alloc.sql

ADD ./installation/ /installation/
