<?php

namespace App\Http\Controllers\Agent;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class AgentTicketReportsController extends Controller
{
    public function index(Request $request)
    {
        $agentId = auth()->id();
        $from = $request->input('date_from', now()->subDays(30)->toDateString());
        $to   = $request->input('date_to', now()->toDateString());

        $slaResponseHours   = (int) (DB::table('ticket_settings')->where('key', 'sla_response_hours')->whereNull('branch_id')->value('value') ?? 0);
        $slaResolutionHours = (int) (DB::table('ticket_settings')->where('key', 'sla_resolution_hours')->whereNull('branch_id')->value('value') ?? 0);

        // Base scope: tickets assigned to or created by this agent
        $myBase = fn() => DB::table('tickets')
            ->whereNull('tickets.deleted_at')
            ->where('tickets.status', 1)
            ->where(fn($q) => $q->where('tickets.created_by', $agentId)->orWhere('tickets.assigned_to', $agentId));

        $myRange = fn() => $myBase()
            ->whereDate('tickets.created_at', '>=', $from)
            ->whereDate('tickets.created_at', '<=', $to);

        // ── KPIs ──────────────────────────────────────────────────────────────
        $total    = $myRange()->count();
        $resolved = $myRange()->whereNotNull('tickets.resolved_at')->count();

        $openStatusIds = DB::table('ticket_statuses')->where('is_closed', false)->pluck('id');
        $open    = $myRange()->whereIn('tickets.status_id', $openStatusIds)->count();
        $overdue = $myRange()->whereIn('tickets.status_id', $openStatusIds)
                             ->whereNotNull('tickets.due_at')->where('tickets.due_at', '<', now())->count();

        $avgFirstResponse = $myRange()->whereNotNull('tickets.first_response_at')
            ->selectRaw('ROUND(AVG(TIMESTAMPDIFF(MINUTE, tickets.created_at, tickets.first_response_at)), 0) as avg')
            ->value('avg');

        $avgResolution = $myRange()->whereNotNull('tickets.resolved_at')
            ->selectRaw('ROUND(AVG(TIMESTAMPDIFF(MINUTE, tickets.created_at, tickets.resolved_at)), 0) as avg')
            ->value('avg');

        $resolutionRate = $total > 0 ? round(($resolved / $total) * 100) : 0;

        // ── Streak (consecutive days with resolved ticket) ────────────────────
        $resolvedDays = $myBase()->whereNotNull('tickets.resolved_at')
            ->selectRaw('DATE(tickets.resolved_at) as day')
            ->groupByRaw('DATE(tickets.resolved_at)')
            ->orderByDesc('day')->pluck('day')->toArray();

        $streak = 0;
        foreach ($resolvedDays as $i => $day) {
            $expected = now()->subDays($i)->toDateString();
            if ($day === $expected) { $streak++; } else { break; }
        }

        // ── Performance Score (0–100) ─────────────────────────────────────────
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

        $overdueRatio = $total > 0 ? ($overdue / $total) : 0;
        $scoreOverdue = round((1 - $overdueRatio) * 15);

        $performanceScore = (int) min(100, max(0, round($scoreResolution + $scoreSlaResponse + $scoreSlaResolution + $scoreOverdue)));

        $tier = match(true) {
            $performanceScore >= 90 => ['label' => 'Elite',      'color' => '#6366f1', 'ring' => 'ring-indigo-400', 'text' => 'text-indigo-500'],
            $performanceScore >= 75 => ['label' => 'Expert',     'color' => '#10b981', 'ring' => 'ring-success',    'text' => 'text-success'],
            $performanceScore >= 60 => ['label' => 'Proficient', 'color' => '#3b82f6', 'ring' => 'ring-info',       'text' => 'text-info'],
            $performanceScore >= 40 => ['label' => 'Developing', 'color' => '#f59e0b', 'ring' => 'ring-warning',    'text' => 'text-warning'],
            default                 => ['label' => 'Needs Work', 'color' => '#ef4444', 'ring' => 'ring-error',      'text' => 'text-error'],
        };

        // ── Leaderboard rank (score-based, anonymous) ─────────────────────────
        $allScores = DB::table('tickets as t')
            ->whereNull('t.deleted_at')->where('t.status', 1)
            ->whereDate('t.created_at', '>=', $from)->whereDate('t.created_at', '<=', $to)
            ->whereNotNull('t.assigned_to')
            ->selectRaw('
                t.assigned_to as agent_id,
                COUNT(*) as total,
                SUM(CASE WHEN t.resolved_at IS NOT NULL THEN 1 ELSE 0 END) as res,
                ROUND(AVG(TIMESTAMPDIFF(MINUTE, t.created_at, t.first_response_at)), 0) as avg_fr,
                ROUND(AVG(TIMESTAMPDIFF(MINUTE, t.created_at, t.resolved_at)), 0) as avg_rv
            ')
            ->groupBy('t.assigned_to')
            ->get()
            ->map(function ($a) use ($slaResponseHours, $slaResolutionHours) {
                $rate = $a->total > 0 ? ($a->res / $a->total) : 0;
                $s  = $rate * 40;
                $s += ($slaResponseHours > 0 && $a->avg_fr)
                    ? max(0, 25 - max(0, ($a->avg_fr - $slaResponseHours * 60) / ($slaResponseHours * 60) * 25))
                    : 25;
                $s += ($slaResolutionHours > 0 && $a->avg_rv)
                    ? max(0, 20 - max(0, ($a->avg_rv - $slaResolutionHours * 60) / ($slaResolutionHours * 60) * 20))
                    : 20;
                $a->score = (int) min(100, max(0, round($s)));
                return $a;
            })
            ->sortByDesc('score')->values();

        $totalAgents   = $allScores->count();
        $rankIdx       = $allScores->search(fn($a) => $a->agent_id == $agentId);
        $myRank        = $rankIdx !== false ? $rankIdx + 1 : $totalAgents;
        $teamAvgScore  = $totalAgents > 0 ? (int) round($allScores->avg('score')) : 0;
        $teamAvgResolutionRate = $totalAgents > 0
            ? (int) round($allScores->avg(fn($a) => $a->total > 0 ? ($a->res / $a->total) * 100 : 0))
            : 0;

        // ── Volume chart data (created vs resolved per day) ───────────────────
        $createdByDay  = $myRange()->selectRaw('DATE(tickets.created_at) as date, COUNT(*) as count')
                                    ->groupByRaw('DATE(tickets.created_at)')->orderBy('date')->get()->keyBy('date');
        $resolvedByDay = $myBase()->whereNotNull('tickets.resolved_at')
                                    ->whereDate('tickets.resolved_at', '>=', $from)->whereDate('tickets.resolved_at', '<=', $to)
                                    ->selectRaw('DATE(tickets.resolved_at) as date, COUNT(*) as count')
                                    ->groupByRaw('DATE(tickets.resolved_at)')->orderBy('date')->get()->keyBy('date');

        $allDates = collect();
        $cursor   = \Carbon\Carbon::parse($from);
        $endDate  = \Carbon\Carbon::parse($to);
        while ($cursor->lte($endDate)) {
            $d = $cursor->toDateString();
            $allDates->push(['date' => $d, 'created' => $createdByDay[$d]?->count ?? 0, 'resolved' => $resolvedByDay[$d]?->count ?? 0]);
            $cursor->addDay();
        }

        // ── Distributions ─────────────────────────────────────────────────────
        $byStatus = $myRange()->leftJoin('ticket_statuses', 'tickets.status_id', '=', 'ticket_statuses.id')
            ->selectRaw('ticket_statuses.name, ticket_statuses.color, COUNT(*) as count')
            ->groupBy('ticket_statuses.name', 'ticket_statuses.color')->get();

        $byPriority = $myRange()->leftJoin('ticket_priorities', 'tickets.priority_id', '=', 'ticket_priorities.id')
            ->selectRaw('ticket_priorities.name, ticket_priorities.color, COUNT(*) as count')
            ->groupBy('ticket_priorities.name', 'ticket_priorities.color')->get();

        $byCategory = $myRange()->leftJoin('ticket_categories', 'tickets.category_id', '=', 'ticket_categories.id')
            ->selectRaw('COALESCE(ticket_categories.name, "Uncategorized") as name, COUNT(*) as count')
            ->groupBy('ticket_categories.name')->orderByDesc('count')->limit(6)->get();

        // ── Recent tickets ────────────────────────────────────────────────────
        $recentTickets = $myBase()
            ->leftJoin('ticket_statuses as ts', 'tickets.status_id', '=', 'ts.id')
            ->leftJoin('ticket_priorities as tp', 'tickets.priority_id', '=', 'tp.id')
            ->select('tickets.uuid', 'tickets.ticket_no', 'tickets.subject', 'tickets.created_at',
                     'tickets.resolved_at', 'ts.name as status_name', 'ts.color as status_color',
                     'tp.name as priority_name', 'tp.color as priority_color')
            ->orderByDesc('tickets.created_at')->limit(8)->get();

        // ── Format helper ─────────────────────────────────────────────────────
        $formatMins = function (?float $mins): string {
            if ($mins === null) return '—';
            if ($mins < 60)    return round($mins) . 'm';
            if ($mins < 1440)  return round($mins / 60, 1) . 'h';
            return round($mins / 1440, 1) . 'd';
        };

        return view('agent.tickets.reports', compact(
            'from', 'to',
            'total', 'resolved', 'open', 'overdue',
            'resolutionRate', 'avgFirstResponse', 'avgResolution',
            'performanceScore', 'tier', 'streak',
            'myRank', 'totalAgents', 'teamAvgScore', 'teamAvgResolutionRate',
            'allDates', 'byStatus', 'byPriority', 'byCategory',
            'recentTickets', 'slaResponseHours', 'slaResolutionHours', 'formatMins',
            'scoreResolution', 'scoreSlaResponse', 'scoreSlaResolution', 'scoreOverdue'
        ));
    }
}
