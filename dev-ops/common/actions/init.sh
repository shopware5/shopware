#!/usr/bin/env bash
#DESCRIPTION: initialization of your environment

cp /usr/local/bin/composer composer.phar
ant -f build/build.xml build-unit
