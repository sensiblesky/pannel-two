<x-base-layout title="Two-Factor Authentication">
    <div class="fixed top-0 hidden p-6 lg:block lg:px-12">
        <a href="#" class="flex items-center space-x-2">
            <img class="size-12" src="{{ asset('images/app-logo.svg') }}" alt="logo" />
            <p class="text-xl font-semibold uppercase text-slate-700 dark:text-navy-100">
                {{ config('app.name') }}
            </p>
        </a>
    </div>
    <div class="hidden w-full place-items-center lg:grid">
        <div class="w-full max-w-lg p-6">
            <img class="w-full" x-show="!$store.global.isDarkModeEnabled"
                src="{{ asset('images/illustrations/dashboard-check.svg') }}" alt="image" />
            <img class="w-full" x-show="$store.global.isDarkModeEnabled"
                src="{{ asset('images/illustrations/dashboard-check-dark.svg') }}" alt="image" />
        </div>
    </div>
    <main class="flex w-full flex-col items-center bg-white dark:bg-navy-700 lg:max-w-md"
        x-data="{ useRecovery: false, resending: false, resent: false, resendError: '', resendRemaining: 3, resendLocked: false }">
        <div class="flex w-full max-w-sm grow flex-col justify-center p-5">
            <div class="text-center">
                <img class="mx-auto size-16 lg:hidden" src="{{ asset('images/app-logo.svg') }}" alt="logo" />
                <div class="mt-4">
                    <h2 class="text-2xl font-semibold text-slate-600 dark:text-navy-100">
                        Two-Factor Authentication
                    </h2>
                    <p class="mt-1 text-slate-400 dark:text-navy-300" x-show="!useRecovery">
                        @if ($method === 'email')
                            @php
                                [$name, $domain] = explode('@', $email);
                                $masked = Str::mask($name, '*', 3) . '@' . $domain;
                            @endphp
                            We sent a verification code to <strong class="text-slate-600 dark:text-navy-100">{{ $masked }}</strong>
                        @else
                            Enter the 6-digit code from your authenticator app.
                        @endif
                    </p>
                    <p class="mt-1 text-slate-400 dark:text-navy-300" x-show="useRecovery" x-cloak>
                        Enter one of your recovery codes.
                    </p>
                </div>
            </div>

            @if (session('error'))
                <div class="mt-5 flex items-center space-x-3 rounded-lg bg-error/10 px-4 py-3 text-error">
                    <svg xmlns="http://www.w3.org/2000/svg" class="size-5 shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M12 9v3.75m9-.75a9 9 0 11-18 0 9 9 0 0118 0zm-9 3.75h.008v.008H12v-.008z" />
                    </svg>
                    <p class="text-sm">{{ session('error') }}</p>
                </div>
            @endif

            <form class="mt-10" action="{{ route('two-factor.verify') }}" method="POST">
                @csrf
                {{-- Normal code input --}}
                <div x-show="!useRecovery">
                    <label class="block">
                        <span class="font-medium text-slate-600 dark:text-navy-100">
                            {{ $method === 'email' ? 'Verification Code' : 'Authenticator Code' }}
                        </span>
                        <input
                            class="form-input mt-1.5 w-full rounded-lg bg-slate-150 px-3 py-2 text-center font-mono text-lg tracking-[0.5em] ring-primary/50 placeholder:text-slate-400 hover:bg-slate-200 focus:ring-3 dark:bg-navy-900/90 dark:ring-accent/50 dark:placeholder:text-navy-300 dark:hover:bg-navy-900 dark:focus:bg-navy-900 @error('code') ring-error/50 @enderror"
                            type="text" :name="useRecovery ? '' : 'code'" maxlength="6" placeholder="000000"
                            autocomplete="one-time-code" inputmode="numeric" autofocus />
                    </label>
                </div>

                {{-- Recovery code input --}}
                <div x-show="useRecovery" x-cloak>
                    <label class="block">
                        <span class="font-medium text-slate-600 dark:text-navy-100">Recovery Code</span>
                        <input
                            class="form-input mt-1.5 w-full rounded-lg bg-slate-150 px-3 py-2 text-center font-mono text-lg tracking-widest ring-primary/50 placeholder:text-slate-400 hover:bg-slate-200 focus:ring-3 dark:bg-navy-900/90 dark:ring-accent/50 dark:placeholder:text-navy-300 dark:hover:bg-navy-900 dark:focus:bg-navy-900 @error('code') ring-error/50 @enderror"
                            type="text" :name="useRecovery ? 'code' : ''" maxlength="10" placeholder="xxxxxxxxxx"
                            autocomplete="off" />
                    </label>
                </div>

                @error('code')
                    <span class="mt-1 text-tiny-plus text-error">{{ $message }}</span>
                @enderror

                <button type="submit"
                    class="btn mt-8 h-10 w-full bg-primary font-medium text-white hover:bg-primary-focus focus:bg-primary-focus active:bg-primary-focus/90 dark:bg-accent dark:hover:bg-accent-focus dark:focus:bg-accent-focus dark:active:bg-accent/90">
                    Verify
                </button>
            </form>

            <div class="mt-4 flex items-center justify-between">
                <button type="button"
                    @click="useRecovery = !useRecovery"
                    class="text-xs text-primary transition-colors hover:text-primary-focus dark:text-accent-light dark:hover:text-accent">
                    <span x-show="!useRecovery">Use a recovery code</span>
                    <span x-show="useRecovery">Use authenticator code</span>
                </button>

                @if ($method === 'email')
                    <button type="button"
                        x-show="!useRecovery && !resendLocked"
                        :disabled="resending || resent"
                        @click="
                            resending = true;
                            resendError = '';
                            fetch('{{ route('two-factor.resend') }}', {
                                method: 'POST',
                                headers: {
                                    'Content-Type': 'application/json',
                                    'X-CSRF-TOKEN': '{{ csrf_token() }}',
                                    'Accept': 'application/json',
                                },
                            }).then(async res => {
                                const data = await res.json();
                                resending = false;
                                if (res.ok) {
                                    resent = true;
                                    resendRemaining = data.remaining ?? resendRemaining;
                                    setTimeout(() => resent = false, 60000);
                                } else {
                                    if (data.locked) {
                                        resendLocked = true;
                                    }
                                    resendError = data.message;
                                    resendRemaining = data.remaining ?? resendRemaining;
                                }
                            }).catch(() => { resending = false; resendError = 'Network error. Please try again.'; });
                        "
                        class="text-xs text-slate-400 transition-colors hover:text-slate-800 dark:text-navy-300 dark:hover:text-navy-100">
                        <span x-show="!resending && !resent" x-text="'Resend code' + (resendRemaining < 3 ? ' (' + resendRemaining + ' left)' : '')"></span>
                        <span x-show="resending" x-cloak>Sending...</span>
                        <span x-show="resent" x-cloak class="text-success">Code sent!</span>
                    </button>
                    <span x-show="resendLocked && !useRecovery" x-cloak class="text-xs text-error">Resend locked for 1 hour</span>
                @endif
            </div>

            <div x-show="resendError" x-cloak class="mt-3 flex items-center space-x-2 rounded-lg bg-error/10 px-3 py-2 text-error">
                <svg xmlns="http://www.w3.org/2000/svg" class="size-4 shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M12 9v3.75m9-.75a9 9 0 11-18 0 9 9 0 0118 0zm-9 3.75h.008v.008H12v-.008z" />
                </svg>
                <p class="text-xs" x-text="resendError"></p>
            </div>

            <div class="mt-6 text-center">
                <a href="{{ route('loginView') }}"
                    class="text-xs text-slate-400 transition-colors hover:text-slate-800 dark:text-navy-300 dark:hover:text-navy-100">
                    &larr; Back to login
                </a>
            </div>
        </div>
    </main>
</x-base-layout>
