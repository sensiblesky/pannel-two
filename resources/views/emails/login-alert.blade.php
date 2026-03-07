@component('emails.layout', [
    'subject' => 'New Login Alert',
    'preheader' => 'A new login was detected on your account.',
])
    <p style="margin: 0 0 20px 0; font-size: 15px; line-height: 1.6; color: #3f3f46;">
        Dear {{ $userName }},
    </p>

    <p style="margin: 0 0 16px 0; font-size: 14px; line-height: 1.6; color: #52525b;">
        We detected a new sign-in to your account. Here are the details:
    </p>

    <table role="presentation" cellspacing="0" cellpadding="0" border="0" width="100%">
        <tr>
            <td style="padding: 8px 0 20px 0;">
                <table role="presentation" cellspacing="0" cellpadding="0" border="0" width="100%" style="background-color: #fafafa; border: 1px solid #e4e4e7; border-radius: 6px;">
                    <tr>
                        <td style="padding: 14px 16px; border-bottom: 1px solid #e4e4e7;">
                            <p style="margin: 0; font-size: 13px; color: #52525b;">
                                <strong>Date & Time:</strong> {{ $loginAt }}
                            </p>
                        </td>
                    </tr>
                    <tr>
                        <td style="padding: 14px 16px; border-bottom: 1px solid #e4e4e7;">
                            <p style="margin: 0; font-size: 13px; color: #52525b;">
                                <strong>Device:</strong> {{ $device }}
                            </p>
                        </td>
                    </tr>
                    <tr>
                        <td style="padding: 14px 16px; border-bottom: 1px solid #e4e4e7;">
                            <p style="margin: 0; font-size: 13px; color: #52525b;">
                                <strong>IP Address:</strong> {{ $ipAddress }}
                            </p>
                        </td>
                    </tr>
                    <tr>
                        <td style="padding: 14px 16px;">
                            <p style="margin: 0; font-size: 13px; color: #52525b;">
                                <strong>Location:</strong> {{ $location }}
                            </p>
                        </td>
                    </tr>
                </table>
            </td>
        </tr>
    </table>

    <p style="margin: 0; font-size: 13px; line-height: 1.5; color: #71717a;">
        If this was you, no further action is needed. If you didn't sign in, please change your password immediately and enable two-factor authentication to secure your account.
    </p>
@endcomponent
