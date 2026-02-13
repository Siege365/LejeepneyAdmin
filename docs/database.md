# 🗄️ Database Schema

Complete reference of all database tables, columns, and relationships.

> For a high-level overview of how the system works, see [system-overview.md](system-overview.md).

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
  └── (admin actions logged)

jeepney_routes (standalone)
landmarks (standalone)
```

---

## Tables

### `users`

| Column            | Type                  | Attributes         |
| ----------------- | --------------------- | ------------------ |
| id                | bigint                | PK, auto-increment |
| name              | string                | required           |
| email             | string                | unique, required   |
| role              | enum(`admin`, `user`) | default: `user`    |
| phone             | string                | nullable           |
| email_verified_at | timestamp             | nullable           |
| password          | string                | hashed             |
| remember_token    | string                | nullable           |
| created_at        | timestamp             |                    |
| updated_at        | timestamp             |                    |

**Related tables:** `password_reset_tokens`, `sessions`

---

### `jeepney_routes`

| Column         | Type                             | Attributes             |
| -------------- | -------------------------------- | ---------------------- |
| id             | bigint                           | PK, auto-increment     |
| route_number   | string(50)                       | unique, indexed        |
| name           | string                           | required               |
| path           | json                             | route path coordinates |
| waypoints      | json                             | nullable               |
| start_point    | string                           | nullable               |
| end_point      | string                           | nullable               |
| total_distance | decimal(8,2)                     | nullable, km           |
| estimated_time | integer                          | nullable, minutes      |
| fare           | decimal(8,2)                     | nullable, PHP currency |
| status         | enum(`available`, `unavailable`) | default: `available`   |
| color          | string(7)                        | default: `#EBAF3E`     |
| description    | text                             | nullable               |
| created_at     | timestamp                        |                        |
| updated_at     | timestamp                        |                        |

**Indexes:** `route_number`, `status`

---

### `landmarks`

| Column         | Type          | Attributes               |
| -------------- | ------------- | ------------------------ |
| id             | bigint        | PK, auto-increment       |
| name           | string        | required                 |
| latitude       | decimal(10,8) | required                 |
| longitude      | decimal(11,8) | required                 |
| description    | text          | nullable                 |
| icon_image     | string        | nullable, file path      |
| gallery_images | json          | nullable, array of paths |
| category       | string(100)   | nullable, indexed        |
| is_featured    | boolean       | default: false, indexed  |
| created_at     | timestamp     |                          |
| updated_at     | timestamp     |                          |

**Indexes:** `category`, `is_featured`, (`latitude`, `longitude`)

**Categories:** mall, school, church, hospital, government, terminal, park, market, hotel, restaurant, other

---

### `support_tickets`

| Column      | Type                                                    | Attributes           |
| ----------- | ------------------------------------------------------- | -------------------- |
| id          | bigint                                                  | PK, auto-increment   |
| user_id     | bigint                                                  | nullable, FK → users |
| admin_id    | bigint                                                  | nullable, FK → users |
| subject     | string                                                  | required             |
| message     | text                                                    | required             |
| name        | string                                                  | customer name        |
| email       | string                                                  | customer email       |
| type        | enum                                                    | see below            |
| priority    | enum(`low`, `medium`, `high`, `urgent`)                 | default: `medium`    |
| status      | enum(`pending`, `in-progress`, `resolved`, `cancelled`) | default: `pending`   |
| is_flagged  | boolean                                                 | default: false       |
| is_archived | boolean                                                 | default: false       |
| archived_at | timestamp                                               | nullable             |
| created_at  | timestamp                                               |                      |
| updated_at  | timestamp                                               |                      |

**Type values:** `general`, `technical`, `billing`, `feedback`, `other`, `complaint`, `bug`, `inquiry`, `suggestion`, `report`

**Indexes:** `status`, `type`, `priority`, `is_flagged`, `is_archived`

---

### `ticket_replies`

| Column            | Type                      | Attributes                            |
| ----------------- | ------------------------- | ------------------------------------- |
| id                | bigint                    | PK, auto-increment                    |
| support_ticket_id | bigint                    | FK → support_tickets (cascade delete) |
| sender_type       | enum(`admin`, `customer`) | default: `admin`                      |
| admin_id          | bigint                    | nullable, FK → users                  |
| user_id           | bigint                    | nullable, FK → users (set null)       |
| sender_name       | string                    | nullable                              |
| message           | text                      | required                              |
| admin_name        | string                    | nullable, default: `Admin Support`    |
| email_sent        | boolean                   | default: false                        |
| created_at        | timestamp                 |                                       |
| updated_at        | timestamp                 |                                       |

---

### `ticket_notifications`

| Column     | Type      | Attributes                                                          |
| ---------- | --------- | ------------------------------------------------------------------- |
| id         | bigint    | PK, auto-increment                                                  |
| ticket_id  | bigint    | FK → support_tickets (cascade delete)                               |
| user_email | string    | indexed                                                             |
| event_type | enum      | `created`, `replied`, `status_changed`, `resolved`, `admin_message` |
| title      | string    | notification title                                                  |
| message    | text      | notification body                                                   |
| metadata   | json      | nullable (old_status, new_status, admin_name, etc.)                 |
| is_read    | boolean   | default: false                                                      |
| read_at    | timestamp | nullable                                                            |
| created_at | timestamp |                                                                     |
| updated_at | timestamp |                                                                     |

