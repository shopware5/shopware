#!/usr/bin/env bash

if [ "$1" = "7.2" ]; then
    if [ ! -e phpstan.phar ]; then
        curl -s -L "https://github.com/phpstan/phpstan/releases/download/0.11.15/phpstan.phar" > phpstan.phar
        chmod +x phpstan.phar
    fi

    ./phpstan.phar analyze -c .phpstan.neon --no-progress --error-format=table
fi
