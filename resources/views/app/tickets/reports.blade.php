<x-app-layout title="Ticket Reports" is-sidebar-open="true" is-header-blur="true">

    <main class="main-content w-full px-[var(--margin-x)] pb-8">
        <div class="flex items-center justify-between py-5 lg:py-6">
            <div class="flex items-center space-x-4">
                <h2 class="text-xl font-medium text-slate-800 dark:text-navy-50 lg:text-2xl">Reports & Analytics</h2>
                <div class="hidden h-full py-1 sm:flex"><div class="h-full w-px bg-slate-300 dark:bg-navy-600"></div></div>
                <ul class="hidden flex-wrap items-center space-x-2 sm:flex">
                    <li class="flex items-center space-x-2">
                        <a class="text-primary transition-colors hover:text-primary-focus dark:text-accent-light dark:hover:text-accent" href="{{ route('tickets/dashboard') }}">Tickets</a>
                        <svg xmlns="http://www.w3.org/2000/svg" class="size-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/></svg>
                    </li>
                    <li>Reports</li>
                </ul>
            </div>
        </div>

        {{-- Date Range Filter --}}
        <div class="card p-4 sm:p-5">
            <form method="GET" action="{{ route('tickets/reports') }}" class="flex flex-wrap items-end gap-4">
                <label class="block">
                    <span class="text-sm font-medium text-slate-600 dark:text-navy-100">From</span>
                    <input class="form-input mt-1.5 rounded-lg border border-slate-300 bg-transparent px-3 py-2 hover:border-slate-400 focus:border-primary dark:border-navy-450 dark:hover:border-navy-400 dark:focus:border-accent" type="date" name="date_from" value="{{ $from }}">
                </label>
                <label class="block">
                    <span class="text-sm font-medium text-slate-600 dark:text-navy-100">To</span>
                    <input class="form-input mt-1.5 rounded-lg border border-slate-300 bg-transparent px-3 py-2 hover:border-slate-400 focus:border-primary dark:border-navy-450 dark:hover:border-navy-400 dark:focus:border-accent" type="date" name="date_to" value="{{ $to }}">
                </label>
                <button type="submit" class="btn bg-primary font-medium text-white hover:bg-primary-focus dark:bg-accent dark:hover:bg-accent-focus">
                    <svg xmlns="http://www.w3.org/2000/svg" class="mr-1.5 size-4" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 4a1 1 0 011-1h16a1 1 0 011 1v2.586a1 1 0 01-.293.707l-6.414 6.414a1 1 0 00-.293.707V17l-4 4v-6.586a1 1 0 00-.293-.707L3.293 7.293A1 1 0 013 6.586V4z"/></svg>
                    Apply
                </button>
            </form>
        </div>

        {{-- SLA Overview --}}
        <div class="mt-4 grid grid-cols-1 gap-4 sm:grid-cols-3">
            <div class="card p-4 sm:p-5">
                <div class="flex items-center justify-between">
                    <div>
                        <p class="text-sm+ text-slate-500 dark:text-navy-200">Total Tickets</p>
                        <p class="mt-1 text-2xl font-semibold text-slate-700 dark:text-navy-100">{{ number_format($slaMetrics['total']) }}</p>
                    </div>
                    <div class="flex size-12 items-center justify-center rounded-full bg-primary/10 text-primary dark:bg-accent/10 dark:text-accent-light">
                        <svg xmlns="http://www.w3.org/2000/svg" class="size-6" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2"/></svg>
                    </div>
                </div>
                <p class="mt-2 text-xs text-slate-400">In selected date range</p>
            </div>
            <div class="card p-4 sm:p-5">
                <div class="flex items-center justify-between">
                    <div>
                        <p class="text-sm+ text-slate-500 dark:text-navy-200">Avg First Response</p>
                        <p class="mt-1 text-2xl font-semibold text-slate-700 dark:text-navy-100">{{ $slaMetrics['avg_first_response_hours'] ?? '—' }}<span class="text-sm font-normal text-slate-400 ml-1">hrs</span></p>
                    </div>
                    <div class="flex size-12 items-center justify-center rounded-full {{ ($slaResponseHours > 0 && ($slaMetrics['avg_first_response_hours'] ?? 0) > $slaResponseHours) ? 'bg-error/10 text-error' : 'bg-success/10 text-success' }}">
                        <svg xmlns="http://www.w3.org/2000/svg" class="size-6" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                    </div>
                </div>
                <p class="mt-2 text-xs text-slate-400">SLA target: {{ $slaResponseHours > 0 ? $slaResponseHours . ' hrs' : 'Not set' }}</p>
            </div>
            <div class="card p-4 sm:p-5">
                <div class="flex items-center justify-between">
                    <div>
                        <p class="text-sm+ text-slate-500 dark:text-navy-200">Avg Resolution</p>
                        <p class="mt-1 text-2xl font-semibold text-slate-700 dark:text-navy-100">{{ $slaMetrics['avg_resolution_hours'] ?? '—' }}<span class="text-sm font-normal text-slate-400 ml-1">hrs</span></p>
                    </div>
                    <div class="flex size-12 items-center justify-center rounded-full {{ ($slaResolutionHours > 0 && ($slaMetrics['avg_resolution_hours'] ?? 0) > $slaResolutionHours) ? 'bg-error/10 text-error' : 'bg-success/10 text-success' }}">
                        <svg xmlns="http://www.w3.org/2000/svg" class="size-6" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                    </div>
                </div>
                <p class="mt-2 text-xs text-slate-400">SLA target: {{ $slaResolutionHours > 0 ? $slaResolutionHours . ' hrs' : 'Not set' }}</p>
            </div>
        </div>

        {{-- Volume Chart --}}
        <div class="card mt-4 p-4 sm:p-5" x-data="{
            labels: {{ json_encode($volumeByDay->pluck('date')) }},
            data: {{ json_encode($volumeByDay->pluck('count')) }}
        }">
            <div class="flex items-center justify-between">
                <h3 class="text-base font-medium text-slate-700 dark:text-navy-100">Ticket Volume (Daily)</h3>
                <span class="text-xs text-slate-400">{{ $from }} — {{ $to }}</span>
            </div>
            @if($volumeByDay->count())
                <div class="mt-4 is-scrollbar-hidden overflow-x-auto">
                    <div class="flex items-end space-x-1" style="min-width: {{ max($volumeByDay->count() * 28, 300) }}px; height: 180px;">
                        @php $maxVol = $volumeByDay->max('count') ?: 1; @endphp
                        @foreach($volumeByDay as $day)
                            <div class="group relative flex flex-1 flex-col items-center">
                                <div class="w-full max-w-[20px] rounded-t bg-primary/80 dark:bg-accent/80 transition-all hover:bg-primary dark:hover:bg-accent" style="height: {{ ($day->count / $maxVol) * 150 }}px;" x-tooltip.placement.top="'{{ $day->date }}: {{ $day->count }}'"></div>
                                @if($volumeByDay->count() <= 31)
                                    <span class="mt-1 text-[10px] text-slate-400 rotate-[-45deg] origin-top-left whitespace-nowrap">{{ \Carbon\Carbon::parse($day->date)->format('M d') }}</span>
                                @endif
                            </div>
                        @endforeach
                    </div>
                </div>
            @else
                <div class="mt-4 flex h-40 items-center justify-center text-sm text-slate-400 dark:text-navy-300">No data for the selected period</div>
            @endif
        </div>

        {{-- Distribution Cards --}}
        <div class="mt-4 grid grid-cols-1 gap-4 sm:grid-cols-2 lg:grid-cols-4">
            {{-- By Status --}}
            <div class="card p-4 sm:p-5">
                <h3 class="text-base font-medium text-slate-700 dark:text-navy-100">By Status</h3>
                @if($byStatus->count())
                    <div class="mt-3 space-y-3">
                        @php $totalStatusCount = $byStatus->sum('count') ?: 1; @endphp
                        @foreach($byStatus as $item)
                            <div>
                                <div class="flex items-center justify-between text-sm">
                                    <div class="flex items-center space-x-2">
                                        <div class="size-2.5 rounded-full" style="background-color: {{ $item->color ?? '#94a3b8' }}"></div>
                                        <span class="text-slate-600 dark:text-navy-100">{{ $item->name ?? 'Unknown' }}</span>
                                    </div>
                                    <span class="font-medium text-slate-700 dark:text-navy-100">{{ $item->count }}</span>
                                </div>
                                <div class="mt-1 h-1.5 w-full rounded-full bg-slate-150 dark:bg-navy-500">
                                    <div class="h-full rounded-full" style="width: {{ round(($item->count / $totalStatusCount) * 100) }}%; background-color: {{ $item->color ?? '#94a3b8' }}"></div>
                                </div>
                            </div>
                        @endforeach
                    </div>
                @else
                    <p class="mt-3 text-sm text-slate-400">No data</p>
                @endif
            </div>

            {{-- By Priority --}}
            <div class="card p-4 sm:p-5">
                <h3 class="text-base font-medium text-slate-700 dark:text-navy-100">By Priority</h3>
                @if($byPriority->count())
                    <div class="mt-3 space-y-3">
                        @php $totalPriorityCount = $byPriority->sum('count') ?: 1; @endphp
                        @foreach($byPriority as $item)
                            <div>
                                <div class="flex items-center justify-between text-sm">
                                    <div class="flex items-center space-x-2">
                                        <div class="size-2.5 rounded-full" style="background-color: {{ $item->color ?? '#94a3b8' }}"></div>
                                        <span class="text-slate-600 dark:text-navy-100">{{ $item->name ?? 'Unknown' }}</span>
                                    </div>
                                    <span class="font-medium text-slate-700 dark:text-navy-100">{{ $item->count }}</span>
                                </div>
                                <div class="mt-1 h-1.5 w-full rounded-full bg-slate-150 dark:bg-navy-500">
                                    <div class="h-full rounded-full" style="width: {{ round(($item->count / $totalPriorityCount) * 100) }}%; background-color: {{ $item->color ?? '#94a3b8' }}"></div>
                                </div>
                            </div>
                        @endforeach
                    </div>
                @else
                    <p class="mt-3 text-sm text-slate-400">No data</p>
                @endif
            </div>

            {{-- By Category --}}
            <div class="card p-4 sm:p-5">
                <h3 class="text-base font-medium text-slate-700 dark:text-navy-100">By Category</h3>
                @if($byCategory->count())
                    <div class="mt-3 space-y-3">
                        @php
                            $totalCategoryCount = $byCategory->sum('count') ?: 1;
                            $catColors = ['#6366f1', '#8b5cf6', '#ec4899', '#f59e0b', '#10b981', '#3b82f6', '#ef4444', '#14b8a6'];
                        @endphp
                        @foreach($byCategory as $i => $item)
                            <div>
                                <div class="flex items-center justify-between text-sm">
                                    <div class="flex items-center space-x-2">
                                        <div class="size-2.5 rounded-full" style="background-color: {{ $catColors[$i % count($catColors)] }}"></div>
                                        <span class="text-slate-600 dark:text-navy-100">{{ $item->name }}</span>
                                    </div>
                                    <span class="font-medium text-slate-700 dark:text-navy-100">{{ $item->count }}</span>
                                </div>
                                <div class="mt-1 h-1.5 w-full rounded-full bg-slate-150 dark:bg-navy-500">
                                    <div class="h-full rounded-full" style="width: {{ round(($item->count / $totalCategoryCount) * 100) }}%; background-color: {{ $catColors[$i % count($catColors)] }}"></div>
                                </div>
                            </div>
                        @endforeach
                    </div>
                @else
                    <p class="mt-3 text-sm text-slate-400">No data</p>
                @endif
            </div>

            {{-- By Source --}}
            <div class="card p-4 sm:p-5">
                <h3 class="text-base font-medium text-slate-700 dark:text-navy-100">By Source</h3>
                @if($bySource->count())
                    <div class="mt-3 space-y-3">
                        @php
                            $totalSourceCount = $bySource->sum('count') ?: 1;
                            $srcColors = ['#3b82f6', '#10b981', '#f59e0b', '#ef4444', '#8b5cf6', '#ec4899'];
                            $srcIcons = ['email' => '✉️', 'web' => '🌐', 'phone' => '📞', 'chat' => '💬', 'api' => '⚡'];
                        @endphp
                        @foreach($bySource as $i => $item)
                            <div>
                                <div class="flex items-center justify-between text-sm">
                                    <div class="flex items-center space-x-2">
                                        <span>{{ $srcIcons[strtolower($item->source)] ?? '📋' }}</span>
                                        <span class="text-slate-600 dark:text-navy-100 capitalize">{{ $item->source }}</span>
                                    </div>
                                    <span class="font-medium text-slate-700 dark:text-navy-100">{{ $item->count }}</span>
                                </div>
                                <div class="mt-1 h-1.5 w-full rounded-full bg-slate-150 dark:bg-navy-500">
                                    <div class="h-full rounded-full" style="width: {{ round(($item->count / $totalSourceCount) * 100) }}%; background-color: {{ $srcColors[$i % count($srcColors)] }}"></div>
                                </div>
                            </div>
                        @endforeach
                    </div>
                @else
                    <p class="mt-3 text-sm text-slate-400">No data</p>
                @endif
            </div>
        </div>

        {{-- Agent Performance Table --}}
        <div class="card mt-4">
            <div class="flex items-center justify-between p-4 sm:p-5">
                <h3 class="text-base font-medium text-slate-700 dark:text-navy-100">Agent Performance</h3>
            </div>
            <div class="is-scrollbar-hidden min-w-full overflow-x-auto">
                <table class="is-hoverable w-full text-left">
                    <thead>
                        <tr>
                            <th class="whitespace-nowrap bg-slate-200 px-4 py-3 font-semibold uppercase text-slate-800 dark:bg-navy-800 dark:text-navy-100 lg:px-5 rounded-tl-lg">Agent</th>
                            <th class="whitespace-nowrap bg-slate-200 px-4 py-3 font-semibold uppercase text-slate-800 dark:bg-navy-800 dark:text-navy-100 lg:px-5">Total</th>
                            <th class="whitespace-nowrap bg-slate-200 px-4 py-3 font-semibold uppercase text-slate-800 dark:bg-navy-800 dark:text-navy-100 lg:px-5">Resolved</th>
                            <th class="whitespace-nowrap bg-slate-200 px-4 py-3 font-semibold uppercase text-slate-800 dark:bg-navy-800 dark:text-navy-100 lg:px-5">Resolution Rate</th>
                            <th class="whitespace-nowrap bg-slate-200 px-4 py-3 font-semibold uppercase text-slate-800 dark:bg-navy-800 dark:text-navy-100 lg:px-5">Avg First Response</th>
                            <th class="whitespace-nowrap bg-slate-200 px-4 py-3 font-semibold uppercase text-slate-800 dark:bg-navy-800 dark:text-navy-100 lg:px-5 rounded-tr-lg">Avg Resolution</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($agentPerformance as $agent)
                            <tr class="border-y border-transparent border-b-slate-200 dark:border-b-navy-500">
                                <td class="whitespace-nowrap px-4 py-3 sm:px-5">
                                    <div class="flex items-center space-x-3">
                                        <div class="avatar size-8">
                                            <div class="is-initial rounded-full bg-primary/10 text-primary dark:bg-accent/10 dark:text-accent-light text-xs font-medium">{{ strtoupper(substr($agent->agent_name ?? '?', 0, 2)) }}</div>
                                        </div>
                                        <span class="font-medium text-slate-700 dark:text-navy-100">{{ $agent->agent_name ?? 'Unknown' }}</span>
                                    </div>
                                </td>
                                <td class="whitespace-nowrap px-4 py-3 font-medium text-slate-700 dark:text-navy-100 sm:px-5">{{ $agent->total }}</td>
                                <td class="whitespace-nowrap px-4 py-3 sm:px-5">
                                    <span class="badge rounded-full bg-success/10 text-success dark:bg-success/15">{{ $agent->resolved }}</span>
                                </td>
                                <td class="whitespace-nowrap px-4 py-3 sm:px-5">
                                    @php $rate = $agent->total > 0 ? round(($agent->resolved / $agent->total) * 100) : 0; @endphp
                                    <div class="flex items-center space-x-2">
                                        <div class="h-1.5 w-16 rounded-full bg-slate-150 dark:bg-navy-500">
                                            <div class="h-full rounded-full {{ $rate >= 80 ? 'bg-success' : ($rate >= 50 ? 'bg-warning' : 'bg-error') }}" style="width: {{ $rate }}%"></div>
                                        </div>
                                        <span class="text-sm text-slate-600 dark:text-navy-200">{{ $rate }}%</span>
                                    </div>
                                </td>
                                <td class="whitespace-nowrap px-4 py-3 sm:px-5">
                                    <span class="{{ ($slaResponseHours > 0 && ($agent->avg_first_response_hours ?? 0) > $slaResponseHours) ? 'text-error font-medium' : 'text-slate-600 dark:text-navy-200' }}">
                                        {{ $agent->avg_first_response_hours ?? '—' }} hrs
                                    </span>
                                </td>
                                <td class="whitespace-nowrap px-4 py-3 sm:px-5">
                                    <span class="{{ ($slaResolutionHours > 0 && ($agent->avg_resolution_hours ?? 0) > $slaResolutionHours) ? 'text-error font-medium' : 'text-slate-600 dark:text-navy-200' }}">
                                        {{ $agent->avg_resolution_hours ?? '—' }} hrs
                                    </span>
                                </td>
                            </tr>
                        @empty
                            <tr><td colspan="6" class="px-4 py-6 text-center text-slate-400 dark:text-navy-300">No agent data for the selected period</td></tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>

    </main>

</x-app-layout>
