<?php

namespace App\Http\Controllers\App;

use App\Http\Controllers\Controller;
use App\Models\RealtimeSetting;
use App\Realtime\RealtimeManager;
use App\Services\ActivityLogService;
use Illuminate\Http\Request;

class RealtimeSettingsController extends Controller
{
    public function index()
    {
        $settings = RealtimeSetting::allSettings();

        return view('app.settings.realtime', compact('settings'));
    }

    public function update(Request $request)
    {
        $request->validate([
            'realtime_driver' => ['required', 'string', 'in:polling,pusher,ably'],
            'realtime_fallback_driver' => ['required', 'string', 'in:polling,pusher,ably'],
            'polling_interval_ms' => ['required', 'integer', 'min:1000', 'max:60000'],
            'polling_idle_interval_ms' => ['required', 'integer', 'min:5000', 'max:120000'],
            'typing_timeout_seconds' => ['required', 'integer', 'min:1', 'max:30'],
            'online_timeout_seconds' => ['required', 'integer', 'min:30', 'max:600'],
            'pusher_app_id' => ['nullable', 'string', 'max:255'],
            'pusher_key' => ['nullable', 'string', 'max:255'],
            'pusher_secret' => ['nullable', 'string', 'max:255'],
            'pusher_cluster' => ['nullable', 'string', 'max:50'],
            'ably_key' => ['nullable', 'string', 'max:255'],
            'ably_client_key' => ['nullable', 'string', 'max:255'],
        ]);

        $oldSettings = RealtimeSetting::allSettings();

        $fields = [
            'realtime_driver',
            'realtime_fallback_driver',
            'polling_interval_ms',
            'polling_idle_interval_ms',
            'typing_timeout_seconds',
            'online_timeout_seconds',
            'pusher_app_id',
            'pusher_key',
            'pusher_secret',
            'pusher_cluster',
            'ably_key',
            'ably_client_key',
        ];

        foreach ($fields as $field) {
            RealtimeSetting::set($field, $request->input($field));
        }

        // Reset the cached driver instance
        app(RealtimeManager::class)->reset();

        $newSettings = RealtimeSetting::allSettings();

        $setting = RealtimeSetting::first();
        if ($setting) {
            ActivityLogService::log('UPDATE', $setting, $oldSettings, $newSettings);
        }

        return redirect()->route('config/realtime')->with('success', 'Realtime settings updated successfully.');
    }

    public function testConnection(Request $request)
    {
        try {
            $result = app(RealtimeManager::class)->testConnection();

            return response()->json([
                'success' => $result['success'],
                'message' => $result['message'] ?? ($result['success'] ? 'Connection successful!' : 'Connection failed.'),
                'driver' => $result['driver'] ?? null,
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Connection test failed: ' . $e->getMessage(),
            ]);
        }
    }
}
