<?php

namespace App\Http\Controllers\App;

use App\Http\Controllers\Controller;
use App\Jobs\SendMailJob;
use App\Jobs\SendSmsJob;
use App\Realtime\RealtimeManager;
use App\Services\ActivityLogService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Str;

class TicketController extends Controller
{
    /**
     * Poll for new tickets since a given timestamp (AJAX).
     */
    public function poll(Request $request)
    {
        $since = $request->input('since');
        if (!$since) {
            return response()->json(['count' => 0, 'tickets' => [], 'timestamp' => now()->toIso8601String()]);
        }

        $newTickets = DB::table('tickets')
            ->whereNull('tickets.deleted_at')
            ->where('tickets.status', 1)
            ->where('tickets.created_at', '>', $since)
            ->leftJoin('users as customers', 'tickets.customer_id', '=', 'customers.id')
            ->orderByDesc('tickets.created_at')
            ->limit(10)
            ->get(['tickets.id', 'tickets.ticket_no', 'tickets.subject', 'tickets.uuid', 'customers.name as customer_name']);

        return response()->json([
            'count' => $newTickets->count(),
            'tickets' => $newTickets,
            'timestamp' => now()->toIso8601String(),
        ]);
    }

    public function index(Request $request)
    {
        $query = DB::table('tickets')
            ->whereNull('tickets.deleted_at')
            ->where('tickets.status', 1)
            ->leftJoin('ticket_statuses', 'tickets.status_id', '=', 'ticket_statuses.id')
            ->leftJoin('ticket_priorities', 'tickets.priority_id', '=', 'ticket_priorities.id')
            ->leftJoin('ticket_categories', 'tickets.category_id', '=', 'ticket_categories.id')
            ->leftJoin('departments', 'tickets.department_id', '=', 'departments.id')
            ->leftJoin('branches', 'tickets.branch_id', '=', 'branches.id')
            ->leftJoin('users as customers', 'tickets.customer_id', '=', 'customers.id')
            ->leftJoin('users_customers', 'customers.id', '=', 'users_customers.user_id')
            ->leftJoin('users as agents', 'tickets.assigned_to', '=', 'agents.id')
            ->leftJoin('users as creators', 'tickets.created_by', '=', 'creators.id')
            ->select(
                'tickets.*',
                'ticket_statuses.name as status_name', 'ticket_statuses.color as status_color', 'ticket_statuses.code as status_code', 'ticket_statuses.is_closed',
                'ticket_priorities.name as priority_name', 'ticket_priorities.color as priority_color',
                'ticket_categories.name as category_name',
                'departments.name as department_name',
                'branches.name as branch_name',
                'customers.name as customer_name',
                'customers.email as customer_email', 'customers.phone as customer_phone', 'users_customers.company as customer_company',
                'agents.name as agent_name',
                'creators.name as creator_name',
            );

        $query = $this->applyAgentScope($query);

        // Search
        if ($search = $request->input('search')) {
            $query->where(function ($q) use ($search) {
                $q->where('tickets.ticket_no', 'like', "%{$search}%")
                  ->orWhere('tickets.subject', 'like', "%{$search}%")
                  ->orWhere('customers.name', 'like', "%{$search}%")
                  ->orWhere('customers.email', 'like', "%{$search}%");
            });
        }

        // Filters
        if ($statusId = $request->input('status_id')) {
            $query->where('tickets.status_id', $statusId);
        }
        if ($priorityId = $request->input('priority_id')) {
            $query->where('tickets.priority_id', $priorityId);
        }
        if ($categoryId = $request->input('category_id')) {
            $query->where('tickets.category_id', $categoryId);
        }
        if ($departmentId = $request->input('department_id')) {
            $query->where('tickets.department_id', $departmentId);
        }
        if ($branchId = $request->input('branch_id')) {
            $query->where('tickets.branch_id', $branchId);
        }
        if ($assignedTo = $request->input('assigned_to')) {
            $query->where('tickets.assigned_to', $assignedTo);
        }
        if ($source = $request->input('source')) {
            $query->where('tickets.source', $source);
        }

        // Quick view filters
        $view = $request->input('view', 'all');
        $openStatusIds = DB::table('ticket_statuses')->whereNull('deleted_at')->where('status', 1)->where('is_closed', false)->pluck('id');

        switch ($view) {
            case 'open':
                $openId = DB::table('ticket_statuses')->where('code', 'open')->value('id');
                if ($openId) $query->where('tickets.status_id', $openId);
                break;
            case 'pending':
                $pendingId = DB::table('ticket_statuses')->where('code', 'pending')->value('id');
                if ($pendingId) $query->where('tickets.status_id', $pendingId);
                break;
            case 'resolved':
                $resolvedId = DB::table('ticket_statuses')->where('code', 'resolved')->value('id');
                if ($resolvedId) $query->where('tickets.status_id', $resolvedId);
                break;
            case 'closed':
                $closedId = DB::table('ticket_statuses')->where('code', 'closed')->value('id');
                if ($closedId) $query->where('tickets.status_id', $closedId);
                break;
            case 'unassigned':
                $query->whereNull('tickets.assigned_to')->whereIn('tickets.status_id', $openStatusIds);
                break;
            case 'overdue':
                $query->whereIn('tickets.status_id', $openStatusIds)
                      ->whereNotNull('tickets.due_at')->where('tickets.due_at', '<', now());
                break;
            case 'mine':
                $query->where('tickets.assigned_to', auth()->id());
                break;
        }

        if ($request->input('overdue') === '1') {
            $query->whereIn('tickets.status_id', $openStatusIds)
                  ->whereNotNull('tickets.due_at')->where('tickets.due_at', '<', now());
        }
        if ($request->input('unassigned') === '1') {
            $query->whereNull('tickets.assigned_to');
        }

        // Date range
        if ($from = $request->input('date_from')) {
            $query->whereDate('tickets.created_at', '>=', $from);
        }
        if ($to = $request->input('date_to')) {
            $query->whereDate('tickets.created_at', '<=', $to);
        }

        $tickets = $query->orderByDesc('tickets.created_at')->paginate(20)->withQueryString();

        // Filter options
        $statuses = DB::table('ticket_statuses')->whereNull('deleted_at')->where('status', 1)->orderBy('sort_order')->get();
        $priorities = DB::table('ticket_priorities')->whereNull('deleted_at')->where('status', 1)->orderBy('sort_order')->get();
        $categories = DB::table('ticket_categories')->whereNull('deleted_at')->where('status', 1)->orderBy('name')->get();
        $departments = DB::table('departments')->whereNull('deleted_at')->where('status', true)->orderBy('name')->get();
        $branches = DB::table('branches')->whereNull('deleted_at')->where('status', true)->orderBy('name')->get();
        // Only look up the currently-filtered agent for pre-populate (no full list)
        $selectedAgentId   = $request->input('assigned_to');
        $selectedAgentName = $selectedAgentId
            ? DB::table('users')->where('id', $selectedAgentId)->value('name')
            : null;

        // View counts
        $viewCounts = $this->buildViewCounts($openStatusIds);

        return view('app.tickets.index', compact(
            'tickets', 'statuses', 'priorities', 'categories', 'departments', 'branches',
            'selectedAgentId', 'selectedAgentName', 'view', 'viewCounts'
        ));
    }

