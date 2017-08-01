#!/usr/bin/env bash

mysql -u __DB_USER__ -p__DB_PASSWORD__ -h __DB_HOST__ -e "DROP DATABASE IF EXISTS __DB_NAME__"
mysql -u __DB_USER__ -p__DB_PASSWORD__ -h __DB_HOST__ -e "CREATE DATABASE __DB_NAME__"

mysql -u __DB_USER__ -p__DB_PASSWORD__ -h __DB_HOST__ shopware < _sql/install/latest.sql

__DOC_ROOT__/_sql/ApplyDeltas.php --migrationpath="__DOC_ROOT__/_sql/migrations/" --shoppath="__DOC_ROOT__" --mode=update

mysql -u __DB_USER__ -p__DB_PASSWORD__ -h __DB_HOST__ shopware < _sql/fixup.sql


