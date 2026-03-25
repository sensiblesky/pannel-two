<?php

namespace App\Http\Controllers\App;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class TicketDashboardController extends Controller
{
    public function index(Request $request)
    {
        $branchId = $request->input('branch_id');

        $base = fn() => DB::table('tickets')
            ->whereNull('tickets.deleted_at')
            ->where('tickets.status', 1)
            ->when($branchId, fn($q) => $q->where('tickets.branch_id', $branchId));

        $openStatusIds = DB::table('ticket_statuses')
            ->where('is_closed', false)->pluck('id');
        $closedStatusIds = DB::table('ticket_statuses')
            ->where('is_closed', true)->pluck('id');
        $pendingStatusId  = DB::table('ticket_statuses')->where('code', 'pending')->value('id');
        $resolvedStatusId = DB::table('ticket_statuses')->where('code', 'resolved')->value('id');

        // ── KPI counts ───────────────────────────────────────────────────────
        $totalTickets  = $base()->count();
        $totalOpen     = $base()->whereIn('tickets.status_id', $openStatusIds)->count();
        $totalPending  = $pendingStatusId ? $base()->where('tickets.status_id', $pendingStatusId)->count() : 0;
        $resolvedToday = $base()->whereDate('tickets.resolved_at', today())->count();
        $overdue       = $base()->whereIn('tickets.status_id', $openStatusIds)
                                ->whereNotNull('tickets.due_at')->where('tickets.due_at', '<', now())->count();
        $unassigned    = $base()->whereIn('tickets.status_id', $openStatusIds)->whereNull('tickets.assigned_to')->count();
        $totalResolved = $base()->whereNotNull('tickets.resolved_at')->count();
        $resolutionRate = $totalTickets > 0 ? round(($totalResolved / $totalTickets) * 100) : 0;

        // ── SLA settings ─────────────────────────────────────────────────────
        $slaResponseHours   = (int)(DB::table('ticket_settings')->where('key', 'sla_response_hours')->whereNull('branch_id')->value('value') ?? 0);
        $slaResolutionHours = (int)(DB::table('ticket_settings')->where('key', 'sla_resolution_hours')->whereNull('branch_id')->value('value') ?? 0);

        // ── Avg response & resolution ────────────────────────────────────────
        $avgFirstResponse = $base()->whereNotNull('tickets.first_response_at')
            ->selectRaw('ROUND(AVG(TIMESTAMPDIFF(MINUTE, tickets.created_at, tickets.first_response_at)), 0) as avg')
            ->value('avg');

        $avgResolution = $base()->whereNotNull('tickets.resolved_at')
            ->selectRaw('ROUND(AVG(TIMESTAMPDIFF(MINUTE, tickets.created_at, tickets.resolved_at)), 0) as avg')
            ->value('avg');

        // ── SLA compliance rates ─────────────────────────────────────────────
        $slaResponseCompliance = null;
        if ($slaResponseHours > 0) {
            $withFirstResponse = $base()->whereNotNull('tickets.first_response_at')->count();
            $withinSla = $base()->whereNotNull('tickets.first_response_at')
                ->whereRaw('TIMESTAMPDIFF(MINUTE, tickets.created_at, tickets.first_response_at) <= ?', [$slaResponseHours * 60])
                ->count();
            $slaResponseCompliance = $withFirstResponse > 0 ? round(($withinSla / $withFirstResponse) * 100) : null;
        }

        $slaResolutionCompliance = null;
        if ($slaResolutionHours > 0) {
            $withResolved = $base()->whereNotNull('tickets.resolved_at')->count();
            $withinSla = $base()->whereNotNull('tickets.resolved_at')
                ->whereRaw('TIMESTAMPDIFF(MINUTE, tickets.created_at, tickets.resolved_at) <= ?', [$slaResolutionHours * 60])
                ->count();
            $slaResolutionCompliance = $withResolved > 0 ? round(($withinSla / $withResolved) * 100) : null;
        }

        // ── Active agents count ───────────────────────────────────────────────
        $totalAgents = DB::table('users')->whereNull('deleted_at')->where('status', true)
            ->where('role', 'agent')->count();

        // ── 30-day volume chart ───────────────────────────────────────────────
        $createdByDay = $base()
            ->whereDate('tickets.created_at', '>=', now()->subDays(29)->toDateString())
            ->selectRaw('DATE(tickets.created_at) as date, COUNT(*) as count')
            ->groupByRaw('DATE(tickets.created_at)')->get()->keyBy('date');

        $resolvedByDay = $base()->whereNotNull('tickets.resolved_at')
            ->whereDate('tickets.resolved_at', '>=', now()->subDays(29)->toDateString())
            ->selectRaw('DATE(tickets.resolved_at) as date, COUNT(*) as count')
            ->groupByRaw('DATE(tickets.resolved_at)')->get()->keyBy('date');

        $chartDays = collect();
        for ($i = 29; $i >= 0; $i--) {
            $d = now()->subDays($i)->toDateString();
            $chartDays->push([
                'date'     => $d,
                'label'    => now()->subDays($i)->format('M j'),
                'created'  => $createdByDay[$d]->count ?? 0,
                'resolved' => $resolvedByDay[$d]->count ?? 0,
            ]);
        }

        // ── Agent leaderboard (top 8 by ticket count + resolution rate) ──────
        $agentLeaderboard = DB::table('tickets as t')
            ->whereNull('t.deleted_at')->where('t.status', 1)
            ->when($branchId, fn($q) => $q->where('t.branch_id', $branchId))
            ->whereNotNull('t.assigned_to')
            ->join('users as u', 't.assigned_to', '=', 'u.id')
            ->selectRaw('
                u.id, u.name,
                COUNT(*) as total,
                SUM(CASE WHEN t.resolved_at IS NOT NULL THEN 1 ELSE 0 END) as resolved,
                ROUND(AVG(TIMESTAMPDIFF(MINUTE, t.created_at, t.first_response_at)), 0) as avg_fr,
                ROUND(AVG(TIMESTAMPDIFF(MINUTE, t.created_at, t.resolved_at)), 0) as avg_rv
            ')
            ->groupBy('u.id', 'u.name')
            ->orderByDesc('total')
            ->limit(8)->get()
            ->map(function ($a) use ($slaResponseHours, $slaResolutionHours) {
                $a->resolution_rate = $a->total > 0 ? round(($a->resolved / $a->total) * 100) : 0;
                $rate = $a->total > 0 ? ($a->resolved / $a->total) : 0;
                $s  = $rate * 40;
                $s += ($slaResponseHours > 0 && $a->avg_fr)
                    ? max(0, 25 - max(0, ($a->avg_fr - $slaResponseHours * 60) / ($slaResponseHours * 60) * 25))
                    : 25;
                $s += ($slaResolutionHours > 0 && $a->avg_rv)
                    ? max(0, 20 - max(0, ($a->avg_rv - $slaResolutionHours * 60) / ($slaResolutionHours * 60) * 20))
                    : 20;
                $a->score = (int) min(100, max(0, round($s)));
                return $a;
            })->sortByDesc('score')->values();

        // ── Distributions ─────────────────────────────────────────────────────
        $byStatus = $base()
            ->leftJoin('ticket_statuses', 'tickets.status_id', '=', 'ticket_statuses.id')
            ->selectRaw('ticket_statuses.name, ticket_statuses.color, COUNT(*) as count')
            ->groupBy('ticket_statuses.name', 'ticket_statuses.color')
            ->orderByDesc('count')->get();

        $byPriority = $base()
            ->leftJoin('ticket_priorities', 'tickets.priority_id', '=', 'ticket_priorities.id')
            ->selectRaw('ticket_priorities.name, ticket_priorities.color, COUNT(*) as count')
            ->groupBy('ticket_priorities.name', 'ticket_priorities.color')
            ->orderByDesc('count')->get();

        $byCategory = $base()
            ->leftJoin('ticket_categories', 'tickets.category_id', '=', 'ticket_categories.id')
            ->selectRaw('COALESCE(ticket_categories.name, "Uncategorized") as name, COUNT(*) as count')
            ->groupBy('ticket_categories.name')
            ->orderByDesc('count')->limit(7)->get();

        // ── Overdue & unassigned lists ────────────────────────────────────────
        $overdueTickets = $base()
            ->whereIn('tickets.status_id', $openStatusIds)
            ->whereNotNull('tickets.due_at')->where('tickets.due_at', '<', now())
            ->leftJoin('ticket_statuses as ts', 'tickets.status_id', '=', 'ts.id')
            ->leftJoin('ticket_priorities as tp', 'tickets.priority_id', '=', 'tp.id')
            ->leftJoin('users as ag', 'tickets.assigned_to', '=', 'ag.id')
            ->select('tickets.uuid', 'tickets.ticket_no', 'tickets.subject', 'tickets.due_at',
                     'ts.name as status_name', 'ts.color as status_color',
                     'tp.name as priority_name', 'tp.color as priority_color',
                     'ag.name as agent_name')
            ->orderBy('tickets.due_at')->limit(6)->get();

        $unassignedTickets = $base()
            ->whereIn('tickets.status_id', $openStatusIds)
            ->whereNull('tickets.assigned_to')
            ->leftJoin('ticket_statuses as ts', 'tickets.status_id', '=', 'ts.id')
            ->leftJoin('ticket_priorities as tp', 'tickets.priority_id', '=', 'tp.id')
            ->leftJoin('users as cu', 'tickets.customer_id', '=', 'cu.id')
            ->select('tickets.uuid', 'tickets.ticket_no', 'tickets.subject', 'tickets.created_at',
                     'ts.name as status_name', 'ts.color as status_color',
                     'tp.name as priority_name', 'tp.color as priority_color',
                     'cu.name as customer_name')
            ->orderBy('tickets.created_at')->limit(6)->get();

        // ── Recent tickets ─────────────────────────────────────────────────────
        $recentTickets = $base()
            ->leftJoin('ticket_statuses as ts', 'tickets.status_id', '=', 'ts.id')
            ->leftJoin('ticket_priorities as tp', 'tickets.priority_id', '=', 'tp.id')
            ->leftJoin('users as customers', 'tickets.customer_id', '=', 'customers.id')
            ->leftJoin('users as agents', 'tickets.assigned_to', '=', 'agents.id')
            ->select('tickets.uuid', 'tickets.ticket_no', 'tickets.subject', 'tickets.created_at',
                     'ts.name as status_name', 'ts.color as status_color',
                     'tp.name as priority_name', 'tp.color as priority_color',
                     'customers.name as customer_name', 'agents.name as agent_name')
            ->orderByDesc('tickets.created_at')->limit(10)->get();

        // ── Format helper ─────────────────────────────────────────────────────
        $formatMins = function (?float $mins): string {
            if ($mins === null) return '—';
            if ($mins < 60)    return round($mins) . 'm';
            if ($mins < 1440)  return round($mins / 60, 1) . 'h';
            return round($mins / 1440, 1) . 'd';
        };

        // ── Branches for filter ───────────────────────────────────────────────
        $branches = DB::table('branches')->whereNull('deleted_at')->where('status', true)
            ->orderBy('name')->get(['id', 'name']);

        return view('app.tickets.dashboard', compact(
            'totalTickets', 'totalOpen', 'totalPending', 'resolvedToday',
            'overdue', 'unassigned', 'totalResolved', 'resolutionRate',
            'avgFirstResponse', 'avgResolution', 'formatMins',
            'slaResponseHours', 'slaResolutionHours',
            'slaResponseCompliance', 'slaResolutionCompliance',
            'totalAgents', 'chartDays', 'agentLeaderboard',
            'byStatus', 'byPriority', 'byCategory',
            'overdueTickets', 'unassignedTickets', 'recentTickets',
            'branches', 'branchId'
        ));
    }
}
