<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class TicketSeeder extends Seeder
{
    public function run(): void
    {
        $now = now();

        // Default ticket statuses
        $statuses = [
            ['name' => 'New',      'code' => 'new',      'color' => '#3B82F6', 'sort_order' => 1, 'is_closed' => false],
            ['name' => 'Open',     'code' => 'open',     'color' => '#10B981', 'sort_order' => 2, 'is_closed' => false],
            ['name' => 'Pending',  'code' => 'pending',  'color' => '#F59E0B', 'sort_order' => 3, 'is_closed' => false],
            ['name' => 'Resolved', 'code' => 'resolved', 'color' => '#8B5CF6', 'sort_order' => 4, 'is_closed' => false],
            ['name' => 'Closed',   'code' => 'closed',   'color' => '#6B7280', 'sort_order' => 5, 'is_closed' => true],
        ];

        foreach ($statuses as $status) {
            DB::table('ticket_statuses')->updateOrInsert(
                ['code' => $status['code']],
                [
                    'uuid'       => Str::uuid(),
                    'name'       => $status['name'],
                    'code'       => $status['code'],
                    'color'      => $status['color'],
                    'sort_order' => $status['sort_order'],
                    'is_closed'  => $status['is_closed'],
                    'status'     => true,
                    'created_at' => $now,
                    'updated_at' => $now,
                ]
            );
        }

        // Default ticket priorities
        $priorities = [
            ['name' => 'Low',    'code' => 'low',    'color' => '#10B981', 'sort_order' => 1],
            ['name' => 'Medium', 'code' => 'medium', 'color' => '#F59E0B', 'sort_order' => 2],
            ['name' => 'High',   'code' => 'high',   'color' => '#F97316', 'sort_order' => 3],
            ['name' => 'Urgent', 'code' => 'urgent', 'color' => '#EF4444', 'sort_order' => 4],
        ];

        foreach ($priorities as $priority) {
            DB::table('ticket_priorities')->updateOrInsert(
                ['code' => $priority['code']],
                [
                    'uuid'       => Str::uuid(),
                    'name'       => $priority['name'],
                    'code'       => $priority['code'],
                    'color'      => $priority['color'],
                    'sort_order' => $priority['sort_order'],
                    'status'     => true,
                    'created_at' => $now,
                    'updated_at' => $now,
                ]
            );
        }

        // Default ticket settings
        $settings = [
            ['key' => 'categories_enabled',       'value' => '1',    'type' => 'boolean', 'description' => 'Enable ticket categories'],
            ['key' => 'tags_enabled',              'value' => '1',    'type' => 'boolean', 'description' => 'Enable ticket tags'],
            ['key' => 'priorities_enabled',        'value' => '1',    'type' => 'boolean', 'description' => 'Enable ticket priorities'],
            ['key' => 'watchers_enabled',          'value' => '1',    'type' => 'boolean', 'description' => 'Enable ticket watchers'],
            ['key' => 'attachments_enabled',       'value' => '1',    'type' => 'boolean', 'description' => 'Enable ticket attachments'],
            ['key' => 'internal_notes_enabled',    'value' => '1',    'type' => 'boolean', 'description' => 'Enable internal notes on tickets'],
            ['key' => 'visitors_can_create',       'value' => '0',    'type' => 'boolean', 'description' => 'Allow visitors to create tickets'],
            ['key' => 'auto_ticket_number',        'value' => '1',    'type' => 'boolean', 'description' => 'Auto-generate ticket numbers'],
            ['key' => 'ticket_number_prefix',      'value' => 'TKT-', 'type' => 'string',  'description' => 'Prefix for auto-generated ticket numbers'],
            ['key' => 'default_status',            'value' => 'new',  'type' => 'string',  'description' => 'Default status code for new tickets'],
            ['key' => 'default_priority',          'value' => 'medium', 'type' => 'string', 'description' => 'Default priority code for new tickets'],
            ['key' => 'default_department',        'value' => null,   'type' => 'string',  'description' => 'Default department ID for new tickets'],
            ['key' => 'allowed_attachment_types',   'value' => '["image/jpeg","image/png","image/gif","application/pdf","application/msword","application/vnd.openxmlformats-officedocument.wordprocessingml.document","text/plain"]', 'type' => 'json', 'description' => 'Allowed MIME types for attachments'],
            ['key' => 'max_attachment_size',        'value' => '10',   'type' => 'number',  'description' => 'Maximum attachment size in MB'],
        ];

        foreach ($settings as $setting) {
            DB::table('ticket_settings')->updateOrInsert(
                ['key' => $setting['key'], 'branch_id' => null],
                [
                    'uuid'        => Str::uuid(),
                    'branch_id'   => null,
                    'key'         => $setting['key'],
                    'value'       => $setting['value'],
                    'type'        => $setting['type'],
                    'description' => $setting['description'],
                    'status'      => true,
                    'created_at'  => $now,
                    'updated_at'  => $now,
                ]
            );
        }
    }
}
