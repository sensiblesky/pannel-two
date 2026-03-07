<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class TwoFactorAlert extends Mailable
{
    use Queueable, SerializesModels;

    public function __construct(
        public string $userName,
        public bool $enabled,
        public array $recoveryCodes = [],
    ) {}

    public function envelope(): Envelope
    {
        $action = $this->enabled ? 'Enabled' : 'Disabled';

        return new Envelope(
            subject: "Two-Factor Authentication {$action} — " . now()->format('H:i A'),
        );
    }

    public function content(): Content
    {
        return new Content(
            view: 'emails.two-factor-alert',
            with: [
                'userName' => $this->userName,
                'enabled' => $this->enabled,
                'recoveryCodes' => $this->recoveryCodes,
            ],
        );
    }
}
