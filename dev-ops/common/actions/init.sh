#!/usr/bin/env bash
#DESCRIPTION: initialization of your environment

sh ./dev-ops/common/actions/.init_composer.sh
INCLUDE: ./cache.sh

php bin/console sw:database:setup --steps=drop,create,import,setupShop --host=__SW_HOST__ --path=__SW_BASE_PATH__
php bin/console sw:database:setup --steps=importDemodata,setupShop --host=__SW_HOST__ --path=__SW_BASE_PATH__
php bin/console sw:snippets:to:db --include-plugins

php bin/console sw:theme:initialize

php bin/console sw:admin:create --name="Demo user" --email="demo@example.com" --username="demo" --password="demo" --locale="de_DE"

date +"%Y%m%d%H%M" | tee recovery/install/data/install.lock

php bin/console sw:firstrunwizard:disable

INCLUDE: ./.init_git-hooks.sh

INCLUDE: ./cache.sh
