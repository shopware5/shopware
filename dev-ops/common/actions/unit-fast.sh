#!/usr/bin/env bash
#DESCRIPTION: execute unit tests without coverage

vendor/bin/phpunit tests/Functional/ --config tests/phpunit.xml.dist --log-junit build/artifacts/test-log.xml --exclude-group=elasticSearch
vendor/bin/phpunit tests/Unit/ --config tests/phpunit_unit.xml.dist --log-junit build/artifacts/test-log.xml
