@component('emails.layout', [
    'subject' => "Your Ticket {$ticketNo} has been {$statusName}",
    'preheader' => "Ticket {$ticketNo} status update: {$statusName}",
])
    <p style="margin: 0 0 20px 0; font-size: 15px; line-height: 1.6; color: #3f3f46;">
        Dear Customer,
    </p>

    <p style="margin: 0 0 16px 0; font-size: 14px; line-height: 1.6; color: #52525b;">
        We wanted to let you know that your support ticket has been marked as <strong>{{ $statusName }}</strong>.
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
                    <tr>
                        <td style="padding: 14px 16px;">
                            <p style="margin: 0; font-size: 13px; color: #52525b;">
                                <strong>Status:</strong> {{ $statusName }}
                            </p>
                        </td>
                    </tr>
                </table>
            </td>
        </tr>
    </table>

    <p style="margin: 0 0 20px 0; font-size: 14px; line-height: 1.6; color: #52525b;">
        You can view the full details of your ticket by clicking the button below:
    </p>

    <table role="presentation" cellspacing="0" cellpadding="0" border="0" width="100%">
        <tr>
            <td style="text-align: center; padding: 4px 0 20px 0;">
                <a href="{{ $trackUrl }}" target="_blank" style="display: inline-block; padding: 12px 32px; background-color: #4f46e5; color: #ffffff; font-size: 14px; font-weight: 600; text-decoration: none; border-radius: 6px;">
                    View Ticket Details
                </a>
            </td>
        </tr>
    </table>

    <p style="margin: 0; font-size: 13px; line-height: 1.5; color: #71717a;">
        If you have any further questions or concerns, please don't hesitate to reply to this ticket through the tracking page.
    </p>
@endcomponent
