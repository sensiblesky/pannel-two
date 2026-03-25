<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('ticket_events', function (Blueprint $table) {
            $table->id();
            $table->uuid('uuid')->unique();
            $table->foreignId('ticket_id')->constrained('tickets')->cascadeOnDelete();
            $table->string('event_type', 50);
            $table->enum('actor_type', ['user', 'customer', 'visitor', 'system'])->default('system');
            $table->unsignedBigInteger('actor_id')->nullable();
            $table->text('old_value')->nullable();
            $table->text('new_value')->nullable();
            $table->json('meta')->nullable();
            $table->timestamp('created_at')->nullable();

            $table->index('ticket_id', 'ticket_events_ticket_idx');
            $table->index('event_type', 'ticket_events_type_idx');
            $table->index(['actor_type', 'actor_id'], 'ticket_events_actor_idx');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('ticket_events');
    }
};
