#!/usr/bin/env bash

vendor/bin/php-cs-fixer fix --dry-run --stop-on-violation --verbose --show-progress=dots

vendor/bin/phpunit --config tests/phpunit.xml.dist --log-junit build/artifacts/test-log.xml