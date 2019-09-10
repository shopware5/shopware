#!/usr/bin/env bash

if [ "$1" = "7.3" ]; then
    if [ ! -e phpstan.phar ]; then
        curl -s -L "https://github.com/phpstan/phpstan/releases/download/0.11.12/phpstan.phar" > phpstan.phar
        chmod +x phpstan.phar
    fi

    ./phpstan.phar analyze -c .phpstan.neon --no-progress --error-format=table
fi
