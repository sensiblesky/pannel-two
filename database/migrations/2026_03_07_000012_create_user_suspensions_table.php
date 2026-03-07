<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('user_suspensions', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->text('reason');
            $table->foreignId('suspended_by')->constrained('users')->cascadeOnDelete();
            $table->foreignId('unsuspended_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamp('suspended_at');
            $table->timestamp('unsuspended_at')->nullable();
            $table->timestamps();

            $table->index(['user_id', 'unsuspended_at']);
        });

        Schema::table('users', function (Blueprint $table) {
            $table->boolean('is_suspended')->default(false)->after('status');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('user_suspensions');

        Schema::table('users', function (Blueprint $table) {
            $table->dropColumn('is_suspended');
        });
    }
};
