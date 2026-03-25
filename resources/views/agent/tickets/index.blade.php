<x-app-layout title="All Tickets" is-sidebar-open="true" is-header-blur="true">

    @slot('script')
        <script>
            function confirmDelete(id) {
                Swal.fire({
                    title: 'Are you sure?',
                    text: 'This ticket will be deleted.',
                    icon: 'warning',
                    showCancelButton: true,
                    confirmButtonColor: '#e53e3e',
                    confirmButtonText: 'Yes, delete it!',
                }).then((result) => {
                    if (result.isConfirmed) {
                        document.getElementById('delete-form-' + id).submit();
                    }
                });
            }

            function ticketPoller() {
                return {
                    newCount: 0,
                    since: new Date().toISOString(),
                    alertSound: null,
                    interval: null,
                    start() {
                        fetch('{{ route("api/alert-sound") }}?type=ticket')
                            .then(r => r.json())
                            .then(data => {
                                if (data.url) {
                                    this.alertSound = new Audio(data.url);
                                }
                            }).catch(() => {});

                        this.interval = setInterval(() => this.poll(), 10000);
                    },
                    async poll() {
                        try {
                            const res = await fetch(`{{ route("agent.tickets/poll") }}?since=${encodeURIComponent(this.since)}`);
                            const data = await res.json();
                            if (data.count > 0) {
                                this.newCount += data.count;
                                this.since = data.timestamp;
                                this.playAlert();
                                data.tickets.forEach(t => {
                                    Swal.fire({
                                        toast: true, position: 'top-end', icon: 'info',
                                        title: `New ticket: ${t.ticket_no}`,
                                        text: t.subject,
                                        showConfirmButton: false, timer: 5000,
                                    });
                                });
                            }
                        } catch (e) {}
                    },
                    playAlert() {
                        if (this.alertSound) {
                            this.alertSound.currentTime = 0;
                            this.alertSound.play().catch(() => {});
                        }
                    },
                    destroy() {
                        if (this.interval) clearInterval(this.interval);
                    }
                };
            }
        </script>
        @if(session('success'))
            <script>
                Swal.fire({ toast: true, position: 'top-end', icon: 'success', title: @json(session('success')), showConfirmButton: false, timer: 3000 });
            </script>
        @endif
    @endslot

    <main class="main-content w-full px-[var(--margin-x)] pb-8">

        {{-- Real-time New Ticket Polling --}}
        <div x-data="ticketPoller()" x-init="start()">
            <template x-if="newCount > 0">
                <div class="mt-4 flex items-center justify-between rounded-lg bg-info/10 px-4 py-3 text-info dark:bg-info/15">
                    <div class="flex items-center space-x-2">
                        <svg xmlns="http://www.w3.org/2000/svg" class="size-5" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 17h5l-1.405-1.405A2.032 2.032 0 0118 14.158V11a6.002 6.002 0 00-4-5.659V5a2 2 0 10-4 0v.341C7.67 6.165 6 8.388 6 11v3.159c0 .538-.214 1.055-.595 1.436L4 17h5m6 0v1a3 3 0 11-6 0v-1m6 0H9"/></svg>
                        <span class="text-sm font-medium" x-text="newCount + ' new ticket' + (newCount > 1 ? 's' : '') + ' received'"></span>
                    </div>
                    <button @click="window.location.reload()" class="btn px-4 py-1.5 text-xs+ font-medium text-white bg-info hover:bg-info-focus rounded-lg">
                        Refresh
                    </button>
                </div>
            </template>
        </div>

        {{-- Page Header --}}
        <div class="flex items-center justify-between py-5 lg:py-6">
            <div class="flex items-center space-x-4">
                <h2 class="text-xl font-medium text-slate-800 dark:text-navy-50 lg:text-2xl">All Tickets</h2>
                <div class="hidden h-full py-1 sm:flex">
                    <div class="h-full w-px bg-slate-300 dark:bg-navy-600"></div>
                </div>
                <ul class="hidden flex-wrap items-center space-x-2 sm:flex">
                    <li class="flex items-center space-x-2">
                        <a class="text-primary transition-colors hover:text-primary-focus dark:text-accent-light dark:hover:text-accent" href="{{ route('agent.tickets/dashboard') }}">Tickets</a>
                        <svg xmlns="http://www.w3.org/2000/svg" class="size-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/></svg>
                    </li>
                    <li>All Tickets</li>
                </ul>
            </div>
            <a href="{{ route('agent.tickets/create') }}" class="btn space-x-2 bg-primary font-medium text-white shadow-lg shadow-primary/50 hover:bg-primary-focus focus:bg-primary-focus active:bg-primary-focus/90 dark:bg-accent dark:shadow-accent/50 dark:hover:bg-accent-focus dark:focus:bg-accent-focus dark:active:bg-accent/90">
                <svg xmlns="http://www.w3.org/2000/svg" class="size-5" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/></svg>
                <span>New Ticket</span>
            </a>
        </div>

        {{-- View Tabs --}}
        <div class="is-scrollbar-hidden mb-4 overflow-x-auto rounded-lg bg-slate-100 dark:bg-navy-800">
            <div class="flex">
                @php
                    $tabs = [
                        'all' => 'All',
                        'open' => 'Open',
                        'pending' => 'Pending',
                        'resolved' => 'Resolved',
                        'overdue' => 'Overdue',
                        'mine' => 'My Tickets',
                    ];
                @endphp
                @foreach($tabs as $key => $label)
                    <a href="{{ route('agent.tickets/index', ['view' => $key]) }}"
                       class="shrink-0 px-4 py-2.5 text-sm font-medium transition-colors {{ $view === $key ? 'bg-white text-primary shadow-sm dark:bg-navy-700 dark:text-accent-light' : 'text-slate-500 hover:text-slate-700 dark:text-navy-200 dark:hover:text-navy-100' }}">
                        {{ $label }}
                        <span class="ml-1 text-xs {{ $view === $key ? 'text-primary dark:text-accent-light' : 'text-slate-400 dark:text-navy-300' }}">({{ $viewCounts[$key] ?? 0 }})</span>
                    </a>
                @endforeach
            </div>
        </div>

        {{-- Bulk Actions --}}
        <div x-data="{ selectedTickets: [], showBulk: false }" x-init="$watch('selectedTickets', value => showBulk = value.length > 0)">
            <div class="card mt-3">
                {{-- Bulk Action Bar --}}
                <div x-show="showBulk" x-collapse class="flex items-center justify-between border-b border-slate-200 bg-primary/5 px-4 py-3 dark:border-navy-500 dark:bg-accent/5">
                    <span class="text-sm font-medium text-slate-700 dark:text-navy-100"><span x-text="selectedTickets.length"></span> ticket(s) selected</span>
                    <div class="flex items-center space-x-2">
                        <form method="POST" action="{{ route('agent.tickets/bulk-action') }}" class="flex items-center space-x-2">
                            @csrf
                            <template x-for="id in selectedTickets" :key="id">
                                <input type="hidden" name="ticket_ids[]" :value="id">
                            </template>
                            <select name="action" class="form-select rounded-lg border border-slate-300 bg-white px-3 py-1.5 text-sm hover:border-slate-400 focus:border-primary dark:border-navy-450 dark:bg-navy-700 dark:hover:border-navy-400 dark:focus:border-accent">
                                <option value="">Bulk Action</option>
                                <option value="status">Change Status</option>
                                <option value="priority">Change Priority</option>
                                <option value="assign">Assign To</option>
                                <option value="delete">Delete</option>
                            </select>
                            {{-- Value: status/priority select OR agent typeahead --}}
                            <div x-data="{
                                    action: '',
                                    agentQuery: '', agentResults: [], agentId: '', agentText: '', agentOpen: false, agentLoading: false,
                                    async searchAgent() {
                                        if (this.agentQuery.length < 1) { this.agentResults = []; return; }
                                        this.agentLoading = true; this.agentOpen = true;
                                        try {
                                            const r = await fetch(`{{ route('agent.tickets/search-agents') }}?q=${encodeURIComponent(this.agentQuery)}`, { headers: {'Accept':'application/json','X-Requested-With':'XMLHttpRequest'} });
                                            this.agentResults = await r.json();
                                        } finally { this.agentLoading = false; }
                                    },
                                    selectAgent(item) { this.agentId = item.id; this.agentText = item.text; this.agentQuery = ''; this.agentOpen = false; this.agentResults = []; },
                                    clearAgent() { this.agentId = ''; this.agentText = ''; this.agentQuery = ''; }
                                }"
                                 @change.window="action = $el.closest('form').querySelector('[name=action]').value"
                                 class="flex items-center space-x-2">

                                {{-- Status / Priority select (hidden when action=assign) --}}
                                <div x-show="action !== 'assign'">
                                    <select name="value" class="form-select rounded-lg border border-slate-300 bg-white px-3 py-1.5 text-sm hover:border-slate-400 focus:border-primary dark:border-navy-450 dark:bg-navy-700 dark:hover:border-navy-400 dark:focus:border-accent">
                                        <option value="">Select Value</option>
                                        <optgroup label="Statuses">
                                            @foreach($statuses as $s)
                                                <option value="{{ $s->id }}">{{ $s->name }}</option>
                                            @endforeach
                                        </optgroup>
                                        <optgroup label="Priorities">
                                            @foreach($priorities as $p)
                                                <option value="{{ $p->id }}">{{ $p->name }}</option>
                                            @endforeach
                                        </optgroup>
                                    </select>
                                </div>

                                {{-- Agent typeahead (shown only when action=assign) --}}
                                <div x-show="action === 'assign'" class="relative" @click.away="agentOpen = false">
                                    <input type="hidden" name="value" :value="agentId">
                                    <div class="relative">
                                        <input type="text" x-model="agentQuery" @input.debounce.300ms="searchAgent()" @focus="agentOpen = true; if(agentQuery.length >= 1) searchAgent()" @keydown.escape="agentOpen = false"
                                               placeholder="Search agent..." autocomplete="off"
                                               class="form-input w-44 rounded-lg border border-slate-300 bg-white px-3 py-1.5 text-sm placeholder:text-slate-400/70 hover:border-slate-400 focus:border-primary dark:border-navy-450 dark:bg-navy-700 dark:hover:border-navy-400 dark:focus:border-accent">
                                        <button type="button" x-show="agentId" @click="clearAgent()" class="absolute inset-y-0 right-0 flex items-center pr-2 text-slate-400 hover:text-slate-600">
                                            <svg class="size-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg>
                                        </button>
                                    </div>
                                    <div x-show="agentText && !agentOpen" class="mt-0.5">
                                        <span class="inline-flex items-center rounded-full bg-primary/10 px-2 py-0.5 text-xs font-medium text-primary dark:bg-accent/10 dark:text-accent-light" x-text="agentText"></span>
                                    </div>
                                    <div x-show="agentOpen" x-transition class="absolute z-50 mt-1 max-h-52 w-56 overflow-auto rounded-lg border border-slate-200 bg-white shadow-lg dark:border-navy-500 dark:bg-navy-700">
                                        <div x-show="agentLoading" class="flex items-center justify-center p-3">
                                            <div class="size-4 animate-spin rounded-full border-2 border-primary/30 border-r-primary"></div>
                                            <span class="ml-2 text-xs text-slate-500">Searching...</span>
                                        </div>
                                        <template x-for="item in agentResults" :key="item.id">
                                            <div @click="selectAgent(item)" class="cursor-pointer px-3 py-2 text-sm text-slate-700 hover:bg-slate-100 dark:text-navy-100 dark:hover:bg-navy-600" x-text="item.text"></div>
                                        </template>
                                        <div x-show="!agentLoading && agentResults.length === 0 && agentQuery.length >= 1" class="p-3 text-center text-xs text-slate-500">No agents found.</div>
                                    </div>
                                </div>
                            </div>
                            <button type="submit" class="btn bg-slate-150 px-3 py-1.5 text-sm font-medium text-slate-800 hover:bg-slate-200 dark:bg-navy-500 dark:text-navy-50 dark:hover:bg-navy-450">Apply</button>
                        </form>
                    </div>
                </div>

                {{-- Search & Filters --}}
                <div class="flex flex-col space-y-4 px-4 py-4 sm:flex-row sm:items-center sm:justify-between sm:space-y-0 sm:px-5">
                    <form method="GET" action="{{ route('agent.tickets/index') }}" class="flex flex-1 flex-col space-y-3 sm:flex-row sm:items-center sm:space-x-3 sm:space-y-0">
                        <input type="hidden" name="view" value="{{ $view }}">
                        <div class="relative flex-1">
                            <span class="absolute flex size-full items-center justify-center pointer-events-none w-9">
                                <svg xmlns="http://www.w3.org/2000/svg" class="size-4 text-slate-400 transition-colors duration-200" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/></svg>
                            </span>
                            <input class="form-input peer w-full rounded-lg border border-slate-300 bg-transparent px-3 py-2 pl-9 placeholder:text-slate-400/70 hover:border-slate-400 focus:border-primary dark:border-navy-450 dark:hover:border-navy-400 dark:focus:border-accent"
                                   placeholder="Search tickets..." type="text" name="search" value="{{ request('search') }}">
                        </div>
                        <select name="status_id" class="form-select rounded-lg border border-slate-300 bg-white px-3 py-2 hover:border-slate-400 focus:border-primary dark:border-navy-450 dark:bg-navy-700 dark:hover:border-navy-400 dark:focus:border-accent">
                            <option value="">All Statuses</option>
                            @foreach($statuses as $s)
                                <option value="{{ $s->id }}" @selected(request('status_id') == $s->id)>{{ $s->name }}</option>
                            @endforeach
                        </select>
                        <select name="priority_id" class="form-select rounded-lg border border-slate-300 bg-white px-3 py-2 hover:border-slate-400 focus:border-primary dark:border-navy-450 dark:bg-navy-700 dark:hover:border-navy-400 dark:focus:border-accent">
                            <option value="">All Priorities</option>
                            @foreach($priorities as $p)
                                <option value="{{ $p->id }}" @selected(request('priority_id') == $p->id)>{{ $p->name }}</option>
                            @endforeach
                        </select>
                        {{-- Agent filter typeahead --}}
                        <div x-data="{
                                searchUrl: '{{ route('agent.tickets/search-agents') }}',
                                query: '', results: [], open: false, loading: false,
                                selectedId: '{{ $selectedAgentId ?? '' }}',
                                selectedText: '{{ addslashes($selectedAgentName ?? '') }}',
                                async search() {
                                    if (this.query.length < 1) { this.results = []; return; }
                                    this.loading = true; this.open = true;
                                    try {
                                        const r = await fetch(`${this.searchUrl}?q=${encodeURIComponent(this.query)}`, { headers: {'Accept':'application/json','X-Requested-With':'XMLHttpRequest'} });
                                        this.results = await r.json();
                                    } finally { this.loading = false; }
                                },
                                select(item) { this.selectedId = item.id; this.selectedText = item.text; this.query = ''; this.open = false; this.results = []; },
                                clear() { this.selectedId = ''; this.selectedText = ''; this.query = ''; this.results = []; }
                            }" class="relative" @click.away="open = false">
                            <input type="hidden" name="assigned_to" :value="selectedId">
                            <div class="relative">
                                <input type="text" x-model="query"
                                       @input.debounce.300ms="search()"
                                       @focus="open = true; if(query.length >= 1) search()"
                                       @keydown.escape="open = false"
                                       :placeholder="selectedText || 'All Agents'"
                                       autocomplete="off"
                                       class="form-input w-44 rounded-lg border border-slate-300 bg-transparent px-3 py-2 placeholder:text-slate-400/70 hover:border-slate-400 focus:border-primary dark:border-navy-450 dark:bg-navy-700 dark:hover:border-navy-400 dark:focus:border-accent">
                                <button type="button" x-show="selectedId" @click="clear()" class="absolute inset-y-0 right-0 flex items-center pr-2.5 text-slate-400 hover:text-slate-600 dark:hover:text-navy-200">
                                    <svg class="size-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg>
                                </button>
                            </div>
                            <div x-show="open" x-transition class="absolute z-50 mt-1 max-h-60 w-56 overflow-auto rounded-lg border border-slate-200 bg-white shadow-lg dark:border-navy-500 dark:bg-navy-700">
                                <div x-show="loading" class="flex items-center justify-center p-3">
                                    <div class="size-4 animate-spin rounded-full border-2 border-primary/30 border-r-primary"></div>
                                    <span class="ml-2 text-xs text-slate-500">Searching...</span>
                                </div>
                                <template x-for="item in results" :key="item.id">
                                    <div @click="select(item)" class="cursor-pointer px-3 py-2 text-sm text-slate-700 hover:bg-slate-100 dark:text-navy-100 dark:hover:bg-navy-600" x-text="item.text"></div>
                                </template>
                                <div x-show="!loading && results.length === 0 && query.length >= 1" class="p-3 text-center text-xs text-slate-500">No agents found.</div>
                            </div>
                        </div>
                        <button type="submit" class="btn bg-slate-150 font-medium text-slate-800 hover:bg-slate-200 focus:bg-slate-200 active:bg-slate-200/80 dark:bg-navy-500 dark:text-navy-50 dark:hover:bg-navy-450 dark:focus:bg-navy-450 dark:active:bg-navy-450/90">Filter</button>
                        <a href="{{ route('agent.tickets/index', ['view' => $view]) }}" class="btn border border-slate-300 font-medium text-slate-800 hover:bg-slate-150 dark:border-navy-450 dark:text-navy-50 dark:hover:bg-navy-500">Clear</a>
                    </form>
                </div>

                {{-- Table --}}
                <div class="is-scrollbar-hidden min-w-full overflow-x-auto">
                    <table class="is-hoverable w-full text-left">
                        <thead>
                            <tr>
                                <th class="whitespace-nowrap bg-slate-200 px-4 py-3 font-semibold uppercase text-slate-800 dark:bg-navy-800 dark:text-navy-100 lg:px-5 rounded-tl-lg">
                                    <input type="checkbox" class="form-checkbox is-basic size-4 rounded border-slate-400/70 checked:bg-primary checked:border-primary hover:border-primary focus:border-primary dark:border-navy-400 dark:checked:bg-accent dark:checked:border-accent dark:hover:border-accent dark:focus:border-accent"
                                           @change="selectedTickets = $event.target.checked ? Array.from(document.querySelectorAll('.ticket-checkbox')).map(cb => cb.value) : []">
                                </th>
                                <th class="whitespace-nowrap bg-slate-200 px-4 py-3 font-semibold uppercase text-slate-800 dark:bg-navy-800 dark:text-navy-100 lg:px-5">Ticket</th>
                                <th class="whitespace-nowrap bg-slate-200 px-4 py-3 font-semibold uppercase text-slate-800 dark:bg-navy-800 dark:text-navy-100 lg:px-5">Subject</th>
                                <th class="whitespace-nowrap bg-slate-200 px-4 py-3 font-semibold uppercase text-slate-800 dark:bg-navy-800 dark:text-navy-100 lg:px-5">Customer</th>
                                <th class="whitespace-nowrap bg-slate-200 px-4 py-3 font-semibold uppercase text-slate-800 dark:bg-navy-800 dark:text-navy-100 lg:px-5">Status</th>
                                <th class="whitespace-nowrap bg-slate-200 px-4 py-3 font-semibold uppercase text-slate-800 dark:bg-navy-800 dark:text-navy-100 lg:px-5">Priority</th>
                                <th class="whitespace-nowrap bg-slate-200 px-4 py-3 font-semibold uppercase text-slate-800 dark:bg-navy-800 dark:text-navy-100 lg:px-5">Agent</th>
                                <th class="whitespace-nowrap bg-slate-200 px-4 py-3 font-semibold uppercase text-slate-800 dark:bg-navy-800 dark:text-navy-100 lg:px-5">Date</th>
                                <th class="whitespace-nowrap bg-slate-200 px-4 py-3 font-semibold uppercase text-slate-800 dark:bg-navy-800 dark:text-navy-100 lg:px-5 rounded-tr-lg">Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($tickets as $ticket)
                                <tr class="border-y border-transparent border-b-slate-200 dark:border-b-navy-500">
                                    <td class="whitespace-nowrap px-4 py-3 sm:px-5">
                                        <input type="checkbox" class="ticket-checkbox form-checkbox is-basic size-4 rounded border-slate-400/70 checked:bg-primary checked:border-primary hover:border-primary focus:border-primary dark:border-navy-400 dark:checked:bg-accent dark:checked:border-accent dark:hover:border-accent dark:focus:border-accent"
                                               value="{{ $ticket->id }}"
                                               :checked="selectedTickets.includes('{{ $ticket->id }}')"
                                               @change="$event.target.checked ? selectedTickets.push('{{ $ticket->id }}') : selectedTickets = selectedTickets.filter(i => i !== '{{ $ticket->id }}')">
                                    </td>
                                    <td class="whitespace-nowrap px-4 py-3 sm:px-5">
                                        <a href="{{ route('agent.tickets/show', $ticket->uuid) }}" class="font-medium text-primary hover:text-primary-focus dark:text-accent-light dark:hover:text-accent">{{ $ticket->ticket_no }}</a>
                                    </td>
                                    <td class="px-4 py-3 sm:px-5">
                                        <a href="{{ route('agent.tickets/show', $ticket->uuid) }}" class="max-w-[250px] truncate block text-sm font-medium text-slate-700 hover:text-primary dark:text-navy-100 dark:hover:text-accent-light">{{ $ticket->subject }}</a>
                                        @if($ticket->category_name)
                                            <span class="text-xs text-slate-400 dark:text-navy-300">{{ $ticket->category_name }}</span>
                                        @endif
                                    </td>
                                    <td class="whitespace-nowrap px-4 py-3 sm:px-5">
                                        <div class="flex items-center space-x-2">
                                            <div class="avatar flex size-8">
                                                <div class="is-initial rounded-full bg-primary/10 text-xs font-medium uppercase text-primary dark:bg-accent/10 dark:text-accent-light">{{ strtoupper(substr(trim($ticket->customer_name), 0, 1)) }}</div>
                                            </div>
                                            <div>
                                                <p class="text-sm font-medium text-slate-700 dark:text-navy-100">{{ $ticket->customer_name }}</p>
                                                <p class="text-xs text-slate-400 dark:text-navy-300">{{ $ticket->customer_email }}</p>
                                            </div>
                                        </div>
                                    </td>
                                    <td class="whitespace-nowrap px-4 py-3 sm:px-5">
                                        <div class="badge rounded-full px-2 py-1 text-xs" style="background-color: {{ $ticket->status_color }}20; color: {{ $ticket->status_color }}">{{ $ticket->status_name }}</div>
                                    </td>
                                    <td class="whitespace-nowrap px-4 py-3 sm:px-5">
                                        <div class="badge rounded-full px-2 py-1 text-xs" style="background-color: {{ $ticket->priority_color }}20; color: {{ $ticket->priority_color }}">{{ $ticket->priority_name }}</div>
                                    </td>
                                    <td class="whitespace-nowrap px-4 py-3 sm:px-5">
                                        @if($ticket->agent_name)
                                            <div class="flex items-center space-x-2">
                                                <div class="avatar flex size-7">
                                                    <div class="is-initial rounded-full bg-info/10 text-xs text-info">{{ strtoupper(substr($ticket->agent_name, 0, 1)) }}</div>
                                                </div>
                                                <span class="text-sm">{{ $ticket->agent_name }}</span>
                                            </div>
                                        @else
                                            <span class="text-xs italic text-slate-400 dark:text-navy-300">Unassigned</span>
                                        @endif
                                    </td>
                                    <td class="whitespace-nowrap px-4 py-3 sm:px-5">
                                        <span class="text-xs text-slate-400 dark:text-navy-300">{{ \Carbon\Carbon::parse($ticket->created_at)->format('M d, Y') }}</span>
                                        @if($ticket->due_at && \Carbon\Carbon::parse($ticket->due_at)->isPast() && !$ticket->is_closed)
                                            <span class="mt-0.5 block text-xs font-medium text-error">Overdue</span>
                                        @endif
                                    </td>
                                    <td class="whitespace-nowrap px-4 py-3 sm:px-5">
                                        <div x-data="usePopper({placement:'bottom-end',offset:4})" @click.outside="if(isShowPopper) isShowPopper = false" class="inline-flex">
                                            <button x-ref="popperRef" @click="isShowPopper = !isShowPopper" class="btn size-8 rounded-full p-0 hover:bg-slate-300/20 focus:bg-slate-300/20 active:bg-slate-300/25 dark:hover:bg-navy-300/20 dark:focus:bg-navy-300/20 dark:active:bg-navy-300/25">
                                                <svg xmlns="http://www.w3.org/2000/svg" class="size-5" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 12h.01M12 12h.01M19 12h.01M6 12a1 1 0 11-2 0 1 1 0 012 0zm7 0a1 1 0 11-2 0 1 1 0 012 0zm7 0a1 1 0 11-2 0 1 1 0 012 0z"/></svg>
                                            </button>
                                            <div x-ref="popperRoot" class="popper-root" :class="isShowPopper && 'show'">
                                                <div class="popper-box rounded-md border border-slate-150 bg-white py-1.5 font-inter dark:border-navy-500 dark:bg-navy-700">
                                                    <ul>
                                                        <li>
                                                            <a href="{{ route('agent.tickets/show', $ticket->uuid) }}" class="flex h-8 items-center px-3 pr-8 font-medium tracking-wide text-slate-600 outline-hidden transition-all hover:bg-slate-100 hover:text-slate-800 focus:bg-slate-100 focus:text-slate-800 dark:text-navy-100 dark:hover:bg-navy-600 dark:hover:text-navy-50 dark:focus:bg-navy-600 dark:focus:text-navy-50">
                                                                View
                                                            </a>
                                                        </li>
                                                        <li>
                                                            <button @click="confirmDelete('{{ $ticket->uuid }}')" class="flex h-8 w-full items-center px-3 pr-8 font-medium tracking-wide text-error outline-hidden transition-all hover:bg-error/10 focus:bg-error/10">
                                                                Delete
                                                            </button>
                                                        </li>
                                                    </ul>
                                                </div>
                                            </div>
                                        </div>
                                        <form id="delete-form-{{ $ticket->uuid }}" action="{{ route('agent.tickets/destroy', $ticket->uuid) }}" method="POST" class="hidden">
                                            @csrf
                                            @method('DELETE')
                                        </form>
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="9" class="px-4 py-8 text-center">
                                        <div class="flex flex-col items-center">
                                            <svg xmlns="http://www.w3.org/2000/svg" class="size-16 text-slate-300 dark:text-navy-400" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1" d="M7 8h10M7 12h4m1 8l-4-4H5a2 2 0 01-2-2V6a2 2 0 012-2h14a2 2 0 012 2v8a2 2 0 01-2 2h-3l-4 4z"/></svg>
                                            <p class="mt-2 text-slate-400 dark:text-navy-300">No tickets found</p>
                                            <a href="{{ route('agent.tickets/create') }}" class="btn mt-4 bg-primary text-sm font-medium text-white hover:bg-primary-focus dark:bg-accent dark:hover:bg-accent-focus">Create First Ticket</a>
                                        </div>
                                    </td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>

                {{-- Pagination --}}
                @if($tickets->hasPages())
                    <div class="flex flex-col justify-between space-y-4 px-4 py-4 sm:flex-row sm:items-center sm:space-y-0 sm:px-5">
                        <div class="text-xs-plus text-slate-400 dark:text-navy-300">
                            Showing {{ $tickets->firstItem() }} to {{ $tickets->lastItem() }} of {{ $tickets->total() }} entries
                        </div>
                        <ol class="pagination">
                            @if($tickets->onFirstPage())
                                <li class="rounded-l-lg bg-slate-150 dark:bg-navy-500">
                                    <span class="flex size-8 items-center justify-center rounded-lg text-slate-400 dark:text-navy-300">
                                        <svg xmlns="http://www.w3.org/2000/svg" class="size-4" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7"/></svg>
                                    </span>
                                </li>
                            @else
                                <li class="rounded-l-lg bg-slate-150 dark:bg-navy-500">
                                    <a href="{{ $tickets->previousPageUrl() }}" class="flex size-8 items-center justify-center rounded-lg text-slate-500 transition-colors hover:bg-slate-300 focus:bg-slate-300 active:bg-slate-300/80 dark:text-navy-200 dark:hover:bg-navy-450 dark:focus:bg-navy-450 dark:active:bg-navy-450/90">
                                        <svg xmlns="http://www.w3.org/2000/svg" class="size-4" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7"/></svg>
                                    </a>
                                </li>
                            @endif
                            @foreach($tickets->getUrlRange(max(1, $tickets->currentPage() - 2), min($tickets->lastPage(), $tickets->currentPage() + 2)) as $page => $url)
                                <li class="{{ $page == $tickets->currentPage() ? 'bg-slate-150 dark:bg-navy-500' : 'bg-slate-150 dark:bg-navy-500' }}">
                                    <a href="{{ $url }}" class="flex size-8 items-center justify-center rounded-lg {{ $page == $tickets->currentPage() ? 'bg-primary font-medium text-white dark:bg-accent' : 'text-slate-500 transition-colors hover:bg-slate-300 focus:bg-slate-300 active:bg-slate-300/80 dark:text-navy-200 dark:hover:bg-navy-450 dark:focus:bg-navy-450 dark:active:bg-navy-450/90' }}">{{ $page }}</a>
                                </li>
                            @endforeach
                            @if($tickets->hasMorePages())
                                <li class="rounded-r-lg bg-slate-150 dark:bg-navy-500">
                                    <a href="{{ $tickets->nextPageUrl() }}" class="flex size-8 items-center justify-center rounded-lg text-slate-500 transition-colors hover:bg-slate-300 focus:bg-slate-300 active:bg-slate-300/80 dark:text-navy-200 dark:hover:bg-navy-450 dark:focus:bg-navy-450 dark:active:bg-navy-450/90">
                                        <svg xmlns="http://www.w3.org/2000/svg" class="size-4" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/></svg>
                                    </a>
                                </li>
                            @else
                                <li class="rounded-r-lg bg-slate-150 dark:bg-navy-500">
                                    <span class="flex size-8 items-center justify-center rounded-lg text-slate-400 dark:text-navy-300">
                                        <svg xmlns="http://www.w3.org/2000/svg" class="size-4" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/></svg>
                                    </span>
                                </li>
                            @endif
                        </ol>
                    </div>
                @endif
            </div>
        </div>
    </main>

</x-app-layout>
