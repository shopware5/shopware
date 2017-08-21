#!/usr/bin/env bash
#DESCRIPTION: build nexus for production and run assetic

npm install --prefix src/Nexus/Resources/nexus/
npm run --prefix src/Nexus/Resources/nexus/ build
bin/console assets:install
