<?php

namespace App\Http\Controllers\Customer;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class CustomerDashboardController extends Controller
{
    public function index(Request $request)
    {
        $customerId = auth()->id();

        $openStatusIds = DB::table('ticket_statuses')
            ->whereNull('deleted_at')->where('status', 1)->where('is_closed', false)
            ->pluck('id');

        $totalTickets = DB::table('tickets')
            ->whereNull('deleted_at')->where('status', 1)
            ->where('customer_id', $customerId)
            ->count();

        $totalOpen = DB::table('tickets')
            ->whereNull('deleted_at')->where('status', 1)
            ->where('customer_id', $customerId)
            ->whereIn('status_id', $openStatusIds)
            ->count();

        $pendingStatusId = DB::table('ticket_statuses')->where('code', 'pending')->value('id');
        $totalPending = $pendingStatusId
            ? DB::table('tickets')->whereNull('deleted_at')->where('status', 1)
                ->where('customer_id', $customerId)->where('status_id', $pendingStatusId)->count()
            : 0;

        $closedStatusIds = DB::table('ticket_statuses')
            ->whereNull('deleted_at')->where('status', 1)->where('is_closed', true)
            ->pluck('id');
        $totalClosed = DB::table('tickets')
            ->whereNull('deleted_at')->where('status', 1)
            ->where('customer_id', $customerId)
            ->whereIn('status_id', $closedStatusIds)
            ->count();

        $recentTickets = DB::table('tickets')
            ->whereNull('tickets.deleted_at')->where('tickets.status', 1)
            ->where('tickets.customer_id', $customerId)
            ->leftJoin('ticket_statuses', 'tickets.status_id', '=', 'ticket_statuses.id')
            ->leftJoin('ticket_priorities', 'tickets.priority_id', '=', 'ticket_priorities.id')
            ->select(
                'tickets.id', 'tickets.uuid', 'tickets.ticket_no', 'tickets.subject',
                'tickets.created_at', 'tickets.due_at',
                'ticket_statuses.name as status_name', 'ticket_statuses.color as status_color',
                'ticket_priorities.name as priority_name', 'ticket_priorities.color as priority_color',
            )
            ->orderByDesc('tickets.created_at')
            ->limit(10)
            ->get();

        return view('customer.dashboard', compact(
            'totalTickets', 'totalOpen', 'totalPending', 'totalClosed', 'recentTickets'
        ));
    }
}
