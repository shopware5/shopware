#!/usr/bin/env bash

INCLUDE: ./.init_database.sh
INCLUDE: ./.init_composer.sh

bin/console cache:clear --no-warmup --no-optional-warmers
bin/console cache:warmup

