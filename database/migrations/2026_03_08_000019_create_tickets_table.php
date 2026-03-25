<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('tickets', function (Blueprint $table) {
            $table->id();
            $table->uuid('uuid')->unique();
            $table->string('ticket_no', 30)->unique();
            $table->foreignId('branch_id')->nullable()->constrained('branches')->nullOnDelete();
            $table->foreignId('department_id')->nullable()->constrained('departments')->nullOnDelete();
            $table->foreignId('category_id')->nullable()->constrained('ticket_categories')->nullOnDelete();
            $table->foreignId('priority_id')->nullable()->constrained('ticket_priorities')->nullOnDelete();
            $table->foreignId('status_id')->nullable()->constrained('ticket_statuses')->nullOnDelete();
            $table->foreignId('customer_id')->nullable()->constrained('customers')->nullOnDelete();
            $table->foreignId('visitor_id')->nullable()->constrained('visitors')->nullOnDelete();
            $table->foreignId('assigned_to')->nullable()->constrained('users')->nullOnDelete();
            $table->string('subject', 255);
            $table->longText('description')->nullable();
            $table->enum('source', ['web', 'widget', 'email', 'api', 'manual', 'phone', 'chat'])->default('web');
            $table->string('reference_no', 100)->nullable();
            $table->timestamp('first_response_at')->nullable();
            $table->timestamp('last_customer_reply_at')->nullable();
            $table->timestamp('last_agent_reply_at')->nullable();
            $table->timestamp('resolved_at')->nullable();
            $table->timestamp('closed_at')->nullable();
            $table->timestamp('due_at')->nullable();
            $table->boolean('is_locked')->default(false);
            $table->boolean('is_spam')->default(false);
            $table->boolean('status')->default(true);
            $table->foreignId('created_by')->nullable()->constrained('users')->nullOnDelete();
            $table->foreignId('deleted_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamps();
            $table->softDeletes();

            $table->index('branch_id', 'tickets_branch_idx');
            $table->index('department_id', 'tickets_department_idx');
            $table->index('category_id', 'tickets_category_idx');
            $table->index('priority_id', 'tickets_priority_idx');
            $table->index('status_id', 'tickets_status_idx');
            $table->index('customer_id', 'tickets_customer_idx');
            $table->index('visitor_id', 'tickets_visitor_idx');
            $table->index('assigned_to', 'tickets_assigned_idx');
            $table->index('created_at', 'tickets_created_at_idx');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('tickets');
    }
};
