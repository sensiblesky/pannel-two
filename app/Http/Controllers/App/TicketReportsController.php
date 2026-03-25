<?php

namespace App\Http\Controllers\App;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class TicketReportsController extends Controller
{
    public function index(Request $request)
    {
        $from = $request->input('date_from', now()->subDays(30)->toDateString());
        $to = $request->input('date_to', now()->toDateString());

        // Volume over time (daily)
        $volumeByDay = DB::table('tickets')
            ->whereNull('deleted_at')->where('status', 1)
            ->whereDate('created_at', '>=', $from)
            ->whereDate('created_at', '<=', $to)
            ->selectRaw('DATE(created_at) as date, COUNT(*) as count')
            ->groupByRaw('DATE(created_at)')
            ->orderBy('date')
            ->get();

        // By status
        $byStatus = DB::table('tickets')
            ->whereNull('tickets.deleted_at')->where('tickets.status', 1)
            ->whereDate('tickets.created_at', '>=', $from)
            ->whereDate('tickets.created_at', '<=', $to)
            ->leftJoin('ticket_statuses', 'tickets.status_id', '=', 'ticket_statuses.id')
            ->selectRaw('ticket_statuses.name, ticket_statuses.color, COUNT(*) as count')
            ->groupBy('ticket_statuses.name', 'ticket_statuses.color')
            ->get();

        // By priority
        $byPriority = DB::table('tickets')
            ->whereNull('tickets.deleted_at')->where('tickets.status', 1)
            ->whereDate('tickets.created_at', '>=', $from)
            ->whereDate('tickets.created_at', '<=', $to)
            ->leftJoin('ticket_priorities', 'tickets.priority_id', '=', 'ticket_priorities.id')
            ->selectRaw('ticket_priorities.name, ticket_priorities.color, COUNT(*) as count')
            ->groupBy('ticket_priorities.name', 'ticket_priorities.color')
            ->get();

        // By category
        $byCategory = DB::table('tickets')
            ->whereNull('tickets.deleted_at')->where('tickets.status', 1)
            ->whereDate('tickets.created_at', '>=', $from)
            ->whereDate('tickets.created_at', '<=', $to)
            ->leftJoin('ticket_categories', 'tickets.category_id', '=', 'ticket_categories.id')
            ->selectRaw('COALESCE(ticket_categories.name, "Uncategorized") as name, COUNT(*) as count')
            ->groupBy('ticket_categories.name')
            ->get();

        // By source
        $bySource = DB::table('tickets')
            ->whereNull('deleted_at')->where('status', 1)
            ->whereDate('created_at', '>=', $from)
            ->whereDate('created_at', '<=', $to)
            ->selectRaw('source, COUNT(*) as count')
            ->groupBy('source')
            ->get();

        // Agent performance
        $agentPerformance = DB::table('tickets')
            ->whereNull('tickets.deleted_at')->where('tickets.status', 1)
            ->whereDate('tickets.created_at', '>=', $from)
            ->whereDate('tickets.created_at', '<=', $to)
            ->whereNotNull('tickets.assigned_to')
            ->leftJoin('users', 'tickets.assigned_to', '=', 'users.id')
            ->selectRaw('users.name as agent_name, COUNT(*) as total,
                SUM(CASE WHEN tickets.resolved_at IS NOT NULL THEN 1 ELSE 0 END) as resolved,
                ROUND(AVG(TIMESTAMPDIFF(HOUR, tickets.created_at, tickets.first_response_at)), 1) as avg_first_response_hours,
                ROUND(AVG(TIMESTAMPDIFF(HOUR, tickets.created_at, tickets.resolved_at)), 1) as avg_resolution_hours')
            ->groupBy('users.name')
            ->orderByDesc('total')
            ->get();

        // SLA metrics
        $slaResponseHours = DB::table('ticket_settings')->where('key', 'sla_response_hours')->whereNull('branch_id')->value('value') ?? 0;
        $slaResolutionHours = DB::table('ticket_settings')->where('key', 'sla_resolution_hours')->whereNull('branch_id')->value('value') ?? 0;

        $slaMetrics = [
            'total' => DB::table('tickets')->whereNull('deleted_at')->where('status', 1)
                ->whereDate('created_at', '>=', $from)->whereDate('created_at', '<=', $to)->count(),
            'avg_first_response_hours' => DB::table('tickets')->whereNull('deleted_at')->where('status', 1)
                ->whereNotNull('first_response_at')
                ->whereDate('created_at', '>=', $from)->whereDate('created_at', '<=', $to)
                ->selectRaw('ROUND(AVG(TIMESTAMPDIFF(HOUR, created_at, first_response_at)), 1) as avg')
                ->value('avg'),
            'avg_resolution_hours' => DB::table('tickets')->whereNull('deleted_at')->where('status', 1)
                ->whereNotNull('resolved_at')
                ->whereDate('created_at', '>=', $from)->whereDate('created_at', '<=', $to)
                ->selectRaw('ROUND(AVG(TIMESTAMPDIFF(HOUR, created_at, resolved_at)), 1) as avg')
                ->value('avg'),
        ];

        return view('app.tickets.reports', compact(
            'from', 'to', 'volumeByDay', 'byStatus', 'byPriority', 'byCategory',
            'bySource', 'agentPerformance', 'slaMetrics', 'slaResponseHours', 'slaResolutionHours'
        ));
    }
}
