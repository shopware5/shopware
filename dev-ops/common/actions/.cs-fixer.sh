#!/usr/bin/env bash

if [ "$1" = "7.2" ]; then
    vendor/bin/php-cs-fixer fix --verbose --show-progress=dots
fi
