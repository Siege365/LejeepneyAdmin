<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Support Reply — LeJeepney</title>
</head>
<body style="margin: 0; padding: 0; background-color: #f4f4f7; font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, sans-serif;">
    <table role="presentation" width="100%" cellspacing="0" cellpadding="0" style="background-color: #f4f4f7;">
        <tr>
            <td align="center" style="padding: 40px 20px;">
                <table role="presentation" width="600" cellspacing="0" cellpadding="0" style="background-color: #ffffff; border-radius: 12px; box-shadow: 0 2px 8px rgba(0, 0, 0, 0.06);">
                    {{-- Header --}}
                    <tr>
                        <td style="background: linear-gradient(135deg, #EBAF3E 0%, #D4982E 100%); padding: 30px 40px; border-radius: 12px 12px 0 0;">
                            <h1 style="margin: 0; color: #ffffff; font-size: 22px; font-weight: 600;">
                                🚍 LeJeepney Support
                            </h1>
                        </td>
                    </tr>

                    {{-- Body --}}
                    <tr>
                        <td style="padding: 32px 40px;">
                            <p style="margin: 0 0 16px; color: #333; font-size: 16px;">
                                Hi <strong>{{ $ticket->name }}</strong>,
                            </p>
                            <p style="margin: 0 0 20px; color: #555; font-size: 14px; line-height: 1.6;">
                                Our support team has replied to your ticket:
                            </p>

                            {{-- Ticket info --}}
                            <table role="presentation" width="100%" cellspacing="0" cellpadding="0" style="background-color: #f9f9fb; border-radius: 8px; padding: 16px; margin-bottom: 20px;">
                                <tr>
                                    <td style="padding: 16px;">
                                        <p style="margin: 0 0 4px; font-size: 12px; color: #888; text-transform: uppercase; letter-spacing: 0.5px;">Ticket #{{ $ticket->id }}</p>
                                        <p style="margin: 0; font-size: 15px; color: #333; font-weight: 600;">{{ $ticket->subject }}</p>
                                    </td>
                                </tr>
                            </table>

                            {{-- Reply message --}}
                            <div style="border-left: 3px solid #EBAF3E; padding: 16px 20px; background-color: #fffbf0; border-radius: 0 8px 8px 0; margin-bottom: 24px;">
                                <p style="margin: 0 0 8px; font-size: 12px; color: #888;">
                                    <strong>{{ $adminName }}</strong> — {{ now()->format('M j, Y \\a\\t g:i A') }}
                                </p>
                                <p style="margin: 0; font-size: 14px; color: #333; line-height: 1.7; white-space: pre-wrap;">{{ $replyMessage }}</p>
                            </div>

                            <p style="margin: 0; color: #888; font-size: 13px; line-height: 1.5;">
                                You can reply to this ticket directly from the LeJeepney app, or simply reply to this email.
                            </p>
                        </td>
                    </tr>

                    {{-- Footer --}}
                    <tr>
                        <td style="padding: 20px 40px 30px; border-top: 1px solid #eee;">
                            <p style="margin: 0; color: #aaa; font-size: 12px; text-align: center;">
                                © {{ date('Y') }} LeJeepney — Davao City Jeepney Navigation
                            </p>
                        </td>
                    </tr>
                </table>
            </td>
        </tr>
    </table>
</body>
</html>
