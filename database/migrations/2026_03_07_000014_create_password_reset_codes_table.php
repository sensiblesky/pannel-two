<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('password_reset_codes', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->onDelete('cascade');
            $table->string('code');
            $table->string('ip_address', 45);
            $table->string('fingerprint')->nullable();
            $table->unsignedTinyInteger('resend_count')->default(0);
            $table->unsignedTinyInteger('attempt_count')->default(0);
            $table->timestamp('blocked_until')->nullable();
            $table->timestamp('expires_at')->useCurrent();
            $table->timestamps();

            $table->index(['user_id', 'ip_address']);
            $table->index(['ip_address', 'fingerprint']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('password_reset_codes');
    }
};
