<x-customer-layout title="My Tickets">

    @slot('script')
        <script>
            function customerTicketPoller() {
                return {
                    newCount: 0,
                    since: new Date().toISOString(),
                    alertSound: null,
                    interval: null,
                    start() {
                        fetch('{{ route("api/alert-sound") }}?type=ticket')
                            .then(r => r.json())
                            .then(data => { if (data.url) this.alertSound = new Audio(data.url); })
                            .catch(() => {});
                        this.interval = setInterval(() => this.poll(), 10000);
                    },
                    async poll() {
                        try {
                            const res = await fetch(`{{ route("customer.tickets.poll") }}?since=${encodeURIComponent(this.since)}`);
                            const data = await res.json();
                            if (data.count > 0) {
                                this.newCount += data.count;
                                this.since = data.timestamp;
                                if (this.alertSound) { this.alertSound.currentTime = 0; this.alertSound.play().catch(() => {}); }
                                data.updates.forEach(u => {
                                    const title = u.type === 'reply' ? `New reply on ${u.ticket_no}` : `Status updated on ${u.ticket_no}`;
                                    Swal.fire({ toast: true, position: 'top-end', icon: 'info', title, showConfirmButton: false, timer: 5000 });
                                });
                            }
                        } catch (e) {}
                    },
                    destroy() { if (this.interval) clearInterval(this.interval); }
                };
            }
        </script>
    @endslot

    {{-- Real-time Update Polling --}}
    <div x-data="customerTicketPoller()" x-init="start()">
        <template x-if="newCount > 0">
            <div class="mb-4 flex items-center justify-between rounded-lg bg-info/10 px-4 py-3 text-info dark:bg-info/15">
                <div class="flex items-center space-x-2">
                    <svg xmlns="http://www.w3.org/2000/svg" class="size-5" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 17h5l-1.405-1.405A2.032 2.032 0 0118 14.158V11a6.002 6.002 0 00-4-5.659V5a2 2 0 10-4 0v.341C7.67 6.165 6 8.388 6 11v3.159c0 .538-.214 1.055-.595 1.436L4 17h5m6 0v1a3 3 0 11-6 0v-1m6 0H9"/></svg>
                    <span class="text-sm font-medium" x-text="newCount + ' new update' + (newCount > 1 ? 's' : '') + ' on your tickets'"></span>
                </div>
                <button @click="window.location.reload()" class="btn px-4 py-1.5 text-xs+ font-medium text-white bg-info hover:bg-info-focus rounded-lg">Refresh</button>
            </div>
        </template>
    </div>

    <div class="flex items-center justify-between">
        <h2 class="text-xl font-medium text-slate-800 dark:text-navy-50 lg:text-2xl">My Tickets</h2>
        <a href="{{ route('customer.tickets.create') }}" class="btn space-x-2 bg-primary font-medium text-white shadow-lg shadow-primary/50 hover:bg-primary-focus focus:bg-primary-focus active:bg-primary-focus/90 dark:bg-accent dark:shadow-accent/50 dark:hover:bg-accent-focus dark:focus:bg-accent-focus dark:active:bg-accent/90">
            <svg xmlns="http://www.w3.org/2000/svg" class="size-5" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/></svg>
            <span>New Ticket</span>
        </a>
    </div>

    {{-- View Tabs --}}
    <div class="mt-4 flex space-x-1 rounded-lg bg-slate-100 p-1 dark:bg-navy-700">
        <a href="{{ route('customer.tickets', ['view' => 'all']) }}" class="rounded-md px-4 py-2 text-sm font-medium {{ $view === 'all' ? 'bg-white text-slate-800 shadow dark:bg-navy-500 dark:text-navy-100' : 'text-slate-500 hover:text-slate-700 dark:text-navy-300' }}">
            All <span class="ml-1 text-xs">({{ $viewCounts['all'] }})</span>
        </a>
        <a href="{{ route('customer.tickets', ['view' => 'open']) }}" class="rounded-md px-4 py-2 text-sm font-medium {{ $view === 'open' ? 'bg-white text-slate-800 shadow dark:bg-navy-500 dark:text-navy-100' : 'text-slate-500 hover:text-slate-700 dark:text-navy-300' }}">
            Open <span class="ml-1 text-xs">({{ $viewCounts['open'] }})</span>
        </a>
        <a href="{{ route('customer.tickets', ['view' => 'closed']) }}" class="rounded-md px-4 py-2 text-sm font-medium {{ $view === 'closed' ? 'bg-white text-slate-800 shadow dark:bg-navy-500 dark:text-navy-100' : 'text-slate-500 hover:text-slate-700 dark:text-navy-300' }}">
            Closed <span class="ml-1 text-xs">({{ $viewCounts['closed'] }})</span>
        </a>
    </div>

    {{-- Search --}}
    <div class="mt-4">
        <form method="GET" action="{{ route('customer.tickets') }}">
            <input type="hidden" name="view" value="{{ $view }}">
            <label class="relative flex">
                <input type="text" name="search" value="{{ request('search') }}" placeholder="Search tickets..." class="form-input w-full rounded-lg border border-slate-300 bg-transparent px-3 py-2 pl-9 placeholder:text-slate-400 hover:border-slate-400 focus:border-primary dark:border-navy-450 dark:hover:border-navy-400 dark:focus:border-accent" />
                <span class="pointer-events-none absolute flex h-full w-10 items-center justify-center text-slate-400">
                    <svg xmlns="http://www.w3.org/2000/svg" class="size-4.5" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/></svg>
                </span>
            </label>
        </form>
    </div>

    {{-- Tickets Table --}}
    <div class="card mt-4">
        @if ($tickets->isEmpty())
            <div class="px-4 py-8 text-center sm:px-5">
                <svg xmlns="http://www.w3.org/2000/svg" class="mx-auto size-12 text-slate-300 dark:text-navy-400" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M20 13V6a2 2 0 00-2-2H6a2 2 0 00-2 2v7m16 0v5a2 2 0 01-2 2H6a2 2 0 01-2-2v-5m16 0h-2.586a1 1 0 00-.707.293l-2.414 2.414a1 1 0 01-.707.293h-3.172a1 1 0 01-.707-.293l-2.414-2.414A1 1 0 006.586 13H4"/></svg>
                <p class="mt-3 text-sm text-slate-400 dark:text-navy-300">No tickets found</p>
            </div>
        @else
            <div class="overflow-x-auto">
                <table class="w-full text-left">
                    <thead>
                        <tr class="border-b border-slate-200 dark:border-navy-500">
                            <th class="whitespace-nowrap px-4 py-3 text-xs-plus font-semibold uppercase text-slate-400 dark:text-navy-300 sm:px-5">Ticket</th>
                            <th class="whitespace-nowrap px-4 py-3 text-xs-plus font-semibold uppercase text-slate-400 dark:text-navy-300 sm:px-5">Subject</th>
                            <th class="whitespace-nowrap px-4 py-3 text-xs-plus font-semibold uppercase text-slate-400 dark:text-navy-300 sm:px-5">Category</th>
                            <th class="whitespace-nowrap px-4 py-3 text-xs-plus font-semibold uppercase text-slate-400 dark:text-navy-300 sm:px-5">Status</th>
                            <th class="whitespace-nowrap px-4 py-3 text-xs-plus font-semibold uppercase text-slate-400 dark:text-navy-300 sm:px-5">Priority</th>
                            <th class="whitespace-nowrap px-4 py-3 text-xs-plus font-semibold uppercase text-slate-400 dark:text-navy-300 sm:px-5">Date</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach ($tickets as $ticket)
                            <tr class="border-b border-slate-150 dark:border-navy-500 hover:bg-slate-50 dark:hover:bg-navy-700">
                                <td class="whitespace-nowrap px-4 py-3 sm:px-5">
                                    <a href="{{ route('customer.tickets.show', $ticket->uuid) }}" class="font-medium text-primary hover:text-primary-focus dark:text-accent-light dark:hover:text-accent">{{ $ticket->ticket_no }}</a>
                                </td>
                                <td class="px-4 py-3 sm:px-5">
                                    <a href="{{ route('customer.tickets.show', $ticket->uuid) }}" class="text-sm text-slate-600 hover:text-slate-800 dark:text-navy-200 line-clamp-1">{{ $ticket->subject }}</a>
                                </td>
                                <td class="whitespace-nowrap px-4 py-3 text-sm text-slate-500 dark:text-navy-300 sm:px-5">
                                    {{ $ticket->category_name ?? '-' }}
                                </td>
                                <td class="whitespace-nowrap px-4 py-3 sm:px-5">
                                    <span class="badge rounded-full px-2.5 py-1 text-xs font-medium" style="background-color: {{ $ticket->status_color }}20; color: {{ $ticket->status_color }}">{{ $ticket->status_name }}</span>
                                </td>
                                <td class="whitespace-nowrap px-4 py-3 sm:px-5">
                                    @if ($ticket->priority_name)
                                        <span class="badge rounded-full px-2.5 py-1 text-xs font-medium" style="background-color: {{ $ticket->priority_color }}20; color: {{ $ticket->priority_color }}">{{ $ticket->priority_name }}</span>
                                    @endif
                                </td>
                                <td class="whitespace-nowrap px-4 py-3 text-xs text-slate-400 dark:text-navy-300 sm:px-5">
                                    {{ \Carbon\Carbon::parse($ticket->created_at)->format('M d, Y') }}
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>

            <div class="px-4 py-3 sm:px-5">
                {{ $tickets->links() }}
            </div>
        @endif
    </div>
</x-customer-layout>
