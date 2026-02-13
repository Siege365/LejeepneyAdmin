# 🚍 LeJeepney Admin

**Jeepney Route Management System — Admin Panel & API**

A Laravel-based web admin panel for managing jeepney routes, landmarks, and customer support tickets in Davao. Includes a REST API consumed by the **LeJeepney Flutter mobile app**.

---

## 📋 Table of Contents

- [Tech Stack](#tech-stack)
- [Features](#features)
- [Quick Start](#quick-start)
- [Project Structure](#project-structure)
- [Documentation](#documentation)
- [Team](#team)

---

## ⚙️ Tech Stack

| Layer      | Technology                          |
| ---------- | ----------------------------------- |
| Backend    | Laravel 12, PHP 8.2+                |
| Frontend   | Blade, Vite 7, Tailwind CSS 4       |
| Database   | MySQL                               |
| Auth (Web) | Laravel built-in (session-based)    |
| Auth (API) | Laravel Sanctum (token-based)       |
| Email      | EmailJS (client-side notifications) |
| Maps       | Leaflet.js / OpenStreetMap          |

---

## ✨ Features

### Admin Panel (Web)

- **Dashboard** — Overview with stats cards and recent activity logs
- **Routes Management** — CRUD for jeepney routes with interactive map drawing
- **Landmarks Management** — CRUD for landmarks with categories, gallery images, and map picker
- **Customer Service** — Support ticket system with replies, status management, flagging, archiving
- **Email Notifications** — EmailJS integration to notify customers on ticket replies
- **Account Settings** — Profile update, password change, account deletion
- **Notifications** — In-app notification system
- **Audit Trail** — Searchable, filterable activity log with CSV export
- **Settings / Configuration** — Manage fare settings (base fare, fare per km) exposed via API

### REST API (for Flutter App)

- **Routes** — List, search, find routes between points, get route paths
- **Landmarks** — List, filter by category, featured, nearby search
- **Support Tickets** — Create tickets, add messages, view notifications
- **Settings** — Fetch public app settings (base fare, fare per km)
- **Authentication** — Register, login, token-based auth via Sanctum

---

## 🚀 Quick Start

### Prerequisites

- PHP 8.2+
- Composer
- Node.js 18+ & npm
- MySQL
- [Laragon](https://laragon.org/) (recommended for Windows)

### Installation

```bash
# 1. Clone the repository
git clone <repo-url> LejeepneyAdmin
cd LejeepneyAdmin

# 2. Install PHP dependencies
composer install

# 3. Install JS dependencies
npm install

# 4. Setup environment
cp .env.example .env
php artisan key:generate

# 5. Configure database in .env
# DB_DATABASE=lejeepneyadmin
# DB_USERNAME=root
# DB_PASSWORD=

# 6. Run migrations & seeders
php artisan migrate
php artisan db:seed

# 7. Start development servers (two terminals)
php artisan serve        # Terminal 1: Laravel at http://localhost:8000
npm run dev              # Terminal 2: Vite HMR at http://localhost:5173
```

> 📖 See [docs/setup.md](docs/setup.md) for detailed setup instructions.

---

## 📁 Project Structure

```
LejeepneyAdmin/
├── app/
│   ├── Http/Controllers/
│   │   ├── Admin/           # Web admin controllers
│   │   ├── Api/             # API controllers (Flutter app)
│   │   └── Auth/            # Authentication
│   ├── Models/              # Eloquent models
│   └── Providers/
├── config/                  # App configuration
├── database/
│   ├── migrations/          # Database schema
│   └── seeders/             # Sample data
├── docs/                    # 📖 Team documentation
├── resources/
│   ├── css/                 # Stylesheets (Vite-processed)
│   │   ├── admin-bundle.css # Master CSS import
│   │   ├── components/      # Reusable component styles
│   │   └── pages/           # Page-specific styles
│   ├── js/                  # JavaScript (Vite-processed)
│   │   ├── admin-bundle.js  # Master JS import
│   │   ├── components/      # Reusable components (toast, modal)
│   │   └── pages/           # Page-specific scripts
│   └── views/               # Blade templates
│       ├── admin/           # Admin page views
│       ├── auth/            # Login/register views
│       ├── components/      # Blade components
│       └── layouts/         # Layout templates
├── routes/
│   ├── web.php              # Admin panel routes
│   └── api.php              # REST API routes
├── public/                  # Public assets
└── vite.config.js           # Vite build config
```

---

## 📖 Documentation

Detailed documentation is in the [`docs/`](docs/) folder:

| Document                                                 | Description                                    |
| -------------------------------------------------------- | ---------------------------------------------- |
| [System Overview](docs/system-overview.md)               | Architecture, data flows, and how it all works |
| [Mobile App Integration](docs/mobile-app-integration.md) | Flutter app API guide with examples            |
| [Setup Guide](docs/setup.md)                             | Local development environment setup            |
| [Deployment Guide](docs/deployment.md)                   | Production deployment & environment checklist  |
| [API Reference](docs/api-reference.md)                   | All REST API endpoints with params & examples  |
| [Admin Panel Guide](docs/admin-panel.md)                 | Features, modules, and usage guide             |
| [Database Schema](docs/database.md)                      | Models, tables, relationships                  |
| [Security](docs/security.md)                             | Security measures & best practices             |
| [EmailJS Integration](docs/emailjs.md)                   | Email notification setup & configuration       |

---

## 👥 Team

LeJeepney Admin is developed and maintained by the LeJeepney team.

---

## 📄 License

This project is proprietary software. All rights reserved.
