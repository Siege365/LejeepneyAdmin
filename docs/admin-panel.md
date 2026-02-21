# 🖥️ Admin Panel Guide

Overview of all admin panel modules, features, and how to use them.

---

## Accessing the Admin Panel

- **URL:** `https://yourdomain.com/login`
- **Authentication:** Email + password (admin role required)
- Only users with `role = 'admin'` can log in to the web panel
- Mobile API users (`role = 'user'`) are rejected with an error

---

## Dashboard

**Route:** `/dashboard`

The dashboard provides a quick overview of system metrics:

| Card             | Description                           |
| ---------------- | ------------------------------------- |
| Total Landmarks  | Count of all landmarks in the system  |
| Total Routes     | Count of all jeepney routes           |
| Active Users     | Count of registered users             |
| Pending Requests | Support tickets with "pending" status |

**Recent Activity Log** — Shows the latest 5 activity log entries with pagination.

---

## Routes Management

**Route:** `/routes`

Manage jeepney routes with their paths, stops, fares, and status.

### Features

| Action            | Description                                           |
| ----------------- | ----------------------------------------------------- |
| **List**          | View all routes with search, status filter, sort      |
| **Create**        | Add a new route with interactive map drawing          |
| **Edit**          | Modify route details and redraw paths                 |
| **Delete**        | Remove a single route                                 |
| **Batch Delete**  | Select multiple routes and delete at once             |
| **Toggle Status** | Activate/deactivate a route (available ↔ unavailable) |
| **View Details**  | See full route info via AJAX modal                    |

### Route Fields

- **Route Number** — Unique identifier (e.g., `01A`)
- **Name** — Human-readable name (e.g., `Matina–San Pedro`)
- **Start Point / End Point** — Terminal names
- **Description** — Optional route description
- **Path Coordinates** — JSON array of `[lat, lng]` pairs (drawn on interactive map)
- **Waypoints** — JSON array of named stops along the route
- **Total Distance** — Auto-calculated from path coordinates (km)
- **Estimated Time** — Minutes
- **Fare** — Calculated from `AppSetting` values (base_fare + fare_per_km)
- **Color** — Hex color code for map rendering (default: `#EBAF3E`)
- **Status** — `available` or `unavailable`

### Interactive Map

- Draw route paths by clicking on the Leaflet.js map
- Supports polyline editing and waypoint placement
- Uses **OpenStreetMap** tiles

### Stats Bar

Shows total routes, available routes, unavailable routes, and average distance.

---

## Landmarks Management

**Route:** `/landmarks`

Manage points of interest that users can discover on the mobile app.

### Features

| Action           | Description                                        |
| ---------------- | -------------------------------------------------- |
| **List**         | View all landmarks with search and category filter |
| **Create**       | Add a new landmark with map picker and gallery     |
| **Edit**         | Modify landmark details, add/remove images         |
| **Delete**       | Remove a single landmark (cleans up stored images) |
| **Batch Delete** | Select multiple and delete at once                 |

### Landmark Fields

- **Name** — Landmark display name
- **Description** — Optional description
- **Category** — `city_center`, `mall`, `school`, `hospital`, `transport`, `other`
- **Latitude / Longitude** — Set via interactive map picker
- **Featured** — Yes/no toggle; featured landmarks are highlighted in the mobile app
- **Icon Image** — File upload (max 2 MB, 50–2048 px) or external URL
- **Gallery Images** — Multiple file uploads (max 5 MB each, 100–4096 px) or external URLs

### Image Handling

- Uploaded images stored in `storage/app/public/` via Laravel Storage
- Old images are cleaned up from disk when replaced or when landmark is deleted
- Supports both file upload and external URL input for each image

---

## Customer Service

**Route:** `/customer-service`

Handle support tickets submitted by mobile app users.

### Ticket List Features

| Feature             | Description                                                 |
| ------------------- | ----------------------------------------------------------- |
| **Search**          | Search by ID, name, email, subject, message                 |
| **Type Filter**     | general, technical, billing, feedback, complaint, bug, etc. |
| **Priority Filter** | low, medium, high, urgent                                   |
| **Status Filter**   | pending, in-progress, resolved, cancelled                   |
| **Flag Filter**     | Show only flagged tickets                                   |
| **Archive View**    | Toggle between active and archived tickets                  |
| **Bulk Actions**    | Archive, restore, flag, unflag, mark_resolved on selections |
| **Stats Sidebar**   | Quick counts by status                                      |

### Ticket Detail Features

| Feature                | Description                                                           |
| ---------------------- | --------------------------------------------------------------------- |
| **Reply**              | Send a response to the customer (creates TicketReply)                 |
| **Email Notification** | Checkbox to send reply email to customer via server-side Laravel Mail |
| **Status Update**      | Change to pending / in-progress / resolved                            |
| **Flag**               | Toggle importance flag (AJAX)                                         |
| **Archive / Restore**  | Archive old tickets or restore archived ones                          |

### Email Notifications

When replying to a ticket with the "Send email notification" checkbox enabled:

