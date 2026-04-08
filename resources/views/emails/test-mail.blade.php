<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Laravel Mail Test</title>
</head>
<body style="margin:0;padding:0;background:#f1f5f9;font-family:'Segoe UI',Tahoma,Geneva,Verdana,sans-serif;">
    <table role="presentation" width="100%" cellspacing="0" cellpadding="0" style="background:#f1f5f9;padding:40px 0;">
        <tr>
            <td align="center">
                <table role="presentation" width="520" cellspacing="0" cellpadding="0" style="background:#ffffff;border-radius:12px;box-shadow:0 4px 16px rgba(0,0,0,.06);overflow:hidden;">
                    <tr>
                        <td style="background:linear-gradient(135deg,#0056b3 0%,#004494 100%);padding:28px 36px;text-align:center;">
                            <h1 style="margin:0;color:#ffffff;font-size:20px;font-weight:700;">{{ $appName }}</h1>
                            <p style="margin:6px 0 0;color:rgba(255,255,255,.82);font-size:12px;">SMTP test email</p>
                        </td>
                    </tr>
                    <tr>
                        <td style="padding:36px;">
                            <p style="margin:0 0 16px;color:#1e293b;font-size:15px;line-height:1.7;">
                                Your Laravel mail configuration is working.
                            </p>
                            <p style="margin:0 0 16px;color:#475569;font-size:14px;line-height:1.7;">
                                This confirms that forgot-password and account activation emails can be delivered using the current SMTP settings.
                            </p>
                            <p style="margin:0;color:#64748b;font-size:13px;">
                                App URL: <a href="{{ $appUrl }}" style="color:#0056b3;text-decoration:none;">{{ $appUrl }}</a>
                            </p>
                        </td>
                    </tr>
                    <tr>
                        <td style="background:#f8fafc;padding:18px 36px;text-align:center;border-top:1px solid #e2e8f0;">
                            <p style="margin:0;color:#94a3b8;font-size:11px;">&copy; {{ date('Y') }} {{ $appName }}</p>
                        </td>
                    </tr>
                </table>
            </td>
        </tr>
    </table>
</body>
</html>
