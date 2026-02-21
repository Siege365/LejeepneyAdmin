# 📡 API Reference

REST API endpoints for the **LeJeepney Flutter mobile app**.

**Base URL:** `https://yourdomain.com/api`

**Rate Limit:** All `/api/v1/*` endpoints are rate-limited to **60 requests per minute**. Auth endpoints have tighter limits (noted per-endpoint).

**Authentication:** Token-based via [Laravel Sanctum](https://laravel.com/docs/sanctum). Tokens expire after **30 days**.

**Content-Type:** `application/json`

> Endpoints marked with 🔒 require an `Authorization: Bearer <token>` header. Endpoints marked with 🔓 are publicly accessible.

---

## Table of Contents

- [Authentication](#authentication)
- [Password Reset](#password-reset)
- [Settings](#settings)
- [Routes](#routes)
- [Route Finder](#route-finder)
- [Walking Routes](#walking-routes)
- [Landmarks](#landmarks)
- [Support Tickets](#support-tickets)
- [Ticket Notifications](#ticket-notifications)
- [Recent Activities](#recent-activities)
- [Caching & Conditional Requests](#caching--conditional-requests)
- [Error Responses](#error-responses)

---

## Authentication

### Register

```
POST /api/register
```

🔓 **Public**

| Parameter             | Type   | Required | Description          |
| --------------------- | ------ | -------- | -------------------- |
| name                  | string | ✅       | User's full name     |
| email                 | string | ✅       | Valid email address  |
| password              | string | ✅       | Minimum 8 characters |
| password_confirmation | string | ✅       | Must match password  |
| phone                 | string | ❌       | Phone number         |

**Response (201):**

```json
{
    "success": true,
    "message": "Registration successful",
    "user": {
        "id": 1,
        "name": "John",
        "email": "john@example.com",
        "phone": null,
        "role": "user"
    },
    "token": "1|abc123..."
}
```

> Only creates accounts with `role = 'user'`. Admin accounts are created via the web panel.

---

### Login

```
POST /api/login
```

🔓 **Public** — _Rate limited: 5 attempts per minute_

| Parameter | Type   | Required | Description      |
| --------- | ------ | -------- | ---------------- |
| email     | string | ✅       | Registered email |
| password  | string | ✅       | Account password |

**Response (200):**

```json
{
    "success": true,
    "message": "Login successful",
    "user": {
        "id": 1,
        "name": "John",
        "email": "john@example.com",
        "phone": "09123456789",
        "role": "user"
    },
    "token": "1|abc123..."
}
```

> Only `role = 'user'` accounts can log in via the API. Admin accounts receive a 403 error.

---

### Get Current User

```
GET /api/user
```

🔒 **Requires:** `Authorization: Bearer <token>`

**Response (200):**

```json
{
    "id": 1,
    "name": "John",
    "email": "john@example.com",
    "phone": "09123456789",
    "role": "user"
}
```

---

### Logout

```
POST /api/logout
```

🔒 **Requires:** `Authorization: Bearer <token>`

Revokes the current access token only.

**Response (200):**

```json
{
    "success": true,
    "message": "Logged out successfully"
}
```

---

## Password Reset

Six-digit code-based password reset flow for mobile users.

### Request Reset Code

```
POST /api/password/forgot
```

🔓 **Public** — _Rate limited: 5/min (global) + 3/hr per email (application-level)_

| Parameter | Type   | Required | Description      |
| --------- | ------ | -------- | ---------------- |
| email     | string | ✅       | Registered email |

**Response (200):**

```json
{
    "success": true,
    "message": "If an account with that email exists, a reset code has been sent."
}
```

> **Security:** Returns the same success message regardless of whether the email exists (prevents email enumeration). Only `role = 'user'` accounts receive codes.

**Rate Limit Exceeded (429):**

```json
{
    "success": false,
    "message": "Too many reset requests. Please try again in 45 minutes."
}
```

**How it works:**

1. Generates a random 6-digit code
2. Stores `Hash::make(code)` in `password_reset_tokens` table
3. Sends the plain code to the user's email via `ResetCodeMail`
4. Code expires after **15 minutes**

---

### Reset Password with Code

```
POST /api/password/reset
```

🔓 **Public** — _Rate limited: 5/min (global) + 5/hr per email (application-level)_

| Parameter             | Type   | Required | Description                           |
| --------------------- | ------ | -------- | ------------------------------------- |
| email                 | string | ✅       | Email used to request the code        |
| code                  | string | ✅       | 6-digit code from email               |
| password              | string | ✅       | New password (min 8 chars, see rules) |
| password_confirmation | string | ✅       | Must match password                   |

**Password Requirements:**

- Minimum 8 characters
- At least one lowercase letter
- At least one uppercase letter
- At least one number

**Response (200):**

```json
{
    "success": true,
    "message": "Password has been reset successfully. You can now log in with your new password."
}
```

**Invalid/Expired Code (422):**

```json
{
    "success": false,
    "message": "Invalid or expired reset code."
}
```

**Expired Code (422):**

```json
{
    "success": false,
    "message": "Reset code has expired. Please request a new one."
}
```

---

## Settings

### Get Public Settings

```
GET /api/v1/settings
```

🔓 **Public** — No authentication required.

Returns all app settings marked as `is_public = true`. Currently used for fare configuration.

**Response (200):**

```json
{
    "success": true,
    "data": {
        "base_fare": 13.0,
        "fare_per_km": 1.8
    }
}
```

| Field       | Type  | Description                                |
| ----------- | ----- | ------------------------------------------ |
| base_fare   | float | Minimum fare for first 4 km (PHP currency) |
| fare_per_km | float | Additional fare per km after 4 km (PHP)    |

> Values are automatically type-cast (numbers as floats, not strings). These are managed by admins at `/settings` in the admin panel.

---

## Routes

### List All Routes

```
GET /api/v1/routes
```

🔓 **Public** — Supports conditional requests via `If-Modified-Since` header.

Returns all routes with `status = 'available'`.

**Headers (optional):**

| Header            | Description                                     |
| ----------------- | ----------------------------------------------- |
| If-Modified-Since | Returns `304 Not Modified` if no routes changed |

**Response (200):**

```json
{
    "success": true,
    "data": [
        {
            "id": 1,
            "route_number": "01A",
            "name": "Matina–San Pedro",
            "path": [[7.0650, 125.6080], ...],
            "waypoints": [...],
            "start_point": "Matina Crossing",
            "end_point": "San Pedro",
            "total_distance": 5.79,
            "estimated_time": 24,
            "fare": 16.22,
            "status": "available",
            "color": "#FF5733",
            "description": "..."
        }
    ],
    "count": 50,
    "last_modified": "2026-02-21T12:00:00Z"
}
```

**Response Headers:**

| Header        | Value                           |
| ------------- | ------------------------------- |
| Last-Modified | Timestamp of most recent update |

---

### Get Route Details

```
GET /api/v1/routes/{id}
```

🔓 **Public** — Includes `Last-Modified` response header.

---

### Get All Route Paths (Lightweight Map Data)

```
GET /api/v1/routes/paths
```

🔓 **Public** — Supports delta sync via `?since=` parameter and `If-Modified-Since` → 304.

Returns all available routes with their coordinate paths for map rendering.

**Query Parameters:**

| Parameter | Type              | Required | Description                                |
| --------- | ----------------- | -------- | ------------------------------------------ |
| since     | ISO 8601 datetime | ❌       | Only return routes updated after this time |

**Example — Delta sync:**

```
GET /api/v1/routes/paths?since=2026-02-20T10:00:00Z
```

Returns only routes modified since the given timestamp, enabling efficient incremental updates on the client.

---

## Route Finder

### Find Routes Between Two Points

```
POST /api/v1/routes/find
```

🔓 **Public**

The core navigation endpoint. Finds jeepney routes between an origin and destination, supporting **direct rides (0 transfers), 1-transfer, and 2-transfer** combinations.

| Parameter             | Type  | Required | Default | Description                                        |
| --------------------- | ----- | -------- | ------- | -------------------------------------------------- |
| from_lat              | float | ✅       | —       | Origin latitude                                    |
| from_lng              | float | ✅       | —       | Origin longitude                                   |
| to_lat                | float | ✅       | —       | Destination latitude                               |
| to_lng                | float | ✅       | —       | Destination longitude                              |
| tolerance             | float | ❌       | 0.5     | Max walking distance to/from route in km (0.1–2.0) |
| transfer_walk_max     | float | ❌       | 0.5     | Max walking distance between transfers in km       |
| include_walking_paths | bool  | ❌       | false   | Fetch real walking directions via ORS/OSRM         |

**Response (200):**

```json
{
    "success": true,
    "data": [
        {
            "id": "uuid-string",
            "type": "direct",
            "segments": [
                {
                    "type": "walking",
                    "from": { "lat": 7.065, "lng": 125.608 },
                    "to": { "lat": 7.066, "lng": 125.609 },
                    "distance_km": 0.12,
                    "duration_minutes": 1.4,
                    "path": [[7.065, 125.608], [7.066, 125.609]],
                    "walking_path": null
                },
                {
                    "type": "jeepney_ride",
                    "route_id": 1,
                    "route_name": "01A - Matina–San Pedro",
                    "route_color": "#FF5733",
                    "from": { "lat": 7.066, "lng": 125.609 },
                    "to": { "lat": 7.080, "lng": 125.620 },
                    "board_index": 15,
                    "alight_index": 42,
                    "distance_km": 5.79,
                    "duration_minutes": 23.2,
                    "path": [[7.066, 125.609], ...]
                },
                {
                    "type": "walking",
                    "from": { "lat": 7.080, "lng": 125.620 },
                    "to": { "lat": 7.081, "lng": 125.621 },
                    "distance_km": 0.08,
                    "duration_minutes": 1.0,
                    "path": [[7.080, 125.620], [7.081, 125.621]],
                    "walking_path": null
                }
            ],
            "total_distance_km": 5.99,
            "total_duration_minutes": 25.6,
            "total_walking_km": 0.20,
            "transfers": 0,
            "fare": {
                "regular": 16.22,
                "student": 12.98,
                "senior": 12.98,
                "per_segment": [16.22]
            },
            "score": 65.66
        }
    ],
    "count": 5,
    "search": {
        "from": { "lat": 7.065, "lng": 125.608 },
        "to": { "lat": 7.081, "lng": 125.621 },
        "tolerance_km": 0.5
    }
}
```

**Scoring formula:** `transfers × 40 + fare × 2 + walking_km × 100 + duration × 1 − direct_bonus(20)`. Lower is better.

**Fare calculation:** Base fare ₱13.00 (first 4 km) + ₱1.80/km thereafter. Student/senior: 20% discount. Multi-transfer totals summed per segment.

---

## Walking Routes

### Get Walking Directions

```
POST /api/v1/walking-route
```

🔓 **Public**

Proxies walking directions from OpenRouteService (primary) with OSRM (fallback). Results are cached for **1 hour**.

| Parameter | Type  | Required | Description           |
| --------- | ----- | -------- | --------------------- |
| from_lat  | float | ✅       | Origin latitude       |
| from_lng  | float | ✅       | Origin longitude      |
| to_lat    | float | ✅       | Destination latitude  |
| to_lng    | float | ✅       | Destination longitude |

**Response (200):**

```json
{
    "success": true,
    "data": {
        "path": [[7.065, 125.608], [7.066, 125.609], ...],
        "distance_km": 0.45,
        "duration_minutes": 5
    }
}
```

**Failure (503):**

```json
{
    "success": false,
    "message": "Walking route service temporarily unavailable"
}
```

---

## Landmarks

### List All Landmarks

```
GET /api/v1/landmarks
```

🔓 **Public**

**Query Parameters:**

| Parameter | Type   | Required | Description                               |
| --------- | ------ | -------- | ----------------------------------------- |
| search    | string | ❌       | Search by name or description             |
| category  | string | ❌       | Filter by category                        |
| featured  | bool   | ❌       | If `true`, return only featured landmarks |

**Categories:** `city_center`, `mall`, `school`, `hospital`, `transport`, `other`

---

### Get Featured Landmarks

```
GET /api/v1/landmarks/featured
```

🔓 **Public**

---

### Get Landmarks by Category

```
GET /api/v1/landmarks/category/{category}
```

🔓 **Public**

---

### Get Landmark Details

```
GET /api/v1/landmarks/{id}
```

🔓 **Public**

---

### Find Nearby Landmarks

```
POST /api/v1/landmarks/nearby
```

🔓 **Public** — Uses Haversine formula for distance calculation.

| Parameter | Type  | Required | Default | Description         |
| --------- | ----- | -------- | ------- | ------------------- |
| latitude  | float | ✅       | —       | User's latitude     |
| longitude | float | ✅       | —       | User's longitude    |
| radius    | float | ❌       | 2       | Search radius in km |

---

## Support Tickets

### Create Ticket

```
POST /api/v1/support/tickets
```

🔓 **Public** — `user_id` is auto-attached if authenticated.

| Parameter | Type   | Required | Description                                                                                                   |
| --------- | ------ | -------- | ------------------------------------------------------------------------------------------------------------- |
| name      | string | ✅       | Customer's name                                                                                               |
| email     | string | ✅       | Customer's email                                                                                              |
| subject   | string | ✅       | Ticket subject                                                                                                |
| message   | string | ✅       | Ticket message body (10–5000 chars)                                                                           |
| type      | string | ❌       | `general`, `technical`, `billing`, `feedback`, `complaint`, `bug`, `inquiry`, `suggestion`, `report`, `other` |
| priority  | string | ❌       | `low`, `medium` (default), `high`, `urgent`                                                                   |

---

### List Tickets

```
GET /api/v1/support/tickets
```

| Parameter | Type   | Required | Description                   |
| --------- | ------ | -------- | ----------------------------- |
| email     | string | ✅       | Customer's email to filter by |
| status    | string | ❌       | Filter by status              |

> Excludes archived tickets.

---

### Get Ticket Details

```
GET /api/v1/support/tickets/{id}
```

| Parameter | Type   | Required | Description                       |
| --------- | ------ | -------- | --------------------------------- |
| email     | string | ✅       | Customer's email for verification |

Returns ticket with all replies.

---

### Add Message to Ticket

```
POST /api/v1/support/tickets/{id}/message
```

| Parameter | Type   | Required | Description      |
| --------- | ------ | -------- | ---------------- |
| email     | string | ✅       | Customer's email |
| message   | string | ✅       | Message content  |

> Reopens a resolved ticket if the customer sends a new message.

---

### Cancel Ticket

```
PUT /api/v1/support/tickets/{id}/cancel
```

| Parameter | Type   | Required | Description                       |
| --------- | ------ | -------- | --------------------------------- |
| email     | string | ✅       | Customer's email for verification |

---

### Get Support Stats

```
GET /api/v1/support/stats
```

🔒 **Requires:** `Authorization: Bearer <token>`

Returns ticket count breakdown (total/pending/in-progress/resolved) for the authenticated user.

---

## Ticket Notifications

### List Notifications

```
GET /api/v1/support/notifications
```

| Parameter  | Type   | Required | Description           |
| ---------- | ------ | -------- | --------------------- |
| email      | string | ✅       | Customer's email      |
| is_read    | bool   | ❌       | Filter by read status |
| event_type | string | ❌       | Filter by event type  |

**Event types:** `created`, `replied`, `status_changed`, `resolved`, `admin_message`

---

### Get Unread Count

```
GET /api/v1/support/notifications/unread-count
```

| Parameter | Type   | Required | Description      |
| --------- | ------ | -------- | ---------------- |
| email     | string | ✅       | Customer's email |

---

### Mark All as Read

```
PUT /api/v1/support/notifications/mark-all-read
```

| Parameter | Type   | Required | Description      |
| --------- | ------ | -------- | ---------------- |
| email     | string | ✅       | Customer's email |

---

### Mark Single as Read

```
PUT /api/v1/support/notifications/{id}/read
```

---

### Delete Notification

```
DELETE /api/v1/support/notifications/{id}
```

---

## Recent Activities

### List Activities

```
GET /api/v1/recent-activities
```

| Parameter     | Type   | Required | Description                        |
| ------------- | ------ | -------- | ---------------------------------- |
| user_id       | int    | ❌       | Filter by user (or from auth)      |
| limit         | int    | ❌       | Max results (default: 20, max: 50) |
| activity_type | string | ❌       | Filter by activity type            |

**Activity types:** `route_calculated`, `fare_calculated`, `location_search`, `route_saved`, `ticket_created`, `ticket_replied`, `ticket_status_changed`

---

### Create Activity

```
POST /api/v1/recent-activities
```

| Parameter     | Type   | Required | Description                     |
| ------------- | ------ | -------- | ------------------------------- |
| activity_type | string | ✅       | One of the activity types above |
| title         | string | ✅       | Display title (max 255 chars)   |
| subtitle      | string | ❌       | Secondary display text          |
| from_location | string | ❌       | Origin location name            |
| to_location   | string | ❌       | Destination location name       |
| route_names   | string | ❌       | Comma-separated route names     |
| fare          | float  | ❌       | Calculated fare (0–9999.99)     |
| metadata      | object | ❌       | Additional JSON data            |

> **Limit:** Each user can store a maximum of **50 activities**. Oldest are auto-deleted when the limit is exceeded.

---

### Batch Create Activities

```
POST /api/v1/recent-activities/batch
```

| Parameter  | Type  | Required | Description                                  |
| ---------- | ----- | -------- | -------------------------------------------- |
| activities | array | ✅       | Array of activity objects (max 50 per batch) |

---

### Delete Activity

```
DELETE /api/v1/recent-activities/{id}
```

🔒 **Requires:** `Authorization: Bearer <token>` — ownership verified.

---

### Clear All Activities

```
DELETE /api/v1/recent-activities/clear
```

🔒 **Requires:** `Authorization: Bearer <token>`

---

## Caching & Conditional Requests

Several endpoints support HTTP caching to reduce bandwidth and improve performance:

### `If-Modified-Since` / `Last-Modified`

The following endpoints return `Last-Modified` headers and support `If-Modified-Since` conditional GET:

| Endpoint                   | Behavior                                             |
| -------------------------- | ---------------------------------------------------- |
| `GET /api/v1/routes`       | Returns `304` if no routes updated since header      |
| `GET /api/v1/routes/{id}`  | Returns `304` if route not updated since header      |
| `GET /api/v1/routes/paths` | Returns `304` if no route paths changed since header |

### Delta Sync (`?since=`)

```
GET /api/v1/routes/paths?since=2026-02-20T10:00:00Z
```

Returns only routes modified after the given timestamp, enabling the Flutter app to efficiently sync incremental changes without re-downloading all route data.

### Walking Route Cache

Walking route results from ORS/OSRM are cached server-side for **1 hour** with coordinate-precision cache keys (5 decimal places ≈ 1.1m accuracy).

---

## Error Responses

All errors follow a consistent format:

```json
{
    "success": false,
    "message": "Error description"
}
```

| Status Code | Meaning                                 |
| ----------- | --------------------------------------- |
| 200         | Success                                 |
| 201         | Created                                 |
| 304         | Not Modified (conditional request)      |
| 400         | Bad Request — invalid parameters        |
| 401         | Unauthorized — missing or invalid token |
| 403         | Forbidden — wrong role                  |
| 404         | Not Found                               |
| 422         | Validation Error                        |
| 429         | Too Many Requests (rate limited)        |
| 500         | Server Error                            |
| 503         | Service Unavailable (external API down) |

### Validation Error Detail

```json
{
    "message": "The email field is required.",
    "errors": {
        "email": ["The email field is required."]
    }
}
```
