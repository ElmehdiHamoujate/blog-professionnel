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

# Use Railway's dynamic PORT variable
ENV PORT=80
EXPOSE 80

# Start Apache in foreground
CMD ["apache2-foreground"]
