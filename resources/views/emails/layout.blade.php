<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>{{ $subject ?? config('app.name') }}</title>
    <style>
        body, table, td, a { -webkit-text-size-adjust: 100%; -ms-text-size-adjust: 100%; }
        table, td { mso-table-lspace: 0pt; mso-table-rspace: 0pt; }
        img { -ms-interpolation-mode: bicubic; border: 0; height: auto; line-height: 100%; outline: none; text-decoration: none; }
        body { margin: 0; padding: 0; width: 100% !important; height: 100% !important; background-color: #f4f4f5; }
        body, td, p { font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, Helvetica, Arial, sans-serif; }
        @media only screen and (max-width: 620px) {
            .email-container { width: 100% !important; }
            .email-content { padding: 24px 20px !important; }
        }
    </style>
</head>
<body style="margin: 0; padding: 0; background-color: #f4f4f5;">
    @isset($preheader)
    <div style="display: none; font-size: 1px; line-height: 1px; max-height: 0px; max-width: 0px; opacity: 0; overflow: hidden; mso-hide: all;">{{ $preheader }}</div>
    @endisset

    <table role="presentation" cellspacing="0" cellpadding="0" border="0" width="100%" style="background-color: #f4f4f5;">
        <tr>
            <td style="padding: 48px 16px;">
                <table role="presentation" cellspacing="0" cellpadding="0" border="0" width="520" class="email-container" style="margin: 0 auto; max-width: 520px;">

                    {{-- Logo --}}
                    <tr>
                        <td style="padding: 0 0 32px 0; text-align: center;">
                            <span style="font-size: 20px; font-weight: 700; color: #18181b; letter-spacing: -0.025em;">{{ config('app.name') }}</span>
                        </td>
                    </tr>

                    {{-- Card --}}
                    <tr>
                        <td>
                            <table role="presentation" cellspacing="0" cellpadding="0" border="0" width="100%" style="background-color: #ffffff; border-radius: 8px; border: 1px solid #e4e4e7;">
                                <tr>
                                    <td class="email-content" style="padding: 32px 36px;">
                                        {{ $slot }}
                                    </td>
                                </tr>
                            </table>
                        </td>
                    </tr>

                    {{-- Footer --}}
                    <tr>
                        <td style="padding: 24px 0 0 0; text-align: center;">
                            <p style="margin: 0; font-size: 12px; color: #a1a1aa;">
                                &copy; {{ date('Y') }} {{ config('app.name') }}
                            </p>
                            <p style="margin: 6px 0 0 0; font-size: 11px; color: #a1a1aa;">
                                Designed by <a href="https://www.linkedin.com/in/edwinikimathi/" style="color: #a1a1aa; text-decoration: underline;" target="_blank">Denic Edwin</a>
                            </p>
                            @isset($footerText)
                            <p style="margin: 6px 0 0 0; font-size: 11px; color: #a1a1aa;">{{ $footerText }}</p>
                            @endisset
                        </td>
                    </tr>

                </table>
            </td>
        </tr>
    </table>
</body>
</html>
