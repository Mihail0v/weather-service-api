.PHONY: help
help:
	@grep -E '^[a-zA-Z_-]+:.*?## .*$$' $(MAKEFILE_LIST) | sort | awk 'BEGIN {FS = ":.*?## "}; {printf "\033[36m%-30s\033[0m %s\n", $$1, $$2}'

.PHONY: build
build:
	docker compose build

.PHONY: up
up:
	docker compose up -d

.PHONY: down
down:
	docker compose down

.PHONY: restart
restart: down up

.PHONY: logs
logs:
	docker compose logs -f

.PHONY: shell
shell:
	docker compose exec php sh

.PHONY: install
install:
	docker compose exec php composer install

.PHONY: update
update:
	docker compose exec php composer update

.PHONY: test
test:
	docker compose exec php vendor/bin/phpunit

.PHONY: cs-check
cs-check: #
	docker compose exec php vendor/bin/php-cs-fixer fix --dry-run --diff --verbose

.PHONY: cs-fix
cs-fix:
	docker compose exec php vendor/bin/php-cs-fixer fix --verbose

.PHONY: migrate
migrate:
	docker compose exec php bin/console doctrine:migrations:migrate --no-interaction

.PHONY: cache-clear
cache-clear:
	docker compose exec php bin/console cache:clear

.PHONY: db-create
db-create:
	docker compose exec php bin/console doctrine:database:create --if-not-exists

.PHONY: db-drop
db-drop:
	docker compose exec php bin/console doctrine:database:drop --force --if-exists

.PHONY: db-reset
db-reset: db-drop db-create migrate

.PHONY: swagger-prod
swagger-prod:
	docker compose exec php sh bin/generate-swagger.sh --prod

.PHONY: setup
setup: build up install db-create migrate cache-warmup
