# 🚍 System Overview

A comprehensive guide explaining how the **LeJeepney** system works — covering both the **Admin Panel** (web) and the **Flutter Mobile App**, and how they interact through a shared Laravel backend.

---

## What is LeJeepney?

LeJeepney is a **jeepney route navigation and information system** for Davao City, Philippines. It helps commuters find jeepney routes between two points, discover landmarks, and get fare estimates — all through a mobile app. Behind the scenes, an admin panel lets administrators manage routes, landmarks, support tickets, and system settings.

---

## System Architecture

```
┌─────────────────────────────────┐
│        Flutter Mobile App       │
│    (Android / iOS / Web)        │
│                                 │
│  • Route Finder                 │
│  • Landmark Explorer            │
│  • Fare Calculator              │
│  • Support Tickets              │
│  • Recent Activity History      │
│  • In-App Notifications         │
└──────────┬──────────────────────┘
           │  REST API (JSON)
           │  Auth: Laravel Sanctum (Bearer Token)
           │  Rate Limit: 60 req/min
           │
┌──────────▼──────────────────────┐
│      Laravel Backend (API)      │
│      /api/v1/*                  │
│                                 │
│  • RouteApiController           │
│  • LandmarkApiController        │
│  • SupportTicketController      │
│  • TicketNotificationController │
│  • RecentActivityController     │
│  • SettingsController           │
│  • AuthController (API)         │
└──────────┬──────────────────────┘
           │
           │  Shared Database (MySQL)
           │
┌──────────▼──────────────────────┐
│      Laravel Backend (Web)      │
│      /dashboard, /routes, etc.  │
│                                 │
│  • RouteController              │
│  • LandmarkController           │
│  • CustomerServiceController    │
│  • AuditTrailController         │
│  • SettingsController           │
│  • NotificationController       │
│  • AccountSettingsController    │
│  • AuthController (Web)         │
└──────────┬──────────────────────┘
           │
┌──────────▼──────────────────────┐
│       Admin Panel (Browser)     │
│    Blade + Tailwind + Vite      │
│    Leaflet.js Maps              │
│    EmailJS (client-side email)  │
└─────────────────────────────────┘
```

---

## How Data Flows

### 1. Admin Creates a Route

1. Admin logs into the web panel at `/login`
2. Navigates to `/routes/create`
3. Fills in route details and draws the path on an interactive Leaflet.js map
4. The path coordinates (array of `{lat, lng}` objects) are saved as JSON
5. The system auto-calculates `total_distance` using the **Haversine formula**
6. An `ActivityLog` entry is created for audit purposes
7. The route is immediately available to the mobile app via `GET /api/v1/routes`

### 2. Mobile User Finds a Route

1. User opens the Flutter app and enters a starting point and destination
2. The app sends `POST /api/v1/routes/find` with origin/destination coordinates
3. The API iterates over all available routes and checks which ones pass near both points (within a configurable tolerance, default 0.5km)
4. For matching routes, it calculates:
    - **Boarding point** — closest point on the route to the origin
    - **Alighting point** — closest point on the route to the destination
    - **Walking distances** — from origin to boarding point, and from alighting point to destination
    - **Walk times** — estimated at ~5 km/h
    - **Ride distance** — along the actual route path between boarding and alighting
    - **Fare** — using the formula: base fare (₱13.00) for the first 4km + ₱1.80 per additional km
    - **Relevance score** — walking is penalized 3x vs riding for ranking
5. Results are sorted by relevance and returned to the app

### 3. Support Ticket Lifecycle

```
Mobile User creates ticket ──► Ticket saved (status: pending)
                                       │
                               TicketNotification created
                                       │
                              Admin sees ticket in panel
                                       │
                              Admin replies + optionally sends email
                                       │
                               TicketReply saved (sender_type: admin)
                               TicketNotification created (admin_message)
                               EmailJS sends branded email (if checked)
                                       │
                              Mobile User sees notification
                              Mobile User can reply (sender_type: customer)
                                       │
                              Admin updates status → resolved
                               TicketNotification created (resolved)
                                       │
                              Admin archives old ticket
```

### 4. Fare Settings Flow

