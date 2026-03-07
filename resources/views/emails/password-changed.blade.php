@component('emails.layout', [
    'subject' => 'Password Changed',
    'preheader' => 'Your account password was recently changed.',
])
    <p style="margin: 0 0 20px 0; font-size: 15px; line-height: 1.6; color: #3f3f46;">
        Hi {{ $userName }},
    </p>

    <p style="margin: 0 0 16px 0; font-size: 14px; line-height: 1.6; color: #52525b;">
        Your account password was successfully changed.
    </p>

    <table role="presentation" cellspacing="0" cellpadding="0" border="0" width="100%">
        <tr>
            <td style="padding: 8px 0;">
                <table role="presentation" cellspacing="0" cellpadding="0" border="0" width="100%" style="background-color: #fafafa; border: 1px solid #e4e4e7; border-radius: 6px;">
                    <tr>
                        <td style="padding: 12px 16px;">
                            <p style="margin: 0; font-size: 13px; color: #52525b;">
                                <strong>When:</strong> {{ now()->format('M d, Y \a\t h:i A') }}
                            </p>
                        </td>
                    </tr>
                </table>
            </td>
        </tr>
    </table>

    <p style="margin: 16px 0 0 0; font-size: 13px; line-height: 1.5; color: #71717a;">
        If you didn't make this change, please reset your password immediately and secure your account.
    </p>
@endcomponent
