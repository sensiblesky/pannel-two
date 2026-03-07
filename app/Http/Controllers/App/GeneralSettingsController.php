<?php

namespace App\Http\Controllers\App;

use App\Http\Controllers\Controller;
use App\Models\SiteSetting;
use App\Services\ActivityLogService;
use Illuminate\Http\Request;

class GeneralSettingsController extends Controller
{
    public function index()
    {
        $settings = SiteSetting::allSettings();

        return view('app.settings.general', compact('settings'));
    }

    public function update(Request $request)
    {
        $request->validate([
            'site_name' => ['required', 'string', 'max:255'],
            'site_email' => ['nullable', 'email', 'max:255'],
            'site_phone' => ['nullable', 'string', 'max:50'],
            'logo_light' => ['nullable', 'image', 'max:2048'],
            'logo_dark' => ['nullable', 'image', 'max:2048'],
            'logo_compact' => ['nullable', 'image', 'max:2048'],
            'favicon' => ['nullable', 'image', 'max:1024'],
            'maintenance_mode' => ['nullable', 'boolean'],
            'maintenance_end_at' => ['nullable', 'date', 'after:now'],
        ]);

        $oldSettings = SiteSetting::allSettings();

        // Text fields
        SiteSetting::set('site_name', $request->input('site_name'));
        SiteSetting::set('site_email', $request->input('site_email'));
        SiteSetting::set('site_phone', $request->input('site_phone'));
        SiteSetting::set('maintenance_mode', $request->boolean('maintenance_mode') ? '1' : '0');
        SiteSetting::set('maintenance_end_at', $request->boolean('maintenance_mode') ? $request->input('maintenance_end_at') : null);

        // File uploads
        foreach (['logo_light', 'logo_dark', 'logo_compact', 'favicon'] as $field) {
            if ($request->hasFile($field)) {
                $path = $request->file($field)->store('settings', 'public');
                SiteSetting::set($field, $path);
            }
        }

        $newSettings = SiteSetting::allSettings();

        // Log changes
        $setting = SiteSetting::first();
        ActivityLogService::log('UPDATE', $setting, $oldSettings, $newSettings);

        return redirect()->route('config/general')->with('success', 'Settings updated successfully.');
    }
}
