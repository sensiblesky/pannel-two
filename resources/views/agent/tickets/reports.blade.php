<x-app-layout title="My Performance" is-sidebar-open="true" is-header-blur="true">

    <main class="main-content w-full px-[var(--margin-x)] pb-8">
        {{-- Header --}}
        <div class="flex flex-col gap-4 py-5 lg:py-6 sm:flex-row sm:items-center sm:justify-between">
            <div class="flex items-center space-x-4">
                <h2 class="text-xl font-medium text-slate-800 dark:text-navy-50 lg:text-2xl">My Performance</h2>
                <div class="hidden h-full py-1 sm:flex"><div class="h-full w-px bg-slate-300 dark:bg-navy-600"></div></div>
                <ul class="hidden flex-wrap items-center space-x-2 sm:flex">
                    <li class="flex items-center space-x-2">
                        <a class="text-primary transition-colors hover:text-primary-focus dark:text-accent-light dark:hover:text-accent" href="{{ route('agent.tickets/dashboard') }}">Tickets</a>
                        <svg xmlns="http://www.w3.org/2000/svg" class="size-4 text-slate-400" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/></svg>
                    </li>
                    <li><span class="text-slate-800 dark:text-navy-100">Reports</span></li>
                </ul>
            </div>

            {{-- Date range filter --}}
            <form method="GET" class="flex items-center gap-2 flex-wrap">
                <input type="date" name="date_from" value="{{ $from }}"
                       class="form-input h-9 rounded-lg border border-slate-300 bg-white px-3 text-xs dark:border-navy-450 dark:bg-navy-700 dark:text-navy-100">
                <span class="text-slate-500 text-xs">to</span>
                <input type="date" name="date_to" value="{{ $to }}"
                       class="form-input h-9 rounded-lg border border-slate-300 bg-white px-3 text-xs dark:border-navy-450 dark:bg-navy-700 dark:text-navy-100">
                <button type="submit" class="btn h-9 rounded-lg bg-primary px-4 text-xs font-medium text-white hover:bg-primary-focus">Apply</button>
            </form>
        </div>

        {{-- ── Row 1: Score + Rank + Streak + Resolution Rate ──────────────────── --}}
        <div class="grid grid-cols-1 gap-4 sm:grid-cols-2 lg:grid-cols-4">

            {{-- Performance Score Gauge --}}
            <div class="card rounded-2xl bg-white p-5 shadow dark:bg-navy-700 flex flex-col items-center justify-center gap-2">
                <p class="text-xs font-semibold uppercase tracking-wider text-slate-400 dark:text-navy-300">Performance Score</p>
                <div class="relative flex items-center justify-center" style="width:120px;height:120px;">
                    <svg viewBox="0 0 36 36" class="w-full h-full -rotate-90">
                        <circle cx="18" cy="18" r="15.9" fill="none" stroke="#e4e4e7" stroke-width="3"/>
                        <circle cx="18" cy="18" r="15.9" fill="none"
                                stroke="{{ $tier['color'] }}" stroke-width="3"
                                stroke-dasharray="{{ round(($performanceScore/100)*100, 1) }} 100"
                                stroke-linecap="round"/>
                    </svg>
                    <div class="absolute flex flex-col items-center">
                        <span class="text-2xl font-bold text-slate-800 dark:text-navy-50">{{ $performanceScore }}</span>
                        <span class="text-[10px] font-medium" style="color:{{ $tier['color'] }}">{{ $tier['label'] }}</span>
                    </div>
                </div>
                <div class="flex items-center gap-1.5 rounded-full px-3 py-1 text-xs font-semibold {{ $tier['ring'] }} ring-1 {{ $tier['text'] }}">
                    @if($performanceScore >= 90) 🏆
                    @elseif($performanceScore >= 75) ⭐
                    @elseif($performanceScore >= 60) 💪
                    @elseif($performanceScore >= 40) 📈
                    @else 🎯 @endif
                    {{ $tier['label'] }} Tier
                </div>
            </div>

            {{-- Rank --}}
            <div class="card rounded-2xl bg-white p-5 shadow dark:bg-navy-700 flex flex-col items-center justify-center gap-2">
                <p class="text-xs font-semibold uppercase tracking-wider text-slate-400 dark:text-navy-300">Team Rank</p>
                <div class="flex items-end gap-1">
                    <span class="text-5xl font-bold text-slate-800 dark:text-navy-50">#{{ $myRank }}</span>
                    <span class="mb-2 text-sm text-slate-400 dark:text-navy-300">/ {{ $totalAgents }}</span>
                </div>
                <p class="text-xs text-slate-500 dark:text-navy-300">
                    @if($totalAgents > 0)
                        Top {{ $totalAgents > 1 ? round(($myRank / $totalAgents) * 100) : 100 }}% of agents
                    @else
                        No team data
                    @endif
                </p>
                <div class="w-full bg-slate-100 dark:bg-navy-600 rounded-full h-1.5 mt-1">
                    @php $rankPct = $totalAgents > 1 ? round((($totalAgents - $myRank + 1) / $totalAgents) * 100) : 100; @endphp
                    <div class="h-1.5 rounded-full bg-primary" style="width:{{ $rankPct }}%"></div>
                </div>
            </div>

            {{-- Streak --}}
            <div class="card rounded-2xl bg-white p-5 shadow dark:bg-navy-700 flex flex-col items-center justify-center gap-2">
                <p class="text-xs font-semibold uppercase tracking-wider text-slate-400 dark:text-navy-300">Resolve Streak</p>
                <div class="flex items-center gap-2">
                    <span class="text-4xl">🔥</span>
                    <span class="text-5xl font-bold text-slate-800 dark:text-navy-50">{{ $streak }}</span>
                </div>
                <p class="text-xs text-slate-500 dark:text-navy-300">
                    {{ $streak === 1 ? 'day in a row' : 'consecutive days' }}
                </p>
                @if($streak >= 7)
                    <span class="rounded-full bg-amber-100 px-2.5 py-0.5 text-[11px] font-semibold text-amber-700">On Fire!</span>
                @elseif($streak >= 3)
                    <span class="rounded-full bg-orange-100 px-2.5 py-0.5 text-[11px] font-semibold text-orange-600">Hot Streak</span>
                @elseif($streak > 0)
                    <span class="rounded-full bg-slate-100 px-2.5 py-0.5 text-[11px] font-semibold text-slate-500 dark:bg-navy-600 dark:text-navy-200">Keep Going</span>
                @else
                    <span class="rounded-full bg-slate-100 px-2.5 py-0.5 text-[11px] font-semibold text-slate-400 dark:bg-navy-600 dark:text-navy-300">No streak yet</span>
                @endif
            </div>

            {{-- Resolution Rate --}}
            <div class="card rounded-2xl bg-white p-5 shadow dark:bg-navy-700 flex flex-col items-center justify-center gap-2">
                <p class="text-xs font-semibold uppercase tracking-wider text-slate-400 dark:text-navy-300">Resolution Rate</p>
                <span class="text-5xl font-bold {{ $resolutionRate >= 75 ? 'text-success' : ($resolutionRate >= 50 ? 'text-warning' : 'text-error') }}">
                    {{ $resolutionRate }}%
                </span>
                <div class="w-full bg-slate-100 dark:bg-navy-600 rounded-full h-2">
                    <div class="h-2 rounded-full {{ $resolutionRate >= 75 ? 'bg-success' : ($resolutionRate >= 50 ? 'bg-warning' : 'bg-error') }}"
                         style="width:{{ $resolutionRate }}%"></div>
                </div>
                <p class="text-xs text-slate-500 dark:text-navy-300">
                    {{ $resolved }} of {{ $total }} tickets resolved
                </p>
            </div>
        </div>

        {{-- ── Row 2: KPI Cards ─────────────────────────────────────────────────── --}}
        <div class="mt-4 grid grid-cols-2 gap-4 sm:grid-cols-4">
            @php
                $kpis = [
                    ['label' => 'Total Assigned',  'value' => $total,    'icon' => 'M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2', 'color' => 'text-primary bg-primary/10'],
                    ['label' => 'Resolved',         'value' => $resolved, 'icon' => 'M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0', 'color' => 'text-success bg-success/10'],
                    ['label' => 'Open',             'value' => $open,     'icon' => 'M8 12h.01M12 12h.01M16 12h.01M21 12c0 4.418-4.03 8-9 8a9.863 9.863 0 01-4.255-.949L3 20l1.395-3.72C3.512 15.042 3 13.574 3 12c0-4.418 4.03-8 9-8s9 3.582 9 8z', 'color' => 'text-info bg-info/10'],
                    ['label' => 'Overdue',          'value' => $overdue,  'icon' => 'M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0', 'color' => 'text-error bg-error/10'],
                ];
            @endphp
            @foreach($kpis as $kpi)
            <div class="card rounded-2xl bg-white p-5 shadow dark:bg-navy-700">
                <div class="flex items-center justify-between">
                    <div>
                        <p class="text-xs text-slate-400 dark:text-navy-300">{{ $kpi['label'] }}</p>
                        <p class="mt-1 text-3xl font-bold text-slate-800 dark:text-navy-50">{{ $kpi['value'] }}</p>
                    </div>
                    <div class="flex size-11 items-center justify-center rounded-full {{ $kpi['color'] }}">
                        <svg xmlns="http://www.w3.org/2000/svg" class="size-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.5">
                            <path stroke-linecap="round" stroke-linejoin="round" d="{{ $kpi['icon'] }}"/>
                        </svg>
                    </div>
                </div>
            </div>
            @endforeach
        </div>

        {{-- ── Row 3: Score Breakdown + SLA Metrics ────────────────────────────── --}}
        <div class="mt-4 grid grid-cols-1 gap-4 lg:grid-cols-2">

            {{-- Score Breakdown --}}
            <div class="card rounded-2xl bg-white p-5 shadow dark:bg-navy-700">
                <h3 class="mb-4 text-sm font-semibold text-slate-700 dark:text-navy-100">Score Breakdown</h3>
                @php
                    $breakdown = [
                        ['label' => 'Resolution Rate',    'score' => $scoreResolution,    'max' => 40, 'color' => 'bg-primary'],
                        ['label' => 'First Response SLA', 'score' => $scoreSlaResponse,   'max' => 25, 'color' => 'bg-info'],
                        ['label' => 'Resolution SLA',     'score' => $scoreSlaResolution, 'max' => 20, 'color' => 'bg-success'],
                        ['label' => 'Overdue Control',    'score' => $scoreOverdue,        'max' => 15, 'color' => 'bg-warning'],
                    ];
                @endphp
                <div class="space-y-4">
                    @foreach($breakdown as $b)
                    <div>
                        <div class="flex items-center justify-between mb-1">
                            <span class="text-xs text-slate-600 dark:text-navy-200">{{ $b['label'] }}</span>
                            <span class="text-xs font-bold text-slate-800 dark:text-navy-50">{{ round($b['score']) }} <span class="font-normal text-slate-400">/ {{ $b['max'] }}</span></span>
                        </div>
                        <div class="h-2 w-full rounded-full bg-slate-100 dark:bg-navy-600">
                            <div class="h-2 rounded-full {{ $b['color'] }}"
                                 style="width:{{ $b['max'] > 0 ? round(($b['score']/$b['max'])*100) : 0 }}%"></div>
                        </div>
                    </div>
                    @endforeach
                </div>
                <div class="mt-4 flex items-center justify-between rounded-xl bg-slate-50 dark:bg-navy-600 px-4 py-3">
                    <span class="text-sm font-semibold text-slate-600 dark:text-navy-200">Total Score</span>
                    <span class="text-lg font-bold" style="color:{{ $tier['color'] }}">{{ $performanceScore }} / 100</span>
                </div>
            </div>

            {{-- SLA Metrics + You vs Team --}}
            <div class="card rounded-2xl bg-white p-5 shadow dark:bg-navy-700">
                <h3 class="mb-4 text-sm font-semibold text-slate-700 dark:text-navy-100">SLA & Team Comparison</h3>

                <div class="space-y-4">
                    {{-- Avg First Response --}}
                    <div class="flex items-center justify-between rounded-xl bg-slate-50 dark:bg-navy-600 px-4 py-3">
                        <div>
                            <p class="text-xs text-slate-400 dark:text-navy-300">Avg First Response</p>
                            <p class="mt-0.5 text-lg font-bold text-slate-800 dark:text-navy-50">{{ $formatMins($avgFirstResponse) }}</p>
                        </div>
                        @if($slaResponseHours > 0)
                        <div class="text-right">
                            <p class="text-xs text-slate-400 dark:text-navy-300">SLA Target</p>
                            <p class="mt-0.5 text-sm font-semibold {{ ($avgFirstResponse !== null && $avgFirstResponse <= $slaResponseHours*60) ? 'text-success' : 'text-error' }}">
                                {{ $slaResponseHours }}h
                                @if($avgFirstResponse !== null)
                                    {{ $avgFirstResponse <= $slaResponseHours*60 ? '✓' : '✗' }}
                                @endif
                            </p>
                        </div>
                        @endif
                    </div>

                    {{-- Avg Resolution --}}
                    <div class="flex items-center justify-between rounded-xl bg-slate-50 dark:bg-navy-600 px-4 py-3">
                        <div>
                            <p class="text-xs text-slate-400 dark:text-navy-300">Avg Resolution Time</p>
                            <p class="mt-0.5 text-lg font-bold text-slate-800 dark:text-navy-50">{{ $formatMins($avgResolution) }}</p>
                        </div>
                        @if($slaResolutionHours > 0)
                        <div class="text-right">
                            <p class="text-xs text-slate-400 dark:text-navy-300">SLA Target</p>
                            <p class="mt-0.5 text-sm font-semibold {{ ($avgResolution !== null && $avgResolution <= $slaResolutionHours*60) ? 'text-success' : 'text-error' }}">
                                {{ $slaResolutionHours }}h
                                @if($avgResolution !== null)
                                    {{ $avgResolution <= $slaResolutionHours*60 ? '✓' : '✗' }}
                                @endif
                            </p>
                        </div>
                        @endif
                    </div>

                    {{-- You vs Team --}}
                    <div class="rounded-xl border border-slate-200 dark:border-navy-500 px-4 py-3">
                        <p class="text-xs font-semibold text-slate-500 dark:text-navy-300 mb-3">You vs Team Average</p>
                        <div class="grid grid-cols-2 gap-3">
                            <div class="text-center">
                                <p class="text-[11px] text-slate-400 dark:text-navy-400">Your Score</p>
                                <p class="text-2xl font-bold" style="color:{{ $tier['color'] }}">{{ $performanceScore }}</p>
                            </div>
                            <div class="text-center">
                                <p class="text-[11px] text-slate-400 dark:text-navy-400">Team Avg</p>
                                <p class="text-2xl font-bold text-slate-600 dark:text-navy-200">{{ $teamAvgScore }}</p>
                            </div>
                        </div>
                        <div class="mt-2 text-center text-xs">
                            @if($performanceScore > $teamAvgScore)
                                <span class="text-success font-semibold">+{{ $performanceScore - $teamAvgScore }} pts above team average</span>
                            @elseif($performanceScore < $teamAvgScore)
                                <span class="text-error font-semibold">{{ $performanceScore - $teamAvgScore }} pts below team average</span>
                            @else
                                <span class="text-slate-400">Equal to team average</span>
                            @endif
                        </div>
                    </div>
                </div>
            </div>
        </div>

        {{-- ── Row 4: Volume Chart ──────────────────────────────────────────────── --}}
        <div class="mt-4 card rounded-2xl bg-white p-5 shadow dark:bg-navy-700">
            <div class="flex items-center justify-between mb-4">
                <h3 class="text-sm font-semibold text-slate-700 dark:text-navy-100">Ticket Volume</h3>
                <div class="flex items-center gap-4 text-xs">
                    <span class="flex items-center gap-1.5"><span class="inline-block size-2.5 rounded-sm bg-primary"></span>Created</span>
                    <span class="flex items-center gap-1.5"><span class="inline-block size-2.5 rounded-sm bg-success"></span>Resolved</span>
                </div>
            </div>

            @php
                $dates = $allDates->toArray();
                $maxVal = max(1, collect($dates)->max(fn($d) => max($d['created'], $d['resolved'])));
                $showEveryN = count($dates) > 14 ? (int)ceil(count($dates) / 14) : 1;
            @endphp

            @if(count($dates) > 0)
            <div class="overflow-x-auto">
                <div style="min-width: {{ max(600, count($dates) * 28) }}px;">
                    {{-- Bars --}}
                    <div class="flex items-end gap-0.5 h-40">
                        @foreach($dates as $i => $d)
                        <div class="flex flex-1 items-end gap-px group relative">
                            {{-- Created bar --}}
                            <div class="flex-1 rounded-t bg-primary/80 hover:bg-primary transition-all"
                                 style="height:{{ $d['created'] > 0 ? round(($d['created']/$maxVal)*100) : 2 }}%;min-height:{{ $d['created'] > 0 ? '4px' : '0' }};"
                                 title="{{ $d['date'] }}: {{ $d['created'] }} created"></div>
                            {{-- Resolved bar --}}
                            <div class="flex-1 rounded-t bg-success/80 hover:bg-success transition-all"
                                 style="height:{{ $d['resolved'] > 0 ? round(($d['resolved']/$maxVal)*100) : 2 }}%;min-height:{{ $d['resolved'] > 0 ? '4px' : '0' }};"
                                 title="{{ $d['date'] }}: {{ $d['resolved'] }} resolved"></div>
                        </div>
                        @endforeach
                    </div>
                    {{-- X-axis labels --}}
                    <div class="flex gap-0.5 mt-1">
                        @foreach($dates as $i => $d)
                        <div class="flex-1 text-center">
                            @if($i % $showEveryN === 0)
                            <span class="text-[9px] text-slate-400 dark:text-navy-400 leading-none">
                                {{ \Carbon\Carbon::parse($d['date'])->format('M d') }}
                            </span>
                            @endif
                        </div>
                        @endforeach
                    </div>
                </div>
            </div>
            @else
            <div class="flex h-40 items-center justify-center text-sm text-slate-400">No data for this period</div>
            @endif
        </div>

        {{-- ── Row 5: Distributions ─────────────────────────────────────────────── --}}
        <div class="mt-4 grid grid-cols-1 gap-4 md:grid-cols-3">

            {{-- By Status --}}
            <div class="card rounded-2xl bg-white p-5 shadow dark:bg-navy-700">
                <h3 class="mb-4 text-sm font-semibold text-slate-700 dark:text-navy-100">By Status</h3>
                @if($byStatus->isEmpty())
                    <p class="text-sm text-slate-400 text-center py-4">No data</p>
                @else
                @php $statusTotal = $byStatus->sum('count'); @endphp
                <div class="space-y-3">
                    @foreach($byStatus as $s)
                    <div>
                        <div class="flex items-center justify-between mb-1">
                            <div class="flex items-center gap-2">
                                <span class="inline-block size-2 rounded-full" style="background:{{ $s->color ?? '#94a3b8' }}"></span>
                                <span class="text-xs text-slate-600 dark:text-navy-200">{{ $s->name ?? 'Unknown' }}</span>
                            </div>
                            <span class="text-xs font-bold text-slate-700 dark:text-navy-100">{{ $s->count }}</span>
                        </div>
                        <div class="h-1.5 w-full rounded-full bg-slate-100 dark:bg-navy-600">
                            <div class="h-1.5 rounded-full" style="background:{{ $s->color ?? '#94a3b8' }};width:{{ $statusTotal > 0 ? round(($s->count/$statusTotal)*100) : 0 }}%"></div>
                        </div>
                    </div>
                    @endforeach
                </div>
                @endif
            </div>

            {{-- By Priority --}}
            <div class="card rounded-2xl bg-white p-5 shadow dark:bg-navy-700">
                <h3 class="mb-4 text-sm font-semibold text-slate-700 dark:text-navy-100">By Priority</h3>
                @if($byPriority->isEmpty())
                    <p class="text-sm text-slate-400 text-center py-4">No data</p>
                @else
                @php $priorityTotal = $byPriority->sum('count'); @endphp
                <div class="space-y-3">
                    @foreach($byPriority as $p)
                    <div>
                        <div class="flex items-center justify-between mb-1">
                            <div class="flex items-center gap-2">
                                <span class="inline-block size-2 rounded-full" style="background:{{ $p->color ?? '#94a3b8' }}"></span>
                                <span class="text-xs text-slate-600 dark:text-navy-200">{{ $p->name ?? 'Unknown' }}</span>
                            </div>
                            <span class="text-xs font-bold text-slate-700 dark:text-navy-100">{{ $p->count }}</span>
                        </div>
                        <div class="h-1.5 w-full rounded-full bg-slate-100 dark:bg-navy-600">
                            <div class="h-1.5 rounded-full" style="background:{{ $p->color ?? '#94a3b8' }};width:{{ $priorityTotal > 0 ? round(($p->count/$priorityTotal)*100) : 0 }}%"></div>
                        </div>
                    </div>
                    @endforeach
                </div>
                @endif
            </div>

            {{-- By Category --}}
            <div class="card rounded-2xl bg-white p-5 shadow dark:bg-navy-700">
                <h3 class="mb-4 text-sm font-semibold text-slate-700 dark:text-navy-100">By Category</h3>
                @if($byCategory->isEmpty())
                    <p class="text-sm text-slate-400 text-center py-4">No data</p>
                @else
                @php $categoryTotal = $byCategory->sum('count'); @endphp
                <div class="space-y-3">
                    @foreach($byCategory as $c)
                    <div>
                        <div class="flex items-center justify-between mb-1">
                            <span class="text-xs text-slate-600 dark:text-navy-200 truncate max-w-[140px]">{{ $c->name }}</span>
                            <span class="text-xs font-bold text-slate-700 dark:text-navy-100">{{ $c->count }}</span>
                        </div>
                        <div class="h-1.5 w-full rounded-full bg-slate-100 dark:bg-navy-600">
                            <div class="h-1.5 rounded-full bg-slate-400 dark:bg-navy-300"
                                 style="width:{{ $categoryTotal > 0 ? round(($c->count/$categoryTotal)*100) : 0 }}%"></div>
                        </div>
                    </div>
                    @endforeach
                </div>
                @endif
            </div>
        </div>

        {{-- ── Row 6: Recent Tickets ────────────────────────────────────────────── --}}
        <div class="mt-4 card rounded-2xl bg-white shadow dark:bg-navy-700">
            <div class="flex items-center justify-between px-5 pt-5 pb-3">
                <h3 class="text-sm font-semibold text-slate-700 dark:text-navy-100">Recent Tickets</h3>
                <a href="{{ route('agent.tickets/index') }}" class="text-xs text-primary hover:underline">View all</a>
            </div>
            <div class="overflow-x-auto">
                <table class="is-hoverable w-full text-left">
                    <thead>
                        <tr class="border-y border-slate-200 dark:border-navy-500">
                            <th class="px-4 py-3 text-xs font-semibold text-slate-400 uppercase">Ticket #</th>
                            <th class="px-4 py-3 text-xs font-semibold text-slate-400 uppercase">Subject</th>
                            <th class="px-4 py-3 text-xs font-semibold text-slate-400 uppercase">Status</th>
                            <th class="px-4 py-3 text-xs font-semibold text-slate-400 uppercase">Priority</th>
                            <th class="px-4 py-3 text-xs font-semibold text-slate-400 uppercase">Created</th>
                            <th class="px-4 py-3 text-xs font-semibold text-slate-400 uppercase">Resolved</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($recentTickets as $t)
                        <tr class="border-b border-slate-100 dark:border-navy-600 hover:bg-slate-50 dark:hover:bg-navy-600/50">
                            <td class="px-4 py-3">
                                <a href="{{ route('agent.tickets/show', $t->uuid) }}" class="text-xs font-mono text-primary hover:underline">{{ $t->ticket_no }}</a>
                            </td>
                            <td class="px-4 py-3 max-w-xs">
                                <a href="{{ route('agent.tickets/show', $t->uuid) }}" class="text-xs text-slate-700 dark:text-navy-100 truncate block max-w-[200px]">{{ $t->subject }}</a>
                            </td>
                            <td class="px-4 py-3">
                                @if($t->status_name)
                                <span class="inline-flex items-center rounded-full px-2 py-0.5 text-[11px] font-medium text-white"
                                      style="background:{{ $t->status_color ?? '#94a3b8' }}">{{ $t->status_name }}</span>
                                @else
                                <span class="text-xs text-slate-400">—</span>
                                @endif
                            </td>
                            <td class="px-4 py-3">
                                @if($t->priority_name)
                                <span class="inline-flex items-center rounded-full px-2 py-0.5 text-[11px] font-medium text-white"
                                      style="background:{{ $t->priority_color ?? '#94a3b8' }}">{{ $t->priority_name }}</span>
                                @else
                                <span class="text-xs text-slate-400">—</span>
                                @endif
                            </td>
                            <td class="px-4 py-3 text-xs text-slate-500 dark:text-navy-300 whitespace-nowrap">
                                {{ \Carbon\Carbon::parse($t->created_at)->format('M d, Y') }}
                            </td>
                            <td class="px-4 py-3 text-xs whitespace-nowrap">
                                @if($t->resolved_at)
                                    <span class="text-success">{{ \Carbon\Carbon::parse($t->resolved_at)->format('M d, Y') }}</span>
                                @else
                                    <span class="text-slate-400">—</span>
                                @endif
                            </td>
                        </tr>
                        @empty
                        <tr>
                            <td colspan="6" class="px-4 py-8 text-center text-sm text-slate-400">No tickets in this period</td>
                        </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>

    </main>
</x-app-layout>
