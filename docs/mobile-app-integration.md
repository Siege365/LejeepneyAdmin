# 📱 Mobile App Integration Guide

How the **LeJeepney Flutter mobile app** communicates with the Laravel backend API.

---

## Overview

The Flutter mobile app consumes the Laravel REST API to provide commuters with:

- **Route finding** — search for jeepney routes between two locations
- **Landmarks** — discover points of interest on the map
- **Fare estimation** — calculate jeepney fares based on distance
- **Support tickets** — submit and track help requests
- **Activity history** — keep a log of recent searches and calculations
- **Notifications** — receive updates on support ticket activity

All API endpoints are prefixed with `/api/v1/` and return JSON responses.

---

## API Base Configuration

```
Base URL:     https://yourdomain.com/api
API Version:  v1
Rate Limit:   60 requests per minute (per IP)
Auth Method:  Laravel Sanctum Bearer Token
Content-Type: application/json
```

### Request Headers

```
Content-Type: application/json
Accept: application/json
Authorization: Bearer <sanctum_token>    // Only for protected endpoints
```

---

## Authentication Flow

### 1. Registration

The app registers a new user account:

```
POST /api/register
```

```json
{
    "name": "Juan Dela Cruz",
    "email": "juan@example.com",
    "password": "password123",
    "password_confirmation": "password123",
    "phone": "09171234567"
}
```

**Response (201):**

```json
{
    "user": {
        "id": 1,
        "name": "Juan Dela Cruz",
        "email": "juan@example.com"
    },
    "token": "1|abc123def456..."
}
```

The app stores the `token` locally (e.g., SharedPreferences / secure storage) and includes it in future requests as `Authorization: Bearer <token>`.

> **Note:** All users created via the API are assigned `role = 'user'`. Only the admin panel can create admin accounts.

### 2. Login

```
POST /api/login
```

```json
{
    "email": "juan@example.com",
    "password": "password123"
}
```

**Response (200):**

```json
{
    "user": {
        "id": 1,
        "name": "Juan Dela Cruz",
        "email": "juan@example.com"
    },
    "token": "2|xyz789..."
}
```

**Error — Admin account blocked (403):**

```json
{
    "message": "Admin accounts cannot access the mobile API"
}
```

> **Important:** Admin accounts (`role = 'admin'`) are rejected at the API login endpoint. This enforces role separation between the admin panel and mobile app.

Login is rate-limited to **5 attempts per minute**.

### 3. Get Current User

```
GET /api/user
Authorization: Bearer <token>
```

**Response:**

```json
{
    "id": 1,
    "name": "Juan Dela Cruz",
    "email": "juan@example.com",
    "phone": "09171234567",
    "role": "user"
}
```

### 4. Logout

```
POST /api/logout
Authorization: Bearer <token>
```

Deletes only the current token (not all user tokens). The user must re-login to get a new token.

### 5. Token Behavior

- Tokens expire after **30 days** by default (configurable via `SANCTUM_TOKEN_EXPIRATION` env variable)
- Each login generates a new token named `mobile-app`
- Logout deletes only the current access token
- If a token expires or is deleted, the API returns `401 Unauthorized`
- The app should handle 401 responses by redirecting to the login screen

---

## Core Features

### Route Finding (Main Feature)

This is the primary feature of the mobile app — finding which jeepney to take between two points.

#### How It Works

1. User pins a **starting location** and **destination** on the map
2. App sends the coordinates to the API
3. API checks ALL available routes to find which ones pass near both points
4. Returns matching routes ranked by relevance with boarding/alighting details

#### API Call

```
POST /api/v1/routes/find
```

```json
{
    "from_lat": 10.3157,
    "from_lng": 123.8854,
    "to_lat": 10.2947,
    "to_lng": 123.8986,
    "tolerance": 0.5
}
```

| Parameter   | Type  | Required | Default | Description                                            |
| ----------- | ----- | -------- | ------- | ------------------------------------------------------ |
| `from_lat`  | float | Yes      | —       | Starting point latitude                                |
| `from_lng`  | float | Yes      | —       | Starting point longitude                               |
| `to_lat`    | float | Yes      | —       | Destination latitude                                   |
| `to_lng`    | float | Yes      | —       | Destination longitude                                  |
| `tolerance` | float | No       | 0.5     | Maximum walking distance to/from route in km (0.1–2.0) |

#### Response Structure

