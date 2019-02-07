#!/usr/bin/env bash

./vendor/bin/php-cs-fixer fix --dry-run --stop-on-violation --verbose --show-progress=dots
