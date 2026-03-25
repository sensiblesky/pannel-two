<x-app-layout title="Ticket Dashboard" is-sidebar-open="true" is-header-blur="true">

    <main class="main-content w-full px-[var(--margin-x)] pb-10">

        {{-- ── Header ─────────────────────────────────────────────────────── --}}
        <div class="flex flex-col gap-4 py-5 lg:py-6 sm:flex-row sm:items-center sm:justify-between">
            <div>
                <h2 class="text-xl font-semibold text-slate-800 dark:text-navy-50 lg:text-2xl">Ticket Dashboard</h2>
                <p class="mt-0.5 text-sm text-slate-400 dark:text-navy-300">
                    {{ now()->format('l, F j, Y') }} · All teams{{ $branchId ? ' · Filtered by branch' : '' }}
                </p>
            </div>
            <div class="flex items-center gap-2 flex-wrap">
                {{-- Branch filter --}}
                <form method="GET" class="flex items-center gap-2">
                    <select name="branch_id" onchange="this.form.submit()"
                            class="form-select h-9 rounded-lg border border-slate-300 bg-white px-3 text-xs dark:border-navy-450 dark:bg-navy-700 dark:text-navy-100">
                        <option value="">All Branches</option>
                        @foreach($branches as $b)
                            <option value="{{ $b->id }}" @selected($branchId == $b->id)>{{ $b->name }}</option>
                        @endforeach
                    </select>
                </form>
                <a href="{{ route('tickets/create') }}"
                   class="btn h-9 rounded-lg bg-primary px-4 text-sm font-medium text-white shadow-lg shadow-primary/40 hover:bg-primary-focus dark:bg-accent dark:shadow-accent/40 dark:hover:bg-accent-focus">
                    <svg xmlns="http://www.w3.org/2000/svg" class="mr-1.5 size-4" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/></svg>
                    New Ticket
                </a>
            </div>
        </div>

        {{-- ── Row 1: 6 KPI Cards ───────────────────────────────────────────── --}}
        <div class="grid grid-cols-2 gap-4 sm:grid-cols-3 lg:grid-cols-6">
            @php
                $kpis = [
                    ['label'=>'Total',          'value'=>$totalTickets,  'sub'=>$resolutionRate.'% resolved',         'subclr'=>'text-slate-400', 'icon'=>'M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2','bg'=>'bg-primary/10','tc'=>'text-primary',  'route'=>route('tickets/index')],
                    ['label'=>'Open',           'value'=>$totalOpen,     'sub'=>'needs attention',                    'subclr'=>'text-warning',   'icon'=>'M8 10h.01M12 10h.01M16 10h.01M9 16H5a2 2 0 01-2-2V6a2 2 0 012-2h14a2 2 0 012 2v8a2 2 0 01-2 2h-5l-3 3v-3z',                          'bg'=>'bg-warning/10','tc'=>'text-warning', 'route'=>route('tickets/index',['view'=>'open'])],
                    ['label'=>'Pending',        'value'=>$totalPending,  'sub'=>'awaiting reply',                     'subclr'=>'text-slate-400', 'icon'=>'M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0',                                                                                           'bg'=>'bg-info/10',   'tc'=>'text-info',    'route'=>route('tickets/index',['view'=>'pending'])],
                    ['label'=>'Resolved Today', 'value'=>$resolvedToday, 'sub'=>'closed today',                       'subclr'=>'text-success',   'icon'=>'M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0',                                                                                         'bg'=>'bg-success/10','tc'=>'text-success', 'route'=>route('tickets/index',['view'=>'resolved'])],
                    ['label'=>'Overdue',        'value'=>$overdue,       'sub'=>$overdue>0?'requires action':'on time','subclr'=>$overdue>0?'text-error':'text-success', 'icon'=>'M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z','bg'=>'bg-error/10','tc'=>'text-error','route'=>route('tickets/index',['view'=>'overdue'])],
                    ['label'=>'Unassigned',     'value'=>$unassigned,    'sub'=>$unassigned>0?'needs agent':'fully assigned','subclr'=>$unassigned>0?'text-warning':'text-success', 'icon'=>'M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z',                              'bg'=>'bg-slate-200','tc'=>'text-slate-500 dark:text-navy-200', 'route'=>route('tickets/index',['view'=>'unassigned'])],
                ];
            @endphp
            @foreach($kpis as $kpi)
            <a href="{{ $kpi['route'] }}"
               class="card group rounded-2xl bg-white p-4 shadow transition-shadow hover:shadow-md dark:bg-navy-700">
                <div class="flex items-start justify-between">
                    <div class="flex size-9 items-center justify-center rounded-xl {{ $kpi['bg'] }} {{ $kpi['tc'] }} transition-transform group-hover:scale-110">
                        <svg xmlns="http://www.w3.org/2000/svg" class="size-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.5">
                            <path stroke-linecap="round" stroke-linejoin="round" d="{{ $kpi['icon'] }}"/>
                        </svg>
                    </div>
                </div>
                <p class="mt-3 text-2xl font-bold text-slate-800 dark:text-navy-50">{{ $kpi['value'] }}</p>
                <p class="text-xs font-medium text-slate-500 dark:text-navy-300">{{ $kpi['label'] }}</p>
                <p class="mt-0.5 text-[11px] {{ $kpi['subclr'] }}">{{ $kpi['sub'] }}</p>
            </a>
            @endforeach
        </div>

        {{-- ── Row 2: SLA + Avg Times + Agents + Resolution strip ─────────── --}}
        <div class="mt-4 grid grid-cols-2 gap-4 sm:grid-cols-4 lg:grid-cols-4">

            {{-- Avg First Response --}}
            <div class="card rounded-2xl bg-white p-4 shadow dark:bg-navy-700">
                <div class="flex items-center gap-2 mb-2">
                    <div class="flex size-8 items-center justify-center rounded-lg bg-info/10">
                        <svg class="size-4 text-info" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M13 10V3L4 14h7v7l9-11h-7z"/></svg>
                    </div>
                    <p class="text-xs font-medium text-slate-500 dark:text-navy-300">Avg First Response</p>
                </div>
                <p class="text-2xl font-bold text-slate-800 dark:text-navy-50">{{ $formatMins($avgFirstResponse) }}</p>
                @if($slaResponseHours > 0)
                <p class="mt-1 text-[11px] {{ ($avgFirstResponse !== null && $avgFirstResponse <= $slaResponseHours*60) ? 'text-success' : 'text-slate-400' }}">
                    SLA: {{ $slaResponseHours }}h {{ ($avgFirstResponse !== null) ? (($avgFirstResponse <= $slaResponseHours*60) ? '✓ within' : '✗ over') : '' }}
                </p>
                @endif
            </div>

            {{-- Avg Resolution --}}
            <div class="card rounded-2xl bg-white p-4 shadow dark:bg-navy-700">
                <div class="flex items-center gap-2 mb-2">
                    <div class="flex size-8 items-center justify-center rounded-lg bg-success/10">
                        <svg class="size-4 text-success" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0"/></svg>
                    </div>
                    <p class="text-xs font-medium text-slate-500 dark:text-navy-300">Avg Resolution</p>
                </div>
                <p class="text-2xl font-bold text-slate-800 dark:text-navy-50">{{ $formatMins($avgResolution) }}</p>
                @if($slaResolutionHours > 0)
                <p class="mt-1 text-[11px] {{ ($avgResolution !== null && $avgResolution <= $slaResolutionHours*60) ? 'text-success' : 'text-slate-400' }}">
                    SLA: {{ $slaResolutionHours }}h {{ ($avgResolution !== null) ? (($avgResolution <= $slaResolutionHours*60) ? '✓ within' : '✗ over') : '' }}
                </p>
                @endif
            </div>

            {{-- SLA Compliance --}}
            <div class="card rounded-2xl bg-white p-4 shadow dark:bg-navy-700">
                <div class="flex items-center gap-2 mb-2">
                    <div class="flex size-8 items-center justify-center rounded-lg bg-primary/10">
                        <svg class="size-4 text-primary" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z"/></svg>
                    </div>
                    <p class="text-xs font-medium text-slate-500 dark:text-navy-300">SLA Compliance</p>
                </div>
                @php
                    $compliancePct = $slaResolutionCompliance ?? $slaResponseCompliance;
                @endphp
                @if($compliancePct !== null)
                    <p class="text-2xl font-bold {{ $compliancePct >= 80 ? 'text-success' : ($compliancePct >= 60 ? 'text-warning' : 'text-error') }}">
                        {{ $compliancePct }}%
                    </p>
                    <div class="mt-1.5 h-1.5 w-full rounded-full bg-slate-100 dark:bg-navy-600">
                        <div class="h-1.5 rounded-full {{ $compliancePct >= 80 ? 'bg-success' : ($compliancePct >= 60 ? 'bg-warning' : 'bg-error') }}"
                             style="width:{{ $compliancePct }}%"></div>
                    </div>
                @else
                    <p class="text-2xl font-bold text-slate-400">—</p>
                    <p class="mt-1 text-[11px] text-slate-400">No SLA configured</p>
                @endif
            </div>

            {{-- Active Agents --}}
            <div class="card rounded-2xl bg-white p-4 shadow dark:bg-navy-700">
                <div class="flex items-center gap-2 mb-2">
                    <div class="flex size-8 items-center justify-center rounded-lg bg-amber-100 dark:bg-amber-900/30">
                        <svg class="size-4 text-amber-600" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0z"/></svg>
                    </div>
                    <p class="text-xs font-medium text-slate-500 dark:text-navy-300">Active Agents</p>
                </div>
                <p class="text-2xl font-bold text-slate-800 dark:text-navy-50">{{ $totalAgents }}</p>
                <p class="mt-1 text-[11px] text-slate-400">{{ $totalAgents > 0 ? round($totalTickets / max(1,$totalAgents)) : 0 }} tickets/agent avg</p>
            </div>
        </div>

        {{-- ── Row 3: 30-day Chart + Agent Leaderboard ─────────────────────── --}}
        <div class="mt-4 grid grid-cols-1 gap-4 lg:grid-cols-3">

            {{-- 30-day Volume Chart --}}
            <div class="card rounded-2xl bg-white p-5 shadow dark:bg-navy-700 lg:col-span-2">
                <div class="flex items-center justify-between mb-4">
                    <h3 class="text-sm font-semibold text-slate-700 dark:text-navy-100">30-Day Ticket Volume</h3>
                    <div class="flex items-center gap-3 text-xs text-slate-400">
                        <span class="flex items-center gap-1.5"><span class="inline-block size-2.5 rounded-sm bg-primary"></span>Created</span>
                        <span class="flex items-center gap-1.5"><span class="inline-block size-2.5 rounded-sm bg-success"></span>Resolved</span>
                    </div>
                </div>
                @php
                    $maxBar = max(1, $chartDays->max(fn($d) => max($d['created'], $d['resolved'])));
                    $showEvery = 5;
                @endphp
                <div class="overflow-x-auto">
                    <div style="min-width:600px">
                        <div class="flex items-end gap-px h-32">
                            @foreach($chartDays as $day)
                            <div class="flex flex-1 flex-col items-center gap-px group">
                                <div class="flex w-full items-end gap-px" style="height:112px;">
                                    <div class="flex-1 rounded-t bg-primary/70 hover:bg-primary transition-colors cursor-default"
                                         style="height:{{ $day['created'] > 0 ? round(($day['created']/$maxBar)*100) : 1 }}%;min-height:{{ $day['created'] > 0 ? '3px' : '0' }};"
                                         title="{{ $day['date'] }}: {{ $day['created'] }} created"></div>
                                    <div class="flex-1 rounded-t bg-success/70 hover:bg-success transition-colors cursor-default"
                                         style="height:{{ $day['resolved'] > 0 ? round(($day['resolved']/$maxBar)*100) : 1 }}%;min-height:{{ $day['resolved'] > 0 ? '3px' : '0' }};"
                                         title="{{ $day['date'] }}: {{ $day['resolved'] }} resolved"></div>
                                </div>
                            </div>
                            @endforeach
                        </div>
                        <div class="flex gap-px mt-1">
                            @foreach($chartDays as $i => $day)
                            <div class="flex-1 text-center">
                                @if($i % $showEvery === 0)
                                <span class="text-[9px] text-slate-400">{{ $day['label'] }}</span>
                                @endif
                            </div>
                            @endforeach
                        </div>
                    </div>
                </div>
                <div class="mt-3 grid grid-cols-3 gap-3 border-t border-slate-100 pt-3 dark:border-navy-600">
                    <div class="text-center">
                        <p class="text-lg font-bold text-slate-800 dark:text-navy-50">{{ $chartDays->sum('created') }}</p>
                        <p class="text-xs text-slate-400">Created (30d)</p>
                    </div>
                    <div class="text-center">
                        <p class="text-lg font-bold text-success">{{ $chartDays->sum('resolved') }}</p>
                        <p class="text-xs text-slate-400">Resolved (30d)</p>
                    </div>
                    <div class="text-center">
                        @php $net = $chartDays->sum('created') - $chartDays->sum('resolved'); @endphp
                        <p class="text-lg font-bold {{ $net > 0 ? 'text-error' : 'text-success' }}">
                            {{ $net > 0 ? '+' : '' }}{{ $net }}
                        </p>
                        <p class="text-xs text-slate-400">Net backlog</p>
                    </div>
                </div>
            </div>

            {{-- Agent Leaderboard --}}
            <div class="card rounded-2xl bg-white p-5 shadow dark:bg-navy-700">
                <div class="flex items-center justify-between mb-4">
                    <h3 class="text-sm font-semibold text-slate-700 dark:text-navy-100">Agent Leaderboard</h3>
                    <span class="text-xs text-slate-400">by score</span>
                </div>
                @if($agentLeaderboard->isEmpty())
                    <p class="py-6 text-center text-sm text-slate-400">No agent data yet</p>
                @else
                <div class="space-y-3">
                    @foreach($agentLeaderboard as $i => $agent)
                    @php
                        $medal = match($i) { 0 => '🥇', 1 => '🥈', 2 => '🥉', default => null };
                        $scoreColor = $agent->score >= 75 ? 'text-success' : ($agent->score >= 50 ? 'text-warning' : 'text-error');
                    @endphp
                    <div class="flex items-center gap-3">
                        <div class="flex size-7 shrink-0 items-center justify-center">
                            @if($medal)
                                <span class="text-base">{{ $medal }}</span>
                            @else
                                <span class="text-xs font-bold text-slate-400">#{{ $i+1 }}</span>
                            @endif
                        </div>
                        <div class="flex-1 min-w-0">
                            <div class="flex items-center justify-between">
                                <p class="truncate text-xs font-medium text-slate-700 dark:text-navy-100">{{ $agent->name }}</p>
                                <span class="ml-2 shrink-0 text-xs font-bold {{ $scoreColor }}">{{ $agent->score }}</span>
                            </div>
                            <div class="mt-1 flex items-center gap-2">
                                <div class="flex-1 h-1.5 rounded-full bg-slate-100 dark:bg-navy-600">
                                    <div class="h-1.5 rounded-full {{ $agent->score >= 75 ? 'bg-success' : ($agent->score >= 50 ? 'bg-warning' : 'bg-error') }}"
                                         style="width:{{ $agent->score }}%"></div>
                                </div>
                                <span class="text-[10px] text-slate-400 shrink-0">{{ $agent->total }}t · {{ $agent->resolution_rate }}%</span>
                            </div>
                        </div>
                    </div>
                    @endforeach
                </div>
                @endif
            </div>
        </div>

        {{-- ── Row 4: By Status + By Priority + By Category ────────────────── --}}
        <div class="mt-4 grid grid-cols-1 gap-4 sm:grid-cols-3">

            {{-- By Status --}}
            <div class="card rounded-2xl bg-white p-5 shadow dark:bg-navy-700">
                <h3 class="mb-4 text-sm font-semibold text-slate-700 dark:text-navy-100">By Status</h3>
                @if($byStatus->isEmpty())
                    <p class="py-4 text-center text-sm text-slate-400">No data</p>
                @else
                @php $st = $byStatus->sum('count'); @endphp
                <div class="space-y-3">
                    @foreach($byStatus as $s)
                    <div>
                        <div class="flex items-center justify-between mb-1">
                            <div class="flex items-center gap-2">
                                <span class="size-2 rounded-full shrink-0" style="background:{{ $s->color ?? '#94a3b8' }}"></span>
                                <span class="text-xs text-slate-600 dark:text-navy-200">{{ $s->name ?? 'Unknown' }}</span>
                            </div>
                            <span class="text-xs font-bold text-slate-700 dark:text-navy-100">{{ $s->count }}</span>
                        </div>
                        <div class="h-1.5 w-full rounded-full bg-slate-100 dark:bg-navy-600">
                            <div class="h-1.5 rounded-full" style="background:{{ $s->color ?? '#94a3b8' }};width:{{ $st > 0 ? round(($s->count/$st)*100) : 0 }}%"></div>
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
                    <p class="py-4 text-center text-sm text-slate-400">No data</p>
                @else
                @php $pt = $byPriority->sum('count'); @endphp
                <div class="space-y-3">
                    @foreach($byPriority as $p)
                    <div>
                        <div class="flex items-center justify-between mb-1">
                            <div class="flex items-center gap-2">
                                <span class="size-2 rounded-full shrink-0" style="background:{{ $p->color ?? '#94a3b8' }}"></span>
                                <span class="text-xs text-slate-600 dark:text-navy-200">{{ $p->name ?? 'Unknown' }}</span>
                            </div>
                            <span class="text-xs font-bold text-slate-700 dark:text-navy-100">{{ $p->count }}</span>
                        </div>
                        <div class="h-1.5 w-full rounded-full bg-slate-100 dark:bg-navy-600">
                            <div class="h-1.5 rounded-full" style="background:{{ $p->color ?? '#94a3b8' }};width:{{ $pt > 0 ? round(($p->count/$pt)*100) : 0 }}%"></div>
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
                    <p class="py-4 text-center text-sm text-slate-400">No data</p>
                @else
                @php $ct = $byCategory->sum('count'); @endphp
                <div class="space-y-3">
                    @foreach($byCategory as $c)
                    <div>
                        <div class="flex items-center justify-between mb-1">
                            <span class="text-xs text-slate-600 dark:text-navy-200 truncate max-w-[150px]">{{ $c->name }}</span>
                            <span class="text-xs font-bold text-slate-700 dark:text-navy-100 ml-2 shrink-0">{{ $c->count }}</span>
                        </div>
                        <div class="h-1.5 w-full rounded-full bg-slate-100 dark:bg-navy-600">
                            <div class="h-1.5 rounded-full bg-slate-400 dark:bg-navy-300" style="width:{{ $ct > 0 ? round(($c->count/$ct)*100) : 0 }}%"></div>
                        </div>
                    </div>
                    @endforeach
                </div>
                @endif
            </div>
        </div>

        {{-- ── Row 5: Overdue + Unassigned needing action ──────────────────── --}}
        <div class="mt-4 grid grid-cols-1 gap-4 lg:grid-cols-2">

            {{-- Overdue Tickets --}}
            <div class="card rounded-2xl bg-white shadow dark:bg-navy-700">
                <div class="flex items-center justify-between px-5 pt-4 pb-3">
                    <div class="flex items-center gap-2">
                        @if($overdueTickets->isNotEmpty())
                        <span class="flex size-2 rounded-full bg-error animate-pulse"></span>
                        @endif
                        <h3 class="text-sm font-semibold text-slate-700 dark:text-navy-100">Overdue Tickets</h3>
                        @if($overdueTickets->isNotEmpty())
                        <span class="rounded-full bg-error/10 px-2 py-0.5 text-[11px] font-semibold text-error">{{ $overdue }}</span>
                        @endif
                    </div>
                    <a href="{{ route('tickets/index', ['view' => 'overdue']) }}" class="text-xs text-primary hover:underline">View all</a>
                </div>
                @if($overdueTickets->isEmpty())
                    <div class="flex flex-col items-center justify-center px-5 py-8 text-center">
                        <div class="flex size-10 items-center justify-center rounded-full bg-success/10">
                            <svg class="size-5 text-success" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0"/></svg>
                        </div>
                        <p class="mt-2 text-sm font-medium text-slate-600 dark:text-navy-200">All clear!</p>
                        <p class="text-xs text-slate-400">No overdue tickets</p>
                    </div>
                @else
                <div class="divide-y divide-slate-100 dark:divide-navy-600">
                    @foreach($overdueTickets as $t)
                    <a href="{{ route('tickets/show', $t->uuid) }}"
                       class="flex items-center gap-3 px-5 py-3 hover:bg-slate-50 dark:hover:bg-navy-600/50 transition-colors">
                        <div class="flex size-8 shrink-0 items-center justify-center rounded-full bg-error/10">
                            <svg class="size-4 text-error" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0"/></svg>
                        </div>
                        <div class="min-w-0 flex-1">
                            <div class="flex items-center gap-2">
                                <span class="font-mono text-xs text-primary">{{ $t->ticket_no }}</span>
                                @if($t->priority_name)
                                <span class="rounded-full px-1.5 py-0.5 text-[10px] font-medium text-white"
                                      style="background:{{ $t->priority_color ?? '#94a3b8' }}">{{ $t->priority_name }}</span>
                                @endif
                            </div>
                            <p class="mt-0.5 truncate text-xs text-slate-600 dark:text-navy-200">{{ $t->subject }}</p>
                            <p class="text-[11px] text-error">{{ \Carbon\Carbon::parse($t->due_at)->diffForHumans() }}</p>
                        </div>
                        @if($t->agent_name)
                        <span class="shrink-0 text-xs text-slate-400 hidden sm:block">{{ $t->agent_name }}</span>
                        @else
                        <span class="shrink-0 rounded-full bg-warning/10 px-2 py-0.5 text-[10px] font-medium text-warning">Unassigned</span>
                        @endif
                    </a>
                    @endforeach
                </div>
                @endif
            </div>

            {{-- Unassigned Tickets --}}
            <div class="card rounded-2xl bg-white shadow dark:bg-navy-700">
                <div class="flex items-center justify-between px-5 pt-4 pb-3">
                    <div class="flex items-center gap-2">
                        @if($unassignedTickets->isNotEmpty())
                        <span class="flex size-2 rounded-full bg-warning animate-pulse"></span>
                        @endif
                        <h3 class="text-sm font-semibold text-slate-700 dark:text-navy-100">Unassigned Tickets</h3>
                        @if($unassignedTickets->isNotEmpty())
                        <span class="rounded-full bg-warning/10 px-2 py-0.5 text-[11px] font-semibold text-warning">{{ $unassigned }}</span>
                        @endif
                    </div>
                    <a href="{{ route('tickets/index', ['view' => 'unassigned']) }}" class="text-xs text-primary hover:underline">View all</a>
                </div>
                @if($unassignedTickets->isEmpty())
                    <div class="flex flex-col items-center justify-center px-5 py-8 text-center">
                        <div class="flex size-10 items-center justify-center rounded-full bg-success/10">
                            <svg class="size-5 text-success" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0"/></svg>
                        </div>
                        <p class="mt-2 text-sm font-medium text-slate-600 dark:text-navy-200">All assigned!</p>
                        <p class="text-xs text-slate-400">No unassigned tickets</p>
                    </div>
                @else
                <div class="divide-y divide-slate-100 dark:divide-navy-600">
                    @foreach($unassignedTickets as $t)
                    <a href="{{ route('tickets/show', $t->uuid) }}"
                       class="flex items-center gap-3 px-5 py-3 hover:bg-slate-50 dark:hover:bg-navy-600/50 transition-colors">
                        <div class="flex size-8 shrink-0 items-center justify-center rounded-full bg-warning/10">
                            <svg class="size-4 text-warning" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"/></svg>
                        </div>
                        <div class="min-w-0 flex-1">
                            <div class="flex items-center gap-2">
                                <span class="font-mono text-xs text-primary">{{ $t->ticket_no }}</span>
                                @if($t->priority_name)
                                <span class="rounded-full px-1.5 py-0.5 text-[10px] font-medium text-white"
                                      style="background:{{ $t->priority_color ?? '#94a3b8' }}">{{ $t->priority_name }}</span>
                                @endif
                            </div>
                            <p class="mt-0.5 truncate text-xs text-slate-600 dark:text-navy-200">{{ $t->subject }}</p>
                            <p class="text-[11px] text-slate-400">{{ \Carbon\Carbon::parse($t->created_at)->diffForHumans() }} · {{ $t->customer_name ?? 'Unknown' }}</p>
                        </div>
                    </a>
                    @endforeach
                </div>
                @endif
            </div>
        </div>

        {{-- ── Row 6: Recent Tickets ────────────────────────────────────────── --}}
        <div class="mt-4 card rounded-2xl bg-white shadow dark:bg-navy-700">
            <div class="flex items-center justify-between px-5 pt-5 pb-3">
                <h3 class="text-sm font-semibold text-slate-700 dark:text-navy-100">Recent Tickets</h3>
                <a href="{{ route('tickets/index') }}" class="text-xs text-primary hover:underline">View all</a>
            </div>
            <div class="overflow-x-auto">
                <table class="w-full text-left">
                    <thead>
                        <tr class="border-y border-slate-100 dark:border-navy-600">
                            <th class="px-5 py-3 text-[11px] font-semibold uppercase tracking-wider text-slate-400">Ticket</th>
                            <th class="px-5 py-3 text-[11px] font-semibold uppercase tracking-wider text-slate-400">Subject</th>
                            <th class="px-5 py-3 text-[11px] font-semibold uppercase tracking-wider text-slate-400">Customer</th>
                            <th class="px-5 py-3 text-[11px] font-semibold uppercase tracking-wider text-slate-400">Agent</th>
                            <th class="px-5 py-3 text-[11px] font-semibold uppercase tracking-wider text-slate-400">Status</th>
                            <th class="px-5 py-3 text-[11px] font-semibold uppercase tracking-wider text-slate-400">Priority</th>
                            <th class="px-5 py-3 text-[11px] font-semibold uppercase tracking-wider text-slate-400">Created</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-100 dark:divide-navy-600">
                        @forelse($recentTickets as $ticket)
                        <tr class="hover:bg-slate-50 dark:hover:bg-navy-600/50 transition-colors">
                            <td class="whitespace-nowrap px-5 py-3">
                                <a href="{{ route('tickets/show', $ticket->uuid) }}"
                                   class="font-mono text-xs font-semibold text-primary hover:underline">{{ $ticket->ticket_no }}</a>
                            </td>
                            <td class="px-5 py-3 max-w-[180px]">
                                <a href="{{ route('tickets/show', $ticket->uuid) }}"
                                   class="block truncate text-sm text-slate-700 hover:text-primary dark:text-navy-100">{{ $ticket->subject }}</a>
                            </td>
                            <td class="whitespace-nowrap px-5 py-3 text-sm text-slate-500 dark:text-navy-300">{{ $ticket->customer_name ?? '—' }}</td>
                            <td class="whitespace-nowrap px-5 py-3 text-sm text-slate-500 dark:text-navy-300">
                                @if($ticket->agent_name)
                                    {{ $ticket->agent_name }}
                                @else
                                    <span class="text-warning text-xs">Unassigned</span>
                                @endif
                            </td>
                            <td class="whitespace-nowrap px-5 py-3">
                                @if($ticket->status_name)
                                <span class="inline-flex items-center rounded-full px-2.5 py-0.5 text-[11px] font-medium text-white"
                                      style="background:{{ $ticket->status_color ?? '#94a3b8' }}">{{ $ticket->status_name }}</span>
                                @else <span class="text-xs text-slate-400">—</span> @endif
                            </td>
                            <td class="whitespace-nowrap px-5 py-3">
                                @if($ticket->priority_name)
                                <span class="inline-flex items-center rounded-full px-2.5 py-0.5 text-[11px] font-medium text-white"
                                      style="background:{{ $ticket->priority_color ?? '#94a3b8' }}">{{ $ticket->priority_name }}</span>
                                @else <span class="text-xs text-slate-400">—</span> @endif
                            </td>
                            <td class="whitespace-nowrap px-5 py-3 text-xs text-slate-400 dark:text-navy-400">
                                {{ \Carbon\Carbon::parse($ticket->created_at)->diffForHumans() }}
                            </td>
                        </tr>
                        @empty
                        <tr>
                            <td colspan="7" class="px-5 py-10 text-center">
                                <p class="text-sm text-slate-400">No tickets yet</p>
                            </td>
                        </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>

    </main>
</x-app-layout>
