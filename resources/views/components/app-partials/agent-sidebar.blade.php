<div class="main-sidebar">
    <div
        class="flex h-full w-full flex-col items-center border-r border-slate-150 bg-white dark:border-navy-700 dark:bg-navy-800">
        <!-- Application Logo -->
        <div class="flex pt-4">
            <a href="{{ route('agent.tickets/dashboard') }}">
                <img class="size-11 transition-transform duration-500 ease-in-out hover:rotate-[360deg]"
                    src="{{ asset('images/app-logo.svg') }}" alt="logo" />
            </a>
        </div>

        <!-- Main Sections Links -->
        @php
            $activeClass = 'text-primary hover:bg-primary/20 focus:bg-primary/20 active:bg-primary/25 dark:bg-navy-600 bg-primary/10 dark:text-accent-light dark:hover:bg-navy-450 dark:focus:bg-navy-450 dark:active:bg-navy-450/90';
            $inactiveClass = 'hover:bg-primary/20 focus:bg-primary/20 active:bg-primary/25 dark:hover:bg-navy-300/20 dark:focus:bg-navy-300/20 dark:active:bg-navy-300/25';
        @endphp
        <div class="is-scrollbar-hidden flex grow flex-col space-y-4 overflow-y-auto pt-6">
            <!-- Tickets -->
            <a href="{{ route('agent.tickets/dashboard') }}"
                class="flex size-11 items-center justify-center rounded-lg outline-hidden transition-colors duration-200 {{ in_array(request()->route()?->getName(), ['agent.tickets/dashboard', 'agent.tickets/index', 'agent.tickets/create', 'agent.tickets/show', 'agent.tickets/poll', 'agent.tickets/reports']) ? $activeClass : $inactiveClass }}"
                x-tooltip.placement.right="'Tickets'">
                <svg class="size-7" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg">
                    <path fill="currentColor" fill-opacity="0.3"
                        d="M4 6a2 2 0 0 1 2-2h12a2 2 0 0 1 2 2v3.17a3.001 3.001 0 0 0 0 5.66V18a2 2 0 0 1-2 2H6a2 2 0 0 1-2-2v-3.17a3.001 3.001 0 0 0 0-5.66V6Z" />
                    <path fill="currentColor"
                        d="M4 9.17V6a2 2 0 0 1 2-2h12a2 2 0 0 1 2 2v3.17a3.001 3.001 0 0 0-2.83 2.83H6.83A3.001 3.001 0 0 0 4 9.17Z" />
                    <circle cx="9" cy="8" r="1" fill="currentColor" />
                    <circle cx="12" cy="8" r="1" fill="currentColor" />
                    <circle cx="15" cy="8" r="1" fill="currentColor" />
                </svg>
            </a>

            <!-- Tags -->
            <a href="{{ route('agent.tickets/settings-tags') }}"
                class="flex size-11 items-center justify-center rounded-lg outline-hidden transition-colors duration-200 {{ request()->routeIs('agent.tickets/settings-tags*') ? $activeClass : $inactiveClass }}"
                x-tooltip.placement.right="'Tags'">
                <svg class="size-7" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg">
                    <path fill="currentColor" fill-opacity="0.3"
                        d="M20.59 13.41l-7.17 7.17a2 2 0 0 1-2.83 0L2 12V2h10l8.59 8.59a2 2 0 0 1 0 2.82Z" />
                    <circle cx="7" cy="7" r="1.5" fill="currentColor" />
                </svg>
            </a>

            <!-- Canned Responses -->
            <a href="{{ route('agent.tickets/settings-canned-responses') }}"
                class="flex size-11 items-center justify-center rounded-lg outline-hidden transition-colors duration-200 {{ request()->routeIs('agent.tickets/settings-canned-responses*') ? $activeClass : $inactiveClass }}"
                x-tooltip.placement.right="'Canned Responses'">
                <svg class="size-7" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg">
                    <path fill="currentColor" fill-opacity="0.3"
                        d="M21 15a2 2 0 0 1-2 2H7l-4 4V5a2 2 0 0 1 2-2h14a2 2 0 0 1 2 2v10Z" />
                    <path fill="currentColor"
                        d="M8 9h8M8 13h5" stroke="currentColor" stroke-width="1.5" stroke-linecap="round"/>
                </svg>
            </a>
        </div>

        <!-- Bottom Links -->
        <div class="flex flex-col items-center space-y-3 py-3">
            <!-- Profile Dropdown -->
            <div x-data="usePopper({ placement: 'right-end', offset: 12 })" @click.outside="if(isShowPopper) isShowPopper = false" class="flex">
                <button @click="isShowPopper = !isShowPopper" x-ref="popperRef" class="avatar size-12 cursor-pointer">
                    @if (auth()->user()?->avatar)
                        <img class="rounded-full" src="{{ asset('storage/' . auth()->user()->avatar) }}" alt="avatar" />
                    @else
                        <div class="is-initial rounded-full bg-primary text-white dark:bg-accent text-sm font-medium">
                            {{ strtoupper(substr(auth()->user()?->name ?? 'A', 0, 2)) }}
                        </div>
                    @endif
                    <span class="absolute right-0 size-3.5 rounded-full border-2 border-white bg-success dark:border-navy-700"></span>
                </button>
                <div :class="isShowPopper && 'show'" class="popper-root fixed" x-ref="popperRoot">
                    <div
                        class="popper-box w-64 rounded-lg border border-slate-150 bg-white shadow-soft dark:border-navy-600 dark:bg-navy-700">
                        <div class="flex items-center space-x-4 rounded-t-lg bg-slate-100 py-5 px-4 dark:bg-navy-800">
                            <div class="avatar size-14">
                                @if (auth()->user()?->avatar)
                                    <img class="rounded-full" src="{{ asset('storage/' . auth()->user()->avatar) }}" alt="avatar" />
                                @else
                                    <div class="is-initial rounded-full bg-primary text-white dark:bg-accent text-lg font-medium">
                                        {{ strtoupper(substr(auth()->user()?->name ?? 'A', 0, 2)) }}
                                    </div>
                                @endif
                            </div>
                            <div>
                                <a href="{{ route('agent.profile') }}"
                                    class="text-base font-medium text-slate-700 hover:text-primary focus:text-primary dark:text-navy-100 dark:hover:text-accent-light dark:focus:text-accent-light">
                                    {{ auth()->user()?->name }}
                                </a>
                                <p class="text-xs text-slate-400 dark:text-navy-300">
                                    {{ ucfirst(auth()->user()?->role ?? 'Agent') }}
                                </p>
                            </div>
                        </div>
                        <div class="flex flex-col pt-2 pb-5">
                            <a href="{{ route('agent.profile') }}"
                                class="group flex items-center space-x-3 py-2 px-4 tracking-wide outline-hidden transition-all hover:bg-slate-100 focus:bg-slate-100 dark:hover:bg-navy-600 dark:focus:bg-navy-600">
                                <div class="flex size-8 items-center justify-center rounded-lg bg-warning text-white">
                                    <svg xmlns="http://www.w3.org/2000/svg" class="size-4.5" fill="none"
                                        viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                        <path stroke-linecap="round" stroke-linejoin="round"
                                            d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z" />
                                    </svg>
                                </div>
                                <div>
                                    <h2
                                        class="font-medium text-slate-700 transition-colors group-hover:text-primary group-focus:text-primary dark:text-navy-100 dark:group-hover:text-accent-light dark:group-focus:text-accent-light">
                                        Profile
                                    </h2>
                                    <div class="text-xs text-slate-400 line-clamp-1 dark:text-navy-300">
                                        Your profile settings
                                    </div>
                                </div>
                            </a>
                            <div class="mt-3 px-4">
                                <form method="POST" action="{{ route('logout') }}">
                                    @csrf
                                    <button type="submit"
                                        class="btn h-9 w-full space-x-2 bg-primary text-white hover:bg-primary-focus focus:bg-primary-focus active:bg-primary-focus/90 dark:bg-accent dark:hover:bg-accent-focus dark:focus:bg-accent-focus dark:active:bg-accent/90">
                                        <svg xmlns="http://www.w3.org/2000/svg" class="size-5" fill="none"
                                            viewBox="0 0 24 24" stroke="currentColor">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5"
                                                d="M17 16l4-4m0 0l-4-4m4 4H7m6 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h4a3 3 0 013 3v1" />
                                        </svg>
                                        <span>Logout</span>
                                    </button>
                                </form>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
