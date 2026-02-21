# 🗄️ Database Schema

Complete reference of all database tables, columns, and relationships.

---

## Entity Relationship Overview

```
users ──────────── activity_logs
  │
  ├── support_tickets ──── ticket_replies
  │         │
  │         └── ticket_notifications
  │
  ├── recent_activities
  │
  └── personal_access_tokens (Sanctum)

jeepney_routes ←── app_settings (fare calculation)
landmarks (standalone)
password_reset_tokens (shared by web + API reset flows)
```

---

## Tables

### `users`

| Column            | Type                  | Attributes         |
| ----------------- | --------------------- | ------------------ |
| id                | bigint unsigned       | PK, auto-increment |
| name              | string                | required           |
| email             | string                | unique, required   |
| role              | enum(`admin`, `user`) | default: `user`    |
| phone             | string                | nullable           |
| email_verified_at | timestamp             | nullable           |
| password          | string                | hashed (bcrypt)    |
| remember_token    | string(100)           | nullable           |
| created_at        | timestamp             |                    |
| updated_at        | timestamp             |                    |

**Roles:**

- `admin` — Can access the web admin panel. Cannot log in via the mobile API.
- `user` — Can log in via the mobile API. Cannot access the admin panel.

---

### `jeepney_routes`

| Column         | Type                             | Attributes                    |
| -------------- | -------------------------------- | ----------------------------- |
| id             | bigint unsigned                  | PK, auto-increment            |
| route_number   | string(50)                       | unique, indexed               |
| name           | string                           | required                      |
| path           | json                             | Array of `[lat, lng]` pairs   |
| waypoints      | json                             | nullable, named stops         |
| start_point    | string                           | nullable                      |
| end_point      | string                           | nullable                      |
| terminal       | string                           | nullable                      |
| total_distance | decimal(8,2)                     | nullable, in km               |
| estimated_time | integer                          | nullable, in minutes          |
| fare           | decimal(8,2)                     | nullable, in PHP currency     |
| status         | enum(`available`, `unavailable`) | default: `available`, indexed |
| color          | string(7)                        | default: `#EBAF3E`, hex code  |
| description    | text                             | nullable                      |
| created_at     | timestamp                        |                               |
| updated_at     | timestamp                        |                               |

**Indexes:** `route_number` (unique), `status`

**Model Constants:** `BASE_DISTANCE_KM = 4` (first 4 km included in base fare)

**Model Methods:**

- `calculateFare($distance)` — Uses `AppSetting` values (`base_fare`, `fare_per_km`) for dynamic fare calculation
- `calculateDistance()` — Sums Haversine distances along path coordinates
- `isPointNearRoute($lat, $lng, $tolerance)` — Spatial proximity check
- `findClosestPoint($lat, $lng)` — Returns closest path point with index

---

### `landmarks`

| Column         | Type            | Attributes                 |
| -------------- | --------------- | -------------------------- |
| id             | bigint unsigned | PK, auto-increment         |
| name           | string          | required                   |
| latitude       | decimal(10,8)   | required                   |
| longitude      | decimal(11,8)   | required                   |
| description    | text            | nullable                   |
| icon_image     | string          | nullable, file path or URL |
| gallery_images | json            | nullable, array of paths   |
| category       | string(100)     | nullable, indexed          |
| is_featured    | boolean         | default: false, indexed    |
| created_at     | timestamp       |                            |
| updated_at     | timestamp       |                            |

**Indexes:** `category`, `is_featured`, (`latitude`, `longitude`)

**Categories (defined in model):** `city_center`, `mall`, `school`, `hospital`, `transport`, `other`

---

### `support_tickets`

| Column      | Type                                                    | Attributes              |
| ----------- | ------------------------------------------------------- | ----------------------- |
| id          | bigint unsigned                                         | PK, auto-increment      |
| user_id     | bigint unsigned                                         | nullable, FK → users    |
| admin_id    | bigint unsigned                                         | nullable, FK → users    |
| subject     | string                                                  | required                |
| message     | text                                                    | required                |
| name        | string                                                  | customer display name   |
| email       | string                                                  | customer email, indexed |
| type        | enum (see below)                                        | default: `general`      |
| priority    | enum(`low`, `medium`, `high`, `urgent`)                 | default: `medium`       |
| status      | enum(`pending`, `in-progress`, `resolved`, `cancelled`) | default: `pending`      |
| is_flagged  | boolean                                                 | default: false          |
| is_archived | boolean                                                 | default: false          |
| archived_at | timestamp                                               | nullable                |
| created_at  | timestamp                                               |                         |
| updated_at  | timestamp                                               |                         |

**Type values:** `general`, `technical`, `billing`, `feedback`, `complaint`, `bug`, `inquiry`, `suggestion`, `report`, `other`

**Indexes:** `status`, `type`, `priority`, `is_flagged`, `is_archived`

