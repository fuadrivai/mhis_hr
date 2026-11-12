<!doctype html>
<html lang="en" xmlns="http://www.w3.org/1999/xhtml" xmlns:v="urn:schemas-microsoft-com:vml"
    xmlns:o="urn:schemas-microsoft-com:office:office">

<head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0" />
    <meta http-equiv="X-UA-Compatible" content="IE=edge" />
    <meta name="x-apple-disable-message-reformatting" />
    <title>{{ $title }} | MHIS HUB</title>
</head>

<body style="margin:0;padding:0;background-color:#f9f7f4;font-family:Arial,'Helvetica Neue',Helvetica,sans-serif;-webkit-text-size-adjust:100%;-ms-text-size-adjust:100%;">
    <center style="width:100%;background-color:#f9f7f4;">
        <table role="presentation" align="center" border="0" cellpadding="0" cellspacing="0" width="100%" style="max-width:600px;">
            <tr>
                <td height="4" style="background:linear-gradient(90deg,#800000 0%,#d4af37 50%,#800000 100%);font-size:0;line-height:0;">&nbsp;</td>
            </tr>
        </table>

        <table role="presentation" align="center" border="0" cellpadding="0" cellspacing="0" width="100%" bgcolor="#ffffff" style="max-width:600px;margin:0 auto;background-color:#ffffff;">
            <tr>
                <td align="center" style="padding:40px 30px 25px;background-color:#ffffff;border-left:1px solid #f0f0f0;border-right:1px solid #f0f0f0;">
                    <img src="https://bangka.mutiaraharapan.sch.id/wp-content/uploads/2020/03/LOGO-5-1536x864-1-1024x576.png"
                        alt="Mutiara Harapan Islamic School" width="200" border="0"
                        style="display:block;height:auto;max-width:200px;width:100%;margin:0 auto 20px;" />
                    <h1 style="margin:0 0 15px;font-size:24px;color:#800000;font-weight:bold;letter-spacing:0.5px;">{{ $title }}</h1>
                    <p style="margin:0 0 10px;font-size:20px;color:#800000;font-weight:bold;line-height:1.4;">
                        ٱلسَّلَامُ عَلَيْكُمْ وَرَحْمَةُ ٱللَّٰهِ وَبَرَكَاتُهُ
                    </p>
                </td>
            </tr>
            <tr>
                <td style="padding:0 30px;background-color:#ffffff;border-left:1px solid #f0f0f0;border-right:1px solid #f0f0f0;">
                    <table role="presentation" border="0" cellpadding="0" cellspacing="0" width="100%" style="background-color:#ffffff;">
                        <tr>
                            <td style="text-align:left;color:#444444;font-size:15px;line-height:1.7;">
                                <p style="margin:0 0 18px;">
                                    Dear <strong style="color:#800000;">{{ $recipientName }}</strong>,<br />
                                    {{ $intro }}
                                </p>
                            </td>
                        </tr>
                        <tr>
                            <td style="padding:0 0 25px;">
                                <table role="presentation" border="0" cellpadding="0" cellspacing="0" width="100%"
                                    style="border-collapse:separate;border-spacing:0;border:1px solid #e8e4e0;border-radius:8px;overflow:hidden;font-size:15px;">
                                    @foreach ($details as $label => $value)
                                        <tr>
                                            <td style="padding:14px 15px;text-align:left;font-weight:bold;color:#800000;vertical-align:top;width:38%;border-bottom:1px solid #f0f0f0;">
                                                {{ $label }}
                                            </td>
                                            <td style="padding:14px 15px;text-align:left;color:#333333;vertical-align:top;border-bottom:1px solid #f0f0f0;">
                                                {{ $value }}
                                            </td>
                                        </tr>
                                    @endforeach
                                </table>
                            </td>
                        </tr>
                        @if (!empty($qrPng))
                            <tr>
                                <td align="center" style="padding:0 0 25px;">
                                    <img src="{{ $message->embedData($qrPng, 'visitor-invitation.png', 'image/png') }}"
                                        alt="Visitor invitation QR code" width="220" height="220"
                                        style="display:block;width:220px;height:220px;border:0;" />
                                    <p style="margin:12px 0 0;color:#666666;font-size:13px;line-height:1.6;">
                                        Please show this QR code or your visit code at the security desk.
                                    </p>
                                </td>
                            </tr>
                        @endif
                        @if (!empty($actionUrl))
                            <tr>
                                <td align="center" style="padding:0 0 30px;">
                                    <a href="{{ $actionUrl }}"
                                        style="background-color:#800000;border:2px solid #800000;border-radius:8px;color:#ffffff;display:inline-block;font-family:Arial,sans-serif;font-size:16px;font-weight:bold;line-height:48px;text-align:center;text-decoration:none;min-width:220px;padding:0 16px;-webkit-text-size-adjust:none;">
                                        <span style="color:#ffffff;">{{ $actionLabel }}</span>
                                    </a>
                                    @if (!empty($actionNote))
                                        <p style="margin:12px 0 0;color:#666666;font-size:13px;font-style:italic;">{{ $actionNote }}</p>
                                    @endif
                                </td>
                            </tr>
                        @endif
                    </table>
                </td>
            </tr>
            <tr>
                <td align="center" style="padding:30px 30px 40px;background-color:#ffffff;border-left:1px solid #f0f0f0;border-right:1px solid #f0f0f0;border-bottom:1px solid #f0f0f0;">
                    <p style="margin:0 0 20px;font-size:20px;color:#800000;font-weight:bold;line-height:1.4;">
                        وَالسَّلَامُ عَلَيْكُمْ وَرَحْمَةُ اللَّهِ وَبَرَكَاتُهُ
                    </p>
                    <p style="margin:0;color:#333333;font-size:15px;line-height:1.6;">
                        Warm regards,<br />
                        <strong style="font-size:18px;color:#800000;display:block;margin:15px 0 5px;letter-spacing:0.5px;">
                            Mutiara Harapan Islamic School
                        </strong>
                        <em style="color:#d4af37;font-size:14px;">Home of The Champions</em>
                    </p>
                </td>
            </tr>
            <tr>
                <td align="center" style="padding:25px 30px;background-color:#f5f1ec;color:#777777;font-size:12px;line-height:1.5;border-top:1px solid #e8e4e0;">
                    <p style="margin:0 0 10px;">&copy; {{ now()->format('Y') }} Mutiara Harapan Islamic School. All Rights Reserved.</p>
                    <p style="margin:0;font-size:11px;color:#999999;">
                        Jl. Pondok Kacang Raya No.2 Pondok Kacang Timur, Pondok Aren, Tangerang Selatan &ndash; 15426
                    </p>
                </td>
            </tr>
        </table>
    </center>
</body>

</html>
