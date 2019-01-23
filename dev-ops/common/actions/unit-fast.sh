#!/usr/bin/env bash
#DESCRIPTION: execute unit tests without coverage

php vendor/bin/phpunit -c tests/phpunit.xml.dist --stop-on-failure --stop-on-error --exclude-group=elasticSearch
