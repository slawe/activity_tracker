FROM composer:2 AS composer

FROM php:8.3-fpm-alpine

RUN docker-php-ext-install pdo_mysql

COPY --from=composer /usr/bin/composer /usr/bin/composer

WORKDIR /var/www/html
