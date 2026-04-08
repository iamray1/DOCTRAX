<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Activate Your DocTrax Account</title>
</head>
<body style="margin: 0; padding: 0; background-color: #f1f5f9; font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;">
    <table role="presentation" width="100%" cellspacing="0" cellpadding="0" style="background-color: #f1f5f9; padding: 40px 0;">
        <tr>
            <td align="center">
                <table role="presentation" width="520" cellspacing="0" cellpadding="0" style="background-color: #ffffff; border-radius: 12px; box-shadow: 0 4px 16px rgba(0,0,0,0.06); overflow: hidden;">
                    <!-- Header -->
                    <tr>
                        <td style="background: linear-gradient(135deg, #0056b3 0%, #004494 100%); padding: 28px 36px; text-align: center;">
                            <h1 style="margin: 0; color: #ffffff; font-size: 20px; font-weight: 700; letter-spacing: 0.5px;">Document Tracking System &mdash; DOCTRAX</h1>
                            <p style="margin: 4px 0 0; color: rgba(255,255,255,0.8); font-size: 12px;">Department of Education &bull; City of San Jose del Monte</p>
                        </td>
                    </tr>

                    <!-- Body -->
                    <tr>
                        <td style="padding: 36px 36px 24px;">
                            <p style="margin: 0 0 16px; color: #1e293b; font-size: 15px; line-height: 1.6;">
                                Hi <strong>{{ $user->name }}</strong>,
                            </p>
                            <p style="margin: 0 0 24px; color: #334155; font-size: 14px; line-height: 1.7;">
                                Your DocTrax account has been created. To activate it, please set your password by clicking the button below. This link will expire in <strong>60 minutes</strong>.
                            </p>

                            <!-- CTA Button -->
                            <table role="presentation" width="100%" cellspacing="0" cellpadding="0">
                                <tr>
                                    <td align="center" style="padding: 8px 0 28px;">
                                        <a href="{{ $activationUrl }}" target="_blank" style="display: inline-block; background: linear-gradient(135deg, #0056b3 0%, #004494 100%); color: #ffffff; text-decoration: none; padding: 14px 40px; border-radius: 8px; font-size: 14px; font-weight: 600; letter-spacing: 0.3px;">
                                            Set Password &amp; Activate
                                        </a>
                                    </td>
                                </tr>
                            </table>

                            <!-- Fallback link -->
                            <p style="margin: 0 0 8px; color: #64748b; font-size: 12px;">
                                If the button doesn&rsquo;t work, copy and paste this URL into your browser:
                            </p>
                            <p style="margin: 0 0 24px; word-break: break-all; background: #f8fafc; padding: 12px; border-radius: 6px; border: 1px solid #e2e8f0; font-size: 12px; color: #0056b3;">
                                {{ $activationUrl }}
                            </p>

                            <hr style="border: none; border-top: 1px solid #e2e8f0; margin: 24px 0;">

                            <p style="margin: 0; color: #94a3b8; font-size: 12px; line-height: 1.6;">
                                If you did not create an account, you can safely ignore this email. This link will expire automatically.
                            </p>
                        </td>
                    </tr>

                    <!-- Footer -->
                    <tr>
                        <td style="background: #f8fafc; padding: 20px 36px; text-align: center; border-top: 1px solid #e2e8f0;">
                            <p style="margin: 0; color: #94a3b8; font-size: 11px;">
                                &copy; {{ date('Y') }} City of San Jose del Monte &mdash; Document Tracking System
                            </p>
                        </td>
                    </tr>
                </table>
            </td>
        </tr>
    </table>
</body>
</html>
