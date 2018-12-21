#!/usr/bin/env bash
#DESCRIPTION: Check the code for php-cs-fixer and php-stan issues

sh dev-ops/bamboo.shopware.com/actions/.cs-fixer.sh "__PHP_VERSION__"
sh dev-ops/common/actions/.phpstan.sh "__PHP_VERSION__"