<!doctype html>
<html lang="en" xmlns="http://www.w3.org/1999/xhtml" xmlns:v="urn:schemas-microsoft-com:vml"
    xmlns:o="urn:schemas-microsoft-com:office:office">

<head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0" />
    <meta http-equiv="X-UA-Compatible" content="IE=edge" />
    <meta name="x-apple-disable-message-reformatting" />
    <title>Approval Request MHIS HUB</title>
</head>

<body
    style="
      margin: 0;
      padding: 0;
      background-color: #f9f7f4;
      font-family:
        &quot;Arial&quot;, &quot;Helvetica Neue&quot;, Helvetica, sans-serif;
      -webkit-text-size-adjust: 100%;
      -ms-text-size-adjust: 100%;
    ">
    <center style="width: 100%; background-color: #f9f7f4">
        <!-- Decorative Top Border -->
        <table role="presentation" align="center" border="0" cellpadding="0" cellspacing="0" width="100%"
            style="max-width: 600px">
            <tr>
                <td height="4"
                    style="
              background: linear-gradient(
                90deg,
                #800000 0%,
                #d4af37 50%,
                #800000 100%
              );
            ">
                </td>
            </tr>
        </table>

        <!-- Main Container -->
        <table role="presentation" align="center" border="0" cellpadding="0" cellspacing="0" width="100%"
            style="max-width: 600px; margin: 0 auto">
            <!-- Header with Logo & Title -->
            <tr>
                <td align="center"
                    style="
              padding: 40px 30px 25px;
              background-color: #ffffff;
              border-left: 1px solid #f0f0f0;
              border-right: 1px solid #f0f0f0;
            ">
                    <!-- School Logo -->
                    <img src="https://bangka.mutiaraharapan.sch.id/wp-content/uploads/2020/03/LOGO-5-1536x864-1-1024x576.png"
                        alt="Mutiara Harapan Islamic School" width="200" height="61" border="0"
                        style="
                display: block;
                height: auto;
                max-width: 200px;
                width: 100%;
                margin: 0 auto 20px;
              " />

                    <!-- Page Title -->
                    <h1
                        style="
                margin: 0 0 15px;
                font-size: 24px;
                color: #800000;
                font-weight: bold;
                letter-spacing: 0.5px;
              ">
                        Timeoff Approval Request
                    </h1>

                    <!-- Arabic Greeting -->
                    <p
                        style="
                margin: 0 0 10px;
                font-size: 20px;
                color: #800000;
                font-weight: bold;
                line-height: 1.4;
              ">
                        ٱلسَّلَامُ عَلَيْكُمْ وَرَحْمَةُ ٱللَّٰهِ وَبَرَكَاتُهُ
                    </p>
                </td>
            </tr>

            <!-- Main Content Card -->
            <tr>
                <td align="center"
                    style="
              padding: 0 30px 35px;
              background-color: #ffffff;
              border-left: 1px solid #f0f0f0;
              border-right: 1px solid #f0f0f0;
            ">
                    <table role="presentation" border="0" cellpadding="0" cellspacing="0" width="100%"
                        style="background-color: #ffffff">
                        <!-- Welcome Message -->
                        <tr>
                            <td
                                style="
                    padding: 0 0 25px;
                    text-align: left;
                    color: #444444;
                    font-size: 15px;
                    line-height: 1.7;
                  ">
                                <p style="margin: 0 0 18px">
                                    Dear
                                    <strong style="color: #800000">{{ $data['approver_name'] }}</strong>
                                </p>
                                <p style="margin: 0">
                                    {{ $data['requester_name'] }} would like to request time off
                                    with the following details:
                                </p>
                            </td>
                        </tr>

                        <!-- Documents Checklist -->
                        <tr>
                            <td style="padding: 0 0 25px">
                                <table role="presentation" border="0" cellpadding="0" cellspacing="0" width="100%"
                                    style="
                      border-collapse: separate;
                      border-spacing: 0;
                      border: 1px solid #e8e4e0;
                      border-radius: 8px;
                      overflow: hidden;
                      font-size: 15px;
                    ">
                                    <tr>
                                        <td
                                            style="
                          border-bottom: 1px solid #f0f0f0;
                          padding: 16px 15px;
                          text-align: left;
                          font-weight: bold;
                          color: #800000;
                        ">
                                            Requested time off
                                        </td>
                                        <td
                                            style="
                          border-bottom: 1px solid #f0f0f0;
                          padding: 16px 15px;
                          text-align: left;
                        ">
                                            <p
                                                style="
                            margin: 0 0 8px;
                            font-weight: bold;
                            color: #333;
                          ">
                                                {{ $data['timeoff_name'] }}
                                            </p>
                                        </td>
                                    </tr>
                                    <!-- Table Row 2 -->
                                    <tr>
                                        <td
                                            style="
                          padding: 16px 15px;
                          text-align: left;
                          font-weight: bold;
                          color: #800000;
                        ">
                                            Date
                                        </td>
                                        <td style="padding: 16px 15px; text-align: left">
                                            <p
                                                style="
                            margin: 0 0 8px;
                            font-weight: bold;
                            color: #333;
                          ">
                                                {{ $data['timeoff_date'] }}
                                            </p>
                                        </td>
                                    </tr>
                                    <tr>
                                        <td
                                            style="
                          padding: 16px 15px;
                          text-align: left;
                          font-weight: bold;
                          color: #800000;
                        ">
                                            Reason
                                        </td>
                                        <td style="padding: 16px 15px; text-align: left">
                                            <p
                                                style="
                            margin: 0 0 8px;
                            font-weight: bold;
                            color: #333;
                          ">
                                                {{ $data['reason'] }}
                                            </p>
                                        </td>
                                    </tr>
                                    <tr>
                                        <td
                                            style="
                          padding: 16px 15px;
                          text-align: left;
                          font-weight: bold;
                          color: #800000;
                        ">
                                            Remaining balance
                                        </td>
                                        <td style="padding: 16px 15px; text-align: left">
                                            <p
                                                style="
                            margin: 0 0 8px;
                            font-weight: bold;
                            color: #333;
                          ">
                                                {{ $data['remaining_balance'] }}
                                            </p>
                                        </td>
                                    </tr>
                                </table>
                            </td>
                        </tr>

                        <!-- Approval Action Buttons -->
                        <tr>
                            <td align="center" style="padding: 0 0 30px">
                                <table role="presentation" border="0" cellpadding="0" cellspacing="0"
                                    align="center">
                                    <tr>
                                        <td style="padding: 0 6px 0 0">
                                            <a href="{{ $data['approve_url'] ?? '#' }}"
                                                style="
                            background-color: #2d7d46;
                            border: 2px solid #2d7d46;
                            border-radius: 8px;
                            color: #ffffff;
                            display: inline-block;
                            font-family: &quot;Arial&quot;, sans-serif;
                            font-size: 16px;
                            font-weight: bold;
                            line-height: 48px;
                            text-align: center;
                            text-decoration: none;
                            width: 170px;
                            -webkit-text-size-adjust: none;
                            mso-hide: all;
                            box-shadow: 0 4px 12px rgba(45, 125, 70, 0.18);
                          ">
                                                <span style="color: #ffffff">Approve</span>
                                            </a>
                                        </td>
                                        <td style="padding: 0 0 0 6px">
                                            <a href="{{ $data['reject_url'] ?? '#' }}"
                                                style="
                            background-color: #b33a3a;
                            border: 2px solid #b33a3a;
                            border-radius: 8px;
                            color: #ffffff;
                            display: inline-block;
                            font-family: &quot;Arial&quot;, sans-serif;
                            font-size: 16px;
                            font-weight: bold;
                            line-height: 48px;
                            text-align: center;
                            text-decoration: none;
                            width: 170px;
                            -webkit-text-size-adjust: none;
                            mso-hide: all;
                            box-shadow: 0 4px 12px rgba(179, 58, 58, 0.18);
                          ">
                                                <span style="color: #ffffff">Reject</span>
                                            </a>
                                        </td>
                                    </tr>
                                </table>
                                <p
                                    style="
                      margin: 12px 0 0;
                      color: #666666;
                      font-size: 13px;
                      font-style: italic;
                    ">
                                    Please review this request and choose one action
                                </p>
                            </td>
                        </tr>
                    </table>
                </td>
            </tr>

            <!-- Closing Section -->
            <tr>
                <td align="center"
                    style="
              padding: 30px 30px 40px;
              background-color: #ffffff;
              border-left: 1px solid #f0f0f0;
              border-right: 1px solid #f0f0f0;
              border-bottom: 1px solid #f0f0f0;
            ">
                    <!-- Arabic Closing -->
                    <p
                        style="
                margin: 0 0 20px;
                font-size: 20px;
                color: #800000;
                font-weight: bold;
                line-height: 1.4;
              ">
                        وَالسَّلَامُ عَلَيْكُمْ وَرَحْمَةُ اللَّهِ وَبَرَكَاتُهُ
                    </p>

                    <!-- School Signature -->
                    <p
                        style="
                margin: 0;
                color: #333333;
                font-size: 15px;
                line-height: 1.6;
              ">
                        Warm regards,<br />
                        <strong
                            style="
                  font-size: 18px;
                  color: #800000;
                  display: block;
                  margin: 15px 0 5px;
                  letter-spacing: 0.5px;
                ">Human
                            Resources Mutiara Harapan Islamic School</strong>
                        <em style="color: #d4af37; font-size: 14px">Home of The Champions</em>
                    </p>
                </td>
            </tr>

            <!-- Footer -->
            <tr>
                <td align="center"
                    style="
              padding: 25px 30px;
              background-color: #f5f1ec;
              color: #777777;
              font-size: 12px;
              line-height: 1.5;
              border-top: 1px solid #e8e4e0;
            ">
                    <p style="margin: 0 0 10px">
                        &copy; 2026 Mutiara Harapan Islamic School. All Rights Reserved.
                    </p>
                    <p style="margin: 0; font-size: 11px; color: #999999">
                        This email confirms successful enrolment payment receipt.<br />
                        Jl. Pondok Kacang Raya No.2 Pondok Kacang Timur, Pondok Aren
                        Tangerang Selatan – 15426
                    </p>
                </td>
            </tr>
        </table>
    </center>
</body>

</html>
