FROM php:7.4-apache

RUN apt-get update && \
    apt-get install -y libpq-dev && \
    docker-php-ext-install pdo pdo_pgsql pgsql mysqli

COPY . /var/www/html/
