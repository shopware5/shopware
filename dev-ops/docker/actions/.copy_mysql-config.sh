#!/usr/bin/env bash

if [ "$1" = "8" ]; then
    cp dev-ops/docker/templates/mysql-dev-8.cnf dev-ops/docker/containers/mysql/dev.cnf
else
    cp dev-ops/docker/templates/mysql-dev.cnf dev-ops/docker/containers/mysql/dev.cnf
fi
