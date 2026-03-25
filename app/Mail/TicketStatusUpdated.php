<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class TicketStatusUpdated extends Mailable
{
    use Queueable, SerializesModels;

    public function __construct(
        public string $ticketNo,
        public string $ticketSubject,
        public string $statusName,
        public string $trackUrl,
    ) {}

    public function envelope(): Envelope
    {
        return new Envelope(
            subject: "Your Ticket {$this->ticketNo} has been {$this->statusName}",
        );
    }

    public function content(): Content
    {
        return new Content(
            view: 'emails.ticket-status-updated',
        );
    }
}
