#!/usr/bin/env bash
#DESCRIPTION: Check the code for php-cs-fixer and php-stan issues

docker exec -u __USERKEY__ __APP_ID__ /usr/local/bin/wait-for-it.sh --timeout=120 mysql:3306

docker exec -u __USERKEY__ __APP_ID__ ant -f build/build.xml build-composer-install
docker exec -u __USERKEY__ __APP_ID__ ant -f build/build.xml build-cache-dir
docker exec -u __USERKEY__ __APP_ID__ ant -f build/build.xml build-config
docker exec -u __USERKEY__ __APP_ID__ ant -f build/build.xml build-database

docker exec -u __USERKEY__ __APP_ID__ bin/console sw:generate:attributes

docker exec -u __USERKEY__ __APP_ID__ sh dev-ops/bamboo.shopware.com/actions/.cs-fixer.sh "__PHP_VERSION__"
docker exec -u __USERKEY__ __APP_ID__ sh dev-ops/common/actions/.phpstan.sh "__PHP_VERSION__"