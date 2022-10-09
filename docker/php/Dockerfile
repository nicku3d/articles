FROM php:8.1.11-apache
RUN a2enmod rewrite
RUN docker-php-ext-install mysqli && docker-php-ext-enable mysqli
RUN pecl install xdebug && docker-php-ext-enable xdebug
RUN apt-get update && apt-get upgrade -y