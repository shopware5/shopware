#!/usr/bin/env bash

mkdir -p .git/hooks/
ln -sf build/gitHooks/pre-commit .git/hooks/pre-commit
