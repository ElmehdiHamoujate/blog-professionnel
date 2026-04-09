FROM php:8.2-cli

# Install required extensions
RUN apt-get update && apt-get install -y \
    libsqlite3-dev \
    libcurl4-openssl-dev \
    && docker-php-ext-install pdo pdo_sqlite curl \
    && apt-get clean && rm -rf /var/lib/apt/lists/*

WORKDIR /var/www/html

# Copy all project files
COPY . .

# Create db directory with write permissions
RUN mkdir -p /var/www/html/db && chmod -R 777 /var/www/html/db

EXPOSE 8080

# Use PHP built-in server — Railway injects $PORT automatically
CMD php -S 0.0.0.0:${PORT:-8080} -t /var/www/html /var/www/html/router.php
