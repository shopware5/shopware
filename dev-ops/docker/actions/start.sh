#!/usr/bin/env bash

echo "COMPOSE_PROJECT_NAME: ${COMPOSE_PROJECT_NAME}"

sh dev-ops/docker/actions/.copy_mysql-config.sh "__MYSQL_VERSION__"

docker-compose build && docker-compose up -d
wait

echo "All containers started successfully"
echo "Web server IP: http://__SW_HOST____SW_BASE_PATH__"
