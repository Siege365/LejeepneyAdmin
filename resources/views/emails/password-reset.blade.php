<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Reset Password — LeJeepney Admin</title>
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
                                🔐 Password Reset Request
                            </h1>
                        </td>
                    </tr>

                    {{-- Body --}}
                    <tr>
                        <td style="padding: 32px 40px;">
                            <p style="margin: 0 0 16px; color: #333; font-size: 16px;">
                                Hello,
                            </p>
                            <p style="margin: 0 0 20px; color: #555; font-size: 14px; line-height: 1.6;">
                                You are receiving this email because we received a password reset request for your LeJeepney Admin account.
                            </p>

                            {{-- Reset button --}}
                            <table role="presentation" width="100%" cellspacing="0" cellpadding="0" style="margin: 28px 0;">
                                <tr>
                                    <td align="center">
                                        <a href="{{ route('password.reset', ['token' => $token, 'email' => $email]) }}" 
                                           style="display: inline-block; padding: 14px 32px; background: linear-gradient(135deg, #EBAF3E 0%, #D4982E 100%); color: #ffffff; text-decoration: none; border-radius: 8px; font-weight: 600; font-size: 15px; box-shadow: 0 2px 6px rgba(235, 175, 62, 0.3);">
                                            Reset Password
                                        </a>
                                    </td>
                                </tr>
                            </table>

                            <p style="margin: 0 0 16px; color: #555; font-size: 14px; line-height: 1.6;">
                                This password reset link will expire in <strong>60 minutes</strong>.
                            </p>

                            <p style="margin: 0 0 20px; color: #555; font-size: 14px; line-height: 1.6;">
                                If you did not request a password reset, no further action is required. Your password will remain unchanged.
                            </p>

                            {{-- Alternative link --}}
                            <div style="background-color: #f9f9fb; border-radius: 8px; padding: 16px; margin-top: 24px;">
                                <p style="margin: 0 0 8px; color: #888; font-size: 12px;">
                                    If you're having trouble clicking the "Reset Password" button, copy and paste the URL below into your web browser:
                                </p>
                                <p style="margin: 0; font-size: 12px; color: #EBAF3E; word-break: break-all;">
                                    {{ route('password.reset', ['token' => $token, 'email' => $email]) }}
                                </p>
                            </div>
                        </td>
                    </tr>

                    {{-- Footer --}}
                    <tr>
                        <td style="padding: 20px 40px 30px; border-top: 1px solid #eee;">
                            <p style="margin: 0; color: #aaa; font-size: 12px; text-align: center;">
                                © {{ date('Y') }} LeJeepney Admin — Davao City Jeepney Navigation System
                            </p>
                        </td>
                    </tr>
                </table>
            </td>
        </tr>
    </table>
</body>
</html>
