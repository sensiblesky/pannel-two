<?php

namespace App\Http\Controllers\Agent;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class AgentTicketDashboardController extends Controller
{
    public function index(Request $request)
    {
        $agentId = auth()->id();

        $base = fn() => DB::table('tickets')
            ->whereNull('tickets.deleted_at')
            ->where('tickets.status', 1)
            ->where(fn($q) => $q->where('tickets.created_by', $agentId)
                                ->orWhere('tickets.assigned_to', $agentId));

        $openStatusIds    = DB::table('ticket_statuses')->where('is_closed', false)->pluck('id');
        $resolvedStatusId = DB::table('ticket_statuses')->where('code', 'resolved')->value('id');
        $pendingStatusId  = DB::table('ticket_statuses')->where('code', 'pending')->value('id');

        // ── KPI counts ───────────────────────────────────────────────────────
        $totalTickets  = $base()->count();
        $totalOpen     = $base()->whereIn('status_id', $openStatusIds)->count();
        $totalPending  = $pendingStatusId ? $base()->where('status_id', $pendingStatusId)->count() : 0;
        $resolvedToday = $base()->whereDate('resolved_at', today())->count();
        $overdue       = $base()->whereIn('status_id', $openStatusIds)
                                ->whereNotNull('due_at')->where('due_at', '<', now())->count();
        $totalResolved = $base()->whereNotNull('resolved_at')->count();
        $resolutionRate = $totalTickets > 0 ? round(($totalResolved / $totalTickets) * 100) : 0;

        // ── Quick performance score (same algorithm as reports) ──────────────
        $slaResponseHours   = (int)(DB::table('ticket_settings')->where('key', 'sla_response_hours')->whereNull('branch_id')->value('value') ?? 0);
        $slaResolutionHours = (int)(DB::table('ticket_settings')->where('key', 'sla_resolution_hours')->whereNull('branch_id')->value('value') ?? 0);

        $avgFirstResponse = $base()->whereNotNull('first_response_at')
            ->selectRaw('ROUND(AVG(TIMESTAMPDIFF(MINUTE, tickets.created_at, tickets.first_response_at)), 0) as avg')
            ->value('avg');

        $avgResolution = $base()->whereNotNull('resolved_at')
            ->selectRaw('ROUND(AVG(TIMESTAMPDIFF(MINUTE, tickets.created_at, tickets.resolved_at)), 0) as avg')
            ->value('avg');

        $scoreResolution = ($resolutionRate / 100) * 40;
        $scoreSlaResponse = 25;
        if ($slaResponseHours > 0 && $avgFirstResponse !== null) {
            $slaMins = $slaResponseHours * 60;
            $scoreSlaResponse = max(0, 25 - round(max(0, ($avgFirstResponse - $slaMins) / $slaMins) * 25));
        }
        $scoreSlaResolution = 20;
        if ($slaResolutionHours > 0 && $avgResolution !== null) {
            $slaMins = $slaResolutionHours * 60;
            $scoreSlaResolution = max(0, 20 - round(max(0, ($avgResolution - $slaMins) / $slaMins) * 20));
        }
        $overdueRatio = $totalTickets > 0 ? ($overdue / $totalTickets) : 0;
        $scoreOverdue = round((1 - $overdueRatio) * 15);
        $performanceScore = (int) min(100, max(0, round($scoreResolution + $scoreSlaResponse + $scoreSlaResolution + $scoreOverdue)));

        $tier = match(true) {
            $performanceScore >= 90 => ['label' => 'Elite',      'color' => '#6366f1', 'text' => 'text-indigo-500'],
            $performanceScore >= 75 => ['label' => 'Expert',     'color' => '#10b981', 'text' => 'text-success'],
            $performanceScore >= 60 => ['label' => 'Proficient', 'color' => '#3b82f6', 'text' => 'text-info'],
            $performanceScore >= 40 => ['label' => 'Developing', 'color' => '#f59e0b', 'text' => 'text-warning'],
            default                 => ['label' => 'Needs Work', 'color' => '#ef4444', 'text' => 'text-error'],
        };

        // ── Streak ───────────────────────────────────────────────────────────
        $resolvedDays = $base()->whereNotNull('resolved_at')
            ->selectRaw('DATE(tickets.resolved_at) as day')
            ->groupByRaw('DATE(tickets.resolved_at)')
            ->orderByDesc('day')->pluck('day')->toArray();

        $streak = 0;
        foreach ($resolvedDays as $i => $day) {
            if ($day === now()->subDays($i)->toDateString()) { $streak++; } else { break; }
        }

        // ── Last 7 days mini-chart ────────────────────────────────────────────
        $last7 = collect();
        for ($i = 6; $i >= 0; $i--) {
            $d = now()->subDays($i)->toDateString();
            $last7->push(['date' => $d, 'label' => now()->subDays($i)->format('D')]);
        }
        $createdLast7 = $base()
            ->whereDate('tickets.created_at', '>=', now()->subDays(6)->toDateString())
            ->selectRaw('DATE(tickets.created_at) as date, COUNT(*) as count')
            ->groupByRaw('DATE(tickets.created_at)')->get()->keyBy('date');
        $resolvedLast7 = $base()->whereNotNull('resolved_at')
            ->whereDate('tickets.resolved_at', '>=', now()->subDays(6)->toDateString())
            ->selectRaw('DATE(tickets.resolved_at) as date, COUNT(*) as count')
            ->groupByRaw('DATE(tickets.resolved_at)')->get()->keyBy('date');

        $chartDays = $last7->map(fn($d) => [
            'date'     => $d['date'],
            'label'    => $d['label'],
            'created'  => $createdLast7[$d['date']]->count ?? 0,
            'resolved' => $resolvedLast7[$d['date']]->count ?? 0,
        ]);

        // ── By status / priority distributions ───────────────────────────────
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

        // ── Overdue & due soon ────────────────────────────────────────────────
        $overdueTickets = $base()
            ->whereIn('tickets.status_id', $openStatusIds)
            ->whereNotNull('tickets.due_at')->where('tickets.due_at', '<', now())
            ->leftJoin('ticket_statuses as ts', 'tickets.status_id', '=', 'ts.id')
            ->leftJoin('ticket_priorities as tp', 'tickets.priority_id', '=', 'tp.id')
            ->select('tickets.uuid', 'tickets.ticket_no', 'tickets.subject', 'tickets.due_at',
                     'ts.name as status_name', 'ts.color as status_color',
                     'tp.name as priority_name', 'tp.color as priority_color')
            ->orderBy('tickets.due_at')->limit(5)->get();

        $dueSoon = $base()
            ->whereIn('tickets.status_id', $openStatusIds)
            ->whereNotNull('tickets.due_at')
            ->where('tickets.due_at', '>=', now())
            ->where('tickets.due_at', '<=', now()->addHours(24))
            ->leftJoin('ticket_statuses as ts', 'tickets.status_id', '=', 'ts.id')
            ->leftJoin('ticket_priorities as tp', 'tickets.priority_id', '=', 'tp.id')
            ->select('tickets.uuid', 'tickets.ticket_no', 'tickets.subject', 'tickets.due_at',
                     'ts.name as status_name', 'ts.color as status_color',
                     'tp.name as priority_name', 'tp.color as priority_color')
            ->orderBy('tickets.due_at')->limit(5)->get();

        // ── Recent activity (last 8 tickets) ─────────────────────────────────
        $recentTickets = $base()
            ->leftJoin('ticket_statuses as ts', 'tickets.status_id', '=', 'ts.id')
            ->leftJoin('ticket_priorities as tp', 'tickets.priority_id', '=', 'tp.id')
            ->leftJoin('users as customers', 'tickets.customer_id', '=', 'customers.id')
            ->select('tickets.uuid', 'tickets.ticket_no', 'tickets.subject',
                     'tickets.created_at', 'tickets.resolved_at', 'tickets.due_at',
                     'ts.name as status_name', 'ts.color as status_color',
                     'tp.name as priority_name', 'tp.color as priority_color',
                     'customers.name as customer_name')
            ->orderByDesc('tickets.created_at')->limit(8)->get();

        // ── Greeting ─────────────────────────────────────────────────────────
        $hour = now()->hour;
        $greeting = $hour < 12 ? 'Good morning' : ($hour < 17 ? 'Good afternoon' : 'Good evening');

        return view('agent.tickets.dashboard', compact(
            'totalTickets', 'totalOpen', 'totalPending', 'resolvedToday', 'overdue',
            'totalResolved', 'resolutionRate',
            'performanceScore', 'tier', 'streak',
            'chartDays', 'byStatus', 'byPriority',
            'overdueTickets', 'dueSoon', 'recentTickets',
            'greeting'
        ));
    }
}
