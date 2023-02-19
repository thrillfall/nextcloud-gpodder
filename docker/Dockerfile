# https://hub.docker.com/_/nextcloud/
FROM nextcloud:25-apache

COPY entrypoint.sh /

RUN deluser www-data
RUN useradd -u 1000 -ms /bin/bash www-data
RUN usermod -a -G www-data www-data
RUN mkdir /var/www/deploy
