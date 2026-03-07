<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->uuid('uid')->nullable()->after('id');
        });

        // Generate UIDs for existing users
        DB::table('users')->orderBy('id')->each(function ($user) {
            DB::table('users')->where('id', $user->id)->update(['uid' => Str::uuid()->toString()]);
        });

        Schema::table('users', function (Blueprint $table) {
            $table->uuid('uid')->nullable(false)->unique()->change();
        });
    }

    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropColumn('uid');
        });
    }
};
