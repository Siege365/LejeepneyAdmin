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

# Configure Apache to listen on PORT environment variable (Railway/Render compatible)
# Default to 80, but will be overridden by $PORT in startup script
RUN sed -i 's/Listen 80/Listen ${PORT:-80}/' /etc/apache2/ports.conf

# Set production environment
ENV APP_ENV=production
ENV APP_DEBUG=false

# Create startup script
RUN printf '#!/bin/bash\n\
# Railway/Render compatible - use PORT env var\n\
export PORT=${PORT:-10000}\n\
export APP_ENV=production\n\
export APP_DEBUG=false\n\
\n\
# Configure Apache to listen on Railway PORT\n\
sed -i "s/Listen 80/Listen $PORT/" /etc/apache2/ports.conf\n\
sed -i "s/:80/:$PORT/" /etc/apache2/sites-available/*.conf\n\
\n\
# Remove Vite dev server marker\n\
rm -f /var/www/html/public/hot\n\
\n\
# Verify build assets exist\n\
if [ ! -f /var/www/html/public/build/manifest.json ]; then\n\
    echo "ERROR: Build manifest not found!"\n\
    exit 1\n\
fi\n\
\n\
echo "✓ Build assets verified"\n\
echo "✓ Listening on port: $PORT"\n\
echo "✓ APP_ENV: $APP_ENV"\n\
\n\
# Clear ALL caches\n\
php artisan optimize:clear\n\
\n\
# Run migrations and seeders\n\
php artisan migrate --force\n\
php artisan db:seed --force\n\
php artisan storage:link\n\
\n\
# Cache for production\n\
php artisan config:cache\n\
php artisan route:cache\n\
\n\
echo "✓ Laravel ready!"\n\
\n\
apache2-foreground\n' > /usr/local/bin/start.sh && chmod +x /usr/local/bin/start.sh

# Expose port (Railway auto-detects, but good practice)
EXPOSE ${PORT:-10000}

# Start with our script
CMD ["/usr/local/bin/start.sh"]
