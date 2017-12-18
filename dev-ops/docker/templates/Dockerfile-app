FROM webdevops/php-apache__CONTAINER_SUFFIX__:__PHP_VERSION__

RUN curl -sL https://deb.nodesource.com/setup_9.x | bash

# https://bugs.debian.org/cgi-bin/bugreport.cgi?bug=863199
RUN mkdir -p /usr/share/man/man1

RUN apt-get update && apt-get install -y ant nodejs

COPY php-config.ini /opt/docker/etc/php/php.ini

COPY wait-for-it.sh /usr/local/bin/
RUN chmod +x /usr/local/bin/wait-for-it.sh

ENV COMPOSER_CACHE_DIR=/.composer/cache
ENV WEB_DOCUMENT_ROOT=/app

WORKDIR /app
