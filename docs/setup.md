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

Open `.env` and configure your database:

```dotenv
DB_CONNECTION=mysql
DB_HOST=127.0.0.1
DB_PORT=3306
DB_DATABASE=lejeepneyadmin
DB_USERNAME=root
DB_PASSWORD=
```

### EmailJS Configuration (optional for local)

```dotenv
EMAILJS_PUBLIC_KEY=your_public_key
EMAILJS_SERVICE_ID=your_service_id
EMAILJS_TEMPLATE_ID=your_template_id
```

> These are only needed if you want to test email notifications locally.

---

## Step 4: Database Setup

Create the database first:

```sql
CREATE DATABASE lejeepneyadmin;
```

Then run migrations and seeders:

```bash
php artisan migrate
php artisan db:seed
```

This creates:

- Default admin user (check `DatabaseSeeder.php` for credentials)
- Sample landmarks (from `LandmarkSeeder.php`)

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

---

## Build for Production

To compile assets for production:

```bash
npm run build
```

This creates optimized, minified files in the `public/build/` directory.

---

## Common Issues

### Vite not loading styles/scripts

Make sure `npm run dev` is running. Without Vite dev server, `@vite()` directives won't resolve.

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

### Permission issues (Linux/Mac)

```bash
chmod -R 775 storage bootstrap/cache
```

---

## Useful Artisan Commands

| Command                            | Description                |
| ---------------------------------- | -------------------------- |
| `php artisan migrate`              | Run pending migrations     |
| `php artisan migrate:fresh --seed` | Reset database & re-seed   |
| `php artisan db:seed`              | Run seeders only           |
| `php artisan config:clear`         | Clear config cache         |
| `php artisan cache:clear`          | Clear application cache    |
| `php artisan route:list`           | List all registered routes |
| `php artisan tinker`               | Interactive PHP shell      |
