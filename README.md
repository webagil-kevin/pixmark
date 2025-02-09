# PixMark

## Overview

This project is a web application built with a Symfony (and API Platform) backend and a Node.js-based frontend. The project is containerized with Docker, using FrankenPHP as a lightweight PHP server and Caddy for TLS termination (supporting HTTP/2 and HTTP/3). The frontend is served in a separate container using an official Node.js image.

## Architecture

- **Backend:**  
  A Symfony application running on PHP 8.3 with FrankenPHP. The backend container is built from a custom Dockerfile based on `dunglas/frankenphp:builder-php8.3`, which installs necessary system dependencies and PHP extensions.
- **Frontend:**  
  A Node.js application running in its own container, exposing port 3000.
- **Reverse Proxy / TLS:**  
  Caddy is used (via FrankenPHP) to serve the backend over HTTPS using certificates generated with a Makefile command.

## Prerequisites

- [Docker](https://www.docker.com/) and [Docker Compose](https://docs.docker.com/compose/) installed on your system.
- GNU Make (commonly available on Unix-like systems).
- (Optional) Node.js and npm if you need to run frontend commands outside Docker.

## Getting Started

### 1. Clone the Repository

```bash
git clone https://github.com/webagil-kevin/pixmark.git
cd pixmark
```

### 2. Generate SSL Certificates
Generate the SSL certificate for localhost by running:

```bash
make install-cert
```
This command uses npx devcert-cli to generate a certificate and key for localhost, placing the files in ./docker/certs and renaming them as localhost.pem and localhost-key.pem.

### 3. Build the Docker Images
Build all images with:

```bash
make build
```

### 4. Start the Services
Launch the containers in detached mode:

```bash
make up
```

### 5. Access the Application

 - Backend (HTTP): http://localhost:8080
 - Backend (HTTPS): https://localhost:4443
 - Frontend: http://localhost:3000

## Development

The project uses a Makefile to simplify common tasks. Available commands include:

 - build: Build all Docker images.
 - up: Start the services in detached mode.
 - down: Stop and remove the services.
 - lint-frontend: Run ESLint on the frontend.
 - lint-backend: Run PHP-CS-Fixer (dry-run) on the backend.
 - lint: Run both frontend and backend linters.
 - fix: Automatically fix PHP code style issues.
 - phpstan: Run PHPStan analysis at maximum level.
 - install-cert: Generate SSL certificates for localhost.
 - You can run these commands from the root directory. For example:

```bash
make lint
make phpstan
```

## Testing
To ensure the quality of your code, run the tests and code quality checks using:

 - Backend Tests:

Execute PHPUnit tests inside the backend container.

 - Frontend Tests:

Run npm test within the frontend container.

You can also leverage the GitHub Actions CI workflow provided in the project to run these tests automatically on every push and pull request.

## Continuous Integration
A GitHub Actions workflow is set up to:

 - Build Docker images,
 - Start the services,
 - Run linters and static analysis,
 - Execute tests.

This CI configuration ensures that the project is tested automatically on each commit or pull request.

## Contributing
Contributions are welcome! Please open issues or submit pull requests with your changes. Make sure to follow the project's coding standards and that all tests pass.

## License
This project is licensed under the MIT License.

Contact
For any inquiries, please contact [kevin@webagil.com].