```json
{
    "success": true,
    "count": 2,
    "data": [
        {
            "route": {
                "id": 5,
                "route_number": "12A",
                "name": "Colon – Ayala",
                "color": "#EBAF3E",
                "status": "available"
            },
            "boarding_point": {
                "lat": 10.3150,
                "lng": 123.8860,
                "path_index": 12
            },
            "alighting_point": {
                "lat": 10.2950,
                "lng": 123.8980,
                "path_index": 45
            },
            "walk_to_route": 0.08,
            "walk_to_route_time": "~1 min",
            "walk_from_route": 0.05,
            "walk_from_route_time": "~1 min",
            "ride_distance": 3.2,
            "fare": {
                "regular": 13.00,
                "student": 10.40,
                "senior": 10.40,
                "breakdown": {
                    "base_fare": 13.00,
                    "additional_fare": 0.00,
                    "distance_charged": 3.2,
                    "base_distance": 4
                }
            },
            "relevance_score": 0.45,
            "path": [ {"lat": 10.31, "lng": 123.88}, ... ]
        }
    ]
}
```

#### Fare Calculation Logic

The fare is calculated using these rules:

- **Base fare:** ₱13.00 for the first 4 km
- **Additional fare:** ₱1.80 per km after the first 4 km
- **Student/Senior discount:** 20% off the total
- These values are stored in the `app_settings` table and can be changed by admins

```
If distance ≤ 4 km:
    fare = base_fare (₱13.00)

If distance > 4 km:
    fare = base_fare + (distance - 4) × fare_per_km
    fare = 13.00 + (distance - 4) × 1.80
```

#### Relevance Scoring

Routes are ranked by a relevance score (lower = better):

```
relevance = (walk_to_route × 3) + (walk_from_route × 3) + (ride_distance × 0.1)
```

Walking distance is penalized **3x** because users prefer routes they can reach easily.

---

### Route Browsing

#### List All Routes

```
GET /api/v1/routes
```

**Query Parameters:**

| Parameter | Type   | Required | Description                      |
| --------- | ------ | -------- | -------------------------------- |
| `search`  | string | No       | Search by route name or number   |
| `status`  | string | No       | Filter by `active` or `inactive` |

Returns all available routes with their details (excluding path coordinates for performance).

#### Get Route Details

```
GET /api/v1/routes/{id}
```

Returns full route data including the complete path coordinate array.

#### Get All Route Paths (Map Overlay)

```
GET /api/v1/routes/paths
```

Lightweight endpoint optimized for rendering all routes on a map simultaneously. Returns only `id`, `route_number`, `name`, `path`, and `color` for each route.

---

### Landmarks

#### List All Landmarks

```
GET /api/v1/landmarks
```

| Parameter  | Type    | Required | Description                   |
| ---------- | ------- | -------- | ----------------------------- |
| `search`   | string  | No       | Search by name or description |
| `category` | string  | No       | Filter by category            |
| `featured` | boolean | No       | Only featured landmarks       |

**Response includes:**

- `icon_url` — full URL to the icon image
- `gallery_urls` — array of full gallery image URLs
- `coordinates` — `{latitude, longitude}` object
- `category_label` — human-readable category name

#### Get Featured Landmarks

```
GET /api/v1/landmarks/featured
```

Returns landmarks marked as `is_featured = true`, shown prominently in the app's home screen.

#### Get Landmarks by Category

```
GET /api/v1/landmarks/category/{category}
```

**Categories:** `city_center`, `mall`, `school`, `hospital`, `transport`, `other`

#### Get Landmark Details

```
GET /api/v1/landmarks/{id}
```

#### Find Nearby Landmarks

```
POST /api/v1/landmarks/nearby
```

```json
{
    "latitude": 10.3157,
    "longitude": 123.8854,
    "radius": 2
}
```

| Parameter   | Type  | Required | Default | Description                          |
| ----------- | ----- | -------- | ------- | ------------------------------------ |
| `latitude`  | float | Yes      | —       | User's current latitude              |
| `longitude` | float | Yes      | —       | User's current longitude             |
| `radius`    | float | No       | 2       | Search radius in kilometers (0.1–10) |

Returns landmarks sorted by distance from the user's location, calculated using the Haversine formula.

---

### App Settings

```
GET /api/v1/settings
```

**No authentication required.** Returns public settings like fare configuration.

```json
{
    "success": true,
    "data": {
        "base_fare": 13.0,
        "fare_per_km": 1.8
    }
}
```

The app should fetch this on startup or periodically to stay in sync with admin-configured fare values.

---

### Support Tickets

The support ticket system allows mobile users to submit issues and communicate with admins.

#### Create a Ticket

```
POST /api/v1/support/tickets
```

```json
{
    "name": "Juan Dela Cruz",
    "email": "juan@example.com",
    "subject": "Route not showing on map",
    "message": "I searched for Route 12A but it does not appear on the map when I try to navigate.",
    "type": "bug",
    "priority": "medium"
}
```

