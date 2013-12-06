#!/bin/bash
DIR="$(cd "$(dirname "$0")" && pwd)"

echo $DIR

echo "Clearing regular caches"
rm -rf $DIR/html/*
rm -rf $DIR/general/*
rm -rf $DIR/templates/*
rm -rf $DIR/proxies/*
rm -rf $DIR/doctrine/filecache/*
rm -rf $DIR/doctrine/proxies/*

if [[ $1 = "-f" ]] || [[ $1 = "--force" ]]; then
    echo "Clearing attributes"
    rm -rf $DIR/doctrine/attributes/*
fi
