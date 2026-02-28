# Use official PHP image with Apache
FROM php:8.2-apache

# Install system dependencies
RUN apt-get update && apt-get install -y \
    git \
    curl \
    libpng-dev \
    libonig-dev \
    libxml2-dev \
    zip \
    unzip \
    libpq-dev \
    && docker-php-ext-install pdo_mysql pdo_pgsql mbstring exif pcntl bcmath gd

# Install Composer
COPY --from=composer:latest /usr/bin/composer /usr/bin/composer

# Install Node.js
RUN curl -fsSL https://deb.nodesource.com/setup_20.x | bash - \
    && apt-get install -y nodejs

# Set working directory
WORKDIR /var/www/html

# Copy application files
COPY . .

# Install dependencies
RUN composer install --no-dev --optimize-autoloader --no-interaction

# Build frontend assets with error checking
RUN npm install

# Increase Node memory limit for Vite build (Render free tier has limited RAM)
ENV NODE_OPTIONS="--max-old-space-size=1024"

RUN npm run build || { echo "ERROR: npm run build failed!"; exit 1; }

# Verify build artifacts exist
RUN test -d public/build || { echo "ERROR: public/build directory not found!"; exit 1; }
RUN test -f public/build/manifest.json || { echo "ERROR: manifest.json not found!"; exit 1; }

# Cleanup
RUN rm -rf node_modules

# Set permissions
RUN chown -R www-data:www-data /var/www/html/storage /var/www/html/bootstrap/cache
RUN chmod -R 775 /var/www/html/storage /var/www/html/bootstrap/cache

# Enable Apache mod_rewrite
RUN a2enmod rewrite

# Configure Apache DocumentRoot
ENV APACHE_DOCUMENT_ROOT=/var/www/html/public
RUN sed -ri -e 's!/var/www/html!${APACHE_DOCUMENT_ROOT}!g' /etc/apache2/sites-available/*.conf
RUN sed -ri -e 's!/var/www/!${APACHE_DOCUMENT_ROOT}!g' /etc/apache2/apache2.conf /etc/apache2/conf-available/*.conf

# Configure Apache to listen on Render's port (10000)
RUN sed -i 's/Listen 80/Listen 10000/' /etc/apache2/ports.conf
RUN sed -i 's/:80/:10000/' /etc/apache2/sites-available/*.conf

# Set production environment
ENV APP_ENV=production
ENV APP_DEBUG=false

# Create startup script
RUN printf '#!/bin/bash\nphp artisan config:clear\nphp artisan migrate --force\nphp artisan db:seed --force\nphp artisan storage:link\napache2-foreground\n' > /usr/local/bin/start.sh && chmod +x /usr/local/bin/start.sh

# Expose Render's default port
EXPOSE 10000

# Start with our script
CMD ["/usr/local/bin/start.sh"]
