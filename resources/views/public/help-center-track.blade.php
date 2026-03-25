<x-base-layout title="Track Your Ticket">
    <div class="min-h-100vh flex w-full flex-col bg-slate-50 dark:bg-navy-900">
        {{-- Header --}}
        <header class="sticky top-0 z-20 border-b border-slate-150 bg-white/80 backdrop-blur dark:border-navy-700 dark:bg-navy-800/80">
            <div class="mx-auto flex h-16 max-w-5xl items-center justify-between px-4 sm:px-6">
                <a href="{{ route('help-center') }}" class="flex items-center space-x-2">
                    <img class="size-8" src="{{ asset('images/app-logo.svg') }}" alt="logo" />
                    <span class="text-lg font-semibold text-slate-700 dark:text-navy-100">{{ config('app.name') }}</span>
                </a>
                <div class="flex items-center space-x-3">
                    <a href="{{ route('help-center') }}" class="btn space-x-1.5 rounded-full border border-slate-300 px-4 py-1.5 text-sm font-medium text-slate-600 hover:bg-slate-100 dark:border-navy-500 dark:text-navy-200 dark:hover:bg-navy-600">
                        <svg xmlns="http://www.w3.org/2000/svg" class="size-4" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 5v2m0 4v2m0 4v2M5 5a2 2 0 00-2 2v3a2 2 0 110 4v3a2 2 0 002 2h14a2 2 0 002-2v-3a2 2 0 110-4V7a2 2 0 00-2-2H5z"/></svg>
                        <span>New Ticket</span>
                    </a>
                    <a href="{{ route('loginView') }}" class="btn rounded-full bg-primary px-4 py-1.5 text-sm font-medium text-white hover:bg-primary-focus dark:bg-accent dark:hover:bg-accent-focus">
                        Sign In
                    </a>
                </div>
            </div>
        </header>

        {{-- Main --}}
        <main class="mx-auto w-full max-w-lg px-4 py-16 sm:px-6">
            <div class="text-center">
                <div class="mx-auto flex size-14 items-center justify-center rounded-full bg-info/10">
                    <svg xmlns="http://www.w3.org/2000/svg" class="size-7 text-info" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/></svg>
                </div>
                <h1 class="mt-4 text-2xl font-bold text-slate-700 dark:text-navy-100">Track Your Ticket</h1>
                <p class="mt-2 text-slate-400 dark:text-navy-300">Enter your ticket number and the email or phone number used when creating the ticket.</p>
            </div>

            <form method="GET" action="{{ route('help-center.track') }}" class="mt-8 space-y-4"
                  x-data="{
                      mode: 'email',
                      digits: '',
                      emailVal: '{{ request('email_or_phone') && !str_contains(request('email_or_phone'), '@') ? '' : request('email_or_phone') }}',
                      get full() { return '+255' + this.digits; },
                      onPhoneInput(e) {
                          let v = e.target.value.replace(/\D/g, '');
                          if (v.startsWith('255')) v = v.slice(3);
                          if (v.startsWith('0')) v = v.slice(1);
                          this.digits = v.slice(0, 9);
                      },
                      init() {
                          @php
                              $eop = request('email_or_phone', '');
                              $isPhone = $eop && !str_contains($eop, '@');
                          @endphp
                          @if($isPhone)
                              this.mode = 'phone';
                              let raw = '{{ preg_replace('/^\+?255/', '', preg_replace('/^0/', '', request('email_or_phone', ''))) }}';
                              this.digits = raw.replace(/\D/g, '').slice(0, 9);
                          @endif
                      }
                  }">

                <label class="block">
                    <span class="text-sm font-medium text-slate-600 dark:text-navy-100">Ticket Number <span class="text-error">*</span></span>
                    <input class="form-input mt-1.5 w-full rounded-lg border border-slate-300 bg-transparent px-3 py-2 placeholder:text-slate-400/70 hover:border-slate-400 focus:border-primary dark:border-navy-450 dark:hover:border-navy-400 dark:focus:border-accent"
                           type="text" name="ticket_no" value="{{ request('ticket_no') }}" placeholder="e.g. TKT-000001" required>
                </label>

                {{-- Toggle: Email / Phone --}}
                <div>
                    <div class="mb-2 flex items-center justify-between">
                        <span class="text-sm font-medium text-slate-600 dark:text-navy-100">
                            Email or Phone <span class="text-error">*</span>
                        </span>
                        <div class="flex rounded-lg border border-slate-200 dark:border-navy-500 overflow-hidden text-xs">
                            <button type="button" @click="mode = 'email'"
                                    :class="mode === 'email' ? 'bg-primary text-white' : 'bg-white text-slate-500 hover:bg-slate-50 dark:bg-navy-700 dark:text-navy-300'"
                                    class="px-3 py-1 font-medium transition-colors">Email</button>
                            <button type="button" @click="mode = 'phone'"
                                    :class="mode === 'phone' ? 'bg-primary text-white' : 'bg-white text-slate-500 hover:bg-slate-50 dark:bg-navy-700 dark:text-navy-300'"
                                    class="px-3 py-1 font-medium transition-colors border-l border-slate-200 dark:border-navy-500">Phone</button>
                        </div>
                    </div>

                    {{-- Email input --}}
                    <div x-show="mode === 'email'">
                        <input type="hidden" name="email_or_phone" :value="mode === 'email' ? emailVal : full">
                        <input type="email" x-model="emailVal"
                               class="form-input w-full rounded-lg border border-slate-300 bg-transparent px-3 py-2 placeholder:text-slate-400/70 hover:border-slate-400 focus:border-primary dark:border-navy-450 dark:hover:border-navy-400 dark:focus:border-accent"
                               placeholder="john@example.com"
                               :required="mode === 'email'">
                    </div>

                    {{-- Phone input --}}
                    <div x-show="mode === 'phone'">
                        <div class="flex rounded-lg border border-slate-300 dark:border-navy-450 overflow-hidden hover:border-slate-400 focus-within:border-primary dark:hover:border-navy-400 dark:focus-within:border-accent">
                            <span class="flex items-center bg-slate-100 px-3 text-sm font-medium text-slate-600 dark:bg-navy-600 dark:text-navy-200 shrink-0">+255</span>
                            <input type="text" inputmode="numeric" x-model="digits" @input="onPhoneInput($event)"
                                   placeholder="748859172" maxlength="9"
                                   class="form-input w-full border-0 bg-transparent px-3 py-2 placeholder:text-slate-400/70 focus:ring-0 focus:outline-none"
                                   :required="mode === 'phone'">
                        </div>
                        <p x-show="digits.length > 0 && digits.length < 9" class="mt-1 text-xs text-error">Must be exactly 9 digits after +255</p>
                    </div>
                </div>

                <button type="submit" class="btn w-full space-x-2 rounded-lg bg-primary py-2.5 font-medium text-white shadow-lg shadow-primary/50 hover:bg-primary-focus dark:bg-accent dark:shadow-accent/50 dark:hover:bg-accent-focus">
                    <svg xmlns="http://www.w3.org/2000/svg" class="size-5" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/></svg>
                    <span>Look Up Ticket</span>
                </button>
            </form>

            @if($searched)
                <div class="mt-8">
                    @if($ticket)
                        <div class="rounded-lg border border-slate-200 bg-white p-5 dark:border-navy-500 dark:bg-navy-700">
                            <h3 class="text-sm font-semibold uppercase tracking-wider text-slate-400 dark:text-navy-300">Ticket Found</h3>
                            <div class="mt-4 space-y-3">
                                <div class="flex items-center justify-between">
                                    <span class="text-sm text-slate-500 dark:text-navy-300">Ticket No</span>
                                    <span class="font-mono font-bold text-slate-700 dark:text-navy-100">{{ $ticket->ticket_no }}</span>
                                </div>
                                <div class="h-px bg-slate-100 dark:bg-navy-600"></div>
                                <div class="flex items-center justify-between">
                                    <span class="text-sm text-slate-500 dark:text-navy-300">Subject</span>
                                    <span class="text-sm font-medium text-slate-700 dark:text-navy-100">{{ Str::limit($ticket->subject, 35) }}</span>
                                </div>
                                <div class="h-px bg-slate-100 dark:bg-navy-600"></div>
                                <div class="flex items-center justify-between">
                                    <span class="text-sm text-slate-500 dark:text-navy-300">Status</span>
                                    <span class="badge rounded-full px-3 py-1" style="background-color: {{ $ticket->status_color }}20; color: {{ $ticket->status_color }};">
                                        {{ $ticket->status_name }}
                                    </span>
                                </div>
                                @if($ticket->priority_name)
                                    <div class="h-px bg-slate-100 dark:bg-navy-600"></div>
                                    <div class="flex items-center justify-between">
                                        <span class="text-sm text-slate-500 dark:text-navy-300">Priority</span>
                                        <span class="badge rounded-full px-3 py-1" style="background-color: {{ $ticket->priority_color }}20; color: {{ $ticket->priority_color }};">
                                            {{ $ticket->priority_name }}
                                        </span>
                                    </div>
                                @endif
                                @if($ticket->category_name)
                                    <div class="h-px bg-slate-100 dark:bg-navy-600"></div>
                                    <div class="flex items-center justify-between">
                                        <span class="text-sm text-slate-500 dark:text-navy-300">Category</span>
                                        <span class="text-sm text-slate-700 dark:text-navy-100">{{ $ticket->category_name }}</span>
                                    </div>
                                @endif
                                @if($ticket->agent_name)
                                    <div class="h-px bg-slate-100 dark:bg-navy-600"></div>
                                    <div class="flex items-center justify-between">
                                        <span class="text-sm text-slate-500 dark:text-navy-300">Assigned To</span>
                                        <span class="text-sm text-slate-700 dark:text-navy-100">{{ $ticket->agent_name }}</span>
                                    </div>
                                @endif
                                <div class="h-px bg-slate-100 dark:bg-navy-600"></div>
                                <div class="flex items-center justify-between">
                                    <span class="text-sm text-slate-500 dark:text-navy-300">Submitted</span>
                                    <span class="text-sm text-slate-700 dark:text-navy-100">{{ \Carbon\Carbon::parse($ticket->created_at)->format('M d, Y h:i A') }}</span>
                                </div>
                                @if($ticket->resolved_at)
                                    <div class="h-px bg-slate-100 dark:bg-navy-600"></div>
                                    <div class="flex items-center justify-between">
                                        <span class="text-sm text-slate-500 dark:text-navy-300">Resolved</span>
                                        <span class="text-sm text-slate-700 dark:text-navy-100">{{ \Carbon\Carbon::parse($ticket->resolved_at)->format('M d, Y h:i A') }}</span>
                                    </div>
                                @endif
                                @if($ticket->closed_at)
                                    <div class="h-px bg-slate-100 dark:bg-navy-600"></div>
                                    <div class="flex items-center justify-between">
                                        <span class="text-sm text-slate-500 dark:text-navy-300">Closed</span>
                                        <span class="text-sm text-slate-700 dark:text-navy-100">{{ \Carbon\Carbon::parse($ticket->closed_at)->format('M d, Y h:i A') }}</span>
                                    </div>
                                @endif
                            </div>
                        </div>

                        @if($ticket->description)
                            <div class="mt-4 rounded-lg border border-slate-200 bg-white p-5 dark:border-navy-500 dark:bg-navy-700">
                                <h3 class="text-sm font-semibold uppercase tracking-wider text-slate-400 dark:text-navy-300">Description</h3>
                                <div class="mt-3 text-sm leading-relaxed text-slate-600 dark:text-navy-200">
                                    {!! nl2br(e($ticket->description)) !!}
                                </div>
                            </div>
                        @endif

                        @if($lastRemark)
                            <div class="mt-4 rounded-lg border border-primary/20 bg-primary/5 p-5 dark:border-accent/20 dark:bg-accent/5">
                                <h3 class="text-sm font-semibold uppercase tracking-wider text-primary dark:text-accent">Latest Reply from Support</h3>
                                <div class="mt-3 text-sm leading-relaxed text-slate-600 dark:text-navy-200">
                                    {!! nl2br(e($lastRemark->message)) !!}
                                </div>
                                <p class="mt-2 text-xs text-slate-400 dark:text-navy-300">
                                    {{ \Carbon\Carbon::parse($lastRemark->created_at)->format('M d, Y h:i A') }}
                                </p>
                            </div>
                        @endif
                    @else
                        <div class="rounded-lg border border-warning/30 bg-warning/10 p-5 text-center">
                            <svg xmlns="http://www.w3.org/2000/svg" class="mx-auto size-8 text-warning" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-2.5L13.732 4c-.77-.833-1.964-.833-2.732 0L4.082 16.5c-.77.833.192 2.5 1.732 2.5z"/></svg>
                            <p class="mt-2 font-medium text-slate-700 dark:text-navy-100">Ticket Not Found</p>
                            <p class="mt-1 text-sm text-slate-400 dark:text-navy-300">Please check your ticket number and the email or phone number used when the ticket was created, then try again.</p>
                        </div>
                    @endif
                </div>
            @endif
        </main>

        {{-- Footer --}}
        <footer class="mt-auto border-t border-slate-200 bg-white py-6 dark:border-navy-700 dark:bg-navy-800">
            <div class="mx-auto max-w-5xl px-4 text-center text-sm text-slate-400 dark:text-navy-300 sm:px-6">
                <p>&copy; {{ date('Y') }} {{ config('app.name') }}. All rights reserved.</p>
            </div>
        </footer>
    </div>
</x-base-layout>
