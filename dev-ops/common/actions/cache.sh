#!/usr/bin/env bash

bin/console cache:clear --no-warmup --no-optional-warmers
bin/console cache:warmup
