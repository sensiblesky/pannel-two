<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('users_agents', function (Blueprint $table) {
            $table->id();
            $table->uuid('uuid')->unique();
            $table->foreignId('user_id')->constrained('users')->cascadeOnDelete();
            $table->string('display_name', 150)->nullable();
            $table->string('specialization', 200)->nullable();
            $table->unsignedInteger('max_tickets')->nullable();
            $table->boolean('is_available')->default(true);
            $table->boolean('status')->default(true);
            $table->foreignId('created_by')->nullable()->constrained('users')->nullOnDelete();
            $table->foreignId('deleted_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamps();
            $table->softDeletes();

            $table->unique('user_id', 'users_agents_user_unique');
        });

        Schema::create('agent_department', function (Blueprint $table) {
            $table->id();
            $table->foreignId('agent_id')->constrained('users_agents')->cascadeOnDelete();
            $table->foreignId('department_id')->constrained('departments')->cascadeOnDelete();
            $table->foreignId('created_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamp('created_at')->nullable();

            $table->unique(['agent_id', 'department_id'], 'agent_department_unique');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('agent_department');
        Schema::dropIfExists('users_agents');
    }
};
