#!/usr/bin/env bash

vendor/bin/phpunit --config tests/phpunit.xml.dist --log-junit build/artifacts/test-log.xml --exclude-group=elasticSearch
