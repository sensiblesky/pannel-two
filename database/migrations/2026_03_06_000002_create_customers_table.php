<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('customers', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('tenant_id');
            $table->uuid('uuid');
            $table->string('first_name', 100)->nullable();
            $table->string('middle_name', 100)->nullable();
            $table->string('last_name', 100)->nullable();
            $table->string('email', 150)->nullable();
            $table->string('phone', 50)->nullable();
            $table->string('avatar', 255)->nullable();
            $table->string('company', 150)->nullable();
            $table->enum('source', ['widget', 'ticket', 'import', 'api', 'manual'])->default('widget');
            $table->string('external_id', 150)->nullable();
            $table->unsignedBigInteger('visitor_id')->nullable();
            $table->string('country', 100)->nullable();
            $table->string('city', 100)->nullable();
            $table->string('ip_address', 45)->nullable();
            $table->text('user_agent')->nullable();
            $table->timestamp('last_seen_at')->nullable();
            $table->timestamp('last_message_at')->nullable();
            $table->enum('status', ['active', 'blocked', 'deleted'])->default('active');
            $table->text('notes')->nullable();
            $table->timestamps();

            $table->index('tenant_id', 'idx_tenant');
            $table->index('email', 'idx_email');
            $table->index('phone', 'idx_phone');
            $table->index('visitor_id', 'idx_visitor');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('customers');
    }
};
