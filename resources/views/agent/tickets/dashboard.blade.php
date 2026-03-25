<x-app-layout title="My Dashboard" is-sidebar-open="true" is-header-blur="true">

    <main class="main-content w-full px-[var(--margin-x)] pb-10">

        {{-- ── Header ─────────────────────────────────────────────────────── --}}
        <div class="flex flex-col gap-4 py-5 lg:py-6 sm:flex-row sm:items-center sm:justify-between">
            <div>
                <h2 class="text-xl font-semibold text-slate-800 dark:text-navy-50 lg:text-2xl">
                    {{ $greeting }}, {{ auth()->user()->name }} 👋
                </h2>
                <p class="mt-0.5 text-sm text-slate-400 dark:text-navy-300">
                    Here's what's happening with your tickets today, {{ now()->format('l, F j') }}.
                </p>
            </div>
            <div class="flex items-center gap-2">
                <a href="{{ route('agent.tickets/reports') }}"
                   class="btn h-9 rounded-lg border border-slate-300 px-4 text-sm font-medium text-slate-600 hover:bg-slate-100 dark:border-navy-500 dark:text-navy-200 dark:hover:bg-navy-600">
                    <svg xmlns="http://www.w3.org/2000/svg" class="mr-1.5 size-4" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z"/></svg>
                    Reports
                </a>
                <a href="{{ route('agent.tickets/create') }}"
                   class="btn h-9 rounded-lg bg-primary px-4 text-sm font-medium text-white shadow-lg shadow-primary/40 hover:bg-primary-focus dark:bg-accent dark:shadow-accent/40 dark:hover:bg-accent-focus">
                    <svg xmlns="http://www.w3.org/2000/svg" class="mr-1.5 size-4" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/></svg>
                    New Ticket
                </a>
            </div>
        </div>

        {{-- ── Row 1: KPI Cards ────────────────────────────────────────────── --}}
        <div class="grid grid-cols-2 gap-4 sm:grid-cols-3 lg:grid-cols-5">

            @php
                $kpis = [
                    [
                        'label'  => 'Total',
                        'value'  => $totalTickets,
                        'icon'   => 'M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2',
                        'bg'     => 'bg-primary/10', 'text' => 'text-primary',
                        'route'  => route('agent.tickets/index'),
                        'sub'    => $resolutionRate.'% resolved',
                        'subclr' => 'text-slate-400',
                    ],
                    [
                        'label'  => 'Open',
                        'value'  => $totalOpen,
                        'icon'   => 'M8 10h.01M12 10h.01M16 10h.01M9 16H5a2 2 0 01-2-2V6a2 2 0 012-2h14a2 2 0 012 2v8a2 2 0 01-2 2h-5l-3 3v-3z',
                        'bg'     => 'bg-warning/10', 'text' => 'text-warning',
                        'route'  => route('agent.tickets/index', ['view' => 'open']),
                        'sub'    => 'needs attention',
                        'subclr' => 'text-warning',
                    ],
                    [
                        'label'  => 'Pending',
                        'value'  => $totalPending,
                        'icon'   => 'M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0',
                        'bg'     => 'bg-info/10', 'text' => 'text-info',
                        'route'  => route('agent.tickets/index', ['view' => 'pending']),
                        'sub'    => 'awaiting response',
                        'subclr' => 'text-slate-400',
                    ],
                    [
                        'label'  => 'Resolved Today',
                        'value'  => $resolvedToday,
                        'icon'   => 'M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0',
                        'bg'     => 'bg-success/10', 'text' => 'text-success',
                        'route'  => route('agent.tickets/index', ['view' => 'resolved']),
                        'sub'    => 'today',
                        'subclr' => 'text-success',
                    ],
                    [
                        'label'  => 'Overdue',
                        'value'  => $overdue,
                        'icon'   => 'M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z',
                        'bg'     => 'bg-error/10', 'text' => 'text-error',
                        'route'  => route('agent.tickets/index', ['view' => 'overdue']),
                        'sub'    => $overdue > 0 ? 'requires action' : 'all on time',
                        'subclr' => $overdue > 0 ? 'text-error' : 'text-success',
                    ],
                ];
            @endphp

            @foreach($kpis as $kpi)
            <a href="{{ $kpi['route'] }}"
               class="card group rounded-2xl bg-white p-5 shadow transition-shadow hover:shadow-md dark:bg-navy-700">
                <div class="flex items-start justify-between">
                    <div>
                        <p class="text-xs font-medium uppercase tracking-wider text-slate-400 dark:text-navy-300">{{ $kpi['label'] }}</p>
                        <p class="mt-1.5 text-3xl font-bold text-slate-800 dark:text-navy-50">{{ $kpi['value'] }}</p>
                        <p class="mt-1 text-xs {{ $kpi['subclr'] }}">{{ $kpi['sub'] }}</p>
                    </div>
                    <div class="flex size-10 items-center justify-center rounded-xl {{ $kpi['bg'] }} {{ $kpi['text'] }} transition-transform group-hover:scale-110">
                        <svg xmlns="http://www.w3.org/2000/svg" class="size-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.5">
                            <path stroke-linecap="round" stroke-linejoin="round" d="{{ $kpi['icon'] }}"/>
                        </svg>
                    </div>
                </div>
            </a>
            @endforeach
        </div>

        {{-- ── Row 2: Perf Score + Streak + Resolution + 7-day chart ─────── --}}
        <div class="mt-4 grid grid-cols-1 gap-4 lg:grid-cols-3">

            {{-- Performance + Streak side cards --}}
            <div class="flex flex-col gap-4">

                {{-- Performance Score mini-card --}}
                <a href="{{ route('agent.tickets/reports') }}"
                   class="card rounded-2xl bg-gradient-to-br from-primary to-primary-focus p-5 shadow text-white hover:shadow-lg transition-shadow">
                    <div class="flex items-center justify-between">
                        <div>
                            <p class="text-xs font-semibold uppercase tracking-wider text-white/70">Performance Score</p>
                            <div class="mt-1 flex items-end gap-2">
                                <span class="text-4xl font-bold">{{ $performanceScore }}</span>
                                <span class="mb-1 text-sm text-white/70">/ 100</span>
                            </div>
                            <span class="mt-1 inline-flex items-center rounded-full bg-white/20 px-2.5 py-0.5 text-xs font-semibold">
                                {{ $tier['label'] }} Tier
                            </span>
                        </div>
                        <div class="relative size-16">
                            <svg viewBox="0 0 36 36" class="w-full h-full -rotate-90">
                                <circle cx="18" cy="18" r="15.9" fill="none" stroke="rgba(255,255,255,0.2)" stroke-width="3"/>
                                <circle cx="18" cy="18" r="15.9" fill="none" stroke="white" stroke-width="3"
                                        stroke-dasharray="{{ round(($performanceScore/100)*100,1) }} 100"
                                        stroke-linecap="round"/>
                            </svg>
                            <div class="absolute inset-0 flex items-center justify-center">
                                <span class="text-xs font-bold">{{ $performanceScore }}%</span>
                            </div>
                        </div>
                    </div>
                    <p class="mt-3 text-xs text-white/60">Tap to view full report →</p>
                </a>

                {{-- Streak + Resolution Rate side by side --}}
                <div class="grid grid-cols-2 gap-4">
                    <div class="card rounded-2xl bg-white p-4 shadow dark:bg-navy-700 flex flex-col items-center justify-center text-center">
                        <span class="text-2xl">🔥</span>
                        <p class="mt-1 text-2xl font-bold text-slate-800 dark:text-navy-50">{{ $streak }}</p>
                        <p class="text-xs text-slate-400 dark:text-navy-300 mt-0.5">day streak</p>
                        @if($streak >= 3)
                            <span class="mt-1.5 rounded-full bg-amber-100 px-2 py-0.5 text-[10px] font-bold text-amber-700">On Fire!</span>
                        @endif
                    </div>
                    <div class="card rounded-2xl bg-white p-4 shadow dark:bg-navy-700 flex flex-col items-center justify-center text-center">
                        <p class="text-2xl font-bold {{ $resolutionRate >= 75 ? 'text-success' : ($resolutionRate >= 50 ? 'text-warning' : 'text-error') }}">
                            {{ $resolutionRate }}%
                        </p>
                        <p class="text-xs text-slate-400 dark:text-navy-300 mt-0.5">resolution rate</p>
                        <div class="mt-2 w-full h-1.5 rounded-full bg-slate-100 dark:bg-navy-600">
                            <div class="h-1.5 rounded-full {{ $resolutionRate >= 75 ? 'bg-success' : ($resolutionRate >= 50 ? 'bg-warning' : 'bg-error') }}"
                                 style="width:{{ $resolutionRate }}%"></div>
                        </div>
                    </div>
                </div>
            </div>

            {{-- 7-day activity chart --}}
            <div class="card rounded-2xl bg-white p-5 shadow dark:bg-navy-700 lg:col-span-2">
                <div class="flex items-center justify-between mb-4">
                    <h3 class="text-sm font-semibold text-slate-700 dark:text-navy-100">Last 7 Days Activity</h3>
                    <div class="flex items-center gap-3 text-xs text-slate-400">
                        <span class="flex items-center gap-1.5"><span class="inline-block size-2.5 rounded-sm bg-primary"></span>Created</span>
                        <span class="flex items-center gap-1.5"><span class="inline-block size-2.5 rounded-sm bg-success"></span>Resolved</span>
                    </div>
                </div>
                @php
                    $maxBar = max(1, $chartDays->max(fn($d) => max($d['created'], $d['resolved'])));
                @endphp
                <div class="flex items-end gap-1.5 h-28">
                    @foreach($chartDays as $day)
                    <div class="flex flex-1 flex-col items-center gap-0.5">
                        <div class="flex w-full items-end gap-px" style="height:88px;">
                            <div class="flex-1 rounded-t bg-primary/80 hover:bg-primary transition-colors"
                                 style="height:{{ $day['created'] > 0 ? round(($day['created']/$maxBar)*100) : 2 }}%;min-height:{{ $day['created'] > 0 ? '4px' : '0' }};"
                                 title="{{ $day['date'] }}: {{ $day['created'] }} created"></div>
                            <div class="flex-1 rounded-t bg-success/80 hover:bg-success transition-colors"
                                 style="height:{{ $day['resolved'] > 0 ? round(($day['resolved']/$maxBar)*100) : 2 }}%;min-height:{{ $day['resolved'] > 0 ? '4px' : '0' }};"
                                 title="{{ $day['date'] }}: {{ $day['resolved'] }} resolved"></div>
                        </div>
                        <span class="text-[10px] text-slate-400 dark:text-navy-400">{{ $day['label'] }}</span>
                    </div>
                    @endforeach
                </div>
                <div class="mt-3 grid grid-cols-2 gap-3 border-t border-slate-100 pt-3 dark:border-navy-600">
                    <div class="flex items-center gap-2">
                        <div class="flex size-8 items-center justify-center rounded-lg bg-primary/10">
                            <svg class="size-4 text-primary" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M12 4v16m8-8H4"/></svg>
                        </div>
                        <div>
                            <p class="text-xs text-slate-400">Created (7d)</p>
                            <p class="text-sm font-bold text-slate-700 dark:text-navy-100">{{ $chartDays->sum('created') }}</p>
                        </div>
                    </div>
                    <div class="flex items-center gap-2">
                        <div class="flex size-8 items-center justify-center rounded-lg bg-success/10">
                            <svg class="size-4 text-success" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0"/></svg>
                        </div>
                        <div>
                            <p class="text-xs text-slate-400">Resolved (7d)</p>
                            <p class="text-sm font-bold text-slate-700 dark:text-navy-100">{{ $chartDays->sum('resolved') }}</p>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        {{-- ── Row 3: By Status + By Priority + Overdue/Due Soon ──────────── --}}
        <div class="mt-4 grid grid-cols-1 gap-4 sm:grid-cols-2 lg:grid-cols-3">

            {{-- By Status --}}
            <div class="card rounded-2xl bg-white p-5 shadow dark:bg-navy-700">
                <h3 class="mb-4 text-sm font-semibold text-slate-700 dark:text-navy-100">By Status</h3>
                @if($byStatus->isEmpty())
                    <p class="py-6 text-center text-sm text-slate-400">No tickets yet</p>
                @else
                @php $statusTotal = $byStatus->sum('count'); @endphp
                <div class="space-y-3">
                    @foreach($byStatus as $s)
                    <div>
                        <div class="flex items-center justify-between mb-1">
                            <div class="flex items-center gap-2">
                                <span class="inline-block size-2 rounded-full shrink-0" style="background:{{ $s->color ?? '#94a3b8' }}"></span>
                                <span class="text-xs text-slate-600 dark:text-navy-200">{{ $s->name ?? 'Unknown' }}</span>
                            </div>
                            <span class="text-xs font-bold text-slate-700 dark:text-navy-100">{{ $s->count }}</span>
                        </div>
                        <div class="h-1.5 w-full rounded-full bg-slate-100 dark:bg-navy-600">
                            <div class="h-1.5 rounded-full transition-all"
                                 style="background:{{ $s->color ?? '#94a3b8' }};width:{{ $statusTotal > 0 ? round(($s->count/$statusTotal)*100) : 0 }}%"></div>
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
                    <p class="py-6 text-center text-sm text-slate-400">No tickets yet</p>
                @else
                @php $priorityTotal = $byPriority->sum('count'); @endphp
                <div class="space-y-3">
                    @foreach($byPriority as $p)
                    <div>
                        <div class="flex items-center justify-between mb-1">
                            <div class="flex items-center gap-2">
                                <span class="inline-block size-2 rounded-full shrink-0" style="background:{{ $p->color ?? '#94a3b8' }}"></span>
                                <span class="text-xs text-slate-600 dark:text-navy-200">{{ $p->name ?? 'Unknown' }}</span>
                            </div>
                            <span class="text-xs font-bold text-slate-700 dark:text-navy-100">{{ $p->count }}</span>
                        </div>
                        <div class="h-1.5 w-full rounded-full bg-slate-100 dark:bg-navy-600">
                            <div class="h-1.5 rounded-full transition-all"
                                 style="background:{{ $p->color ?? '#94a3b8' }};width:{{ $priorityTotal > 0 ? round(($p->count/$priorityTotal)*100) : 0 }}%"></div>
                        </div>
                    </div>
                    @endforeach
                </div>
                @endif
            </div>

            {{-- Overdue / Due Soon --}}
            <div class="card rounded-2xl bg-white p-5 shadow dark:bg-navy-700">
                <div class="flex items-center justify-between mb-4">
                    <h3 class="text-sm font-semibold text-slate-700 dark:text-navy-100">
                        @if($overdueTickets->isNotEmpty())
                            <span class="inline-flex items-center gap-1.5">
                                <span class="flex size-2 rounded-full bg-error animate-pulse"></span>
                                Overdue
                            </span>
                        @else
                            Due Soon
                        @endif
                    </h3>
                    <a href="{{ route('agent.tickets/index', ['view' => 'overdue']) }}" class="text-xs text-primary hover:underline">View all</a>
                </div>
                @php $urgentList = $overdueTickets->isNotEmpty() ? $overdueTickets : $dueSoon; @endphp
                @if($urgentList->isEmpty())
                    <div class="flex flex-col items-center justify-center py-6 text-center">
                        <div class="flex size-10 items-center justify-center rounded-full bg-success/10">
                            <svg class="size-5 text-success" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0"/></svg>
                        </div>
                        <p class="mt-2 text-sm font-medium text-slate-600 dark:text-navy-200">All clear!</p>
                        <p class="text-xs text-slate-400">No overdue or due-soon tickets</p>
                    </div>
                @else
                <div class="space-y-2.5">
                    @foreach($urgentList as $t)
                    <a href="{{ route('agent.tickets/show', $t->uuid) }}"
                       class="flex items-start gap-3 rounded-xl p-2.5 hover:bg-slate-50 dark:hover:bg-navy-600 transition-colors">
                        <div class="mt-0.5 flex size-7 shrink-0 items-center justify-center rounded-full {{ $overdueTickets->isNotEmpty() ? 'bg-error/10' : 'bg-warning/10' }}">
                            <svg class="size-3.5 {{ $overdueTickets->isNotEmpty() ? 'text-error' : 'text-warning' }}" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0"/></svg>
                        </div>
                        <div class="min-w-0 flex-1">
                            <p class="truncate text-xs font-medium text-slate-700 dark:text-navy-100">{{ $t->subject }}</p>
                            <p class="mt-0.5 text-[10px] {{ $overdueTickets->isNotEmpty() ? 'text-error' : 'text-warning' }}">
                                {{ \Carbon\Carbon::parse($t->due_at)->diffForHumans() }}
                            </p>
                        </div>
                        @if($t->priority_name)
                        <span class="shrink-0 rounded-full px-1.5 py-0.5 text-[10px] font-medium text-white"
                              style="background:{{ $t->priority_color ?? '#94a3b8' }}">{{ $t->priority_name }}</span>
                        @endif
                    </a>
                    @endforeach
                </div>
                @endif
            </div>
        </div>

        {{-- ── Row 4: Recent Tickets ─────────────────────────────────────── --}}
        <div class="mt-4 card rounded-2xl bg-white shadow dark:bg-navy-700">
            <div class="flex items-center justify-between px-5 pt-5 pb-3">
                <h3 class="text-sm font-semibold text-slate-700 dark:text-navy-100">Recent Tickets</h3>
                <a href="{{ route('agent.tickets/index') }}" class="text-xs text-primary hover:underline">View all</a>
            </div>
            <div class="overflow-x-auto">
                <table class="w-full text-left">
                    <thead>
                        <tr class="border-y border-slate-100 dark:border-navy-600">
                            <th class="px-5 py-3 text-[11px] font-semibold uppercase tracking-wider text-slate-400 dark:text-navy-300">Ticket</th>
                            <th class="px-5 py-3 text-[11px] font-semibold uppercase tracking-wider text-slate-400 dark:text-navy-300">Subject</th>
                            <th class="px-5 py-3 text-[11px] font-semibold uppercase tracking-wider text-slate-400 dark:text-navy-300">Customer</th>
                            <th class="px-5 py-3 text-[11px] font-semibold uppercase tracking-wider text-slate-400 dark:text-navy-300">Status</th>
                            <th class="px-5 py-3 text-[11px] font-semibold uppercase tracking-wider text-slate-400 dark:text-navy-300">Priority</th>
                            <th class="px-5 py-3 text-[11px] font-semibold uppercase tracking-wider text-slate-400 dark:text-navy-300">Created</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-100 dark:divide-navy-600">
                        @forelse($recentTickets as $ticket)
                        <tr class="hover:bg-slate-50 dark:hover:bg-navy-600/50 transition-colors">
                            <td class="whitespace-nowrap px-5 py-3">
                                <a href="{{ route('agent.tickets/show', $ticket->uuid) }}"
                                   class="font-mono text-xs font-semibold text-primary hover:underline dark:text-accent-light">{{ $ticket->ticket_no }}</a>
                            </td>
                            <td class="px-5 py-3 max-w-[220px]">
                                <a href="{{ route('agent.tickets/show', $ticket->uuid) }}"
                                   class="block truncate text-sm text-slate-700 hover:text-primary dark:text-navy-100 dark:hover:text-accent-light">{{ $ticket->subject }}</a>
                            </td>
                            <td class="whitespace-nowrap px-5 py-3 text-sm text-slate-500 dark:text-navy-300">
                                {{ $ticket->customer_name ?? '—' }}
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
                            <td colspan="6" class="px-5 py-10 text-center">
                                <div class="flex flex-col items-center gap-2">
                                    <div class="flex size-12 items-center justify-center rounded-full bg-slate-100 dark:bg-navy-600">
                                        <svg class="size-6 text-slate-400" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2"/></svg>
                                    </div>
                                    <p class="text-sm font-medium text-slate-500 dark:text-navy-300">No tickets yet</p>
                                    <a href="{{ route('agent.tickets/create') }}" class="text-xs text-primary hover:underline">Create your first ticket →</a>
                                </div>
                            </td>
                        </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>

    </main>
</x-app-layout>
