<x-app-layout title="Admin Dashboard" is-sidebar-open="true" is-header-blur="true">

<main class="main-content w-full px-[var(--margin-x)] pb-10">

    {{-- ── Header ──────────────────────────────────────────────────────────── --}}
    <div class="flex flex-col gap-4 py-5 lg:py-6 sm:flex-row sm:items-center sm:justify-between">
        <div>
            <h2 class="text-xl font-semibold text-slate-800 dark:text-navy-50 lg:text-2xl">
                Admin Dashboard
            </h2>
            <p class="mt-0.5 text-sm text-slate-400 dark:text-navy-300">
                {{ now()->format('l, F j, Y') }} · System overview
            </p>
        </div>
        <div class="flex items-center gap-2 flex-wrap">
            <a href="{{ route('users/create') }}"
               class="btn h-9 rounded-lg border border-slate-300 px-4 text-sm font-medium text-slate-700 hover:bg-slate-50 dark:border-navy-450 dark:text-navy-100 dark:hover:bg-navy-600">
                <svg xmlns="http://www.w3.org/2000/svg" class="mr-1.5 size-4" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M18 9v3m0 0v3m0-3h3m-3 0h-3m-2-5a4 4 0 11-8 0 4 4 0 018 0zM3 20a6 6 0 0112 0v1H3v-1z"/></svg>
                Add User
            </a>
            <a href="{{ route('tickets/create') }}"
               class="btn h-9 rounded-lg bg-primary px-4 text-sm font-medium text-white shadow-lg shadow-primary/40 hover:bg-primary-focus dark:bg-accent dark:shadow-accent/40 dark:hover:bg-accent-focus">
                <svg xmlns="http://www.w3.org/2000/svg" class="mr-1.5 size-4" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/></svg>
                New Ticket
            </a>
        </div>
    </div>

    {{-- ── Row 1: System KPIs ───────────────────────────────────────────────── --}}
    <div class="grid grid-cols-2 gap-4 sm:grid-cols-3 lg:grid-cols-6">
        @php
            $sysKpis = [
                ['label'=>'Total Users',    'value'=> number_format($totalUsers),    'sub'=>'+'.number_format($newUsers30d).' last 30d',  'subclr'=>'text-success',  'bg'=>'bg-primary/10', 'tc'=>'text-primary',  'icon'=>'M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0zm6 3a2 2 0 11-4 0 2 2 0 014 0zM7 10a2 2 0 11-4 0 2 2 0 014 0z', 'route'=>route('users/index')],
                ['label'=>'Active Agents',  'value'=> number_format($activeAgents),  'sub'=>number_format($totalAgents).' total agents',  'subclr'=>'text-slate-400','bg'=>'bg-info/10',    'tc'=>'text-info',     'icon'=>'M9 12l2 2 4-4m5.618-4.016A11.955 11.955 0 0112 2.944a11.955 11.955 0 01-8.618 3.04A12.02 12.02 0 003 9c0 5.591 3.824 10.29 9 11.622 5.176-1.332 9-6.03 9-11.622 0-1.042-.133-2.052-.382-3.016z', 'route'=>route('users/index',['role'=>'agent'])],
                ['label'=>'Customers',      'value'=> number_format($totalCustomers), 'sub'=>'+'.number_format($newCustomers30d).' last 30d', 'subclr'=>'text-success', 'bg'=>'bg-secondary/10','tc'=>'text-secondary','icon'=>'M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z', 'route'=>route('users/index',['role'=>'customer'])],
                ['label'=>'Branches',       'value'=> number_format($totalBranches),  'sub'=>'active locations',                            'subclr'=>'text-slate-400','bg'=>'bg-warning/10', 'tc'=>'text-warning',  'icon'=>'M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4', 'route'=>route('config/branches')],
                ['label'=>'Departments',    'value'=> number_format($totalDepts),     'sub'=>'active depts',                                'subclr'=>'text-slate-400','bg'=>'bg-accent/10',  'tc'=>'text-accent',   'icon'=>'M4 6a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2H6a2 2 0 01-2-2V6zM14 6a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2h-2a2 2 0 01-2-2V6zM4 16a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2H6a2 2 0 01-2-2v-2zM14 16a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2h-2a2 2 0 01-2-2v-2z', 'route'=>route('config/departments')],
                ['label'=>'New Today',      'value'=> number_format($newUsersToday),  'sub'=>number_format($newUsers7d).' this week',       'subclr'=>'text-info',    'bg'=>'bg-success/10', 'tc'=>'text-success',  'icon'=>'M18 9v3m0 0v3m0-3h3m-3 0h-3m-2-5a4 4 0 11-8 0 4 4 0 018 0zM3 20a6 6 0 0112 0v1H3v-1z', 'route'=>route('users/index')],
            ];
        @endphp
        @foreach($sysKpis as $k)
        <a href="{{ $k['route'] }}" class="card group rounded-2xl bg-white p-4 shadow transition-shadow hover:shadow-md dark:bg-navy-700">
            <div class="flex size-9 items-center justify-center rounded-xl {{ $k['bg'] }} {{ $k['tc'] }} transition-transform group-hover:scale-110">
                <svg xmlns="http://www.w3.org/2000/svg" class="size-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.5">
                    <path stroke-linecap="round" stroke-linejoin="round" d="{{ $k['icon'] }}"/>
                </svg>
            </div>
            <p class="mt-3 text-2xl font-bold text-slate-800 dark:text-navy-50">{{ $k['value'] }}</p>
            <p class="text-xs font-medium text-slate-500 dark:text-navy-300">{{ $k['label'] }}</p>
            <p class="mt-0.5 text-[11px] {{ $k['subclr'] }}">{{ $k['sub'] }}</p>
        </a>
        @endforeach
    </div>

    {{-- ── Row 2: Ticket KPIs ───────────────────────────────────────────────── --}}
    <div class="mt-4 grid grid-cols-2 gap-4 sm:grid-cols-3 lg:grid-cols-6">
        @php
            $tktKpis = [
                ['label'=>'Total Tickets',  'value'=>$totalTickets,  'sub'=>$resolutionRate.'% resolved',                                      'subclr'=>'text-slate-400',                        'bg'=>'bg-primary/10', 'tc'=>'text-primary',  'icon'=>'M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2', 'route'=>route('tickets/index')],
                ['label'=>'Open',           'value'=>$totalOpen,     'sub'=>'need attention',                                                   'subclr'=>'text-warning',                          'bg'=>'bg-warning/10', 'tc'=>'text-warning',  'icon'=>'M8 10h.01M12 10h.01M16 10h.01M9 16H5a2 2 0 01-2-2V6a2 2 0 012-2h14a2 2 0 012 2v8a2 2 0 01-2 2h-5l-3 3v-3z', 'route'=>route('tickets/index',['view'=>'open'])],
                ['label'=>'Resolved',       'value'=>$totalResolved, 'sub'=>$resolvedToday.' today',                                            'subclr'=>'text-success',                          'bg'=>'bg-success/10', 'tc'=>'text-success',  'icon'=>'M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0', 'route'=>route('tickets/index',['view'=>'resolved'])],
                ['label'=>'Overdue',        'value'=>$overdue,       'sub'=>$overdue>0?'requires action':'all on time',                         'subclr'=>$overdue>0?'text-error':'text-success',  'bg'=>'bg-error/10',   'tc'=>'text-error',    'icon'=>'M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z', 'route'=>route('tickets/index',['view'=>'overdue'])],
                ['label'=>'Unassigned',     'value'=>$unassigned,    'sub'=>$unassigned>0?'need agent':'all assigned',                          'subclr'=>$unassigned>0?'text-warning':'text-success','bg'=>'bg-slate-100','tc'=>'text-slate-500 dark:text-navy-200','icon'=>'M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z', 'route'=>route('tickets/index',['view'=>'unassigned'])],
                ['label'=>'This Week',      'value'=>$ticketsThisWeek,'sub'=>'tickets created',                                                 'subclr'=>'text-info',                             'bg'=>'bg-info/10',    'tc'=>'text-info',     'icon'=>'M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z', 'route'=>route('tickets/index')],
            ];
        @endphp
        @foreach($tktKpis as $k)
        <a href="{{ $k['route'] }}" class="card group rounded-2xl bg-white p-4 shadow transition-shadow hover:shadow-md dark:bg-navy-700">
            <div class="flex size-9 items-center justify-center rounded-xl {{ $k['bg'] }} {{ $k['tc'] }} transition-transform group-hover:scale-110">
                <svg xmlns="http://www.w3.org/2000/svg" class="size-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.5">
                    <path stroke-linecap="round" stroke-linejoin="round" d="{{ $k['icon'] }}"/>
                </svg>
            </div>
            <p class="mt-3 text-2xl font-bold text-slate-800 dark:text-navy-50">{{ $k['value'] }}</p>
            <p class="text-xs font-medium text-slate-500 dark:text-navy-300">{{ $k['label'] }}</p>
            <p class="mt-0.5 text-[11px] {{ $k['subclr'] }}">{{ $k['sub'] }}</p>
        </a>
        @endforeach
    </div>

    {{-- ── Row 3: SLA Metrics ───────────────────────────────────────────────── --}}
    <div class="mt-4 grid grid-cols-2 gap-4 lg:grid-cols-4">
        @php
            $slaCards = [
                ['label'=>'Response SLA',    'value'=>$slaResponseCompliance !== null ? $slaResponseCompliance.'%' : 'N/A',  'sub'=>$slaResponseHours>0 ? 'Target: '.$slaResponseHours.'h first response' : 'No SLA configured', 'color'=>$slaResponseCompliance >= 90 ? 'text-success' : ($slaResponseCompliance >= 70 ? 'text-warning' : 'text-error'), 'bg'=>'bg-success/10', 'tc'=>'text-success'],
                ['label'=>'Resolution SLA',  'value'=>$slaResolutionCompliance !== null ? $slaResolutionCompliance.'%' : 'N/A','sub'=>$slaResolutionHours>0 ? 'Target: '.$slaResolutionHours.'h resolution' : 'No SLA configured',  'color'=>$slaResolutionCompliance >= 90 ? 'text-success' : ($slaResolutionCompliance >= 70 ? 'text-warning' : 'text-error'), 'bg'=>'bg-info/10', 'tc'=>'text-info'],
                ['label'=>'Resolution Rate', 'value'=>$resolutionRate.'%', 'sub'=>$totalResolved.' of '.$totalTickets.' resolved',  'color'=>$resolutionRate >= 70 ? 'text-success' : ($resolutionRate >= 40 ? 'text-warning' : 'text-error'), 'bg'=>'bg-primary/10', 'tc'=>'text-primary'],
                ['label'=>'Resolved Today',  'value'=>$resolvedToday,      'sub'=>'tickets closed today', 'color'=>'text-success', 'bg'=>'bg-success/10', 'tc'=>'text-success'],
            ];
        @endphp
        @foreach($slaCards as $c)
        <div class="card rounded-2xl bg-white p-4 shadow dark:bg-navy-700">
            <div class="flex items-center justify-between">
                <p class="text-xs font-medium uppercase tracking-wide text-slate-400 dark:text-navy-300">{{ $c['label'] }}</p>
                <div class="flex size-8 items-center justify-center rounded-lg {{ $c['bg'] }} {{ $c['tc'] }}">
                    <svg xmlns="http://www.w3.org/2000/svg" class="size-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.5"><path stroke-linecap="round" stroke-linejoin="round" d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z"/></svg>
                </div>
            </div>
            <p class="mt-3 text-3xl font-bold {{ $c['color'] }}">{{ $c['value'] }}</p>
            <p class="mt-1 text-xs text-slate-400 dark:text-navy-300">{{ $c['sub'] }}</p>
        </div>
        @endforeach
    </div>

    {{-- ── Row 4: 30-day Ticket Chart + User Growth ─────────────────────────── --}}
    <div class="mt-4 grid grid-cols-1 gap-4 lg:grid-cols-3">

        {{-- Ticket Volume Chart --}}
        <div class="card lg:col-span-2 rounded-2xl bg-white shadow dark:bg-navy-700">
            <div class="flex items-center justify-between border-b border-slate-100 px-5 py-4 dark:border-navy-600">
                <div>
                    <h3 class="font-semibold text-slate-700 dark:text-navy-100">Ticket Volume (30 days)</h3>
                    <p class="text-xs text-slate-400 dark:text-navy-300">Created vs Resolved per day</p>
                </div>
                <div class="flex items-center gap-3 text-xs">
                    <span class="flex items-center gap-1"><span class="size-2.5 rounded-full bg-primary inline-block"></span> Created</span>
                    <span class="flex items-center gap-1"><span class="size-2.5 rounded-full bg-success inline-block"></span> Resolved</span>
                </div>
            </div>
            <div class="p-4">
                @php
                    $maxVal = max(1, $chartDays->max(fn($d) => max($d['created'], $d['resolved'])));
                    $showEvery = 5;
                @endphp
                <div class="overflow-x-auto">
                    <div class="flex items-end gap-[3px] h-40" style="min-width: {{ $chartDays->count() * 16 }}px">
                        @foreach($chartDays as $i => $day)
                        <div class="flex flex-1 flex-col items-center gap-0.5 group relative"
                             x-data x-tooltip.placement.top="'{{ $day['label'] }}: {{ $day['created'] }} created, {{ $day['resolved'] }} resolved'">
                            <div class="flex items-end gap-px w-full justify-center h-full">
                                <div class="w-[5px] rounded-t bg-primary/80 transition-all group-hover:bg-primary"
                                     style="height: {{ $maxVal > 0 ? round(($day['created']/$maxVal)*100) : 0 }}%"></div>
                                <div class="w-[5px] rounded-t bg-success/80 transition-all group-hover:bg-success"
                                     style="height: {{ $maxVal > 0 ? round(($day['resolved']/$maxVal)*100) : 0 }}%"></div>
                            </div>
                        </div>
                        @endforeach
                    </div>
                    <div class="mt-1 flex gap-[3px] overflow-hidden" style="min-width: {{ $chartDays->count() * 16 }}px">
                        @foreach($chartDays as $i => $day)
                        <div class="flex-1 text-center text-[9px] text-slate-400 dark:text-navy-400 truncate">
                            @if($i % $showEvery === 0) {{ $day['label'] }} @endif
                        </div>
                        @endforeach
                    </div>
                </div>
            </div>
        </div>

        {{-- Ticket Distributions (Status + Priority) --}}
        <div class="card rounded-2xl bg-white shadow dark:bg-navy-700">
            <div class="border-b border-slate-100 px-5 py-4 dark:border-navy-600">
                <h3 class="font-semibold text-slate-700 dark:text-navy-100">Ticket Breakdown</h3>
                <p class="text-xs text-slate-400 dark:text-navy-300">By status and priority</p>
            </div>
            <div class="p-4 space-y-4">
                {{-- By Status --}}
                <div>
                    <p class="mb-2 text-xs font-medium uppercase tracking-wide text-slate-400 dark:text-navy-300">By Status</p>
                    @php $statusTotal = $byStatus->sum('count'); @endphp
                    @foreach($byStatus as $s)
                    <div class="mb-1.5">
                        <div class="flex justify-between text-xs mb-0.5">
                            <span class="font-medium text-slate-600 dark:text-navy-200">{{ $s->name }}</span>
                            <span class="text-slate-400">{{ $s->count }}</span>
                        </div>
                        <div class="h-1.5 rounded-full bg-slate-100 dark:bg-navy-600">
                            <div class="h-1.5 rounded-full transition-all"
                                 style="width:{{ $statusTotal > 0 ? round(($s->count/$statusTotal)*100) : 0 }}%; background-color:{{ $s->color }}"></div>
                        </div>
                    </div>
                    @endforeach
                </div>
                {{-- By Priority --}}
                <div>
                    <p class="mb-2 text-xs font-medium uppercase tracking-wide text-slate-400 dark:text-navy-300">By Priority</p>
                    @php $prioTotal = $byPriority->sum('count'); @endphp
                    @foreach($byPriority as $p)
                    <div class="mb-1.5">
                        <div class="flex justify-between text-xs mb-0.5">
                            <span class="font-medium text-slate-600 dark:text-navy-200">{{ $p->name }}</span>
                            <span class="text-slate-400">{{ $p->count }}</span>
                        </div>
                        <div class="h-1.5 rounded-full bg-slate-100 dark:bg-navy-600">
                            <div class="h-1.5 rounded-full transition-all"
                                 style="width:{{ $prioTotal > 0 ? round(($p->count/$prioTotal)*100) : 0 }}%; background-color:{{ $p->color }}"></div>
                        </div>
                    </div>
                    @endforeach
                </div>
            </div>
        </div>
    </div>

    {{-- ── Row 5: Agent Leaderboard + User Growth Chart ────────────────────── --}}
    <div class="mt-4 grid grid-cols-1 gap-4 lg:grid-cols-2">

        {{-- Agent Leaderboard --}}
        <div class="card rounded-2xl bg-white shadow dark:bg-navy-700">
            <div class="flex items-center justify-between border-b border-slate-100 px-5 py-4 dark:border-navy-600">
                <div>
                    <h3 class="font-semibold text-slate-700 dark:text-navy-100">Agent Leaderboard</h3>
                    <p class="text-xs text-slate-400 dark:text-navy-300">Top performers by score</p>
                </div>
                <a href="{{ route('tickets/settings-agents') }}" class="text-xs text-primary hover:text-primary-focus dark:text-accent-light">All agents →</a>
            </div>
            <div class="divide-y divide-slate-100 dark:divide-navy-600">
                @forelse($agentLeaderboard as $i => $agent)
                <div class="flex items-center gap-3 px-5 py-3">
                    <div class="w-6 text-center text-xs font-bold {{ $i === 0 ? 'text-yellow-500' : ($i === 1 ? 'text-slate-400' : ($i === 2 ? 'text-amber-600' : 'text-slate-300 dark:text-navy-400')) }}">
                        {{ $i === 0 ? '🥇' : ($i === 1 ? '🥈' : ($i === 2 ? '🥉' : '#'.($i+1))) }}
                    </div>
                    <div class="is-initial flex size-8 shrink-0 items-center justify-center rounded-full bg-primary/10 text-xs font-semibold text-primary dark:bg-accent/10 dark:text-accent-light">
                        {{ strtoupper(substr($agent->name, 0, 2)) }}
                    </div>
                    <div class="min-w-0 flex-1">
                        <p class="truncate text-sm font-medium text-slate-700 dark:text-navy-100">{{ $agent->name }}</p>
                        <div class="mt-0.5 flex items-center gap-2">
                            <div class="h-1.5 flex-1 rounded-full bg-slate-100 dark:bg-navy-600">
                                <div class="h-1.5 rounded-full bg-primary transition-all dark:bg-accent" style="width:{{ $agent->score }}%"></div>
                            </div>
                        </div>
                    </div>
                    <div class="text-right shrink-0">
                        <p class="text-sm font-bold text-slate-700 dark:text-navy-100">{{ $agent->score }}<span class="text-xs font-normal text-slate-400">/100</span></p>
                        <p class="text-[11px] text-slate-400">{{ $agent->total }} tickets · {{ $agent->resolution_rate }}%</p>
                    </div>
                </div>
                @empty
                <div class="px-5 py-8 text-center text-sm text-slate-400">No agent data yet.</div>
                @endforelse
            </div>
        </div>

        {{-- User Growth Chart --}}
        <div class="card rounded-2xl bg-white shadow dark:bg-navy-700">
            <div class="flex items-center justify-between border-b border-slate-100 px-5 py-4 dark:border-navy-600">
                <div>
                    <h3 class="font-semibold text-slate-700 dark:text-navy-100">User Growth (30 days)</h3>
                    <p class="text-xs text-slate-400 dark:text-navy-300">New customers & agents per day</p>
                </div>
                <div class="flex items-center gap-3 text-xs">
                    <span class="flex items-center gap-1"><span class="size-2.5 rounded-full bg-secondary inline-block"></span> Customers</span>
                    <span class="flex items-center gap-1"><span class="size-2.5 rounded-full bg-info inline-block"></span> Agents</span>
                </div>
            </div>
            <div class="p-4">
                @php
                    $gMax = max(1, $growthDays->max(fn($d) => max($d['customers'], $d['agents'])));
                @endphp
                <div class="overflow-x-auto">
                    <div class="flex items-end gap-[3px] h-40" style="min-width: {{ $growthDays->count() * 16 }}px">
                        @foreach($growthDays as $day)
                        <div class="flex flex-1 flex-col items-center gap-0.5 group relative"
                             x-data x-tooltip.placement.top="'{{ $day['label'] }}: {{ $day['customers'] }} customers, {{ $day['agents'] }} agents'">
                            <div class="flex items-end gap-px w-full justify-center h-full">
                                <div class="w-[5px] rounded-t bg-secondary/70 transition-all group-hover:bg-secondary"
                                     style="height: {{ $gMax > 0 ? round(($day['customers']/$gMax)*100) : 0 }}%"></div>
                                <div class="w-[5px] rounded-t bg-info/70 transition-all group-hover:bg-info"
                                     style="height: {{ $gMax > 0 ? round(($day['agents']/$gMax)*100) : 0 }}%"></div>
                            </div>
                        </div>
                        @endforeach
                    </div>
                    <div class="mt-1 flex gap-[3px]" style="min-width: {{ $growthDays->count() * 16 }}px">
                        @foreach($growthDays as $i => $day)
                        <div class="flex-1 text-center text-[9px] text-slate-400 dark:text-navy-400 truncate">
                            @if($i % 5 === 0) {{ $day['label'] }} @endif
                        </div>
                        @endforeach
                    </div>
                </div>
                {{-- Summary row --}}
                <div class="mt-4 grid grid-cols-3 gap-3 border-t border-slate-100 pt-4 dark:border-navy-600">
                    <div class="text-center">
                        <p class="text-lg font-bold text-slate-700 dark:text-navy-100">{{ number_format($totalUsers) }}</p>
                        <p class="text-[11px] text-slate-400">Total Users</p>
                    </div>
                    <div class="text-center">
                        <p class="text-lg font-bold text-slate-700 dark:text-navy-100">{{ number_format($newUsers30d) }}</p>
                        <p class="text-[11px] text-slate-400">New (30d)</p>
                    </div>
                    <div class="text-center">
                        <p class="text-lg font-bold text-slate-700 dark:text-navy-100">{{ number_format($activeAgents) }}</p>
                        <p class="text-[11px] text-slate-400">Active Agents</p>
                    </div>
                </div>
            </div>
        </div>
    </div>

    {{-- ── Row 6: Recent Tickets + Recent Customers ─────────────────────────── --}}
    <div class="mt-4 grid grid-cols-1 gap-4 lg:grid-cols-2">

        {{-- Recent Tickets --}}
        <div class="card rounded-2xl bg-white shadow dark:bg-navy-700">
            <div class="flex items-center justify-between border-b border-slate-100 px-5 py-4 dark:border-navy-600">
                <div>
                    <h3 class="font-semibold text-slate-700 dark:text-navy-100">Recent Tickets</h3>
                    <p class="text-xs text-slate-400 dark:text-navy-300">Latest 8 created</p>
                </div>
                <a href="{{ route('tickets/index') }}" class="text-xs text-primary hover:text-primary-focus dark:text-accent-light">View all →</a>
            </div>
            <div class="divide-y divide-slate-100 dark:divide-navy-600">
                @forelse($recentTickets as $t)
                <a href="{{ route('tickets/show', $t->uuid) }}"
                   class="flex items-start gap-3 px-5 py-3 transition-colors hover:bg-slate-50 dark:hover:bg-navy-600">
                    <div class="mt-0.5 shrink-0">
                        <span class="inline-block rounded-full px-2 py-0.5 text-[10px] font-semibold text-white"
                              style="background-color:{{ $t->priority_color ?? '#94a3b8' }}">
                            {{ $t->priority_name ?? '–' }}
                        </span>
                    </div>
                    <div class="min-w-0 flex-1">
                        <p class="truncate text-sm font-medium text-slate-700 dark:text-navy-100">{{ $t->subject }}</p>
                        <p class="text-[11px] text-slate-400">{{ $t->ticket_no }} · {{ $t->customer_name ?? 'Unknown' }}</p>
                    </div>
                    <div class="shrink-0 text-right">
                        <span class="inline-block rounded-full px-2 py-0.5 text-[10px] font-semibold text-white"
                              style="background-color:{{ $t->status_color ?? '#94a3b8' }}">
                            {{ $t->status_name ?? '–' }}
                        </span>
                        <p class="mt-0.5 text-[10px] text-slate-400">{{ \Carbon\Carbon::parse($t->created_at)->diffForHumans() }}</p>
                    </div>
                </a>
                @empty
                <div class="px-5 py-8 text-center text-sm text-slate-400">No tickets yet.</div>
                @endforelse
            </div>
        </div>

        {{-- Recent Customers --}}
        <div class="card rounded-2xl bg-white shadow dark:bg-navy-700">
            <div class="flex items-center justify-between border-b border-slate-100 px-5 py-4 dark:border-navy-600">
                <div>
                    <h3 class="font-semibold text-slate-700 dark:text-navy-100">Recent Customers</h3>
                    <p class="text-xs text-slate-400 dark:text-navy-300">Latest 6 registered</p>
                </div>
                <a href="{{ route('users/index', ['role' => 'customer']) }}" class="text-xs text-primary hover:text-primary-focus dark:text-accent-light">View all →</a>
            </div>
            <div class="divide-y divide-slate-100 dark:divide-navy-600">
                @forelse($recentCustomers as $c)
                <div class="flex items-center gap-3 px-5 py-3">
                    <div class="is-initial flex size-9 shrink-0 items-center justify-center rounded-full bg-secondary/10 text-sm font-semibold text-secondary dark:bg-secondary/20">
                        {{ strtoupper(substr($c->name, 0, 2)) }}
                    </div>
                    <div class="min-w-0 flex-1">
                        <p class="truncate text-sm font-medium text-slate-700 dark:text-navy-100">{{ $c->name }}</p>
                        <p class="truncate text-[11px] text-slate-400">{{ $c->email ?? $c->phone ?? 'No contact' }}{{ $c->company ? ' · '.$c->company : '' }}</p>
                    </div>
                    <div class="shrink-0 text-right">
                        <span class="inline-block rounded-full px-2 py-0.5 text-[10px] font-medium {{ $c->status ? 'bg-success/10 text-success' : 'bg-error/10 text-error' }}">
                            {{ $c->status ? 'Active' : 'Inactive' }}
                        </span>
                        <p class="mt-0.5 text-[10px] text-slate-400">{{ \Carbon\Carbon::parse($c->created_at)->diffForHumans() }}</p>
                    </div>
                </div>
                @empty
                <div class="px-5 py-8 text-center text-sm text-slate-400">No customers yet.</div>
                @endforelse
            </div>
            <div class="border-t border-slate-100 px-5 py-3 dark:border-navy-600">
                <a href="{{ route('users/create') }}"
                   class="flex items-center justify-center gap-1.5 text-xs font-medium text-primary hover:text-primary-focus dark:text-accent-light">
                    <svg xmlns="http://www.w3.org/2000/svg" class="size-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M12 4v16m8-8H4"/></svg>
                    Add new customer
                </a>
            </div>
        </div>
    </div>

</main>

</x-app-layout>
