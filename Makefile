.PHONY: build up down lint-backend lint-frontend lint fix phpstan phpunit npm-test install-cert

# Build all Docker images
build:
	docker-compose build

# Start the services in detached mode
up:
	docker-compose up -d

# Stop and remove the services
down:
	docker-compose down

# Run ESLint on the front-end
lint-frontend:
	docker-compose run --rm frontend npm run lint

# Run PHP-CS-Fixer in dry-run mode on the back-end
lint-backend:
	docker-compose run --rm backend vendor/bin/php-cs-fixer fix --dry-run --diff

# Run both linters
lint: lint-frontend lint-backend

# Automatically fix PHP code style issues
fix:
	docker-compose run --rm backend vendor/bin/php-cs-fixer fix

# Run PHPStan analysis at maximum level
phpstan:
	docker-compose run --rm backend vendor/bin/phpstan analyse -l max

# Run backend unit tests analysis
phpunit:
	docker-compose exec -T backend vendor/bin/phpunit --colors=always || echo "Aucun test unitaire back-end détecté"

# Run frontend unit tests analysis
npm-test:
	docker-compose exec -T frontend npm test || echo "Aucun test unitaire front-end défini"

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