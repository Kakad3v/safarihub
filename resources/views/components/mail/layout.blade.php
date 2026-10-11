@props(['preheader' => ''])
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="color-scheme" content="light dark">
    <meta name="supported-color-schemes" content="light dark">
    <title>SafariHub</title>
    <link
        href="https://fonts.googleapis.com/css2?family=Bricolage+Grotesque:wght@700&family=Figtree:wght@400;600&display=swap"
        rel="stylesheet">
    <style>
        body,
        table,
        td,
        a {
            -webkit-text-size-adjust: 100%;
            -ms-text-size-adjust: 100%;
        }

        img {
            border: 0;
            outline: none;
            text-decoration: none;
            -ms-interpolation-mode: bicubic;
        }

        @media (prefers-color-scheme: dark) {
            .bg {
                background-color: #0A1517 !important;
            }

            .card {
                background-color: #112124 !important;
                border-color: #223A3E !important;
            }

            .ink {
                color: #E6F0EF !important;
            }

            .muted {
                color: #8FA7AA !important;
            }

            .codebox {
                background-color: #16393B !important;
                border-color: #1F5456 !important;
            }

            .btn {
                background-color: #47C9C3 !important;
            }

            .btn a {
                background-color: #47C9C3 !important;
                color: #06201F !important;
            }

            .rule {
                border-color: #223A3E !important;
            }
        }

        @media only screen and (max-width: 540px) {
            .outer {
                padding: 12px 8px !important;
            }

            .px {
                padding-left: 20px !important;
                padding-right: 20px !important;
            }

            .h1 {
                font-size: 26px !important;
                line-height: 32px !important;
            }

            .code {
                font-size: 32px !important;
                letter-spacing: 7px !important;
            }

            .btn a {
                display: block !important;
            }
        }
    </style>
</head>

<body class="bg" style="margin:0;padding:0;background-color:#F3F7F6;">
    <div style="display:none;max-height:0;overflow:hidden;opacity:0;color:transparent;">{{ $preheader }}</div>
    <table role="presentation" width="100%" cellpadding="0" cellspacing="0" class="bg" bgcolor="#F3F7F6"
        style="background-color:#F3F7F6;">
        <tr>
            <td align="center" class="outer" style="padding:32px 16px;">
                <table role="presentation" width="100%" cellpadding="0" cellspacing="0" style="max-width:540px;">
                    <tr>
                        <td class="card" bgcolor="#FFFFFF"
                            style="background-color:#FFFFFF;border:1px solid #D3DFDD;border-radius:16px;overflow:hidden;">
                            <table role="presentation" width="100%" cellpadding="0" cellspacing="0">
                                <tr>
                                    <td class="px" bgcolor="#0B3B3C"
                                        style="background-color:#0B3B3C;padding:20px 28px;font-family:'Bricolage Grotesque',-apple-system,BlinkMacSystemFont,'Segoe UI',Roboto,Helvetica,Arial,sans-serif;font-size:24px;line-height:28px;font-weight:700;letter-spacing:-0.3px;color:#FFFFFF;">
                                        SafariHub</td>
                                </tr>
                                <tr>
                                    <td bgcolor="#F4C76B" style="background-color:#F4C76B;line-height:0;font-size:0;">
                                        <img src="{{ asset('images/email/banner.png') }}" width="540" alt=""
                                            style="display:block;width:100%;max-width:540px;height:auto;">
                                    </td>
                                </tr>
                                <tr>
                                    <td class="px" style="padding:32px 28px 12px;">
                                        {{ $slot }}
                                    </td>
                                </tr>
                            </table>
                        </td>
                    </tr>
                    <tr>
                        <td class="muted"
                            style="padding:20px 8px 0;font-family:'Figtree',-apple-system,BlinkMacSystemFont,'Segoe UI',Roboto,Helvetica,Arial,sans-serif;font-size:12px;line-height:18px;color:#587074;text-align:center;">
                            SafariHub &middot; Honest trips and reviews for Kenya and Africa.<br>
                            You are receiving this email because of activity on your SafariHub account.
                        </td>
                    </tr>
                </table>
            </td>
        </tr>
    </table>
</body>

</html>
