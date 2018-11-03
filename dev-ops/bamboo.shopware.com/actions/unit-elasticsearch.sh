#!/usr/bin/env bash

TEMPLATE: ./../../common/templates/config-elasticsearch.php:./../../../config.php

php bin/console sw:es:index:populate

vendor/bin/phpunit --config tests/phpunit.xml.dist --log-junit build/artifacts/test-log.xml --exclude-group=skipElasticSearch --group=elasticSearch
