FROM php:8.0.16-cli-buster

WORKDIR "/app"

# Fixes some weird terminal issues such as broken clear / CTRL+L
ENV TERM=linux
# Ensure apt doesn't ask questions when installing stuff
ENV DEBIAN_FRONTEND=noninteractive

RUN apt-get update && \
    apt-get upgrade -y

#php-intl
RUN apt-get install -y \
    libicu-dev \
  && docker-php-ext-install intl
#php-xdebug
RUN pecl install xdebug-3.1.2 \
    && docker-php-ext-enable xdebug

#Composer
ENV COMPOSER_HOME=/usr/composer
RUN apt-get install -y \
        zip \
        unzip \
        git \
    && mkdir $COMPOSER_HOME \
    && curl -sS https://getcomposer.org/installer | php -- --install-dir=/usr/local/bin --filename=composer \
    && chmod -R 777 $COMPOSER_HOME

RUN apt-get install -y \
        software-properties-common \
        graphviz

#Clean container
RUN apt-get clean \
    && rm -rf /var/lib/apt/lists/* /tmp/* /var/tmp/* /usr/share/doc/* ~/.composer

RUN mkdir /tmp/php-sessions && chmod -R 777 /tmp
RUN mkdir /var/log/php && chmod -R 777 /var/log/php
