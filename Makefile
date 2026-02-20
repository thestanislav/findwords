_OS := $(shell uname -s)
_IPS := 1

_UID := $(shell id -u)
_GID := $(shell id -g)

export _UID
export _GID

export CI_SWOOLE_APP_IMAGE=mezzio-swoole-app-dev
CI_ENVIRONMENT_NAME	?= dev
export CI_ENVIRONMENT_NAME

do = docker exec -t $(1)
# Load .env from project root for override substitution
ifneq ($(wildcard .env),)
COMPOSE_ENV		= --env-file .env
else
COMPOSE_ENV		=
endif
# Compose file set: prod vs dev (use CI_ENVIRONMENT_NAME=prod for prod)
ifeq ($(CI_ENVIRONMENT_NAME),prod)
COMPOSE_FILES		= -f docker/docker-compose.base.yml -f docker/docker-compose.prod.yml
COMPOSE_FILES_BUILD	= -f docker/docker-compose.base.yml -f docker/docker-compose.prod.yml
else
COMPOSE_FILES		= -f docker/docker-compose.base.yml -f docker/docker-compose.dev.yml
# Optional override for env; not used for build to avoid unset-var warnings
ifneq ($(wildcard docker/docker-compose.override.yml),)
COMPOSE_FILES		+= -f docker/docker-compose.override.yml
endif
COMPOSE_FILES_BUILD	= -f docker/docker-compose.base.yml -f docker/docker-compose.dev.yml
endif
doco_dev 		= docker compose -p mezzio-expras $(COMPOSE_ENV) $(COMPOSE_FILES) $(1)
doco_dev_build	= docker compose -p mezzio-expras $(COMPOSE_FILES_BUILD) $(1)

start:
	@echo -e '\e[1;31mGoing up...\e[0m'
	@$(call doco_dev, up -d)
	@echo -e '\e[1;31mDone\e[0m'

status:
	@$(call doco_dev, ps)

restart:
	@echo -e '\e[1;31mRestarting...\e[0m'
	@$(call doco_dev, restart)
	@echo -e '\e[1;31mDone\e[0m'

swoole-reload:
	@docker exec mezzio-swoole-app php vendor/bin/laminas mezzio:swoole:reload

swoole-status:
	@docker exec mezzio-swoole-app php vendor/bin/laminas mezzio:swoole:status

# DB schema: run in container (CI or manual). schema-update applies entity changes.
schema-update:
	@docker exec mezzio-swoole-app php vendor/bin/mezzio-sf-console orm:schema-tool:update --force --dump-sql --complete

schema-create:
	@docker exec mezzio-swoole-app php vendor/bin/mezzio-sf-console orm:schema-tool:create

fixtures-load:
	@docker exec mezzio-swoole-app php vendor/bin/mezzio-sf-console doctrine:fixtures:load --no-interaction

# Sync only the ExprAs packages that exist in packages/expras from /sites/expras.
# Add or remove dirs in packages/expras to control which packages are copied.
sync-expras:
	@mkdir -p packages/expras
	@if [ -d /sites/expras ]; then \
		for pkg in packages/expras/*/; do \
			name=$$(basename "$$pkg"); \
			if [ -d "/sites/expras/$$name" ]; then \
				echo -e '\e[1;31mSyncing' $$name '...\e[0m'; \
				rsync -a --delete "/sites/expras/$$name/" "packages/expras/$$name/"; \
			fi; \
		done; \
		echo -e '\e[1;31mDone\e[0m'; \
	else \
		echo -e '\e[1;33m/sites/expras not found; packages/expras unchanged\e[0m'; \
	fi

build: sync-expras
	@echo -e '\e[1;31mBuilding...\e[0m'
	@$(call doco_dev_build, build --pull --parallel)
	@echo -e '\e[1;31mDone\e[0m'

stop:
	@echo -e '\e[1;31mStopping...\e[0m'
	@$(call doco_dev, stop)
	@echo -e '\e[1;31mDone\e[0m'

destroy:
	@echo -e '\e[1;31mDestroying...\e[0m'
	@$(call doco_dev, down --remove-orphans)
	@echo -e '\e[1;31mDone\e[0m'