1. Admin updates `base_fare` or `fare_per_km` at `/settings`
2. Values are stored in the `app_settings` table (key-value store)
3. Changes are logged in the audit trail with old → new values
4. The mobile app fetches current fare settings via `GET /api/v1/settings`
5. The route finder API uses these values when calculating fares

---

## Authentication Architecture

### Web (Admin Panel) — Session-Based

| Aspect                 | Details                                          |
| ---------------------- | ------------------------------------------------ |
| **Method**             | Laravel session-based auth with cookies          |
| **Guard**              | Default `web` guard                              |
| **Roles**              | Only `role = 'admin'` users can access the panel |
| **Middleware**         | `['auth', 'admin']` on all admin routes          |
| **Login limit**        | 5 attempts per minute (throttle)                 |
| **Session storage**    | Database (`sessions` table)                      |
| **Session encryption** | Enabled in production                            |

### API (Mobile App) — Token-Based

| Aspect               | Details                                                         |
| -------------------- | --------------------------------------------------------------- |
| **Method**           | Laravel Sanctum personal access tokens                          |
| **Token name**       | `mobile-app`                                                    |
| **Token expiry**     | 30 days (configurable via `SANCTUM_TOKEN_EXPIRATION`)           |
| **Login limit**      | 5 attempts per minute (throttle)                                |
| **API rate limit**   | 60 requests per minute on all `/api/v1/*` routes                |
| **Roles**            | Only `role = 'user'` can log in via API (admins blocked)        |
| **Public endpoints** | Routes, landmarks, settings, ticket creation — no auth required |

### Role Separation

The system enforces strict role separation:

- **Admin accounts** (`role = 'admin'`) can **only** log in via the web panel. The API login endpoint rejects admins with HTTP 403.
- **User accounts** (`role = 'user'`) can **only** use the API. The web login checks for admin role and logs out non-admin users.
- New admin accounts can only be created by existing admins (via `/register` behind auth middleware).
- The `role` field is excluded from mass assignment (`$fillable`) to prevent privilege escalation.

---

## Technology Stack

### Backend

| Component | Technology                 | Purpose                                |
| --------- | -------------------------- | -------------------------------------- |
| Framework | Laravel 12                 | PHP web framework                      |
| PHP       | 8.2+                       | Server-side language                   |
| Database  | MySQL 8.0+ / MariaDB 10.6+ | Relational data storage                |
| API Auth  | Laravel Sanctum            | Token-based authentication for mobile  |
| Web Auth  | Laravel built-in           | Session-based authentication for admin |
| ORM       | Eloquent                   | Database queries and relationships     |

### Admin Panel Frontend

| Component  | Technology                 | Purpose                                  |
| ---------- | -------------------------- | ---------------------------------------- |
| Templates  | Blade                      | Server-side rendered HTML                |
| CSS        | Tailwind CSS 4             | Utility-first styling                    |
| Build Tool | Vite 7                     | Asset bundling with HMR                  |
| Maps       | Leaflet.js + OpenStreetMap | Interactive route drawing and map picker |
| Email      | EmailJS                    | Client-side email notifications          |
| Icons      | Font Awesome               | UI icons                                 |

### Mobile App

| Component  | Technology            | Purpose                     |
| ---------- | --------------------- | --------------------------- |
| Framework  | Flutter               | Cross-platform mobile app   |
| API Client | HTTP/Dio              | REST API consumption        |
| Auth       | Sanctum tokens        | Bearer token authentication |
| Maps       | Google Maps / Leaflet | Route visualization         |

---

## Core Modules

### 1. Routes Management

Routes represent jeepney paths through the city. Each route has:

- **Route number** — unique identifier (e.g., "12A", "04L")
- **Path** — array of `{lat, lng}` coordinates defining the route line
- **Waypoints** — optional named stops along the route
- **Start/End points** — terminal names
- **Total distance** — auto-calculated from path coordinates via Haversine formula
- **Fare** — calculated using: `base_fare + (distance - 4km) × fare_per_km`
- **Status** — `available` or `unavailable` (controls visibility in the app)
- **Color** — hex color for map display (default: `#EBAF3E`)

### 2. Landmarks Management

Landmarks are points of interest that users can discover:

