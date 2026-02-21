# 🔒 Security

Overview of all security measures implemented in the LeJeepney Admin system.

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

Applied in: `RouteController`, `LandmarkController`, `CustomerServiceController`, `AuditTrailController`

---

## Cross-Site Scripting (XSS) Prevention

### Blade Auto-Escaping

All Blade template output uses `{{ }}` which auto-escapes HTML entities:

```blade
{{ $ticket->subject }}  {{-- Escaped output --}}
```

### Input Sanitization

User inputs are sanitized with `strip_tags()`:

```php
$validated['subject'] = strip_tags($validated['subject']);
$validated['message'] = strip_tags($validated['message']);
```

Applied on: support ticket creation, ticket replies, customer messages.

---

## Cross-Site Request Forgery (CSRF)

All web forms include CSRF tokens via Blade:

```blade
@csrf
```

API routes use **Sanctum token authentication** instead of CSRF.

---

## Authentication & Authorization

### Web (Admin Panel)

- Session-based authentication via Laravel's built-in auth
- Only `role = 'admin'` users can access the admin panel
- Middleware chain: `['auth', 'admin']` on all admin routes
- `AdminMiddleware` checks `auth()->user()->role !== 'admin'` → aborts 403
- Session invalidation and CSRF token regeneration on logout

### API (Mobile App)

- **Laravel Sanctum** token-based authentication
- Tokens issued on login/register: `$user->createToken('auth-token')`
- Tokens expire after **30 days** (configurable in `config/sanctum.php`)
- Protected routes require `auth:sanctum` middleware
- **Role separation:** Only `role = 'user'` accounts can log in via the API. Admin accounts receive HTTP 403.
- Logout deletes only the current access token (not all user tokens)

### Role Enforcement

| Role    | Web Panel Access | API Login | Admin Routes | API Routes |
| ------- | ---------------- | --------- | ------------ | ---------- |
| `admin` | ✅               | ❌ (403)  | ✅           | ❌         |
| `user`  | ❌ (rejected)    | ✅        | ❌           | ✅         |

---

## Rate Limiting

### Login Brute Force Protection

```php
// web.php — Admin login
Route::post('/login', ...)->middleware('throttle:5,1');   // 5 attempts/minute

// api.php — Mobile login
Route::post('/login', ...)->middleware('throttle:5,1');    // 5 attempts/minute
```

### API Global Throttle

```php
// All /api/v1/* routes
Route::prefix('v1')->middleware('throttle:60,1')->group(...);  // 60 requests/minute
```

### Password Reset Rate Limiting (Application-Level)

The API password reset endpoints use **dual-layer rate limiting**:

1. **Global middleware:** `throttle:5,1` (5 requests/minute per IP)
2. **Per-email application rate limiter** via `RateLimiter` facade:

```php
// Forgot endpoint — 3 requests per hour per email
$rateLimitKey = 'password-forgot:' . $email;
RateLimiter::tooManyAttempts($rateLimitKey, 3);
RateLimiter::hit($rateLimitKey, 3600); // 1 hour decay

// Reset endpoint — 5 attempts per hour per email
$rateLimitKey = 'password-reset:' . $email;
RateLimiter::tooManyAttempts($rateLimitKey, 5);
RateLimiter::hit($rateLimitKey, 3600); // 1 hour decay
```

Both rate limiters are **cleared on successful password reset** to allow legitimate users to log in immediately.

### Web Password Reset Rate Limiting

```php
// Forgot password — 3 per minute
Route::post('/forgot-password', ...)->middleware('throttle:3,1');

// Reset password — 3 per minute
Route::post('/reset-password', ...)->middleware('throttle:3,1');
```

---

## Password Reset Security

### Web Admin (Link-Based)

- Uses Laravel's built-in `Password::sendResetLink()` with cryptographic tokens
- Token expires after **60 minutes** (Laravel default)
- Hashed token stored in `password_reset_tokens` table
- Token consumed (deleted) after successful reset

### Mobile API (6-Digit Code)

