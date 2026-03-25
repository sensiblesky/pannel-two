<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('canned_responses', function (Blueprint $table) {
            $table->id();
            $table->uuid('uuid')->unique();
            $table->string('title', 255);
            $table->string('shortcut', 100)->nullable()->index();
            $table->text('message');
            $table->string('category', 100)->nullable()->index();
            $table->boolean('is_shared')->default(true);
            $table->unsignedBigInteger('created_by')->nullable();
            $table->timestamps();
            $table->timestamp('deleted_at')->nullable();
            $table->unsignedBigInteger('deleted_by')->nullable();

            $table->index(['is_shared', 'created_by']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('canned_responses');
    }
};
