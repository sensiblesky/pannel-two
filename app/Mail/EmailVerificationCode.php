<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class EmailVerificationCode extends Mailable
{
    use Queueable, SerializesModels;

    public function __construct(
        public string $code,
        public string $userName,
    ) {}

    public function envelope(): Envelope
    {
        return new Envelope(
            subject: 'Verify Your Email — ' . now()->format('H:i A'),
        );
    }

    public function content(): Content
    {
        return new Content(
            view: 'emails.verify-email',
            with: [
                'code' => $this->code,
                'userName' => $this->userName,
            ],
        );
    }
}
