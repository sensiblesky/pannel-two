<x-base-layout title="429 - Too Many Requests">
    <main class="grid w-full grow grid-cols-1 place-items-center">
        <div class="max-w-md p-6 text-center">
            <div class="w-full">
                <img class="w-full" x-show="!$store.global.isDarkModeEnabled" src="{{ asset('images/illustrations/error-429.svg') }}" alt="429" />
                <img class="w-full" x-show="$store.global.isDarkModeEnabled" src="{{ asset('images/illustrations/error-429-dark.svg') }}" alt="429" />
            </div>
            <p class="pt-4 text-7xl font-bold text-primary dark:text-accent">429</p>
            <p class="pt-4 text-xl font-semibold text-slate-800 dark:text-navy-50">Too Many Requests</p>
            <p class="pt-2 text-slate-500 dark:text-navy-200">
                You've sent too many requests in a short period. Please slow down and try again later.
            </p>
        </div>
    </main>
</x-base-layout>
