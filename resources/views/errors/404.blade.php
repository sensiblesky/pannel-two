<x-base-layout title="404 - Page Not Found">
    <main
        :style="$store.global.isDarkModeEnabled ? { backgroundImage: `url('{{ asset('images/illustrations/ufo-bg-dark.svg') }}')` } :
        { backgroundImage: `url('{{ asset('images/illustrations/ufo-bg.svg') }}')` }"
        class="grid w-full grow grid-cols-1 place-items-center bg-center">
        <div class="max-w-[26rem] text-center">
            <div class="w-full">
                <img class="w-full" x-show="!$store.global.isDarkModeEnabled" src="{{ asset('images/illustrations/ufo.svg') }}" alt="404" />
                <img class="w-full" x-show="$store.global.isDarkModeEnabled" src="{{ asset('images/illustrations/ufo-dark.svg') }}" alt="404" />
            </div>
            <p class="pt-4 text-7xl font-bold text-primary dark:text-accent">404</p>
            <p class="pt-4 text-xl font-semibold text-slate-800 dark:text-navy-50">Page Not Found</p>
            <p class="pt-2 text-slate-500 dark:text-navy-200">
                The page you are looking for doesn't exist or has been moved.
            </p>
            <a href="{{ url('/') }}"
                class="btn mt-8 h-11 bg-primary text-base font-medium text-white hover:bg-primary-focus hover:shadow-lg hover:shadow-primary/50 focus:bg-primary-focus focus:shadow-lg focus:shadow-primary/50 active:bg-primary-focus/90 dark:bg-accent dark:hover:bg-accent-focus dark:hover:shadow-accent/50 dark:focus:bg-accent-focus dark:focus:shadow-accent/50 dark:active:bg-accent/90">
                Back To Home
            </a>
        </div>
    </main>
</x-base-layout>
