<!doctype html>
<html lang="en" xmlns="http://www.w3.org/1999/xhtml">
<head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0" />
    <meta http-equiv="X-UA-Compatible" content="IE=edge" />
    <meta name="x-apple-disable-message-reformatting" />
    <title>Reset Password MHIS HUB</title>
</head>
<body style="margin: 0; padding: 0; background-color: #f9f7f4; font-family: Arial, 'Helvetica Neue', Helvetica, sans-serif; -webkit-text-size-adjust: 100%; -ms-text-size-adjust: 100%;">
    <center style="width: 100%; background-color: #f9f7f4;">
        <table role="presentation" align="center" border="0" cellpadding="0" cellspacing="0" width="100%" style="max-width: 600px;">
            <tr>
                <td height="4" bgcolor="#800000" style="background: linear-gradient(90deg, #800000 0%, #d4af37 50%, #800000 100%);"></td>
            </tr>
        </table>
        <table role="presentation" align="center" border="0" cellpadding="0" cellspacing="0" width="100%" bgcolor="#ffffff" style="max-width: 600px; margin: 0 auto; background-color: #ffffff;">
            <tr>
                <td align="center" style="padding: 40px 30px 25px; background-color: #ffffff; border-left: 1px solid #f0f0f0; border-right: 1px solid #f0f0f0;">
                    <img src="https://bangka.mutiaraharapan.sch.id/wp-content/uploads/2020/03/LOGO-5-1536x864-1-1024x576.png"
                        alt="Mutiara Harapan Islamic School" width="200" height="61" border="0"
                        style="display: block; height: auto; max-width: 200px; width: 100%; margin: 0 auto 20px;" />
                    <h1 style="margin: 0 0 15px; font-size: 24px; color: #800000; font-weight: bold; letter-spacing: 0.5px;">Reset Password</h1>
                    <p style="margin: 0 0 10px; font-size: 20px; color: #800000; font-weight: bold; line-height: 1.4;">
                        ٱلسَّلَامُ عَلَيْكُمْ وَرَحْمَةُ ٱللَّٰهِ وَبَرَكَاتُهُ
                    </p>
                </td>
            </tr>
            <tr>
                <td style="padding: 0 30px; background-color: #ffffff; border-left: 1px solid #f0f0f0; border-right: 1px solid #f0f0f0; text-align: left; color: #444444; font-size: 15px; line-height: 1.7;">
                    <p style="margin: 0 0 18px;">Dear <strong style="color: #800000;">{{ $name }}</strong>,</p>
                    <p style="margin: 0 0 18px;">We received a request to reset the password for your MHIS HUB account. Click the button below to choose a new password.</p>
                    <table role="presentation" align="center" border="0" cellpadding="0" cellspacing="0">
                        <tr>
                            <td align="center" bgcolor="#800000" style="border-radius: 8px;">
                                <a href="{{ $resetUrl }}" style="display: inline-block; padding: 12px 28px; color: #ffffff; font-size: 16px; font-weight: bold; text-decoration: none;">Reset Password</a>
                            </td>
                        </tr>
                    </table>
                    <p style="margin: 18px 0;">This password reset link will expire in {{ $expiresIn }} minutes. If you did not request a password reset, no further action is required.</p>
                    <p style="margin: 0; font-size: 13px; overflow-wrap: anywhere;">If the button does not work, copy and paste this link into your browser:<br /><a href="{{ $resetUrl }}" style="color: #800000;">{{ $resetUrl }}</a></p>
                </td>
            </tr>
            <tr>
                <td align="center" style="padding: 30px 30px 40px; background-color: #ffffff; border-left: 1px solid #f0f0f0; border-right: 1px solid #f0f0f0; border-bottom: 1px solid #f0f0f0;">
                    <p style="margin: 0 0 20px; font-size: 20px; color: #800000; font-weight: bold; line-height: 1.4;">
                        وَالسَّلَامُ عَلَيْكُمْ وَرَحْمَةُ اللَّهِ وَبَرَكَاتُهُ
                    </p>
                    <p style="margin: 0; color: #333333; font-size: 15px; line-height: 1.6;">
                        Warm regards,<br />
                        <strong style="font-size: 18px; color: #800000; display: block; margin: 15px 0 5px; letter-spacing: 0.5px;">Human Resources Mutiara Harapan Islamic School</strong>
                        <em style="color: #d4af37; font-size: 14px;">Home of The Champions</em>
                    </p>
                </td>
            </tr>
            <tr>
                <td align="center" style="padding: 25px 30px; background-color: #f5f1ec; color: #777777; font-size: 12px; line-height: 1.5; border-top: 1px solid #e8e4e0;">
                    <p style="margin: 0 0 10px;">&copy; {{ date('Y') }} Mutiara Harapan Islamic School. All Rights Reserved.</p>
                    <p style="margin: 0; font-size: 11px; color: #999999;">Jl. Pondok Kacang Raya No.2 Pondok Kacang Timur, Pondok Aren<br />Tangerang Selatan - 15426</p>
                </td>
            </tr>
        </table>
    </center>
</body>
</html>
