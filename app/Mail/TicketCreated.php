<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class TicketCreated extends Mailable
{
    use Queueable, SerializesModels;

    public function __construct(
        public string $customerName,
        public string $ticketNo,
        public string $ticketSubject,
        public ?string $description,
        public ?string $statusName,
        public ?string $priorityName,
        public string $createdAt,
        public string $trackUrl,
    ) {}

    public function envelope(): Envelope
    {
        return new Envelope(
            subject: "Ticket {$this->ticketNo} — {$this->ticketSubject}",
        );
    }

    public function content(): Content
    {
        return new Content(
            view: 'emails.ticket-created',
        );
    }
}
