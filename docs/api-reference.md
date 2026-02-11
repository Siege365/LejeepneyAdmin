# 📡 API Reference

REST API endpoints for the **LeJeepney Flutter mobile app**.

**Base URL:** `https://yourdomain.com/api`

**Rate Limit:** All v1 endpoints are rate-limited to **60 requests per minute**.

---

## Table of Contents

- [Authentication](#authentication)
- [Settings](#settings)
- [Routes](#routes)
- [Landmarks](#landmarks)
- [Support Tickets](#support-tickets)
- [Ticket Notifications](#ticket-notifications)
- [Recent Activities](#recent-activities)

---

## Authentication

### Register

```
POST /api/register
```

| Parameter             | Type   | Required | Description          |
| --------------------- | ------ | -------- | -------------------- |
| name                  | string | ✅       | User's full name     |
| email                 | string | ✅       | Valid email address  |
| password              | string | ✅       | Minimum 8 characters |
| password_confirmation | string | ✅       | Must match password  |

**Response:**

```json
{
    "user": { "id": 1, "name": "John", "email": "john@example.com" },
    "token": "1|abc123..."
}
```

---

### Login

```
POST /api/login
```

_Rate limited: 5 attempts per minute_

| Parameter | Type   | Required | Description      |
| --------- | ------ | -------- | ---------------- |
| email     | string | ✅       | Registered email |
| password  | string | ✅       | Account password |

**Response:**

```json
{
    "user": { "id": 1, "name": "John", "email": "john@example.com" },
    "token": "1|abc123..."
}
```

---

### Get Current User

```
GET /api/user
```

🔒 **Requires:** `Authorization: Bearer <token>`

---

### Logout

```
POST /api/logout
```

🔒 **Requires:** `Authorization: Bearer <token>`

---

## Settings

### Get Public Settings

```
GET /api/v1/settings
```

🔓 **Public** — No authentication required.

Returns all app settings marked as public (e.g., fare configuration).

**Response:**

```json
{
    "success": true,
    "data": {
        "base_fare": 13.0,
        "fare_per_km": 1.8
    }
}
```

| Field       | Type  | Description                                  |
| ----------- | ----- | -------------------------------------------- |
| base_fare   | float | Minimum fare charged for jeepney rides (PHP) |
| fare_per_km | float | Additional fare per kilometer traveled (PHP) |

> Values are automatically type-cast (numbers returned as floats, not strings).

---

## Routes

### List All Routes

```
GET /api/v1/routes
```

**Query Parameters:**

| Parameter | Type   | Required | Description                  |
| --------- | ------ | -------- | ---------------------------- |
| search    | string | ❌       | Search by name, route number |
| status    | string | ❌       | Filter: `active`, `inactive` |

---

### Get Route Details

```
GET /api/v1/routes/{id}
```

---

### Get All Route Paths (Map Data)

```
GET /api/v1/routes/paths
```

Returns all routes with their coordinate paths for map rendering.

---

### Find Routes Between Points

```
POST /api/v1/routes/find
```

| Parameter | Type  | Required | Description           |
| --------- | ----- | -------- | --------------------- |
| start_lat | float | ✅       | Starting latitude     |
| start_lng | float | ✅       | Starting longitude    |
| end_lat   | float | ✅       | Destination latitude  |
| end_lng   | float | ✅       | Destination longitude |

---

## Landmarks

### List All Landmarks

```
GET /api/v1/landmarks
```

**Query Parameters:**

| Parameter | Type   | Required | Description                 |
| --------- | ------ | -------- | --------------------------- |
| search    | string | ❌       | Search by name, description |
| category  | string | ❌       | Filter by category          |

---

### Get Featured Landmarks

```
GET /api/v1/landmarks/featured
```

---

### Get Landmarks by Category

```
GET /api/v1/landmarks/category/{category}
```

Categories: `mall`, `school`, `church`, `hospital`, `government`, `terminal`, `park`, `market`, `hotel`, `restaurant`, `other`

---

### Get Landmark Details

```
GET /api/v1/landmarks/{id}
```

---

### Find Nearby Landmarks

```
POST /api/v1/landmarks/nearby
```

| Parameter | Type  | Required | Description               |
| --------- | ----- | -------- | ------------------------- |
| latitude  | float | ✅       | User's latitude           |
| longitude | float | ✅       | User's longitude          |
| radius    | float | ❌       | Radius in km (default: 1) |

---

## Support Tickets

### Create Ticket

```
POST /api/v1/support/tickets
```

| Parameter | Type   | Required | Description         |
| --------- | ------ | -------- | ------------------- |
| name      | string | ✅       | Customer's name     |
| email     | string | ✅       | Customer's email    |
| subject   | string | ✅       | Ticket subject      |
| message   | string | ✅       | Ticket message body |

---

### List Tickets

```
GET /api/v1/support/tickets
```

| Parameter | Type   | Required | Description                   |
| --------- | ------ | -------- | ----------------------------- |
| email     | string | ✅       | Customer's email to filter by |

---

### Get Ticket Details

```
GET /api/v1/support/tickets/{id}
```

| Parameter | Type   | Required | Description                       |
| --------- | ------ | -------- | --------------------------------- |
| email     | string | ✅       | Customer's email for verification |

---

### Add Message to Ticket

```
POST /api/v1/support/tickets/{id}/message
```

| Parameter | Type   | Required | Description      |
| --------- | ------ | -------- | ---------------- |
| email     | string | ✅       | Customer's email |
| message   | string | ✅       | Message content  |

---

### Cancel Ticket

```
PUT /api/v1/support/tickets/{id}/cancel
```

| Parameter | Type   | Required | Description                       |
| --------- | ------ | -------- | --------------------------------- |
| email     | string | ✅       | Customer's email for verification |

Changes the ticket status to `cancelled`.

---

### Get Support Stats

```
GET /api/v1/support/stats
```

🔒 **Requires:** `Authorization: Bearer <token>`

---

## Ticket Notifications

### List Notifications

```
GET /api/v1/support/notifications
```

| Parameter | Type   | Required | Description      |
| --------- | ------ | -------- | ---------------- |
| email     | string | ✅       | Customer's email |

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

| Parameter | Type   | Required | Description          |
| --------- | ------ | -------- | -------------------- |
| email     | string | ❌       | Filter by user email |

---

### Create Activity

```
POST /api/v1/recent-activities
```

| Parameter   | Type   | Required | Description          |
| ----------- | ------ | -------- | -------------------- |
| action      | string | ✅       | Activity type        |
| description | string | ✅       | Activity description |

---

### Batch Create Activities

```
POST /api/v1/recent-activities/batch
```

| Parameter  | Type  | Required | Description               |
| ---------- | ----- | -------- | ------------------------- |
| activities | array | ✅       | Array of activity objects |

---

### Delete Activity

```
DELETE /api/v1/recent-activities/{id}
```

🔒 **Requires:** `Authorization: Bearer <token>`

---

### Clear All Activities

```
DELETE /api/v1/recent-activities/clear
```

🔒 **Requires:** `Authorization: Bearer <token>`

---

## Error Responses

All errors follow this format:

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
| 400         | Bad Request — invalid parameters        |
| 401         | Unauthorized — missing or invalid token |
| 404         | Not Found                               |
| 422         | Validation Error                        |
| 429         | Too Many Requests (rate limited)        |
| 500         | Server Error                            |
