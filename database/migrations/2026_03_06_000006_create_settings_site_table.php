<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('settings_site_general', function (Blueprint $table) {
            $table->id();
            $table->string('key')->unique();
            $table->text('value')->nullable();
            $table->timestamps();
        });

        // Seed default settings
        $defaults = [
            'site_name' => 'LineOne',
            'site_email' => null,
            'site_phone' => null,
            'logo_light' => null,
            'logo_dark' => null,
            'logo_compact' => null,
            'favicon' => null,
            'maintenance_mode' => '0',
        ];

        foreach ($defaults as $key => $value) {
            DB::table('settings_site_general')->insert([
                'key' => $key,
                'value' => $value,
                'created_at' => now(),
                'updated_at' => now(),
            ]);
        }
    }

    public function down(): void
    {
        Schema::dropIfExists('settings_site_general');
    }
};
