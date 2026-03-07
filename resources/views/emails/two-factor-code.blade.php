@component('emails.layout', [
    'subject' => 'Login Verification',
    'preheader' => 'Your login code: ' . $code,
])
    <p style="margin: 0 0 20px 0; font-size: 15px; line-height: 1.6; color: #3f3f46;">
        Hi {{ $userName }},
    </p>
    <p style="margin: 0 0 24px 0; font-size: 14px; line-height: 1.6; color: #52525b;">
        Use this code to complete your sign in. It expires in 10 minutes.
    </p>

    <table role="presentation" cellspacing="0" cellpadding="0" border="0" width="100%">
        <tr>
            <td style="text-align: center; padding: 8px 0 28px 0;">
                <table role="presentation" cellspacing="0" cellpadding="0" border="0" style="margin: 0 auto;">
                    <tr>
                        <td style="background-color: #fafafa; border: 1px solid #e4e4e7; border-radius: 6px; padding: 16px 32px;">
                            <span style="font-family: 'SFMono-Regular', Consolas, 'Liberation Mono', Menlo, monospace; font-size: 28px; font-weight: 600; letter-spacing: 8px; color: #18181b;">
                                {{ $code }}
                            </span>
                        </td>
                    </tr>
                </table>
            </td>
        </tr>
    </table>

    <p style="margin: 0; font-size: 13px; line-height: 1.5; color: #71717a;">
        If you didn't try to sign in, someone may be trying to access your account. Consider changing your password.
    </p>
@endcomponent
