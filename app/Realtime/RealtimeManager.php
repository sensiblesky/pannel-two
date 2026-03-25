<?php

namespace App\Realtime;

use App\Realtime\Contracts\RealtimeDriverInterface;
use App\Realtime\Drivers\AblyDriver;
use App\Realtime\Drivers\PollingDriver;
use App\Realtime\Drivers\PusherDriver;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class RealtimeManager
{
    protected ?RealtimeDriverInterface $driver = null;
    protected ?array $settingsCache = null;

    /**
     * Resolve and return the active realtime driver.
     */
    public function driver(): RealtimeDriverInterface
    {
        if ($this->driver) {
            return $this->driver;
        }

        $settings = $this->settings();
        $driverName = $settings['realtime_driver'] ?? 'polling';

        $this->driver = $this->resolveDriver($driverName);

        // Graceful fallback: if chosen driver is not configured, use fallback.
        if (!$this->driver->isConfigured()) {
            $fallback = $settings['realtime_fallback_driver'] ?? 'polling';
            Log::info("Realtime driver [{$driverName}] not configured, falling back to [{$fallback}]");
            $this->driver = $this->resolveDriver($fallback);

            // Ultimate fallback to polling if even fallback is broken.
            if (!$this->driver->isConfigured()) {
                $this->driver = new PollingDriver();
            }
        }

        return $this->driver;
    }

    /**
     * Get the name of the currently active driver.
     */
    public function activeDriverName(): string
    {
        return $this->driver()->name();
    }

    /**
     * Build the frontend config payload — passed to the JavaScript layer.
     */
    public function frontendPayload(): array
    {
        $settings = $this->settings();
        $driver = $this->driver();

        return [
            'driver' => $driver->name(),
            'fallback_driver' => $settings['realtime_fallback_driver'] ?? 'polling',
            'polling_interval' => (int) ($settings['polling_interval_ms'] ?? 3000),
            'polling_idle_interval' => (int) ($settings['polling_idle_interval_ms'] ?? 10000),
            'typing_timeout' => (int) ($settings['typing_timeout_seconds'] ?? 5),
            'online_timeout' => (int) ($settings['online_timeout_seconds'] ?? 120),
            $driver->name() => $driver->frontendConfig(),
        ];
    }

    /**
     * Broadcast a message event through the active driver.
     */
    public function broadcastMessage(int $ticketId, array $messageData): void
    {
        try {
            $this->driver()->broadcastMessage($ticketId, $messageData);
        } catch (\Throwable $e) {
            Log::warning('Realtime broadcastMessage failed', ['error' => $e->getMessage()]);
        }
    }

    /**
     * Broadcast typing event through the active driver.
     */
    public function broadcastTyping(int $ticketId, array $typingData): void
    {
        try {
            $this->driver()->broadcastTyping($ticketId, $typingData);
        } catch (\Throwable $e) {
            Log::warning('Realtime broadcastTyping failed', ['error' => $e->getMessage()]);
        }
    }

    /**
     * Broadcast presence event through the active driver.
     */
    public function broadcastPresence(int $ticketId, array $presenceData): void
    {
        try {
            $this->driver()->broadcastPresence($ticketId, $presenceData);
        } catch (\Throwable $e) {
            Log::warning('Realtime broadcastPresence failed', ['error' => $e->getMessage()]);
        }
    }

    /**
     * Authorize a channel for the given user. Returns auth data or null.
     */
    public function authorizeChannel(string $channelName, string $socketId, $user): ?array
    {
        return $this->driver()->authorizeChannel($channelName, $socketId, $user);
    }

    /**
     * Test connection to the configured driver. Returns [ok => bool, message => string].
     */
    public function testConnection(): array
    {
        $driver = $this->driver();

        if ($driver->name() === 'polling') {
            return ['ok' => true, 'message' => 'Polling is always available.', 'driver' => 'polling'];
        }

        if (!$driver->isConfigured()) {
            return ['ok' => false, 'message' => 'Driver credentials are incomplete.', 'driver' => $driver->name()];
        }

        // Try a test broadcast.
        try {
            $driver->broadcastMessage(0, ['test' => true, 'timestamp' => now()->toIso8601String()]);
            return ['ok' => true, 'message' => ucfirst($driver->name()) . ' connection successful.', 'driver' => $driver->name()];
        } catch (\Throwable $e) {
            return ['ok' => false, 'message' => 'Connection failed: ' . $e->getMessage(), 'driver' => $driver->name()];
        }
    }

    /**
     * Force re-resolve the driver (after settings change).
     */
    public function reset(): void
    {
        $this->driver = null;
        $this->settingsCache = null;
        Cache::forget('realtime_settings');
    }

    /**
     * Load realtime settings from cache or database.
     */
    public function settings(): array
    {
        if ($this->settingsCache !== null) {
            return $this->settingsCache;
        }

        $this->settingsCache = Cache::remember('realtime_settings', 300, function () {
            return DB::table('settings_realtime')
                ->pluck('value', 'key')
                ->toArray();
        });

        return $this->settingsCache;
    }

    /**
     * Resolve a driver instance by name.
     */
    protected function resolveDriver(string $name): RealtimeDriverInterface
    {
        $settings = $this->settings();

        return match ($name) {
            'pusher' => new PusherDriver(
                $settings['pusher_app_id'] ?? '',
                $settings['pusher_key'] ?? '',
                $settings['pusher_secret'] ?? '',
                $settings['pusher_cluster'] ?? 'mt1',
            ),
            'ably' => new AblyDriver(
                $settings['ably_key'] ?? '',
                $settings['ably_client_key'] ?? '',
            ),
            default => new PollingDriver(),
        };
    }
}
