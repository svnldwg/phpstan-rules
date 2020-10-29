export PROJECT_NAME = phpstan-rules
export SHELL = bash

DOCKER_COMPOSE = docker-compose
CLI = $(DOCKER_COMPOSE) run -T ${PROJECT_NAME}
CLI_COMPOSER = $(DOCKER_COMPOSE) run -T ${PROJECT_NAME} php -d memory_limit=-1 /usr/bin/composer

start:
	$(DOCKER_COMPOSE) up -d
.PHONY: start

stop:
	$(DOCKER_COMPOSE) stop
.PHONY: stop

down:
	$(DOCKER_COMPOSE) down
.PHONY: down

ps:
	$(DOCKER_COMPOSE) ps
.PHONY: ps

cli:
	$(DOCKER_COMPOSE) exec ${PROJECT_NAME} $(SHELL)
.PHONY: cli

composer-install:
	$(CLI_COMPOSER) install --ansi
.PHONY: composer-install

composer-update:
	$(CLI_COMPOSER) update
.PHONY: composer-update

composer-dump-autoload:
	$(CLI_COMPOSER) dump-autoload -a
.PHONY: composer-dump-autoload

phpstan: ##@development run phpstan
	$(CLI) vendor/bin/phpstan analyze -c dev/phpstan.neon --memory-limit=1G
.PHONY: phpstan

php-cs-fixer: ##@development run php-cs-fixer
	$(CLI) ./vendor/bin/php-cs-fixer fix --config=dev/csfixer.php_cs
.PHONY: php-cs-fixer

tests:
	$(CLI) ./vendor/bin/phpunit --configuration=test/Integration/phpunit.xml
.PHONY: tests

check: php-cs-fixer phpstan tests
.PHONY: check