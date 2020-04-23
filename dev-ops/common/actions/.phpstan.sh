#!/usr/bin/env bash

vendor/bin/phpstan analyze -c .phpstan.neon --no-progress --error-format=table --memory-limit=2G
