<?php

namespace App\Jobs;

use App\Http\Controllers\App\CommunicationSettingsController;
use App\Mail\EmailVerificationCode;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Mail;

class SendVerificationEmailJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public int $tries = 3;

    public function __construct(
        public string $email,
        public string $code,
        public string $userName,
    ) {}

    public function handle(): void
    {
        // Apply DB SMTP settings in the queue worker context
        CommunicationSettingsController::applySmtpSettings();

        // Force a fresh SMTP transport with the updated config
        Mail::purge('smtp');

        // Send synchronously within the job (already queued via the job itself)
        $mailable = new EmailVerificationCode($this->code, $this->userName);
        Mail::mailer(config('mail.default'))->to($this->email)->send($mailable);
    }
}
