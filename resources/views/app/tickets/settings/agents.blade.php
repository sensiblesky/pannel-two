<x-app-layout title="Ticket Agents" is-sidebar-open="true" is-header-blur="true">

    @slot('script')
        <script>
            function confirmDelete(id) {
                Swal.fire({
                    title: 'Are you sure?',
                    text: 'This agent will be removed.',
                    icon: 'warning',
                    showCancelButton: true,
                    confirmButtonColor: '#e53e3e',
                    confirmButtonText: 'Yes, remove it!',
                    reverseButtons: true,
                }).then((result) => {
                    if (result.isConfirmed) {
                        document.getElementById('delete-form-' + id).submit();
                    }
                });
            }
        </script>
        @if(session('success'))
            <script>
                Swal.fire({ toast: true, position: 'top-end', icon: 'success', title: @json(session('success')), showConfirmButton: false, timer: 3000 });
            </script>
        @endif
    @endslot

    <main class="main-content w-full px-[var(--margin-x)] pb-8" x-data="{
        showCreateModal: {{ $errors->any() ? 'true' : 'false' }},
        editItem: null,
        editDepartments: [],
        openEdit(agent, deptIds) {
            this.editItem = agent;
            this.editDepartments = deptIds.map(String);
            this.showCreateModal = true;
        }
    }">
        <div class="flex items-center justify-between py-5 lg:py-6">
            <div class="flex items-center space-x-4">
                <h2 class="text-xl font-medium text-slate-800 dark:text-navy-50 lg:text-2xl">Agents</h2>
                <div class="hidden h-full py-1 sm:flex">
                    <div class="h-full w-px bg-slate-300 dark:bg-navy-600"></div>
                </div>
                <ul class="hidden flex-wrap items-center space-x-2 sm:flex">
                    <li class="flex items-center space-x-2">
                        <a class="text-primary transition-colors hover:text-primary-focus dark:text-accent-light dark:hover:text-accent" href="{{ route('tickets/dashboard') }}">Tickets</a>
                        <svg xmlns="http://www.w3.org/2000/svg" class="size-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/></svg>
                    </li>
                    <li class="flex items-center space-x-2">
                        <span>Settings</span>
                        <svg xmlns="http://www.w3.org/2000/svg" class="size-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/></svg>
                    </li>
                    <li>Agents</li>
                </ul>
            </div>
            <button @click="showCreateModal = true; editItem = null; editDepartments = []" class="btn space-x-2 bg-primary font-medium text-white shadow-lg shadow-primary/50 hover:bg-primary-focus focus:bg-primary-focus active:bg-primary-focus/90 dark:bg-accent dark:shadow-accent/50 dark:hover:bg-accent-focus dark:focus:bg-accent-focus dark:active:bg-accent/90">
                <svg xmlns="http://www.w3.org/2000/svg" class="size-5" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/></svg>
                <span>Add Agent</span>
            </button>
        </div>

        <div class="card mt-3">
            {{-- Search & Filters --}}
            <div class="flex flex-col space-y-4 px-4 py-4 sm:flex-row sm:items-center sm:space-y-0 sm:space-x-4 sm:px-5">
                <form method="GET" action="{{ route('tickets/settings-agents') }}" class="flex flex-1 flex-wrap items-center gap-3">
                    <label class="relative flex flex-1">
                        <input
                            name="search"
                            value="{{ request('search') }}"
                            class="form-input peer w-full rounded-lg border border-slate-300 bg-transparent px-3 py-2 pl-9 placeholder:text-slate-400/70 hover:border-slate-400 focus:border-primary dark:border-navy-450 dark:hover:border-navy-400 dark:focus:border-accent"
                            placeholder="Search agents..."
                            type="text"
                        />
                        <span class="pointer-events-none absolute flex h-full w-10 items-center justify-center text-slate-400 peer-focus:text-primary dark:text-navy-300 dark:peer-focus:text-accent">
                            <svg xmlns="http://www.w3.org/2000/svg" class="size-4.5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.5">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z" />
                            </svg>
                        </span>
                    </label>
                    <select name="department_id"
                        class="form-select rounded-lg border border-slate-300 bg-white px-3 py-2 hover:border-slate-400 focus:border-primary dark:border-navy-450 dark:bg-navy-700 dark:hover:border-navy-400 dark:focus:border-accent">
                        <option value="">All Departments</option>
                        @foreach($departments as $dept)
                            <option value="{{ $dept->id }}" {{ request('department_id') == $dept->id ? 'selected' : '' }}>{{ $dept->name }}</option>
                        @endforeach
                    </select>
                    <select name="status"
                        class="form-select rounded-lg border border-slate-300 bg-white px-3 py-2 hover:border-slate-400 focus:border-primary dark:border-navy-450 dark:bg-navy-700 dark:hover:border-navy-400 dark:focus:border-accent">
                        <option value="">All Status</option>
                        <option value="1" {{ request('status') === '1' ? 'selected' : '' }}>Active</option>
                        <option value="0" {{ request('status') === '0' ? 'selected' : '' }}>Inactive</option>
                    </select>
                    <button type="submit"
                        class="btn bg-slate-150 font-medium text-slate-800 hover:bg-slate-200 focus:bg-slate-200 active:bg-slate-200/80 dark:bg-navy-500 dark:text-navy-50 dark:hover:bg-navy-450 dark:focus:bg-navy-450 dark:active:bg-navy-450/90">
                        Filter
                    </button>
                    @if(request()->hasAny(['search', 'status', 'department_id']))
                        <a href="{{ route('tickets/settings-agents') }}"
                            class="btn bg-slate-150 font-medium text-slate-800 hover:bg-slate-200 focus:bg-slate-200 active:bg-slate-200/80 dark:bg-navy-500 dark:text-navy-50 dark:hover:bg-navy-450 dark:focus:bg-navy-450 dark:active:bg-navy-450/90">
                            Clear
                        </a>
                    @endif
                </form>
            </div>

            {{-- Table --}}
            <div class="is-scrollbar-hidden min-w-full overflow-x-auto">
                <table class="is-hoverable w-full text-left">
                    <thead>
                        <tr>
                            <th class="whitespace-nowrap rounded-tl-lg bg-slate-200 px-4 py-3 font-semibold uppercase text-slate-800 dark:bg-navy-800 dark:text-navy-100 lg:px-5">#</th>
                            <th class="whitespace-nowrap bg-slate-200 px-4 py-3 font-semibold uppercase text-slate-800 dark:bg-navy-800 dark:text-navy-100 lg:px-5">User</th>
                            <th class="whitespace-nowrap bg-slate-200 px-4 py-3 font-semibold uppercase text-slate-800 dark:bg-navy-800 dark:text-navy-100 lg:px-5">Display Name</th>
                            <th class="whitespace-nowrap bg-slate-200 px-4 py-3 font-semibold uppercase text-slate-800 dark:bg-navy-800 dark:text-navy-100 lg:px-5">Departments</th>
                            <th class="whitespace-nowrap bg-slate-200 px-4 py-3 font-semibold uppercase text-slate-800 dark:bg-navy-800 dark:text-navy-100 lg:px-5">Specialization</th>
                            <th class="whitespace-nowrap bg-slate-200 px-4 py-3 font-semibold uppercase text-slate-800 dark:bg-navy-800 dark:text-navy-100 lg:px-5">Max Tickets</th>
                            <th class="whitespace-nowrap bg-slate-200 px-4 py-3 font-semibold uppercase text-slate-800 dark:bg-navy-800 dark:text-navy-100 lg:px-5">Available</th>
                            <th class="whitespace-nowrap bg-slate-200 px-4 py-3 font-semibold uppercase text-slate-800 dark:bg-navy-800 dark:text-navy-100 lg:px-5">Status</th>
                            <th class="whitespace-nowrap rounded-tr-lg bg-slate-200 px-4 py-3 font-semibold uppercase text-slate-800 dark:bg-navy-800 dark:text-navy-100 lg:px-5">Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($agents as $agent)
                            @php
                                $depts = $agentDepartments->get($agent->id, collect());
                                $deptIds = $depts->pluck('dept_id')->toArray();
                            @endphp
                            <tr class="border-y border-transparent border-b-slate-200 dark:border-b-navy-500">
                                <td class="whitespace-nowrap px-4 py-3 text-sm text-slate-500 dark:text-navy-200 sm:px-5">{{ $agents->firstItem() + $loop->index }}</td>
                                <td class="whitespace-nowrap px-4 py-3 sm:px-5">
                                    <div class="flex items-center space-x-3">
                                        <div class="avatar size-9">
                                            @if($agent->user_avatar)
                                                <img class="rounded-full" src="{{ asset('storage/' . $agent->user_avatar) }}" alt="{{ $agent->user_name }}">
                                            @else
                                                <div class="is-initial rounded-full bg-primary/10 text-primary dark:bg-accent-light/10 dark:text-accent-light">
                                                    {{ strtoupper(substr($agent->user_name, 0, 1)) }}
                                                </div>
                                            @endif
                                        </div>
                                        <div>
                                            <p class="font-medium text-slate-700 dark:text-navy-100">{{ $agent->user_name }}</p>
                                            <p class="text-xs text-slate-400 dark:text-navy-300">{{ $agent->user_email }}</p>
                                        </div>
                                    </div>
                                </td>
                                <td class="whitespace-nowrap px-4 py-3 text-sm text-slate-600 dark:text-navy-100 sm:px-5">{{ $agent->display_name ?? '—' }}</td>
                                <td class="px-4 py-3 sm:px-5">
                                    <div class="flex flex-wrap gap-1">
                                        @forelse($depts as $dept)
                                            <span class="badge rounded-full bg-info/10 text-info dark:bg-info/15">{{ $dept->dept_name }}</span>
                                        @empty
                                            <span class="text-xs text-slate-400">None</span>
                                        @endforelse
                                    </div>
                                </td>
                                <td class="whitespace-nowrap px-4 py-3 text-sm text-slate-500 dark:text-navy-200 sm:px-5">{{ $agent->specialization ?? '—' }}</td>
                                <td class="whitespace-nowrap px-4 py-3 text-sm text-slate-500 dark:text-navy-200 sm:px-5">{{ $agent->max_tickets ?? '—' }}</td>
                                <td class="whitespace-nowrap px-4 py-3 sm:px-5">
                                    <div class="badge rounded-full {{ $agent->is_available ? 'bg-success/10 text-success dark:bg-success/15' : 'bg-warning/10 text-warning dark:bg-warning/15' }}">
                                        {{ $agent->is_available ? 'Yes' : 'No' }}
                                    </div>
                                </td>
                                <td class="whitespace-nowrap px-4 py-3 sm:px-5">
                                    <div class="badge rounded-full {{ $agent->status ? 'bg-success/10 text-success dark:bg-success/15' : 'bg-slate-200 text-slate-500 dark:bg-navy-500 dark:text-navy-200' }}">{{ $agent->status ? 'Active' : 'Inactive' }}</div>
                                </td>
                                <td class="whitespace-nowrap px-4 py-3 sm:px-5">
                                    <div class="flex space-x-2">
                                        <button @click="openEdit({
                                            id: {{ $agent->id }},
                                            display_name: '{{ addslashes($agent->display_name ?? '') }}',
                                            specialization: '{{ addslashes($agent->specialization ?? '') }}',
                                            max_tickets: {{ $agent->max_tickets ?? 'null' }},
                                            is_available: {{ $agent->is_available ? 'true' : 'false' }},
                                            status: {{ $agent->status ? 'true' : 'false' }}
                                        }, {{ json_encode($deptIds) }})"
                                                class="btn size-8 rounded-full p-0 hover:bg-slate-300/20 focus:bg-slate-300/20 active:bg-slate-300/25 dark:hover:bg-navy-300/20 dark:focus:bg-navy-300/20 dark:active:bg-navy-300/25">
                                            <svg xmlns="http://www.w3.org/2000/svg" class="size-4.5" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"/></svg>
                                        </button>
                                        <button @click="confirmDelete({{ $agent->id }})" class="btn size-8 rounded-full p-0 hover:bg-error/20 focus:bg-error/20 active:bg-error/25 text-error">
                                            <svg xmlns="http://www.w3.org/2000/svg" class="size-4.5" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/></svg>
                                        </button>
                                        <form id="delete-form-{{ $agent->id }}" action="{{ route('tickets/settings-agents-destroy', $agent->id) }}" method="POST" class="hidden">@csrf @method('DELETE')</form>
                                    </div>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="9" class="px-4 py-8 text-center">
                                    <div class="flex flex-col items-center space-y-2">
                                        <svg xmlns="http://www.w3.org/2000/svg" class="size-10 text-slate-300 dark:text-navy-400" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1">
                                            <path stroke-linecap="round" stroke-linejoin="round" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0zm6 3a2 2 0 11-4 0 2 2 0 014 0zM7 10a2 2 0 11-4 0 2 2 0 014 0z" />
                                        </svg>
                                        <p class="text-slate-400 dark:text-navy-300">No agents found</p>
                                        @if(request()->hasAny(['search', 'status', 'department_id']))
                                            <a href="{{ route('tickets/settings-agents') }}" class="text-sm text-primary hover:text-primary-focus dark:text-accent-light dark:hover:text-accent">Clear filters</a>
                                        @endif
                                    </div>
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            {{-- Pagination --}}
            <div class="flex flex-col justify-between space-y-4 px-4 py-4 sm:flex-row sm:items-center sm:space-y-0 sm:px-5">
                <div class="flex items-center space-x-2 text-xs-plus">
                    <span>Showing</span>
                    <span class="font-semibold">{{ $agents->firstItem() ?? 0 }} - {{ $agents->lastItem() ?? 0 }}</span>
                    <span>of {{ $agents->total() }} entries</span>
                </div>

                @if ($agents->hasPages())
                    <ol class="pagination">
                        <li class="rounded-l-lg bg-slate-150 dark:bg-navy-500">
                            @if ($agents->onFirstPage())
                                <span class="flex size-8 items-center justify-center rounded-lg text-slate-400 dark:text-navy-300">
                                    <svg xmlns="http://www.w3.org/2000/svg" class="size-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M15 19l-7-7 7-7" /></svg>
                                </span>
                            @else
                                <a href="{{ $agents->previousPageUrl() }}" class="flex size-8 items-center justify-center rounded-lg text-slate-500 transition-colors hover:bg-slate-300 focus:bg-slate-300 active:bg-slate-300/80 dark:text-navy-200 dark:hover:bg-navy-450 dark:focus:bg-navy-450 dark:active:bg-navy-450/90">
                                    <svg xmlns="http://www.w3.org/2000/svg" class="size-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M15 19l-7-7 7-7" /></svg>
                                </a>
                            @endif
                        </li>
                        @foreach ($agents->getUrlRange(1, $agents->lastPage()) as $page => $url)
                            <li class="bg-slate-150 dark:bg-navy-500">
                                <a href="{{ $url }}" class="{{ $page == $agents->currentPage() ? 'flex size-8 items-center justify-center rounded-lg bg-primary font-medium text-white transition-colors hover:bg-primary-focus focus:bg-primary-focus active:bg-primary-focus/90 dark:bg-accent dark:hover:bg-accent-focus dark:focus:bg-accent-focus dark:active:bg-accent/90' : 'flex size-8 items-center justify-center rounded-lg text-slate-500 transition-colors hover:bg-slate-300 focus:bg-slate-300 active:bg-slate-300/80 dark:text-navy-200 dark:hover:bg-navy-450 dark:focus:bg-navy-450 dark:active:bg-navy-450/90' }}">
                                    {{ $page }}
                                </a>
                            </li>
                        @endforeach
                        <li class="rounded-r-lg bg-slate-150 dark:bg-navy-500">
                            @if ($agents->hasMorePages())
                                <a href="{{ $agents->nextPageUrl() }}" class="flex size-8 items-center justify-center rounded-lg text-slate-500 transition-colors hover:bg-slate-300 focus:bg-slate-300 active:bg-slate-300/80 dark:text-navy-200 dark:hover:bg-navy-450 dark:focus:bg-navy-450 dark:active:bg-navy-450/90">
                                    <svg xmlns="http://www.w3.org/2000/svg" class="size-4" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7" /></svg>
                                </a>
                            @else
                                <span class="flex size-8 items-center justify-center rounded-lg text-slate-400 dark:text-navy-300">
                                    <svg xmlns="http://www.w3.org/2000/svg" class="size-4" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7" /></svg>
                                </span>
                            @endif
                        </li>
                    </ol>
                @endif
            </div>
        </div>

        {{-- Create/Edit Modal --}}
        <template x-teleport="body">
            <div x-show="showCreateModal" x-transition.opacity class="fixed inset-0 z-100 flex items-center justify-center bg-slate-900/60" @click.self="showCreateModal = false">
                <div class="relative w-full max-w-lg max-h-[90vh] overflow-y-auto rounded-lg bg-white p-6 shadow-xl dark:bg-navy-700" @click.stop>
                    <h3 class="text-lg font-medium text-slate-700 dark:text-navy-100" x-text="editItem ? 'Edit Agent' : 'Add Agent'"></h3>

                    {{-- Create Form --}}
                    <form x-show="!editItem" method="POST" action="{{ route('tickets/settings-agents-store') }}" class="mt-4 space-y-4">
                        @csrf
                        @if($errors->any())
                            <div class="rounded-lg border border-error/30 bg-error/10 px-4 py-3">
                                <ul class="list-inside list-disc space-y-1 text-sm text-error">
                                    @foreach($errors->all() as $error)
                                        <li>{{ $error }}</li>
                                    @endforeach
                                </ul>
                            </div>
                        @endif
                        <label class="block">
                            <span class="font-medium text-slate-600 dark:text-navy-100">User <span class="text-error">*</span></span>
                            <select name="user_id" required
                                class="form-select mt-1.5 w-full rounded-lg border border-slate-300 bg-white px-3 py-2 hover:border-slate-400 focus:border-primary dark:border-navy-450 dark:bg-navy-700 dark:hover:border-navy-400 dark:focus:border-accent">
                                <option value="">— Select a user —</option>
                                @foreach($availableUsers as $user)
                                    <option value="{{ $user->id }}" {{ old('user_id') == $user->id ? 'selected' : '' }}>{{ $user->name }} ({{ $user->email }}) — {{ ucfirst($user->role) }}</option>
                                @endforeach
                            </select>
                        </label>
                        <label class="block">
                            <span class="font-medium text-slate-600 dark:text-navy-100">Display Name</span>
                            <input class="form-input mt-1.5 w-full rounded-lg border border-slate-300 bg-transparent px-3 py-2 placeholder:text-slate-400/70 hover:border-slate-400 focus:border-primary dark:border-navy-450 dark:hover:border-navy-400 dark:focus:border-accent" type="text" name="display_name" value="{{ old('display_name') }}" placeholder="Optional display name">
                        </label>
                        <label class="block">
                            <span class="font-medium text-slate-600 dark:text-navy-100">Specialization</span>
                            <input class="form-input mt-1.5 w-full rounded-lg border border-slate-300 bg-transparent px-3 py-2 placeholder:text-slate-400/70 hover:border-slate-400 focus:border-primary dark:border-navy-450 dark:hover:border-navy-400 dark:focus:border-accent" type="text" name="specialization" value="{{ old('specialization') }}" placeholder="e.g. Technical Support, Billing">
                        </label>
                        <label class="block">
                            <span class="font-medium text-slate-600 dark:text-navy-100">Max Tickets</span>
                            <input class="form-input mt-1.5 w-full rounded-lg border border-slate-300 bg-transparent px-3 py-2 placeholder:text-slate-400/70 hover:border-slate-400 focus:border-primary dark:border-navy-450 dark:hover:border-navy-400 dark:focus:border-accent" type="number" name="max_tickets" min="1" max="999" value="{{ old('max_tickets') }}" placeholder="Maximum concurrent tickets">
                        </label>
                        <div>
                            <span class="font-medium text-slate-600 dark:text-navy-100">Departments <span class="text-error">*</span></span>
                            <div class="mt-2 grid grid-cols-2 gap-2">
                                @forelse($departments as $dept)
                                    <label class="inline-flex items-center space-x-2">
                                        <input class="form-checkbox is-outline size-5 rounded border-slate-400/70 before:bg-primary checked:border-primary hover:border-primary focus:border-primary dark:border-navy-400 dark:before:bg-accent dark:checked:border-accent dark:hover:border-accent dark:focus:border-accent" type="checkbox" name="departments[]" value="{{ $dept->id }}" {{ is_array(old('departments')) && in_array($dept->id, old('departments')) ? 'checked' : '' }}>
                                        <span class="text-sm text-slate-600 dark:text-navy-100">{{ $dept->name }}@if(!$dept->status) <span class="text-xs text-warning">(Inactive)</span>@endif</span>
                                    </label>
                                @empty
                                    <p class="col-span-2 text-sm text-slate-400 dark:text-navy-300">No departments available. <a href="{{ route('config/departments') }}" class="text-primary hover:text-primary-focus dark:text-accent-light">Create one first</a>.</p>
                                @endforelse
                            </div>
                        </div>
                        <label class="inline-flex items-center space-x-2">
                            <input class="form-switch h-5 w-10 rounded-full bg-slate-300 before:rounded-full before:bg-white checked:bg-primary checked:before:bg-white dark:bg-navy-900 dark:before:bg-navy-300 dark:checked:bg-accent dark:checked:before:bg-white" type="checkbox" name="status" value="1" checked>
                            <span class="text-sm text-slate-600 dark:text-navy-100">Active</span>
                        </label>
                        <div class="flex justify-end space-x-2 pt-2">
                            <button type="button" @click="showCreateModal = false" class="btn border border-slate-300 font-medium text-slate-800 hover:bg-slate-150 dark:border-navy-450 dark:text-navy-50 dark:hover:bg-navy-500">Cancel</button>
                            <button type="submit" class="btn bg-primary font-medium text-white hover:bg-primary-focus dark:bg-accent dark:hover:bg-accent-focus">Create</button>
                        </div>
                    </form>

                    {{-- Edit Form --}}
                    <template x-if="editItem">
                        <form method="POST" :action="`/tickets/settings/agents/${editItem.id}`" class="mt-4 space-y-4">
                            @csrf @method('PUT')
                            <label class="block">
                                <span class="font-medium text-slate-600 dark:text-navy-100">Display Name</span>
                                <input class="form-input mt-1.5 w-full rounded-lg border border-slate-300 bg-transparent px-3 py-2 placeholder:text-slate-400/70 hover:border-slate-400 focus:border-primary dark:border-navy-450 dark:hover:border-navy-400 dark:focus:border-accent" type="text" name="display_name" x-model="editItem.display_name" placeholder="Optional display name">
                            </label>
                            <label class="block">
                                <span class="font-medium text-slate-600 dark:text-navy-100">Specialization</span>
                                <input class="form-input mt-1.5 w-full rounded-lg border border-slate-300 bg-transparent px-3 py-2 placeholder:text-slate-400/70 hover:border-slate-400 focus:border-primary dark:border-navy-450 dark:hover:border-navy-400 dark:focus:border-accent" type="text" name="specialization" x-model="editItem.specialization" placeholder="e.g. Technical Support, Billing">
                            </label>
                            <label class="block">
                                <span class="font-medium text-slate-600 dark:text-navy-100">Max Tickets</span>
                                <input class="form-input mt-1.5 w-full rounded-lg border border-slate-300 bg-transparent px-3 py-2 placeholder:text-slate-400/70 hover:border-slate-400 focus:border-primary dark:border-navy-450 dark:hover:border-navy-400 dark:focus:border-accent" type="number" name="max_tickets" x-model="editItem.max_tickets" min="1" max="999" placeholder="Maximum concurrent tickets">
                            </label>
                            <div>
                                <span class="font-medium text-slate-600 dark:text-navy-100">Departments <span class="text-error">*</span></span>
                                <div class="mt-2 grid grid-cols-2 gap-2">
                                    @foreach($departments as $dept)
                                        <label class="inline-flex items-center space-x-2">
                                            <input class="form-checkbox is-outline size-5 rounded border-slate-400/70 before:bg-primary checked:border-primary hover:border-primary focus:border-primary dark:border-navy-400 dark:before:bg-accent dark:checked:border-accent dark:hover:border-accent dark:focus:border-accent"
                                                type="checkbox" name="departments[]" value="{{ $dept->id }}"
                                                :checked="editDepartments.includes('{{ $dept->id }}')">
                                            <span class="text-sm text-slate-600 dark:text-navy-100">{{ $dept->name }}@if(!$dept->status) <span class="text-xs text-warning">(Inactive)</span>@endif</span>
                                        </label>
                                    @endforeach
                                </div>
                            </div>
                            <div class="flex items-center space-x-6">
                                <label class="inline-flex items-center space-x-2">
                                    <input class="form-switch h-5 w-10 rounded-full bg-slate-300 before:rounded-full before:bg-white checked:bg-primary checked:before:bg-white dark:bg-navy-900 dark:before:bg-navy-300 dark:checked:bg-accent dark:checked:before:bg-white" type="checkbox" name="is_available" value="1" :checked="editItem.is_available">
                                    <span class="text-sm text-slate-600 dark:text-navy-100">Available</span>
                                </label>
                                <label class="inline-flex items-center space-x-2">
                                    <input class="form-switch h-5 w-10 rounded-full bg-slate-300 before:rounded-full before:bg-white checked:bg-primary checked:before:bg-white dark:bg-navy-900 dark:before:bg-navy-300 dark:checked:bg-accent dark:checked:before:bg-white" type="checkbox" name="status" value="1" :checked="editItem.status">
                                    <span class="text-sm text-slate-600 dark:text-navy-100">Active</span>
                                </label>
                            </div>
                            <div class="flex justify-end space-x-2 pt-2">
                                <button type="button" @click="showCreateModal = false" class="btn border border-slate-300 font-medium text-slate-800 hover:bg-slate-150 dark:border-navy-450 dark:text-navy-50 dark:hover:bg-navy-500">Cancel</button>
                                <button type="submit" class="btn bg-primary font-medium text-white hover:bg-primary-focus dark:bg-accent dark:hover:bg-accent-focus">Update</button>
                            </div>
                        </form>
                    </template>
                </div>
            </div>
        </template>
    </main>

</x-app-layout>
