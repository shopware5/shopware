#!/usr/bin/env bash

sh dev-ops/bamboo.shopware.com/actions/.cs-fixer.sh "__PHP_VERSION__"
sh dev-ops/bamboo.shopware.com/actions/.phpstan.sh "__PHP_VERSION__"

vendor/bin/phpunit --config tests/phpunit.xml.dist --log-junit build/artifacts/test-log.xml