- **Categories** — city_center, mall, school, hospital, transport, other
- **Location** — lat/lng coordinates set via map picker
- **Featured** — highlighted landmarks shown prominently in the app
- **Gallery** — multiple images (uploaded files or external URLs)
- **Icon** — representative image (uploaded file or external URL)
- **Nearby search** — API endpoint finds landmarks within a radius using Haversine formula

### 3. Support Ticket System

A full-featured customer service system:

- **Ticket types** — general, technical, billing, feedback, complaint, bug, inquiry, suggestion, report, other
- **Priority levels** — low, medium, high, urgent
- **Statuses** — pending → in-progress → resolved (or cancelled)
- **Flagging** — mark important tickets
- **Archiving** — move old tickets out of the active view
- **Reply system** — conversation thread between admin and customer
- **Email notifications** — optional via EmailJS when admin replies
- **In-app notifications** — `TicketNotification` records pushed to the mobile app
- **Bulk actions** — archive, restore, flag, unflag, mark resolved for multiple tickets

### 4. Audit Trail

Every administrative action is logged:

- **Who** — user name, user ID
- **What** — action (created, updated, deleted), model type, model name
- **When** — timestamp
- **Where** — IP address
- **Changes** — JSON diff of old vs new values
- **Exportable** — filtered results can be downloaded as CSV

### 5. App Settings

Global configuration stored as key-value pairs:

| Setting       | Default | Description                            |
| ------------- | ------- | -------------------------------------- |
| `base_fare`   | 13.00   | Minimum fare for the first 4km (PHP)   |
| `fare_per_km` | 1.80    | Additional fare per km after 4km (PHP) |

- Settings marked `is_public = true` are exposed via `GET /api/v1/settings`
- The mobile app fetches these on startup to calculate fares with current rates
- All changes are logged in the audit trail

---

## Database Overview

The system uses 11 tables:

| Table                    | Purpose                                        |
| ------------------------ | ---------------------------------------------- |
| `users`                  | Admin and mobile app user accounts             |
| `jeepney_routes`         | Jeepney route data with paths and fares        |
| `landmarks`              | Points of interest with coordinates and images |
| `support_tickets`        | Customer support tickets                       |
| `ticket_replies`         | Conversation messages on tickets               |
| `ticket_notifications`   | In-app notifications for ticket events         |
| `activity_logs`          | Admin action audit trail                       |
| `recent_activities`      | Mobile user activity history                   |
| `app_settings`           | Key-value configuration store                  |
| `personal_access_tokens` | Sanctum API tokens                             |
| `sessions`               | Active user sessions                           |

See [database.md](database.md) for the complete schema reference.

---

## File Storage

- **Landmark icons** — stored in `storage/app/public/landmarks/` (accessed via `public/storage` symlink)
- **Gallery images** — stored in `storage/app/public/landmarks/gallery/`
- **Storage link** — `php artisan storage:link` creates the `public/storage` → `storage/app/public` symlink
- The API returns full URLs for images via `asset('storage/...')`
- When landmarks are deleted, associated image files are cleaned up from disk

---

## Environment Variables

Key `.env` values that control the system:

| Variable                   | Purpose                                                  |
| -------------------------- | -------------------------------------------------------- |
| `APP_ENV`                  | `local` or `production`                                  |
| `APP_DEBUG`                | Show detailed errors (must be `false` in production)     |
| `APP_URL`                  | Base URL for the application                             |
| `DB_*`                     | Database connection settings                             |
| `EMAILJS_PUBLIC_KEY`       | EmailJS API public key                                   |
| `EMAILJS_SERVICE_ID`       | EmailJS service identifier                               |
| `EMAILJS_TEMPLATE_ID`      | EmailJS template identifier                              |
| `SANCTUM_TOKEN_EXPIRATION` | API token lifetime in minutes (default: 43200 = 30 days) |
| `FRONTEND_URLS`            | Comma-separated allowed CORS origins                     |
| `SESSION_DRIVER`           | Session storage backend (`database` recommended)         |
| `SESSION_ENCRYPT`          | Encrypt session data (`true` in production)              |
| `SESSION_SECURE_COOKIE`    | HTTPS-only cookies (`true` in production)                |
