#!/bin/bash
DIR="$(cd "$(dirname "$0")" && pwd)"

echo "Clearing caches"
find $DIR -mindepth 1 -maxdepth 1 -type d -print0 | xargs -0 rm -R || true

