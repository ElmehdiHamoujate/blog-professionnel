FROM php:8.2-apache

# Enable required extensions
RUN apt-get update && apt-get install -y \
    libsqlite3-dev \
    libcurl4-openssl-dev \
    && docker-php-ext-install pdo pdo_sqlite curl \
    && apt-get clean && rm -rf /var/lib/apt/lists/*

# Enable Apache mod_rewrite
RUN a2enmod rewrite

# Configure Apache to allow .htaccess overrides
RUN sed -i 's/AllowOverride None/AllowOverride All/g' /etc/apache2/apache2.conf

# Set the working directory
WORKDIR /var/www/html

# Copy all project files
COPY . .

# Create the db directory and set permissions
RUN mkdir -p /var/www/html/db \
    && chown -R www-data:www-data /var/www/html \
    && chmod -R 755 /var/www/html \
    && chmod -R 777 /var/www/html/db

# Railway injects PORT at runtime — configure Apache to use it
EXPOSE 8080

# Startup script: replace Apache's port with Railway's $PORT then start
CMD bash -c "sed -i \"s/Listen 80/Listen \${PORT:-8080}/g\" /etc/apache2/ports.conf && \
    sed -i \"s/<VirtualHost \*:80>/<VirtualHost *:\${PORT:-8080}>/g\" /etc/apache2/sites-enabled/000-default.conf && \
    apache2-foreground"
