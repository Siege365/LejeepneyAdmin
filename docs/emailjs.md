# 📧 EmailJS Integration

This project uses **EmailJS** to send email notifications to customers when an admin replies to their support ticket. Emails are sent **client-side** from the browser — no SMTP server is needed.

---

## How It Works

```
Admin replies to ticket
       │
       ▼
  ☑ "Send email notification" checkbox is checked
       │
       ▼
  Reply saved to database (AJAX)
       │
       ▼
  JavaScript calls EmailJS API
       │
       ▼
  EmailJS sends branded email to customer
```

1. Admin opens a support ticket and writes a reply
2. If the **"Send email notification"** checkbox is ticked, the reply is sent via AJAX
3. On success, the frontend JavaScript calls `emailjs.send()` with ticket data
4. EmailJS sends the email using a pre-configured template
5. The customer receives a branded LeJeepney email

---

## Configuration

### 1. Environment Variables

Add these to your `.env` file:

```env
EMAILJS_PUBLIC_KEY=your_public_key_here
EMAILJS_SERVICE_ID=your_service_id_here
EMAILJS_TEMPLATE_ID=your_template_id_here
```

### 2. Laravel Config

These are read in `config/services.php`:

```php
'emailjs' => [
    'public_key'  => env('EMAILJS_PUBLIC_KEY', ''),
    'service_id'  => env('EMAILJS_SERVICE_ID', ''),
    'template_id' => env('EMAILJS_TEMPLATE_ID', ''),
],
```

### 3. Blade Template

The Blade view passes credentials to JavaScript via `window` globals:

```blade
window.EMAILJS_PUBLIC_KEY = '{{ config("services.emailjs.public_key") }}';
window.EMAILJS_SERVICE_ID = '{{ config("services.emailjs.service_id") }}';
window.EMAILJS_TEMPLATE_ID = '{{ config("services.emailjs.template_id") }}';
```

> ⚠️ **Never hardcode API keys in JavaScript files.** Always pass them from `.env` through `config()`.

---

## EmailJS Dashboard Setup

### 1. Create an Account

Go to [emailjs.com](https://www.emailjs.com/) and sign up.

### 2. Add an Email Service

- Go to **Email Services** → **Add New Service**
- Choose your email provider (Gmail, Outlook, etc.)
- Connect and authorize
- Note the **Service ID** (e.g., `service_xxxxxxxxx`)

### 3. Create an Email Template

- Go to **Email Templates** → **Create New Template**
- Note the **Template ID** (e.g., `template_xxxxxxx`)
- Use the template variables below

### 4. Get Your Public Key

- Go to **Account** → **API Keys**
- Copy the **Public Key** (e.g., `YOUR_PUBLIC_KEY_HERE`)

---

## Template Variables

The JavaScript sends these variables to the EmailJS template:

| Variable    | Description                | Example                    |
| ----------- | -------------------------- | -------------------------- |
| `to_email`  | Customer's email address   | `juan@gmail.com`           |
| `ticket_id` | Ticket ID number           | `5`                        |
| `title`     | Ticket subject             | `App Crash Issue`          |
| `name`      | Customer's name            | `Juan Dela Cruz`           |
| `email`     | Customer's email (display) | `juan@gmail.com`           |
| `time`      | Timestamp of the reply     | `2026-02-10 14:30:00`      |
| `message`   | Admin's reply message      | `We've fixed the issue...` |

### Sample Template HTML

Use this in the EmailJS dashboard template editor:

```html
<div style="font-family: Arial, sans-serif; max-width: 600px; margin: 0 auto;">
    <div style="background: #0C4E94; padding: 20px; text-align: center;">
        <h1 style="color: #EBAF3E; margin: 0;">LeJeepney</h1>
        <p style="color: #ffffff; margin: 5px 0 0;">Support Notification</p>
    </div>

    <div style="padding: 30px; background: #f9f9f9;">
        <p>Hi <strong>{{name}}</strong>,</p>
        <p>You have a new response to your support ticket:</p>

        <div
            style="background: #ffffff; border-left: 4px solid #EBAF3E; padding: 15px; margin: 20px 0;"
        >
            <p style="margin: 0 0 5px;">
                <strong>Ticket #{{ticket_id}}:</strong> {{title}}
            </p>
            <p style="margin: 0; color: #666;">{{time}}</p>
        </div>

        <div style="background: #ffffff; padding: 15px; border-radius: 5px;">
            <p>{{message}}</p>
        </div>

        <div style="text-align: center; margin-top: 25px;">
            <div
                style="background: #EBAF3E; color: #0C4E94; padding: 12px 30px; border-radius: 5px; display: inline-block; font-weight: bold;"
            >
                Reply to the Apps Ticket
            </div>
        </div>
    </div>

    <div style="background: #0C4E94; padding: 15px; text-align: center;">
        <p style="color: #ffffff; margin: 0; font-size: 12px;">
            LeJeepney Support Team
        </p>
    </div>
</div>
```

---

## JavaScript Flow

The email sending logic lives in `resources/js/pages/customer-service-show.js`:

```
initEmailJS()
  └── Reads window.EMAILJS_PUBLIC_KEY
  └── Calls emailjs.init(publicKey)

sendEmailNotification(ticketData, replyMessage)
  └── Builds template params: { to_email, ticket_id, title, name, email, time, message }
  └── Calls emailjs.send(serviceId, templateId, params)
  └── Shows success/error toast
```

The EmailJS SDK is loaded via CDN in the Blade template:

```html
<script src="https://cdn.jsdelivr.net/npm/@emailjs/browser@4/dist/email.min.js"></script>
```

---

## Troubleshooting

| Issue                     | Solution                                                         |
| ------------------------- | ---------------------------------------------------------------- |
| Email not sending         | Check `.env` values match your EmailJS dashboard                 |
| "EmailJS not initialized" | Ensure the CDN script loads before your JS                       |
| Wrong template variables  | Verify variable names in EmailJS dashboard match the table above |
| Rate limited              | Free EmailJS plan allows 200 emails/month                        |
| Email goes to spam        | Add SPF/DKIM records for your sending domain                     |

---

## Free Plan Limits

| Feature          | Limit |
| ---------------- | ----- |
| Emails per month | 200   |
| Email templates  | 2     |
| Email size       | 50KB  |
| Contacts         | 200   |

For higher volume, upgrade at [emailjs.com/pricing](https://www.emailjs.com/pricing).
