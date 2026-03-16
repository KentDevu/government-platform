<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>GOV.PH Login Code</title>
</head>
<body style="margin: 0; padding: 0; background-color: #eef2f7; font-family: -apple-system, 'Segoe UI', Roboto, Helvetica, Arial, sans-serif;">
    <table role="presentation" width="100%" cellpadding="0" cellspacing="0" style="background-color: #eef2f7; padding: 48px 16px;">
        <tr>
            <td align="center">
                <table role="presentation" width="520" cellpadding="0" cellspacing="0" style="background-color: #ffffff; border-radius: 16px; overflow: hidden; box-shadow: 0 8px 32px rgba(19,91,236,0.08), 0 1px 4px rgba(0,0,0,0.04);">

                    {{-- Tri-color accent bar (PH flag: blue, red, gold) --}}
                    <tr>
                        <td>
                            <table role="presentation" width="100%" cellpadding="0" cellspacing="0">
                                <tr>
                                    <td style="height: 4px; background-color: #135bec; width: 33.33%;"></td>
                                    <td style="height: 4px; background-color: #ce1126; width: 33.33%;"></td>
                                    <td style="height: 4px; background-color: #fcd116; width: 33.34%;"></td>
                                </tr>
                            </table>
                        </td>
                    </tr>

                    {{-- Logo & Title --}}
                    <tr>
                        <td style="padding: 36px 40px 0;">
                            <table role="presentation" cellpadding="0" cellspacing="0">
                                <tr>
                                    <td style="vertical-align: middle; padding-right: 14px;">
                                        <div style="width: 44px; height: 44px; background: linear-gradient(135deg, #135bec, #0d4abf); border-radius: 12px; text-align: center; line-height: 44px;">
                                            <span style="color: #fcd116; font-size: 22px;">&#9733;</span>
                                        </div>
                                    </td>
                                    <td style="vertical-align: middle;">
                                        <p style="margin: 0; font-size: 20px; font-weight: 800; color: #0f172a; letter-spacing: -0.3px;">GOV<span style="color: #135bec;">.PH</span></p>
                                        <p style="margin: 2px 0 0; font-size: 11px; color: #94a3b8; text-transform: uppercase; letter-spacing: 1.5px; font-weight: 600;">Admin Portal</p>
                                    </td>
                                </tr>
                            </table>
                        </td>
                    </tr>

                    {{-- Divider --}}
                    <tr>
                        <td style="padding: 24px 40px 0;">
                            <div style="height: 1px; background-color: #e2e8f0;"></div>
                        </td>
                    </tr>

                    {{-- Body --}}
                    <tr>
                        <td style="padding: 28px 40px 0;">
                            <p style="margin: 0 0 6px; color: #64748b; font-size: 12px; text-transform: uppercase; letter-spacing: 1.2px; font-weight: 700;">Sign-In Verification</p>
                            <p style="margin: 0 0 24px; color: #334155; font-size: 15px; line-height: 1.7;">
                                Enter this one-time code to access the admin dashboard. It expires in <strong style="color: #0f172a;">10 minutes</strong>.
                            </p>
                        </td>
                    </tr>

                    {{-- OTP Code Block --}}
                    <tr>
                        <td style="padding: 0 40px;">
                            <div style="background: linear-gradient(135deg, #f8fafc 0%, #f1f5f9 100%); border: 1px solid #e2e8f0; border-radius: 14px; padding: 32px 20px; text-align: center;">
                                <p style="margin: 0 0 14px; color: #94a3b8; font-size: 10px; text-transform: uppercase; letter-spacing: 2.5px; font-weight: 700;">Verification Code</p>
                                <p style="margin: 0; font-size: 38px; font-weight: 800; color: #0f172a; letter-spacing: 14px; font-family: 'SF Mono', 'Fira Code', 'Courier New', monospace; background-color: #ffffff; display: inline-block; padding: 14px 28px; border: 2px solid #135bec; border-radius: 12px; box-shadow: 0 2px 12px rgba(19,91,236,0.1);">{{ $otp }}</p>
                            </div>
                        </td>
                    </tr>

                    {{-- Security Info --}}
                    <tr>
                        <td style="padding: 24px 40px 0;">
                            <table role="presentation" cellpadding="0" cellspacing="0" width="100%" style="background-color: #fefce8; border-left: 3px solid #fbbf24; border-radius: 0 8px 8px 0;">
                                <tr>
                                    <td style="padding: 12px 16px;">
                                        <p style="margin: 0; color: #78350f; font-size: 12px; line-height: 1.6; font-weight: 500;">
                                            <strong>Security:</strong> Do not share this code. GOV.PH will never ask for your verification code via phone or message.
                                        </p>
                                    </td>
                                </tr>
                            </table>
                        </td>
                    </tr>

                    {{-- Footer --}}
                    <tr>
                        <td style="padding: 32px 40px 36px;">
                            <div style="height: 1px; background-color: #f1f5f9; margin-bottom: 20px;"></div>
                            <p style="margin: 0; color: #94a3b8; font-size: 11px; text-align: center; line-height: 1.7;">
                                Automated message &mdash; do not reply.<br>
                                <span style="color: #cbd5e1;">&copy; {{ date('Y') }} Republic of the Philippines</span>
                            </p>
                        </td>
                    </tr>

                </table>
            </td>
        </tr>
    </table>
</body>
</html>
