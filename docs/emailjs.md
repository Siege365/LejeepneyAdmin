# 📧 Email System

Overview of all email functionality in the LeJeepney system.

---

## Architecture

All emails are sent **server-side** via Laravel Mail. There is no client-side email sending.

```
Event (ticket reply, password reset, etc.)
       │
       ▼
  Laravel Controller dispatches Mailable
       │
       ▼
  Laravel Mail → configured MAIL_MAILER driver
       │
       ├── log (development) → storage/logs/laravel.log
       ├── smtp (production) → SMTP server → user inbox
       ├── ses / mailgun / postmark (production alternatives)
       └── array (testing) → in-memory
```

---

## Mailables

The system has **3 Mailable classes** in `app/Mail/`:

### 1. `TicketReplyMail`

**Purpose:** Notify customers when an admin replies to their support ticket.

**Triggered by:** Admin clicking "Reply" with the "Send email notification" checkbox enabled in the Customer Service panel.

**File:** `app/Mail/TicketReplyMail.php`

| Property        | Type          | Description                |
| --------------- | ------------- | -------------------------- |
| `$ticket`       | SupportTicket | The support ticket         |
| `$replyMessage` | string        | Admin's reply text         |
| `$adminName`    | string        | Name of the replying admin |

**Subject:** `Re: {subject} — LeJeepney Support (Ticket #{id})`  
**Template:** `resources/views/emails/ticket-reply.blade.php`

---

### 2. `PasswordResetMail`

**Purpose:** Send password reset link to admin users (web panel).

**Triggered by:** Admin submitting the "Forgot Password" form at `/forgot-password`. Uses Laravel's built-in `Password::sendResetLink()`.

**File:** `app/Mail/PasswordResetMail.php`

| Property | Type   | Description               |
| -------- | ------ | ------------------------- |
| `$email` | string | Admin's email address     |
| `$token` | string | Cryptographic reset token |

**Subject:** `Reset Your LeJeepney Admin Password`  
**Template:** `resources/views/emails/password-reset.blade.php`  
**Token expiry:** 60 minutes

---

### 3. `ResetCodeMail`

**Purpose:** Send 6-digit password reset code to mobile app users.

**Triggered by:** `POST /api/password/forgot` endpoint when a valid `role = 'user'` email is provided.

**File:** `app/Mail/ResetCodeMail.php`

| Property | Type   | Description        |
| -------- | ------ | ------------------ |
| `$code`  | string | 6-digit reset code |

**Subject:** `Your LeJeepney Password Reset Code`  
**Template:** `resources/views/emails/reset-code.blade.php`  
**Code expiry:** 15 minutes

---

## Email Templates

All templates are in `resources/views/emails/` and use inline CSS for maximum email client compatibility.

### Shared Design Language

- **Header:** Gold gradient background (`#EBAF3E` → `#D4982E`) with white text
- **Body:** White card with `12px` border-radius and subtle box shadow
- **Footer:** Light border-top with copyright text
- **Responsive:** 600px max-width, scales for mobile

### Template Files

| Template                   | Used By           | Key Content                                         |
| -------------------------- | ----------------- | --------------------------------------------------- |
| `ticket-reply.blade.php`   | TicketReplyMail   | Ticket details, admin reply, reply-in-app prompt    |
| `password-reset.blade.php` | PasswordResetMail | Reset button/link, 60-min expiry notice             |
| `reset-code.blade.php`     | ResetCodeMail     | Prominent 6-digit code, 15-min expiry, security tip |

---

## Configuration

### Environment Variables

```dotenv
# Development — emails logged to storage/logs/laravel.log
MAIL_MAILER=log

# Production — use SMTP or a transactional email service
MAIL_MAILER=smtp
MAIL_HOST=smtp.example.com
MAIL_PORT=587
MAIL_USERNAME=your_username
MAIL_PASSWORD=your_password
MAIL_ENCRYPTION=tls
MAIL_FROM_ADDRESS=noreply@yourdomain.com
MAIL_FROM_NAME="LeJeepney"
```

