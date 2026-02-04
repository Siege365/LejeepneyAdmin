# 📱 Flutter AI - API Integration Answers

## 🎯 Comprehensive Answers to Your Questions

---

## 1. ✅ Ticket Notifications Endpoint

**YES**, the admin system now has a full notifications system implemented!

### **Endpoints Available:**

#### **GET `/api/v1/support/notifications`**

Get all notifications for a user (authenticated or by email)

**Query Parameters:**

- `email` (optional if authenticated): User's email address
- `is_read` (optional): Filter by read status (true/false)
- `event_type` (optional): Filter by event type
- `days` (optional): Limit to last N days (default: 30)
- `per_page` (optional): Pagination limit (default: 20)

**Request Example:**

```http
GET /api/v1/support/notifications?email=user@example.com&is_read=false
```

**Response:**

```json
{
    "success": true,
    "data": [
        {
            "id": 1,
            "ticket_id": 123,
            "user_email": "user@example.com",
            "event_type": "admin_message",
            "title": "New Reply from Support Team",
            "message": "John Admin replied to your ticket 'Login Issue'",
            "metadata": {
                "admin_name": "John Admin",
                "reply_preview": "We've identified the issue and are working on a fix..."
            },
            "is_read": false,
            "read_at": null,
            "created_at": "2026-02-04T12:30:00.000000Z",
            "updated_at": "2026-02-04T12:30:00.000000Z",
            "ticket": {
                "id": 123,
                "subject": "Login Issue",
                "status": "in-progress"
            }
        }
    ],
    "meta": {
        "current_page": 1,
        "last_page": 1,
        "per_page": 20,
        "total": 1,
        "unread_count": 5
    }
}
```

---

#### **GET `/api/v1/support/notifications/unread-count`**

Get unread notification count

**Query Parameters:**

- `email` (optional if authenticated): User's email address

**Request Example:**

```http
GET /api/v1/support/notifications/unread-count?email=user@example.com
```

**Response:**

```json
{
    "success": true,
    "unread_count": 5
}
```

---

#### **PUT `/api/v1/support/notifications/{id}/read`**

Mark a single notification as read

**Request Example:**

```http
PUT /api/v1/support/notifications/15/read?email=user@example.com
```

**Response:**

```json
{
    "success": true,
    "message": "Notification marked as read"
}
```

---

#### **PUT `/api/v1/support/notifications/mark-all-read`**

Mark all notifications as read for the user

**Query Parameters:**

- `email` (required if not authenticated): User's email address

**Request Example:**

```http
PUT /api/v1/support/notifications/mark-all-read?email=user@example.com
```

**Response:**

```json
{
    "success": true,
    "message": "3 notification(s) marked as read",
    "updated_count": 3
}
```

---

#### **DELETE `/api/v1/support/notifications/{id}`**

Delete a notification

**Request Example:**

```http
DELETE /api/v1/support/notifications/15?email=user@example.com
```

**Response:**

```json
{
    "success": true,
    "message": "Notification deleted successfully"
}
```

---

### **Event Types:**

The system automatically creates notifications for these events:

| Event Type       | Triggered When                 | Example Title                 |
| ---------------- | ------------------------------ | ----------------------------- |
| `created`        | User creates a ticket          | "Support Ticket Created"      |
| `admin_message`  | Admin replies to ticket        | "New Reply from Support Team" |
| `status_changed` | Admin changes ticket status    | "Ticket Status Updated"       |
| `resolved`       | Admin marks ticket as resolved | "Ticket Resolved"             |

---

### **Metadata Structure:**

Each notification includes a `metadata` JSON field with context:

**For `created` event:**

```json
{
    "ticket_id": 123,
    "subject": "Login Issue"
}
```

**For `admin_message` event:**

```json
{
    "admin_name": "John Admin",
    "reply_preview": "We've identified the issue and are working..."
}
```

**For `status_changed` or `resolved` event:**

```json
{
    "old_status": "pending",
    "new_status": "in-progress",
    "admin_name": "John Admin"
}
```

---

## 2. 🔄 Real-time Chat/Polling

**POLLING is the recommended approach** - no WebSocket currently implemented.

### **Recommended Strategy:**

#### **Option A: Polling for Ticket Details (Recommended)**

Use the existing endpoint to poll for new replies:

```dart
// Poll every 10-15 seconds when user is viewing a ticket
Timer.periodic(Duration(seconds: 10), (timer) async {
  final response = await http.get(
    Uri.parse('$baseUrl/api/v1/support/tickets/$ticketId?email=$userEmail')
  );

  if (response.statusCode == 200) {
    final data = jsonDecode(response.body);
    // Update UI with new replies
    updateReplies(data['data']['replies']);
  }
});
```

**Endpoint:** `GET /api/v1/support/tickets/{id}`

- Returns full ticket with all replies
- Replies are ordered by `created_at` ascending
- Check if new replies exist by comparing with local cache

---

#### **Option B: Notification-Based (More Efficient)**

Poll the notifications endpoint for new activity:

```dart
// Poll every 30-60 seconds in background
Timer.periodic(Duration(seconds: 30), (timer) async {
  final response = await http.get(
    Uri.parse('$baseUrl/api/v1/support/notifications/unread-count?email=$userEmail')
  );

  if (response.statusCode == 200) {
    final data = jsonDecode(response.body);
    int unreadCount = data['unread_count'];

    if (unreadCount > 0) {
      // Fetch notifications and show badge/alert
      showNotificationBadge(unreadCount);
    }
  }
});
```

**Advantages:**

- Less bandwidth (only count check)
- User gets notified across all tickets
- Can show notification badge on app icon

---

#### **Option C: Combined Strategy (Best UX)**

1. **Background Polling** (every 60s): Check unread notification count
2. **Active Ticket Polling** (every 10s): When user is viewing a ticket, poll for new replies
3. **On Resume**: Refresh notifications when app comes to foreground

```dart
// Background polling for notifications
void startBackgroundPolling() {
  _notificationTimer = Timer.periodic(Duration(seconds: 60), (_) {
    checkUnreadNotifications();
  });
}

// Active polling when viewing ticket
void startTicketPolling(int ticketId) {
  _ticketTimer?.cancel();
  _ticketTimer = Timer.periodic(Duration(seconds: 10), (_) {
    fetchTicketReplies(ticketId);
  });
}

// Stop polling when leaving ticket view
@override
void dispose() {
  _ticketTimer?.cancel();
  super.dispose();
}
```

---

### **No Separate Messages Endpoint**

Currently, there is **no dedicated `/api/v1/support/tickets/{id}/messages` endpoint**.

The `GET /api/v1/support/tickets/{id}` endpoint returns:

```json
{
    "data": {
        "id": 123,
        "subject": "Login Issue",
        "message": "Initial ticket message...",
        "replies": [
            {
                "id": 1,
                "message": "Admin reply...",
                "admin_name": "John Admin",
                "created_at": "2026-02-04T12:30:00.000000Z"
            }
        ]
    }
}
```

**Note:** The `replies` array contains all conversation messages from admin.

---

## 3. 🔔 Status Change Notifications

**YES, notifications are automatically created** when admin changes status!

### **Backend Handles Everything:**

When an admin:

- **Changes status** → Notification created with `event_type: "status_changed"`
- **Marks as resolved** → Notification created with `event_type: "resolved"`
- **Replies to ticket** → Notification created with `event_type: "admin_message"`

### **Mobile App Responsibility:**

Your app only needs to:

1. **Poll notifications** using `/api/v1/support/notifications`
2. **Display notifications** to the user
3. **Mark as read** when user views them

### **Example Flow:**

```
Admin Action → Backend creates notification → Mobile polls API → Shows alert/badge
```

**You don't need to manually create notifications** - they're auto-generated server-side!

---

## 4. 🌐 Current Base URL

### **Checking Your Base URL:**

Look in your Flutter project's `lib/services/api_service.dart` or similar file:

```dart
class ApiService {
  static const String baseUrl = 'YOUR_BASE_URL_HERE';
  // Examples:
  // 'http://localhost:8000'
  // 'http://192.168.1.100:8000'
  // 'https://api.lejeepney.com'
}
```

### **Recommended Base URLs:**

**For Development:**

- **Android Emulator:** `http://10.0.2.2:8000` (maps to host machine)
- **iOS Simulator:** `http://localhost:8000` or `http://127.0.0.1:8000`
- **Physical Device (same network):** `http://YOUR_COMPUTER_IP:8000`
    - Find your IP: Run `ipconfig` (Windows) or `ifconfig` (Mac/Linux)
    - Example: `http://192.168.1.105:8000`

**For Production:**

```dart
static const String baseUrl = 'https://api.lejeepney.com';
```