| Parameter  | Type   | Required | Description                                |
| ---------- | ------ | -------- | ------------------------------------------ |
| `name`     | string | Yes      | Customer's full name                       |
| `email`    | string | Yes      | Customer's email (used for identification) |
| `subject`  | string | Yes      | Brief ticket subject                       |
| `message`  | string | Yes      | Detailed description (10–5000 chars)       |
| `type`     | string | No       | Ticket type (see below)                    |
| `priority` | string | No       | Priority level (default: `medium`)         |

**Ticket Types:** `general`, `technical`, `billing`, `feedback`, `complaint`, `bug`, `inquiry`, `suggestion`, `report`, `other`

**Priority Levels:** `low`, `medium`, `high`, `urgent`

> **Note:** Tickets can be created without authentication. If the user is authenticated, their `user_id` is automatically attached.

#### List My Tickets

```
GET /api/v1/support/tickets?email=juan@example.com
```

For authenticated users, tickets are filtered by `user_id`. For guests, the `email` query parameter is required.

| Parameter  | Type   | Required | Description                                        |
| ---------- | ------ | -------- | -------------------------------------------------- |
| `email`    | string | Yes\*    | Customer's email (\*not required if authenticated) |
| `status`   | string | No       | Filter by status                                   |
| `per_page` | int    | No       | Results per page (default: 15)                     |

#### View Ticket Details

```
GET /api/v1/support/tickets/{id}?email=juan@example.com
```

Returns the ticket with all its replies (conversation thread). Each reply includes:

- `sender_type` — `admin` or `customer`
- `sender_display_name` — display name of the sender
- `message` — reply content
- `created_at` — timestamp

#### Add a Message (Reply to Ticket)

```
POST /api/v1/support/tickets/{id}/message
```

```json
{
    "email": "juan@example.com",
    "message": "I tried again and it's still not working."
}
```

- Only the ticket owner (matched by `user_id` or `email`) can add messages
- If the ticket was `resolved`, adding a message reopens it to `pending`
- Cannot add messages to `cancelled` tickets

#### Cancel a Ticket

```
PUT /api/v1/support/tickets/{id}/cancel
```

```json
{
    "email": "juan@example.com"
}
```

- Changes status to `cancelled`
- Cannot cancel already cancelled or resolved tickets
- Creates a notification for the event

#### Get Ticket Stats (Authenticated)

```
GET /api/v1/support/stats
Authorization: Bearer <token>
```

```json
{
    "success": true,
    "data": {
        "total": 5,
        "pending": 2,
        "in_progress": 1,
        "resolved": 2
    }
}
```

---

### Ticket Notifications

In-app notifications for ticket-related events (admin replies, status changes, etc.).

#### List Notifications

```
GET /api/v1/support/notifications?email=juan@example.com
```

| Parameter    | Type    | Required | Description                                           |
| ------------ | ------- | -------- | ----------------------------------------------------- |
| `email`      | string  | Yes\*    | Customer's email (\*not required if authenticated)    |
| `is_read`    | boolean | No       | Filter by read status                                 |
| `event_type` | string  | No       | Filter by event type                                  |
| `days`       | int     | No       | Show notifications from the last N days (default: 30) |
| `per_page`   | int     | No       | Results per page (default: 20)                        |

**Event Types:** `created`, `replied`, `status_changed`, `resolved`, `admin_message`

**Response includes `unread_count`** in addition to the paginated notifications.

#### Get Unread Count

```
GET /api/v1/support/notifications/unread-count?email=juan@example.com
```

```json
{
    "success": true,
    "unread_count": 3
}
```

Use this for the notification badge in the app's UI.

#### Mark Notifications as Read

**Mark all as read:**

```
PUT /api/v1/support/notifications/mark-all-read
```

**Mark single as read:**

```
PUT /api/v1/support/notifications/{id}/read
```

Email parameter required in the request body or query for identification.

#### Delete a Notification

```
DELETE /api/v1/support/notifications/{id}
```

---

### Recent Activities

Track user activity history within the app (route searches, fare calculations, etc.).

#### List Recent Activities

```
GET /api/v1/recent-activities
```

| Parameter       | Type   | Required | Description                        |
| --------------- | ------ | -------- | ---------------------------------- |
| `limit`         | int    | No       | Max results (default: 20, max: 50) |
| `activity_type` | string | No       | Filter by type                     |

Returns empty for guest (unauthenticated) users.

#### Create an Activity

```
POST /api/v1/recent-activities
```

