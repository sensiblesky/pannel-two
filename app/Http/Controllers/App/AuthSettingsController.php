<?php

namespace App\Http\Controllers\App;

use App\Http\Controllers\Controller;
use App\Models\AuthSetting;
use App\Services\ActivityLogService;
use Illuminate\Http\Request;

class AuthSettingsController extends Controller
{
    public function index()
    {
        $settings = AuthSetting::allSettings();

        return view('app.settings.authentication', compact('settings'));
    }

    public function update(Request $request)
    {
        $request->validate([
            'registration_enabled' => ['nullable', 'boolean'],
            'email_verification_required' => ['nullable', 'boolean'],
            'social_login_enabled' => ['nullable', 'boolean'],
            'social_login_only_connected' => ['nullable', 'boolean'],
            'single_device_login' => ['nullable', 'boolean'],
            'domain_blacklist' => ['nullable', 'string', 'max:5000'],
            'google_login_enabled' => ['nullable', 'boolean'],
            'google_client_id' => ['nullable', 'string', 'max:500'],
            'google_client_secret' => ['nullable', 'string', 'max:500'],
            'facebook_login_enabled' => ['nullable', 'boolean'],
            'facebook_client_id' => ['nullable', 'string', 'max:500'],
            'facebook_client_secret' => ['nullable', 'string', 'max:500'],
            'twitter_login_enabled' => ['nullable', 'boolean'],
            'twitter_client_id' => ['nullable', 'string', 'max:500'],
            'twitter_client_secret' => ['nullable', 'string', 'max:500'],
        ]);

        $oldSettings = AuthSetting::allSettings();

        // Boolean toggles
        $booleanFields = [
            'registration_enabled',
            'email_verification_required',
            'social_login_enabled',
            'social_login_only_connected',
            'single_device_login',
            'google_login_enabled',
            'facebook_login_enabled',
            'twitter_login_enabled',
        ];

        foreach ($booleanFields as $field) {
            AuthSetting::set($field, $request->boolean($field) ? '1' : '0');
        }

        // Text fields
        $textFields = [
            'domain_blacklist',
            'google_client_id',
            'google_client_secret',
            'facebook_client_id',
            'facebook_client_secret',
            'twitter_client_id',
            'twitter_client_secret',
        ];

        foreach ($textFields as $field) {
            AuthSetting::set($field, $request->input($field));
        }

        $newSettings = AuthSetting::allSettings();

        $setting = AuthSetting::first();
        ActivityLogService::log('UPDATE', $setting, $oldSettings, $newSettings);

        return redirect()->route('config/authentication')->with('success', 'Authentication settings updated successfully.');
    }
}
