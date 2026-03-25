@component('emails.layout', [
    'subject' => 'Ticket ' . $ticketNo . ' — ' . $ticketSubject,
    'preheader' => 'Your support ticket has been created. Ticket No: ' . $ticketNo,
])
    <p style="margin: 0 0 20px 0; font-size: 15px; line-height: 1.6; color: #3f3f46;">
        Dear {{ $customerName }},
    </p>

    <p style="margin: 0 0 16px 0; font-size: 14px; line-height: 1.6; color: #52525b;">
        A support ticket has been created on your behalf. Here are the details:
    </p>

    <table role="presentation" cellspacing="0" cellpadding="0" border="0" width="100%">
        <tr>
            <td style="padding: 8px 0 20px 0;">
                <table role="presentation" cellspacing="0" cellpadding="0" border="0" width="100%" style="background-color: #fafafa; border: 1px solid #e4e4e7; border-radius: 6px;">
                    <tr>
                        <td style="padding: 14px 16px; border-bottom: 1px solid #e4e4e7;">
                            <p style="margin: 0; font-size: 13px; color: #52525b;">
                                <strong>Ticket No:</strong> {{ $ticketNo }}
                            </p>
                        </td>
                    </tr>
                    <tr>
                        <td style="padding: 14px 16px; border-bottom: 1px solid #e4e4e7;">
                            <p style="margin: 0; font-size: 13px; color: #52525b;">
                                <strong>Subject:</strong> {{ $ticketSubject }}
                            </p>
                        </td>
                    </tr>
                    @if($statusName)
                    <tr>
                        <td style="padding: 14px 16px; border-bottom: 1px solid #e4e4e7;">
                            <p style="margin: 0; font-size: 13px; color: #52525b;">
                                <strong>Status:</strong> {{ $statusName }}
                            </p>
                        </td>
                    </tr>
                    @endif
                    @if($priorityName)
                    <tr>
                        <td style="padding: 14px 16px; border-bottom: 1px solid #e4e4e7;">
                            <p style="margin: 0; font-size: 13px; color: #52525b;">
                                <strong>Priority:</strong> {{ $priorityName }}
                            </p>
                        </td>
                    </tr>
                    @endif
                    <tr>
                        <td style="padding: 14px 16px;">
                            <p style="margin: 0; font-size: 13px; color: #52525b;">
                                <strong>Created:</strong> {{ $createdAt }}
                            </p>
                        </td>
                    </tr>
                </table>
            </td>
        </tr>
    </table>

    @if($description)
    <p style="margin: 0 0 8px 0; font-size: 13px; font-weight: 600; color: #3f3f46;">Description:</p>
    <div style="margin: 0 0 20px 0; padding: 12px 16px; background-color: #fafafa; border: 1px solid #e4e4e7; border-radius: 6px; font-size: 13px; line-height: 1.6; color: #52525b;">
        {!! nl2br(e($description)) !!}
    </div>
    @endif

    <p style="margin: 0 0 20px 0; font-size: 14px; line-height: 1.6; color: #52525b;">
        You can track the status of your ticket at any time by clicking the button below:
    </p>

    <table role="presentation" cellspacing="0" cellpadding="0" border="0" width="100%">
        <tr>
            <td style="text-align: center; padding: 4px 0 20px 0;">
                <a href="{{ $trackUrl }}" target="_blank" style="display: inline-block; padding: 12px 32px; background-color: #4f46e5; color: #ffffff; font-size: 14px; font-weight: 600; text-decoration: none; border-radius: 6px; mso-padding-alt: 0;">
                    <!--[if mso]><i style="letter-spacing: 32px; mso-font-width: -100%; mso-text-raise: 24pt;">&nbsp;</i><![endif]-->
                    <span style="mso-text-raise: 12pt;">Track Ticket Status</span>
                    <!--[if mso]><i style="letter-spacing: 32px; mso-font-width: -100%;">&nbsp;</i><![endif]-->
                </a>
            </td>
        </tr>
    </table>

    <p style="margin: 0; font-size: 13px; line-height: 1.5; color: #71717a;">
        Our support team will review your ticket and get back to you as soon as possible. If you have any additional information, please reply to this ticket through the tracking page.
    </p>
@endcomponent
