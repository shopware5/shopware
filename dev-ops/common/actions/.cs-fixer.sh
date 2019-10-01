#!/usr/bin/env bash

vendor/bin/php-cs-fixer  fix --dry-run -v --allow-risky=yes --format=junit | tee php-cs-fixer.xml