    public function create()
    {
        $statuses = DB::table('ticket_statuses')->whereNull('deleted_at')->where('status', 1)->orderBy('sort_order')->get();
        $priorities = DB::table('ticket_priorities')->whereNull('deleted_at')->where('status', 1)->orderBy('sort_order')->get();
        $categories = DB::table('ticket_categories')->whereNull('deleted_at')->where('status', 1)->orderBy('name')->get();
        $departments = DB::table('departments')->whereNull('deleted_at')->where('status', true)->orderBy('name')->get();
        $branches = DB::table('branches')->whereNull('deleted_at')->where('status', true)->orderBy('name')->get();
        $tags = DB::table('tags')->whereNull('deleted_at')->where('status', 1)->orderBy('name')->get();

        $defaultStatus = DB::table('ticket_settings')->where('key', 'default_status')->whereNull('branch_id')->value('value');
        $defaultPriority = DB::table('ticket_settings')->where('key', 'default_priority')->whereNull('branch_id')->value('value');

        return view('app.tickets.create', compact(
            'statuses', 'priorities', 'categories', 'departments', 'branches', 'tags',
            'defaultStatus', 'defaultPriority'
        ));
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'subject'       => ['required', 'string', 'max:255'],
            'description'   => ['nullable', 'string'],
            'customer_id'   => ['nullable', 'exists:users,id'],
            'branch_id'     => ['nullable', 'exists:branches,id'],
            'department_id' => ['nullable', 'exists:departments,id'],
            'category_id'   => ['nullable', 'exists:ticket_categories,id'],
            'priority_id'   => ['nullable', 'exists:ticket_priorities,id'],
            'status_id'     => ['nullable', 'exists:ticket_statuses,id'],
            'assigned_to'   => ['nullable', 'exists:users,id'],
            'source'        => ['nullable', 'in:web,widget,email,api,manual,phone,chat'],
            'due_at'        => ['nullable', 'date', 'after:1970-01-01'],
            'contact_phone' => ['nullable', 'string', 'regex:/^\+255\d{9}$/'],
            'contact_email' => ['nullable', 'email', 'max:255'],
            'tags'          => ['nullable', 'array'],
            'tags.*'        => ['exists:tags,id'],
            'attachments'   => ['nullable', 'array'],
            'attachments.*' => ['file', 'max:10240'],
        ], [
            'contact_phone.regex' => 'Phone must be in format +255 followed by exactly 9 digits.',
        ]);

        // Generate ticket number
        $prefix = DB::table('ticket_settings')->where('key', 'ticket_number_prefix')->whereNull('branch_id')->value('value') ?? 'TKT-';
        $lastTicket = DB::table('tickets')->orderByDesc('id')->value('id') ?? 0;
        $ticketNo = $prefix . str_pad($lastTicket + 1, 6, '0', STR_PAD_LEFT);

        // Default status/priority from settings
        if (empty($validated['status_id'])) {
            $defaultCode = DB::table('ticket_settings')->where('key', 'default_status')->whereNull('branch_id')->value('value') ?? 'new';
            $validated['status_id'] = DB::table('ticket_statuses')->where('code', $defaultCode)->value('id');
        }
        if (empty($validated['priority_id'])) {
            $defaultCode = DB::table('ticket_settings')->where('key', 'default_priority')->whereNull('branch_id')->value('value') ?? 'medium';
            $validated['priority_id'] = DB::table('ticket_priorities')->where('code', $defaultCode)->value('id');
        }

        $ticketId = DB::table('tickets')->insertGetId([
            'uuid' => Str::uuid(),
            'ticket_no' => $ticketNo,
            'subject' => $validated['subject'],
            'description' => $validated['description'] ?? null,
            'customer_id' => $validated['customer_id'] ?? null,
            'branch_id' => $validated['branch_id'] ?? null,
            'department_id' => $validated['department_id'] ?? null,
            'category_id' => $validated['category_id'] ?? null,
            'priority_id' => $validated['priority_id'] ?? null,
            'status_id' => $validated['status_id'] ?? null,
            'assigned_to' => $validated['assigned_to'] ?? null,
            'contact_phone' => $validated['contact_phone'] ?? null,
            'contact_email' => $validated['contact_email'] ?? null,
            'source' => $validated['source'] ?? 'manual',
            'due_at' => !empty($validated['due_at']) ? \Carbon\Carbon::parse($validated['due_at'])->format('Y-m-d H:i:s') : null,
            'created_by' => auth()->id(),
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        // Tags
        if (!empty($validated['tags'])) {
            foreach ($validated['tags'] as $tagId) {
                DB::table('ticket_tag')->insert([
                    'ticket_id' => $ticketId,
                    'tag_id' => $tagId,
                    'created_by' => auth()->id(),
                    'created_at' => now(),
                ]);
            }
        }

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
                    'uploaded_by_type' => 'user',
                    'uploaded_by_id' => auth()->id(),
                    'created_by' => auth()->id(),
                    'created_at' => now(),
                    'updated_at' => now(),
                ]);
            }
        }

        // Assignment record
        if (!empty($validated['assigned_to'])) {
            DB::table('ticket_assignments')->insert([
                'uuid' => Str::uuid(),
                'ticket_id' => $ticketId,
                'assigned_to' => $validated['assigned_to'],
                'assigned_by' => auth()->id(),
                'assigned_at' => now(),
            ]);
        }

        // Status log
        DB::table('ticket_status_logs')->insert([
            'uuid' => Str::uuid(),
            'ticket_id' => $ticketId,
            'old_status_id' => null,
            'new_status_id' => $validated['status_id'],
            'changed_by' => auth()->id(),
            'started_at' => now(),
            'created_at' => now(),
        ]);

        // Event
        DB::table('ticket_events')->insert([
            'uuid' => Str::uuid(),
            'ticket_id' => $ticketId,
            'event_type' => 'created',
            'actor_type' => 'user',
            'actor_id' => auth()->id(),
            'new_value' => $ticketNo,
            'created_at' => now(),
        ]);

        $uuid = DB::table('tickets')->where('id', $ticketId)->value('uuid');

        $statusName   = null;
        $priorityName = null;

        // Send email notification to customer
        if (!empty($validated['customer_id'])) {
            $customer = DB::table('users')->where('id', $validated['customer_id'])->select('name', 'email')->first();
            if ($customer && $customer->email) {
                $statusName = $validated['status_id']
                    ? DB::table('ticket_statuses')->where('id', $validated['status_id'])->value('name')
                    : null;
                $priorityName = $validated['priority_id']
                    ? DB::table('ticket_priorities')->where('id', $validated['priority_id'])->value('name')
                    : null;

                $trackUrl = route('help-center.track') . '?' . http_build_query([
                    'ticket_no' => $ticketNo,
                    'email' => $customer->email,
                ]);

                SendMailJob::dispatch($customer->email, new \App\Mail\TicketCreated(
                    customerName: $customer->name ?? 'Customer',
                    ticketNo: $ticketNo,
                    ticketSubject: $validated['subject'],
                    description: $validated['description'] ?? null,
                    statusName: $statusName,
                    priorityName: $priorityName,
                    createdAt: now()->format('M d, Y h:i A'),
                    trackUrl: $trackUrl,
                ));
            }
        }

        // Send email to contact_email (if different from customer email)
        $contactEmail = $validated['contact_email'] ?? null;
        if ($contactEmail) {
            $statusName  = $statusName  ?? ($validated['status_id']  ? DB::table('ticket_statuses')->where('id', $validated['status_id'])->value('name')   : null);
            $priorityName = $priorityName ?? ($validated['priority_id'] ? DB::table('ticket_priorities')->where('id', $validated['priority_id'])->value('name') : null);
            $trackUrl = route('help-center.track') . '?' . http_build_query(['ticket_no' => $ticketNo, 'email' => $contactEmail]);
            SendMailJob::dispatch($contactEmail, new \App\Mail\TicketCreated(
                customerName: 'Customer',
                ticketNo: $ticketNo,
                ticketSubject: $validated['subject'],
                description: $validated['description'] ?? null,
                statusName: $statusName,
                priorityName: $priorityName,
                createdAt: now()->format('M d, Y h:i A'),
                trackUrl: $trackUrl,
            ));
        }

        // Send SMS to contact_phone
        $contactPhone = $validated['contact_phone'] ?? null;
        if ($contactPhone) {
            $smsMessage = "Dear Customer, your ticket #{$ticketNo} has been created. Subject: {$validated['subject']}. We will get back to you shortly.";
            SendSmsJob::dispatch($contactPhone, $smsMessage);
        }

        return redirect()->route('tickets/show', $uuid)->with('success', 'Ticket created successfully.');
    }

    public function show(string $uuid)
    {
        $ticketQuery = DB::table('tickets')
            ->whereNull('tickets.deleted_at')
            ->where('tickets.uuid', $uuid)
            ->leftJoin('ticket_statuses', 'tickets.status_id', '=', 'ticket_statuses.id')
            ->leftJoin('ticket_priorities', 'tickets.priority_id', '=', 'ticket_priorities.id')
            ->leftJoin('ticket_categories', 'tickets.category_id', '=', 'ticket_categories.id')
            ->leftJoin('departments', 'tickets.department_id', '=', 'departments.id')
            ->leftJoin('branches', 'tickets.branch_id', '=', 'branches.id')
            ->leftJoin('users as customers', 'tickets.customer_id', '=', 'customers.id')
            ->leftJoin('users_customers', 'customers.id', '=', 'users_customers.user_id')
            ->leftJoin('users as agents', 'tickets.assigned_to', '=', 'agents.id')
            ->leftJoin('users as creators', 'tickets.created_by', '=', 'creators.id')
            ->select(
                'tickets.*',
                'ticket_statuses.name as status_name', 'ticket_statuses.color as status_color', 'ticket_statuses.code as status_code', 'ticket_statuses.is_closed',
                'ticket_priorities.name as priority_name', 'ticket_priorities.color as priority_color',
                'ticket_categories.name as category_name',
                'departments.name as department_name',
                'branches.name as branch_name',
                'customers.name as customer_name',
                'customers.email as customer_email', 'customers.phone as customer_phone', 'users_customers.company as customer_company',
                'agents.name as agent_name',
                'creators.name as creator_name',
            );
        $ticket = $ticketQuery->firstOrFail();

        // Messages
        $messages = DB::table('ticket_messages')
            ->where('ticket_messages.ticket_id', $ticket->id)
            ->whereNull('ticket_messages.deleted_at')
            ->leftJoin('users', function ($join) {
                $join->on('ticket_messages.sender_id', '=', 'users.id')
                     ->where('ticket_messages.sender_type', '=', 'user');
            })
            ->leftJoin('users as customer_senders', function ($join) {
                $join->on('ticket_messages.sender_id', '=', 'customer_senders.id')
                     ->where('ticket_messages.sender_type', '=', 'customer');
            })
            ->select(
                'ticket_messages.*',
                'users.name as user_name',
                'customer_senders.name as customer_sender_name'
            )
            ->orderBy('ticket_messages.created_at')
            ->get();

        // Attachments
        $attachments = DB::table('ticket_attachments')
            ->where('ticket_attachments.ticket_id', $ticket->id)->whereNull('ticket_attachments.deleted_at')
            ->orderBy('ticket_attachments.created_at')->get();

        // Events/timeline
        $events = DB::table('ticket_events')
            ->where('ticket_id', $ticket->id)
            ->leftJoin('users', function ($join) {
                $join->on('ticket_events.actor_id', '=', 'users.id')
                     ->where('ticket_events.actor_type', '=', 'user');
            })
            ->select('ticket_events.*', 'users.name as actor_name')
            ->orderByDesc('ticket_events.created_at')
            ->get();

        // Status logs
        $statusLogs = DB::table('ticket_status_logs')
            ->where('ticket_status_logs.ticket_id', $ticket->id)
            ->leftJoin('ticket_statuses as old_s', 'ticket_status_logs.old_status_id', '=', 'old_s.id')
            ->leftJoin('ticket_statuses as new_s', 'ticket_status_logs.new_status_id', '=', 'new_s.id')
            ->leftJoin('users', 'ticket_status_logs.changed_by', '=', 'users.id')
            ->select(
                'ticket_status_logs.*',
                'old_s.name as old_status_name', 'old_s.color as old_status_color',
                'new_s.name as new_status_name', 'new_s.color as new_status_color',
                'users.name as changed_by_name'
            )
            ->orderByDesc('ticket_status_logs.created_at')
            ->get();

        // Assignment history
        $assignmentHistory = DB::table('ticket_assignments')
            ->where('ticket_assignments.ticket_id', $ticket->id)
            ->leftJoin('users as assigned', 'ticket_assignments.assigned_to', '=', 'assigned.id')
            ->leftJoin('users as assigner', 'ticket_assignments.assigned_by', '=', 'assigner.id')
            ->select('ticket_assignments.*', 'assigned.name as assigned_to_name', 'assigner.name as assigned_by_name')
            ->orderByDesc('ticket_assignments.assigned_at')
            ->get();

        // Tags
        $ticketTags = DB::table('ticket_tag')
            ->where('ticket_id', $ticket->id)
            ->join('tags', 'ticket_tag.tag_id', '=', 'tags.id')
            ->select('tags.*')
            ->get();

        // Watchers
        $watchers = DB::table('ticket_watchers')
            ->where('ticket_id', $ticket->id)
            ->join('users', 'ticket_watchers.user_id', '=', 'users.id')
            ->select('ticket_watchers.*', 'users.name as user_name')
            ->get();

        // Options for quick actions
        $statuses = DB::table('ticket_statuses')->whereNull('deleted_at')->where('status', 1)->orderBy('sort_order')->get();
        $priorities = DB::table('ticket_priorities')->whereNull('deleted_at')->where('status', 1)->orderBy('sort_order')->get();
        $agents = DB::table('users')->whereNull('deleted_at')->where('status', true)->orderBy('name')->get(['id', 'name']);
        $allTags = DB::table('tags')->whereNull('deleted_at')->where('status', 1)->orderBy('name')->get();

        return view('app.tickets.show', compact(
            'ticket', 'messages', 'attachments', 'events', 'statusLogs', 'assignmentHistory',
            'ticketTags', 'watchers', 'statuses', 'priorities', 'agents', 'allTags'
        ));
    }

    public function reply(Request $request, string $uuid)
    {
        $ticket = DB::table('tickets')->where('uuid', $uuid)->firstOrFail();

        $status = DB::table('ticket_statuses')->where('id', $ticket->status_id)->first();
        if ($status && $status->is_closed) {
            return redirect()->route('tickets/show', $uuid)->with('error', 'This ticket is closed. Reopen it before sending a reply.');
        }

        $validated = $request->validate([
            'message' => ['required', 'string'],
            'message_type' => ['required', 'in:reply,note'],
            'attachments' => ['nullable', 'array'],
            'attachments.*' => ['file', 'max:10240'],
        ]);

        $isInternal = $validated['message_type'] === 'note';
        $isFirstResponse = !$isInternal && DB::table('ticket_messages')
            ->where('ticket_id', $ticket->id)
            ->where('sender_type', 'user')
            ->where('message_type', 'reply')
            ->doesntExist();

        // Check if this agent has sent any message on this ticket before
        $agentHasMessaged = DB::table('ticket_messages')
            ->where('ticket_id', $ticket->id)
            ->where('sender_type', 'user')
            ->where('sender_id', auth()->id())
            ->whereNull('deleted_at')
            ->exists();

        // Insert "joined" system message if agent is new to this conversation
        if (!$agentHasMessaged && !$isInternal) {
            DB::table('ticket_messages')->insert([
                'uuid' => Str::uuid(),
                'ticket_id' => $ticket->id,
                'sender_type' => 'system',
                'sender_id' => null,
                'message_type' => 'system',
                'message' => auth()->user()->name . ' joined the conversation',
                'is_internal' => false,
                'is_first_response' => false,
                'created_by' => auth()->id(),
                'created_at' => now(),
                'updated_at' => now(),
            ]);
        }

        $messageId = DB::table('ticket_messages')->insertGetId([
            'uuid' => Str::uuid(),
            'ticket_id' => $ticket->id,
            'sender_type' => 'user',
            'sender_id' => auth()->id(),
            'message_type' => $validated['message_type'],
            'message' => $validated['message'],
            'is_internal' => $isInternal,
            'is_first_response' => $isFirstResponse,
            'created_by' => auth()->id(),
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        if ($isFirstResponse) {
            DB::table('tickets')->where('id', $ticket->id)->update([
                'first_response_at' => now(),
                'last_agent_reply_at' => now(),
                'updated_at' => now(),
            ]);
        } elseif (!$isInternal) {
            DB::table('tickets')->where('id', $ticket->id)->update([
                'last_agent_reply_at' => now(),
                'updated_at' => now(),
            ]);
        }

        // Attachments
        if ($request->hasFile('attachments')) {
            foreach ($request->file('attachments') as $file) {
                $path = $file->store('ticket-attachments/' . $ticket->id, 'public');
                DB::table('ticket_attachments')->insert([
                    'uuid' => Str::uuid(),
                    'ticket_id' => $ticket->id,
                    'ticket_message_id' => $messageId,
                    'file_name' => $file->getClientOriginalName(),
                    'file_path' => $path,
                    'file_size' => $file->getSize(),
                    'mime_type' => $file->getMimeType(),
                    'uploaded_by_type' => 'user',
                    'uploaded_by_id' => auth()->id(),
                    'created_by' => auth()->id(),
                    'created_at' => now(),
                    'updated_at' => now(),
                ]);
            }
        }

        // Event
        DB::table('ticket_events')->insert([
            'uuid' => Str::uuid(),
            'ticket_id' => $ticket->id,
            'event_type' => $isInternal ? 'note_added' : 'reply_sent',
            'actor_type' => 'user',
            'actor_id' => auth()->id(),
            'created_at' => now(),
        ]);

        return redirect()->route('tickets/show', $uuid)->with('success', $isInternal ? 'Internal note added.' : 'Reply sent.');
    }

    /**
     * AJAX: Get new messages for a ticket since a timestamp.
     */
    public function pollMessages(Request $request, string $uuid)
    {
        $ticket = DB::table('tickets')->where('uuid', $uuid)->firstOrFail();
        $since = $request->input('since');

        // Keep current agent's online status alive on every poll
        Cache::put('user_online_' . auth()->id(), true, 120);

        $query = DB::table('ticket_messages')
            ->where('ticket_messages.ticket_id', $ticket->id)
            ->whereNull('ticket_messages.deleted_at')
            ->leftJoin('users', function ($join) {
                $join->on('ticket_messages.sender_id', '=', 'users.id')
                     ->where('ticket_messages.sender_type', '=', 'user');
            })
            ->leftJoin('users as customer_senders', function ($join) {
                $join->on('ticket_messages.sender_id', '=', 'customer_senders.id')
                     ->where('ticket_messages.sender_type', '=', 'customer');
            })
            ->select(
                'ticket_messages.id', 'ticket_messages.uuid', 'ticket_messages.message',
                'ticket_messages.sender_type', 'ticket_messages.sender_id',
                'ticket_messages.message_type', 'ticket_messages.is_internal',
                'ticket_messages.created_at',
                'users.name as user_name',
                'customer_senders.name as customer_sender_name'
            )
            ->orderBy('ticket_messages.created_at');

        if ($since) {
            $query->where('ticket_messages.created_at', '>', $since);
        }

        $messages = $query->get();

        // Load attachments for these messages
        $messageIds = $messages->pluck('id');
        $allAttachments = $messageIds->isNotEmpty()
            ? DB::table('ticket_attachments')
                ->whereIn('ticket_message_id', $messageIds)
                ->whereNull('deleted_at')
                ->get()
                ->groupBy('ticket_message_id')
            : collect();

        $messages = $messages->map(function ($msg) use ($ticket, $allAttachments) {
            $msgAttachments = ($allAttachments[$msg->id] ?? collect())->map(fn($a) => [
                'id' => $a->id, 'file_name' => $a->file_name, 'file_size' => $a->file_size,
                'mime_type' => $a->mime_type, 'url' => asset('storage/' . $a->file_path),
            ])->values();
            return [
                'id' => $msg->id,
                'uuid' => $msg->uuid,
                'message' => $msg->message,
                'sender_type' => $msg->sender_type,
                'sender_id' => $msg->sender_id,
                'message_type' => $msg->message_type,
                'is_internal' => (bool) $msg->is_internal,
                'sender_name' => $msg->sender_type === 'system'
                    ? 'System'
                    : ($msg->sender_type === 'user'
                        ? ($msg->user_name ?? 'Agent')
                        : ($msg->customer_sender_name ?? $ticket->customer_name ?? 'Customer')),
                'created_at' => $msg->created_at,
                'time' => \Carbon\Carbon::parse($msg->created_at)->format('g:i A'),
                'date' => \Carbon\Carbon::parse($msg->created_at)->format('M d, Y'),
                'initial' => $msg->sender_type === 'system'
                    ? 'S'
                    : strtoupper(substr(
                        $msg->sender_type === 'user' ? ($msg->user_name ?? 'A') : ($msg->customer_sender_name ?? 'C'), 0, 1
                    )),
                'attachments' => $msgAttachments,
            ];
        });

        // Customer typing status
        $customerTyping = Cache::get("typing_ticket_{$ticket->id}_customer", false);

        // Customer online status
        $customerOnline = $ticket->customer_id ? Cache::get("user_online_{$ticket->customer_id}", false) : false;
        $customerLastSeen = null;
        if ($ticket->customer_id && !$customerOnline) {
            $customerLastSeen = DB::table('users_customers')
                ->where('user_id', $ticket->customer_id)
                ->value('last_seen_at');
        }

        // Check current ticket closed status
        $currentStatus = DB::table('ticket_statuses')->where('id', $ticket->status_id)->first();
        $isClosed = $currentStatus && $currentStatus->is_closed;

        return response()->json([
            'messages' => $messages,
            'timestamp' => now()->toIso8601String(),
            'typing' => $customerTyping,
            'customer_online' => $customerOnline,
            'customer_last_seen' => $customerLastSeen,
            'is_closed' => (bool) $isClosed,
        ]);
    }

    /**
     * AJAX: Send reply without page reload.
     */
    public function ajaxReply(Request $request, string $uuid)
    {
        $ticket = DB::table('tickets')->where('uuid', $uuid)->firstOrFail();

        $status = DB::table('ticket_statuses')->where('id', $ticket->status_id)->first();
        if ($status && $status->is_closed) {
            return response()->json(['success' => false, 'error' => 'This ticket is closed.', 'is_closed' => true], 403);
        }

        $validated = $request->validate([
            'message' => ['required_without:attachments', 'nullable', 'string'],
            'message_type' => ['required', 'in:reply,note'],
            'attachments' => ['nullable', 'array', 'max:10'],
            'attachments.*' => ['file', 'max:10240'],
        ]);

        $message = $validated['message'] ?? '';

        $isInternal = $validated['message_type'] === 'note';
        $isFirstResponse = !$isInternal && DB::table('ticket_messages')
            ->where('ticket_id', $ticket->id)
            ->where('sender_type', 'user')
            ->where('message_type', 'reply')
            ->doesntExist();

        // Check if this agent has sent any message on this ticket before
        $agentHasMessaged = DB::table('ticket_messages')
            ->where('ticket_id', $ticket->id)
            ->where('sender_type', 'user')
            ->where('sender_id', auth()->id())
            ->whereNull('deleted_at')
            ->exists();

        // Insert "joined" system message if agent is new to this conversation
        $joinedMsg = null;
        if (!$agentHasMessaged && !$isInternal) {
            $joinedMsgId = DB::table('ticket_messages')->insertGetId([
                'uuid' => Str::uuid(),
                'ticket_id' => $ticket->id,
                'sender_type' => 'system',
                'sender_id' => null,
                'message_type' => 'system',
                'message' => auth()->user()->name . ' joined the conversation',
                'is_internal' => false,
                'is_first_response' => false,
                'created_by' => auth()->id(),
                'created_at' => now(),
                'updated_at' => now(),
            ]);
            $joinedMsg = [
                'id' => $joinedMsgId,
                'message' => auth()->user()->name . ' joined the conversation',
                'sender_type' => 'system',
                'sender_id' => null,
                'message_type' => 'system',
                'is_internal' => false,
                'sender_name' => 'System',
                'created_at' => now()->toIso8601String(),
                'time' => now()->format('g:i A'),
                'initial' => 'S',
                'attachments' => [],
            ];
        }

        $messageId = DB::table('ticket_messages')->insertGetId([
            'uuid' => Str::uuid(),
            'ticket_id' => $ticket->id,
            'sender_type' => 'user',
            'sender_id' => auth()->id(),
            'message_type' => $validated['message_type'],
            'message' => $message,
            'is_internal' => $isInternal,
            'is_first_response' => $isFirstResponse,
            'created_by' => auth()->id(),
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        // Handle attachments
        $attachmentPayloads = [];
        if ($request->hasFile('attachments')) {
            foreach ($request->file('attachments') as $file) {
                $path = $file->store('ticket-attachments/' . $ticket->id, 'public');
                $attId = DB::table('ticket_attachments')->insertGetId([
                    'uuid' => Str::uuid(),
                    'ticket_id' => $ticket->id,
                    'ticket_message_id' => $messageId,
                    'file_name' => $file->getClientOriginalName(),
                    'file_path' => $path,
                    'file_size' => $file->getSize(),
                    'mime_type' => $file->getMimeType(),
                    'uploaded_by_type' => 'user',
                    'uploaded_by_id' => auth()->id(),
                    'created_by' => auth()->id(),
                    'created_at' => now(),
                    'updated_at' => now(),
                ]);
                $attachmentPayloads[] = [
                    'id' => $attId,
                    'file_name' => $file->getClientOriginalName(),
                    'file_size' => $file->getSize(),
                    'mime_type' => $file->getMimeType(),
                    'url' => asset('storage/' . $path),
                ];
            }
        }

        if ($isFirstResponse) {
            DB::table('tickets')->where('id', $ticket->id)->update([
                'first_response_at' => now(),
                'last_agent_reply_at' => now(),
                'updated_at' => now(),
            ]);
        } elseif (!$isInternal) {
            DB::table('tickets')->where('id', $ticket->id)->update([
                'last_agent_reply_at' => now(),
                'updated_at' => now(),
            ]);
        }

        DB::table('ticket_events')->insert([
            'uuid' => Str::uuid(),
            'ticket_id' => $ticket->id,
            'event_type' => $isInternal ? 'note_added' : 'reply_sent',
            'actor_type' => 'user',
            'actor_id' => auth()->id(),
            'created_at' => now(),
        ]);

        // Clear typing indicator
        Cache::forget("typing_ticket_{$ticket->id}_agent");

        $messagePayload = [
            'id' => $messageId,
            'message' => $message,
            'sender_type' => 'user',
            'sender_id' => auth()->id(),
            'message_type' => $validated['message_type'],
            'is_internal' => $isInternal,
            'sender_name' => auth()->user()->name,
            'created_at' => now()->toIso8601String(),
            'time' => now()->format('g:i A'),
            'initial' => strtoupper(substr(auth()->user()->name, 0, 1)),
            'attachments' => $attachmentPayloads,
        ];

        // Broadcast through active realtime driver
        if ($joinedMsg) {
            app(RealtimeManager::class)->broadcastMessage($ticket->id, $joinedMsg);
        }
        app(RealtimeManager::class)->broadcastMessage($ticket->id, $messagePayload);

        $responseMessages = [];
        if ($joinedMsg) {
            $responseMessages[] = $joinedMsg;
        }
        $responseMessages[] = $messagePayload;

        return response()->json([
            'success' => true,
            'message' => $messagePayload,
            'messages' => $responseMessages,
        ]);
    }

    /**
     * AJAX: Signal typing state.
     */
    public function typing(Request $request, string $uuid)
    {
        $ticket = DB::table('tickets')->where('uuid', $uuid)->firstOrFail();
        $typing = $request->boolean('typing', false);

        if ($typing) {
            Cache::put("typing_ticket_{$ticket->id}_agent", auth()->user()->name, 10);
        } else {
            Cache::forget("typing_ticket_{$ticket->id}_agent");
        }

        // Broadcast typing through active realtime driver
        app(RealtimeManager::class)->broadcastTyping($ticket->id, [
            'sender_type' => 'user',
            'sender_name' => auth()->user()->name,
            'typing' => $typing,
        ]);

        return response()->json(['ok' => true]);
    }

    public function updateStatus(Request $request, string $uuid)
    {
        $ticket = DB::table('tickets')->where('uuid', $uuid)->firstOrFail();
        $validated = $request->validate(['status_id' => ['required', 'exists:ticket_statuses,id']]);

        $oldStatusId = $ticket->status_id;
        $newStatus = DB::table('ticket_statuses')->where('id', $validated['status_id'])->first();

        DB::table('tickets')->where('id', $ticket->id)->update([
            'status_id' => $validated['status_id'],
            'resolved_at' => $newStatus->code === 'resolved' ? now() : $ticket->resolved_at,
            'closed_at' => $newStatus->is_closed ? now() : $ticket->closed_at,
            'updated_at' => now(),
        ]);

        // End previous status log
        DB::table('ticket_status_logs')
            ->where('ticket_id', $ticket->id)
            ->whereNull('ended_at')
            ->update(['ended_at' => now()]);

        DB::table('ticket_status_logs')->insert([
            'uuid' => Str::uuid(),
            'ticket_id' => $ticket->id,
            'old_status_id' => $oldStatusId,
            'new_status_id' => $validated['status_id'],
            'changed_by' => auth()->id(),
            'started_at' => now(),
            'created_at' => now(),
        ]);

        DB::table('ticket_events')->insert([
            'uuid' => Str::uuid(),
            'ticket_id' => $ticket->id,
            'event_type' => 'status_changed',
            'actor_type' => 'user',
            'actor_id' => auth()->id(),
            'old_value' => DB::table('ticket_statuses')->where('id', $oldStatusId)->value('name'),
            'new_value' => $newStatus->name,
            'created_at' => now(),
        ]);

        return redirect()->route('tickets/show', $uuid)->with('success', 'Status updated.');
    }

    public function updatePriority(Request $request, string $uuid)
    {
        $ticket = DB::table('tickets')->where('uuid', $uuid)->firstOrFail();
        $validated = $request->validate(['priority_id' => ['required', 'exists:ticket_priorities,id']]);

        $oldPriority = DB::table('ticket_priorities')->where('id', $ticket->priority_id)->value('name');
        $newPriority = DB::table('ticket_priorities')->where('id', $validated['priority_id'])->value('name');

        DB::table('tickets')->where('id', $ticket->id)->update([
            'priority_id' => $validated['priority_id'],
            'updated_at' => now(),
        ]);

        DB::table('ticket_events')->insert([
            'uuid' => Str::uuid(),
            'ticket_id' => $ticket->id,
            'event_type' => 'priority_changed',
            'actor_type' => 'user',
            'actor_id' => auth()->id(),
            'old_value' => $oldPriority,
            'new_value' => $newPriority,
            'created_at' => now(),
        ]);

        return redirect()->route('tickets/show', $uuid)->with('success', 'Priority updated.');
    }

    public function assign(Request $request, string $uuid)
    {
        $ticket = DB::table('tickets')->where('uuid', $uuid)->firstOrFail();
        $validated = $request->validate(['assigned_to' => ['required', 'exists:users,id']]);

        // Unassign previous
        DB::table('ticket_assignments')
            ->where('ticket_id', $ticket->id)->whereNull('unassigned_at')
            ->update(['unassigned_at' => now()]);

        DB::table('tickets')->where('id', $ticket->id)->update([
            'assigned_to' => $validated['assigned_to'],
            'updated_at' => now(),
        ]);

        DB::table('ticket_assignments')->insert([
            'uuid' => Str::uuid(),
            'ticket_id' => $ticket->id,
            'assigned_to' => $validated['assigned_to'],
            'assigned_by' => auth()->id(),
            'assigned_at' => now(),
        ]);

        $agentName = DB::table('users')->where('id', $validated['assigned_to'])->value('name');
        DB::table('ticket_events')->insert([
            'uuid' => Str::uuid(),
            'ticket_id' => $ticket->id,
            'event_type' => 'assigned',
            'actor_type' => 'user',
            'actor_id' => auth()->id(),
            'new_value' => $agentName,
            'created_at' => now(),
        ]);

        // Insert system message for assignment change visible in chat
        $assignerName = auth()->user()->name;
        $systemMsg = ($ticket->assigned_to && $ticket->assigned_to != $validated['assigned_to'])
            ? "Ticket reassigned to {$agentName} by {$assignerName}"
            : "Ticket assigned to {$agentName} by {$assignerName}";

        DB::table('ticket_messages')->insert([
            'uuid' => Str::uuid(),
            'ticket_id' => $ticket->id,
            'sender_type' => 'system',
            'sender_id' => null,
            'message_type' => 'system',
            'message' => $systemMsg,
            'is_internal' => false,
            'is_first_response' => false,
            'created_by' => auth()->id(),
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        return redirect()->route('tickets/show', $uuid)->with('success', 'Ticket assigned to ' . $agentName . '.');
    }

    public function addTag(Request $request, string $uuid)
    {
        $ticket = DB::table('tickets')->where('uuid', $uuid)->firstOrFail();
        $validated = $request->validate(['tag_id' => ['required', 'exists:tags,id']]);

        $exists = DB::table('ticket_tag')->where('ticket_id', $ticket->id)->where('tag_id', $validated['tag_id'])->exists();
        if (!$exists) {
            DB::table('ticket_tag')->insert([
                'ticket_id' => $ticket->id,
                'tag_id' => $validated['tag_id'],
                'created_by' => auth()->id(),
                'created_at' => now(),
            ]);
        }

        return redirect()->route('tickets/show', $uuid)->with('success', 'Tag added.');
    }

    public function removeTag(string $uuid, int $tagId)
    {
        $ticket = DB::table('tickets')->where('uuid', $uuid)->firstOrFail();
        DB::table('ticket_tag')->where('ticket_id', $ticket->id)->where('tag_id', $tagId)->delete();
        return redirect()->route('tickets/show', $uuid)->with('success', 'Tag removed.');
    }

    public function addWatcher(Request $request, string $uuid)
    {
        $ticket = DB::table('tickets')->where('uuid', $uuid)->firstOrFail();
        $validated = $request->validate(['user_id' => ['required', 'exists:users,id']]);

        $exists = DB::table('ticket_watchers')->where('ticket_id', $ticket->id)->where('user_id', $validated['user_id'])->exists();
        if (!$exists) {
            DB::table('ticket_watchers')->insert([
                'ticket_id' => $ticket->id,
                'user_id' => $validated['user_id'],
                'created_by' => auth()->id(),
                'created_at' => now(),
            ]);
        }

        return redirect()->route('tickets/show', $uuid)->with('success', 'Watcher added.');
    }

    public function removeWatcher(string $uuid, int $userId)
    {
        $ticket = DB::table('tickets')->where('uuid', $uuid)->firstOrFail();
        DB::table('ticket_watchers')->where('ticket_id', $ticket->id)->where('user_id', $userId)->delete();
        return redirect()->route('tickets/show', $uuid)->with('success', 'Watcher removed.');
    }

    public function destroy(string $uuid)
    {
        $ticket = DB::table('tickets')->where('uuid', $uuid)->firstOrFail();
        DB::table('tickets')->where('id', $ticket->id)->update([
            'deleted_by' => auth()->id(),
            'deleted_at' => now(),
        ]);

        return redirect()->route('tickets/index')->with('success', 'Ticket deleted.');
    }

    public function bulkAction(Request $request)
    {
        $validated = $request->validate([
            'action' => ['required', 'in:status,priority,assign,delete'],
            'ticket_ids' => ['required', 'array'],
            'ticket_ids.*' => ['exists:tickets,id'],
            'value' => ['nullable'],
        ]);

        $ids = $validated['ticket_ids'];

        switch ($validated['action']) {
            case 'status':
                DB::table('tickets')->whereIn('id', $ids)->update(['status_id' => $validated['value'], 'updated_at' => now()]);
                break;
            case 'priority':
                DB::table('tickets')->whereIn('id', $ids)->update(['priority_id' => $validated['value'], 'updated_at' => now()]);
                break;
            case 'assign':
                DB::table('tickets')->whereIn('id', $ids)->update(['assigned_to' => $validated['value'], 'updated_at' => now()]);
                break;
            case 'delete':
                DB::table('tickets')->whereIn('id', $ids)->update(['deleted_by' => auth()->id(), 'deleted_at' => now()]);
                break;
        }

        return redirect()->back()->with('success', 'Bulk action applied to ' . count($ids) . ' ticket(s).');
    }

    /**
     * Search customers (AJAX endpoint for searchable select).
     */
    public function searchCustomers(Request $request)
    {
        $q = trim($request->input('q', ''));
        if (mb_strlen($q) < 1) {
            return response()->json([]);
        }

        $customers = DB::table('users')
            ->whereNull('users.deleted_at')
            ->where('users.role', 'customer')
            ->where('users.status', true)
            ->leftJoin('users_customers', 'users.id', '=', 'users_customers.user_id')
            ->where(function ($query) use ($q) {
                $query->where('users.name', 'like', "%{$q}%")
                      ->orWhere('users.email', 'like', "%{$q}%")
                      ->orWhere('users.phone', 'like', "%{$q}%")
                      ->orWhere('users_customers.company', 'like', "%{$q}%");
            })
            ->orderBy('users.name')
            ->limit(10)
            ->get(['users.id', 'users.name', 'users.email', 'users.phone', 'users_customers.company']);

        return response()->json($customers->map(fn ($c) => [
            'id' => $c->id,
            'text' => $c->name . ($c->email ? " ({$c->email})" : ''),
            'email' => $c->email,
            'phone' => $c->phone,
            'company' => $c->company,
        ]));
    }

    /**
     * Search agents/users (AJAX endpoint for searchable select).
     */
    public function searchAgents(Request $request)
    {
        $q = trim($request->input('q', ''));
        if (mb_strlen($q) < 1) {
            return response()->json([]);
        }

        $agents = DB::table('users')
            ->whereNull('deleted_at')
            ->where('status', true)
            ->where('role', 'agent')
            ->where(function ($query) use ($q) {
                $query->where('name', 'like', "%{$q}%")
                      ->orWhere('email', 'like', "%{$q}%");
            })
            ->orderBy('name')
            ->limit(10)
            ->get(['id', 'name', 'email']);

        return response()->json($agents->map(fn ($a) => [
            'id' => $a->id,
            'text' => $a->name . " ({$a->email})",
            'email' => $a->email,
        ]));
    }

    /**
     * Quick-create a customer (AJAX from ticket create form).
     */
    public function storeQuickCustomer(Request $request)
    {
        $validated = $request->validate([
            'name'           => ['required', 'string', 'max:255'],
            'email'          => ['nullable', 'email', 'max:150', 'unique:users,email'],
            'phone'          => ['nullable', 'string', 'max:50'],
            'password'       => ['nullable', 'string', 'min:7'],
            'status'         => ['nullable', 'boolean'],
            'branch_id'      => ['nullable', 'integer', 'exists:branches,id'],
            'company'        => ['nullable', 'string', 'max:150'],
            'country'        => ['nullable', 'string', 'max:100'],
            'city'           => ['nullable', 'string', 'max:100'],
            'customer_notes' => ['nullable', 'string', 'max:5000'],
        ]);

        $userId = DB::table('users')->insertGetId([
            'uid'        => Str::uuid(),
            'name'       => $validated['name'],
            'email'      => $validated['email'] ?? null,
            'phone'      => $validated['phone'] ?? null,
            'role'       => 'customer',
            'status'     => $validated['status'] ?? true,
            'branch_id'  => $validated['branch_id'] ?? null,
            'password'   => bcrypt($validated['password'] ?? Str::random(32)),
            'created_by' => auth()->id(),
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        DB::table('users_customers')->insert([
            'user_id'        => $userId,
            'company'        => $validated['company'] ?? null,
            'country'        => $validated['country'] ?? null,
            'city'           => $validated['city'] ?? null,
            'notes'          => $validated['customer_notes'] ?? null,
            'source'         => 'manual',
            'created_by'     => auth()->id(),
            'created_at'     => now(),
            'updated_at'     => now(),
        ]);

        return response()->json([
            'id'   => $userId,
            'text' => $validated['name'] . (!empty($validated['email']) ? " ({$validated['email']})" : ''),
        ]);
    }

    protected function applyAgentScope($query)
    {
        return $query;
    }

    protected function buildViewCounts($openStatusIds): array
    {
        return [
            'all' => DB::table('tickets')->whereNull('deleted_at')->where('status', 1)->count(),
            'open' => DB::table('tickets')->whereNull('deleted_at')->where('status', 1)
                ->where('status_id', DB::table('ticket_statuses')->where('code', 'open')->value('id'))->count(),
            'pending' => DB::table('tickets')->whereNull('deleted_at')->where('status', 1)
                ->where('status_id', DB::table('ticket_statuses')->where('code', 'pending')->value('id'))->count(),
            'resolved' => DB::table('tickets')->whereNull('deleted_at')->where('status', 1)
                ->where('status_id', DB::table('ticket_statuses')->where('code', 'resolved')->value('id'))->count(),
            'unassigned' => DB::table('tickets')->whereNull('deleted_at')->where('status', 1)
                ->whereNull('assigned_to')->whereIn('status_id', $openStatusIds)->count(),
            'overdue' => DB::table('tickets')->whereNull('deleted_at')->where('status', 1)
                ->whereIn('status_id', $openStatusIds)->whereNotNull('due_at')->where('due_at', '<', now())->count(),
            'mine' => DB::table('tickets')->whereNull('deleted_at')->where('status', 1)
                ->where('assigned_to', auth()->id())->count(),
        ];
    }
}
