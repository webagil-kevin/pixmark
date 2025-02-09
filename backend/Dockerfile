FROM dunglas/frankenphp:builder-php8.3

ENV CADDY_ENABLE_QUIC=1
ENV CADDY_ENABLE_HTTP3=1

# Update packages and install system dependencies
RUN apt-get update && apt-get install -y \
    git \
    wget \
    curl \
    zip \
    unzip \
    libicu-dev \
    libzip-dev \
    libsqlite3-dev \
    ca-certificates \
    gnupg \
    lsb-release \
    && apt-get autoremove -y \
    && apt-get clean -y \
    && rm -rf /var/lib/apt/lists/* /tmp/* /var/tmp/*

# Install required PHP extensions
RUN install-php-extensions \
    bcmath \
    pdo \
    pdo_sqlite \
    gd \
    intl \
    zip \
    opcache

# Install Composer
RUN curl -sS https://getcomposer.org/installer | php -- \
    --install-dir=/usr/local/bin --filename=composer
RUN composer --version

# (Optional) Install Symfony CLI if needed
RUN wget -O - https://get.symfony.com/cli/installer | bash \
    && mv /root/.symfony*/bin/symfony /usr/local/bin/symfony

# Set environment variables
ARG APP_ENV=prod
ENV APP_ENV=${APP_ENV}

# Set working directory
WORKDIR /app

# Copy the entire source code into the container
COPY . /app

# Copy dependency files separately to optimize Composer cache
COPY composer.json composer.lock ./

# Install Composer dependencies based on the environment
RUN if [ "$APP_ENV" = "dev" ]; then \
        composer install --optimize-autoloader --no-interaction --no-progress; \
    else \
        composer install --no-dev --optimize-autoloader --no-interaction --no-progress; \
    fi

# Execute post-install scripts (e.g., cache:clear)
RUN composer run-script post-install-cmd

# Final cleanup
RUN apt-get autoremove -y && apt-get clean -y

# Expose ports
# If you configure TLS via your Caddyfile, port 443 will be used for HTTPS (HTTP/2 and HTTP/3)
# and port 80 for HTTP (HTTP/1). You can also expose another port if needed.
EXPOSE 80 443 8000

# Default command to start the application using FrankenPHP
CMD ["frankenphp", "php-server"]
