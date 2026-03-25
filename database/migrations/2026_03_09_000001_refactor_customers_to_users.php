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
        // 1. Create users_customers table for customer-specific info
        Schema::create('users_customers', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->unique()->constrained('users')->cascadeOnDelete();
            $table->string('company', 150)->nullable();
            $table->enum('source', ['web', 'widget', 'ticket', 'import', 'api', 'manual', 'help_center'])->default('manual');
            $table->string('external_id', 150)->nullable();
            $table->string('country', 100)->nullable();
            $table->string('city', 100)->nullable();
            $table->string('ip_address', 45)->nullable();
            $table->text('user_agent')->nullable();
            $table->timestamp('last_seen_at')->nullable();
            $table->timestamp('last_message_at')->nullable();
            $table->text('notes')->nullable();
            $table->foreignId('created_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamps();
        });

        // 2. Create users_staffs table for staff-specific info
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

        // 3. Drop the old foreign key on tickets.customer_id BEFORE migrating data
        $fks = DB::select("SELECT CONSTRAINT_NAME FROM information_schema.KEY_COLUMN_USAGE WHERE TABLE_NAME = 'tickets' AND COLUMN_NAME = 'customer_id' AND REFERENCED_TABLE_NAME IS NOT NULL AND TABLE_SCHEMA = DATABASE()");
        if (count($fks) > 0) {
            Schema::table('tickets', function (Blueprint $table) use ($fks) {
                $table->dropForeign($fks[0]->CONSTRAINT_NAME);
            });
        }

        // 4. Migrate existing customers into users table + users_customers
        $customers = DB::table('customers')->get();
        foreach ($customers as $customer) {
            $name = trim(($customer->first_name ?? '') . ' ' . ($customer->last_name ?? ''));
            if (empty($name)) {
                $name = $customer->email ?? 'Customer #' . $customer->id;
            }

            // Check if user with this email already exists (including soft-deleted)
            $existingUser = null;
            if ($customer->email) {
                $existingUser = DB::table('users')->where('email', $customer->email)->first();
            }

            if ($existingUser) {
                $userId = $existingUser->id;
            } else {
                $userId = DB::table('users')->insertGetId([
                    'uid' => Str::uuid(),
                    'name' => $name,
                    'email' => $customer->email,
                    'phone' => $customer->phone,
                    'role' => 'customer',
                    'status' => $customer->status === 'active' ? true : false,
                    'avatar' => $customer->avatar,
                    'password' => bcrypt(Str::random(32)),
                    'created_at' => $customer->created_at,
                    'updated_at' => $customer->updated_at,
                    'deleted_at' => $customer->deleted_at ?? null,
                ]);
            }

            // Create users_customers record
            $exists = DB::table('users_customers')->where('user_id', $userId)->exists();
            if (!$exists) {
                DB::table('users_customers')->insert([
                    'user_id' => $userId,
                    'company' => $customer->company,
                    'source' => in_array($customer->source, ['web', 'widget', 'ticket', 'import', 'api', 'manual', 'help_center']) ? $customer->source : 'manual',
                    'external_id' => $customer->external_id,
                    'country' => $customer->country,
                    'city' => $customer->city,
                    'ip_address' => $customer->ip_address,
                    'user_agent' => $customer->user_agent,
                    'last_seen_at' => $customer->last_seen_at,
                    'last_message_at' => $customer->last_message_at,
                    'notes' => $customer->notes ?? null,
                    'created_at' => $customer->created_at,
                    'updated_at' => $customer->updated_at,
                ]);
            }

            // Update tickets that reference this customer to point to the new user
            DB::table('tickets')->where('customer_id', $customer->id)->update(['customer_id' => $userId]);

            // Update ticket_messages with sender_type='customer' and sender_id = old customer id
            DB::table('ticket_messages')
                ->where('sender_type', 'customer')
                ->where('sender_id', $customer->id)
                ->update(['sender_id' => $userId]);

            // Update ticket_attachments with uploaded_by_type='customer'
            DB::table('ticket_attachments')
                ->where('uploaded_by_type', 'customer')
                ->where('uploaded_by_id', $customer->id)
                ->update(['uploaded_by_id' => $userId]);

            // Update ticket_events with actor_type='customer'
            DB::table('ticket_events')
                ->where('actor_type', 'customer')
                ->where('actor_id', $customer->id)
                ->update(['actor_id' => $userId]);
        }

        // 5. Re-add FK on tickets.customer_id pointing to users
        Schema::table('tickets', function (Blueprint $table) {
            $table->foreign('customer_id')->references('id')->on('users')->nullOnDelete();
        });

        // 6. Drop the old customers table
        DB::statement('SET FOREIGN_KEY_CHECKS=0');
        Schema::dropIfExists('customers');
        DB::statement('SET FOREIGN_KEY_CHECKS=1');
    }

    public function down(): void
    {
        // Re-create customers table
        Schema::create('customers', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('tenant_id')->default(0);
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
            $table->softDeletes();
        });

        // Repoint tickets FK back to customers
        Schema::table('tickets', function (Blueprint $table) {
            $table->dropForeign(['customer_id']);
            $table->foreign('customer_id')->references('id')->on('customers')->nullOnDelete();
        });

        Schema::dropIfExists('users_staffs');
        Schema::dropIfExists('users_customers');
    }
};
