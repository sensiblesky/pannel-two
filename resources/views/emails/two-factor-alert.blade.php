@component('emails.layout', [
    'subject' => $enabled ? '2FA Enabled' : '2FA Disabled',
    'preheader' => $enabled ? 'Two-factor authentication has been enabled on your account.' : 'Two-factor authentication has been disabled on your account.',
])
    <p style="margin: 0 0 20px 0; font-size: 15px; line-height: 1.6; color: #3f3f46;">
        Hi {{ $userName }},
    </p>

    @if ($enabled)
        <p style="margin: 0 0 16px 0; font-size: 14px; line-height: 1.6; color: #52525b;">
            Two-factor authentication has been <strong style="color: #16a34a;">enabled</strong> on your account.
        </p>
        <p style="margin: 0 0 16px 0; font-size: 14px; line-height: 1.6; color: #52525b;">
            From now on, you'll need your authenticator app to sign in.
        </p>

        @if (!empty($recoveryCodes))
            <p style="margin: 0 0 12px 0; font-size: 14px; font-weight: 600; color: #18181b;">
                Recovery Codes
            </p>
            <p style="margin: 0 0 12px 0; font-size: 13px; line-height: 1.5; color: #52525b;">
                Save these codes somewhere safe. Each code can only be used once if you lose access to your authenticator app.
            </p>
            <table role="presentation" cellspacing="0" cellpadding="0" border="0" width="100%">
                <tr>
                    <td style="padding: 0 0 16px 0;">
                        <table role="presentation" cellspacing="0" cellpadding="0" border="0" width="100%" style="background-color: #fafafa; border: 1px solid #e4e4e7; border-radius: 6px;">
                            <tr>
                                <td style="padding: 16px 20px;">
                                    @foreach ($recoveryCodes as $code)
                                        <span style="display: inline-block; font-family: 'SFMono-Regular', Consolas, 'Liberation Mono', Menlo, monospace; font-size: 13px; color: #18181b; padding: 2px 0; margin-right: 16px;">{{ $code }}</span>
                                    @endforeach
                                </td>
                            </tr>
                        </table>
                    </td>
                </tr>
            </table>
        @endif
    @else
        <p style="margin: 0 0 16px 0; font-size: 14px; line-height: 1.6; color: #52525b;">
            Two-factor authentication has been <strong style="color: #dc2626;">disabled</strong> on your account.
        </p>
        <p style="margin: 0 0 16px 0; font-size: 14px; line-height: 1.6; color: #52525b;">
            Your account is now less secure. We recommend re-enabling two-factor authentication as soon as possible.
        </p>
    @endif

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
        If you didn't make this change, please secure your account immediately.
    </p>
@endcomponent
