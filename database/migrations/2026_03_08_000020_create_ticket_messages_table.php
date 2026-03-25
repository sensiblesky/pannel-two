<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('ticket_messages', function (Blueprint $table) {
            $table->id();
            $table->uuid('uuid')->unique();
            $table->foreignId('ticket_id')->constrained('tickets')->cascadeOnDelete();
            $table->enum('sender_type', ['user', 'customer', 'visitor', 'system']);
            $table->unsignedBigInteger('sender_id')->nullable();
            $table->enum('message_type', ['reply', 'note', 'system'])->default('reply');
            $table->longText('message')->nullable();
            $table->boolean('is_internal')->default(false);
            $table->boolean('is_first_response')->default(false);
            $table->boolean('status')->default(true);
            $table->foreignId('created_by')->nullable()->constrained('users')->nullOnDelete();
            $table->foreignId('deleted_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamps();
            $table->softDeletes();

            $table->index('ticket_id', 'ticket_messages_ticket_idx');
            $table->index(['sender_type', 'sender_id'], 'ticket_messages_sender_idx');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('ticket_messages');
    }
};
