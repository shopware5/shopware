#!/usr/bin/env bash

vendor/bin/phpunit tests/Functional/ --config tests/phpunit.xml.dist --log-junit build/artifacts/test-log.xml --exclude-group=elasticSearch
vendor/bin/phpunit tests/Unit/ --config tests/phpunit_unit.xml.dist --log-junit build/artifacts/test-log.xml
