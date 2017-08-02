#!/usr/bin/env bash

I: mkdir .git/hooks
ln -s -r -f build/gitHooks/pre-commit .git/hooks/pre-commit
