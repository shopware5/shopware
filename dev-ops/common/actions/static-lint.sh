#!/usr/bin/env bash


find -L ./engine/Shopware/ -name '*.php' -print0 | xargs -0 -n 1 -P 4 php -l
