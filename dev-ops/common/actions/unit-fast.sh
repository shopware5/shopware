#!/usr/bin/env bash
#DESCRIPTION: execute unit tests without coverage

php vendor/bin/php-cs-fixer fix --dry-run --stop-on-violation --verbose --show-progress=dots

php vendor/bin/phpunit -c tests/phpunit.xml.dist --stop-on-failure --stop-on-error
