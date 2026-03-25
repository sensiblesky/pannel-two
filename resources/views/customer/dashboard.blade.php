<x-customer-layout title="Dashboard">
    <div class="flex items-center justify-between">
        <h2 class="text-xl font-medium text-slate-800 dark:text-navy-50 lg:text-2xl">Dashboard</h2>
        <a href="{{ route('customer.tickets.create') }}" class="btn space-x-2 bg-primary font-medium text-white shadow-lg shadow-primary/50 hover:bg-primary-focus focus:bg-primary-focus active:bg-primary-focus/90 dark:bg-accent dark:shadow-accent/50 dark:hover:bg-accent-focus dark:focus:bg-accent-focus dark:active:bg-accent/90">
            <svg xmlns="http://www.w3.org/2000/svg" class="size-5" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/></svg>
            <span>New Ticket</span>
        </a>
    </div>

    {{-- KPI Cards --}}
    <div class="mt-5 grid grid-cols-2 gap-4 sm:grid-cols-4">
        <div class="card px-4 py-4 sm:px-5">
            <p class="text-xs-plus uppercase tracking-wider text-slate-400 dark:text-navy-300">Total</p>
            <p class="mt-1 text-2xl font-semibold text-slate-700 dark:text-navy-100">{{ $totalTickets }}</p>
            <a href="{{ route('customer.tickets') }}" class="mt-2 inline-flex items-center space-x-1 text-xs text-primary hover:text-primary-focus dark:text-accent-light dark:hover:text-accent">
                <span>View all</span>
                <svg xmlns="http://www.w3.org/2000/svg" class="size-3" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/></svg>
            </a>
        </div>
        <div class="card px-4 py-4 sm:px-5">
            <p class="text-xs-plus uppercase tracking-wider text-slate-400 dark:text-navy-300">Open</p>
            <p class="mt-1 text-2xl font-semibold text-warning">{{ $totalOpen }}</p>
            <a href="{{ route('customer.tickets', ['view' => 'open']) }}" class="mt-2 inline-flex items-center space-x-1 text-xs text-primary hover:text-primary-focus dark:text-accent-light dark:hover:text-accent">
                <span>View open</span>
                <svg xmlns="http://www.w3.org/2000/svg" class="size-3" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/></svg>
            </a>
        </div>
        <div class="card px-4 py-4 sm:px-5">
            <p class="text-xs-plus uppercase tracking-wider text-slate-400 dark:text-navy-300">Pending</p>
            <p class="mt-1 text-2xl font-semibold text-info">{{ $totalPending }}</p>
        </div>
        <div class="card px-4 py-4 sm:px-5">
            <p class="text-xs-plus uppercase tracking-wider text-slate-400 dark:text-navy-300">Closed</p>
            <p class="mt-1 text-2xl font-semibold text-success">{{ $totalClosed }}</p>
            <a href="{{ route('customer.tickets', ['view' => 'closed']) }}" class="mt-2 inline-flex items-center space-x-1 text-xs text-primary hover:text-primary-focus dark:text-accent-light dark:hover:text-accent">
                <span>View closed</span>
                <svg xmlns="http://www.w3.org/2000/svg" class="size-3" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/></svg>
            </a>
        </div>
    </div>

    {{-- Recent Tickets --}}
    <div class="card mt-5">
        <div class="flex items-center justify-between border-b border-slate-150 px-4 py-3 dark:border-navy-600 sm:px-5">
            <h3 class="text-base font-medium text-slate-700 dark:text-navy-100">Recent Tickets</h3>
            <a href="{{ route('customer.tickets') }}" class="text-xs font-medium text-primary hover:text-primary-focus dark:text-accent-light dark:hover:text-accent">View All</a>
        </div>

        @if ($recentTickets->isEmpty())
            <div class="px-4 py-8 text-center sm:px-5">
                <svg xmlns="http://www.w3.org/2000/svg" class="mx-auto size-12 text-slate-300 dark:text-navy-400" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M20 13V6a2 2 0 00-2-2H6a2 2 0 00-2 2v7m16 0v5a2 2 0 01-2 2H6a2 2 0 01-2-2v-5m16 0h-2.586a1 1 0 00-.707.293l-2.414 2.414a1 1 0 01-.707.293h-3.172a1 1 0 01-.707-.293l-2.414-2.414A1 1 0 006.586 13H4"/></svg>
                <p class="mt-3 text-sm text-slate-400 dark:text-navy-300">No tickets yet</p>
                <a href="{{ route('customer.tickets.create') }}" class="btn mt-4 bg-primary font-medium text-white hover:bg-primary-focus focus:bg-primary-focus active:bg-primary-focus/90 dark:bg-accent dark:hover:bg-accent-focus dark:focus:bg-accent-focus dark:active:bg-accent/90">
                    Create Your First Ticket
                </a>
            </div>
        @else
            <div class="overflow-x-auto">
                <table class="w-full text-left">
                    <thead>
                        <tr class="border-b border-slate-200 dark:border-navy-500">
                            <th class="whitespace-nowrap px-4 py-3 text-xs-plus font-semibold uppercase text-slate-400 dark:text-navy-300 sm:px-5">Ticket</th>
                            <th class="whitespace-nowrap px-4 py-3 text-xs-plus font-semibold uppercase text-slate-400 dark:text-navy-300 sm:px-5">Subject</th>
                            <th class="whitespace-nowrap px-4 py-3 text-xs-plus font-semibold uppercase text-slate-400 dark:text-navy-300 sm:px-5">Status</th>
                            <th class="whitespace-nowrap px-4 py-3 text-xs-plus font-semibold uppercase text-slate-400 dark:text-navy-300 sm:px-5">Priority</th>
                            <th class="whitespace-nowrap px-4 py-3 text-xs-plus font-semibold uppercase text-slate-400 dark:text-navy-300 sm:px-5">Date</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach ($recentTickets as $ticket)
                            <tr class="border-b border-slate-150 dark:border-navy-500 hover:bg-slate-50 dark:hover:bg-navy-700">
                                <td class="whitespace-nowrap px-4 py-3 sm:px-5">
                                    <a href="{{ route('customer.tickets.show', $ticket->uuid) }}" class="font-medium text-primary hover:text-primary-focus dark:text-accent-light dark:hover:text-accent">{{ $ticket->ticket_no }}</a>
                                </td>
                                <td class="px-4 py-3 sm:px-5">
                                    <a href="{{ route('customer.tickets.show', $ticket->uuid) }}" class="text-sm text-slate-600 hover:text-slate-800 dark:text-navy-200 line-clamp-1">{{ $ticket->subject }}</a>
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
        @endif
    </div>
</x-customer-layout>
