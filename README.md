# PixMark

![Temps de travail sur le projet](https://wakapi.webagil.com/api/badge/Webagil/interval:any/project:PixMark)


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

### 2. Install the project
```bash
make install
```

### 3. Access the Application
 - Backend (HTTP): http://localhost:8080/api
 - Backend (HTTPS): https://localhost:4443/api
 - Frontend: http://localhost:3000

## Development
The project uses a Makefile to simplify common tasks. Available commands include:

- **build**: Build all Docker images.
- **cache-clear-backend**: Clear the backend cache.
- **db-create**: Create the database (skips if SQLite is detected).
- **down**: Stop and remove the services.
- **fix**: Run both the frontend and backend fixers.
- **fix-backend**: Automatically fix PHP code style issues.
- **fix-frontend**: Automatically fix frontend linting issues.
- **help**: Display a summary of all available commands.
- **install**: Perform the initial installation – generate SSL certificates, build images, start services, create and migrate the database, and open browser tabs.
- **install-cert**: Generate SSL certificates for localhost.
- **lint**: Run both frontend and backend linters.
- **lint-backend**: Run PHP-CS-Fixer (dry-run) on the backend.
- **lint-frontend**: Run ESLint on the frontend.
- **migrate**: Execute Doctrine migrations.
- **npm-test**: Run frontend unit tests via npm.
- **phpstan**: Run PHPStan analysis at maximum level.
- **phpunit**: Run backend PHPUnit tests.
- **shell-backend**: Open a shell in the backend container.
- **shell-frontend**: Open a shell in the frontend container.
- **stop**: Stop the services.
- **up**: Start the services in detached mode.

You can run these commands from the root directory. For example:

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