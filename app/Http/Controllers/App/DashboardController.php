<?php

namespace App\Http\Controllers\App;

use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\DB;

class DashboardController extends Controller
{
    public function index()
    {
        // ── Users ─────────────────────────────────────────────────────────────
        $totalUsers     = DB::table('users')->whereNull('deleted_at')->count();
        $totalAgents    = DB::table('users')->where('role', 'agent')->whereNull('deleted_at')->count();
        $activeAgents   = DB::table('users')->where('role', 'agent')->where('status', true)->whereNull('deleted_at')->count();
        $totalCustomers = DB::table('users')->where('role', 'customer')->whereNull('deleted_at')->count();
        $totalBranches  = DB::table('branches')->whereNull('deleted_at')->where('status', true)->count();
        $totalDepts     = DB::table('departments')->whereNull('deleted_at')->where('status', true)->count();

        $newUsersToday  = DB::table('users')->whereNull('deleted_at')->whereDate('created_at', today())->count();
        $newUsers7d     = DB::table('users')->whereNull('deleted_at')->where('created_at', '>=', now()->subDays(7))->count();
        $newUsers30d    = DB::table('users')->whereNull('deleted_at')->where('created_at', '>=', now()->subDays(30))->count();
        $newCustomers30d = DB::table('users')->where('role', 'customer')->whereNull('deleted_at')
            ->where('created_at', '>=', now()->subDays(30))->count();

        // ── Tickets ───────────────────────────────────────────────────────────
        $openStatusIds = DB::table('ticket_statuses')->where('is_closed', false)->pluck('id');

        $totalTickets   = DB::table('tickets')->whereNull('tickets.deleted_at')->where('tickets.status', 1)->count();
        $totalOpen      = DB::table('tickets')->whereNull('tickets.deleted_at')->where('tickets.status', 1)
            ->whereIn('tickets.status_id', $openStatusIds)->count();
        $totalResolved  = DB::table('tickets')->whereNull('tickets.deleted_at')->where('tickets.status', 1)
            ->whereNotNull('tickets.resolved_at')->count();
        $resolvedToday  = DB::table('tickets')->whereNull('tickets.deleted_at')->where('tickets.status', 1)
            ->whereDate('tickets.resolved_at', today())->count();
        $overdue        = DB::table('tickets')->whereNull('tickets.deleted_at')->where('tickets.status', 1)
            ->whereIn('tickets.status_id', $openStatusIds)
            ->whereNotNull('tickets.due_at')->where('tickets.due_at', '<', now())->count();
        $unassigned     = DB::table('tickets')->whereNull('tickets.deleted_at')->where('tickets.status', 1)
            ->whereIn('tickets.status_id', $openStatusIds)->whereNull('tickets.assigned_to')->count();
        $ticketsThisWeek = DB::table('tickets')->whereNull('tickets.deleted_at')->where('tickets.status', 1)
            ->where('tickets.created_at', '>=', now()->startOfWeek())->count();

        $resolutionRate = $totalTickets > 0 ? round(($totalResolved / $totalTickets) * 100) : 0;

        // ── SLA settings ──────────────────────────────────────────────────────
        $slaResponseHours   = (int)(DB::table('ticket_settings')->where('key', 'sla_response_hours')->whereNull('branch_id')->value('value') ?? 0);
        $slaResolutionHours = (int)(DB::table('ticket_settings')->where('key', 'sla_resolution_hours')->whereNull('branch_id')->value('value') ?? 0);

        $slaResponseCompliance = null;
        if ($slaResponseHours > 0) {
            $with = DB::table('tickets')->whereNull('tickets.deleted_at')->where('tickets.status', 1)->whereNotNull('tickets.first_response_at')->count();
            $ok   = DB::table('tickets')->whereNull('tickets.deleted_at')->where('tickets.status', 1)->whereNotNull('tickets.first_response_at')
                ->whereRaw('TIMESTAMPDIFF(MINUTE, tickets.created_at, tickets.first_response_at) <= ?', [$slaResponseHours * 60])->count();
            $slaResponseCompliance = $with > 0 ? round(($ok / $with) * 100) : null;
        }
        $slaResolutionCompliance = null;
        if ($slaResolutionHours > 0) {
            $with = DB::table('tickets')->whereNull('tickets.deleted_at')->where('tickets.status', 1)->whereNotNull('tickets.resolved_at')->count();
            $ok   = DB::table('tickets')->whereNull('tickets.deleted_at')->where('tickets.status', 1)->whereNotNull('tickets.resolved_at')
                ->whereRaw('TIMESTAMPDIFF(MINUTE, tickets.created_at, tickets.resolved_at) <= ?', [$slaResolutionHours * 60])->count();
            $slaResolutionCompliance = $with > 0 ? round(($ok / $with) * 100) : null;
        }

        // ── 30-day ticket chart ───────────────────────────────────────────────
        $createdByDay = DB::table('tickets')->whereNull('tickets.deleted_at')->where('tickets.status', 1)
            ->whereDate('tickets.created_at', '>=', now()->subDays(29)->toDateString())
            ->selectRaw('DATE(tickets.created_at) as date, COUNT(*) as count')
            ->groupByRaw('DATE(tickets.created_at)')->get()->keyBy('date');

        $resolvedByDay = DB::table('tickets')->whereNull('tickets.deleted_at')->where('tickets.status', 1)
            ->whereNotNull('tickets.resolved_at')
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

        // ── Ticket distributions ──────────────────────────────────────────────
        $byStatus = DB::table('tickets')
            ->whereNull('tickets.deleted_at')->where('tickets.status', 1)
            ->join('ticket_statuses', 'tickets.status_id', '=', 'ticket_statuses.id')
            ->selectRaw('ticket_statuses.name, ticket_statuses.color, COUNT(*) as count')
            ->groupBy('ticket_statuses.name', 'ticket_statuses.color')
            ->orderByDesc('count')->get();

        $byPriority = DB::table('tickets')
            ->whereNull('tickets.deleted_at')->where('tickets.status', 1)
            ->join('ticket_priorities', 'tickets.priority_id', '=', 'ticket_priorities.id')
            ->selectRaw('ticket_priorities.name, ticket_priorities.color, COUNT(*) as count')
            ->groupBy('ticket_priorities.name', 'ticket_priorities.color')
            ->orderByDesc('count')->get();

        // ── Agent leaderboard (top 8) ─────────────────────────────────────────
        $agentLeaderboard = DB::table('tickets as t')
            ->whereNull('t.deleted_at')->where('t.status', 1)
            ->whereNotNull('t.assigned_to')
            ->join('users as u', 't.assigned_to', '=', 'u.id')
            ->selectRaw('u.id, u.name,
                COUNT(*) as total,
                SUM(CASE WHEN t.resolved_at IS NOT NULL THEN 1 ELSE 0 END) as resolved,
                ROUND(AVG(TIMESTAMPDIFF(MINUTE, t.created_at, t.first_response_at)), 0) as avg_fr,
                ROUND(AVG(TIMESTAMPDIFF(MINUTE, t.created_at, t.resolved_at)), 0) as avg_rv')
            ->groupBy('u.id', 'u.name')
            ->orderByDesc('total')
            ->limit(8)->get()
            ->map(function ($a) use ($slaResponseHours, $slaResolutionHours) {
                $a->resolution_rate = $a->total > 0 ? round(($a->resolved / $a->total) * 100) : 0;
                $rate = $a->total > 0 ? ($a->resolved / $a->total) : 0;
                $s  = $rate * 40;
                $s += ($slaResponseHours > 0 && $a->avg_fr)
                    ? max(0, 25 - max(0, ($a->avg_fr - $slaResponseHours * 60) / ($slaResponseHours * 60) * 25)) : 25;
                $s += ($slaResolutionHours > 0 && $a->avg_rv)
                    ? max(0, 20 - max(0, ($a->avg_rv - $slaResolutionHours * 60) / ($slaResolutionHours * 60) * 20)) : 20;
                $a->score = (int) min(100, max(0, round($s)));
                return $a;
            })->sortByDesc('score')->values();

        // ── Recent tickets (last 8) ───────────────────────────────────────────
        $recentTickets = DB::table('tickets')
            ->whereNull('tickets.deleted_at')->where('tickets.status', 1)
            ->leftJoin('ticket_statuses as ts', 'tickets.status_id', '=', 'ts.id')
            ->leftJoin('ticket_priorities as tp', 'tickets.priority_id', '=', 'tp.id')
            ->leftJoin('users as cu', 'tickets.customer_id', '=', 'cu.id')
            ->leftJoin('users as ag', 'tickets.assigned_to', '=', 'ag.id')
            ->select('tickets.uuid', 'tickets.ticket_no', 'tickets.subject', 'tickets.created_at',
                     'ts.name as status_name', 'ts.color as status_color',
                     'tp.name as priority_name', 'tp.color as priority_color',
                     'cu.name as customer_name', 'ag.name as agent_name')
            ->orderByDesc('tickets.created_at')->limit(8)->get();

        // ── Recent customers (last 6) ─────────────────────────────────────────
        $recentCustomers = DB::table('users')
            ->leftJoin('users_customers', 'users.id', '=', 'users_customers.user_id')
            ->where('users.role', 'customer')->whereNull('users.deleted_at')
            ->select('users.id', 'users.name', 'users.email', 'users.phone',
                     'users.created_at', 'users.status', 'users_customers.company')
            ->orderByDesc('users.created_at')->limit(6)->get();

        // ── User growth (30d by role) ─────────────────────────────────────────
        $userGrowth = DB::table('users')->whereNull('deleted_at')
            ->where('created_at', '>=', now()->subDays(29))
            ->selectRaw('DATE(created_at) as date, role, COUNT(*) as count')
            ->groupByRaw('DATE(created_at), role')
            ->get();

        $growthDays = collect();
        for ($i = 29; $i >= 0; $i--) {
            $d = now()->subDays($i)->toDateString();
            $dayData = $userGrowth->where('date', $d);
            $growthDays->push([
                'date'      => $d,
                'label'     => now()->subDays($i)->format('M j'),
                'customers' => $dayData->where('role', 'customer')->sum('count'),
                'agents'    => $dayData->where('role', 'agent')->sum('count'),
            ]);
        }

        return view('app.dashboard', compact(
            'totalUsers', 'totalAgents', 'activeAgents', 'totalCustomers',
            'totalBranches', 'totalDepts',
            'newUsersToday', 'newUsers7d', 'newUsers30d', 'newCustomers30d',
            'totalTickets', 'totalOpen', 'totalResolved', 'resolvedToday',
            'overdue', 'unassigned', 'ticketsThisWeek', 'resolutionRate',
            'slaResponseHours', 'slaResolutionHours',
            'slaResponseCompliance', 'slaResolutionCompliance',
            'chartDays', 'growthDays',
            'byStatus', 'byPriority',
            'agentLeaderboard', 'recentTickets', 'recentCustomers'
        ));
    }
}