### **Full Endpoint Examples:**

```dart
// Tickets
final ticketsUrl = '$baseUrl/api/v1/support/tickets';

// Notifications
final notificationsUrl = '$baseUrl/api/v1/support/notifications';

// Specific ticket
final ticketUrl = '$baseUrl/api/v1/support/tickets/$ticketId';
```

---

## 5. 🛡️ Duplicate Submission Prevention

### **Backend Status:** ❌ No idempotency key support currently

### **Recommended Client-Side Prevention:**

#### **Strategy 1: Debouncing with State Management**

```dart
class TicketSubmissionController {
  bool _isSubmitting = false;

  Future<void> submitTicket(TicketData ticket) async {
    if (_isSubmitting) {
      print('Submission already in progress');
      return;
    }

    _isSubmitting = true;

    try {
      final response = await http.post(
        Uri.parse('$baseUrl/api/v1/support/tickets'),
        headers: {'Content-Type': 'application/json'},
        body: jsonEncode(ticket.toJson())
      );

      // Handle response...
    } finally {
      _isSubmitting = false;
    }
  }
}
```

---

#### **Strategy 2: Button State Disable**

```dart
class SubmitTicketButton extends StatefulWidget {
  @override
  _SubmitTicketButtonState createState() => _SubmitTicketButtonState();
}

class _SubmitTicketButtonState extends State<SubmitTicketButton> {
  bool _isLoading = false;

  Future<void> _handleSubmit() async {
    if (_isLoading) return;

    setState(() => _isLoading = true);

    try {
      await submitTicket();
      // Navigate to success screen
    } catch (e) {
      // Show error
    } finally {
      if (mounted) {
        setState(() => _isLoading = false);
      }
    }
  }

  @override
  Widget build(BuildContext context) {
    return ElevatedButton(
      onPressed: _isLoading ? null : _handleSubmit,
      child: _isLoading
        ? CircularProgressIndicator()
        : Text('Submit Ticket'),
    );
  }
}
```

---

#### **Strategy 3: Debounce Timer**

```dart
import 'package:flutter/material.dart';
import 'dart:async';

class DebouncedSubmit {
  Timer? _debounceTimer;

  void submit(VoidCallback action, {Duration delay = const Duration(seconds: 2)}) {
    if (_debounceTimer?.isActive ?? false) {
      print('Debounced - ignoring duplicate submit');
      return;
    }

    action();

    _debounceTimer = Timer(delay, () {
      // Cooldown complete
    });
  }

  void dispose() {
    _debounceTimer?.cancel();
  }
}

// Usage:
final debouncedSubmit = DebouncedSubmit();

onPressed: () {
  debouncedSubmit.submit(() async {
    await submitTicket();
  });
}
```

---

#### **Strategy 4: Local Submission Cache**

```dart
import 'package:shared_preferences/shared_preferences.dart';

class SubmissionCache {
  static const String _lastSubmitKey = 'last_ticket_submit';
  static const int _cooldownSeconds = 10;

  Future<bool> canSubmit() async {
    final prefs = await SharedPreferences.getInstance();
    final lastSubmit = prefs.getInt(_lastSubmitKey) ?? 0;
    final now = DateTime.now().millisecondsSinceEpoch;

    // Check if 10 seconds have passed
    if (now - lastSubmit < _cooldownSeconds * 1000) {
      return false; // Too soon
    }

    return true;
  }

  Future<void> markSubmitted() async {
    final prefs = await SharedPreferences.getInstance();
    await prefs.setInt(_lastSubmitKey, DateTime.now().millisecondsSinceEpoch);
  }
}

// Usage:
if (await submissionCache.canSubmit()) {
  await submitTicket();
  await submissionCache.markSubmitted();
} else {
  showSnackbar('Please wait before submitting again');
}
```

---

## 📊 Complete API Reference

### **Support Tickets**

| Method | Endpoint                               | Auth               | Description                     |
| ------ | -------------------------------------- | ------------------ | ------------------------------- |
| POST   | `/api/v1/support/tickets`              | No                 | Create new ticket               |
| GET    | `/api/v1/support/tickets`              | Email required     | Get user's tickets              |
| GET    | `/api/v1/support/tickets/{id}`         | Email required     | Get ticket details with replies |
| POST   | `/api/v1/support/tickets/{id}/message` | Email required     | Add follow-up message           |
| GET    | `/api/v1/support/stats`                | Yes (auth:sanctum) | Get ticket statistics           |