### Supported Mail Drivers

| Driver     | Use Case                  | Config Needed             |
| ---------- | ------------------------- | ------------------------- |
| `log`      | Local development         | None (default)            |
| `smtp`     | Production (generic SMTP) | MAIL_HOST, USERNAME, etc. |
| `ses`      | AWS Simple Email Service  | AWS credentials           |
| `mailgun`  | Mailgun                   | API key + domain          |
| `postmark` | Postmark                  | Token                     |
| `array`    | Testing (in-memory)       | None                      |

---

## How Emails Are Sent

### Ticket Reply Email

```
Admin opens /customer-service/{id}
  → Writes reply, checks "Send email notification" ☑
  → CustomerServiceController::reply()
  → Saves TicketReply to DB
  → Mail::to($ticket->email)->send(new TicketReplyMail($ticket, $message, $adminName))
  → Sets email_sent = true on the reply record
  → Logs: "Ticket reply email sent"
```

### Admin Password Reset

```
Admin visits /forgot-password
  → Submits email
  → AuthController::sendResetLinkEmail()
  → Password::sendResetLink(['email' => $email])
  → Laravel sends PasswordResetMail with hashed token
  → Admin clicks link → /reset-password/{token}
  → Submits new password → Password::reset()
```

### Mobile Password Reset

```
Flutter app calls POST /api/password/forgot
  → PasswordResetController::forgot()
  → Generates 6-digit code via random_int()
  → Stores Hash::make(code) in password_reset_tokens
  → Mail::to($email)->send(new ResetCodeMail($code))
  → User enters code in app → POST /api/password/reset
  → Hash::check() verifies code → password updated
```

---

## Legacy: EmailJS

> ⚠️ **EmailJS has been fully replaced by server-side Laravel Mail.** The `config/services.php` file still contains EmailJS configuration keys for backward compatibility, but they are no longer used by any active code.

The previous implementation sent ticket reply emails client-side from the browser using the EmailJS JavaScript SDK. This approach had several limitations:

- API keys exposed in client-side JavaScript
- Email sending dependent on user's browser
- No server-side delivery confirmation
- Limited to 200 emails/month on free plan

The current server-side approach resolves all of these issues with proper mail delivery tracking, no client-side key exposure, and scalability via any SMTP provider.

---

## Troubleshooting

| Issue                       | Solution                                                       |
| --------------------------- | -------------------------------------------------------------- |
| Emails not sending (dev)    | Set `MAIL_MAILER=log` and check `storage/logs/laravel.log`     |
| Emails not sending (prod)   | Verify SMTP credentials in `.env`; check mail server logs      |
| Email goes to spam          | Configure SPF, DKIM, and DMARC records for your sending domain |
| Reset code not in log       | Search for "letter-spacing" in the log (HTML template output)  |
| "Failed to send reset code" | Check `MAIL_MAILER` config and mail server connectivity        |
| Template rendering errors   | Run `php artisan view:clear` and check Blade syntax            |

---

## Testing Emails Locally

With `MAIL_MAILER=log`, all emails are written to `storage/logs/laravel.log`. To test:

```bash
# Trigger a password reset
curl -X POST http://localhost:8000/api/password/forgot \
  -H "Content-Type: application/json" \
  -d '{"email": "user@example.com"}'

# Check the log for the email content
tail -100 storage/logs/laravel.log | grep -A 50 "ResetCodeMail"
```

For a more visual experience, use [Mailpit](https://github.com/axllent/mailpit) or [MailHog](https://github.com/mailhog/MailHog):

```dotenv
MAIL_MAILER=smtp
MAIL_HOST=127.0.0.1
MAIL_PORT=1025
MAIL_USERNAME=null
MAIL_PASSWORD=null
MAIL_ENCRYPTION=null
```
