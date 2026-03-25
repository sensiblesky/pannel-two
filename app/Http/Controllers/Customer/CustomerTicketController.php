<?php

namespace App\Http\Controllers\Customer;

use App\Http\Controllers\Controller;
use App\Realtime\RealtimeManager;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class CustomerTicketController extends Controller
{
    /**
     * Poll for new updates on customer's tickets (agent replies & status changes).
     */
    public function poll(Request $request)
    {
        $since = $request->input('since');
        if (!$since) {
            return response()->json(['count' => 0, 'updates' => [], 'timestamp' => now()->toIso8601String()]);
        }

        $customerId = auth()->id();

        // New agent replies (non-internal) on customer's tickets
        $newReplies = DB::table('ticket_messages')
            ->join('tickets', 'ticket_messages.ticket_id', '=', 'tickets.id')
            ->where('tickets.customer_id', $customerId)
            ->whereNull('tickets.deleted_at')
            ->where('ticket_messages.sender_type', 'user')
            ->where('ticket_messages.is_internal', false)
            ->where('ticket_messages.created_at', '>', $since)
            ->whereNull('ticket_messages.deleted_at')
            ->select('tickets.ticket_no', 'tickets.uuid', 'tickets.subject')
            ->distinct()
            ->limit(10)
            ->get()
            ->map(fn ($r) => ['type' => 'reply', 'ticket_no' => $r->ticket_no, 'uuid' => $r->uuid, 'subject' => $r->subject]);

        // Status changes on customer's tickets
        $statusChanges = DB::table('ticket_events')
            ->join('tickets', 'ticket_events.ticket_id', '=', 'tickets.id')
            ->where('tickets.customer_id', $customerId)
            ->whereNull('tickets.deleted_at')
            ->where('ticket_events.event_type', 'status_changed')
            ->where('ticket_events.created_at', '>', $since)
            ->select('tickets.ticket_no', 'tickets.uuid', 'tickets.subject', 'ticket_events.new_value')
            ->limit(10)
            ->get()
            ->map(fn ($e) => ['type' => 'status', 'ticket_no' => $e->ticket_no, 'uuid' => $e->uuid, 'subject' => $e->subject, 'new_status' => $e->new_value]);

        $updates = $newReplies->merge($statusChanges);

        return response()->json([
            'count' => $updates->count(),
            'updates' => $updates->values(),
            'timestamp' => now()->toIso8601String(),
        ]);
    }

    public function index(Request $request)
    {
        $customerId = auth()->id();

        $query = DB::table('tickets')
            ->whereNull('tickets.deleted_at')
            ->where('tickets.status', 1)
            ->where('tickets.customer_id', $customerId)
            ->leftJoin('ticket_statuses', 'tickets.status_id', '=', 'ticket_statuses.id')
            ->leftJoin('ticket_priorities', 'tickets.priority_id', '=', 'ticket_priorities.id')
            ->leftJoin('ticket_categories', 'tickets.category_id', '=', 'ticket_categories.id')
            ->select(
                'tickets.id', 'tickets.uuid', 'tickets.ticket_no', 'tickets.subject',
                'tickets.created_at', 'tickets.due_at',
                'ticket_statuses.name as status_name', 'ticket_statuses.color as status_color', 'ticket_statuses.is_closed',
                'ticket_priorities.name as priority_name', 'ticket_priorities.color as priority_color',
                'ticket_categories.name as category_name',
            );

        if ($search = $request->input('search')) {
            $query->where(function ($q) use ($search) {
                $q->where('tickets.ticket_no', 'like', "%{$search}%")
                  ->orWhere('tickets.subject', 'like', "%{$search}%");
            });
        }

        if ($statusId = $request->input('status_id')) {
            $query->where('tickets.status_id', $statusId);
        }

        $view = $request->input('view', 'all');
        $openStatusIds = DB::table('ticket_statuses')->whereNull('deleted_at')->where('status', 1)->where('is_closed', false)->pluck('id');

        switch ($view) {
            case 'open':
                $query->whereIn('tickets.status_id', $openStatusIds);
                break;
            case 'closed':
                $closedStatusIds = DB::table('ticket_statuses')->whereNull('deleted_at')->where('status', 1)->where('is_closed', true)->pluck('id');
                $query->whereIn('tickets.status_id', $closedStatusIds);
                break;
        }

        $tickets = $query->orderByDesc('tickets.created_at')->paginate(15)->withQueryString();

        $statuses = DB::table('ticket_statuses')->whereNull('deleted_at')->where('status', 1)->orderBy('sort_order')->get();

        $viewCounts = [
            'all' => DB::table('tickets')->whereNull('deleted_at')->where('status', 1)->where('customer_id', $customerId)->count(),
            'open' => DB::table('tickets')->whereNull('deleted_at')->where('status', 1)->where('customer_id', $customerId)->whereIn('status_id', $openStatusIds)->count(),
            'closed' => DB::table('tickets')->whereNull('deleted_at')->where('status', 1)->where('customer_id', $customerId)
                ->whereIn('status_id', DB::table('ticket_statuses')->whereNull('deleted_at')->where('status', 1)->where('is_closed', true)->pluck('id'))->count(),
        ];

        return view('customer.tickets.index', compact('tickets', 'statuses', 'view', 'viewCounts'));
    }

    public function create()
    {
        $categories = DB::table('ticket_categories')->whereNull('deleted_at')->where('status', 1)->orderBy('name')->get();
        $priorities = DB::table('ticket_priorities')->whereNull('deleted_at')->where('status', 1)->orderBy('sort_order')->get();

        return view('customer.tickets.create', compact('categories', 'priorities'));
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'subject' => ['required', 'string', 'max:255'],
            'description' => ['nullable', 'string'],
            'category_id' => ['nullable', 'exists:ticket_categories,id'],
            'priority_id' => ['nullable', 'exists:ticket_priorities,id'],
            'attachments' => ['nullable', 'array'],
            'attachments.*' => ['file', 'max:10240'],
        ]);

        $prefix = DB::table('ticket_settings')->where('key', 'ticket_number_prefix')->whereNull('branch_id')->value('value') ?? 'TKT-';
        $lastTicket = DB::table('tickets')->orderByDesc('id')->value('id') ?? 0;
        $ticketNo = $prefix . str_pad($lastTicket + 1, 6, '0', STR_PAD_LEFT);

        $defaultStatusCode = DB::table('ticket_settings')->where('key', 'default_status')->whereNull('branch_id')->value('value') ?? 'new';
        $statusId = DB::table('ticket_statuses')->where('code', $defaultStatusCode)->value('id');

        if (empty($validated['priority_id'])) {
            $defaultPriorityCode = DB::table('ticket_settings')->where('key', 'default_priority')->whereNull('branch_id')->value('value') ?? 'medium';
            $validated['priority_id'] = DB::table('ticket_priorities')->where('code', $defaultPriorityCode)->value('id');
        }

        $ticketId = DB::table('tickets')->insertGetId([
            'uuid' => Str::uuid(),
            'ticket_no' => $ticketNo,
            'subject' => $validated['subject'],
            'description' => $validated['description'] ?? null,
            'customer_id' => auth()->id(),
            'category_id' => $validated['category_id'] ?? null,
            'priority_id' => $validated['priority_id'] ?? null,
            'status_id' => $statusId,
            'source' => 'web',
            'created_by' => auth()->id(),
            'created_at' => now(),
            'updated_at' => now(),
        ]);

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
                    'uploaded_by_id' => auth()->id(),
                    'created_by' => auth()->id(),
                    'created_at' => now(),
                    'updated_at' => now(),
                ]);
            }
        }

        DB::table('ticket_status_logs')->insert([
            'uuid' => Str::uuid(),
            'ticket_id' => $ticketId,
            'old_status_id' => null,
            'new_status_id' => $statusId,
            'changed_by' => auth()->id(),
            'started_at' => now(),
            'created_at' => now(),
        ]);

        DB::table('ticket_events')->insert([
            'uuid' => Str::uuid(),
            'ticket_id' => $ticketId,
            'event_type' => 'created',
            'actor_type' => 'customer',
            'actor_id' => auth()->id(),
            'new_value' => $ticketNo,
            'created_at' => now(),
        ]);

        $uuid = DB::table('tickets')->where('id', $ticketId)->value('uuid');

        return redirect()->route('customer.tickets.show', $uuid)->with('success', 'Ticket created successfully.');
    }

    public function show(string $uuid)
    {
        $ticket = DB::table('tickets')
            ->whereNull('tickets.deleted_at')
            ->where('tickets.uuid', $uuid)
            ->where('tickets.customer_id', auth()->id())
            ->leftJoin('ticket_statuses', 'tickets.status_id', '=', 'ticket_statuses.id')
            ->leftJoin('ticket_priorities', 'tickets.priority_id', '=', 'ticket_priorities.id')
            ->leftJoin('ticket_categories', 'tickets.category_id', '=', 'ticket_categories.id')
            ->leftJoin('users as agents', 'tickets.assigned_to', '=', 'agents.id')
            ->select(
                'tickets.*',
                'ticket_statuses.name as status_name', 'ticket_statuses.color as status_color', 'ticket_statuses.is_closed',
                'ticket_priorities.name as priority_name', 'ticket_priorities.color as priority_color',
                'ticket_categories.name as category_name',
                'agents.name as agent_name',
            )
            ->firstOrFail();

        $messages = DB::table('ticket_messages')
            ->where('ticket_messages.ticket_id', $ticket->id)
            ->whereNull('ticket_messages.deleted_at')
            ->where('ticket_messages.is_internal', false)
            ->leftJoin('users', function ($join) {
                $join->on('ticket_messages.sender_id', '=', 'users.id');
            })
            ->select('ticket_messages.*', 'users.name as sender_name')
            ->orderBy('ticket_messages.created_at')
            ->get();

        $attachments = DB::table('ticket_attachments')
            ->where('ticket_id', $ticket->id)
            ->whereNull('deleted_at')
            ->orderBy('created_at')
            ->get();

        $allowCustomerAttachments = DB::table('ticket_settings')
            ->where('key', 'allow_customer_attachments')
            ->whereNull('branch_id')
            ->value('value') === '1';

        $allowCustomerClose = DB::table('ticket_settings')
            ->where('key', 'allow_customer_close')
            ->whereNull('branch_id')
            ->value('value') === '1';

        $allowCustomerReopen = DB::table('ticket_settings')
            ->where('key', 'allow_customer_reopen')
            ->whereNull('branch_id')
            ->value('value') === '1';

        return view('customer.tickets.show', compact('ticket', 'messages', 'attachments', 'allowCustomerAttachments', 'allowCustomerClose', 'allowCustomerReopen'));
    }

    public function reply(Request $request, string $uuid)
    {
        $ticket = DB::table('tickets')
            ->where('uuid', $uuid)
            ->where('customer_id', auth()->id())
            ->firstOrFail();

        // Enforce closed ticket restriction
        $status = DB::table('ticket_statuses')->where('id', $ticket->status_id)->first();
        if ($status && $status->is_closed) {
            return redirect()->route('customer.tickets.show', $uuid)->with('error', 'This ticket is closed.');
        }

        $validated = $request->validate([
            'message' => ['required', 'string'],
            'attachments' => ['nullable', 'array'],
            'attachments.*' => ['file', 'max:10240'],
        ]);

        $messageId = DB::table('ticket_messages')->insertGetId([
            'uuid' => Str::uuid(),
            'ticket_id' => $ticket->id,
            'sender_type' => 'customer',
            'sender_id' => auth()->id(),
            'message_type' => 'reply',
            'message' => $validated['message'],
            'is_internal' => false,
            'created_by' => auth()->id(),
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        DB::table('tickets')->where('id', $ticket->id)->update([
            'last_customer_reply_at' => now(),
            'updated_at' => now(),
        ]);

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
                    'uploaded_by_type' => 'customer',
                    'uploaded_by_id' => auth()->id(),
                    'created_by' => auth()->id(),
                    'created_at' => now(),
                    'updated_at' => now(),
                ]);
            }
        }

        DB::table('ticket_events')->insert([
            'uuid' => Str::uuid(),
            'ticket_id' => $ticket->id,
            'event_type' => 'customer_reply',
            'actor_type' => 'customer',
            'actor_id' => auth()->id(),
            'created_at' => now(),
        ]);

        return redirect()->route('customer.tickets.show', $uuid)->with('success', 'Reply sent.');
    }

    /**
     * AJAX: Get new messages for a ticket since a timestamp.
     */
    public function pollMessages(Request $request, string $uuid)
    {
        $ticket = DB::table('tickets')
            ->where('uuid', $uuid)
            ->where('customer_id', auth()->id())
            ->firstOrFail();

        $since = $request->input('since');

        // Keep current customer's online status alive on every poll
        Cache::put('user_online_' . auth()->id(), true, 120);

        $query = DB::table('ticket_messages')
            ->where('ticket_messages.ticket_id', $ticket->id)
            ->whereNull('ticket_messages.deleted_at')
            ->where('ticket_messages.is_internal', false)
            ->leftJoin('users', function ($join) {
                $join->on('ticket_messages.sender_id', '=', 'users.id');
            })
            ->select(
                'ticket_messages.id', 'ticket_messages.uuid', 'ticket_messages.message',
                'ticket_messages.sender_type', 'ticket_messages.sender_id',
                'ticket_messages.created_at',
                'users.name as sender_name'
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

        $messages = $messages->map(function ($msg) use ($allAttachments) {
            $msgAttachments = ($allAttachments[$msg->id] ?? collect())->map(fn($a) => [
                'id' => $a->id, 'file_name' => $a->file_name, 'file_size' => $a->file_size,
                'mime_type' => $a->mime_type, 'url' => asset('storage/' . $a->file_path),
            ])->values();
            return [
                'id' => $msg->id,
                'uuid' => $msg->uuid,
                'message' => $msg->message,
                'sender_type' => $msg->sender_type,
                'sender_name' => $msg->sender_type === 'system'
                    ? 'System'
                    : ($msg->sender_type === 'customer' ? 'You' : ($msg->sender_name ?? 'Support')),
                'created_at' => $msg->created_at,
                'time' => \Carbon\Carbon::parse($msg->created_at)->format('g:i A'),
                'date' => \Carbon\Carbon::parse($msg->created_at)->format('M d, Y'),
                'initial' => strtoupper(substr($msg->sender_name ?? 'S', 0, 1)),
                'attachments' => $msgAttachments,
            ];
        });

        // Check if any agent is typing on this ticket
        $agentTyping = Cache::get("typing_ticket_{$ticket->id}_agent");

        // Agent online status
        $agentOnline = false;
        $agentLastSeen = null;
        if ($ticket->assigned_to) {
            $agentOnline = Cache::get("user_online_{$ticket->assigned_to}", false);
            if (!$agentOnline) {
                $agentLastSeen = Cache::get("user_last_seen_{$ticket->assigned_to}");
            }
        }

        // Check current ticket closed status
        $currentStatus = DB::table('ticket_statuses')->where('id', $ticket->status_id)->first();
        $isClosed = $currentStatus && $currentStatus->is_closed;

        return response()->json([
            'messages' => $messages,
            'timestamp' => now()->toIso8601String(),
            'typing' => $agentTyping,
            'agent_online' => $agentOnline,
            'agent_last_seen' => $agentLastSeen,
            'is_closed' => (bool) $isClosed,
        ]);
    }

    /**
     * AJAX: Send reply without page reload.
     */
    public function ajaxReply(Request $request, string $uuid)
    {
        $ticket = DB::table('tickets')
            ->where('uuid', $uuid)
            ->where('customer_id', auth()->id())
            ->firstOrFail();

        // Enforce closed ticket restriction
        $status = DB::table('ticket_statuses')->where('id', $ticket->status_id)->first();
        if ($status && $status->is_closed) {
            return response()->json(['success' => false, 'error' => 'This ticket is closed.'], 403);
        }

        $allowAttachments = DB::table('ticket_settings')
            ->where('key', 'allow_customer_attachments')
            ->whereNull('branch_id')
            ->value('value') === '1';

        $rules = [
            'message' => [$allowAttachments ? 'required_without:attachments' : 'required', 'nullable', 'string'],
        ];
        if ($allowAttachments) {
            $rules['attachments'] = ['nullable', 'array', 'max:10'];
            $rules['attachments.*'] = ['file', 'max:10240'];
        }
        $validated = $request->validate($rules);

        $message = $validated['message'] ?? '';

        $messageId = DB::table('ticket_messages')->insertGetId([
            'uuid' => Str::uuid(),
            'ticket_id' => $ticket->id,
            'sender_type' => 'customer',
            'sender_id' => auth()->id(),
            'message_type' => 'reply',
            'message' => $message,
            'is_internal' => false,
            'created_by' => auth()->id(),
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        DB::table('tickets')->where('id', $ticket->id)->update([
            'last_customer_reply_at' => now(),
            'updated_at' => now(),
        ]);

        DB::table('ticket_events')->insert([
            'uuid' => Str::uuid(),
            'ticket_id' => $ticket->id,
            'event_type' => 'customer_reply',
            'actor_type' => 'customer',
            'actor_id' => auth()->id(),
            'created_at' => now(),
        ]);

        // Handle attachments
        $attachmentPayloads = [];
        if ($allowAttachments && $request->hasFile('attachments')) {
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
                    'uploaded_by_type' => 'customer',
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

        // Clear typing indicator
        Cache::forget("typing_ticket_{$ticket->id}_customer");

        $messagePayload = [
            'id' => $messageId,
            'message' => $message,
            'sender_type' => 'customer',
            'sender_name' => 'You',
            'created_at' => now()->toIso8601String(),
            'time' => now()->format('g:i A'),
            'initial' => strtoupper(substr(auth()->user()->name, 0, 1)),
            'attachments' => $attachmentPayloads,
        ];

        // Broadcast through active realtime driver
        app(RealtimeManager::class)->broadcastMessage($ticket->id, $messagePayload);

        return response()->json([
            'success' => true,
            'message' => $messagePayload,
        ]);
    }

    /**
     * AJAX: Signal typing state.
     */
    public function typing(Request $request, string $uuid)
    {
        $ticket = DB::table('tickets')
            ->where('uuid', $uuid)
            ->where('customer_id', auth()->id())
            ->firstOrFail();

        $typing = $request->boolean('typing', false);

        if ($typing) {
            Cache::put("typing_ticket_{$ticket->id}_customer", true, 10);
        } else {
            Cache::forget("typing_ticket_{$ticket->id}_customer");
        }

        // Broadcast typing through active realtime driver
        app(RealtimeManager::class)->broadcastTyping($ticket->id, [
            'sender_type' => 'customer',
            'sender_name' => auth()->user()->name,
            'typing' => $typing,
        ]);

        return response()->json(['ok' => true]);
    }

    public function closeTicket(string $uuid)
    {
        $ticket = DB::table('tickets')
            ->where('uuid', $uuid)
            ->where('customer_id', auth()->id())
            ->firstOrFail();

        $allowClose = DB::table('ticket_settings')
            ->where('key', 'allow_customer_close')
            ->whereNull('branch_id')
            ->value('value') === '1';

        if (!$allowClose) {
            return redirect()->route('customer.tickets.show', $uuid)->with('error', 'You are not allowed to close tickets.');
        }

        $closedStatus = DB::table('ticket_statuses')
            ->where('is_closed', true)
            ->whereNull('deleted_at')
            ->where('status', true)
            ->first();

        if (!$closedStatus) {
            return redirect()->route('customer.tickets.show', $uuid)->with('error', 'No closed status available.');
        }

        $oldStatusId = $ticket->status_id;

        DB::table('tickets')->where('id', $ticket->id)->update([
            'status_id' => $closedStatus->id,
            'closed_at' => now(),
            'updated_at' => now(),
        ]);

        DB::table('ticket_status_logs')
            ->where('ticket_id', $ticket->id)
            ->whereNull('ended_at')
            ->update(['ended_at' => now()]);

        DB::table('ticket_status_logs')->insert([
            'uuid' => Str::uuid(),
            'ticket_id' => $ticket->id,
            'old_status_id' => $oldStatusId,
            'new_status_id' => $closedStatus->id,
            'changed_by' => auth()->id(),
            'started_at' => now(),
            'created_at' => now(),
        ]);

        DB::table('ticket_events')->insert([
            'uuid' => Str::uuid(),
            'ticket_id' => $ticket->id,
            'event_type' => 'status_changed',
            'actor_type' => 'customer',
            'actor_id' => auth()->id(),
            'old_value' => DB::table('ticket_statuses')->where('id', $oldStatusId)->value('name'),
            'new_value' => $closedStatus->name,
            'created_at' => now(),
        ]);

        // Insert system message
        DB::table('ticket_messages')->insert([
            'uuid' => Str::uuid(),
            'ticket_id' => $ticket->id,
            'sender_type' => 'system',
            'sender_id' => null,
            'message_type' => 'system',
            'message' => 'Customer closed the ticket',
            'is_internal' => false,
            'is_first_response' => false,
            'created_by' => auth()->id(),
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        return redirect()->route('customer.tickets.show', $uuid)->with('success', 'Ticket closed.');
    }

    public function reopenTicket(string $uuid)
    {
        $ticket = DB::table('tickets')
            ->where('uuid', $uuid)
            ->where('customer_id', auth()->id())
            ->firstOrFail();

        $allowReopen = DB::table('ticket_settings')
            ->where('key', 'allow_customer_reopen')
            ->whereNull('branch_id')
            ->value('value') === '1';

        if (!$allowReopen) {
            return redirect()->route('customer.tickets.show', $uuid)->with('error', 'You are not allowed to reopen tickets.');
        }

        $openStatus = DB::table('ticket_statuses')
            ->where('code', 'open')
            ->whereNull('deleted_at')
            ->where('status', true)
            ->first();

        if (!$openStatus) {
            return redirect()->route('customer.tickets.show', $uuid)->with('error', 'No open status available.');
        }

        $oldStatusId = $ticket->status_id;

        DB::table('tickets')->where('id', $ticket->id)->update([
            'status_id' => $openStatus->id,
            'closed_at' => null,
            'updated_at' => now(),
        ]);

        DB::table('ticket_status_logs')
            ->where('ticket_id', $ticket->id)
            ->whereNull('ended_at')
            ->update(['ended_at' => now()]);

        DB::table('ticket_status_logs')->insert([
            'uuid' => Str::uuid(),
            'ticket_id' => $ticket->id,
            'old_status_id' => $oldStatusId,
            'new_status_id' => $openStatus->id,
            'changed_by' => auth()->id(),
            'started_at' => now(),
            'created_at' => now(),
        ]);

        DB::table('ticket_events')->insert([
            'uuid' => Str::uuid(),
            'ticket_id' => $ticket->id,
            'event_type' => 'status_changed',
            'actor_type' => 'customer',
            'actor_id' => auth()->id(),
            'old_value' => DB::table('ticket_statuses')->where('id', $oldStatusId)->value('name'),
            'new_value' => $openStatus->name,
            'created_at' => now(),
        ]);

        // Insert system message
        DB::table('ticket_messages')->insert([
            'uuid' => Str::uuid(),
            'ticket_id' => $ticket->id,
            'sender_type' => 'system',
            'sender_id' => null,
            'message_type' => 'system',
            'message' => 'Customer reopened the ticket',
            'is_internal' => false,
            'is_first_response' => false,
            'created_by' => auth()->id(),
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        return redirect()->route('customer.tickets.show', $uuid)->with('success', 'Ticket reopened.');
    }
}
