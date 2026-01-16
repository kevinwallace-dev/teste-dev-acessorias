FROM php:8.2-apache

# Enable mysqli and bcmath (bc* functions are used in the app)
RUN docker-php-ext-install mysqli bcmath

COPY docker/php/php.ini /usr/local/etc/php/conf.d/custom.ini

WORKDIR /var/www/html