#!/usr/bin/env bash

TEMPLATE: ./../templates/config-mink.php:./../../../config.php

cat config.php
cat tests/Mink/behat.yml

vendor/bin/behat -vv --config=tests/Mink/behat.yml --format=pretty --out=std --format=junit --out=build/artifacts/mink
