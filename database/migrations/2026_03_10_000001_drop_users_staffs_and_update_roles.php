<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // Update any 'manager' roles to 'agent'
        DB::table('users')->where('role', 'manager')->update(['role' => 'agent']);

        // Drop users_staffs table
        Schema::dropIfExists('users_staffs');
    }

    public function down(): void
    {
        Schema::create('users_staffs', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->unique()->constrained('users')->cascadeOnDelete();
            $table->string('employee_id', 50)->nullable();
            $table->foreignId('department_id')->nullable()->constrained('departments')->nullOnDelete();
            $table->string('position', 150)->nullable();
            $table->date('hire_date')->nullable();
            $table->text('notes')->nullable();
            $table->foreignId('created_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamps();
        });

        DB::table('users')->where('role', 'agent')->update(['role' => 'manager']);
    }
};
