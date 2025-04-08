# Use official PHP image with Apache
FROM php:8.2-apache

# Install PostgreSQL client + dev tools
RUN apt-get update && apt-get install -y \
    libpq-dev \
    && docker-php-ext-install pgsql pdo_pgsql

# Enable Apache rewrite module if needed (optional)
RUN a2enmod rewrite

# Copy app files into Apache's document root
COPY . /var/www/html/

# Set correct file permissions (optional)
RUN chown -R www-data:www-data /var/www/html

# Start Apache server
CMD ["apache2-foreground"]
