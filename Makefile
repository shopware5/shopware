ifeq ($(origin .RECIPEPREFIX), undefined)
  $(error This Make does not support .RECIPEPREFIX. Please use GNU Make 4.0 or later)
endif
.RECIPEPREFIX = >

SHELL := bash
.ONESHELL:
.SHELLFLAGS := -eu -o pipefail -c
.DELETE_ON_ERROR:
MAKEFLAGS += --warn-undefined-variables
MAKEFLAGS += --no-builtin-rules

ifeq ($(origin ENV_FILE), undefined)
    ENV_FILE:=.env
endif

-include ${ENV_FILE}

.PHONY: init clear-cache check-code mink elasticsearch-populate test-phpunit-elasticsearch unit-test-fast unit-test phpstan cs-fixer debug-config replace-config-variables clean-make-config clean

init: .make.init

clear-cache: .make.console.executable
> ./bin/console sw:cache:clear

check-code: check-phpstan check-php-cs-fixer

check-php-cs-fixer:
> ./vendor/bin/php-cs-fixer  fix --dry-run -v --allow-risky=yes --format=junit | tee php-cs-fixer.xml

check-phpstan:
> php -d memory_limit=4G ./vendor/bin/phpstan analyze -c .phpstan.neon --no-progress --error-format=table

prepare-mink: .make.config.mink replace-config-variables
> bin/console sw:rebuild:seo:index
> bin/console sw:theme:cache:generate
> bin/console dbal:run-sql 'UPDATE s_core_config_elements SET value = "b:0;" WHERE name = "show_cookie_note"'
> bin/console sw:cache:clear

test-mink: init .make.config.mink replace-config-variables
> ./vendor/bin/behat -vv --config=tests/Mink/behat.yml --format=pretty --out=std --format=junit --out=build/artifacts

test-phpunit: init
> ./vendor/bin/phpunit --config tests/phpunit_unit.xml.dist --log-junit build/artifacts/test-log.xml
> ./vendor/bin/phpunit --config tests/phpunit.xml.dist --log-junit build/artifacts/test-log.xml --exclude-group=elasticSearch

test-phpunit-elasticsearch: elasticsearch-populate
> ./vendor/bin/phpunit --config tests/phpunit.xml.dist --log-junit build/artifacts/test-log.xml --exclude-group=skipElasticSearch --group=elasticSearch

elasticsearch-populate: .make.config.elasticsearch replace-config-variables .make.console.executable
> php ./bin/console sw:es:index:populate
> php ./bin/console sw:es:backend:index:populate

debug-config: clean-make-config .make.config.debug replace-config-variables
> @echo "Debug configuration file generated"

config: clean-make-config .make.config replace-config-variables
> @echo "Default configuration file generated"

replace-config-variables:
> @if [ -z "${DB_USER}" ]; then echo "No or invalid database user supplied"; exit 1; fi
> @if [ -z "$(DB_PORT)" ]; then echo "No or invalid database port supplied"; exit 1; fi
> @if [ -z "$(DB_NAME)" ]; then echo "No or invalid database name supplied"; exit 1; fi
> @if [ -z "$(DB_HOST)" ]; then echo "No or invalid database host supplied"; exit 1; fi
> @if [ -z "$(DB_PASSWORD)" ]; then echo "No or invalid database password supplied"; exit 1; fi
> @sed -e 's/%db\.user%/${DB_USER}/g' -e 's/%db\.password%/${DB_PASSWORD}/g' -e 's/%db\.database%/${DB_NAME}/g' -e 's/%db\.host%/${DB_HOST}/g' -e 's/%db\.port%/${DB_PORT}/g' -e 's/%db\.port%/${DB_PORT}/g' -e 's/%elasticsearch\.host%/${ELASTICSEARCH_HOST}/g'  -i ./config.php
> cp build/behat.yml.dist tests/Mink/behat.yml
> @sed -e 's/%sw\.host%/${SW_HOST}/g' -e 's/%sw\.path%/${SW_BASE_PATH}/g' -i ./tests/Mink/behat.yml
> @echo "Replaced config variables"

clean-make-config:
> rm .make.config.* 2> /dev/null || true

clean:
> rm .make.* 2> /dev/null || true

.make.config:
> cp ./config.php.dist ./config.php
> touch .make.config.debug

.make.config.debug:
> cp ./build/config-debug.php ./config.php
> touch .make.config.debug

.make.config.mink:
> cp ./build/config-mink.php ./config.php
> touch .make.config.mink

.make.config.elasticsearch: clean-make-config
> cp ./build/config-elasticsearch.php config.php
> touch .make.config.elasticsearch

.make.install:
> @echo "Read additional variables from ${ENV_FILE}"

> composer install
> bin/console sw:database:setup --steps=drop,create,import,importDemodata
> bin/console sw:cache:clear
> bin/console sw:database:setup --steps=setupShop --shop-url=http://${SW_HOST}${SW_BASE_PATH}
> bin/console sw:snippets:to:db --include-plugins
> bin/console sw:theme:initialize
> bin/console sw:firstrunwizard:disable
> bin/console sw:admin:create --name="Demo" --email="demo@demo.de" --username="demo" --password="demo" --locale=de_DE -n
> touch recovery/install/data/install.lock

.make.console.executable:
> chmod u+x bin/console
> touch .make.console.executable

.make.init: clean-make-config .make.config replace-config-variables .make.install
> touch .make.init
