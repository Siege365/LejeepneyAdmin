# ⚙️ Local Development Setup

Step-by-step guide to get the LeJeepney Admin running on your local machine.

---

## Prerequisites

| Software | Version | Notes                                                          |
| -------- | ------- | -------------------------------------------------------------- |
| PHP      | 8.2+    | With `pdo_mysql`, `mbstring`, `openssl`, `fileinfo` extensions |
| Composer | 2.x     | [getcomposer.org](https://getcomposer.org/)                    |
| Node.js  | 18+     | [nodejs.org](https://nodejs.org/)                              |
| npm      | 9+      | Comes with Node.js                                             |
| MySQL    | 8.0+    | Or MariaDB 10.6+                                               |
| Git      | 2.x     | [git-scm.com](https://git-scm.com/)                            |

> **Recommended:** Use [Laragon](https://laragon.org/) on Windows — it bundles PHP, MySQL, Apache, and auto-configures `.test` domains.

---

## Step 1: Clone the Repository

```bash
git clone <repo-url> LejeepneyAdmin
cd LejeepneyAdmin
```

---

## Step 2: Install Dependencies

```bash
# PHP dependencies
composer install

# JavaScript dependencies
npm install
```

---

## Step 3: Environment Configuration

```bash
cp .env.example .env
php artisan key:generate
```

Open `.env` and configure:

### Database

```dotenv
DB_CONNECTION=mysql
DB_HOST=127.0.0.1
DB_PORT=3306
DB_DATABASE=lejeepneyadmin
DB_USERNAME=root
DB_PASSWORD=
```

### Mail (Server-Side Email)

Ticket reply notifications, admin password reset links, and mobile password reset codes are all sent server-side via Laravel Mail.

```dotenv
# For local development (emails logged to storage/logs/laravel.log)
MAIL_MAILER=log

# For production (use SMTP, Mailgun, SES, etc.)
MAIL_MAILER=smtp
MAIL_HOST=smtp.example.com
MAIL_PORT=587
MAIL_USERNAME=your_username
MAIL_PASSWORD=your_password
MAIL_ENCRYPTION=tls
MAIL_FROM_ADDRESS=noreply@lejeepney.com
MAIL_FROM_NAME="LeJeepney"
```

### OpenRouteService API Key (Required for Walking Routes)

The walking route proxy uses OpenRouteService (ORS) as the primary provider with OSRM as fallback. Get a free API key at [openrouteservice.org](https://openrouteservice.org/dev/#/signup):

```dotenv
ORS_API_KEY=your_ors_api_key_here
```

### Cache

```dotenv
CACHE_STORE=database
```

> The system uses database-backed caching for walking route results (1-hour TTL).

### Sanctum (API Tokens)

```dotenv
SANCTUM_STATEFUL_DOMAINS=localhost,localhost:8000,127.0.0.1,127.0.0.1:8000
```

Token expiration is configured to **30 days** in `config/sanctum.php`.

---

## Step 4: Database Setup

Create the database:

```sql
CREATE DATABASE lejeepneyadmin;
```

Run migrations and seeders:

```bash
php artisan migrate
php artisan db:seed
```

This creates:

- Default admin user — see `database/seeders/AdminUserSeeder.php` for credentials
- 50 jeepney routes with real Davao City coordinate paths — `JeepneyRouteSeeder`
- Sample landmarks — `LandmarkSeeder`
- Sample support tickets — `SupportTicketSeeder`
- Default fare settings (base_fare: ₱13.00, fare_per_km: ₱1.80) — `AppSettingSeeder`

---

## Step 5: Start Development Servers

You need **two terminal windows** running simultaneously:

### Terminal 1 — Laravel Backend

```bash
php artisan serve
```

Runs at `http://localhost:8000`

### Terminal 2 — Vite Dev Server (Hot Reload)

```bash
npm run dev
```

Runs at `http://localhost:5173` and provides hot module replacement (HMR).

> **Important:** Both servers must be running. Vite handles all CSS/JS hot reloading during development.

---

## Step 6: Access the Application

| URL                               | Description             |
| --------------------------------- | ----------------------- |
| `http://localhost:8000`           | Redirects to login      |
| `http://localhost:8000/login`     | Admin login page        |
| `http://localhost:8000/dashboard` | Dashboard (after login) |

### API Endpoints (for Flutter App)

| URL                                          | Description                    |
| -------------------------------------------- | ------------------------------ |
| `http://localhost:8000/api/v1/routes`        | List all jeepney routes        |
| `http://localhost:8000/api/v1/routes/find`   | Find routes between two points |
| `http://localhost:8000/api/v1/landmarks`     | List all landmarks             |
| `http://localhost:8000/api/v1/settings`      | Get fare settings              |
| `http://localhost:8000/api/v1/walking-route` | Get walking directions         |
| `http://localhost:8000/api/login`            | Mobile user authentication     |
| `http://localhost:8000/api/password/forgot`  | Request password reset code    |

---

## Build for Production

```bash
npm run build
```

Creates optimized, minified files in `public/build/`.

---

## Project Structure

```
app/
├── Http/Controllers/
│   ├── Admin/          # Admin panel (7 controllers)
│   ├── Api/            # Mobile API (Auth, Routes, Landmarks, PasswordReset)
│   │   └── V1/        # Versioned (Support, Notifications, Activities, Settings, Walking)
│   └── Auth/           # Web authentication
├── Mail/               # Mailables (TicketReplyMail, PasswordResetMail, ResetCodeMail)
├── Models/             # Eloquent models (9 models)
├── Services/           # Business logic (GeoService, RouteFinderService, WalkingRouteService)
└── Http/Middleware/     # AdminMiddleware (role gate)

resources/views/
├── admin/              # Admin panel Blade views
├── auth/               # Login, register, forgot/reset password views
├── emails/             # Email templates (ticket-reply, password-reset, reset-code)
├── components/         # Reusable Blade components
└── layouts/            # Layout templates (admin, auth)

routes/
├── web.php             # Admin panel + auth routes (~116 lines)
└── api.php             # Mobile API routes (~90 lines)
```

---

## Common Issues

### Vite WebSocket / HMR not connecting

The Vite config includes explicit HMR settings in `vite.config.js`:

```javascript
server: {
    host: 'localhost',
    hmr: { host: 'localhost' },
}
```

Restart `npm run dev` after any config changes.

### "Class not found" errors

```bash
composer dump-autoload
php artisan config:clear
php artisan cache:clear
```

### Migration errors

```bash
php artisan migrate:fresh --seed   # ⚠️ This resets ALL data
```

### Walking routes returning 503

Verify `ORS_API_KEY` is set in `.env`. The system falls back to OSRM (public, no key) if ORS fails, but both failing returns 503.

### Permission issues (Linux/Mac)

```bash
chmod -R 775 storage bootstrap/cache
```

---

## Useful Artisan Commands

| Command                             | Description                |
| ----------------------------------- | -------------------------- |
| `php artisan migrate`               | Run pending migrations     |
| `php artisan migrate:fresh --seed`  | Reset database & re-seed   |
| `php artisan db:seed`               | Run seeders only           |
| `php artisan config:clear`          | Clear config cache         |
| `php artisan cache:clear`           | Clear application cache    |
| `php artisan route:list`            | List all registered routes |
| `php artisan route:list --path=api` | List API routes only       |
| `php artisan tinker`                | Interactive PHP shell      |
