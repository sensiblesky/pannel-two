<x-base-layout title="Reset Password">
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
        x-data="{
            fingerprint: '',
            resending: false,
            resendMessage: '',
            resendError: '',
            resendRemaining: 5,
            resendBlocked: false,
            async resendCode() {
                this.resending = true;
                this.resendMessage = '';
                this.resendError = '';
                try {
                    const res = await fetch('{{ route('password.forgot.resend') }}', {
                        method: 'POST',
                        headers: {
                            'Content-Type': 'application/json',
                            'X-CSRF-TOKEN': '{{ csrf_token() }}',
                            'Accept': 'application/json',
                        },
                        body: JSON.stringify({
                            email: '{{ $email }}',
                            fingerprint: this.fingerprint,
                        }),
                    });
                    const data = await res.json();
                    if (res.ok) {
                        this.resendMessage = data.message;
                        if (data.remaining !== undefined) this.resendRemaining = data.remaining;
                    } else {
                        this.resendError = data.message;
                        if (data.blocked) this.resendBlocked = true;
                        if (data.remaining !== undefined) this.resendRemaining = data.remaining;
                    }
                } catch (e) {
                    this.resendError = 'Failed to resend code. Please try again.';
                } finally {
                    this.resending = false;
                }
            }
        }"
        x-init="fingerprint = [screen.width, screen.height, Intl.DateTimeFormat().resolvedOptions().timeZone, navigator.language, navigator.platform].join('|')">
        <div class="flex w-full max-w-sm grow flex-col justify-center p-5">
            <div class="text-center">
                <img class="mx-auto size-16 lg:hidden" src="{{ asset('images/app-logo.svg') }}" alt="logo" />
                <div class="mt-4">
                    <h2 class="text-2xl font-semibold text-slate-600 dark:text-navy-100">
                        Reset Password
                    </h2>
                    @php
                        [$name, $domain] = explode('@', $email);
                        $masked = Str::mask($name, '*', 3) . '@' . $domain;
                    @endphp
                    <p class="text-slate-400 dark:text-navy-300">
                        Enter the code sent to <strong class="text-slate-600 dark:text-navy-100">{{ $masked }}</strong>
                    </p>
                </div>
            </div>

            @if (session('success'))
                <div class="mt-5 flex items-center space-x-3 rounded-lg bg-success/10 px-4 py-3 text-success">
                    <svg xmlns="http://www.w3.org/2000/svg" class="size-5 shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M9 12.75L11.25 15 15 9.75M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                    </svg>
                    <p class="text-sm">{{ session('success') }}</p>
                </div>
            @endif

            @if (session('error'))
                <div class="mt-5 flex items-center space-x-3 rounded-lg bg-error/10 px-4 py-3 text-error">
                    <svg xmlns="http://www.w3.org/2000/svg" class="size-5 shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M12 9v3.75m9-.75a9 9 0 11-18 0 9 9 0 0118 0zm-9 3.75h.008v.008H12v-.008z" />
                    </svg>
                    <p class="text-sm">{{ session('error') }}</p>
                </div>
            @endif

            <form class="mt-10" action="{{ route('password.reset.submit') }}" method="POST">
                @csrf
                <input type="hidden" name="email" value="{{ $email }}">
                <input type="hidden" name="fingerprint" :value="fingerprint">

                {{-- Verification Code --}}
                <div>
                    <label class="block">
                        <span class="font-medium text-slate-600 dark:text-navy-100">Verification Code</span>
                        <input
                            class="form-input mt-1.5 w-full rounded-lg bg-slate-150 px-3 py-2 text-center text-lg tracking-[0.5em] ring-primary/50 placeholder:text-slate-400 placeholder:tracking-normal hover:bg-slate-200 focus:ring-3 dark:bg-navy-900/90 dark:ring-accent/50 dark:placeholder:text-navy-300 dark:hover:bg-navy-900 dark:focus:bg-navy-900"
                            type="text" name="code" placeholder="000000" maxlength="6"
                            inputmode="numeric" pattern="[0-9]*" autocomplete="one-time-code"
                            required autofocus />
                    </label>
                    @error('code')
                        <span class="text-tiny-plus text-error">{{ $message }}</span>
                    @enderror
                </div>

                {{-- New Password --}}
                <div class="mt-4">
                    <label class="block">
                        <span class="font-medium text-slate-600 dark:text-navy-100">New Password</span>
                        <label class="relative flex mt-1.5">
                            <input
                                class="form-input peer w-full rounded-lg bg-slate-150 px-3 py-2 pl-9 ring-primary/50 placeholder:text-slate-400 hover:bg-slate-200 focus:ring-3 dark:bg-navy-900/90 dark:ring-accent/50 dark:placeholder:text-navy-300 dark:hover:bg-navy-900 dark:focus:bg-navy-900"
                                placeholder="New password" type="password" name="password" required />
                            <span
                                class="pointer-events-none absolute flex h-full w-10 items-center justify-center text-slate-400 peer-focus:text-primary dark:text-navy-300 dark:peer-focus:text-accent">
                                <svg xmlns="http://www.w3.org/2000/svg" class="size-5 transition-colors duration-200"
                                    fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5"
                                        d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z" />
                                </svg>
                            </span>
                        </label>
                    </label>
                    @error('password')
                        <span class="text-tiny-plus text-error">{{ $message }}</span>
                    @enderror
                </div>

                {{-- Confirm Password --}}
                <div class="mt-4">
                    <label class="block">
                        <span class="font-medium text-slate-600 dark:text-navy-100">Confirm Password</span>
                        <label class="relative flex mt-1.5">
                            <input
                                class="form-input peer w-full rounded-lg bg-slate-150 px-3 py-2 pl-9 ring-primary/50 placeholder:text-slate-400 hover:bg-slate-200 focus:ring-3 dark:bg-navy-900/90 dark:ring-accent/50 dark:placeholder:text-navy-300 dark:hover:bg-navy-900 dark:focus:bg-navy-900"
                                placeholder="Confirm password" type="password" name="password_confirmation" required />
                            <span
                                class="pointer-events-none absolute flex h-full w-10 items-center justify-center text-slate-400 peer-focus:text-primary dark:text-navy-300 dark:peer-focus:text-accent">
                                <svg xmlns="http://www.w3.org/2000/svg" class="size-5 transition-colors duration-200"
                                    fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5"
                                        d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z" />
                                </svg>
                            </span>
                        </label>
                    </label>
                </div>

                <button type="submit"
                    class="btn mt-10 h-10 w-full bg-primary font-medium text-white hover:bg-primary-focus focus:bg-primary-focus active:bg-primary-focus/90 dark:bg-accent dark:hover:bg-accent-focus dark:focus:bg-accent-focus dark:active:bg-accent/90">
                    Reset Password
                </button>
            </form>

            {{-- Resend Code --}}
            <div class="mt-4 text-center" x-show="!resendBlocked">
                <p class="text-xs text-slate-400 dark:text-navy-300">
                    Didn't receive the code?
                    <button type="button"
                        class="text-primary transition-colors hover:text-primary-focus dark:text-accent-light dark:hover:text-accent disabled:opacity-50"
                        :disabled="resending || resendRemaining <= 0"
                        @click="resendCode()">
                        <span x-show="!resending">Resend</span>
                        <span x-show="resending" x-cloak>Sending...</span>
                    </button>
                    <span x-show="resendRemaining < 5" x-cloak
                        class="text-slate-400 dark:text-navy-300"
                        x-text="'(' + resendRemaining + ' remaining)'"></span>
                </p>
            </div>

            {{-- Resend feedback --}}
            <div x-show="resendMessage" x-cloak class="mt-3 text-center text-xs text-success" x-text="resendMessage"></div>
            <div x-show="resendError" x-cloak class="mt-3 text-center text-xs text-error" x-text="resendError"></div>

            {{-- Blocked state --}}
            <div x-show="resendBlocked" x-cloak class="mt-4 text-center">
                <p class="text-xs text-error">Resend limit reached. Please try again later.</p>
            </div>

            <div class="mt-4 text-center text-xs-plus">
                <p class="line-clamp-1">
                    <a class="text-primary transition-colors hover:text-primary-focus dark:text-accent-light dark:hover:text-accent"
                        href="{{ route('password.forgot') }}">Try a different email</a>
                    <span class="mx-2 text-slate-300 dark:text-navy-400">|</span>
                    <a class="text-primary transition-colors hover:text-primary-focus dark:text-accent-light dark:hover:text-accent"
                        href="{{ route('loginView') }}">Back to Sign In</a>
                </p>
            </div>
        </div>
    </main>
</x-base-layout>
