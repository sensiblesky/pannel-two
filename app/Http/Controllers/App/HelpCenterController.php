<?php

namespace App\Http\Controllers\App;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class HelpCenterController extends Controller
{
    /**
     * Public help center page with ticket submission form.
     */
    public function index()
    {
        $settings = DB::table('ticket_settings')->whereNull('branch_id')->pluck('value', 'key');

        if (empty($settings['help_page_enabled']) || $settings['help_page_enabled'] !== '1') {
            abort(404);
        }

        $categories = DB::table('ticket_categories')
            ->whereNull('deleted_at')
            ->where('status', 1)
            ->orderBy('name')
            ->get(['id', 'name', 'description']);

        $priorities = DB::table('ticket_priorities')
            ->whereNull('deleted_at')
            ->where('status', 1)
            ->orderBy('sort_order')
            ->get(['id', 'name', 'color']);

        return view('public.help-center', compact('settings', 'categories', 'priorities'));
    }

    /**
     * Store a ticket submitted from the public help center.
     */
    public function store(Request $request)
    {
        $settings = DB::table('ticket_settings')->whereNull('branch_id')->pluck('value', 'key');

        if (empty($settings['help_page_enabled']) || $settings['help_page_enabled'] !== '1') {
            abort(404);
        }

        $rules = [
            'name' => ['required', 'string', 'max:150'],
            'email' => ['required', 'email', 'max:150'],
            'phone' => ['nullable', 'string', 'max:50'],
            'subject' => ['required', 'string', 'max:255'],
            'category_id' => ['nullable', 'exists:ticket_categories,id'],
            'priority_id' => ['nullable', 'exists:ticket_priorities,id'],
            'description' => ['required', 'string', 'max:5000'],
            'attachments' => ['nullable', 'array', 'max:5'],
            'attachments.*' => ['file', 'max:10240'],
        ];

        $validated = $request->validate($rules);

        // Find or create customer from submitted email
        $customer = DB::table('users')
            ->where('email', $validated['email'])
            ->where('role', 'customer')
            ->whereNull('deleted_at')
            ->first();

        if (!$customer) {
            $customerId = DB::table('users')->insertGetId([
                'uid' => Str::uuid(),
                'name' => $validated['name'],
                'email' => $validated['email'],
                'phone' => $validated['phone'] ?? null,
                'role' => 'customer',
                'status' => true,
                'password' => bcrypt(Str::random(32)),
                'created_at' => now(),
                'updated_at' => now(),
            ]);

            DB::table('users_customers')->insert([
                'user_id' => $customerId,
                'source' => 'help_center',
                'created_at' => now(),
                'updated_at' => now(),
            ]);
        } else {
            $customerId = $customer->id;
        }

        // Generate ticket number
        $prefix = $settings['ticket_number_prefix'] ?? 'TKT-';
        $lastTicket = DB::table('tickets')->orderByDesc('id')->value('id') ?? 0;
        $ticketNo = $prefix . str_pad($lastTicket + 1, 6, '0', STR_PAD_LEFT);

        // Default status/priority
        $defaultStatusCode = $settings['default_status'] ?? 'new';
        $statusId = DB::table('ticket_statuses')->where('code', $defaultStatusCode)->value('id');

        $priorityId = $validated['priority_id'] ?? null;
        if (!$priorityId) {
            $defaultPriorityCode = $settings['default_priority'] ?? 'medium';
            $priorityId = DB::table('ticket_priorities')->where('code', $defaultPriorityCode)->value('id');
        }

        $ticketId = DB::table('tickets')->insertGetId([
            'uuid' => Str::uuid(),
            'ticket_no' => $ticketNo,
            'subject' => $validated['subject'],
            'description' => $validated['description'],
            'customer_id' => $customerId,
            'category_id' => $validated['category_id'] ?? null,
            'priority_id' => $priorityId,
            'status_id' => $statusId,
            'source' => 'web',
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        // Attachments
        if ($request->hasFile('attachments')) {
            foreach ($request->file('attachments') as $file) {
                $path = $file->store('ticket-attachments/' . $ticketId, 'public');
                DB::table('ticket_attachments')->insert([
                    'uuid' => Str::uuid(),
                    'ticket_id' => $ticketId,
                    'file_name' => $file->getClientOriginalName(),
                    'file_path' => $path,
                    'file_size' => $file->getSize(),
                    'mime_type' => $file->getMimeType(),
                    'uploaded_by_type' => 'customer',
                    'uploaded_by_id' => $customerId,
                    'created_at' => now(),
                    'updated_at' => now(),
                ]);
            }
        }

        // Status log
        DB::table('ticket_status_logs')->insert([
            'uuid' => Str::uuid(),
            'ticket_id' => $ticketId,
            'old_status_id' => null,
            'new_status_id' => $statusId,
            'started_at' => now(),
            'created_at' => now(),
        ]);

        // Event
        DB::table('ticket_events')->insert([
            'uuid' => Str::uuid(),
            'ticket_id' => $ticketId,
            'event_type' => 'created',
            'actor_type' => 'customer',
            'actor_id' => $customerId,
            'new_value' => $ticketNo,
            'created_at' => now(),
        ]);

        $uuid = DB::table('tickets')->where('id', $ticketId)->value('uuid');

        return redirect()->route('help-center.submitted', $uuid);
    }

    /**
     * Ticket submitted confirmation page.
     */
    public function submitted(string $uuid)
    {
        $ticket = DB::table('tickets')
            ->where('uuid', $uuid)
            ->whereNull('deleted_at')
            ->select('ticket_no', 'subject', 'uuid')
            ->firstOrFail();

        $settings = DB::table('ticket_settings')->whereNull('branch_id')->pluck('value', 'key');

        return view('public.help-center-submitted', compact('ticket', 'settings'));
    }

    /**
     * Public ticket status lookup.
     */
    public function trackTicket(Request $request)
    {
        $settings = DB::table('ticket_settings')->whereNull('branch_id')->pluck('value', 'key');

        if (empty($settings['help_page_enabled']) || $settings['help_page_enabled'] !== '1') {
            abort(404);
        }

        $ticket = null;
        $searched = false;
        $lastRemark = null;

        $ticketNo  = trim($request->input('ticket_no', ''));
        $emailOrPhone = trim($request->input('email_or_phone', ''));

        if ($ticketNo !== '' && $emailOrPhone !== '') {
            $searched = true;

            // Normalise phone: accept +255XXXXXXXXX, 0XXXXXXXXX, 255XXXXXXXXX
            $normalised = preg_replace('/^\+/', '', $emailOrPhone);
            if (str_starts_with($normalised, '0')) {
                $normalised = '255' . substr($normalised, 1);
            } elseif (!str_starts_with($normalised, '255')) {
                $normalised = '255' . $normalised;
            }
            $normalisedWithPlus = '+' . $normalised;

            $isPhone = preg_match('/^\+?[0-9]{9,15}$/', preg_replace('/\s+/', '', $emailOrPhone))
                       && !str_contains($emailOrPhone, '@');

            $ticket = DB::table('tickets')
                ->whereNull('tickets.deleted_at')
                ->where('tickets.ticket_no', $ticketNo)
                ->leftJoin('users as customers', 'tickets.customer_id', '=', 'customers.id')
                ->where(function ($q) use ($emailOrPhone, $normalised, $normalisedWithPlus, $isPhone) {
                    if ($isPhone) {
                        // Match contact_phone on the ticket itself OR the customer's phone
                        $q->where('tickets.contact_phone', $normalisedWithPlus)
                          ->orWhere('tickets.contact_phone', $normalised)
                          ->orWhere('tickets.contact_phone', $emailOrPhone)
                          ->orWhere('customers.phone', $normalisedWithPlus)
                          ->orWhere('customers.phone', $normalised)
                          ->orWhere('customers.phone', $emailOrPhone);
                    } else {
                        // Email match: ticket contact_email OR customer email
                        $q->where('tickets.contact_email', $emailOrPhone)
                          ->orWhere('customers.email', $emailOrPhone);
                    }
                })
                ->leftJoin('ticket_statuses', 'tickets.status_id', '=', 'ticket_statuses.id')
                ->leftJoin('ticket_priorities', 'tickets.priority_id', '=', 'ticket_priorities.id')
                ->leftJoin('ticket_categories', 'tickets.category_id', '=', 'ticket_categories.id')
                ->leftJoin('users as agents', 'tickets.assigned_to', '=', 'agents.id')
                ->select(
                    'tickets.id', 'tickets.ticket_no', 'tickets.subject', 'tickets.description',
                    'tickets.created_at', 'tickets.due_at', 'tickets.resolved_at', 'tickets.closed_at',
                    'ticket_statuses.name as status_name', 'ticket_statuses.color as status_color',
                    'ticket_statuses.is_closed',
                    'ticket_priorities.name as priority_name', 'ticket_priorities.color as priority_color',
                    'ticket_categories.name as category_name',
                    'agents.name as agent_name',
                )
                ->first();

            if ($ticket) {
                // Get the last agent reply as final remark
                $lastRemark = DB::table('ticket_messages')
                    ->where('ticket_id', $ticket->id)
                    ->where('sender_type', 'user')
                    ->where('message_type', 'reply')
                    ->where('is_internal', false)
                    ->whereNull('deleted_at')
                    ->orderByDesc('created_at')
                    ->select('message', 'created_at')
                    ->first();
            }
        }

        return view('public.help-center-track', compact('settings', 'ticket', 'searched', 'lastRemark'));
    }
}
