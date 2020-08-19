FROM php:7.4-fpm-alpine

WORKDIR /var/www/html

RUN apk add --no-cache libzip-dev zip sqlite-dev oniguruma-dev freetype-dev libjpeg-turbo-dev libpng-dev \
    && docker-php-ext-configure gd --with-freetype --with-jpeg \
    && docker-php-ext-install gd \
    && docker-php-ext-configure zip \
    && docker-php-ext-install bcmath pdo pdo_mysql json mbstring opcache pdo_sqlite