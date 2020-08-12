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


.PHONY: init clear-cache check-code mink nightwatch-run nightwatch-setup elasticsearch-populate unit-test-elasticsearch unit-test-fast unit-test phpstan cs-fixer debug-config replace-config-variables clean-make-config clean

-include .env

init: .make.init

clear-cache: .make.console.executable
> ./bin/console sw:cache:clear

check-code: phpstan cs-fixer

# TODO we want that phpunit was installed from composer, but not init the complete shopware setup, maybe?
check-php-cs-fixer: init
> ./vendor/bin/php-cs-fixer  fix --dry-run -v --allow-risky=yes --format=junit | tee php-cs-fixer.xml

# TODO we want that phpunit was installed from composer, but not init the complete shopware setup, maybe?
check-phpstan: init
> php -d memory_limit=4G ./vendor/bin/phpstan analyze -c .phpstan.neon --no-progress --error-format=table

# TODO: untested
test-mink: init .make.config.mink replace-config-variables
> ./vendor/bin/behat -vv --config=tests/Mink/behat.yml --format=pretty --out=std --format=junit --out=build/artif

# TODO: untested
test-nightwatch: nightwatch-setup
> URL=http://${SW_HOST}${SW_BASE_PATH} npm run --prefix tests/nightwatch test

test-phpunit-fast:
> ./vendor/bin/phpunit tests/Functional/ --config tests/phpunit.xml.dist --log-junit build/artifacts/test-log.xml --exclude-group=elasticSearch
> ./vendor/bin/phpunit tests/Unit/ --config tests/phpunit_unit.xml.dist --log-junit build/artifacts/test-log.xml

# TODO untested
test-phpunit-elasticsearch: elasticsearch-populate
> ./vendor/bin/phpunit --config tests/phpunit.xml.dist --log-junit build/artifacts/test-log.xml --exclude-group=skipElasticSearch --group=elasticSearch

test-phpunit: init
> php ./vendor/bin/phpunit --config tests/phpunit.xml.dist --stop-on-failure --stop-on-error --exclude-group=elasticSearch

# TODO untested
elasticsearch-populate: .make.config.elasticsearch replace-config-variables .make.console.executable
> php ./bin/console sw:es:index:populate
> php ./bin/console sw:es:backend:index:populate

# TODO untested
nightwatch-setup: .make.nightwatch


debug-config: clean-make-config .make.config.debug replace-config-variables
> @echo "Debug configuration file generated"

config: clean-make-config .make.config replace-config-variables
> @echo "Default configuration file generated"


# TODO: Empty password does not work yet
replace-config-variables:
> @if [ -z "${DB_USER}" ]; then echo "No or invalid database user supplied"; exit 1; fi
> @if [ -z "$(DB_PORT)" ]; then echo "No or invalid database port supplied"; exit 1; fi
> @if [ -z "$(DB_NAME)" ]; then echo "No or invalid database name supplied"; exit 1; fi
> @if [ -z "$(DB_HOST)" ]; then echo "No or invalid database host supplied"; exit 1; fi
> @if [ -z "$(DB_PASSWORD)" ]; then echo "Warning: Using no database password"; fi
> @sed -e 's/%db\.user%/${DB_USER}/g' -e 's/%db\.password%/${DB_PASSWORD}/g' -e 's/%db\.database%/${DB_NAME}/g' -e 's/%db\.host%/${DB_HOST}/g' -e 's/%db\.port%/${DB_PORT}/g' -i ./config.php
> @echo "Replaced config variables"

# TODO: not optimal, since the user sees an error
clean-make-config:
> -rm .make.config.*

# TODO: not optimal, since the user sees an error
clean:
> -rm .make.*


.make.config:
> cp ./config.php.dist ./config.php
> touch .make.config.debug

.make.config.debug:
> cp ./dev-ops/common/templates/config-debug.php ./config.php
> touch .make.config.debug

# TODO: Missing, waiting for rebase of 5.7 branch
.make.config.mink:
> cp ./dev-ops/common/templates/config-mink.php ./config.php
> touch .make.config.mink

.make.config.elasticsearch: clean-make-config
> cp template/config-elasticsearch.php config.php
> touch .make.config.elasticsearch

.make.nightwatch:
> npm install --prefix tests/nightwatch

.make.init: clean-make-config .make.config replace-config-variables
> cp $$(which composer) composer.phar
> ant -f build/build.xml build-unit
> touch .make.init

.make.console.executable:
> chmod u+x bin/console
> touch .make.console.executable