**Relationships:** `replies()` → hasMany TicketReply, `notifications()` → hasMany TicketNotification, `user()` → belongsTo User, `admin()` → belongsTo User

---

### `ticket_replies`

| Column            | Type                      | Attributes                            |
| ----------------- | ------------------------- | ------------------------------------- |
| id                | bigint unsigned           | PK, auto-increment                    |
| support_ticket_id | bigint unsigned           | FK → support_tickets (cascade delete) |
| sender_type       | enum(`admin`, `customer`) | default: `admin`                      |
| admin_id          | bigint unsigned           | nullable, FK → users                  |
| user_id           | bigint unsigned           | nullable, FK → users (set null)       |
| sender_name       | string                    | nullable                              |
| message           | text                      | required                              |
| admin_name        | string                    | nullable, default: `Admin Support`    |
| email_sent        | boolean                   | default: false                        |
| created_at        | timestamp                 |                                       |
| updated_at        | timestamp                 |                                       |

**Relationships:** `ticket()` → belongsTo SupportTicket, `admin()` → belongsTo User, `user()` → belongsTo User

---

### `ticket_notifications`

| Column     | Type            | Attributes                                                          |
| ---------- | --------------- | ------------------------------------------------------------------- |
| id         | bigint unsigned | PK, auto-increment                                                  |
| ticket_id  | bigint unsigned | FK → support_tickets (cascade delete)                               |
| user_email | string          | indexed                                                             |
| event_type | enum            | `created`, `replied`, `status_changed`, `resolved`, `admin_message` |
| title      | string          | notification title                                                  |
| message    | text            | notification body                                                   |
| metadata   | json            | nullable (old_status, new_status, admin_name, etc.)                 |
| is_read    | boolean         | default: false                                                      |
| read_at    | timestamp       | nullable                                                            |
| created_at | timestamp       |                                                                     |
| updated_at | timestamp       |                                                                     |

**Indexes:** (`user_email`, `is_read`, `created_at`), (`ticket_id`, `created_at`)

---

### `activity_logs`

| Column      | Type            | Attributes                                       |
| ----------- | --------------- | ------------------------------------------------ |
| id          | bigint unsigned | PK, auto-increment                               |
| action      | string          | `created`, `updated`, `deleted`, etc.            |
| model_type  | string          | `Route`, `Landmark`, `User`, `Ticket`, `Setting` |
| model_id    | bigint          | nullable                                         |
| model_name  | string          | nullable, human-readable entity name             |
| user_id     | bigint          | nullable                                         |
| user_name   | string          | nullable                                         |
| description | text            | nullable, human-readable action description      |
| changes     | json            | nullable, old→new value diffs                    |
| ip_address  | string          | nullable                                         |
| created_at  | timestamp       |                                                  |
| updated_at  | timestamp       |                                                  |

**Indexes:** (`user_id`, `created_at`), (`model_type`, `model_id`), `created_at`

**Static method:** `ActivityLog::log(action, modelType, modelId, modelName, description, changes)` — auto-captures auth user and IP.

---

### `recent_activities`

| Column        | Type            | Attributes                            |
| ------------- | --------------- | ------------------------------------- |
| id            | bigint unsigned | PK, auto-increment                    |
| user_id       | bigint unsigned | nullable, FK → users (cascade delete) |
| activity_type | enum            | see types below                       |
| title         | string(255)     | required                              |
| subtitle      | text            | nullable                              |
| from_location | string(255)     | nullable                              |
| to_location   | string(255)     | nullable                              |
| route_names   | text            | nullable                              |
| fare          | decimal(10,2)   | nullable                              |
| metadata      | json            | nullable                              |
| created_at    | timestamp       |                                       |
| updated_at    | timestamp       |                                       |

**Activity types:** `route_calculated`, `fare_calculated`, `location_search`, `route_saved`, `ticket_created`, `ticket_replied`, `ticket_status_changed`

**Indexes:** `user_id`, `created_at`, (`user_id`, `created_at`)

**Constraint:** Max 50 activities per user (oldest auto-deleted on insert).

---

### `app_settings`

| Column      | Type            | Attributes                                                |
| ----------- | --------------- | --------------------------------------------------------- |
| id          | bigint unsigned | PK, auto-increment                                        |
| key         | string          | unique, indexed                                           |
| value       | text            | required                                                  |
| type        | string          | default: `string` (`string`, `number`, `boolean`, `json`) |
| description | text            | nullable                                                  |
| is_public   | boolean         | default: true                                             |
| created_at  | timestamp       |                                                           |
| updated_at  | timestamp       |                                                           |

**Default Records:**

| Key         | Value | Type   | Public | Description                             |
| ----------- | ----- | ------ | ------ | --------------------------------------- |
| base_fare   | 13.00 | number | ✅     | Minimum fare for first 4 km (PHP)       |
| fare_per_km | 1.80  | number | ✅     | Additional fare per km after 4 km (PHP) |

