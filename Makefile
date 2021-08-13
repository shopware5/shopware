SHELL := bash
.ONESHELL:
.SHELLFLAGS := -eu -o pipefail -c
.DELETE_ON_ERROR:
MAKEFLAGS += --warn-undefined-variables
MAKEFLAGS += --no-builtin-rules

ifeq ($(origin ENV_FILE), undefined)
    ENV_FILE:=.env
endif

-include $(ENV_FILE)

.PHONY: init clear-cache check-code test-mink elasticsearch-populate test-phpunit test-phpunit-elasticsearch debug-config clean-make-config clean check-config-variables

init: .make.init

clean-setup:
	bash ./var/cache/clear_cache.sh
	rm -rf web/cache/*.js
	rm -rf web/cache/*.css
	rm -rf web/sitemap/shop*

clear-cache: .make.console.executable
	./bin/console sw:cache:clear

check-code: check-phpstan check-php-cs-fixer

check-php-cs-fixer:
	./vendor/bin/php-cs-fixer fix --dry-run -v
	./vendor/bin/php-cs-fixer fix --dry-run -v --config engine/Library/Enlight/.php-cs-fixer.php

fix-code-style:
	php -d memory_limit=-1 ./vendor/bin/php-cs-fixer fix -v
	php -d memory_limit=-1 ./vendor/bin/php-cs-fixer fix -v --config engine/Library/Enlight/.php-cs-fixer.php

check-phpstan:
	php -d memory_limit=4G ./vendor/bin/phpstan analyze -c .phpstan.neon --no-progress --error-format=table

prepare-mink: .make.config.build.mink
	./bin/console sw:rebuild:seo:index
	./bin/console sw:theme:cache:generate
	./bin/console dbal:run-sql 'UPDATE s_core_config_elements SET value = "b:0;" WHERE name = "show_cookie_note"'
	./bin/console sw:cache:clear

test-mink: init .make.config.build.mink
	./vendor/bin/behat -vv --config=tests/Mink/behat.yml --format=pretty --out=std --format=junit --out=build/artifacts

test-phpunit: init
	./vendor/bin/phpunit --config tests/phpunit_unit.xml.dist --log-junit build/artifacts/test-log.xml
	./vendor/bin/phpunit --config tests/phpunit.xml.dist --log-junit build/artifacts/test-log.xml --exclude-group=elasticSearch
	./vendor/bin/phpunit --config recovery/common/phpunit.xml.dist --log-junit build/artifacts/test-log.xml

test-phpunit-elasticsearch: elasticsearch-populate
	./vendor/bin/phpunit --config tests/phpunit.xml.dist --log-junit build/artifacts/test-log.xml --exclude-group=skipElasticSearch --group=elasticSearch

elasticsearch-populate: .make.config.build.elasticsearch .make.console.executable
	./bin/console sw:es:index:populate
	./bin/console sw:es:backend:index:populate

debug-config: clean-make-config .make.config.build.debug
	@echo "Debug configuration file generated"

config: clean-make-config .make.config
	@echo "Default configuration file generated"

check-config-variables:
	@if [ -z "$(DB_USER)" ]; then echo "No or invalid database user supplied"; exit 1; fi
	@if [ -z "$(DB_PORT)" ]; then echo "No or invalid database port supplied"; exit 1; fi
	@if [ -z "$(DB_NAME)" ]; then echo "No or invalid database name supplied"; exit 1; fi
	@if [ -z "$(DB_HOST)" ]; then echo "No or invalid database host supplied"; exit 1; fi
	@if [ -z "$(DB_PASSWORD)" ]; then echo "No or invalid database password supplied"; exit 1; fi

clean-make-config:
	rm .make.config.* 2> /dev/null || true

clean:
	rm .make.* 2> /dev/null || true

debug-config-test: .make.config.build.debug

.make.config: check-config-variables .make.config.behat
	@sed -e 's/%db\.user%/$(DB_USER)/g' -e 's/%db\.password%/$(DB_PASSWORD)/g' -e 's/%db\.database%/$(DB_NAME)/g' -e 's/%db\.host%/$(DB_HOST)/g' -e 's/%db\.port%/$(DB_PORT)/g' -e 's/%db\.port%/$(DB_PORT)/g' -e 's/%elasticsearch\.host%/$(ELASTICSEARCH_HOST)/g' < ./config.php.dist > ./config.php
	touch $@

.make.config.build.%: check-config-variables .make.config.behat
	@sed -e 's/%db\.user%/$(DB_USER)/g' -e 's/%db\.password%/$(DB_PASSWORD)/g' -e 's/%db\.database%/$(DB_NAME)/g' -e 's/%db\.host%/$(DB_HOST)/g' -e 's/%db\.port%/$(DB_PORT)/g' -e 's/%db\.port%/$(DB_PORT)/g' -e 's/%elasticsearch\.host%/$(ELASTICSEARCH_HOST)/g' < ./build/config-$*.php > ./config.php
	touch $@

.make.config.behat:
	@sed -e 's/%sw\.host%/$(SW_HOST)/g' -e 's|%sw\.path%|$(SW_BASE_PATH)|g' < ./build/behat.yml.dist > ./tests/Mink/behat.yml
	touch $@

.make.install:
	@echo "Read additional variables from $(ENV_FILE)"
	composer install
	composer install -d recovery/common
	./bin/console sw:database:setup --steps=drop,create,import,importDemodata
	./bin/console sw:cache:clear
	./bin/console sw:database:setup --steps=setupShop --shop-url=http://$(SW_HOST)$(SW_BASE_PATH)
	./bin/console sw:snippets:to:db --include-plugins
	./bin/console dbal:run-sql "INSERT INTO \`s_core_config_values\` (\`element_id\`, \`shop_id\`, \`value\`) VALUES ((SELECT \`id\` FROM \`s_core_config_elements\` WHERE \`name\` LIKE 'installationSurvey'), 1, 'b:0;');"
	./bin/console sw:firstrunwizard:disable
	./bin/console sw:theme:initialize
	./bin/console sw:admin:create --name="Demo" --email="demo@demo.de" --username="demo" --password="demo" --locale=de_DE -n
	touch recovery/install/data/install.lock

.git/hooks/pre-commit:
	mkdir -p .git/hooks
	ln -s ../../build/gitHooks/pre-commit $@

.make.init: clean-make-config .make.config .make.install .git/hooks/pre-commit

.make.console.executable:
	chmod u+x bin/console
	touch $@
