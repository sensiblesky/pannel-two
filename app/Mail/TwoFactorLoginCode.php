<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class TwoFactorLoginCode extends Mailable
{
    use Queueable, SerializesModels;

    public function __construct(
        public string $code,
        public string $userName,
    ) {}

    public function envelope(): Envelope
    {
        return new Envelope(
            subject: 'Login Verification Code — ' . now()->format('H:i A'),
        );
    }

    public function content(): Content
    {
        return new Content(
            view: 'emails.two-factor-code',
        );
    }
}
