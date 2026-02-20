FROM php:8.4-fpm-alpine

COPY php-uploads.ini /usr/local/etc/php/conf.d/99-uploads.ini

RUN apk add --no-cache \
    bash git unzip zip icu-dev oniguruma-dev libzip-dev mysql-client \
    && docker-php-ext-install pdo_mysql mbstring zip intl opcache

COPY --from=composer:2 /usr/bin/composer /usr/bin/composer

WORKDIR /var/www/html

COPY entrypoint.sh /entrypoint.sh
RUN chmod +x /entrypoint.sh

ENTRYPOINT ["/entrypoint.sh"]
CMD ["php-fpm"]
