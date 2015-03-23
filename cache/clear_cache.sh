#!/bin/bash
DIR="$(cd "$(dirname "$0")" && pwd)"

## Guard against empty $DIR
if [[ "$DIR" != */cache ]]; then
    echo "Could not detect working directory."
    exit 1
fi

echo "Clearing caches"
find $DIR -mindepth 1 -maxdepth 1 -type d -print0 | xargs -0 rm -Rf
rm -f $DIR/../web/cache/*.js > /dev/null
rm -f $DIR/../web/cache/*.css
rm -f $DIR/../web/cache/*.txt

$DIR/../bin/console sw:generate:attributes
