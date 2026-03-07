<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        // Add maintenance_end_at to site general settings
        DB::table('settings_site_general')->insert([
            'key' => 'maintenance_end_at',
            'value' => null,
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        // Create auth settings table
        Schema::create('settings_site_auth', function (Blueprint $table) {
            $table->id();
            $table->string('key')->unique();
            $table->text('value')->nullable();
            $table->timestamps();
        });

        $defaults = [
            'registration_enabled' => '1',
            'email_verification_required' => '0',
            'social_login_enabled' => '0',
            'social_login_only_connected' => '0',
            'single_device_login' => '0',
            'domain_blacklist' => null,
            'google_login_enabled' => '0',
            'google_client_id' => null,
            'google_client_secret' => null,
            'facebook_login_enabled' => '0',
            'facebook_client_id' => null,
            'facebook_client_secret' => null,
            'twitter_login_enabled' => '0',
            'twitter_client_id' => null,
            'twitter_client_secret' => null,
        ];

        foreach ($defaults as $key => $value) {
            DB::table('settings_site_auth')->insert([
                'key' => $key,
                'value' => $value,
                'created_at' => now(),
                'updated_at' => now(),
            ]);
        }
    }

    public function down(): void
    {
        DB::table('settings_site_general')->where('key', 'maintenance_end_at')->delete();
        Schema::dropIfExists('settings_site_auth');
    }
};