### **Notifications**

| Method | Endpoint                                      | Auth           | Description           |
| ------ | --------------------------------------------- | -------------- | --------------------- |
| GET    | `/api/v1/support/notifications`               | Email required | Get all notifications |
| GET    | `/api/v1/support/notifications/unread-count`  | Email required | Get unread count      |
| PUT    | `/api/v1/support/notifications/{id}/read`     | Email required | Mark single as read   |
| PUT    | `/api/v1/support/notifications/mark-all-read` | Email required | Mark all as read      |
| DELETE | `/api/v1/support/notifications/{id}`          | Email required | Delete notification   |

---

## 🚀 Recommended Implementation Flow

### **1. Ticket Creation**

```dart
// Submit ticket
final response = await http.post(
  Uri.parse('$baseUrl/api/v1/support/tickets'),
  body: jsonEncode({
    'name': 'John Doe',
    'email': 'john@example.com',
    'subject': 'Login Issue',
    'message': 'Cannot log into my account...',
    'type': 'technical',
    'priority': 'high'
  })
);
// User receives automatic notification: "Support Ticket Created"
```

### **2. Polling for Updates**

```dart
// When viewing ticket
Timer.periodic(Duration(seconds: 10), (timer) async {
  final ticket = await fetchTicket(ticketId);
  updateUI(ticket);
});
```

### **3. Notification Checking**

```dart
// Background check (every 60s)
final count = await getUnreadNotificationCount(userEmail);
updateBadge(count);
```

### **4. Mark Notifications as Read**

```dart
// When user opens notification panel
await markAllNotificationsAsRead(userEmail);
```

---

## ⚙️ Rate Limiting

All `/api/v1/*` endpoints have:

- **60 requests per minute** throttling
- Returns `429 Too Many Requests` if exceeded

**Recommendation:** Implement exponential backoff in your polling logic.

---

## 🎨 Best Practices

1. **Polling Intervals:**
    - Active ticket view: 10-15 seconds
    - Background notifications: 60 seconds
    - App foreground resume: Immediate refresh

2. **Error Handling:**
    - Handle 429 rate limits gracefully
    - Implement retry logic with backoff
    - Show offline indicators when API is unreachable

3. **Notification Management:**
    - Show badge with unread count
    - Mark as read when notification is viewed
    - Allow swipe-to-dismiss with delete API call

4. **Duplicate Prevention:**
    - Disable submit button during API call
    - Use debounce timer (2-3 seconds)
    - Track last submission timestamp locally

---

## 📝 Quick Start Code Snippet

```dart
import 'package:http/http.dart' as http;
import 'dart:convert';

class SupportApiService {
  static const baseUrl = 'http://YOUR_IP:8000';

  // Get notifications
  Future<Map<String, dynamic>> getNotifications(String email) async {
    final response = await http.get(
      Uri.parse('$baseUrl/api/v1/support/notifications?email=$email')
    );
    return jsonDecode(response.body);
  }

  // Get unread count
  Future<int> getUnreadCount(String email) async {
    final response = await http.get(
      Uri.parse('$baseUrl/api/v1/support/notifications/unread-count?email=$email')
    );
    final data = jsonDecode(response.body);
    return data['unread_count'];
  }

  // Mark as read
  Future<void> markAsRead(String email, int notificationId) async {
    await http.put(
      Uri.parse('$baseUrl/api/v1/support/notifications/$notificationId/read?email=$email')
    );
  }

  // Get ticket with replies
  Future<Map<String, dynamic>> getTicket(int ticketId, String email) async {
    final response = await http.get(
      Uri.parse('$baseUrl/api/v1/support/tickets/$ticketId?email=$email')
    );
    return jsonDecode(response.body);
  }
}
```

---

## ✅ Summary

| Feature                     | Status             | Implementation             |
| --------------------------- | ------------------ | -------------------------- |
| Notifications API           | ✅ Implemented     | 5 endpoints ready          |
| Auto-notification on events | ✅ Implemented     | Backend handles all        |
| Real-time updates           | ⚠️ Use polling     | No WebSocket               |
| Base URL                    | ℹ️ Check your code | Use local IP for dev       |
| Idempotency keys            | ❌ Not implemented | Use client-side prevention |

---

**Questions?** The backend is ready - start implementing! 🚀
