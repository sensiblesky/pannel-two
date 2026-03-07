<x-base-layout title="Forgot Password">
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
    <main class="flex w-full flex-col items-center bg-white dark:bg-navy-700 lg:max-w-md">
        <div class="flex w-full max-w-sm grow flex-col justify-center p-5">
            <div class="text-center">
                <img class="mx-auto size-16 lg:hidden" src="{{ asset('images/app-logo.svg') }}" alt="logo" />
                <div class="mt-4">
                    <h2 class="text-2xl font-semibold text-slate-600 dark:text-navy-100">
                        Forgot Password
                    </h2>
                    <p class="text-slate-400 dark:text-navy-300">
                        Enter your email to receive a reset code
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

            <form class="mt-16" action="{{ route('password.forgot.send') }}" method="POST"
                x-data="{ fingerprint: '' }"
                x-init="fingerprint = [screen.width, screen.height, Intl.DateTimeFormat().resolvedOptions().timeZone, navigator.language, navigator.platform].join('|')">
                @csrf
                <input type="hidden" name="fingerprint" :value="fingerprint">

                <div>
                    <label class="relative flex">
                        <input
                            class="form-input peer w-full rounded-lg bg-slate-150 px-3 py-2 pl-9 ring-primary/50 placeholder:text-slate-400 hover:bg-slate-200 focus:ring-3 dark:bg-navy-900/90 dark:ring-accent/50 dark:placeholder:text-navy-300 dark:hover:bg-navy-900 dark:focus:bg-navy-900"
                            placeholder="Email address" type="email" name="email"
                            value="{{ old('email') }}" required autofocus />
                        <span
                            class="pointer-events-none absolute flex h-full w-10 items-center justify-center text-slate-400 peer-focus:text-primary dark:text-navy-300 dark:peer-focus:text-accent">
                            <svg xmlns="http://www.w3.org/2000/svg" class="size-5 transition-colors duration-200"
                                fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5"
                                    d="M3 8l7.89 5.26a2 2 0 002.22 0L21 8M5 19h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z" />
                            </svg>
                        </span>
                    </label>
                    @error('email')
                        <span class="text-tiny-plus text-error">{{ $message }}</span>
                    @enderror
                </div>

                <button type="submit"
                    class="btn mt-10 h-10 w-full bg-primary font-medium text-white hover:bg-primary-focus focus:bg-primary-focus active:bg-primary-focus/90 dark:bg-accent dark:hover:bg-accent-focus dark:focus:bg-accent-focus dark:active:bg-accent/90">
                    Send Reset Code
                </button>

                <div class="mt-4 text-center text-xs-plus">
                    <p class="line-clamp-1">
                        <span>Remember your password?</span>
                        <a class="text-primary transition-colors hover:text-primary-focus dark:text-accent-light dark:hover:text-accent"
                            href="{{ route('loginView') }}">Sign In</a>
                    </p>
                </div>
            </form>
        </div>
    </main>
</x-base-layout>
