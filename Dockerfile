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

# Copy application files (including pre-built assets in public/build)
COPY . .

# Verify build assets were copied
RUN ls -la public/build/manifest.json && echo "✓ Build assets verified in container"

# Install PHP dependencies
RUN composer install --no-dev --optimize-autoloader --no-interaction

# Note: Frontend assets are pre-built locally and committed to git
# This avoids memory issues on Render's free tier where npm run build fails
# The public/build directory is included in the COPY above

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
RUN printf '#!/bin/bash\n\
# Ensure production mode (Render may set APP_ENV differently)\n\
export APP_ENV=production\n\
export APP_DEBUG=false\n\
\n\
# Remove Vite dev server marker if it exists\n\
rm -f /var/www/html/public/hot\n\
\n\
# Verify build assets exist\n\
if [ ! -f /var/www/html/public/build/manifest.json ]; then\n\
    echo "ERROR: Build manifest not found!"\n\
    ls -la /var/www/html/public/build/ || echo "public/build directory missing"\n\
    exit 1\n\
fi\n\
\n\
echo "✓ Build manifest found, assets ready"\n\
\n\
# Debug: Show environment and manifest location\n\
echo "APP_ENV is: $APP_ENV"\n\
ls -la /var/www/html/public/ | grep -E "hot|build"\n\
\n\
# Clear ALL caches (config, views, routes, events)\n\
php artisan optimize:clear\n\
\n\
# Run migrations and seeders\n\
php artisan migrate --force\n\
php artisan db:seed --force\n\
php artisan storage:link\n\
\n\
# Cache config and routes for performance (views will auto-compile with production assets)\n\
php artisan config:cache\n\
php artisan route:cache\n\
\n\
echo "✓ Laravel optimized for production"\n\
\n\
apache2-foreground\n' > /usr/local/bin/start.sh && chmod +x /usr/local/bin/start.sh

# Expose Render's default port
EXPOSE 10000

# Start with our script
CMD ["/usr/local/bin/start.sh"]
