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
- [ ] ORS API key is obtained and configured
- [ ] Mail provider (SMTP) is configured

---

## Step 1: Server Requirements

| Requirement | Details                                                                                                   |
| ----------- | --------------------------------------------------------------------------------------------------------- |
| PHP         | 8.2+ with extensions: `pdo_mysql`, `mbstring`, `openssl`, `fileinfo`, `json`, `tokenizer`, `xml`, `ctype` |
| MySQL       | 8.0+ or MariaDB 10.6+                                                                                     |
| Web Server  | Apache or Nginx                                                                                           |
| Composer    | 2.x                                                                                                       |
| Node.js     | 18+ (for building assets, not needed at runtime)                                                          |
| SSL         | Required for secure cookies and HTTPS                                                                     |

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
cp .env.example .env
php artisan key:generate
```

### Critical `.env` Settings

```dotenv
# ⚠️ MUST change these for production
APP_ENV=production
APP_DEBUG=false                    # NEVER true in production
APP_URL=https://yourdomain.com    # Your actual domain

# Database — use strong credentials
DB_CONNECTION=mysql
DB_HOST=127.0.0.1
DB_PORT=3306
DB_DATABASE=lejeepneyadmin
DB_USERNAME=lejeepney_user        # NOT 'root'
DB_PASSWORD=strong_password_here  # Strong password required

# Session security
SESSION_DRIVER=database
SESSION_ENCRYPT=true              # Encrypt session data
SESSION_SECURE_COOKIE=true        # HTTPS-only cookies
SESSION_DOMAIN=yourdomain.com     # Your domain

# Cache
CACHE_STORE=database              # Used by walking route cache + rate limiters

# Logging
LOG_LEVEL=error                   # Only log errors in production

# Mail — server-side email for ticket replies + password resets
MAIL_MAILER=smtp
MAIL_HOST=smtp.example.com
MAIL_PORT=587
MAIL_USERNAME=your_smtp_user
MAIL_PASSWORD=your_smtp_password
MAIL_ENCRYPTION=tls
MAIL_FROM_ADDRESS=noreply@yourdomain.com
MAIL_FROM_NAME="LeJeepney"

# OpenRouteService — walking route directions
ORS_API_KEY=your_ors_api_key_here

# Sanctum — API token configuration
SANCTUM_STATEFUL_DOMAINS=yourdomain.com
```

---

## Step 4: Database

```bash
php artisan migrate --force
php artisan db:seed
```

> The `--force` flag is required for production migrations.

Seeders create: admin user, fare settings, sample routes/landmarks/tickets. You can also seed selectively:

```bash
php artisan db:seed --class=AdminUserSeeder
php artisan db:seed --class=AppSettingSeeder
```

---

## Step 5: Storage Link

Create the symbolic link for public file access (landmark images):

```bash
php artisan storage:link
```

This creates `public/storage` → `storage/app/public`.

---

## Step 6: Optimize Laravel

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

## Step 7: File Permissions

```bash
chown -R www-data:www-data /var/www/lejeepneyadmin
chmod -R 755 /var/www/lejeepneyadmin
chmod -R 775 storage bootstrap/cache
```

---

## Step 8: Web Server Configuration

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

    # Handle Laravel routing
    location / {
        try_files $uri $uri/ /index.php?$query_string;
    }

    # PHP-FPM
    location ~ \.php$ {
        fastcgi_pass unix:/var/run/php/php8.2-fpm.sock;
        fastcgi_param SCRIPT_FILENAME $realpath_root$fastcgi_script_name;
        include fastcgi_params;
    }

    # Block hidden files
    location ~ /\.(?!well-known).* {
        deny all;
    }

    # Cache static assets
    location ~* \.(css|js|png|jpg|jpeg|gif|ico|svg|woff|woff2|ttf)$ {
        expires 30d;
        add_header Cache-Control "public, immutable";
    }
}
```

### Apache (.htaccess is already included in `public/`)

Ensure `mod_rewrite` is enabled:

```bash
a2enmod rewrite
```

---

## Step 9: Create First Admin User

**Option A — Via Seeder:**

```bash
php artisan db:seed --class=AdminUserSeeder
```

**Option B — Via Tinker:**

```bash
php artisan tinker
```

```php
$user = App\Models\User::create([
    'name' => 'Admin',
    'email' => 'admin@yourdomain.com',
    'password' => Hash::make('your_secure_password'),
]);
$user->role = 'admin';
$user->save();
```

---

## Post-Deployment Verification

| Check                | URL / Command                                      |
| -------------------- | -------------------------------------------------- |
| Site loads           | `https://yourdomain.com`                           |
| Login works          | `https://yourdomain.com/login`                     |
| API responds         | `https://yourdomain.com/api/v1/routes`             |
| Settings API         | `https://yourdomain.com/api/v1/settings`           |
| Route finder         | `POST https://yourdomain.com/api/v1/routes/find`   |
| Walking route        | `POST https://yourdomain.com/api/v1/walking-route` |
| Password reset (API) | `POST https://yourdomain.com/api/password/forgot`  |
| Debug off            | Should NOT show stack traces on errors             |
| HTTPS enforced       | HTTP should redirect to HTTPS                      |
| Storage link         | Landmark images load correctly                     |
| Email delivery       | Test ticket reply email or password reset          |

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

---

## Monitoring

### Log Files

```bash
tail -f storage/logs/laravel.log
```

### Key Metrics to Monitor

| Metric                   | Where to Check               |
| ------------------------ | ---------------------------- |
| API response times       | Server access logs           |
| Walking route cache hits | `cache` table row count      |
| Failed email delivery    | `storage/logs/laravel.log`   |
| Rate limit hits (429s)   | Server access logs           |
| Database connections     | MySQL `SHOW PROCESSLIST`     |
| Storage disk usage       | `du -sh storage/app/public/` |
