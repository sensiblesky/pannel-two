<?php

namespace App\Http\Controllers\App;

use App\Http\Controllers\Controller;
use App\Models\CommunicationSetting;
use App\Services\ActivityLogService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Mail;

class CommunicationSettingsController extends Controller
{
    public function index()
    {
        $settings = CommunicationSetting::allSettings();

        return view('app.settings.communication', compact('settings'));
    }

    public function update(Request $request)
    {
        $request->validate([
            'mail_driver' => ['required', 'string', 'in:smtp,sendmail,log'],
            'mail_host' => ['required_if:mail_driver,smtp', 'nullable', 'string', 'max:255'],
            'mail_port' => ['required_if:mail_driver,smtp', 'nullable', 'integer', 'min:1', 'max:65535'],
            'mail_username' => ['nullable', 'string', 'max:255'],
            'mail_password' => ['nullable', 'string', 'max:255'],
            'mail_encryption' => ['nullable', 'string', 'in:tls,ssl,none'],
            'mail_from_address' => ['required', 'email', 'max:255'],
            'mail_from_name' => ['required', 'string', 'max:255'],
        ]);

        $oldSettings = CommunicationSetting::allSettings();

        $fields = [
            'mail_driver',
            'mail_host',
            'mail_port',
            'mail_username',
            'mail_encryption',
            'mail_from_address',
            'mail_from_name',
        ];

        foreach ($fields as $field) {
            CommunicationSetting::set($field, $request->input($field));
        }

        // Only update password if provided (not empty)
        if ($request->filled('mail_password')) {
            CommunicationSetting::set('mail_password', $request->input('mail_password'));
        }

        $newSettings = CommunicationSetting::allSettings();

        $setting = CommunicationSetting::first();
        ActivityLogService::log('UPDATE', $setting, $oldSettings, $newSettings);

        return redirect()->route('config/communication')->with('success', 'Communication settings updated successfully.');
    }

    public function testEmail(Request $request)
    {
        $request->validate([
            'test_email' => ['required', 'email', 'max:255'],
        ]);

        try {
            // Apply SMTP settings from DB
            self::applySmtpSettings();

            Mail::raw('This is a test email from ' . config('app.name') . '. Your SMTP configuration is working correctly!', function ($message) use ($request) {
                $settings = CommunicationSetting::allSettings();
                $message->to($request->input('test_email'))
                    ->subject('Test Email — ' . config('app.name'))
                    ->from(
                        $settings['mail_from_address'] ?? config('mail.from.address'),
                        $settings['mail_from_name'] ?? config('mail.from.name')
                    );
            });

            return redirect()->route('config/communication')->with('success', 'Test email sent successfully to ' . $request->input('test_email'));
        } catch (\Exception $e) {
            return redirect()->route('config/communication')->with('error', 'Failed to send test email: ' . $e->getMessage());
        }
    }

    /**
     * Apply SMTP settings from the database to Laravel's mail config at runtime.
     */
    public static function applySmtpSettings(): void
    {
        $settings = CommunicationSetting::allSettings();

        if (empty($settings)) {
            return;
        }

        if (!empty($settings['mail_driver'])) {
            config(['mail.default' => $settings['mail_driver']]);
        }

        if (!empty($settings['mail_host'])) {
            config(['mail.mailers.smtp.host' => $settings['mail_host']]);
        }

        if (!empty($settings['mail_port'])) {
            config(['mail.mailers.smtp.port' => (int) $settings['mail_port']]);
        }

        if (!empty($settings['mail_username'])) {
            config(['mail.mailers.smtp.username' => $settings['mail_username']]);
        }

        if (!empty($settings['mail_password'])) {
            config(['mail.mailers.smtp.password' => $settings['mail_password']]);
        }

        $encryption = $settings['mail_encryption'] ?? null;
        config(['mail.mailers.smtp.encryption' => $encryption === 'none' ? null : $encryption]);

        if (!empty($settings['mail_from_address'])) {
            config(['mail.from.address' => $settings['mail_from_address']]);
        }

        if (!empty($settings['mail_from_name'])) {
            config(['mail.from.name' => $settings['mail_from_name']]);
        }

        // Purge the cached SMTP transport so a new one is created with updated config
        Mail::purge('smtp');
    }
}
