<!doctype html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">

<head>
    <meta charset="UTF-8">
    <link rel="icon" type="image/png" href="{{ asset('favicon.png') }}" />
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <meta name="viewport" content="width=device-width, user-scalable=no, initial-scale=1.0, maximum-scale=1.0, minimum-scale=1.0">
    <meta http-equiv="X-UA-Compatible" content="ie=edge">
    <title>{{ config('app.name') }} @isset($title) - {{ $title }} @endisset</title>

    @vite(['resources/css/app.css', 'resources/js/app.js'])

    <link rel="preconnect" href="https://fonts.googleapis.com" />
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin />
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&family=Poppins:ital,wght@0,300;0,400;0,500;0,600;0,700;1,300;1,400;1,500;1,600;1,700&display=swap" rel="stylesheet" />

    <script>
        localStorage.getItem("_x_darkMode_on") === "true" && document.documentElement.classList.add("dark");
    </script>

    @isset($head) {{ $head }} @endisset
</head>

<body x-data="{ darkMode: $store.global.isDarkModeEnabled, mobileNav: false }" class="is-header-blur">

    <!-- Page Wrapper -->
    <div id="root" class="min-h-screen bg-slate-50 dark:bg-navy-900" x-cloak>

        <!-- Top Navigation -->
        <nav class="sticky top-0 z-20 border-b border-slate-150 bg-white/80 backdrop-blur dark:border-navy-700 dark:bg-navy-800/80">
            <div class="mx-auto flex h-16 max-w-5xl items-center justify-between px-4 sm:px-6">
                <!-- Logo -->
                <a href="{{ route('customer.dashboard') }}" class="flex items-center space-x-2">
                    <img class="size-8" src="{{ asset('images/app-logo.svg') }}" alt="logo" />
                    <span class="text-lg font-semibold text-slate-700 dark:text-navy-100">{{ config('app.name') }}</span>
                </a>

                <!-- Desktop Nav -->
                <div class="hidden items-center space-x-1 sm:flex">
                    <a href="{{ route('customer.dashboard') }}" class="rounded-lg px-3 py-2 text-sm font-medium {{ request()->routeIs('customer.dashboard') ? 'bg-primary/10 text-primary dark:bg-accent/10 dark:text-accent-light' : 'text-slate-600 hover:bg-slate-100 dark:text-navy-200 dark:hover:bg-navy-600' }}">
                        Dashboard
                    </a>
                    <a href="{{ route('customer.tickets') }}" class="rounded-lg px-3 py-2 text-sm font-medium {{ request()->routeIs('customer.tickets*') ? 'bg-primary/10 text-primary dark:bg-accent/10 dark:text-accent-light' : 'text-slate-600 hover:bg-slate-100 dark:text-navy-200 dark:hover:bg-navy-600' }}">
                        My Tickets
                    </a>
                    <a href="{{ route('customer.profile') }}" class="rounded-lg px-3 py-2 text-sm font-medium {{ request()->routeIs('customer.profile*') ? 'bg-primary/10 text-primary dark:bg-accent/10 dark:text-accent-light' : 'text-slate-600 hover:bg-slate-100 dark:text-navy-200 dark:hover:bg-navy-600' }}">
                        Profile
                    </a>
                </div>

                <!-- Right Side -->
                <div class="flex items-center space-x-3">
                    <!-- Dark Mode Toggle -->
                    <button @click="$store.global.isDarkModeEnabled = !$store.global.isDarkModeEnabled" class="rounded-full p-2 text-slate-400 hover:bg-slate-100 hover:text-slate-600 dark:text-navy-300 dark:hover:bg-navy-600 dark:hover:text-navy-100">
                        <svg x-show="!$store.global.isDarkModeEnabled" xmlns="http://www.w3.org/2000/svg" class="size-5" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20.354 15.354A9 9 0 018.646 3.646 9.003 9.003 0 0012 21a9.003 9.003 0 008.354-5.646z"/></svg>
                        <svg x-show="$store.global.isDarkModeEnabled" xmlns="http://www.w3.org/2000/svg" class="size-5" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 3v1m0 16v1m9-9h-1M4 12H3m15.364 6.364l-.707-.707M6.343 6.343l-.707-.707m12.728 0l-.707.707M6.343 17.657l-.707.707M16 12a4 4 0 11-8 0 4 4 0 018 0z"/></svg>
                    </button>

                    <!-- User dropdown -->
                    <div x-data="{ open: false }" class="relative">
                        <button @click="open = !open" class="flex items-center space-x-2 rounded-lg px-2 py-1.5 hover:bg-slate-100 dark:hover:bg-navy-600">
                            <div class="flex size-8 items-center justify-center rounded-full bg-primary/10 text-sm font-medium text-primary dark:bg-accent/10 dark:text-accent-light">
                                {{ substr(auth()->user()->name, 0, 1) }}
                            </div>
                            <span class="hidden text-sm font-medium text-slate-700 dark:text-navy-100 sm:block">{{ auth()->user()->name }}</span>
                            <svg xmlns="http://www.w3.org/2000/svg" class="size-4 text-slate-400" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"/></svg>
                        </button>
                        <div x-show="open" @click.away="open = false" x-transition class="absolute right-0 mt-2 w-48 rounded-lg border border-slate-150 bg-white py-1 shadow-lg dark:border-navy-600 dark:bg-navy-700">
                            <a href="{{ route('customer.profile') }}" class="block px-4 py-2 text-sm text-slate-600 hover:bg-slate-100 dark:text-navy-200 dark:hover:bg-navy-600">Profile</a>
                            <form method="POST" action="{{ route('logout') }}">
                                @csrf
                                <button type="submit" class="block w-full px-4 py-2 text-left text-sm text-slate-600 hover:bg-slate-100 dark:text-navy-200 dark:hover:bg-navy-600">Sign Out</button>
                            </form>
                        </div>
                    </div>

                    <!-- Mobile menu button -->
                    <button @click="mobileNav = !mobileNav" class="rounded-lg p-2 text-slate-400 hover:bg-slate-100 sm:hidden dark:hover:bg-navy-600">
                        <svg xmlns="http://www.w3.org/2000/svg" class="size-5" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16M4 18h16"/></svg>
                    </button>
                </div>
            </div>

            <!-- Mobile Nav -->
            <div x-show="mobileNav" x-transition class="border-t border-slate-150 px-4 py-3 sm:hidden dark:border-navy-700">
                <a href="{{ route('customer.dashboard') }}" class="block rounded-lg px-3 py-2 text-sm font-medium {{ request()->routeIs('customer.dashboard') ? 'bg-primary/10 text-primary' : 'text-slate-600 hover:bg-slate-100 dark:text-navy-200' }}">Dashboard</a>
                <a href="{{ route('customer.tickets') }}" class="block rounded-lg px-3 py-2 text-sm font-medium {{ request()->routeIs('customer.tickets*') ? 'bg-primary/10 text-primary' : 'text-slate-600 hover:bg-slate-100 dark:text-navy-200' }}">My Tickets</a>
                <a href="{{ route('customer.profile') }}" class="block rounded-lg px-3 py-2 text-sm font-medium {{ request()->routeIs('customer.profile*') ? 'bg-primary/10 text-primary' : 'text-slate-600 hover:bg-slate-100 dark:text-navy-200' }}">Profile</a>
            </div>
        </nav>

        <!-- Main Content -->
        <main class="mx-auto max-w-5xl px-4 py-6 sm:px-6">
            @if (session('success'))
                <div class="mb-4 flex items-center space-x-3 rounded-lg bg-success/10 px-4 py-3 text-success">
                    <svg xmlns="http://www.w3.org/2000/svg" class="size-5 shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M9 12.75L11.25 15 15 9.75M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                    <p class="text-sm">{{ session('success') }}</p>
                </div>
            @endif

            @if (session('error'))
                <div class="mb-4 flex items-center space-x-3 rounded-lg bg-error/10 px-4 py-3 text-error">
                    <svg xmlns="http://www.w3.org/2000/svg" class="size-5 shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M12 9v3.75m9-.75a9 9 0 11-18 0 9 9 0 0118 0zm-9 3.75h.008v.008H12v-.008z"/></svg>
                    <p class="text-sm">{{ session('error') }}</p>
                </div>
            @endif

            {{ $slot }}
        </main>

        <!-- Footer -->
        <footer class="border-t border-slate-150 dark:border-navy-700">
            <div class="mx-auto max-w-5xl px-4 py-4 sm:px-6">
                <p class="text-center text-xs text-slate-400 dark:text-navy-300">
                    &copy; {{ date('Y') }} {{ config('app.name') }}. All rights reserved.
                </p>
            </div>
        </footer>
    </div>

    <div id="x-teleport-target"></div>
    <script>
        window.addEventListener("DOMContentLoaded", () => Alpine.start());
    </script>
    @isset($script) {{ $script }} @endisset
</body>

</html>
