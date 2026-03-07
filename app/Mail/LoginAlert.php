<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class LoginAlert extends Mailable
{
    use Queueable, SerializesModels;

    public function __construct(
        public string $userName,
        public string $ipAddress,
        public string $device,
        public string $location,
        public string $loginAt,
    ) {}

    public function envelope(): Envelope
    {
        return new Envelope(
            subject: 'New Login to Your Account — ' . $this->loginAt,
        );
    }

    public function content(): Content
    {
        return new Content(
            view: 'emails.login-alert',
        );
    }
}
