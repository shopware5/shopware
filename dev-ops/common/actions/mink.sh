#!/usr/bin/env bash

vendor/bin/behat --config=tests/Mink/behat.yml --format=pretty --out=std --format=junit --out=build/artifacts/mink