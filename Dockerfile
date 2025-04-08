FROM php:8.2-apache

# Enable mod_rewrite if needed
RUN a2enmod rewrite

# Copy your project to Apache directory
COPY . /var/www/html/

# Set correct permissions
RUN chown -R www-data:www-data /var/www/html

# Expose port 80
EXPOSE 80
