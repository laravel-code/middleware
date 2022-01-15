FROM php:8.0-fpm

RUN apt-get update && apt-get install supervisor git zip -y
RUN docker-php-ext-install pdo pdo_mysql
RUN pecl install -o -f redis \
  &&  rm -rf /tmp/pear \
  &&  docker-php-ext-enable redis

RUN curl --silent --show-error https://getcomposer.org/installer | php
RUN mv composer.phar /usr/sbin/composer

RUN pecl install xdebug \
    && docker-php-ext-enable xdebug

ARG USER_ID
ARG GROUP_ID

RUN addgroup --gid $GROUP_ID user
RUN adduser --disabled-password --gecos '' --uid $USER_ID --gid $GROUP_ID user
RUN adduser user root
RUN chmod g+w -R /var/log/supervisor

COPY ./conf /usr/local/etc/
