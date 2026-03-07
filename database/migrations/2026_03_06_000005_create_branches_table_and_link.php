<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('branches', function (Blueprint $table) {
            $table->id();
            $table->uuid('uuid')->unique();
            $table->string('name', 150);
            $table->string('code', 30)->nullable()->unique();
            $table->string('email', 150)->nullable();
            $table->string('phone', 50)->nullable();
            $table->text('address')->nullable();
            $table->string('city', 100)->nullable();
            $table->string('country', 100)->nullable();
            $table->boolean('status')->default(true);
            $table->foreignId('created_by')->nullable()->constrained('users')->nullOnDelete();
            $table->foreignId('deleted_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamps();
            $table->softDeletes();
        });

        Schema::table('users', function (Blueprint $table) {
            $table->foreignId('branch_id')->nullable()->after('status')->constrained('branches')->nullOnDelete();
        });

        Schema::table('customers', function (Blueprint $table) {
            $table->foreignId('branch_id')->nullable()->after('tenant_id')->constrained('branches')->nullOnDelete();
        });
    }

    public function down(): void
    {
        Schema::table('customers', function (Blueprint $table) {
            $table->dropConstrainedForeignId('branch_id');
        });

        Schema::table('users', function (Blueprint $table) {
            $table->dropConstrainedForeignId('branch_id');
        });

        Schema::dropIfExists('branches');
    }
};
