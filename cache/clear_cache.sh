#!/bin/bash
echo "Clearing all caches"

rm -rf html/*
rm -rf general/*
rm -rf templates/cache/*
rm -rf templates/compile/*

find proxies/ -name '*.php' -print0 | xargs -0 rm -f
find proxies/ -name '*.meta' -print0 | xargs -0 rm -f
find doctrine/filecache/ -name '*.php' -print0 | xargs -0 rm -f
find doctrine/proxies/ -name '*.php' -print0 | xargs -0 rm -f
find doctrine/attributes/ -name '*.php' -print0 | xargs -0 rm -f
