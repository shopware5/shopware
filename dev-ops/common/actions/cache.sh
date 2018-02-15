#!/usr/bin/env bash
#DESCRIPTION: clears all caches

I: SHOPWARE_ENV=dev bin/console sw:cache:clear
I: SHOPWARE_ENV=test bin/console sw:cache:clear
