#!/usr/bin/env bash

docker exec __APP_ID__ /tmp/wait.sh
docker exec -u __USERKEY__ __APP_ID__ ./psh.phar bamboo:init
I: docker exec -u __USERKEY__ __APP_ID__ ./psh.phar bamboo:mink
docker exec -u __USERKEY__ __APP_ID__ sudo chown -R app-shell:app-shell .