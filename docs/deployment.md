# 🚀 Production Deployment Guide

Checklist and guide for deploying LeJeepney Admin to a production server.

---

## Pre-Deployment Checklist

- [ ] Server meets PHP 8.2+ requirements
- [ ] MySQL 8.0+ is installed and accessible
- [ ] SSL certificate is configured (HTTPS)
- [ ] Domain is pointed to the server
- [ ] `.env` is configured with production values
- [ ] Assets are built (`npm run build`)
- [ ] All migrations have been tested

---

## Step 1: Server Requirements

| Requirement | Details                                                                                                   |
| ----------- | --------------------------------------------------------------------------------------------------------- |
| PHP         | 8.2+ with extensions: `pdo_mysql`, `mbstring`, `openssl`, `fileinfo`, `json`, `tokenizer`, `xml`, `ctype` |
| MySQL       | 8.0+ or MariaDB 10.6+                                                                                     |
| Web Server  | Apache or Nginx                                                                                           |
| Composer    | 2.x                                                                                                       |
| Node.js     | 18+ (for building assets)                                                                                 |
| SSL         | Required for secure cookies                                                                               |

---

## Step 2: Deploy Code

```bash
git clone <repo-url> /var/www/lejeepneyadmin
cd /var/www/lejeepneyadmin

composer install --no-dev --optimize-autoloader
npm install
npm run build
```

---

## Step 3: Environment Configuration

```bash
cp .env.production .env
php artisan key:generate
```

### Critical `.env` Settings

```dotenv
# ⚠️ MUST change these for production
APP_ENV=production
APP_DEBUG=false                    # NEVER true in production
APP_URL=https://yourdomain.com    # Your actual domain

# Database - use strong credentials
DB_DATABASE=lejeepneyadmin
DB_USERNAME=lejeepney_user        # NOT 'root'
DB_PASSWORD=strong_password_here  # Strong password required

# Session security
SESSION_ENCRYPT=true              # Encrypt session data
SESSION_SECURE_COOKIE=true        # HTTPS-only cookies
SESSION_DOMAIN=yourdomain.com     # Your domain

# Logging
LOG_LEVEL=error                   # Only log errors

# EmailJS
EMAILJS_PUBLIC_KEY=your_key
EMAILJS_SERVICE_ID=your_service
EMAILJS_TEMPLATE_ID=your_template
```

> A complete template is available at `.env.production` in the project root.

---

## Step 4: Database

```bash
php artisan migrate --force
```

> The `--force` flag is required for production migrations.

---

## Step 5: Optimize Laravel

```bash
php artisan config:cache    # Cache configuration
php artisan route:cache     # Cache routes
php artisan view:cache      # Cache Blade templates
php artisan event:cache     # Cache events
```

To clear all caches:

```bash
php artisan optimize:clear
```

---

## Step 6: File Permissions

```bash
chown -R www-data:www-data /var/www/lejeepneyadmin
chmod -R 755 /var/www/lejeepneyadmin
chmod -R 775 storage bootstrap/cache
```

---

## Step 7: Web Server Configuration

### Nginx (Recommended)

```nginx
server {
    listen 80;
    listen 443 ssl;
    server_name yourdomain.com;
    root /var/www/lejeepneyadmin/public;

    index index.php;

    # SSL Configuration
    ssl_certificate /path/to/ssl/cert.pem;
    ssl_certificate_key /path/to/ssl/key.pem;

    # Redirect HTTP to HTTPS
    if ($scheme != "https") {
        return 301 https://$server_name$request_uri;
    }

    location / {
        try_files $uri $uri/ /index.php?$query_string;
    }

    location ~ \.php$ {
        fastcgi_pass unix:/var/run/php/php8.2-fpm.sock;
        fastcgi_param SCRIPT_FILENAME $realpath_root$fastcgi_script_name;
        include fastcgi_params;
    }

    location ~ /\.(?!well-known).* {
        deny all;
    }
}
```

### Apache (.htaccess is already included in `public/`)

Ensure `mod_rewrite` is enabled:

```bash
a2enmod rewrite
```

---

## Step 8: Create First Admin User

Option A — Via Tinker:

```bash
php artisan tinker
```

```php
$user = App\Models\User::create([
    'name' => 'Admin',
    'email' => 'admin@lejeepney.com',
    'password' => Hash::make('your_secure_password'),
]);
$user->role = 'admin';
$user->save();
```

Option B — Seed the database:

```bash
php artisan db:seed
```

---

## Post-Deployment Verification

| Check          | URL / Command                          |
| -------------- | -------------------------------------- |
| Site loads     | `https://yourdomain.com`               |
| Login works    | `https://yourdomain.com/login`         |
| API responds   | `https://yourdomain.com/api/v1/routes` |
| Debug off      | Should NOT show stack traces on errors |
| HTTPS enforced | HTTP should redirect to HTTPS          |

---

## Updating in Production

```bash
git pull origin main
composer install --no-dev --optimize-autoloader
npm install && npm run build
php artisan migrate --force
php artisan optimize
```

---

## Rollback

If a migration fails:

```bash
php artisan migrate:rollback
```

If the whole deploy needs reverting:

```bash
git revert HEAD
php artisan migrate:rollback
php artisan optimize
```
