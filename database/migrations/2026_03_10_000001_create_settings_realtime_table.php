<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('settings_realtime', function (Blueprint $table) {
            $table->id();
            $table->string('key')->unique();
            $table->text('value')->nullable();
            $table->timestamps();
        });

        // Seed default settings
        $defaults = [
            ['key' => 'realtime_driver', 'value' => 'polling'],
            ['key' => 'realtime_fallback_driver', 'value' => 'polling'],
            ['key' => 'polling_interval_ms', 'value' => '3000'],
            ['key' => 'polling_idle_interval_ms', 'value' => '10000'],
            ['key' => 'typing_timeout_seconds', 'value' => '5'],
            ['key' => 'online_timeout_seconds', 'value' => '120'],
            // Pusher credentials (empty by default)
            ['key' => 'pusher_app_id', 'value' => ''],
            ['key' => 'pusher_key', 'value' => ''],
            ['key' => 'pusher_secret', 'value' => ''],
            ['key' => 'pusher_cluster', 'value' => 'mt1'],
            // Ably credentials (empty by default)
            ['key' => 'ably_key', 'value' => ''],
            ['key' => 'ably_client_key', 'value' => ''],
        ];

        $now = now();
        foreach ($defaults as &$row) {
            $row['created_at'] = $now;
            $row['updated_at'] = $now;
        }

        DB::table('settings_realtime')->insert($defaults);
    }

    public function down(): void
    {
        Schema::dropIfExists('settings_realtime');
    }
};
