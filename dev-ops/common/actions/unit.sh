#!/usr/bin/env bash
#DESCRIPTION: execute unit tests

php vendor/bin/phpunit --config tests/phpunit.xml.dist --stop-on-failure --stop-on-error --exclude-group=elasticSearch
