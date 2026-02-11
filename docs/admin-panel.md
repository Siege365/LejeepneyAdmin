# 🖥️ Admin Panel Guide

Overview of all admin panel modules, features, and how to use them.

---

## Accessing the Admin Panel

- **URL:** `https://yourdomain.com/login`
- **Authentication:** Email + password (admin role required)
- Only users with `role = 'admin'` can log in

---

## Dashboard

**Route:** `/dashboard`

The dashboard provides a quick overview of:

| Card             | Description                           |
| ---------------- | ------------------------------------- |
| Total Landmarks  | Count of all landmarks in the system  |
| Total Routes     | Count of all jeepney routes           |
| Active Users     | Count of registered users             |
| Pending Requests | Support tickets with "pending" status |

**Recent Activity Log** — Shows the latest 5 activity entries with pagination.

---

## Routes Management

**Route:** `/routes`

Manage jeepney routes with their paths, stops, fares, and schedules.

### Features

| Action            | Description                                      |
| ----------------- | ------------------------------------------------ |
| **List**          | View all routes with search, filter, and sorting |
| **Create**        | Add a new route with interactive map drawing     |
| **Edit**          | Modify route details and redraw paths            |
| **Delete**        | Remove a single route                            |
| **Batch Delete**  | Select multiple routes and delete at once        |
| **Toggle Status** | Activate or deactivate a route                   |
| **View Details**  | See full route info with map preview             |

### Route Fields

- Route Number, Name
- Start Point, End Point
- Description
- Fare (minimum/maximum)
- Operating Hours
- Status (active/inactive)
- Path Coordinates (drawn on map)

### Interactive Map

- Draw route paths by clicking on the map
- Supports polyline editing
- Uses **Leaflet.js** with **OpenStreetMap** tiles

---

## Landmarks Management

**Route:** `/landmarks`

Manage points of interest (landmarks) that users can discover on the mobile app.

### Features

| Action           | Description                                        |
| ---------------- | -------------------------------------------------- |
| **List**         | View all landmarks with search and category filter |
| **Create**       | Add a new landmark with map picker and gallery     |
| **Edit**         | Modify landmark details                            |
| **Delete**       | Remove a single landmark                           |
| **Batch Delete** | Select multiple and delete at once                 |

### Landmark Fields

- Name, Description
- Category (mall, school, church, hospital, government, terminal, park, market, hotel, restaurant, other)
- Latitude / Longitude (via map picker)
- Address
- Featured (yes/no)
- Gallery Images (multiple uploads)

### Map Picker

- Click on the map to set coordinates
- Auto-fills latitude/longitude fields
- Search by address

---

## Customer Service

**Route:** `/customer-service`

Handle support tickets submitted by mobile app users.

### Ticket List Features

| Feature          | Description                                                   |
| ---------------- | ------------------------------------------------------------- |
| **Search**       | Search by ID, name, email, subject, message                   |
| **Filter**       | By status (pending, in-progress, resolved), flagged, archived |
| **Sort**         | By date, status, priority                                     |
| **Bulk Actions** | Archive, delete, change status for multiple tickets           |

### Ticket Detail Features

| Feature                | Description                                         |
| ---------------------- | --------------------------------------------------- |
| **Reply**              | Send a response to the customer                     |
| **Email Notification** | Checkbox to send an email via EmailJS when replying |
| **Status Update**      | Change to pending / in-progress / resolved          |
| **Flag**               | Mark a ticket as important                          |
| **Archive**            | Archive old/resolved tickets                        |
| **Restore**            | Restore archived tickets                            |

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

Searchable, filterable log of all admin actions across the system.

### Features

| Feature         | Description                                                         |
| --------------- | ------------------------------------------------------------------- |
| **List**        | View all activity logs with pagination                              |
| **Search**      | Search by description, user name, model name                        |
| **Filter**      | By action (created, updated, deleted), model type, user, date range |
| **View Detail** | See full log entry with changes JSON                                |
| **CSV Export**  | Export filtered results to CSV file                                 |

### Log Entry Fields

- Action (created, updated, deleted)
- Model Type (Route, Landmark, User, Ticket, Setting, etc.)
- Model Name
- User who performed the action
- Description
- Changes (JSON diff)
- IP Address
- Timestamp

---

## Settings / Configuration

**Route:** `/settings`

Manage application-wide settings that are also exposed to the Flutter mobile app via API.

### Features

| Feature              | Description                                                         |
| -------------------- | ------------------------------------------------------------------- |
| **View Settings**    | See current base fare and fare per km values                        |
| **Update Settings**  | Modify fare values with double-confirmation modal                   |
| **Validation**       | Ensures values are valid positive numbers                           |
| **API Exposure**     | Settings marked `is_public` are available at `GET /api/v1/settings` |
| **Activity Logging** | All changes are logged in the audit trail                           |

### Current Settings

| Setting     | Default | Description                                  |
| ----------- | ------- | -------------------------------------------- |
| base_fare   | 13.00   | Minimum fare charged for jeepney rides (PHP) |
| fare_per_km | 1.80    | Additional fare per kilometer traveled (PHP) |

---

## Account Settings

**Route:** `/account/settings`

### Features

| Section            | Description                                 |
| ------------------ | ------------------------------------------- |
| **Profile**        | Update name, email, phone                   |
| **Password**       | Change password (requires current password) |
| **Delete Account** | Permanently delete admin account            |

---

## Notifications

**Route:** `/notifications`

In-app notifications for admin activity.

| Action           | Description                          |
| ---------------- | ------------------------------------ |
| View All         | See all notifications                |
| Mark as Read     | Mark individual notification as read |
| Mark All as Read | Clear all unread notifications       |

---

## Creating New Admin Users

**Route:** `/register` (only accessible when logged in as admin)

Admins can create new admin accounts. This route is protected behind the `auth` + `admin` middleware — only existing admins can access it.

---

## Activity Logging

All important admin actions are logged:

| Action               | Log Example                                                               |
| -------------------- | ------------------------------------------------------------------------- |
| Create landmark      | "Created landmark 'SM City Cebu'"                                         |
| Delete route         | "Deleted route '12A'"                                                     |
| Reply to ticket      | "Replied to ticket '#5: App Crash Issue'"                                 |
| Change ticket status | "Changed status of ticket '#5: App Crash Issue' from pending to resolved" |
| Batch operations     | "Applied bulk action 'archive' to 3 ticket(s)"                            |

Logs are visible on the Dashboard and stored in the `activity_logs` table.
