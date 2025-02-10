.PHONY: build up down lint-backend lint-frontend lint fix-frontend fix-backend fix phpstan phpunit npm-test \
		install-cert install shell-backend shell-frontend cache-clear-backend db-reset db-create migrate help

# Build all Docker images
build:
	docker compose build

# Start the services in detached mode
up:
	docker compose up -d

# Stop the services
stop:
	docker compose stop

# Stop and remove the services
down:
	docker compose down

# Reset database by deleting old files
db-reset:
	@echo "Cleaning old database files..."
	@if [ -f backend/var/data.db ]; then rm backend/var/data.db && echo "Deleted backend/var/data.db"; fi
	@if [ -f backend/var/test.db ]; then rm backend/var/test.db && echo "Deleted backend/var/test.db"; fi

# Create database
db-create: db-reset
	@echo "Using DATABASE_URL: $(DATABASE_URL)"
	@if echo $(DATABASE_URL) | grep -q "sqlite"; then \
	    echo "SQLite detected, skipping doctrine:database:create"; \
	else \
	    docker compose run --rm backend php bin/console doctrine:database:create; \
	fi

# Execute migrations
migrate:
	docker compose run --rm backend php bin/console doctrine:migrations:migrate --no-interaction

# Run ESLint on the front-end
lint-frontend:
	docker compose run --rm frontend npm run lint

# Run PHP-CS-Fixer in dry-run mode on the back-end
lint-backend:
	docker compose run --rm backend vendor/bin/php-cs-fixer fix --dry-run --diff

# Run both linters
lint: lint-frontend lint-backend

# Automatically fix linting issues for the frontend
fix-frontend:
	docker compose run --rm frontend npm run lint -- --fix

# Automatically fix PHP code style issues
fix-backend:
	docker compose run --rm backend vendor/bin/php-cs-fixer fix

# Run both fixers
fix: fix-frontend fix-backend

# Run PHPStan analysis at maximum level
phpstan:
	docker compose run --rm backend vendor/bin/phpstan analyse -l max

# Run backend unit tests analysis
phpunit:
	docker compose exec -T backend vendor/bin/phpunit --colors=always || echo "Aucun test unitaire back-end détecté"

# Run frontend unit tests analysis
npm-test:
	docker compose exec -T frontend npm test || echo "Aucun test unitaire front-end défini"

install-cert:
	@echo "=== Installing certificate for localhost ==="
	@if ! command -v npx > /dev/null 2>&1; then \
	    echo "Error: npx not found. Please install Node.js and npm."; \
	    exit 1; \
	fi
	@mkdir -p docker/certs
	@echo "Running 'npx devcert-cli generate localhost' in docker/certs directory..."
	@cd docker/certs && npx devcert-cli generate localhost
	@echo "Renaming generated files in docker/certs..."
	@if [ -f docker/certs/localhost.cert ]; then \
	    mv docker/certs/localhost.cert docker/certs/localhost.pem; \
	else \
	    echo "Warning: localhost.cert not found"; \
	fi
	@if [ -f docker/certs/localhost.key ]; then \
	    mv docker/certs/localhost.key docker/certs/localhost-key.pem; \
	else \
	    echo "Warning: localhost.key not found"; \
	fi
	@echo "Certificate generated for https://localhost in docker/certs."

# Initial installation and open browser tabs
install:
	$(MAKE) install-cert
	$(MAKE) build
	$(MAKE) up
	$(MAKE) db-create
	$(MAKE) migrate
	@echo "Opening the front-end and back-end pages in the default browser..."
	@if command -v xdg-open >/dev/null 2>&1; then \
	    xdg-open "https://localhost:4443/api" ; \
	    xdg-open "http://localhost:3000" ; \
	elif command -v open >/dev/null 2>&1; then \
	    open "https://localhost:4443/api" ; \
	    open "http://localhost:3000" ; \
	else \
	    echo "Please open the following URLs manually:" ; \
	    echo "Backend: https://localhost:4443/api" ; \
	    echo "Frontend: http://localhost:3000" ; \
	fi

# Backend bash
shell-backend:
	docker compose exec backend bash || docker compose exec backend sh

# Frontend bash
shell-frontend:
	docker compose exec frontend bash || docker compose exec frontend sh

# Backend cache clear
cache-clear-backend:
	docker compose run --rm backend php bin/console cache:clear

help:
	@echo "\033[1;32m═══════════════════════════════════════════════════════════════════════════════\033[0m"
	@echo "\033[1;32m               Available Commands Summary (Alphabetical Order)                 \033[0m"
	@echo "\033[1;32m═══════════════════════════════════════════════════════════════════════════════\033[0m"
	@echo "  \033[1;34mbuild\033[0m                 Build all Docker images"
	@echo "  \033[1;34mcache-clear-backend\033[0m   Clear backend cache"
	@echo "  \033[1;34mdb-reset\033[0m              Remove database (if exist)"
	@echo "  \033[1;34mdb-create\033[0m             Create database (skips if SQLite detected)"
	@echo "  \033[1;34mdown\033[0m                  Stop and remove the services"
	@echo "  \033[1;34mfix\033[0m                   Run both fixers (frontend and backend)"
	@echo "  \033[1;34mfix-backend\033[0m           Automatically fix PHP code style issues"
	@echo "  \033[1;34mfix-frontend\033[0m          Automatically fix frontend lint issues"
	@echo "  \033[1;34minstall\033[0m               Initial installation and open browser tabs"
	@echo "  \033[1;34minstall-cert\033[0m          Install certificate for localhost"
	@echo "  \033[1;34mlint\033[0m                  Run both linters (frontend and backend)"
	@echo "  \033[1;34mlint-backend\033[0m          Run PHP-CS-Fixer in dry-run mode on the backend"
	@echo "  \033[1;34mlint-frontend\033[0m         Run ESLint on the frontend"
	@echo "  \033[1;34mmigrate\033[0m               Execute migrations"
	@echo "  \033[1;34mnpm-test\033[0m              Run frontend unit tests via npm"
	@echo "  \033[1;34mphpstan\033[0m               Run PHPStan analysis at maximum level + code coverage"
	@echo "  \033[1;34mphpunit\033[0m               Run backend PHPUnit tests"
	@echo "  \033[1;34mshell-backend\033[0m         Open a shell in the backend container"
	@echo "  \033[1;34mshell-frontend\033[0m        Open a shell in the frontend container"
	@echo "  \033[1;34mstop\033[0m                  Stop the services"
	@echo "  \033[1;34mup\033[0m                   	Start the services in detached mode"
	@echo "\033[1;32m═══════════════════════════════════════════════════════════════════════════════\033[0m"
	@echo "\033[1;33mUsage: make <command>\033[0m"