<?php

namespace App\Jobs;

use App\Http\Controllers\App\CommunicationSettingsController;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Mail\Mailable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Mail;

class SendMailJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public int $tries = 3;

    public function __construct(
        public string $to,
        public Mailable $mailable,
    ) {}

    public function handle(): void
    {
        CommunicationSettingsController::applySmtpSettings();
        Mail::purge('smtp');
        Mail::mailer(config('mail.default'))->to($this->to)->send($this->mailable);
    }
}