- A `TicketReplyMail` Mailable is dispatched server-side via Laravel Mail
- The email uses a branded HTML template (`resources/views/emails/ticket-reply.blade.php`)
- The `email_sent` field on the reply record is set to `true`
- No client-side JavaScript email sending is needed

### Ticket Statuses

| Status      | Badge Color | Description              |
| ----------- | ----------- | ------------------------ |
| Pending     | Yellow      | New or awaiting response |
| In Progress | Blue        | Being handled by admin   |
| Resolved    | Green       | Issue resolved           |
| Cancelled   | Gray        | Cancelled by customer    |

---

## Audit Trail

**Route:** `/audit-trail`

Comprehensive log of all admin actions across the system.

### Features

| Feature         | Description                                                       |
| --------------- | ----------------------------------------------------------------- |
| **List**        | Paginated view of all activity logs                               |
| **Search**      | By description, user name, model name                             |
| **Filter**      | By action (created/updated/deleted), model type, user, date range |
| **View Detail** | Full log entry with formatted changes (old→new values)            |
| **CSV Export**  | Export filtered results to downloadable CSV file                  |

### Logged Actions

| Action               | Example Description                                                     |
| -------------------- | ----------------------------------------------------------------------- |
| Create landmark      | "Created landmark 'SM City Davao'"                                      |
| Update route         | "Updated route '01A'" with changes: `{fare: {old: 13.00, new: 15.00}}`  |
| Delete route         | "Deleted route '12A'"                                                   |
| Reply to ticket      | "Replied to ticket '#5: App Crash Issue'"                               |
| Change ticket status | "Changed status of ticket '#5' from pending to resolved"                |
| Update settings      | "Updated fare settings" with changes: `{base_fare: {old: 13, new: 15}}` |
| Bulk operations      | "Applied bulk action 'archive' to 3 ticket(s)"                          |

Each entry records: action, model type/id/name, user who performed it, description, JSON changes, IP address, and timestamp.

---

## Settings / Configuration

**Route:** `/settings`

Manage application-wide fare settings that are exposed to the Flutter app via the `GET /api/v1/settings` endpoint.

### Features

| Feature              | Description                                                                     |
| -------------------- | ------------------------------------------------------------------------------- |
| **View Settings**    | See current base fare and fare per km values                                    |
| **Update Settings**  | Modify fare values with double-confirmation modal                               |
| **Validation**       | Ensures values are valid positive numbers                                       |
| **API Sync**         | Settings marked `is_public` are immediately available at `GET /api/v1/settings` |
| **Activity Logging** | All changes logged with old→new values in audit trail                           |

### Current Settings

| Setting     | Default | Description                                    |
| ----------- | ------- | ---------------------------------------------- |
| base_fare   | 13.00   | Minimum fare for the first 4 km (PHP currency) |
| fare_per_km | 1.80    | Additional fare per km after 4 km (PHP)        |

These settings are used by:

- The `JeepneyRoute` model's `calculateFare()` method
- The `RouteFinderService` for multi-transfer fare calculations
- The Flutter app (fetched via `GET /api/v1/settings` on startup)

---

## Account Settings

**Route:** `/account/settings`

### Sections

| Section            | Description                                              |
| ------------------ | -------------------------------------------------------- |
| **Profile**        | Update name, email, phone                                |
| **Password**       | Change password (requires current password verification) |
| **Delete Account** | Permanently delete account (blocked if last admin)       |

All changes are logged in the audit trail.

---

## Notifications

**Route:** `/notifications`

In-app notification bell for admin activity (support ticket events).

| Feature           | Description                                   |
| ----------------- | --------------------------------------------- |
| **Bell Dropdown** | Latest 20 notifications + unread count badge  |
| **View All**      | Paginated list of all notifications (20/page) |
| **Mark as Read**  | Mark individual or all notifications as read  |

---

## Password Reset (Admin)

Admins who forget their password can reset it via a link-based flow:

1. Go to `/forgot-password` → enter email
2. Server sends a reset link via `PasswordResetMail` (uses Laravel's built-in `Password::sendResetLink()`)
3. Link leads to `/reset-password/{token}` → enter new password
4. Token expires after **60 minutes**

Rate limited to **3 requests per minute** on both the request and reset endpoints.

---

## Creating New Admin Users

**Route:** `/register` (only accessible when logged in as admin)

Only existing admins can create new admin accounts. The route is protected behind `auth` + `admin` middleware. New accounts are explicitly assigned `role = 'admin'`.

---

## Tech Stack

| Component  | Technology                               |
| ---------- | ---------------------------------------- |
| Backend    | Laravel 12 / PHP 8.2+                    |
| Frontend   | Blade templates + Tailwind CSS 4         |
| Build Tool | Vite 7 with `@tailwindcss/vite` plugin   |
| Maps       | Leaflet.js + OpenStreetMap tiles         |
| Database   | MySQL 8.0+ / MariaDB 10.6+               |
| Auth       | Laravel built-in session auth            |
| Middleware | Custom `AdminMiddleware` for role gating |
