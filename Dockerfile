FROM ubuntu:16.04

MAINTAINER "Roger Steneteg <roger@steneteg.org>"

ENV DEBIAN_FRONTEND noninteractive

RUN apt-get update && apt-get -y dist-upgrade

RUN apt-get -y install \
    php \
    php-cli \
    php-fpm \
    php-mysql \
    nginx \
    mariadb-server \
    vim

COPY . /var/www

WORKDIR /var/www
RUN chmod +x entrypoint.sh

EXPOSE 80

ENTRYPOINT ["./entrypoint.sh"]
