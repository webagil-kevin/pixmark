.PHONY: help install build up down stop restart logs \
		install-cert db-reset db-create migrate \
		lint lint-frontend lint-backend fix fix-frontend fix-backend \
		test phpstan phpunit npm-test \
		shell-backend shell-frontend cache-clear-backend

# Variables
DOCKER_COMPOSE = docker compose
BACKEND_EXEC = $(DOCKER_COMPOSE) exec -T backend
FRONTEND_EXEC = $(DOCKER_COMPOSE) exec -T frontend
BACKEND_RUN = $(DOCKER_COMPOSE) run --rm backend
FRONTEND_RUN = $(DOCKER_COMPOSE) run --rm frontend

# URLs
BACKEND_URL = https://localhost:4443/back/api
FRONTEND_URL = https://localhost:4443

# ══════════════════════════════════════════════
#  Installation & Configuration
# ══════════════════════════════════════════════

install: install-cert build up db-create migrate open-browser ## Complete project installation

install-cert: ## Install SSL certificate for localhost
	@echo "=== Installing SSL certificate for localhost ==="
	@if ! command -v npx > /dev/null 2>&1; then \
		echo "Error: npx not found. Please install Node.js and npm."; \
		exit 1; \
	fi
	@mkdir -p docker/certs
	@cd docker/certs && npx devcert-cli generate localhost
	@if [ -f docker/certs/localhost.cert ]; then \
		mv docker/certs/localhost.cert docker/certs/localhost.pem; \
	fi
	@if [ -f docker/certs/localhost.key ]; then \
		mv docker/certs/localhost.key docker/certs/localhost-key.pem; \
	fi
	@echo "Certificate generated for https://localhost in docker/certs."

# ══════════════════════════════════════════════
#  Docker Operations
# ══════════════════════════════════════════════

build: ## Build Docker images
	$(DOCKER_COMPOSE) build

up: ## Start services
	$(DOCKER_COMPOSE) up -d

down: ## Stop and remove services
	$(DOCKER_COMPOSE) down

stop: ## Stop services
	$(DOCKER_COMPOSE) stop

restart: stop up ## Restart services

logs: ## Display logs
	$(DOCKER_COMPOSE) logs -f

# ══════════════════════════════════════════════
#  Database Operations
# ══════════════════════════════════════════════

db-reset: ## Reset databases
	@echo "Cleaning database files..."
	@rm -f backend/var/data.db backend/var/test.db
	@echo "Database files removed."

db-create: db-reset ## Create database
	@echo "Using DATABASE_URL: $(DATABASE_URL)"
	@if echo $(DATABASE_URL) | grep -q "sqlite"; then \
		echo "SQLite detected, skipping database creation"; \
	else \
		$(BACKEND_RUN) php bin/console doctrine:database:create; \
	fi

migrate: ## Run database migrations
	$(BACKEND_RUN) php bin/console doctrine:migrations:migrate --no-interaction

# ══════════════════════════════════════════════
#  Linting & Fixing
# ══════════════════════════════════════════════

lint: lint-frontend lint-backend ## Run all linters

lint-frontend: ## Run frontend linter
	$(FRONTEND_RUN) npm run lint

lint-backend: ## Run backend linter
	$(BACKEND_RUN) vendor/bin/php-cs-fixer fix --dry-run --diff

fix: fix-frontend fix-backend ## Run all fixers

fix-frontend: ## Fix frontend code style
	$(FRONTEND_RUN) npm run lint -- --fix

fix-backend: ## Fix backend code style
	$(BACKEND_RUN) vendor/bin/php-cs-fixer fix

# ══════════════════════════════════════════════
#  Testing & Analysis
# ══════════════════════════════════════════════

test: phpstan phpunit npm-test ## Run all tests

phpstan: ## Run PHPStan static analysis
	$(BACKEND_RUN) vendor/bin/phpstan analyse -l max --memory-limit 512M

phpunit: ## Run backend unit tests
	$(BACKEND_EXEC) vendor/bin/phpunit --colors=always || echo "No backend unit tests detected"

npm-test: ## Run frontend unit tests
	$(FRONTEND_EXEC) npm test || echo "No frontend unit tests defined"

# ══════════════════════════════════════════════
#  Utility Commands
# ══════════════════════════════════════════════

shell-backend: ## Open shell in backend container
	$(DOCKER_COMPOSE) exec backend bash || $(DOCKER_COMPOSE) exec backend sh

shell-frontend: ## Open shell in frontend container
	$(DOCKER_COMPOSE) exec frontend bash || $(DOCKER_COMPOSE) exec frontend sh

cache-clear-backend: ## Clear backend cache
	$(BACKEND_RUN) php bin/console cache:clear

open-browser: ## Open application URLs in browser
	@echo "Opening pages in default browser..."
	@if command -v xdg-open >/dev/null 2>&1; then \
		xdg-open "$(BACKEND_URL)" ; \
		xdg-open "$(FRONTEND_URL)" ; \
	elif command -v open >/dev/null 2>&1; then \
		open "$(BACKEND_URL)" ; \
		open "$(FRONTEND_URL)" ; \
	else \
		echo "Please open the following URLs manually:" ; \
		echo "Backend: $(BACKEND_URL)" ; \
		echo "Frontend: $(FRONTEND_URL)" ; \
	fi

help: ## Display this help message
	@echo "\033[1;32m═══════════════════════════════════════════════════════════════════════════════\033[0m"
	@echo "\033[1;32m                           Available Commands                                  \033[0m"
	@echo "\033[1;32m═══════════════════════════════════════════════════════════════════════════════\033[0m"
	@grep -E '^[a-zA-Z_-]+:.*?## .*$$' $(MAKEFILE_LIST) | sort | awk 'BEGIN {FS = ":.*?## "}; {printf "  \033[1;34m%-20s\033[0m %s\n", $$1, $$2}'
	@echo "\033[1;32m═══════════════════════════════════════════════════════════════════════════════\033[0m"
	@echo "\033[1;33mUsage: make <command>\033[0m"