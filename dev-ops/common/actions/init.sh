#!/usr/bin/env bash
#DESCRIPTION: initialization of your environment

TEMPLATE: ./../templates/config.php:./../../../config.php

composer install
./bin/console sw:database:setup --steps=drop,create,import,importDemodata

./bin/console sw:cache:clear
./bin/console sw:database:setup --steps=setupShop --shop-url=http://__SW_HOST____SW_BASE_PATH__
./bin/console sw:snippets:to:db --include-plugins
./bin/console sw:theme:initialize
./bin/console sw:firstrunwizard:disable
./bin/console sw:admin:create --name="Demo" --email="demo@demo.de" --username="demo" --password="demo" --locale=de_DE -n

touch recovery/install/data/install.lock
