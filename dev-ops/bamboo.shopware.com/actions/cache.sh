#!/usr/bin/env bash

docker exec -u __USERKEY__ __APP_ID__ bin/console sw:cache:clear
