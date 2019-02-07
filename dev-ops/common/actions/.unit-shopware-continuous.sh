#!/usr/bin/env bash

cd tests && ./../vendor/bin/phpunit --config=phpunit_unit.xml.dist --exclude-group=elasticSearch
./vendor/bin/phpunit --log-junit=./build/logs/junit.xml --exclude-group=elasticSearch
