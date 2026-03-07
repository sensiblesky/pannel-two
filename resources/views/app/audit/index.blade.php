<x-app-layout title="Audit Logs" is-sidebar-open="true" is-header-blur="true">
    <main class="main-content w-full px-[var(--margin-x)] pb-8">
        <div class="flex items-center space-x-4 py-5 lg:py-6">
            <h2 class="text-xl font-medium text-slate-800 dark:text-navy-50 lg:text-2xl">Audit Logs</h2>
            <div class="hidden h-full py-1 sm:flex"><div class="h-full w-px bg-slate-300 dark:bg-navy-600"></div></div>
            <ul class="hidden flex-wrap items-center space-x-2 sm:flex">
                <li class="flex items-center space-x-2">
                    <a class="text-primary transition-colors hover:text-primary-focus dark:text-accent-light dark:hover:text-accent" href="{{ route('config/audit') }}">Configuration</a>
                    <svg x-ignore xmlns="http://www.w3.org/2000/svg" class="size-4" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7" /></svg>
                </li>
                <li>Activity Logs</li>
            </ul>
        </div>

        <div class="grid grid-cols-12 gap-4 sm:gap-5 lg:gap-6">
            <div class="col-span-12">
                <div class="card">
                    <div class="is-scrollbar-hidden min-w-full overflow-x-auto" x-data="{ expanded: null }">

                        {{-- Filters --}}
                        <div class="flex flex-col gap-3 border-b border-slate-200 p-4 dark:border-navy-500 sm:flex-row sm:items-center sm:justify-between">
                            <form method="GET" action="{{ route('config/audit') }}" class="flex flex-wrap items-center gap-3">
                                <input type="text" name="search" value="{{ request('search') }}" placeholder="Search user, model, IP..."
                                    class="form-input w-64 rounded-lg border border-slate-300 bg-transparent px-3 py-2 placeholder:text-slate-400/70 hover:border-slate-400 focus:border-primary dark:border-navy-450 dark:hover:border-navy-400 dark:focus:border-accent" />
                                <select name="action"
                                    class="form-select rounded-lg border border-slate-300 bg-white px-3 py-2 hover:border-slate-400 focus:border-primary dark:border-navy-450 dark:bg-navy-700 dark:hover:border-navy-400 dark:focus:border-accent">
                                    <option value="">All Actions</option>
                                    <option value="CREATE" {{ request('action') === 'CREATE' ? 'selected' : '' }}>Create</option>
                                    <option value="UPDATE" {{ request('action') === 'UPDATE' ? 'selected' : '' }}>Update</option>
                                    <option value="DELETE" {{ request('action') === 'DELETE' ? 'selected' : '' }}>Delete</option>
                                    <option value="VIEW" {{ request('action') === 'VIEW' ? 'selected' : '' }}>View</option>
                                </select>
                                <select name="model"
                                    class="form-select rounded-lg border border-slate-300 bg-white px-3 py-2 hover:border-slate-400 focus:border-primary dark:border-navy-450 dark:bg-navy-700 dark:hover:border-navy-400 dark:focus:border-accent">
                                    <option value="">All Models</option>
                                    <option value="User" {{ request('model') === 'User' ? 'selected' : '' }}>User</option>
                                    <option value="Customer" {{ request('model') === 'Customer' ? 'selected' : '' }}>Customer</option>
                                    <option value="Branch" {{ request('model') === 'Branch' ? 'selected' : '' }}>Branch</option>
                                    <option value="Department" {{ request('model') === 'Department' ? 'selected' : '' }}>Department</option>
                                </select>
                                <button type="submit" class="btn bg-primary font-medium text-white hover:bg-primary-focus focus:bg-primary-focus active:bg-primary-focus/90 dark:bg-accent dark:hover:bg-accent-focus dark:focus:bg-accent-focus dark:active:bg-accent/90">
                                    Filter
                                </button>
                                @if(request()->hasAny(['search', 'action', 'model']))
                                    <a href="{{ route('config/audit') }}" class="btn border border-slate-300 font-medium text-slate-800 hover:bg-slate-150 dark:border-navy-450 dark:text-navy-50 dark:hover:bg-navy-500">Clear</a>
                                @endif
                            </form>
                            <span class="text-sm text-slate-400">{{ $logs->total() }} records</span>
                        </div>

                        <table class="w-full text-left">
                            <thead>
                                <tr class="border-y border-transparent border-b-slate-200 dark:border-b-navy-500">
                                    <th class="whitespace-nowrap px-4 py-3 font-semibold uppercase text-slate-800 dark:text-navy-100 lg:px-5">#</th>
                                    <th class="whitespace-nowrap px-4 py-3 font-semibold uppercase text-slate-800 dark:text-navy-100 lg:px-5">Date</th>
                                    <th class="whitespace-nowrap px-4 py-3 font-semibold uppercase text-slate-800 dark:text-navy-100 lg:px-5">User</th>
                                    <th class="whitespace-nowrap px-4 py-3 font-semibold uppercase text-slate-800 dark:text-navy-100 lg:px-5">Action</th>
                                    <th class="whitespace-nowrap px-4 py-3 font-semibold uppercase text-slate-800 dark:text-navy-100 lg:px-5">Model</th>
                                    <th class="whitespace-nowrap px-4 py-3 font-semibold uppercase text-slate-800 dark:text-navy-100 lg:px-5">IP Address</th>
                                    <th class="whitespace-nowrap px-4 py-3 font-semibold uppercase text-slate-800 dark:text-navy-100 lg:px-5">Details</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse($logs as $log)
                                    <tr class="border-y border-transparent border-b-slate-200 dark:border-b-navy-500">
                                        <td class="whitespace-nowrap px-4 py-3 sm:px-5">{{ $logs->firstItem() + $loop->index }}</td>
                                        <td class="whitespace-nowrap px-4 py-3 sm:px-5">
                                            <span class="text-xs text-slate-400">{{ $log->created_at?->format('M d, Y') }}</span>
                                            <br>
                                            <span class="text-tiny text-slate-300">{{ $log->created_at?->format('H:i:s') }}</span>
                                        </td>
                                        <td class="whitespace-nowrap px-4 py-3 sm:px-5">
                                            <div class="flex items-center space-x-3">
                                                <div class="flex size-8 items-center justify-center rounded-full bg-primary/10 dark:bg-accent-light/15">
                                                    <span class="text-xs font-medium uppercase text-primary dark:text-accent-light">{{ $log->user ? substr($log->user->name, 0, 2) : 'SY' }}</span>
                                                </div>
                                                <div>
                                                    <p class="font-medium text-slate-700 dark:text-navy-100">{{ $log->user?->name ?? 'System' }}</p>
                                                    <p class="text-xs text-slate-400">{{ $log->user?->email }}</p>
                                                </div>
                                            </div>
                                        </td>
                                        <td class="whitespace-nowrap px-4 py-3 sm:px-5">
                                            @php
                                                $actionColors = [
                                                    'CREATE' => 'bg-success/10 text-success dark:bg-success/15',
                                                    'UPDATE' => 'bg-info/10 text-info dark:bg-info/15',
                                                    'DELETE' => 'bg-error/10 text-error dark:bg-error/15',
                                                    'VIEW'   => 'bg-slate-150 text-slate-800 dark:bg-navy-500 dark:text-navy-100',
                                                ];
                                            @endphp
                                            <span class="badge rounded-full {{ $actionColors[$log->action] ?? 'bg-slate-150 text-slate-800' }}">
                                                {{ $log->action }}
                                            </span>
                                        </td>
                                        <td class="whitespace-nowrap px-4 py-3 sm:px-5">
                                            <p class="font-medium text-slate-700 dark:text-navy-100">{{ class_basename($log->model_type) }}</p>
                                            <p class="text-xs text-slate-400">ID: {{ $log->model_id }}</p>
                                        </td>
                                        <td class="whitespace-nowrap px-4 py-3 sm:px-5 text-xs text-slate-500">{{ $log->ip_address }}</td>
                                        <td class="whitespace-nowrap px-4 py-3 sm:px-5">
                                            @if($log->old_values || $log->new_values)
                                                <button @click="expanded = expanded === {{ $log->id }} ? null : {{ $log->id }}"
                                                    class="btn size-8 rounded-full p-0 hover:bg-slate-300/20 focus:bg-slate-300/20 active:bg-slate-300/25 dark:hover:bg-navy-300/20 dark:focus:bg-navy-300/20 dark:active:bg-navy-300/25">
                                                    <svg :class="expanded === {{ $log->id }} && 'rotate-180'" class="size-4.5 transition-transform duration-200" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                                        <path stroke-linecap="round" stroke-linejoin="round" d="M19 9l-7 7-7-7" />
                                                    </svg>
                                                </button>
                                            @else
                                                <span class="text-xs text-slate-300 dark:text-navy-300">—</span>
                                            @endif
                                        </td>
                                    </tr>
                                    {{-- Expandable row: Old vs New values --}}
                                    @if($log->old_values || $log->new_values)
                                        <tr x-show="expanded === {{ $log->id }}" x-collapse>
                                            <td colspan="7" class="px-4 py-3 sm:px-5">
                                                <div class="rounded-lg border border-slate-200 dark:border-navy-500 overflow-hidden">
                                                    <table class="w-full text-sm">
                                                        <thead>
                                                            <tr class="bg-slate-50 dark:bg-navy-600">
                                                                <th class="px-4 py-2 text-left font-semibold text-slate-600 dark:text-navy-100 w-1/4">Field</th>
                                                                <th class="px-4 py-2 text-left font-semibold text-slate-600 dark:text-navy-100 w-3/8">
                                                                    @if($log->action === 'CREATE')
                                                                        Value
                                                                    @elseif($log->action === 'DELETE')
                                                                        Last Value
                                                                    @else
                                                                        Old Value
                                                                    @endif
                                                                </th>
                                                                @if($log->action === 'UPDATE')
                                                                    <th class="px-4 py-2 text-left font-semibold text-slate-600 dark:text-navy-100 w-3/8">New Value</th>
                                                                @endif
                                                            </tr>
                                                        </thead>
                                                        <tbody>
                                                            @php
                                                                $old = $log->old_values ?? [];
                                                                $new = $log->new_values ?? [];
                                                                $allKeys = array_unique(array_merge(array_keys($old), array_keys($new)));
                                                                sort($allKeys);
                                                            @endphp
                                                            @foreach($allKeys as $key)
                                                                @php
                                                                    $oldVal = $old[$key] ?? null;
                                                                    $newVal = $new[$key] ?? null;
                                                                    $changed = $log->action === 'UPDATE' && $oldVal !== $newVal;
                                                                @endphp
                                                                <tr class="border-t border-slate-100 dark:border-navy-500 {{ $changed ? 'bg-warning/5' : '' }}">
                                                                    <td class="px-4 py-2 font-medium text-slate-600 dark:text-navy-200">
                                                                        {{ str_replace('_', ' ', ucfirst($key)) }}
                                                                        @if($changed)
                                                                            <span class="ml-1 inline-block size-1.5 rounded-full bg-warning"></span>
                                                                        @endif
                                                                    </td>
                                                                    <td class="px-4 py-2 {{ $changed ? 'text-error line-through' : 'text-slate-500 dark:text-navy-200' }}">
                                                                        @if($log->action === 'CREATE')
                                                                            <span class="text-slate-500 dark:text-navy-200 no-underline" style="text-decoration:none">{{ $newVal ?? '—' }}</span>
                                                                        @elseif($log->action === 'DELETE')
                                                                            <span class="text-slate-500 dark:text-navy-200 no-underline" style="text-decoration:none">{{ $oldVal ?? '—' }}</span>
                                                                        @else
                                                                            {{ $oldVal ?? '—' }}
                                                                        @endif
                                                                    </td>
                                                                    @if($log->action === 'UPDATE')
                                                                        <td class="px-4 py-2 {{ $changed ? 'text-success font-medium' : 'text-slate-500 dark:text-navy-200' }}">
                                                                            {{ $newVal ?? '—' }}
                                                                        </td>
                                                                    @endif
                                                                </tr>
                                                            @endforeach
                                                        </tbody>
                                                    </table>
                                                </div>
                                                @if($log->user_agent)
                                                    <p class="mt-2 text-xs text-slate-400 dark:text-navy-300 truncate">
                                                        <span class="font-medium">User Agent:</span> {{ Str::limit($log->user_agent, 120) }}
                                                    </p>
                                                @endif
                                            </td>
                                        </tr>
                                    @endif
                                @empty
                                    <tr>
                                        <td colspan="7" class="px-4 py-8 text-center">
                                            <div class="flex flex-col items-center">
                                                <svg class="size-16 text-slate-300 dark:text-navy-300" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1">
                                                    <path stroke-linecap="round" stroke-linejoin="round" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" />
                                                </svg>
                                                <p class="mt-2 text-slate-400 dark:text-navy-300">No activity logs found</p>
                                            </div>
                                        </td>
                                    </tr>
                                @endforelse
                            </tbody>
                        </table>

                        {{-- Pagination --}}
                        <div class="flex flex-col justify-between space-y-4 px-4 py-4 sm:flex-row sm:items-center sm:space-y-0 sm:px-5">
                            <div class="flex items-center space-x-2 text-xs-plus">
                                <span>Showing</span>
                                <span class="font-semibold">{{ $logs->firstItem() ?? 0 }} - {{ $logs->lastItem() ?? 0 }}</span>
                                <span>of {{ $logs->total() }} entries</span>
                            </div>
                            @if ($logs->hasPages())
                                <ol class="pagination">
                                    <li class="rounded-l-lg bg-slate-150 dark:bg-navy-500">
                                        @if ($logs->onFirstPage())
                                            <span class="flex size-8 items-center justify-center rounded-lg text-slate-400 dark:text-navy-300">
                                                <svg xmlns="http://www.w3.org/2000/svg" class="size-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M15 19l-7-7 7-7" /></svg>
                                            </span>
                                        @else
                                            <a href="{{ $logs->previousPageUrl() }}" class="flex size-8 items-center justify-center rounded-lg text-slate-500 transition-colors hover:bg-slate-300 focus:bg-slate-300 active:bg-slate-300/80 dark:text-navy-200 dark:hover:bg-navy-450 dark:focus:bg-navy-450 dark:active:bg-navy-450/90">
                                                <svg xmlns="http://www.w3.org/2000/svg" class="size-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M15 19l-7-7 7-7" /></svg>
                                            </a>
                                        @endif
                                    </li>
                                    @foreach ($logs->getUrlRange(1, $logs->lastPage()) as $page => $url)
                                        <li class="bg-slate-150 dark:bg-navy-500">
                                            <a href="{{ $url }}"
                                                class="{{ $page == $logs->currentPage() ? 'flex size-8 items-center justify-center rounded-lg bg-primary font-medium text-white transition-colors hover:bg-primary-focus focus:bg-primary-focus active:bg-primary-focus/90 dark:bg-accent dark:hover:bg-accent-focus dark:focus:bg-accent-focus dark:active:bg-accent/90' : 'flex size-8 items-center justify-center rounded-lg text-slate-500 transition-colors hover:bg-slate-300 focus:bg-slate-300 active:bg-slate-300/80 dark:text-navy-200 dark:hover:bg-navy-450 dark:focus:bg-navy-450 dark:active:bg-navy-450/90' }}">
                                                {{ $page }}
                                            </a>
                                        </li>
                                    @endforeach
                                    <li class="rounded-r-lg bg-slate-150 dark:bg-navy-500">
                                        @if ($logs->hasMorePages())
                                            <a href="{{ $logs->nextPageUrl() }}" class="flex size-8 items-center justify-center rounded-lg text-slate-500 transition-colors hover:bg-slate-300 focus:bg-slate-300 active:bg-slate-300/80 dark:text-navy-200 dark:hover:bg-navy-450 dark:focus:bg-navy-450 dark:active:bg-navy-450/90">
                                                <svg xmlns="http://www.w3.org/2000/svg" class="size-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M9 5l7 7-7 7" /></svg>
                                            </a>
                                        @else
                                            <span class="flex size-8 items-center justify-center rounded-lg text-slate-400 dark:text-navy-300">
                                                <svg xmlns="http://www.w3.org/2000/svg" class="size-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M9 5l7 7-7 7" /></svg>
                                            </span>
                                        @endif
                                    </li>
                                </ol>
                            @endif
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </main>
</x-app-layout>