- Random 6-digit code generated via `random_int(0, 999999)` (CSPRNG)
- Code stored as `Hash::make($code)` — only the hash is in the database
- Verified via `Hash::check($code, $storedHash)` — timing-safe comparison
- Code expires after **15 minutes** (`created_at` + 15 min check)
- Code consumed (deleted) after successful reset
- **Anti-enumeration:** Same success response returned whether email exists or not

```php
// Always returns this, even if email doesn't exist
return response()->json([
    'success' => true,
    'message' => 'If an account with that email exists, a reset code has been sent.',
]);
```

- **Role restriction:** Only `role = 'user'` accounts receive reset codes. Admin accounts are silently ignored.

---

## Mass Assignment Protection

All models restrict fillable fields via `$fillable`:

```php
// User model
protected $fillable = ['name', 'email', 'password', 'phone'];
```

The `role` field is **NOT** in `$fillable` — it must be set explicitly via `forceFill()` or direct assignment:

```php
$user = User::create($validated);
$user->role = 'admin';
$user->save();
```

This prevents privilege escalation by injecting `role=admin` into requests.

---

## Password Hashing

- All passwords use `Hash::make()` (bcrypt by default in Laravel)
- Password reset codes are also hashed before storage
- Password validation enforced on API reset: min 8 chars, lowercase + uppercase + number

```php
'password' => [
    'required', 'string', 'min:8', 'confirmed',
    'regex:/[a-z]/',    // at least one lowercase
    'regex:/[A-Z]/',    // at least one uppercase
    'regex:/[0-9]/',    // at least one number
],
```

---

## Error Message Handling

Production error responses use **generic messages** — internal exception details are never exposed:

```php
// ✅ Safe — generic message
return response()->json(['message' => 'Unable to process request'], 500);

// ❌ Never used — exposes internals
return response()->json(['message' => $e->getMessage()], 500);
```

---

## Sensitive Data in Logs

Request data is **NOT** dumped to logs. Only specific fields are logged:

```php
// ✅ Safe — specific fields
Log::info('Password reset code sent', ['email' => $email]);

// ❌ Never used — may log passwords/tokens
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
- Cookies only sent over HTTPS in production

---

## API Key Management

All API keys and secrets are stored in `.env`, never hardcoded:

| Key           | Purpose                         | Used By               |
| ------------- | ------------------------------- | --------------------- |
| `ORS_API_KEY` | OpenRouteService walking routes | `WalkingRouteService` |
| `APP_KEY`     | Encryption / hashing            | Laravel framework     |
| `MAIL_*`      | SMTP credentials                | Laravel Mail          |

Passed to services via `config()`:

```php
$apiKey = config('services.openrouteservice.api_key');
```

---

## Environment File

The `.env` file is **never committed** to version control (listed in `.gitignore`).

---

## Security Checklist

| ✅  | Measure                                   | Status      |
| --- | ----------------------------------------- | ----------- |
| ✅  | Parameterized SQL queries (Eloquent)      | Implemented |
| ✅  | LIKE wildcard escaping                    | Implemented |
| ✅  | Blade auto-escaping (XSS)                 | Implemented |
| ✅  | strip_tags input sanitization             | Implemented |
| ✅  | CSRF protection on web forms              | Implemented |
| ✅  | Login rate limiting (5/min)               | Implemented |
| ✅  | API rate limiting (60/min)                | Implemented |
| ✅  | Password reset rate limiting (per-email)  | Implemented |
| ✅  | Mass assignment protection                | Implemented |
| ✅  | Password hashing (bcrypt)                 | Implemented |
| ✅  | Reset code hashing + timing-safe compare  | Implemented |
| ✅  | Anti-email-enumeration                    | Implemented |
| ✅  | Role-based access control (admin vs user) | Implemented |
| ✅  | Generic error messages in production      | Implemented |
| ✅  | No sensitive data in logs                 | Implemented |
| ✅  | Encrypted sessions (production)           | Configured  |
| ✅  | Secure cookies / HTTPS-only (production)  | Configured  |
| ✅  | API keys in .env only                     | Implemented |
| ✅  | .env excluded from git                    | Confirmed   |
| ✅  | Sanctum token expiration (30 days)        | Configured  |
| ✅  | Session invalidation on logout            | Implemented |
