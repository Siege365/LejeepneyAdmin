# 🔒 Security

Overview of all security measures implemented in this project.

---

## SQL Injection Protection

All database queries use **Laravel Eloquent ORM** with parameterized queries. No raw SQL is used with user input.

```php
// ✅ Safe — Eloquent parameterized query
$routes = JeepneyRoute::where('name', 'like', "%{$escapedSearch}%")->get();

// ❌ Never used — raw unsanitized query
DB::select("SELECT * FROM routes WHERE name = '$input'");
```

**LIKE Wildcard Escaping** — Search inputs are sanitized to prevent wildcard injection:

```php
$search = str_replace(['%', '_'], ['\\%', '\\_'], $request->search);
```

Applied in: `RouteController`, `LandmarkController`, `CustomerServiceController`

---

## Cross-Site Scripting (XSS) Prevention

### Blade Auto-Escaping

All Blade template output uses `{{ }}` which auto-escapes HTML entities:

```blade
{{ $ticket->subject }}  {{-- Escaped output --}}
```

### Input Sanitization

User inputs are sanitized with `strip_tags()` (no allowed tags):

```php
$validated['subject'] = strip_tags($validated['subject']);
$validated['message'] = strip_tags($validated['message']);
```

---

## Cross-Site Request Forgery (CSRF)

All web forms include CSRF tokens via Blade:

```blade
@csrf
```

API routes use **Sanctum token authentication** instead of CSRF.

---

## Authentication

### Web (Admin Panel)

- Session-based authentication via Laravel's built-in auth
- Only `role = 'admin'` users can access the admin panel
- Middleware: `['auth', 'admin']` on all admin routes

### API (Mobile App)

- **Laravel Sanctum** token-based authentication
- Tokens issued on login: `$user->createToken('auth_token')`
- Protected routes require `auth:sanctum` middleware

---

## Rate Limiting

### Login Brute Force Protection

```php
// web.php
Route::post('/login', [AuthController::class, 'login'])->middleware('throttle:5,1');

// api.php
Route::post('/login', ...)->middleware('throttle:5,1');
```

- **5 attempts per minute** on login endpoints
- Returns `429 Too Many Requests` when exceeded

### API Throttling

```php
Route::middleware(['auth:sanctum', 'throttle:60,1'])->group(function () {
    // All API v1 routes — 60 requests per minute
});
```

---

## Mass Assignment Protection

The `User` model restricts fillable fields:

```php
protected $fillable = ['name', 'email', 'password', 'phone'];
```

The `role` field is **NOT** in `$fillable` — it must be set explicitly:

```php
$user = User::create($validated);
$user->role = 'admin';
$user->save();
```

This prevents users from escalating privileges by injecting `role=admin` into requests.

---

## Error Message Handling

Production error responses use **generic messages** — internal exception details are never exposed:

```php
// ✅ Safe — generic message
return response()->json(['message' => 'Unable to process request'], 500);

// ❌ Removed — exposes internals
return response()->json(['message' => $e->getMessage()], 500);
```

---

## Sensitive Data in Logs

Request data is **NOT** dumped to logs. Specific fields are logged instead:

```php
// ✅ Safe
Log::info('Ticket created', ['ticket_id' => $ticket->id]);

// ❌ Removed — may log passwords, tokens
Log::info('Request', $request->all());
```

---

## Session Security

Production `.env` settings:

```env
SESSION_DRIVER=database
SESSION_ENCRYPT=true
SESSION_SECURE_COOKIE=true
```

- Sessions stored in database (not files)
- Encrypted session data
- Cookies only sent over HTTPS

---

## EmailJS Credentials

API keys are stored in `.env`, **never hardcoded** in JavaScript:

```env
EMAILJS_PUBLIC_KEY=your_public_key
EMAILJS_SERVICE_ID=your_service_id
EMAILJS_TEMPLATE_ID=your_template_id
```

Passed to frontend via `config('services.emailjs.*')` in Blade templates:

```blade
window.EMAILJS_PUBLIC_KEY = '{{ config("services.emailjs.public_key") }}';
```

---

## Environment File

The `.env` file is **never committed** to version control (listed in `.gitignore`).

A `.env.production` template is provided as a reference. See [Deployment Guide](deployment.md) for production env setup.

---

## Security Checklist

| ✅  | Measure                              | Status              |
| --- | ------------------------------------ | ------------------- |
| ✅  | Parameterized SQL queries (Eloquent) | Implemented         |
| ✅  | LIKE wildcard escaping               | Implemented         |
| ✅  | Blade auto-escaping (XSS)            | Implemented         |
| ✅  | strip_tags input sanitization        | Implemented         |
| ✅  | CSRF protection on forms             | Implemented         |
| ✅  | Login rate limiting (5/min)          | Implemented         |
| ✅  | API rate limiting (60/min)           | Implemented         |
| ✅  | Mass assignment protection           | Implemented         |
| ✅  | Generic error messages               | Implemented         |
| ✅  | No sensitive data in logs            | Implemented         |
| ✅  | Encrypted sessions                   | Configured for prod |
| ✅  | Secure cookies (HTTPS only)          | Configured for prod |
| ✅  | API keys in .env                     | Implemented         |
| ✅  | .env excluded from git               | Confirmed           |
