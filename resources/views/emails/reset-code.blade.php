<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Password Reset Code — LeJeepney</title>
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
                                🔐 Password Reset Code
                            </h1>
                        </td>
                    </tr>

                    {{-- Body --}}
                    <tr>
                        <td style="padding: 32px 40px;">
                            <p style="margin: 0 0 16px; color: #333; font-size: 16px;">
                                Hello,
                            </p>
                            <p style="margin: 0 0 24px; color: #555; font-size: 14px; line-height: 1.6;">
                                You requested a password reset for your LeJeepney account. Use the code below to reset your password:
                            </p>

                            {{-- Code display --}}
                            <table role="presentation" width="100%" cellspacing="0" cellpadding="0" style="margin: 0 0 28px;">
                                <tr>
                                    <td align="center">
                                        <div style="display: inline-block; padding: 20px 48px; background: linear-gradient(135deg, #f8f4ec 0%, #fdf6e3 100%); border: 2px solid #EBAF3E; border-radius: 12px; letter-spacing: 12px; font-size: 36px; font-weight: 700; color: #333; font-family: 'Courier New', monospace;">
                                            {{ $code }}
                                        </div>
                                    </td>
                                </tr>
                            </table>

                            <p style="margin: 0 0 16px; color: #555; font-size: 14px; line-height: 1.6;">
                                This code will expire in <strong>15 minutes</strong>.
                            </p>

                            <p style="margin: 0 0 20px; color: #555; font-size: 14px; line-height: 1.6;">
                                If you did not request a password reset, you can safely ignore this email. Your password will remain unchanged.
                            </p>

                            {{-- Security notice --}}
                            <div style="background-color: #fff8eb; border-left: 4px solid #EBAF3E; border-radius: 4px; padding: 14px 16px; margin-top: 24px;">
                                <p style="margin: 0; color: #8a6d3b; font-size: 13px; line-height: 1.5;">
                                    <strong>Security tip:</strong> Never share this code with anyone. LeJeepney staff will never ask for your reset code.
                                </p>
                            </div>
                        </td>
                    </tr>

                    {{-- Footer --}}
                    <tr>
                        <td style="padding: 20px 40px 30px; border-top: 1px solid #eee;">
                            <p style="margin: 0; color: #aaa; font-size: 12px; text-align: center;">
                                © {{ date('Y') }} LeJeepney — Davao City Jeepney Navigation System
                            </p>
                        </td>
                    </tr>
                </table>
            </td>
        </tr>
    </table>
</body>
</html>
