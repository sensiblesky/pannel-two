<x-base-layout title="503 - Under Maintenance">
    <main class="grid w-full grow grid-cols-1 place-items-center">
        <div class="max-w-md p-6 text-center">
            <div class="w-full">
                <img class="w-full" x-show="!$store.global.isDarkModeEnabled" src="{{ asset('images/illustrations/error-429.svg') }}" alt="maintenance" />
                <img class="w-full" x-show="$store.global.isDarkModeEnabled" src="{{ asset('images/illustrations/error-429-dark.svg') }}" alt="maintenance" />
            </div>
            <p class="pt-4 text-7xl font-bold text-primary dark:text-accent">503</p>
            <p class="pt-4 text-xl font-semibold text-slate-800 dark:text-navy-50">Under Maintenance</p>
            <p class="pt-2 text-slate-500 dark:text-navy-200">
                We're currently performing scheduled maintenance. We'll be back online shortly. Thank you for your patience.
            </p>

            @if (!empty($maintenanceEndAt))
                <div class="mt-8" x-data="{
                    endAt: new Date('{{ \Carbon\Carbon::parse($maintenanceEndAt)->toIso8601String() }}'),
                    days: 0, hours: 0, minutes: 0, seconds: 0,
                    expired: false,
                    init() {
                        this.tick();
                        setInterval(() => this.tick(), 1000);
                    },
                    tick() {
                        const diff = this.endAt - new Date();
                        if (diff <= 0) {
                            this.expired = true;
                            location.reload();
                            return;
                        }
                        this.days = Math.floor(diff / 86400000);
                        this.hours = Math.floor((diff % 86400000) / 3600000);
                        this.minutes = Math.floor((diff % 3600000) / 60000);
                        this.seconds = Math.floor((diff % 60000) / 1000);
                    }
                }">
                    <p class="text-sm font-medium text-slate-500 dark:text-navy-200 mb-3">Estimated return</p>
                    <div class="flex items-center justify-center space-x-3" x-show="!expired">
                        <template x-if="days > 0">
                            <div class="flex flex-col items-center rounded-lg bg-slate-100 px-4 py-3 dark:bg-navy-600">
                                <span class="text-2xl font-bold text-primary dark:text-accent" x-text="days"></span>
                                <span class="text-xs text-slate-400 dark:text-navy-300">Days</span>
                            </div>
                        </template>
                        <div class="flex flex-col items-center rounded-lg bg-slate-100 px-4 py-3 dark:bg-navy-600">
                            <span class="text-2xl font-bold text-primary dark:text-accent" x-text="String(hours).padStart(2, '0')"></span>
                            <span class="text-xs text-slate-400 dark:text-navy-300">Hours</span>
                        </div>
                        <div class="text-xl font-bold text-slate-300 dark:text-navy-400">:</div>
                        <div class="flex flex-col items-center rounded-lg bg-slate-100 px-4 py-3 dark:bg-navy-600">
                            <span class="text-2xl font-bold text-primary dark:text-accent" x-text="String(minutes).padStart(2, '0')"></span>
                            <span class="text-xs text-slate-400 dark:text-navy-300">Minutes</span>
                        </div>
                        <div class="text-xl font-bold text-slate-300 dark:text-navy-400">:</div>
                        <div class="flex flex-col items-center rounded-lg bg-slate-100 px-4 py-3 dark:bg-navy-600">
                            <span class="text-2xl font-bold text-primary dark:text-accent" x-text="String(seconds).padStart(2, '0')"></span>
                            <span class="text-xs text-slate-400 dark:text-navy-300">Seconds</span>
                        </div>
                    </div>
                    <p x-show="expired" x-cloak class="text-sm text-success font-medium">
                        Maintenance should be complete. Refreshing...
                    </p>
                </div>
            @endif

            <div class="mt-6 flex items-center justify-center space-x-3">
                <button onclick="location.reload()" class="btn space-x-2 rounded-full bg-primary font-medium text-white hover:bg-primary-focus focus:bg-primary-focus active:bg-primary-focus/90 dark:bg-accent dark:hover:bg-accent-focus dark:focus:bg-accent-focus dark:active:bg-accent/90">
                    <svg xmlns="http://www.w3.org/2000/svg" class="size-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15" />
                    </svg>
                    <span>Refresh</span>
                </button>

                @auth
                    <form method="POST" action="{{ route('logout') }}">
                        @csrf
                        <button type="submit" class="btn space-x-2 rounded-full border border-slate-300 font-medium text-slate-800 hover:bg-slate-150 focus:bg-slate-150 active:bg-slate-150/80 dark:border-navy-450 dark:text-navy-50 dark:hover:bg-navy-500 dark:focus:bg-navy-500 dark:active:bg-navy-500/90">
                            <svg xmlns="http://www.w3.org/2000/svg" class="size-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M17 16l4-4m0 0l-4-4m4 4H7m6 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h4a3 3 0 013 3v1" />
                            </svg>
                            <span>Logout</span>
                        </button>
                    </form>
                @endauth
            </div>
        </div>
    </main>
</x-base-layout>
