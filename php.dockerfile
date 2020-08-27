FROM php:7.4-fpm-alpine

WORKDIR /var/www/html

RUN apk add --no-cache libzip-dev zip mariadb sqlite-dev oniguruma-dev freetype-dev libjpeg-turbo-dev libpng-dev jpegoptim optipng pngquant gifsicle\
    && docker-php-ext-configure gd --with-freetype --with-jpeg \
    && docker-php-ext-install gd \
    && docker-php-ext-configure zip \
    docker-php-ext-enable
    && docker-php-ext-install pdo pdo_sqlite bcmath json mbstring opcache exif \
    && docker-php-ext-install mysqli pdo_mysql

RUN apk add --no-cache --update --virtual buildDeps autoconf \
     && pecl install xdebug \
     && docker-php-ext-enable xdebug \
     && apk del buildDeps