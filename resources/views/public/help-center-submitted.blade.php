<x-base-layout title="Ticket Submitted">
    <div class="min-h-100vh flex w-full flex-col bg-slate-50 dark:bg-navy-900">
        {{-- Header --}}
        <header class="sticky top-0 z-20 border-b border-slate-150 bg-white/80 backdrop-blur dark:border-navy-700 dark:bg-navy-800/80">
            <div class="mx-auto flex h-16 max-w-5xl items-center justify-between px-4 sm:px-6">
                <a href="{{ route('help-center') }}" class="flex items-center space-x-2">
                    <img class="size-8" src="{{ asset('images/app-logo.svg') }}" alt="logo" />
                    <span class="text-lg font-semibold text-slate-700 dark:text-navy-100">{{ config('app.name') }}</span>
                </a>
                <a href="{{ route('loginView') }}" class="btn rounded-full bg-primary px-4 py-1.5 text-sm font-medium text-white hover:bg-primary-focus dark:bg-accent dark:hover:bg-accent-focus">
                    Sign In
                </a>
            </div>
        </header>

        {{-- Success Content --}}
        <main class="flex flex-1 items-center justify-center px-4 py-16">
            <div class="w-full max-w-lg text-center">
                <div class="mx-auto flex size-20 items-center justify-center rounded-full bg-success/10">
                    <svg xmlns="http://www.w3.org/2000/svg" class="size-10 text-success" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/></svg>
                </div>

                <h1 class="mt-6 text-2xl font-bold text-slate-700 dark:text-navy-100">Ticket Submitted Successfully!</h1>

                <p class="mt-3 text-slate-500 dark:text-navy-300">
                    {{ $settings['help_page_success_message'] ?? 'Thank you for reaching out. Our support team will review your ticket and get back to you as soon as possible.' }}
                </p>

                <div class="mt-8 rounded-lg border border-slate-200 bg-white p-5 dark:border-navy-500 dark:bg-navy-700">
                    <div class="flex items-center justify-between">
                        <span class="text-sm text-slate-400 dark:text-navy-300">Ticket Number</span>
                        <span class="font-mono text-lg font-bold text-primary dark:text-accent-light">{{ $ticket->ticket_no }}</span>
                    </div>
                    <div class="my-3 h-px bg-slate-200 dark:bg-navy-500"></div>
                    <div class="flex items-center justify-between">
                        <span class="text-sm text-slate-400 dark:text-navy-300">Subject</span>
                        <span class="text-sm font-medium text-slate-700 dark:text-navy-100">{{ Str::limit($ticket->subject, 40) }}</span>
                    </div>
                </div>

                <p class="mt-4 text-sm text-slate-400 dark:text-navy-300">
                    Please save your ticket number <strong>{{ $ticket->ticket_no }}</strong> for future reference.
                </p>

                <div class="mt-8 flex flex-col items-center space-y-3 sm:flex-row sm:justify-center sm:space-x-3 sm:space-y-0">
                    <a href="{{ route('help-center') }}" class="btn space-x-2 rounded-full bg-primary px-6 py-2 font-medium text-white hover:bg-primary-focus dark:bg-accent dark:hover:bg-accent-focus">
                        <svg xmlns="http://www.w3.org/2000/svg" class="size-4" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6v6m0 0v6m0-6h6m-6 0H6"/></svg>
                        <span>Submit Another Ticket</span>
                    </a>
                    @if(!empty($settings['help_page_show_track']) && $settings['help_page_show_track'] === '1')
                        <a href="{{ route('help-center.track') }}" class="btn space-x-2 rounded-full border border-slate-300 px-6 py-2 font-medium text-slate-600 hover:bg-slate-100 dark:border-navy-500 dark:text-navy-200 dark:hover:bg-navy-600">
                            <svg xmlns="http://www.w3.org/2000/svg" class="size-4" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/></svg>
                            <span>Track Your Ticket</span>
                        </a>
                    @endif
                </div>
            </div>
        </main>

        {{-- Footer --}}
        <footer class="mt-auto border-t border-slate-200 bg-white py-6 dark:border-navy-700 dark:bg-navy-800">
            <div class="mx-auto max-w-5xl px-4 text-center text-sm text-slate-400 dark:text-navy-300 sm:px-6">
                <p>&copy; {{ date('Y') }} {{ config('app.name') }}. All rights reserved.</p>
            </div>
        </footer>
    </div>
</x-base-layout>
