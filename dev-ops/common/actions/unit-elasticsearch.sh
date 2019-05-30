#!/usr/bin/env bash
#DESCRIPTION: execute unit tests with elasticsearch enabled

TEMPLATE: ./../templates/config-elasticsearch.php:./../../../config.php

php bin/console sw:es:index:populate
php bin/console sw:es:backend:index:populate

php vendor/bin/phpunit --config tests/phpunit.xml.dist --stop-on-failure --stop-on-error --exclude-group=skipElasticSearch --group=elasticSearch
