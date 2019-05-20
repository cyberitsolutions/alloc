# the default php images from docker are werid...
FROM debian:stretch-slim

# install apache, php, and mariadb (mysql)
RUN apt-get update &&\
	apt-get -y upgrade &&\
	apt-get install -y apache2 php php-mysql php-mbstring php-gd mariadb-server make python vim

# rm any files/folders created by apcahe
RUN rm -rvf /var/www/html/*

# use older broken utf8 implementation, we need to fix this
RUN sed -i -e 's/character-set-server \+= utf8mb4/character-set-server = utf8/' -e 's/collation-server/#collation-server/' /etc/mysql/mariadb.conf.d/50-server.cnf

COPY . /var/www/html/
WORKDIR /var/www/html/

RUN mkdir -p /var/local/alloc/; chown www-data /var/local/alloc/
RUN cd /var/www/html/; make patches; make css

EXPOSE 80

ADD start.sh /
CMD ["/bin/bash", "/start.sh"]