**Indexes:** (`user_email`, `is_read`, `created_at`), (`ticket_id`, `created_at`)

---

### `activity_logs`

| Column      | Type      | Attributes                        |
| ----------- | --------- | --------------------------------- |
| id          | bigint    | PK, auto-increment                |
| action      | string    | `created`, `updated`, `deleted`   |
| model_type  | string    | `Route`, `Landmark`, `User`, etc. |
| model_id    | bigint    | nullable                          |
| model_name  | string    | nullable, name of the entity      |
| user_id     | bigint    | nullable                          |
| user_name   | string    | nullable                          |
| description | text      | nullable, human-readable          |
| changes     | json      | nullable, what changed            |
| ip_address  | string    | nullable                          |
| created_at  | timestamp |                                   |
| updated_at  | timestamp |                                   |

**Indexes:** (`user_id`, `created_at`), (`model_type`, `model_id`), `created_at`

---

### `recent_activities`

| Column        | Type          | Attributes                                                              |
| ------------- | ------------- | ----------------------------------------------------------------------- |
| id            | bigint        | PK, auto-increment                                                      |
| user_id       | bigint        | nullable, FK → users (cascade delete)                                   |
| activity_type | enum          | `route_calculated`, `fare_calculated`, `location_search`, `route_saved` |
| title         | string(255)   | required                                                                |
| subtitle      | text          | nullable                                                                |
| from_location | string(255)   | nullable                                                                |
| to_location   | string(255)   | nullable                                                                |
| route_names   | text          | nullable                                                                |
| fare          | decimal(10,2) | nullable                                                                |
| metadata      | json          | nullable                                                                |
| created_at    | timestamp     |                                                                         |
| updated_at    | timestamp     |                                                                         |

**Indexes:** `user_id`, `created_at`, (`user_id`, `created_at`)

---

### `app_settings`

| Column      | Type      | Attributes                                        |
| ----------- | --------- | ------------------------------------------------- |
| id          | bigint    | PK, auto-increment                                |
| key         | string    | unique, indexed                                   |
| value       | text      | required                                          |
| type        | string    | default: `string` (string, number, boolean, json) |
| description | text      | nullable                                          |
| is_public   | boolean   | default: true                                     |
| created_at  | timestamp |                                                   |
| updated_at  | timestamp |                                                   |

**Indexes:** `key`

**Default Records:**

| Key         | Value | Type   | Description                            |
| ----------- | ----- | ------ | -------------------------------------- |
| base_fare   | 13.00 | number | Minimum fare charged for jeepney rides |
| fare_per_km | 1.80  | number | Additional fare per kilometer traveled |

---

### Framework Tables

| Table                    | Purpose                      |
| ------------------------ | ---------------------------- |
| `password_reset_tokens`  | Stores password reset tokens |
| `sessions`               | Stores active sessions       |
| `cache`                  | Application cache store      |
| `cache_locks`            | Cache lock management        |
| `jobs`                   | Queued jobs                  |
| `job_batches`            | Job batch tracking           |
| `failed_jobs`            | Failed job records           |
| `personal_access_tokens` | Sanctum API tokens           |

---

## Eloquent Models

| Model                | Table                | Key Relationships                                            |
| -------------------- | -------------------- | ------------------------------------------------------------ |
| `User`               | users                | hasMany tickets, hasMany activities                          |
| `JeepneyRoute`       | jeepney_routes       | —                                                            |
| `Landmark`           | landmarks            | —                                                            |
| `SupportTicket`      | support_tickets      | hasMany replies, hasMany notifications, belongsTo user/admin |
| `TicketReply`        | ticket_replies       | belongsTo ticket, belongsTo admin                            |
| `TicketNotification` | ticket_notifications | belongsTo ticket                                             |
| `ActivityLog`        | activity_logs        | belongsTo user                                               |
| `RecentActivity`     | recent_activities    | belongsTo user                                               |
| `AppSetting`         | app_settings         | — (key-value store, static get/set helpers)                  |

---

## Running Migrations

```bash
# Run all migrations
php artisan migrate

# Fresh migration (drops all tables)
php artisan migrate:fresh

# Fresh migration with seeders
php artisan migrate:fresh --seed

# Rollback last migration
php artisan migrate:rollback

# Check migration status
php artisan migrate:status
```

---

## Seeders

| Seeder                | Purpose                                              |
| --------------------- | ---------------------------------------------------- |
| `DatabaseSeeder`      | Master seeder, calls all sub-seeders                 |
| `LandmarkSeeder`      | Seeds sample landmark data                           |
| `AppSettingSeeder`    | Seeds default fare settings (base_fare, fare_per_km) |
| `AdminUserSeeder`     | Seeds default admin user account                     |
| `JeepneyRouteSeeder`  | Seeds sample jeepney route data                      |
| `SupportTicketSeeder` | Seeds sample support tickets                         |

```bash
# Run all seeders
php artisan db:seed

# Run specific seeder
php artisan db:seed --class=LandmarkSeeder
```
