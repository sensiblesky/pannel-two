<?php

namespace App\Http\Controllers\Agent;

use App\Http\Controllers\App\TicketController;
use App\Jobs\SendMailJob;
use App\Jobs\SendSmsJob;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class AgentTicketController extends TicketController
{
    /**
     * Scope ticket list to only tickets created by or assigned to this agent.
     */
    protected function applyAgentScope($query)
    {
        $agentId = auth()->id();

        return $query->where(function ($q) use ($agentId) {
            $q->where('tickets.created_by', $agentId)
              ->orWhere('tickets.assigned_to', $agentId);
        });
    }

    /**
     * Scope view counts to only this agent's tickets.
     */
    protected function buildViewCounts($openStatusIds): array
    {
        $agentId = auth()->id();

        $base = fn() => DB::table('tickets')
            ->whereNull('deleted_at')
            ->where('status', 1)
            ->where(function ($q) use ($agentId) {
                $q->where('created_by', $agentId)->orWhere('assigned_to', $agentId);
            });

        return [
            'all'        => $base()->count(),
            'open'       => $base()->where('status_id', DB::table('ticket_statuses')->where('code', 'open')->value('id'))->count(),
            'pending'    => $base()->where('status_id', DB::table('ticket_statuses')->where('code', 'pending')->value('id'))->count(),
            'resolved'   => $base()->where('status_id', DB::table('ticket_statuses')->where('code', 'resolved')->value('id'))->count(),
            'unassigned' => $base()->whereNull('assigned_to')->whereIn('status_id', $openStatusIds)->count(),
            'overdue'    => $base()->whereIn('status_id', $openStatusIds)->whereNotNull('due_at')->where('due_at', '<', now())->count(),
            'mine'       => $base()->where('assigned_to', $agentId)->count(),
        ];
    }

    /**
     * Abort with 403 if this agent is not the creator or assignee of the ticket.
     */
    protected function authorizeAgentTicket(string $uuid): void
    {
        $agentId = auth()->id();

        $ticket = DB::table('tickets')
            ->where('uuid', $uuid)
            ->whereNull('deleted_at')
            ->first();

        if (!$ticket) {
            abort(404);
        }

        if ($ticket->created_by !== $agentId && $ticket->assigned_to !== $agentId) {
            abort(403, 'You are not authorized to access this ticket.');
        }
    }

    public function index(Request $request)
    {
        $view = parent::index($request);
        return view('agent.tickets.index', $view->getData());
    }

    public function create()
    {
        $view = parent::create();
        return view('agent.tickets.create', $view->getData());
    }

    public function store(Request $request)
    {
        $request->validate([
            'contact_phone' => ['required', 'string', 'regex:/^\+255\d{9}$/'],
            'contact_email' => ['nullable', 'email', 'max:255'],
        ], [
            'contact_phone.regex' => 'Phone must be in format +255 followed by exactly 9 digits.',
        ]);

        $response = parent::store($request);

        if ($response instanceof \Illuminate\Http\RedirectResponse) {
            $uuid = last(explode('/', parse_url($response->getTargetUrl(), PHP_URL_PATH)));

            $contactPhone = $request->input('contact_phone');
            $contactEmail = $request->input('contact_email') ?: null;

            DB::table('tickets')->where('uuid', $uuid)->update([
                'contact_phone' => $contactPhone,
                'contact_email' => $contactEmail,
            ]);

            $ticket = DB::table('tickets')
                ->where('uuid', $uuid)
                ->select('ticket_no', 'subject', 'status_id', 'priority_id')
                ->first();

            $statusName   = $ticket->status_id   ? DB::table('ticket_statuses')->where('id', $ticket->status_id)->value('name')   : null;
            $priorityName = $ticket->priority_id  ? DB::table('ticket_priorities')->where('id', $ticket->priority_id)->value('name') : null;
            $trackUrl     = route('help-center.track') . '?' . http_build_query(['ticket_no' => $ticket->ticket_no]);

            // Dispatch email if contact email provided
            if ($contactEmail) {
                SendMailJob::dispatch($contactEmail, new \App\Mail\TicketCreated(
                    customerName: 'Customer',
                    ticketNo: $ticket->ticket_no,
                    ticketSubject: $ticket->subject,
                    description: $request->input('description'),
                    statusName: $statusName,
                    priorityName: $priorityName,
                    createdAt: now()->format('M d, Y h:i A'),
                    trackUrl: $trackUrl,
                ));
            }

            // Dispatch SMS to contact phone
            $smsMessage = "Dear Customer, your support ticket ({$ticket->ticket_no}) has been successfully created. "
                        . "Subject: {$ticket->subject}. "
                        . "Our team will review your issue and get back to you shortly. "
                        . "You can track your ticket here: {$trackUrl}";
            SendSmsJob::dispatch($contactPhone, $smsMessage);

            return redirect()->route('agent.tickets/show', $uuid)->with(
                session()->has('success') ? 'success' : 'error',
                session()->get('success') ?? session()->get('error')
            );
        }

        return $response;
    }

    public function show(string $uuid)
    {
        $this->authorizeAgentTicket($uuid);
        $view = parent::show($uuid);
        $data = $view->getData();

        // Load read receipts for all messages in this ticket
        $messageIds = collect($data['messages'])->pluck('id')->filter()->toArray();
        $reads = [];
        if (!empty($messageIds)) {
            $rows = DB::table('ticket_message_reads')
                ->whereIn('ticket_message_id', $messageIds)
                ->join('users', 'ticket_message_reads.user_id', '=', 'users.id')
                ->select('ticket_message_reads.ticket_message_id', 'ticket_message_reads.read_at', 'users.name as reader_name')
                ->get();
            foreach ($rows as $row) {
                $reads[$row->ticket_message_id][] = [
                    'name' => $row->reader_name,
                    'read_at' => $row->read_at,
                ];
            }
        }

        // Auto-mark all existing messages as read for the current agent
        if (!empty($messageIds)) {
            $agentId = auth()->id();
            $already = DB::table('ticket_message_reads')
                ->whereIn('ticket_message_id', $messageIds)
                ->where('user_id', $agentId)
                ->pluck('ticket_message_id')
                ->toArray();
            $toInsert = array_diff($messageIds, $already);
            foreach ($toInsert as $msgId) {
                DB::table('ticket_message_reads')->insertOrIgnore([
                    'ticket_message_id' => $msgId,
                    'user_id' => $agentId,
                    'read_at' => now(),
                ]);
            }
        }

        return view('agent.tickets.show', array_merge($data, ['messageReads' => $reads]));
    }

    public function markMessageRead(Request $request, string $uuid, int $messageId)
    {
        $this->authorizeAgentTicket($uuid);

        DB::table('ticket_message_reads')->insertOrIgnore([
            'ticket_message_id' => $messageId,
            'user_id' => auth()->id(),
            'read_at' => now(),
        ]);

        return response()->json(['ok' => true]);
    }

    public function getMessageReaders(string $uuid, int $messageId)
    {
        $this->authorizeAgentTicket($uuid);

        $readers = DB::table('ticket_message_reads')
            ->where('ticket_message_id', $messageId)
            ->join('users', 'ticket_message_reads.user_id', '=', 'users.id')
            ->select('users.name', 'ticket_message_reads.read_at')
            ->orderBy('ticket_message_reads.read_at')
            ->get();

        return response()->json($readers);
    }

    public function reply(Request $request, string $uuid)
    {
        $this->authorizeAgentTicket($uuid);
        $response = parent::reply($request, $uuid);
        if ($response instanceof \Illuminate\Http\RedirectResponse) {
            return redirect()->route('agent.tickets/show', $uuid);
        }
        return $response;
    }

    public function updateStatus(Request $request, string $uuid)
    {
        $this->authorizeAgentTicket($uuid);
        $response = parent::updateStatus($request, $uuid);

        if ($response instanceof \Illuminate\Http\RedirectResponse) {
            // Check if the new status is resolved or closed — send notifications
            $newStatus = DB::table('ticket_statuses')->where('id', $request->input('status_id'))->first();

            if ($newStatus && (in_array($newStatus->code, ['resolved', 'closed']) || $newStatus->is_closed)) {
                $ticket = DB::table('tickets')->where('uuid', $uuid)->first();

                if ($ticket) {
                    $trackUrl = route('help-center.track') . '?' . http_build_query(['ticket_no' => $ticket->ticket_no]);

                    // Email notification
                    if (!empty($ticket->contact_email)) {
                        SendMailJob::dispatch($ticket->contact_email, new \App\Mail\TicketStatusUpdated(
                            ticketNo: $ticket->ticket_no,
                            ticketSubject: $ticket->subject,
                            statusName: $newStatus->name,
                            trackUrl: $trackUrl,
                        ));
                    }

                    // SMS notification
                    if (!empty($ticket->contact_phone)) {
                        $smsMessage = "Dear Customer, we are pleased to inform you that your support ticket ({$ticket->ticket_no}) "
                                    . "has been marked as {$newStatus->name}. "
                                    . "Subject: {$ticket->subject}. "
                                    . "If you have any further concerns, you can follow up here: {$trackUrl}";
                        SendSmsJob::dispatch($ticket->contact_phone, $smsMessage);
                    }
                }
            }

            return redirect()->route('agent.tickets/show', $uuid);
        }

        return $response;
    }

    public function updatePriority(Request $request, string $uuid)
    {
        $this->authorizeAgentTicket($uuid);
        $response = parent::updatePriority($request, $uuid);
        if ($response instanceof \Illuminate\Http\RedirectResponse) {
            return redirect()->route('agent.tickets/show', $uuid);
        }
        return $response;
    }

    public function assign(Request $request, string $uuid)
    {
        $this->authorizeAgentTicket($uuid);
        $response = parent::assign($request, $uuid);
        if ($response instanceof \Illuminate\Http\RedirectResponse) {
            return redirect()->route('agent.tickets/show', $uuid);
        }
        return $response;
    }

    public function addTag(Request $request, string $uuid)
    {
        $this->authorizeAgentTicket($uuid);
        $response = parent::addTag($request, $uuid);
        if ($response instanceof \Illuminate\Http\RedirectResponse) {
            return redirect()->route('agent.tickets/show', $uuid);
        }
        return $response;
    }

    public function removeTag(string $uuid, int $tagId)
    {
        $this->authorizeAgentTicket($uuid);
        $response = parent::removeTag($uuid, $tagId);
        if ($response instanceof \Illuminate\Http\RedirectResponse) {
            return redirect()->route('agent.tickets/show', $uuid);
        }
        return $response;
    }

    public function addWatcher(Request $request, string $uuid)
    {
        $this->authorizeAgentTicket($uuid);
        $response = parent::addWatcher($request, $uuid);
        if ($response instanceof \Illuminate\Http\RedirectResponse) {
            return redirect()->route('agent.tickets/show', $uuid);
        }
        return $response;
    }

    public function removeWatcher(string $uuid, int $userId)
    {
        $this->authorizeAgentTicket($uuid);
        $response = parent::removeWatcher($uuid, $userId);
        if ($response instanceof \Illuminate\Http\RedirectResponse) {
            return redirect()->route('agent.tickets/show', $uuid);
        }
        return $response;
    }

    public function destroy(string $uuid)
    {
        $this->authorizeAgentTicket($uuid);
        $response = parent::destroy($uuid);
        if ($response instanceof \Illuminate\Http\RedirectResponse) {
            return redirect()->route('agent.tickets/index')->with('success', 'Ticket deleted.');
        }
        return $response;
    }
}
