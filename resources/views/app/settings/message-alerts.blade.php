<x-app-layout title="Message Alerts" is-sidebar-open="true" is-header-blur="true">
    @slot('script')
    <script>
        document.addEventListener('DOMContentLoaded', function () {
            @if (session('success'))
                Swal.fire({
                    title: 'Success!',
                    text: @json(session('success')),
                    icon: 'success',
                    confirmButtonColor: '#4f46e5',
                    confirmButtonText: 'OK',
                    timer: 3000,
                    timerProgressBar: true,
                    customClass: { popup: 'rounded-lg' }
                });
            @endif
            @if (session('error'))
                Swal.fire({
                    title: 'Error',
                    text: @json(session('error')),
                    icon: 'error',
                    confirmButtonColor: '#4f46e5',
                    confirmButtonText: 'OK',
                    customClass: { popup: 'rounded-lg' }
                });
            @endif
        });
    </script>
    @endslot

    <main class="main-content w-full px-[var(--margin-x)] pb-8">
        {{-- Page Header --}}
        <div class="flex items-center space-x-4 py-5 lg:py-6">
            <h2 class="text-xl font-medium text-slate-800 dark:text-navy-50 lg:text-2xl">Message Alerts</h2>
            <div class="hidden h-full py-1 sm:flex"><div class="h-full w-px bg-slate-300 dark:bg-navy-600"></div></div>
            <ul class="hidden flex-wrap items-center space-x-2 sm:flex">
                <li class="flex items-center space-x-2">
                    <a class="text-primary transition-colors hover:text-primary-focus dark:text-accent-light dark:hover:text-accent" href="{{ route('config/message-alerts') }}">Configuration</a>
                    <svg xmlns="http://www.w3.org/2000/svg" class="size-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/></svg>
                </li>
                <li>Message Alerts</li>
            </ul>
        </div>

        <div class="grid grid-cols-1 gap-4 sm:gap-5 lg:grid-cols-3 lg:gap-6">
            {{-- Upload Form --}}
            <div class="lg:col-span-1">
                <div class="card">
                    <div class="border-b border-slate-200 p-4 dark:border-navy-500 sm:px-5">
                        <h2 class="text-lg font-medium tracking-wide text-slate-700 dark:text-navy-100">Upload New Alert</h2>
                    </div>
                    <form method="POST" action="{{ route('config/message-alerts-store') }}" enctype="multipart/form-data" class="space-y-4 p-4 sm:p-5">
                        @csrf

                        <label class="block">
                            <span class="font-medium text-slate-600 dark:text-navy-100">Name <span class="text-error">*</span></span>
                            <input class="form-input mt-1.5 w-full rounded-lg border border-slate-300 bg-transparent px-3 py-2 placeholder:text-slate-400/70 hover:border-slate-400 focus:border-primary dark:border-navy-450 dark:hover:border-navy-400 dark:focus:border-accent @error('name') border-error @enderror"
                                   type="text" name="name" value="{{ old('name') }}" placeholder="e.g. Notification Ding" required>
                            @error('name') <span class="text-tiny-plus text-error">{{ $message }}</span> @enderror
                        </label>

                        <label class="block">
                            <span class="font-medium text-slate-600 dark:text-navy-100">Alert Type <span class="text-error">*</span></span>
                            <select name="type" class="form-select mt-1.5 w-full rounded-lg border border-slate-300 bg-white px-3 py-2 hover:border-slate-400 focus:border-primary dark:border-navy-450 dark:bg-navy-700 dark:hover:border-navy-400 dark:focus:border-accent @error('type') border-error @enderror" required>
                                @foreach($types as $value => $label)
                                    <option value="{{ $value }}" @selected(old('type') === $value)>{{ $label }}</option>
                                @endforeach
                            </select>
                            @error('type') <span class="text-tiny-plus text-error">{{ $message }}</span> @enderror
                        </label>

                        <label class="block">
                            <span class="font-medium text-slate-600 dark:text-navy-100">Audio File <span class="text-error">*</span></span>
                            <input class="form-input mt-1.5 w-full rounded-lg border border-slate-300 bg-transparent px-3 py-2 file:mr-4 file:rounded-full file:border-0 file:bg-primary/10 file:px-4 file:py-1 file:text-sm file:font-medium file:text-primary hover:border-slate-400 focus:border-primary dark:border-navy-450 dark:hover:border-navy-400 dark:focus:border-accent @error('audio_file') border-error @enderror"
                                   type="file" name="audio_file" accept=".mp3,.wav,.ogg,.aac,.m4a" required>
                            <span class="mt-1 text-xs text-slate-400 dark:text-navy-300">Accepted: MP3, WAV, OGG, AAC, M4A. Max 5MB.</span>
                            @error('audio_file') <span class="text-tiny-plus text-error">{{ $message }}</span> @enderror
                        </label>

                        <button type="submit" class="btn w-full space-x-2 bg-primary font-medium text-white shadow-lg shadow-primary/50 hover:bg-primary-focus focus:bg-primary-focus active:bg-primary-focus/90 dark:bg-accent dark:shadow-accent/50 dark:hover:bg-accent-focus dark:focus:bg-accent-focus dark:active:bg-accent/90">
                            <svg xmlns="http://www.w3.org/2000/svg" class="size-5" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-8l-4-4m0 0L8 8m4-4v12"/></svg>
                            <span>Upload Alert Sound</span>
                        </button>
                    </form>
                </div>

                {{-- Current Defaults Summary --}}
                @php
                    $defaultTicket = $ticketAlerts->firstWhere('is_default', true);
                    $defaultMessage = $messageAlerts->firstWhere('is_default', true);
                @endphp
                <div class="card mt-4 sm:mt-5 lg:mt-6">
                    <div class="border-b border-slate-200 p-4 dark:border-navy-500 sm:px-5">
                        <h2 class="text-lg font-medium tracking-wide text-slate-700 dark:text-navy-100">Current Defaults</h2>
                    </div>
                    <div class="space-y-4 p-4 sm:p-5">
                        {{-- Ticket Default --}}
                        <div>
                            <p class="text-xs font-semibold uppercase tracking-wider text-slate-400 dark:text-navy-300">Incoming Ticket</p>
                            @if($defaultTicket)
                                <div class="mt-2 flex items-center space-x-3">
                                    <div class="flex size-9 shrink-0 items-center justify-center rounded-lg bg-warning/10 text-warning">
                                        <svg xmlns="http://www.w3.org/2000/svg" class="size-4.5" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 5v2m0 4v2m0 4v2M5 5a2 2 0 00-2 2v3a2 2 0 110 4v3a2 2 0 002 2h14a2 2 0 002-2v-3a2 2 0 110-4V7a2 2 0 00-2-2H5z"/></svg>
                                    </div>
                                    <div class="min-w-0">
                                        <p class="truncate font-medium text-slate-700 dark:text-navy-100">{{ $defaultTicket->name }}</p>
                                        <p class="text-xs text-slate-400 dark:text-navy-300">{{ $defaultTicket->file_name }}</p>
                                    </div>
                                </div>
                                <audio controls class="mt-2 w-full" preload="none">
                                    <source src="{{ \Illuminate\Support\Facades\Storage::disk('public')->url($defaultTicket->file_path) }}" type="{{ $defaultTicket->mime_type }}">
                                </audio>
                            @else
                                <p class="mt-2 text-sm text-slate-400 dark:text-navy-300 italic">No default set</p>
                            @endif
                        </div>

                        <div class="h-px bg-slate-200 dark:bg-navy-500"></div>

                        {{-- Message/Chat Default --}}
                        <div>
                            <p class="text-xs font-semibold uppercase tracking-wider text-slate-400 dark:text-navy-300">Incoming Message / Live Chat</p>
                            @if($defaultMessage)
                                <div class="mt-2 flex items-center space-x-3">
                                    <div class="flex size-9 shrink-0 items-center justify-center rounded-lg bg-info/10 text-info">
                                        <svg xmlns="http://www.w3.org/2000/svg" class="size-4.5" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 12h.01M12 12h.01M16 12h.01M21 12c0 4.418-4.03 8-9 8a9.863 9.863 0 01-4.255-.949L3 20l1.395-3.72C3.512 15.042 3 13.574 3 12c0-4.418 4.03-8 9-8s9 3.582 9 8z"/></svg>
                                    </div>
                                    <div class="min-w-0">
                                        <p class="truncate font-medium text-slate-700 dark:text-navy-100">{{ $defaultMessage->name }}</p>
                                        <p class="text-xs text-slate-400 dark:text-navy-300">{{ $defaultMessage->file_name }}</p>
                                    </div>
                                </div>
                                <audio controls class="mt-2 w-full" preload="none">
                                    <source src="{{ \Illuminate\Support\Facades\Storage::disk('public')->url($defaultMessage->file_path) }}" type="{{ $defaultMessage->mime_type }}">
                                </audio>
                            @else
                                <p class="mt-2 text-sm text-slate-400 dark:text-navy-300 italic">No default set</p>
                            @endif
                        </div>
                    </div>
                </div>
            </div>

            {{-- Alerts Lists --}}
            <div class="lg:col-span-2 space-y-4 sm:space-y-5 lg:space-y-6">

                {{-- Ticket Alerts --}}
                <div class="card">
                    <div class="border-b border-slate-200 p-4 dark:border-navy-500 sm:px-5">
                        <div class="flex items-center justify-between">
                            <div class="flex items-center space-x-3">
                                <div class="flex size-8 items-center justify-center rounded-lg bg-warning/10 text-warning">
                                    <svg xmlns="http://www.w3.org/2000/svg" class="size-4" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 5v2m0 4v2m0 4v2M5 5a2 2 0 00-2 2v3a2 2 0 110 4v3a2 2 0 002 2h14a2 2 0 002-2v-3a2 2 0 110-4V7a2 2 0 00-2-2H5z"/></svg>
                                </div>
                                <h2 class="text-lg font-medium tracking-wide text-slate-700 dark:text-navy-100">Incoming Ticket Alerts</h2>
                            </div>
                            <span class="badge rounded-full bg-warning/10 text-warning">{{ $ticketAlerts->count() }}</span>
                        </div>
                    </div>

                    @if($ticketAlerts->isEmpty())
                        <div class="p-6 text-center">
                            <p class="text-slate-400 dark:text-navy-300">No ticket alert sounds uploaded yet.</p>
                        </div>
                    @else
                        @include('app.settings._alert-table', ['alertList' => $ticketAlerts])
                    @endif
                </div>

                {{-- Message/Chat Alerts --}}
                <div class="card">
                    <div class="border-b border-slate-200 p-4 dark:border-navy-500 sm:px-5">
                        <div class="flex items-center justify-between">
                            <div class="flex items-center space-x-3">
                                <div class="flex size-8 items-center justify-center rounded-lg bg-info/10 text-info">
                                    <svg xmlns="http://www.w3.org/2000/svg" class="size-4" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 12h.01M12 12h.01M16 12h.01M21 12c0 4.418-4.03 8-9 8a9.863 9.863 0 01-4.255-.949L3 20l1.395-3.72C3.512 15.042 3 13.574 3 12c0-4.418 4.03-8 9-8s9 3.582 9 8z"/></svg>
                                </div>
                                <h2 class="text-lg font-medium tracking-wide text-slate-700 dark:text-navy-100">Incoming Message / Live Chat Alerts</h2>
                            </div>
                            <span class="badge rounded-full bg-info/10 text-info">{{ $messageAlerts->count() }}</span>
                        </div>
                    </div>

                    @if($messageAlerts->isEmpty())
                        <div class="p-6 text-center">
                            <p class="text-slate-400 dark:text-navy-300">No message/chat alert sounds uploaded yet.</p>
                        </div>
                    @else
                        @include('app.settings._alert-table', ['alertList' => $messageAlerts])
                    @endif
                </div>

            </div>
        </div>
    </main>
</x-app-layout>
