<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0" />
    <title>Visitor Invitation</title>
</head>
<body style="margin:0;padding:0;background-color:#f4f4f4;font-family:Arial,Helvetica,sans-serif;">
    <table align="center" border="0" cellpadding="0" cellspacing="0" width="100%" style="background-color:#f4f4f4;padding:30px 0;">
        <tr>
            <td align="center">
                <table border="0" cellpadding="0" cellspacing="0" width="600" style="max-width:600px;width:100%;background-color:#ffffff;border-radius:12px;overflow:hidden;box-shadow:0 4px 20px rgba(0,0,0,0.08);">
                    <tr>
                        <td height="8" style="background:linear-gradient(90deg,#800000 0%,#d4af37 50%,#800000 100%);font-size:0;line-height:0;">&nbsp;</td>
                    </tr>
                    <tr>
                        <td style="padding:30px 30px 20px;text-align:center;background-color:#ffffff;">
                            <img src="https://bangka.mutiaraharapan.sch.id/wp-content/uploads/2020/03/LOGO-5-1536x864-1-1024x576.png" alt="Mutiara Harapan Islamic School" width="280" style="display:block;max-width:280px;width:100%;height:auto;margin:0 auto;border:0;">
                        </td>
                    </tr>
                    <tr>
                        <td style="padding:10px 30px 30px;text-align:center;background-color:#ffffff;">
                            <div style="display:inline-block;width:80px;height:80px;border-radius:50%;background:linear-gradient(135deg,#800000 0%,#d4af37 50%,#800000 100%);text-align:center;line-height:80px;margin-bottom:24px;">
                                <span style="font-size:42px;color:#ffffff;display:inline-block;vertical-align:middle;line-height:1;">&#9993;</span>
                            </div>
                            <h1 style="margin:0 0 12px;font-size:28px;font-weight:700;color:#800000;letter-spacing:-0.3px;">Visitor Invitation</h1>
                            <p style="margin:0 0 24px;font-size:17px;color:#444444;line-height:1.6;">Hello {{ $visit->visitor->full_name }}, you are invited to visit us.</p>
                            <div style="width:60px;height:3px;margin:0 auto 24px;background:linear-gradient(90deg,#800000 0%,#d4af37 50%,#800000 100%);border-radius:2px;"></div>
                            <table border="0" cellpadding="0" cellspacing="0" width="100%" style="text-align:left;font-size:15px;color:#444444;line-height:1.7;">
                                <tr><td style="padding:5px 0;font-weight:700;color:#800000;width:38%;">Visit date</td><td style="padding:5px 0;">{{ $visit->visit_date->format('d M Y') }}</td></tr>
                                <tr><td style="padding:5px 0;font-weight:700;color:#800000;">Visit time</td><td style="padding:5px 0;">{{ $visit->expected_arrival_at?->format('H:i') ?? '—' }} &ndash; {{ $visit->expected_departure_at?->format('H:i') ?? '—' }}</td></tr>
                                <tr><td style="padding:5px 0;font-weight:700;color:#800000;">Meeting location</td><td style="padding:5px 0;">{{ $visit->branch->name }}</td></tr>
                                <tr><td style="padding:5px 0;font-weight:700;color:#800000;">Host</td><td style="padding:5px 0;">{{ $visit->host->user?->name ?? 'School contact' }}</td></tr>
                                <tr><td style="padding:5px 0;font-weight:700;color:#800000;">Purpose</td><td style="padding:5px 0;">{{ $visit->purpose->name }}</td></tr>
                                <tr><td style="padding:5px 0;font-weight:700;color:#800000;">Visit code</td><td style="padding:5px 0;"><strong>{{ $visit->visit_code }}</strong></td></tr>
                            </table>
                            <p style="margin:24px 0 12px;font-size:15px;color:#444444;line-height:1.6;">Please show this QR code or your visit code at the security desk.</p>
                            <div style="display:inline-block;padding:12px;background:#ffffff;border:1px solid #dddddd;border-radius:8px;">
                                <img src="{{ $message->embedData($qrPng, 'visitor-invitation.png', 'image/png') }}" alt="Visitor invitation QR code" width="220" height="220" style="display:block;width:220px;height:220px;border:0;">
                            </div>
                        </td>
                    </tr>
                    <tr>
                        <td height="6" style="background:linear-gradient(90deg,#800000 0%,#d4af37 50%,#800000 100%);font-size:0;line-height:0;">&nbsp;</td>
                    </tr>
                    <tr>
                        <td style="padding:24px 30px;text-align:center;background-color:#fafafa;">
                            <p style="margin:0 0 4px;font-size:15px;color:#d4af37;font-weight:600;letter-spacing:0.5px;">Home of The Champions</p>
                            <p style="margin:0 0 16px;font-size:14px;color:#800000;font-weight:500;letter-spacing:0.3px;">Mutiara Harapan Islamic School</p>
                            <div style="width:80px;height:2px;margin:0 auto 16px;background:linear-gradient(90deg,#800000 0%,#d4af37 50%,#800000 100%);border-radius:2px;"></div>
                            <p style="margin:0 0 4px;font-size:13px;color:#777777;letter-spacing:0.2px;">&copy; {{ now()->format('Y') }} Mutiara Harapan Islamic School. All Rights Reserved.</p>
                            <p style="margin:0;font-size:13px;color:#777777;letter-spacing:0.2px;">Jl. Pondok Kacang Raya No.2 Pondok Kacang Timur, Pondok Aren Tangerang Selatan &ndash; 15426</p>
                        </td>
                    </tr>
                </table>
                <div style="height:10px;font-size:0;line-height:0;">&nbsp;</div>
            </td>
        </tr>
    </table>
</body>
</html>