```json
{
    "activity_type": "route_calculated",
    "title": "Colon to Ayala",
    "subtitle": "Via Route 12A",
    "from_location": "Colon Street",
    "to_location": "Ayala Center Cebu",
    "route_names": "12A, 14D",
    "fare": 15.6,
    "metadata": {
        "distance_km": 5.2,
        "duration_min": 25
    }
}
```

| Parameter       | Type   | Required | Description                 |
| --------------- | ------ | -------- | --------------------------- |
| `activity_type` | string | Yes      | See types below             |
| `title`         | string | Yes      | Display title               |
| `subtitle`      | string | No       | Secondary text              |
| `from_location` | string | No       | Origin name                 |
| `to_location`   | string | No       | Destination name            |
| `route_names`   | string | No       | Comma-separated route names |
| `fare`          | float  | No       | Calculated fare (0–9999.99) |
| `metadata`      | object | No       | Additional JSON data        |

**Activity Types:**

- `route_calculated` — user searched for a route
- `fare_calculated` — user calculated a fare
- `location_search` — user searched for a location
- `route_saved` — user saved a route
- `ticket_created` — user created a support ticket
- `ticket_replied` — user replied to a ticket
- `ticket_status_changed` — ticket status was updated

> **Limit:** Each user can store a maximum of **50 activities**. When the limit is reached, the oldest activity is automatically deleted.

#### Batch Create Activities

```
POST /api/v1/recent-activities/batch
```

```json
{
    "activities": [
        {
            "activity_type": "route_calculated",
            "title": "Colon to Ayala",
            "from_location": "Colon Street",
            "to_location": "Ayala Center Cebu"
        },
        {
            "activity_type": "fare_calculated",
            "title": "Fare: ₱15.60",
            "fare": 15.6
        }
    ]
}
```

Maximum 50 activities per batch. The 50-per-user limit is enforced after the batch insert.

#### Delete an Activity (Authenticated)

```
DELETE /api/v1/recent-activities/{id}
Authorization: Bearer <token>
```

Only deletes if the activity belongs to the authenticated user.

#### Clear All Activities (Authenticated)

```
DELETE /api/v1/recent-activities/clear
Authorization: Bearer <token>
```

Deletes all activities for the authenticated user.

---

## Error Handling

### Standard Error Response

```json
{
    "success": false,
    "message": "Error description here"
}
```

### HTTP Status Codes

| Code | Meaning           | When                                       |
| ---- | ----------------- | ------------------------------------------ |
| 200  | Success           | Request completed successfully             |
| 201  | Created           | Resource created (ticket, activity, etc.)  |
| 400  | Bad Request       | Invalid parameters or business logic error |
| 401  | Unauthorized      | Missing or expired token                   |
| 403  | Forbidden         | Admin trying to use mobile API             |
| 404  | Not Found         | Resource does not exist                    |
| 422  | Validation Error  | Request body failed validation rules       |
| 429  | Too Many Requests | Rate limit exceeded                        |
| 500  | Server Error      | Internal server error                      |

### Validation Error Response (422)

```json
{
    "message": "The given data was invalid.",
    "errors": {
        "email": ["The email field is required."],
        "password": ["The password must be at least 8 characters."]
    }
}
```

### Handling 401 in the App

When the app receives a `401` response, it should:

1. Clear the stored token
2. Redirect the user to the login screen
3. Show a message like "Your session has expired. Please log in again."

---

## CORS Configuration

The API is configured to accept requests from the Flutter app:

| Setting         | Value                                                     |
| --------------- | --------------------------------------------------------- |
| Allowed Origins | Configured via `FRONTEND_URLS` env variable               |
| Allowed Methods | All (`*`)                                                 |
| Allowed Headers | All (`*`)                                                 |
| Exposed Headers | `Content-Type`, `X-Auth-Token`, `Origin`, `Authorization` |
| Max Age         | 86400 seconds (24 hours)                                  |

For Flutter mobile apps, CORS is typically not an issue (it's a browser restriction). However, if the app has a web version, ensure the app's domain is added to `FRONTEND_URLS` in the server's `.env` file.

---

## Integration Checklist

When connecting the Flutter app to the backend:

- [ ] Set the correct `API_BASE_URL` in the Flutter app configuration
- [ ] Implement token storage (SharedPreferences or flutter_secure_storage)
- [ ] Handle 401 responses globally (token expiry)
- [ ] Handle 429 responses (rate limiting — show "too many requests" message)
- [ ] Handle 422 responses (show field-level validation errors)
- [ ] Fetch `/api/v1/settings` on app startup for current fare values
- [ ] Implement pull-to-refresh on ticket and notification lists
- [ ] Poll `/api/v1/support/notifications/unread-count` periodically for badge updates
- [ ] Store recent activities locally and sync with the API
- [ ] Test with both authenticated and guest flows
