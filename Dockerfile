FROM php:8.2-apache

RUN apt-get update && apt-get install -y 

RUN docker-php-ext-install mysqli pdo pdo_mysql

WORKDIR /var/www/html
