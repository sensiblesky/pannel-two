<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class SandboxTicketSeeder extends Seeder
{
    public function run(): void
    {
        $now = now();

        // ── 1. Categories ─────────────────────────────────────────────────────
        $categoryDefs = [
            ['name' => 'Technical Support',   'code' => 'technical_support',   'description' => 'Hardware, software and system issues'],
            ['name' => 'Billing & Payments',  'code' => 'billing_payments',    'description' => 'Invoice, payment and refund queries'],
            ['name' => 'Account Management',  'code' => 'account_management',  'description' => 'Account setup, access and profile changes'],
            ['name' => 'Service Request',     'code' => 'service_request',     'description' => 'New service provisioning and changes'],
            ['name' => 'General Inquiry',     'code' => 'general_inquiry',     'description' => 'General questions and information requests'],
            ['name' => 'Bug Report',          'code' => 'bug_report',          'description' => 'System bugs and unexpected behaviour'],
            ['name' => 'Feature Request',     'code' => 'feature_request',     'description' => 'Suggestions for new features'],
        ];

        $categoryIds = [];
        foreach ($categoryDefs as $cat) {
            $existing = DB::table('ticket_categories')->where('code', $cat['code'])->first();
            if ($existing) {
                $categoryIds[] = $existing->id;
            } else {
                $id = DB::table('ticket_categories')->insertGetId([
                    'uuid'        => (string) Str::uuid(),
                    'name'        => $cat['name'],
                    'code'        => $cat['code'],
                    'description' => $cat['description'],
                    'status'      => true,
                    'created_at'  => $now,
                    'updated_at'  => $now,
                ]);
                $categoryIds[] = $id;
            }
        }

        // ── 2. SLA settings ───────────────────────────────────────────────────
        DB::table('ticket_settings')->updateOrInsert(
            ['key' => 'sla_response_hours', 'branch_id' => null],
            ['uuid' => (string) Str::uuid(), 'key' => 'sla_response_hours', 'value' => '4', 'type' => 'number',
             'branch_id' => null, 'description' => 'SLA first response target (hours)', 'status' => true,
             'created_at' => $now, 'updated_at' => $now]
        );
        DB::table('ticket_settings')->updateOrInsert(
            ['key' => 'sla_resolution_hours', 'branch_id' => null],
            ['uuid' => (string) Str::uuid(), 'key' => 'sla_resolution_hours', 'value' => '24', 'type' => 'number',
             'branch_id' => null, 'description' => 'SLA resolution target (hours)', 'status' => true,
             'created_at' => $now, 'updated_at' => $now]
        );

        // ── 3. Fetch reference data from DB ───────────────────────────────────
        $statuses    = DB::table('ticket_statuses')->get()->keyBy('code');
        $priorities  = DB::table('ticket_priorities')->get()->keyBy('code');
        $agentIds    = DB::table('users')->where('role', 'agent')->whereNull('deleted_at')
                          ->where('status', true)->pluck('id')->toArray();
        $customerIds = DB::table('users')->where('role', 'customer')->whereNull('deleted_at')
                          ->pluck('id')->toArray();
        $branchIds   = DB::table('branches')->whereNull('deleted_at')->where('status', true)->pluck('id')->toArray();

        if (empty($agentIds) || empty($customerIds)) {
            $this->command->warn('No agents or customers found — skipping ticket creation.');
            return;
        }

        // ── 4. Subjects by category ───────────────────────────────────────────
        $subjects = [
            'technical_support'  => [
                'System login page not loading',
                'Unable to connect to VPN',
                'Printer not recognized on network',
                'Email client sync failing',
                'Dashboard loading very slowly',
                'Two-factor authentication not working',
                'File upload returning error 500',
                'Mobile app crashing on startup',
                'Password reset link expired immediately',
                'Search results returning incorrect data',
            ],
            'billing_payments'   => [
                'Overcharged on last invoice',
                'Payment failed but amount deducted',
                'Request for invoice copy',
                'Refund not received after 7 days',
                'Subscription renewal not processed',
                'Incorrect tax amount on receipt',
                'Need to update billing address',
                'Card declined despite valid details',
                'Annual plan discount not applied',
                'Invoice shows wrong service period',
            ],
            'account_management' => [
                'Cannot change account email address',
                'Account locked after multiple attempts',
                'Request to add new team member',
                'Transfer account ownership',
                'Delete sub-account',
                'Two accounts merged incorrectly',
                'Profile picture not updating',
                'Notification preferences not saving',
                'Account timezone showing wrong region',
                'API key expired, need new one',
            ],
            'service_request'    => [
                'Request to upgrade service plan',
                'Enable bulk SMS feature for account',
                'Provision new branch workspace',
                'Need read-only access for auditor',
                'Request for data export in CSV',
                'Schedule maintenance window',
                'Enable white-label branding option',
                'Request custom domain setup',
                'Increase storage quota',
                'Enable webhook notifications',
            ],
            'general_inquiry'    => [
                'What are your business hours?',
                'How do I integrate with Zapier?',
                'Where can I find the API documentation?',
                'Is there a mobile app available?',
                'What file formats are supported?',
                'How long is data retained?',
                'Can I export my full ticket history?',
                'What payment methods do you accept?',
                'Do you offer onboarding training?',
                'SLA policy details request',
            ],
            'bug_report'         => [
                'Report status not updating in real time',
                'Export button produces empty file',
                'Date picker shows wrong month',
                'Attachment preview broken in Firefox',
                'Notifications not clearing after read',
                'Table sorting reverting on page refresh',
                'Charts not rendering on Safari',
                'Copy-paste strips formatting in editor',
                'Bulk assign not saving changes',
                'Search filter resets after navigation',
            ],
            'feature_request'    => [
                'Add dark mode to customer portal',
                'Allow multiple assignees per ticket',
                'Canned response keyboard shortcut',
                'Email-to-ticket auto-routing',
                'Custom fields on ticket form',
                'SLA pause on pending status',
                'Merge duplicate tickets feature',
                'Customer satisfaction survey integration',
                'Scheduled ticket reports via email',
                'Slack notification integration',
            ],
        ];

        // ── 5. Build 200 tickets ──────────────────────────────────────────────
        $counter = DB::table('tickets')->max(DB::raw('CAST(SUBSTRING(ticket_no, 5) AS UNSIGNED)')) ?? 1000;

        $tickets = [];
        $sources = ['web', 'manual', 'email', 'phone', 'chat'];

        for ($i = 0; $i < 200; $i++) {
            $counter++;
            $ticketNo  = 'TKT-' . str_pad($counter, 5, '0', STR_PAD_LEFT);

            // Spread over last 30 days, newer tickets more frequent
            $daysAgo     = fake()->numberBetween(0, 29);
            $hoursAgo    = fake()->numberBetween(0, 23);
            $createdAt   = now()->subDays($daysAgo)->subHours($hoursAgo);

            // Pick category
            $catCode     = array_keys($subjects)[array_rand(array_keys($subjects))];
            $catId       = DB::table('ticket_categories')->where('code', $catCode)->value('id');
            $subjectList = $subjects[$catCode];
            $subject     = $subjectList[array_rand($subjectList)];

            // Priority — weighted: 40% medium, 25% low, 20% high, 15% urgent
            $priorityRoll = fake()->numberBetween(1, 100);
            $priorityCode = match(true) {
                $priorityRoll <= 25 => 'low',
                $priorityRoll <= 65 => 'medium',
                $priorityRoll <= 85 => 'high',
                default             => 'urgent',
            };

            // Assign to agent — 85% assigned, 15% unassigned
            $assignedTo = fake()->numberBetween(1, 100) <= 85
                ? $agentIds[array_rand($agentIds)]
                : null;

            // Status logic
            $statusRoll = fake()->numberBetween(1, 100);
            $statusCode = match(true) {
                $statusRoll <= 20 => 'new',
                $statusRoll <= 45 => 'open',
                $statusRoll <= 60 => 'pending',
                $statusRoll <= 80 => 'resolved',
                default           => 'closed',
            };
            $statusId = $statuses[$statusCode]->id ?? null;

            // first_response_at — set for open/pending/resolved/closed
            $firstResponseAt = null;
            if (in_array($statusCode, ['open', 'pending', 'resolved', 'closed'])) {
                // 90% responded within SLA (4h), 10% breached
                $responseMinutes = fake()->numberBetween(1, 100) <= 90
                    ? fake()->numberBetween(10, 240)   // within 4h
                    : fake()->numberBetween(241, 600);  // breached
                $firstResponseAt = $createdAt->copy()->addMinutes($responseMinutes);
            }

            // resolved_at for resolved/closed
            $resolvedAt = null;
            if (in_array($statusCode, ['resolved', 'closed'])) {
                // 80% within SLA (24h), 20% breached
                $resolutionMinutes = fake()->numberBetween(1, 100) <= 80
                    ? fake()->numberBetween(60, 1440)    // within 24h
                    : fake()->numberBetween(1441, 4320); // breached
                $resolvedAt = $createdAt->copy()->addMinutes($resolutionMinutes);
            }

            // due_at — 70% of tickets have a due date
            $dueAt = null;
            if (fake()->numberBetween(1, 100) <= 70) {
                if (in_array($statusCode, ['resolved', 'closed'])) {
                    // Due date around resolution time ±12h
                    $dueAt = $createdAt->copy()->addHours(fake()->numberBetween(6, 36));
                } elseif (in_array($statusCode, ['new', 'open', 'pending'])) {
                    // Mix of overdue (30%) and future due (70%)
                    if (fake()->numberBetween(1, 100) <= 30) {
                        $dueAt = now()->subHours(fake()->numberBetween(1, 72)); // overdue
                    } else {
                        $dueAt = now()->addHours(fake()->numberBetween(2, 96)); // future
                    }
                }
            }

            $branchId   = !empty($branchIds) && fake()->numberBetween(1, 100) <= 80
                ? $branchIds[array_rand($branchIds)]
                : null;
            $customerId = $customerIds[array_rand($customerIds)];
            $createdBy  = $assignedTo ?? $agentIds[array_rand($agentIds)];

            $tickets[] = [
                'uuid'               => (string) Str::uuid(),
                'ticket_no'          => $ticketNo,
                'branch_id'          => $branchId,
                'category_id'        => $catId,
                'priority_id'        => $priorities[$priorityCode]->id ?? null,
                'status_id'          => $statusId,
                'customer_id'        => $customerId,
                'assigned_to'        => $assignedTo,
                'subject'            => $subject . ' (#' . $counter . ')',
                'description'        => fake()->paragraph(3),
                'source'             => $sources[array_rand($sources)],
                'first_response_at'  => $firstResponseAt,
                'resolved_at'        => $resolvedAt,
                'due_at'             => $dueAt,
                'status'             => true,
                'created_by'         => $createdBy,
                'created_at'         => $createdAt,
                'updated_at'         => $createdAt,
            ];
        }

        // Insert in chunks
        foreach (array_chunk($tickets, 50) as $chunk) {
            DB::table('tickets')->insert($chunk);
        }

        $this->command->info('Sandbox data seeded:');
        $this->command->info('  → ' . count($categoryIds) . ' categories');
        $this->command->info('  → SLA settings: 4h response / 24h resolution');
        $this->command->info('  → 200 tickets created');
    }
}