**Static methods:** `AppSetting::get(key, default)`, `AppSetting::set(key, value, type, desc)`, `AppSetting::getPublicSettings()`

---

### Framework Tables

| Table                    | Purpose                                                                 |
| ------------------------ | ----------------------------------------------------------------------- |
| `password_reset_tokens`  | Stores hashed password reset tokens/codes (email PK, token, created_at) |
| `personal_access_tokens` | Sanctum API tokens (30-day expiry)                                      |
| `sessions`               | Active web sessions                                                     |
| `cache`                  | Database-backed cache store                                             |
| `cache_locks`            | Cache lock management                                                   |
| `jobs`                   | Queued jobs                                                             |
| `job_batches`            | Job batch tracking                                                      |
| `failed_jobs`            | Failed job records                                                      |

> **Note:** `password_reset_tokens` is shared between the web admin panel (stores hashed link tokens) and the mobile API (stores hashed 6-digit codes). Both are keyed by email and overwritten on each new request.

---

## Eloquent Models

| Model                | Table                | Key Relationships                                            |
| -------------------- | -------------------- | ------------------------------------------------------------ |
| `User`               | users                | hasMany (via tickets), HasApiTokens (Sanctum)                |
| `JeepneyRoute`       | jeepney_routes       | — (standalone, uses AppSetting for fares)                    |
| `Landmark`           | landmarks            | — (standalone)                                               |
| `SupportTicket`      | support_tickets      | hasMany replies, hasMany notifications, belongsTo user/admin |
| `TicketReply`        | ticket_replies       | belongsTo ticket, belongsTo admin, belongsTo user            |
| `TicketNotification` | ticket_notifications | belongsTo ticket                                             |
| `ActivityLog`        | activity_logs        | — (static `log()` helper, auto-captures user)                |
| `RecentActivity`     | recent_activities    | belongsTo user                                               |
| `AppSetting`         | app_settings         | — (key-value store, static get/set helpers)                  |

---

## Migrations

22 migration files in `database/migrations/`:

| Migration                                            | Purpose                                |
| ---------------------------------------------------- | -------------------------------------- |
| `0001_01_01_000000_create_users_table`               | users, password_reset_tokens, sessions |
| `0001_01_01_000001_create_cache_table`               | cache, cache_locks                     |
| `0001_01_01_000002_create_jobs_table`                | jobs, job_batches, failed_jobs         |
| `2026_01_17_*_create_landmarks_table`                | Initial landmarks                      |
| `2026_01_20_*_create_jeepney_routes_table`           | Initial routes                         |
| `2026_01_20_*_simplify_jeepney_routes_table`         | Schema simplification                  |
| `2026_01_20_*_update_landmarks_table`                | Add category, featured                 |
| `2026_01_20_*_create_activity_logs_table`            | Audit trail                            |
| `2026_01_20_*_create_support_tickets_table`          | Tickets + replies                      |
| `2026_01_21_*_add_missing_columns_to_jeepney_routes` | terminal, color fields                 |
| `2026_01_23_*_create_personal_access_tokens`         | Sanctum tokens                         |
| `2026_01_24_*_add_gallery_images_to_landmarks`       | Gallery support                        |
| `2026_01_30_*_add_features_to_support_tickets`       | flag, archive, type, priority          |
| `2026_01_30_*_rename_customer_columns`               | Column renames                         |
| `2026_02_02_*_create_recent_activities_table`        | User activity tracking                 |
| `2026_02_02_*_add_url_to_landmarks`                  | URL field                              |
| `2026_02_04_*_create_ticket_notifications`           | Notification system                    |
| `2026_02_07_*_remove_url_from_landmarks`             | Remove URL field                       |
| `2026_02_07_*_add_cancelled_status`                  | Cancelled ticket status                |
| `2026_02_08_*_add_ticket_types_to_recent_activities` | Ticket activity types                  |
| `2026_02_08_*_add_sender_type_to_ticket_replies`     | sender_type field                      |
| `2026_02_10_*_create_app_settings_table`             | Dynamic settings store                 |

---

## Seeders

| Seeder                | Purpose                                                   |
| --------------------- | --------------------------------------------------------- |
| `DatabaseSeeder`      | Master seeder — calls all sub-seeders                     |
| `AdminUserSeeder`     | Creates default admin account                             |
| `AppSettingSeeder`    | Seeds fare settings (base_fare: 13.00, fare_per_km: 1.80) |
| `JeepneyRouteSeeder`  | Seeds 50 jeepney routes with real Davao City paths        |
| `LandmarkSeeder`      | Seeds sample landmarks                                    |
| `SupportTicketSeeder` | Seeds sample support tickets                              |

```bash
# Run all seeders
php artisan db:seed

# Run specific seeder
php artisan db:seed --class=LandmarkSeeder

# Fresh migration with seeds
php artisan migrate:fresh --seed
```
