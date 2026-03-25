<x-app-layout title="My Profile" is-header-blur="true">
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
        });
    </script>
    @endslot
    <main class="main-content w-full px-[var(--margin-x)] pb-8">
        <div class="flex items-center space-x-4 py-5 lg:py-6">
            <h2 class="text-xl font-medium text-slate-800 dark:text-navy-50 lg:text-2xl">My Profile</h2>
            <div class="hidden h-full py-1 sm:flex">
                <div class="h-full w-px bg-slate-300 dark:bg-navy-600"></div>
            </div>
            <ul class="hidden flex-wrap items-center space-x-2 sm:flex">
                <li class="flex items-center space-x-2">
                    <a class="text-primary transition-colors hover:text-primary-focus dark:text-accent-light dark:hover:text-accent" href="{{ route('agent.profile') }}">Profile</a>
                    <svg x-ignore xmlns="http://www.w3.org/2000/svg" class="size-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7" />
                    </svg>
                </li>
                <li>{{ $tab === 'security' ? 'Security' : ($tab === 'sessions' ? 'Sessions' : 'Account') }}</li>
            </ul>
        </div>

        <div class="grid grid-cols-12 gap-4 sm:gap-5 lg:gap-6" x-data="{ activeTab: '{{ $tab }}' }">
            {{-- Sidebar --}}
            <div class="col-span-12 lg:col-span-4">
                <div class="card p-4 sm:p-5">
                    <div class="flex items-center space-x-4">
                        <div class="avatar size-14">
                            @if ($user->avatar)
                                <img class="rounded-full" src="{{ asset('storage/' . $user->avatar) }}" alt="avatar" />
                            @else
                                <div class="is-initial rounded-full bg-primary text-white dark:bg-accent">
                                    {{ strtoupper(substr($user->name, 0, 2)) }}
                                </div>
                            @endif
                        </div>
                        <div>
                            <h3 class="text-base font-medium text-slate-700 dark:text-navy-100">{{ $user->name }}</h3>
                            <p class="text-xs-plus text-slate-400">{{ $user->role ?? 'User' }}</p>
                        </div>
                    </div>
                    <ul class="mt-6 space-y-1.5 font-inter font-medium">
                        <li>
                            <a @click.prevent="activeTab = 'account'" href="{{ route('agent.profile') }}"
                                :class="activeTab === 'account'
                                    ? 'flex items-center space-x-2 rounded-lg bg-primary px-4 py-2.5 tracking-wide text-white outline-hidden transition-all dark:bg-accent'
                                    : 'group flex space-x-2 rounded-lg px-4 py-2.5 tracking-wide outline-hidden transition-all hover:bg-slate-100 hover:text-slate-800 focus:bg-slate-100 focus:text-slate-800 dark:hover:bg-navy-600 dark:hover:text-navy-100 dark:focus:bg-navy-600 dark:focus:text-navy-100'">
                                <svg xmlns="http://www.w3.org/2000/svg" class="size-5" :class="activeTab === 'account' ? '' : 'text-slate-400 transition-colors group-hover:text-slate-500 group-focus:text-slate-500 dark:text-navy-300 dark:group-hover:text-navy-200 dark:group-focus:text-navy-200'" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.5">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M5.121 17.804A13.937 13.937 0 0112 16c2.5 0 4.847.655 6.879 1.804M15 10a3 3 0 11-6 0 3 3 0 016 0zm6 2a9 9 0 11-18 0 9 9 0 0118 0z" />
                                </svg>
                                <span>Account</span>
                            </a>
                        </li>
                        <li>
                            <a @click.prevent="activeTab = 'security'" href="{{ route('agent.profile', ['tab' => 'security']) }}"
                                :class="activeTab === 'security'
                                    ? 'flex items-center space-x-2 rounded-lg bg-primary px-4 py-2.5 tracking-wide text-white outline-hidden transition-all dark:bg-accent'
                                    : 'group flex space-x-2 rounded-lg px-4 py-2.5 tracking-wide outline-hidden transition-all hover:bg-slate-100 hover:text-slate-800 focus:bg-slate-100 focus:text-slate-800 dark:hover:bg-navy-600 dark:hover:text-navy-100 dark:focus:bg-navy-600 dark:focus:text-navy-100'">
                                <svg xmlns="http://www.w3.org/2000/svg" class="size-5" :class="activeTab === 'security' ? '' : 'text-slate-400 transition-colors group-hover:text-slate-500 group-focus:text-slate-500 dark:text-navy-300 dark:group-hover:text-navy-200 dark:group-focus:text-navy-200'" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.5">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M9 12l2 2 4-4m5.618-4.016A11.955 11.955 0 0112 2.944a11.955 11.955 0 01-8.618 3.04A12.02 12.02 0 003 9c0 5.591 3.824 10.29 9 11.622 5.176-1.332 9-6.03 9-11.622 0-1.042-.133-2.052-.382-3.016z" />
                                </svg>
                                <span>Security</span>
                            </a>
                        </li>
                        <li>
                            <a @click.prevent="activeTab = 'sessions'" href="{{ route('agent.profile', ['tab' => 'sessions']) }}"
                                :class="activeTab === 'sessions'
                                    ? 'flex items-center space-x-2 rounded-lg bg-primary px-4 py-2.5 tracking-wide text-white outline-hidden transition-all dark:bg-accent'
                                    : 'group flex space-x-2 rounded-lg px-4 py-2.5 tracking-wide outline-hidden transition-all hover:bg-slate-100 hover:text-slate-800 focus:bg-slate-100 focus:text-slate-800 dark:hover:bg-navy-600 dark:hover:text-navy-100 dark:focus:bg-navy-600 dark:focus:text-navy-100'">
                                <svg xmlns="http://www.w3.org/2000/svg" class="size-5" :class="activeTab === 'sessions' ? '' : 'text-slate-400 transition-colors group-hover:text-slate-500 group-focus:text-slate-500 dark:text-navy-300 dark:group-hover:text-navy-200 dark:group-focus:text-navy-200'" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.5">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M9 17.25v1.007a3 3 0 01-.879 2.122L7.5 21h9l-.621-.621A3 3 0 0115 18.257V17.25m6-12V15a2.25 2.25 0 01-2.25 2.25H5.25A2.25 2.25 0 013 15V5.25m18 0A2.25 2.25 0 0018.75 3H5.25A2.25 2.25 0 003 5.25m18 0V12a2.25 2.25 0 01-2.25 2.25H5.25A2.25 2.25 0 013 12V5.25" />
                                </svg>
                                <span>Sessions</span>
                            </a>
                        </li>
                    </ul>
                </div>
            </div>

            {{-- Account Tab --}}
            <div class="col-span-12 lg:col-span-8" x-show="activeTab === 'account'" x-cloak>
                <form method="POST" action="{{ route('agent.profile/update-account') }}" enctype="multipart/form-data">
                    @csrf
                    @method('PUT')
                    <input type="hidden" name="tab" value="account" />
                    <div class="card">
                        <div class="flex flex-col items-center space-y-4 border-b border-slate-200 p-4 dark:border-navy-500 sm:flex-row sm:justify-between sm:space-y-0 sm:px-5">
                            <h2 class="text-lg font-medium tracking-wide text-slate-700 dark:text-navy-100">Account Setting</h2>
                            <div class="flex justify-center space-x-2">
                                <a href="{{ route('agent.profile') }}"
                                    class="btn min-w-[7rem] rounded-full border border-slate-300 font-medium text-slate-700 hover:bg-slate-150 focus:bg-slate-150 active:bg-slate-150/80 dark:border-navy-450 dark:text-navy-100 dark:hover:bg-navy-500 dark:focus:bg-navy-500 dark:active:bg-navy-500/90">
                                    Cancel
                                </a>
                                <button type="submit"
                                    class="btn min-w-[7rem] rounded-full bg-primary font-medium text-white hover:bg-primary-focus focus:bg-primary-focus active:bg-primary-focus/90 dark:bg-accent dark:hover:bg-accent-focus dark:focus:bg-accent-focus dark:active:bg-accent/90">
                                    Save
                                </button>
                            </div>
                        </div>
                        <div class="p-4 sm:p-5">
                            {{-- Avatar --}}
                            <div class="flex flex-col" x-data="{ avatarPreview: '{{ $user->avatar ? asset('storage/' . $user->avatar) : '' }}' }">
                                <span class="text-base font-medium text-slate-600 dark:text-navy-100">Avatar</span>
                                <div class="avatar mt-1.5 size-20">
                                    <template x-if="avatarPreview">
                                        <img class="mask is-squircle" :src="avatarPreview" alt="avatar" />
                                    </template>
                                    <template x-if="!avatarPreview">
                                        <div class="is-initial mask is-squircle bg-primary text-white text-xl dark:bg-accent">
                                            {{ strtoupper(substr($user->name, 0, 2)) }}
                                        </div>
                                    </template>
                                    <div class="absolute bottom-0 right-0 flex items-center justify-center rounded-full bg-white dark:bg-navy-700">
                                        <label class="btn size-6 rounded-full border border-slate-200 p-0 hover:bg-slate-300/20 focus:bg-slate-300/20 active:bg-slate-300/25 dark:border-navy-500 dark:hover:bg-navy-300/20 dark:focus:bg-navy-300/20 dark:active:bg-navy-300/25 cursor-pointer">
                                            <svg xmlns="http://www.w3.org/2000/svg" class="size-3.5" viewBox="0 0 20 20" fill="currentColor">
                                                <path d="M13.586 3.586a2 2 0 112.828 2.828l-.793.793-2.828-2.828.793-.793zM11.379 5.793L3 14.172V17h2.828l8.38-8.379-2.83-2.828z" />
                                            </svg>
                                            <input type="file" name="avatar" class="hidden" accept="image/*"
                                                @change="const file = $event.target.files[0]; if(file) avatarPreview = URL.createObjectURL(file)" />
                                        </label>
                                    </div>
                                </div>
                                @error('avatar')<span class="mt-1 text-tiny-plus text-error">{{ $message }}</span>@enderror
                            </div>

                            <div class="my-7 h-px bg-slate-200 dark:bg-navy-500"></div>

                            {{-- Form Fields --}}
                            <div class="grid grid-cols-1 gap-4 sm:grid-cols-2">
                                <label class="block">
                                    <span class="font-medium text-slate-600 dark:text-navy-100">Full Name</span>
                                    <span class="relative mt-1.5 flex">
                                        <input class="form-input peer w-full rounded-full border border-slate-300 bg-transparent px-3 py-2 pl-9 placeholder:text-slate-400/70 hover:border-slate-400 focus:border-primary dark:border-navy-450 dark:hover:border-navy-400 dark:focus:border-accent @error('name') border-error @enderror"
                                            placeholder="Enter your name" type="text" name="name" value="{{ old('name', $user->name) }}" />
                                        <span class="pointer-events-none absolute flex h-full w-10 items-center justify-center text-slate-400 peer-focus:text-primary dark:text-navy-300 dark:peer-focus:text-accent">
                                            <svg xmlns="http://www.w3.org/2000/svg" class="size-4.5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.5">
                                                <path stroke-linecap="round" stroke-linejoin="round" d="M15.75 6a3.75 3.75 0 11-7.5 0 3.75 3.75 0 017.5 0zM4.501 20.118a7.5 7.5 0 0114.998 0" />
                                            </svg>
                                        </span>
                                    </span>
                                    @error('name')<span class="text-tiny-plus text-error">{{ $message }}</span>@enderror
                                </label>
                                <div class="block">
                                    <div class="flex items-center justify-between">
                                        <span class="font-medium text-slate-600 dark:text-navy-100">Email Address</span>
                                        @if ($user->email_verified_at)
                                            <span class="inline-flex items-center space-x-1 text-xs font-medium text-success">
                                                <svg xmlns="http://www.w3.org/2000/svg" class="size-3.5" viewBox="0 0 20 20" fill="currentColor">
                                                    <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.857-9.809a.75.75 0 00-1.214-.882l-3.483 4.79-1.88-1.88a.75.75 0 10-1.06 1.061l2.5 2.5a.75.75 0 001.137-.089l4-5.5z" clip-rule="evenodd" />
                                                </svg>
                                                <span>Verified</span>
                                            </span>
                                        @else
                                            <span class="inline-flex items-center space-x-1 text-xs font-medium text-warning">
                                                <svg xmlns="http://www.w3.org/2000/svg" class="size-3.5" viewBox="0 0 20 20" fill="currentColor">
                                                    <path fill-rule="evenodd" d="M8.485 2.495c.673-1.167 2.357-1.167 3.03 0l6.28 10.875c.673 1.167-.17 2.625-1.516 2.625H3.72c-1.347 0-2.189-1.458-1.515-2.625L8.485 2.495zM10 5a.75.75 0 01.75.75v3.5a.75.75 0 01-1.5 0v-3.5A.75.75 0 0110 5zm0 9a1 1 0 100-2 1 1 0 000 2z" clip-rule="evenodd" />
                                                </svg>
                                                <span>Not verified</span>
                                            </span>
                                        @endif
                                    </div>
                                    <span class="relative mt-1.5 flex">
                                        <input class="form-input peer w-full rounded-full border border-slate-300 bg-transparent px-3 py-2 pl-9 placeholder:text-slate-400/70 hover:border-slate-400 focus:border-primary dark:border-navy-450 dark:hover:border-navy-400 dark:focus:border-accent @error('email') border-error @enderror"
                                            placeholder="Enter email address" type="email" name="email" value="{{ old('email', $user->email) }}" />
                                        <span class="pointer-events-none absolute flex h-full w-10 items-center justify-center text-slate-400 peer-focus:text-primary dark:text-navy-300 dark:peer-focus:text-accent">
                                            <svg xmlns="http://www.w3.org/2000/svg" class="size-4.5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.5">
                                                <path stroke-linecap="round" stroke-linejoin="round" d="M21.75 6.75v10.5a2.25 2.25 0 01-2.25 2.25h-15a2.25 2.25 0 01-2.25-2.25V6.75m19.5 0A2.25 2.25 0 0019.5 4.5h-15a2.25 2.25 0 00-2.25 2.25m19.5 0v.243a2.25 2.25 0 01-1.07 1.916l-7.5 4.615a2.25 2.25 0 01-2.36 0L3.32 8.91a2.25 2.25 0 01-1.07-1.916V6.75" />
                                            </svg>
                                        </span>
                                    </span>
                                    @error('email')<span class="text-tiny-plus text-error">{{ $message }}</span>@enderror
                                    @unless ($user->email_verified_at)
                                        <div class="mt-2 flex items-center space-x-2" x-data="{
                                            showVerifyModal: false,
                                            sending: false,
                                            sent: false,
                                            async sendCode() {
                                                if (this.sending) return;
                                                this.sending = true;
                                                try {
                                                    const res = await fetch('{{ route('agent.profile/send-verification') }}', {
                                                        method: 'POST',
                                                        headers: {
                                                            'X-CSRF-TOKEN': '{{ csrf_token() }}',
                                                            'Accept': 'application/json',
                                                            'Content-Type': 'application/json',
                                                        },
                                                    });
                                                    const data = await res.json();
                                                    if (res.ok) {
                                                        this.sent = true;
                                                        window.Swal.fire({ icon: 'success', title: 'Sent', text: data.message, timer: 3000, showConfirmButton: false });
                                                    } else {
                                                        window.Swal.fire({ icon: 'error', title: 'Error', text: data.message });
                                                    }
                                                } catch (e) {
                                                    window.Swal.fire({ icon: 'error', title: 'Error', text: 'Something went wrong. Please try again.' });
                                                } finally {
                                                    this.sending = false;
                                                }
                                            }
                                        }">
                                            <button type="button" @click="sendCode()" :disabled="sending || sent"
                                                class="text-xs font-medium text-primary hover:text-primary-focus dark:text-accent-light dark:hover:text-accent disabled:opacity-50 disabled:cursor-not-allowed">
                                                <span x-show="!sending && !sent">Send verification code</span>
                                                <span x-show="sending" x-cloak>Sending...</span>
                                                <span x-show="sent" x-cloak>Code sent</span>
                                            </button>
                                            <span class="text-xs text-slate-300 dark:text-navy-400">|</span>
                                            <button type="button" @click="showVerifyModal = true" class="text-xs font-medium text-primary hover:text-primary-focus dark:text-accent-light dark:hover:text-accent">
                                                Enter code
                                            </button>

                                            {{-- Verify Email OTP Modal --}}
                                            <template x-teleport="#x-teleport-target">
                                                <div class="fixed inset-0 z-100 flex flex-col items-center justify-center overflow-hidden px-4 py-6 sm:px-5"
                                                    x-show="showVerifyModal" role="dialog" @keydown.window.escape="showVerifyModal = false">
                                                    <div class="absolute inset-0 bg-slate-900/60 transition-opacity duration-300"
                                                        @click="showVerifyModal = false" x-show="showVerifyModal"
                                                        x-transition:enter="ease-out" x-transition:enter-start="opacity-0" x-transition:enter-end="opacity-100"
                                                        x-transition:leave="ease-in" x-transition:leave-start="opacity-100" x-transition:leave-end="opacity-0"></div>
                                                    <div class="relative w-full max-w-md rounded-lg bg-white transition-opacity duration-300 dark:bg-navy-700 overflow-y-auto"
                                                        x-show="showVerifyModal"
                                                        x-transition:enter="ease-out" x-transition:enter-start="opacity-0" x-transition:enter-end="opacity-100"
                                                        x-transition:leave="ease-in" x-transition:leave-start="opacity-100" x-transition:leave-end="opacity-0">

                                                        <div class="flex items-center justify-between border-b border-slate-200 px-4 py-3 dark:border-navy-500 sm:px-5">
                                                            <h3 class="text-lg font-medium text-slate-700 dark:text-navy-100">Verify Email</h3>
                                                            <button @click="showVerifyModal = false" class="btn size-7 rounded-full p-0 hover:bg-slate-300/20 focus:bg-slate-300/20 active:bg-slate-300/25 dark:hover:bg-navy-300/20 dark:focus:bg-navy-300/20 dark:active:bg-navy-300/25">
                                                                <svg xmlns="http://www.w3.org/2000/svg" class="size-4.5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                                                    <path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12" />
                                                                </svg>
                                                            </button>
                                                        </div>

                                                        <form method="POST" action="{{ route('agent.profile/verify-email') }}" class="px-4 py-4 sm:px-5">
                                                            @csrf
                                                            <div class="text-center">
                                                                <div class="mx-auto flex size-16 items-center justify-center rounded-full bg-primary/10 dark:bg-accent/15">
                                                                    <svg xmlns="http://www.w3.org/2000/svg" class="size-8 text-primary dark:text-accent-light" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.5">
                                                                        <path stroke-linecap="round" stroke-linejoin="round" d="M21.75 6.75v10.5a2.25 2.25 0 01-2.25 2.25h-15a2.25 2.25 0 01-2.25-2.25V6.75m19.5 0A2.25 2.25 0 0019.5 4.5h-15a2.25 2.25 0 00-2.25 2.25m19.5 0v.243a2.25 2.25 0 01-1.07 1.916l-7.5 4.615a2.25 2.25 0 01-2.36 0L3.32 8.91a2.25 2.25 0 01-1.07-1.916V6.75" />
                                                                    </svg>
                                                                </div>
                                                                <p class="mt-3 text-sm text-slate-400 dark:text-navy-300">
                                                                    Enter the 6-digit code sent to <strong class="text-slate-600 dark:text-navy-100">{{ $user->email }}</strong>
                                                                </p>
                                                            </div>
                                                            <label class="mt-4 block">
                                                                <input class="form-input w-full rounded-full border border-slate-300 bg-transparent px-3 py-2 text-center font-mono text-lg tracking-[0.5em] placeholder:text-slate-400/70 hover:border-slate-400 focus:border-primary dark:border-navy-450 dark:hover:border-navy-400 dark:focus:border-accent @error('code') border-error @enderror"
                                                                    type="text" name="code" maxlength="6" placeholder="000000" autocomplete="one-time-code" inputmode="numeric" required />
                                                                @error('code')<span class="mt-1 block text-center text-tiny-plus text-error">{{ $message }}</span>@enderror
                                                            </label>
                                                            <div class="mt-4 flex space-x-2">
                                                                <button type="submit"
                                                                    class="btn flex-1 rounded-full bg-primary font-medium text-white hover:bg-primary-focus focus:bg-primary-focus active:bg-primary-focus/90 dark:bg-accent dark:hover:bg-accent-focus dark:focus:bg-accent-focus dark:active:bg-accent/90">
                                                                    Verify Email
                                                                </button>
                                                                <button type="button" @click="showVerifyModal = false"
                                                                    class="btn rounded-full border border-slate-300 font-medium text-slate-700 hover:bg-slate-150 dark:border-navy-450 dark:text-navy-100 dark:hover:bg-navy-500">
                                                                    Cancel
                                                                </button>
                                                            </div>
                                                        </form>
                                                    </div>
                                                </div>
                                            </template>
                                        </div>
                                    @endunless
                                </div>
                                <label class="block">
                                    <span class="font-medium text-slate-600 dark:text-navy-100">Phone Number</span>
                                    <span class="relative mt-1.5 flex">
                                        <input class="form-input peer w-full rounded-full border border-slate-300 bg-transparent px-3 py-2 pl-9 placeholder:text-slate-400/70 hover:border-slate-400 focus:border-primary dark:border-navy-450 dark:hover:border-navy-400 dark:focus:border-accent @error('phone') border-error @enderror"
                                            placeholder="Enter phone number" type="text" name="phone" value="{{ old('phone', $user->phone) }}" />
                                        <span class="pointer-events-none absolute flex h-full w-10 items-center justify-center text-slate-400 peer-focus:text-primary dark:text-navy-300 dark:peer-focus:text-accent">
                                            <svg xmlns="http://www.w3.org/2000/svg" class="size-4.5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.5">
                                                <path stroke-linecap="round" stroke-linejoin="round" d="M2.25 6.75c0 8.284 6.716 15 15 15h2.25a2.25 2.25 0 002.25-2.25v-1.372c0-.516-.351-.966-.852-1.091l-4.423-1.106c-.44-.11-.902.055-1.173.417l-.97 1.293c-.282.376-.769.542-1.21.38a12.035 12.035 0 01-7.143-7.143c-.162-.441.004-.928.38-1.21l1.293-.97c.363-.271.527-.734.417-1.173L6.963 3.102a1.125 1.125 0 00-1.091-.852H4.5A2.25 2.25 0 002.25 4.5v2.25z" />
                                            </svg>
                                        </span>
                                    </span>
                                    @error('phone')<span class="text-tiny-plus text-error">{{ $message }}</span>@enderror
                                </label>
                                <label class="block">
                                    <span class="font-medium text-slate-600 dark:text-navy-100">Role</span>
                                    <span class="relative mt-1.5 flex">
                                        <input class="form-input peer w-full rounded-full border border-slate-300 bg-transparent px-3 py-2 pl-9 placeholder:text-slate-400/70 dark:border-navy-450 cursor-not-allowed opacity-60"
                                            type="text" value="{{ $user->role ?? 'User' }}" disabled />
                                        <span class="pointer-events-none absolute flex h-full w-10 items-center justify-center text-slate-400 dark:text-navy-300">
                                            <svg xmlns="http://www.w3.org/2000/svg" class="size-4.5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.5">
                                                <path stroke-linecap="round" stroke-linejoin="round" d="M9 12.75L11.25 15 15 9.75m-3-7.036A11.959 11.959 0 013.598 6 11.99 11.99 0 003 9.749c0 5.592 3.824 10.29 9 11.623 5.176-1.332 9-6.03 9-11.622 0-1.31-.21-2.571-.598-3.751h-.152c-3.196 0-6.1-1.248-8.25-3.285z" />
                                            </svg>
                                        </span>
                                    </span>
                                </label>
                            </div>

                            <div class="my-7 h-px bg-slate-200 dark:bg-navy-500"></div>

                            {{-- Account Info --}}
                            <div>
                                <h3 class="text-base font-medium text-slate-600 dark:text-navy-100">Account Information</h3>
                                <p class="text-xs-plus text-slate-400 dark:text-navy-300">Read-only details about your account.</p>
                                <div class="mt-4 grid grid-cols-1 gap-3 sm:grid-cols-2">
                                    <div class="rounded-lg bg-slate-100 px-4 py-3 dark:bg-navy-600">
                                        <p class="text-xs text-slate-400 dark:text-navy-300">Branch</p>
                                        <p class="font-medium text-slate-700 dark:text-navy-100">{{ $user->branch?->name ?? 'Not assigned' }}</p>
                                    </div>
                                    <div class="rounded-lg bg-slate-100 px-4 py-3 dark:bg-navy-600">
                                        <p class="text-xs text-slate-400 dark:text-navy-300">Account Created</p>
                                        <p class="font-medium text-slate-700 dark:text-navy-100">{{ $user->created_at?->format('M d, Y') }}</p>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </form>
            </div>

            {{-- Security Tab --}}
            <div class="col-span-12 lg:col-span-8" x-show="activeTab === 'security'" x-cloak>
                <div class="space-y-4 sm:space-y-5 lg:space-y-6">

                    {{-- Change Password --}}
                    <form method="POST" action="{{ route('agent.profile/update-password') }}">
                        @csrf
                        @method('PUT')
                        <input type="hidden" name="tab" value="security" />
                        <div class="card">
                            <div class="flex flex-col items-center space-y-4 border-b border-slate-200 p-4 dark:border-navy-500 sm:flex-row sm:justify-between sm:space-y-0 sm:px-5">
                                <div>
                                    <h2 class="text-lg font-medium tracking-wide text-slate-700 dark:text-navy-100">Change Password</h2>
                                    <p class="text-xs-plus text-slate-400 dark:text-navy-300">Update your password to keep your account secure.</p>
                                </div>
                                <button type="submit"
                                    class="btn min-w-[7rem] rounded-full bg-primary font-medium text-white hover:bg-primary-focus focus:bg-primary-focus active:bg-primary-focus/90 dark:bg-accent dark:hover:bg-accent-focus dark:focus:bg-accent-focus dark:active:bg-accent/90">
                                    Update Password
                                </button>
                            </div>
                            <div class="p-4 sm:p-5">
                                <div class="grid grid-cols-1 gap-4">
                                    <label class="block">
                                        <span class="font-medium text-slate-600 dark:text-navy-100">Current Password</span>
                                        <span class="relative mt-1.5 flex">
                                            <input class="form-input peer w-full rounded-full border border-slate-300 bg-transparent px-3 py-2 pl-9 placeholder:text-slate-400/70 hover:border-slate-400 focus:border-primary dark:border-navy-450 dark:hover:border-navy-400 dark:focus:border-accent @error('current_password') border-error @enderror"
                                                placeholder="Enter current password" type="password" name="current_password" />
                                            <span class="pointer-events-none absolute flex h-full w-10 items-center justify-center text-slate-400 peer-focus:text-primary dark:text-navy-300 dark:peer-focus:text-accent">
                                                <svg xmlns="http://www.w3.org/2000/svg" class="size-4.5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.5">
                                                    <path stroke-linecap="round" stroke-linejoin="round" d="M16.5 10.5V6.75a4.5 4.5 0 10-9 0v3.75m-.75 11.25h10.5a2.25 2.25 0 002.25-2.25v-6.75a2.25 2.25 0 00-2.25-2.25H6.75a2.25 2.25 0 00-2.25 2.25v6.75a2.25 2.25 0 002.25 2.25z" />
                                                </svg>
                                            </span>
                                        </span>
                                        @error('current_password')<span class="text-tiny-plus text-error">{{ $message }}</span>@enderror
                                    </label>
                                    <div class="grid grid-cols-1 gap-4 sm:grid-cols-2">
                                        <label class="block">
                                            <span class="font-medium text-slate-600 dark:text-navy-100">New Password</span>
                                            <span class="relative mt-1.5 flex">
                                                <input class="form-input peer w-full rounded-full border border-slate-300 bg-transparent px-3 py-2 pl-9 placeholder:text-slate-400/70 hover:border-slate-400 focus:border-primary dark:border-navy-450 dark:hover:border-navy-400 dark:focus:border-accent @error('password') border-error @enderror"
                                                    placeholder="Enter new password" type="password" name="password" />
                                                <span class="pointer-events-none absolute flex h-full w-10 items-center justify-center text-slate-400 peer-focus:text-primary dark:text-navy-300 dark:peer-focus:text-accent">
                                                    <svg xmlns="http://www.w3.org/2000/svg" class="size-4.5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.5">
                                                        <path stroke-linecap="round" stroke-linejoin="round" d="M15.75 5.25a3 3 0 013 3m3 0a6 6 0 01-7.029 5.912c-.563-.097-1.159.026-1.563.43L10.5 17.25H8.25v2.25H6v2.25H2.25v-2.818c0-.597.237-1.17.659-1.591l6.499-6.499c.404-.404.527-1 .43-1.563A6 6 0 1121.75 8.25z" />
                                                    </svg>
                                                </span>
                                            </span>
                                            @error('password')<span class="text-tiny-plus text-error">{{ $message }}</span>@enderror
                                        </label>
                                        <label class="block">
                                            <span class="font-medium text-slate-600 dark:text-navy-100">Confirm New Password</span>
                                            <span class="relative mt-1.5 flex">
                                                <input class="form-input peer w-full rounded-full border border-slate-300 bg-transparent px-3 py-2 pl-9 placeholder:text-slate-400/70 hover:border-slate-400 focus:border-primary dark:border-navy-450 dark:hover:border-navy-400 dark:focus:border-accent"
                                                    placeholder="Confirm new password" type="password" name="password_confirmation" />
                                                <span class="pointer-events-none absolute flex h-full w-10 items-center justify-center text-slate-400 peer-focus:text-primary dark:text-navy-300 dark:peer-focus:text-accent">
                                                    <svg xmlns="http://www.w3.org/2000/svg" class="size-4.5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.5">
                                                        <path stroke-linecap="round" stroke-linejoin="round" d="M15.75 5.25a3 3 0 013 3m3 0a6 6 0 01-7.029 5.912c-.563-.097-1.159.026-1.563.43L10.5 17.25H8.25v2.25H6v2.25H2.25v-2.818c0-.597.237-1.17.659-1.591l6.499-6.499c.404-.404.527-1 .43-1.563A6 6 0 1121.75 8.25z" />
                                                    </svg>
                                                </span>
                                            </span>
                                        </label>
                                    </div>
                                    <p class="text-xs text-slate-400 dark:text-navy-300">Password must be at least 8 characters with uppercase, lowercase, and numbers.</p>
                                </div>
                            </div>
                        </div>
                    </form>

                    {{-- Two-Factor Authentication --}}
                    <div class="card" x-data="{
                        showEnableModal: false,
                        showDisableModal: false,
                        step: 'choose',
                        qrUrl: '',
                        secret: '',
                        code: '',
                        loading: false,
                        sendingCode: false,
                        resendingCode: false,
                        codeResent: false,
                        emailError: '',
                        disablePassword: '',
                        async setupAuthApp() {
                            this.loading = true;
                            try {
                                const res = await fetch('{{ route('agent.profile/setup-2fa') }}', {
                                    method: 'POST',
                                    headers: {
                                        'Content-Type': 'application/json',
                                        'X-CSRF-TOKEN': '{{ csrf_token() }}',
                                        'Accept': 'application/json',
                                    },
                                });
                                const data = await res.json();
                                this.secret = data.secret;
                                this.qrUrl = `https://api.qrserver.com/v1/create-qr-code/?size=200x200&data=${encodeURIComponent(data.qr_url)}`;
                                this.step = 'scan';
                            } catch (e) {
                                console.error(e);
                            } finally {
                                this.loading = false;
                            }
                        },
                        setupEmailOtp() {
                            this.step = 'email-confirm';
                        },
                        async sendEmailCode(password) {
                            this.sendingCode = true;
                            this.emailError = '';
                            try {
                                const res = await fetch('{{ route('agent.profile/send-email-2fa-code') }}', {
                                    method: 'POST',
                                    headers: {
                                        'Content-Type': 'application/json',
                                        'X-CSRF-TOKEN': '{{ csrf_token() }}',
                                        'Accept': 'application/json',
                                    },
                                    body: JSON.stringify({ current_password: password }),
                                });
                                const data = await res.json();
                                if (!res.ok) {
                                    this.emailError = data.errors?.current_password?.[0] || data.message || 'Something went wrong.';
                                    return;
                                }
                                this.step = 'email-verify';
                            } catch (e) {
                                this.emailError = 'Network error. Please try again.';
                            } finally {
                                this.sendingCode = false;
                            }
                        },
                        async resendEmailCode() {
                            this.resendingCode = true;
                            try {
                                const res = await fetch('{{ route('agent.profile/send-email-2fa-code') }}', {
                                    method: 'POST',
                                    headers: {
                                        'Content-Type': 'application/json',
                                        'X-CSRF-TOKEN': '{{ csrf_token() }}',
                                        'Accept': 'application/json',
                                    },
                                    body: JSON.stringify({ current_password: this._savedPassword }),
                                });
                                if (res.ok) {
                                    this.codeResent = true;
                                    setTimeout(() => this.codeResent = false, 60000);
                                }
                            } catch (e) {
                                console.error(e);
                            } finally {
                                this.resendingCode = false;
                            }
                        },
                        resetModal() {
                            this.step = 'choose';
                            this.qrUrl = '';
                            this.secret = '';
                            this.code = '';
                            this.loading = false;
                            this.sendingCode = false;
                            this.emailError = '';
                            this.resendingCode = false;
                            this.codeResent = false;
                            this._savedPassword = '';
                        }
                    }">
                        <div class="border-b border-slate-200 p-4 dark:border-navy-500 sm:px-5">
                            <div class="flex items-center justify-between">
                                <div>
                                    <h2 class="text-lg font-medium tracking-wide text-slate-700 dark:text-navy-100">Two-Factor Authentication</h2>
                                    <p class="text-xs-plus text-slate-400 dark:text-navy-300">Add an extra layer of security to your account.</p>
                                </div>
                                @if ($user->two_factor_enabled)
                                    <div class="badge rounded-full bg-success/10 text-success dark:bg-success/15">Enabled</div>
                                @else
                                    <div class="badge rounded-full bg-slate-150 text-slate-800 dark:bg-navy-500 dark:text-navy-100">Disabled</div>
                                @endif
                            </div>
                        </div>
                        <div class="p-4 sm:p-5">
                            <div class="flex items-start space-x-4">
                                <div class="flex size-12 shrink-0 items-center justify-center rounded-lg bg-primary/10 dark:bg-accent/15">
                                    <svg xmlns="http://www.w3.org/2000/svg" class="size-6 text-primary dark:text-accent-light" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.5">
                                        <path stroke-linecap="round" stroke-linejoin="round" d="M9 12.75L11.25 15 15 9.75m-3-7.036A11.959 11.959 0 013.598 6 11.99 11.99 0 003 9.749c0 5.592 3.824 10.29 9 11.623 5.176-1.332 9-6.03 9-11.622 0-1.31-.21-2.571-.598-3.751h-.152c-3.196 0-6.1-1.248-8.25-3.285z" />
                                    </svg>
                                </div>
                                <div class="flex-1">
                                    @if ($user->two_factor_enabled)
                                        <p class="font-medium text-slate-700 dark:text-navy-100">Two-factor authentication is active</p>
                                        <p class="mt-1 text-sm text-slate-400 dark:text-navy-300">
                                            Enabled on {{ $user->two_factor_confirmed_at?->format('M d, Y \a\t H:i') }}
                                            via {{ $user->two_factor_method === 'email' ? 'Email OTP' : 'Authenticator App' }}.
                                        </p>
                                        <button type="button" @click="showDisableModal = true"
                                            class="btn mt-4 rounded-full border border-error/30 px-5 text-error hover:bg-error/10 focus:bg-error/10">
                                            Disable Two-Factor
                                        </button>
                                    @else
                                        <p class="font-medium text-slate-700 dark:text-navy-100">Protect your account with 2FA</p>
                                        <p class="mt-1 text-sm text-slate-400 dark:text-navy-300">
                                            Two-factor authentication adds an additional layer of security to your account by requiring more than just a password to sign in.
                                        </p>
                                        <button type="button" @click="showEnableModal = true; resetModal()"
                                            class="btn mt-4 rounded-full bg-primary font-medium text-white hover:bg-primary-focus focus:bg-primary-focus active:bg-primary-focus/90 dark:bg-accent dark:hover:bg-accent-focus dark:focus:bg-accent-focus dark:active:bg-accent/90">
                                            Enable Two-Factor Authentication
                                        </button>
                                    @endif
                                </div>
                            </div>
                        </div>

                        {{-- Enable 2FA Modal --}}
                        <template x-teleport="#x-teleport-target">
                            <div class="fixed inset-0 z-100 flex flex-col items-center justify-center overflow-hidden px-4 py-6 sm:px-5"
                                x-show="showEnableModal" role="dialog" @keydown.window.escape="showEnableModal = false">
                                <div class="absolute inset-0 bg-slate-900/60 transition-opacity duration-300"
                                    @click="showEnableModal = false" x-show="showEnableModal"
                                    x-transition:enter="ease-out" x-transition:enter-start="opacity-0" x-transition:enter-end="opacity-100"
                                    x-transition:leave="ease-in" x-transition:leave-start="opacity-100" x-transition:leave-end="opacity-0"></div>
                                <div class="relative w-full max-w-lg rounded-lg bg-white transition-opacity duration-300 dark:bg-navy-700 overflow-y-auto"
                                    x-show="showEnableModal"
                                    x-transition:enter="ease-out" x-transition:enter-start="opacity-0" x-transition:enter-end="opacity-100"
                                    x-transition:leave="ease-in" x-transition:leave-start="opacity-100" x-transition:leave-end="opacity-0">

                                    {{-- Modal Header --}}
                                    <div class="flex items-center justify-between border-b border-slate-200 px-4 py-3 dark:border-navy-500 sm:px-5">
                                        <h3 class="text-lg font-medium text-slate-700 dark:text-navy-100">
                                            <span x-show="step === 'choose'">Choose 2FA Method</span>
                                            <span x-show="step === 'scan'">Set Up Authenticator App</span>
                                            <span x-show="step === 'email-confirm'" x-cloak>Enable Email OTP</span>
                                            <span x-show="step === 'email-verify'" x-cloak>Verify Email Code</span>
                                        </h3>
                                        <button @click="showEnableModal = false" class="btn size-7 rounded-full p-0 hover:bg-slate-300/20 focus:bg-slate-300/20 active:bg-slate-300/25 dark:hover:bg-navy-300/20 dark:focus:bg-navy-300/20 dark:active:bg-navy-300/25">
                                            <svg xmlns="http://www.w3.org/2000/svg" class="size-4.5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                                <path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12" />
                                            </svg>
                                        </button>
                                    </div>

                                    {{-- Step 1: Choose Method --}}
                                    <div class="px-4 py-4 sm:px-5" x-show="step === 'choose'">
                                        <p class="text-sm text-slate-400 dark:text-navy-300">Select how you'd like to receive your verification codes.</p>
                                        <div class="mt-4 space-y-3">
                                            {{-- Authenticator App Option --}}
                                            <button type="button" @click="setupAuthApp()"
                                                class="flex w-full items-center space-x-4 rounded-lg border border-slate-200 p-4 text-left transition-all hover:border-primary hover:bg-primary/5 dark:border-navy-500 dark:hover:border-accent dark:hover:bg-accent/5">
                                                <div class="flex size-12 shrink-0 items-center justify-center rounded-lg bg-success/10">
                                                    <svg xmlns="http://www.w3.org/2000/svg" class="size-6 text-success" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.5">
                                                        <path stroke-linecap="round" stroke-linejoin="round" d="M10.5 1.5H8.25A2.25 2.25 0 006 3.75v16.5a2.25 2.25 0 002.25 2.25h7.5A2.25 2.25 0 0018 20.25V3.75a2.25 2.25 0 00-2.25-2.25H13.5m-3 0V3h3V1.5m-3 0h3m-3 18.75h3" />
                                                    </svg>
                                                </div>
                                                <div class="flex-1">
                                                    <h4 class="font-medium text-slate-700 dark:text-navy-100">Authenticator App</h4>
                                                    <p class="mt-0.5 text-xs text-slate-400 dark:text-navy-300">Use Google Authenticator, Authy, or similar app to generate codes.</p>
                                                </div>
                                                <svg xmlns="http://www.w3.org/2000/svg" class="size-5 text-slate-400" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                                    <path stroke-linecap="round" stroke-linejoin="round" d="M8.25 4.5l7.5 7.5-7.5 7.5" />
                                                </svg>
                                            </button>

                                            {{-- Email OTP Option --}}
                                            <button type="button" @click="setupEmailOtp()"
                                                class="flex w-full items-center space-x-4 rounded-lg border border-slate-200 p-4 text-left transition-all hover:border-primary hover:bg-primary/5 dark:border-navy-500 dark:hover:border-accent dark:hover:bg-accent/5">
                                                <div class="flex size-12 shrink-0 items-center justify-center rounded-lg bg-info/10">
                                                    <svg xmlns="http://www.w3.org/2000/svg" class="size-6 text-info" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.5">
                                                        <path stroke-linecap="round" stroke-linejoin="round" d="M21.75 6.75v10.5a2.25 2.25 0 01-2.25 2.25h-15a2.25 2.25 0 01-2.25-2.25V6.75m19.5 0A2.25 2.25 0 0019.5 4.5h-15a2.25 2.25 0 00-2.25 2.25m19.5 0v.243a2.25 2.25 0 01-1.07 1.916l-7.5 4.615a2.25 2.25 0 01-2.36 0L3.32 8.91a2.25 2.25 0 01-1.07-1.916V6.75" />
                                                    </svg>
                                                </div>
                                                <div class="flex-1">
                                                    <h4 class="font-medium text-slate-700 dark:text-navy-100">Email OTP</h4>
                                                    <p class="mt-0.5 text-xs text-slate-400 dark:text-navy-300">Receive a one-time code via email each time you sign in.</p>
                                                </div>
                                                <svg xmlns="http://www.w3.org/2000/svg" class="size-5 text-slate-400" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                                    <path stroke-linecap="round" stroke-linejoin="round" d="M8.25 4.5l7.5 7.5-7.5 7.5" />
                                                </svg>
                                            </button>
                                        </div>

                                        <div x-show="loading" class="mt-4 flex justify-center">
                                            <div class="spinner size-7 animate-spin rounded-full border-[3px] border-primary border-r-transparent dark:border-accent dark:border-r-transparent"></div>
                                        </div>
                                    </div>

                                    {{-- Step 2: Scan QR & Enter Code --}}
                                    <div class="px-4 py-4 sm:px-5" x-show="step === 'scan'" x-cloak>
                                        <div class="text-center">
                                            <p class="text-sm text-slate-400 dark:text-navy-300">Scan this QR code with your authenticator app (Google Authenticator, Authy, etc.)</p>
                                            <div class="my-4 flex justify-center">
                                                <div class="rounded-lg border border-slate-200 bg-white p-3 dark:border-navy-500 dark:bg-white">
                                                    <img :src="qrUrl" alt="QR Code" class="size-48" />
                                                </div>
                                            </div>
                                            <p class="text-xs text-slate-400 dark:text-navy-300">Or enter this secret key manually:</p>
                                            <div class="mt-2 inline-flex items-center space-x-2 rounded-lg bg-slate-100 px-4 py-2 dark:bg-navy-600">
                                                <code class="font-mono text-sm font-medium text-slate-700 dark:text-navy-100 select-all" x-text="secret"></code>
                                            </div>
                                        </div>

                                        <form method="POST" action="{{ route('agent.profile/confirm-2fa') }}" class="mt-5">
                                            @csrf
                                            <label class="block">
                                                <span class="font-medium text-slate-600 dark:text-navy-100">Enter the 6-digit code from your app</span>
                                                <input class="form-input mt-1.5 w-full rounded-full border border-slate-300 bg-transparent px-3 py-2 text-center font-mono text-lg tracking-[0.5em] placeholder:text-slate-400/70 hover:border-slate-400 focus:border-primary dark:border-navy-450 dark:hover:border-navy-400 dark:focus:border-accent @error('code') border-error @enderror"
                                                    type="text" name="code" maxlength="6" placeholder="000000" autocomplete="one-time-code" inputmode="numeric" required />
                                                @error('code')<span class="mt-1 text-tiny-plus text-error">{{ $message }}</span>@enderror
                                            </label>
                                            <div class="mt-4 flex space-x-2">
                                                <button type="submit"
                                                    class="btn flex-1 rounded-full bg-primary font-medium text-white hover:bg-primary-focus focus:bg-primary-focus active:bg-primary-focus/90 dark:bg-accent dark:hover:bg-accent-focus dark:focus:bg-accent-focus dark:active:bg-accent/90">
                                                    Verify & Enable
                                                </button>
                                                <button type="button" @click="showEnableModal = false"
                                                    class="btn rounded-full border border-slate-300 font-medium text-slate-700 hover:bg-slate-150 dark:border-navy-450 dark:text-navy-100 dark:hover:bg-navy-500">
                                                    Cancel
                                                </button>
                                            </div>
                                        </form>
                                    </div>

                                    {{-- Step 3: Email OTP — Password Confirmation --}}
                                    <div class="px-4 py-4 sm:px-5" x-show="step === 'email-confirm'" x-cloak>
                                        <div class="text-center">
                                            <div class="mx-auto flex size-16 items-center justify-center rounded-full bg-info/10">
                                                <svg xmlns="http://www.w3.org/2000/svg" class="size-8 text-info" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.5">
                                                    <path stroke-linecap="round" stroke-linejoin="round" d="M21.75 6.75v10.5a2.25 2.25 0 01-2.25 2.25h-15a2.25 2.25 0 01-2.25-2.25V6.75m19.5 0A2.25 2.25 0 0019.5 4.5h-15a2.25 2.25 0 00-2.25 2.25m19.5 0v.243a2.25 2.25 0 01-1.07 1.916l-7.5 4.615a2.25 2.25 0 01-2.36 0L3.32 8.91a2.25 2.25 0 01-1.07-1.916V6.75" />
                                                </svg>
                                            </div>
                                            <p class="mt-3 text-sm text-slate-600 dark:text-navy-200">
                                                We'll send a verification code to your email to confirm. Each time you sign in, a new code will be sent.
                                            </p>
                                        </div>

                                        <div class="mt-5">
                                            <label class="block">
                                                <span class="font-medium text-slate-600 dark:text-navy-100">Confirm your password</span>
                                                <span class="relative mt-1.5 flex">
                                                    <input class="form-input peer w-full rounded-full border border-slate-300 bg-transparent px-3 py-2 pl-9 placeholder:text-slate-400/70 hover:border-slate-400 focus:border-primary dark:border-navy-450 dark:hover:border-navy-400 dark:focus:border-accent"
                                                        type="password" x-ref="emailPassword" placeholder="Enter your password" required />
                                                    <span class="pointer-events-none absolute flex h-full w-10 items-center justify-center text-slate-400 peer-focus:text-primary dark:text-navy-300 dark:peer-focus:text-accent">
                                                        <svg xmlns="http://www.w3.org/2000/svg" class="size-4.5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.5">
                                                            <path stroke-linecap="round" stroke-linejoin="round" d="M16.5 10.5V6.75a4.5 4.5 0 10-9 0v3.75m-.75 11.25h10.5a2.25 2.25 0 002.25-2.25v-6.75a2.25 2.25 0 00-2.25-2.25H6.75a2.25 2.25 0 00-2.25 2.25v6.75a2.25 2.25 0 002.25 2.25z" />
                                                        </svg>
                                                    </span>
                                                </span>
                                                <span class="mt-1 text-tiny-plus text-error" x-show="emailError" x-text="emailError" x-cloak></span>
                                            </label>
                                            <div class="mt-4 flex space-x-2">
                                                <button type="button" :disabled="sendingCode"
                                                    @click="
                                                        const pw = $refs.emailPassword.value;
                                                        if (!pw) { emailError = 'Password is required.'; return; }
                                                        _savedPassword = pw;
                                                        sendEmailCode(pw);
                                                    "
                                                    class="btn flex-1 rounded-full bg-primary font-medium text-white hover:bg-primary-focus focus:bg-primary-focus active:bg-primary-focus/90 dark:bg-accent dark:hover:bg-accent-focus dark:focus:bg-accent-focus dark:active:bg-accent/90">
                                                    <span x-show="!sendingCode">Send Verification Code</span>
                                                    <span x-show="sendingCode" x-cloak>
                                                        <div class="spinner size-4.5 animate-spin rounded-full border-[3px] border-white/30 border-r-white"></div>
                                                    </span>
                                                </button>
                                                <button type="button" @click="showEnableModal = false"
                                                    class="btn rounded-full border border-slate-300 font-medium text-slate-700 hover:bg-slate-150 dark:border-navy-450 dark:text-navy-100 dark:hover:bg-navy-500">
                                                    Cancel
                                                </button>
                                            </div>
                                        </div>
                                    </div>

                                    {{-- Step 4: Email OTP — Verify Code --}}
                                    <div class="px-4 py-4 sm:px-5" x-show="step === 'email-verify'" x-cloak>
                                        <div class="text-center">
                                            <div class="mx-auto flex size-16 items-center justify-center rounded-full bg-success/10">
                                                <svg xmlns="http://www.w3.org/2000/svg" class="size-8 text-success" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.5">
                                                    <path stroke-linecap="round" stroke-linejoin="round" d="M21.75 9v.906a2.25 2.25 0 01-1.183 1.981l-6.478 3.488M2.25 9v.906a2.25 2.25 0 001.183 1.981l6.478 3.488m8.839 2.51l-4.66-2.51m0 0l-1.023-.55a2.25 2.25 0 00-2.134 0l-1.022.55m0 0l-4.661 2.51m16.5 1.615a2.25 2.25 0 01-2.25 2.25h-15a2.25 2.25 0 01-2.25-2.25V8.844a2.25 2.25 0 011.183-1.981l7.5-4.039a2.25 2.25 0 012.134 0l7.5 4.039a2.25 2.25 0 011.183 1.98V19.5z" />
                                                </svg>
                                            </div>
                                            <p class="mt-3 text-sm text-slate-600 dark:text-navy-200">
                                                We've sent a 6-digit verification code to your email. Enter it below to activate Email OTP.
                                            </p>
                                        </div>

                                        <form method="POST" action="{{ route('agent.profile/confirm-email-2fa') }}" class="mt-5">
                                            @csrf
                                            <label class="block">
                                                <span class="font-medium text-slate-600 dark:text-navy-100">Verification Code</span>
                                                <input class="form-input mt-1.5 w-full rounded-full border border-slate-300 bg-transparent px-3 py-2 text-center font-mono text-lg tracking-[0.5em] placeholder:text-slate-400/70 hover:border-slate-400 focus:border-primary dark:border-navy-450 dark:hover:border-navy-400 dark:focus:border-accent @error('code') border-error @enderror"
                                                    type="text" name="code" maxlength="6" placeholder="000000" autocomplete="one-time-code" inputmode="numeric" required />
                                                @error('code')<span class="mt-1 text-tiny-plus text-error">{{ $message }}</span>@enderror
                                            </label>
                                            <div class="mt-4 flex space-x-2">
                                                <button type="submit"
                                                    class="btn flex-1 rounded-full bg-primary font-medium text-white hover:bg-primary-focus focus:bg-primary-focus active:bg-primary-focus/90 dark:bg-accent dark:hover:bg-accent-focus dark:focus:bg-accent-focus dark:active:bg-accent/90">
                                                    Verify & Enable
                                                </button>
                                                <button type="button" @click="showEnableModal = false"
                                                    class="btn rounded-full border border-slate-300 font-medium text-slate-700 hover:bg-slate-150 dark:border-navy-450 dark:text-navy-100 dark:hover:bg-navy-500">
                                                    Cancel
                                                </button>
                                            </div>
                                            <div class="mt-3 text-center">
                                                <button type="button" :disabled="resendingCode || codeResent"
                                                    @click="resendEmailCode()"
                                                    class="text-xs text-slate-400 transition-colors hover:text-slate-800 dark:text-navy-300 dark:hover:text-navy-100">
                                                    <span x-show="!resendingCode && !codeResent">Didn't receive it? Resend code</span>
                                                    <span x-show="resendingCode" x-cloak>Sending...</span>
                                                    <span x-show="codeResent" x-cloak class="text-success">Code sent!</span>
                                                </button>
                                            </div>
                                        </form>
                                    </div>
                                </div>
                            </div>
                        </template>

                        {{-- Recovery Codes Modal --}}
                        @if (session('recovery_codes'))
                        <template x-teleport="#x-teleport-target">
                            <div class="fixed inset-0 z-100 flex flex-col items-center justify-center overflow-hidden px-4 py-6 sm:px-5"
                                x-data="{ showRecoveryCodes: true, copied: false }" x-show="showRecoveryCodes" role="dialog">
                                <div class="absolute inset-0 bg-slate-900/60 transition-opacity duration-300"
                                    x-show="showRecoveryCodes"
                                    x-transition:enter="ease-out" x-transition:enter-start="opacity-0" x-transition:enter-end="opacity-100"
                                    x-transition:leave="ease-in" x-transition:leave-start="opacity-100" x-transition:leave-end="opacity-0"></div>
                                <div class="relative w-full max-w-lg rounded-lg bg-white transition-opacity duration-300 dark:bg-navy-700 overflow-y-auto"
                                    x-show="showRecoveryCodes"
                                    x-transition:enter="ease-out" x-transition:enter-start="opacity-0" x-transition:enter-end="opacity-100"
                                    x-transition:leave="ease-in" x-transition:leave-start="opacity-100" x-transition:leave-end="opacity-0">

                                    <div class="flex items-center justify-between border-b border-slate-200 px-4 py-3 dark:border-navy-500 sm:px-5">
                                        <h3 class="text-lg font-medium text-slate-700 dark:text-navy-100">
                                            Recovery Codes
                                            @if (session('two_factor_method'))
                                                <span class="ml-2 badge rounded-full text-xs {{ session('two_factor_method') === 'email' ? 'bg-info/10 text-info' : 'bg-primary/10 text-primary dark:bg-accent/15 dark:text-accent-light' }}">
                                                    {{ session('two_factor_method') === 'email' ? 'Email OTP' : 'Authenticator App' }}
                                                </span>
                                            @endif
                                        </h3>
                                    </div>

                                    <div class="px-4 py-4 sm:px-5">
                                        <div class="flex items-center space-x-3 rounded-lg bg-warning/10 px-4 py-3 text-warning">
                                            <svg xmlns="http://www.w3.org/2000/svg" class="size-5 shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                                <path stroke-linecap="round" stroke-linejoin="round" d="M12 9v3.75m-9.303 3.376c-.866 1.5.217 3.374 1.948 3.374h14.71c1.73 0 2.813-1.874 1.948-3.374L13.949 3.378c-.866-1.5-3.032-1.5-3.898 0L2.697 16.126zM12 15.75h.007v.008H12v-.008z" />
                                            </svg>
                                            <p class="text-sm">
                                                Save these codes in a safe place. Each code can only be used once to regain access if you lose access to your
                                                {{ session('two_factor_method') === 'email' ? 'email account' : 'authenticator device' }}.
                                            </p>
                                        </div>

                                        <div class="mt-4 rounded-lg border border-slate-200 bg-slate-50 p-4 dark:border-navy-500 dark:bg-navy-600" id="recovery-codes-block">
                                            <div class="grid grid-cols-2 gap-2">
                                                @foreach (session('recovery_codes') as $code)
                                                    <div class="rounded bg-white px-3 py-1.5 text-center font-mono text-sm text-slate-700 dark:bg-navy-700 dark:text-navy-100">{{ $code }}</div>
                                                @endforeach
                                            </div>
                                        </div>

                                        <div class="mt-4 flex space-x-2">
                                            <button type="button"
                                                @click="
                                                    const codes = {{ Js::from(session('recovery_codes')) }};
                                                    navigator.clipboard.writeText(codes.join('\n'));
                                                    copied = true;
                                                    setTimeout(() => copied = false, 2000);
                                                "
                                                class="btn flex-1 rounded-full border border-slate-300 font-medium text-slate-700 hover:bg-slate-150 dark:border-navy-450 dark:text-navy-100 dark:hover:bg-navy-500">
                                                <span x-show="!copied">Copy Codes</span>
                                                <span x-show="copied" x-cloak class="text-success">Copied!</span>
                                            </button>
                                            <button type="button" @click="showRecoveryCodes = false"
                                                class="btn flex-1 rounded-full bg-primary font-medium text-white hover:bg-primary-focus focus:bg-primary-focus active:bg-primary-focus/90 dark:bg-accent dark:hover:bg-accent-focus dark:focus:bg-accent-focus dark:active:bg-accent/90">
                                                I've Saved These Codes
                                            </button>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </template>
                        @endif

                        {{-- Disable 2FA Modal --}}
                        <template x-teleport="#x-teleport-target">
                            <div class="fixed inset-0 z-100 flex flex-col items-center justify-center overflow-hidden px-4 py-6 sm:px-5"
                                x-show="showDisableModal" role="dialog" @keydown.window.escape="showDisableModal = false">
                                <div class="absolute inset-0 bg-slate-900/60 transition-opacity duration-300"
                                    @click="showDisableModal = false" x-show="showDisableModal"
                                    x-transition:enter="ease-out" x-transition:enter-start="opacity-0" x-transition:enter-end="opacity-100"
                                    x-transition:leave="ease-in" x-transition:leave-start="opacity-100" x-transition:leave-end="opacity-0"></div>
                                <div class="relative w-full max-w-lg rounded-lg bg-white transition-opacity duration-300 dark:bg-navy-700 overflow-y-auto"
                                    x-show="showDisableModal"
                                    x-transition:enter="ease-out" x-transition:enter-start="opacity-0" x-transition:enter-end="opacity-100"
                                    x-transition:leave="ease-in" x-transition:leave-start="opacity-100" x-transition:leave-end="opacity-0">

                                    {{-- Modal Header --}}
                                    <div class="flex items-center justify-between border-b border-slate-200 px-4 py-3 dark:border-navy-500 sm:px-5">
                                        <h3 class="text-lg font-medium text-slate-700 dark:text-navy-100">Disable Two-Factor Authentication</h3>
                                        <button @click="showDisableModal = false" class="btn size-7 rounded-full p-0 hover:bg-slate-300/20 focus:bg-slate-300/20 active:bg-slate-300/25 dark:hover:bg-navy-300/20 dark:focus:bg-navy-300/20 dark:active:bg-navy-300/25">
                                            <svg xmlns="http://www.w3.org/2000/svg" class="size-4.5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                                <path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12" />
                                            </svg>
                                        </button>
                                    </div>

                                    {{-- Modal Body --}}
                                    <form method="POST" action="{{ route('agent.profile/disable-2fa') }}" class="px-4 py-4 sm:px-5">
                                        @csrf
                                        @method('DELETE')
                                        <div class="flex items-center space-x-3 rounded-lg bg-warning/10 px-4 py-3 text-warning">
                                            <svg xmlns="http://www.w3.org/2000/svg" class="size-5 shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                                <path stroke-linecap="round" stroke-linejoin="round" d="M12 9v3.75m-9.303 3.376c-.866 1.5.217 3.374 1.948 3.374h14.71c1.73 0 2.813-1.874 1.948-3.374L13.949 3.378c-.866-1.5-3.032-1.5-3.898 0L2.697 16.126zM12 15.75h.007v.008H12v-.008z" />
                                            </svg>
                                            <p class="text-sm">This will remove the extra security layer from your account. You can re-enable it later.</p>
                                        </div>
                                        <label class="mt-4 block">
                                            <span class="font-medium text-slate-600 dark:text-navy-100">Password</span>
                                            <span class="relative mt-1.5 flex">
                                                <input class="form-input peer w-full rounded-full border border-slate-300 bg-transparent px-3 py-2 pl-9 placeholder:text-slate-400/70 hover:border-slate-400 focus:border-primary dark:border-navy-450 dark:hover:border-navy-400 dark:focus:border-accent @error('current_password') border-error @enderror"
                                                    type="password" name="current_password" placeholder="Enter your password" required />
                                                <span class="pointer-events-none absolute flex h-full w-10 items-center justify-center text-slate-400 peer-focus:text-primary dark:text-navy-300 dark:peer-focus:text-accent">
                                                    <svg xmlns="http://www.w3.org/2000/svg" class="size-4.5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.5">
                                                        <path stroke-linecap="round" stroke-linejoin="round" d="M16.5 10.5V6.75a4.5 4.5 0 10-9 0v3.75m-.75 11.25h10.5a2.25 2.25 0 002.25-2.25v-6.75a2.25 2.25 0 00-2.25-2.25H6.75a2.25 2.25 0 00-2.25 2.25v6.75a2.25 2.25 0 002.25 2.25z" />
                                                    </svg>
                                                </span>
                                            </span>
                                            @error('current_password')<span class="mt-1 text-tiny-plus text-error">{{ $message }}</span>@enderror
                                        </label>
                                        @if ($user->two_factor_method === 'app')
                                        <label class="mt-3 block">
                                            <span class="font-medium text-slate-600 dark:text-navy-100">Authenticator Code</span>
                                            <input class="form-input mt-1.5 w-full rounded-full border border-slate-300 bg-transparent px-3 py-2 text-center font-mono text-lg tracking-[0.5em] placeholder:text-slate-400/70 hover:border-slate-400 focus:border-primary dark:border-navy-450 dark:hover:border-navy-400 dark:focus:border-accent @error('code') border-error @enderror"
                                                type="text" name="code" maxlength="6" placeholder="000000" autocomplete="one-time-code" inputmode="numeric" required />
                                            @error('code')<span class="mt-1 text-tiny-plus text-error">{{ $message }}</span>@enderror
                                        </label>
                                        @endif
                                        <div class="mt-4 flex space-x-2">
                                            <button type="submit"
                                                class="btn flex-1 rounded-full bg-error font-medium text-white hover:bg-error-focus focus:bg-error-focus">
                                                Disable Two-Factor
                                            </button>
                                            <button type="button" @click="showDisableModal = false"
                                                class="btn rounded-full border border-slate-300 font-medium text-slate-700 hover:bg-slate-150 dark:border-navy-450 dark:text-navy-100 dark:hover:bg-navy-500">
                                                Cancel
                                            </button>
                                        </div>
                                    </form>
                                </div>
                            </div>
                        </template>
                    </div>

                    {{-- Active Sessions Info --}}
                    <div class="card">
                        <div class="border-b border-slate-200 p-4 dark:border-navy-500 sm:px-5">
                            <h2 class="text-lg font-medium tracking-wide text-slate-700 dark:text-navy-100">Session Information</h2>
                        </div>
                        <div class="p-4 sm:p-5">
                            <div class="grid grid-cols-1 gap-3 sm:grid-cols-2">
                                <div class="rounded-lg bg-slate-100 px-4 py-3 dark:bg-navy-600">
                                    <p class="text-xs text-slate-400 dark:text-navy-300">Last Login IP</p>
                                    <p class="font-medium text-slate-700 dark:text-navy-100">{{ request()->ip() }}</p>
                                </div>
                                <div class="rounded-lg bg-slate-100 px-4 py-3 dark:bg-navy-600">
                                    <p class="text-xs text-slate-400 dark:text-navy-300">Browser</p>
                                    <p class="font-medium text-slate-700 dark:text-navy-100 line-clamp-1">{{ Str::limit(request()->userAgent(), 50) }}</p>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            {{-- Sessions Tab --}}
            <div class="col-span-12 lg:col-span-8" x-show="activeTab === 'sessions'" x-cloak>
                <div class="space-y-4 sm:space-y-5 lg:space-y-6">

                    {{-- Terminate All Other Sessions --}}
                    <div class="card" x-data="{ showTerminateAll: false }">
                        <div class="flex flex-col items-center space-y-4 border-b border-slate-200 p-4 dark:border-navy-500 sm:flex-row sm:justify-between sm:space-y-0 sm:px-5">
                            <div>
                                <h2 class="text-lg font-medium tracking-wide text-slate-700 dark:text-navy-100">Active Sessions</h2>
                                <p class="text-xs-plus text-slate-400 dark:text-navy-300">Manage and terminate your active sessions on other devices.</p>
                            </div>
                            <button type="button" @click="showTerminateAll = true"
                                class="btn min-w-[7rem] rounded-full bg-error font-medium text-white hover:bg-error/80 focus:bg-error/80">
                                Terminate Others
                            </button>
                        </div>
                        <div class="p-4 sm:p-5">
                            @if (!empty($sessions))
                                <div class="space-y-3">
                                    @foreach ($sessions as $session)
                                        <div class="flex items-center justify-between rounded-lg border border-slate-200 p-4 dark:border-navy-500 {{ $session->is_current ? 'bg-primary/5 border-primary/30 dark:bg-accent/5 dark:border-accent/30' : '' }}">
                                            <div class="flex items-center space-x-4">
                                                {{-- Device Icon --}}
                                                <div class="flex size-10 items-center justify-center rounded-lg {{ $session->is_current ? 'bg-primary/10 text-primary dark:bg-accent/15 dark:text-accent-light' : 'bg-slate-100 text-slate-500 dark:bg-navy-600 dark:text-navy-200' }}">
                                                    @if ($session->device['type'] === 'mobile')
                                                        <svg xmlns="http://www.w3.org/2000/svg" class="size-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.5">
                                                            <path stroke-linecap="round" stroke-linejoin="round" d="M10.5 1.5H8.25A2.25 2.25 0 006 3.75v16.5a2.25 2.25 0 002.25 2.25h7.5A2.25 2.25 0 0018 20.25V3.75a2.25 2.25 0 00-2.25-2.25H13.5m-3 0V3h3V1.5m-3 0h3m-3 18.75h3" />
                                                        </svg>
                                                    @elseif ($session->device['type'] === 'tablet')
                                                        <svg xmlns="http://www.w3.org/2000/svg" class="size-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.5">
                                                            <path stroke-linecap="round" stroke-linejoin="round" d="M10.5 19.5h3m-6.75 2.25h10.5a2.25 2.25 0 002.25-2.25v-15a2.25 2.25 0 00-2.25-2.25H6.75A2.25 2.25 0 004.5 4.5v15a2.25 2.25 0 002.25 2.25z" />
                                                        </svg>
                                                    @else
                                                        <svg xmlns="http://www.w3.org/2000/svg" class="size-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.5">
                                                            <path stroke-linecap="round" stroke-linejoin="round" d="M9 17.25v1.007a3 3 0 01-.879 2.122L7.5 21h9l-.621-.621A3 3 0 0115 18.257V17.25m6-12V15a2.25 2.25 0 01-2.25 2.25H5.25A2.25 2.25 0 013 15V5.25m18 0A2.25 2.25 0 0018.75 3H5.25A2.25 2.25 0 003 5.25m18 0V12a2.25 2.25 0 01-2.25 2.25H5.25A2.25 2.25 0 013 12V5.25" />
                                                        </svg>
                                                    @endif
                                                </div>
                                                <div>
                                                    <div class="flex items-center space-x-2">
                                                        <p class="font-medium text-slate-700 dark:text-navy-100">
                                                            {{ $session->device['browser'] }} on {{ $session->device['os'] }}
                                                        </p>
                                                        @if ($session->is_current)
                                                            <span class="badge rounded-full bg-primary/10 text-primary dark:bg-accent/15 dark:text-accent-light text-xs px-2 py-0.5">
                                                                This device
                                                            </span>
                                                        @endif
                                                    </div>
                                                    <p class="mt-0.5 text-xs text-slate-400 dark:text-navy-300">
                                                        {{ $session->ip_address }} &middot; {{ $session->last_active }}
                                                    </p>
                                                </div>
                                            </div>
                                            @unless ($session->is_current)
                                                <form method="POST" action="{{ route('agent.profile/destroy-session', $session->id) }}"
                                                    id="terminate-session-{{ $loop->index }}"
                                                    onsubmit="event.preventDefault(); Swal.fire({ title: 'Terminate Session?', text: 'This will log out the session on that device.', icon: 'warning', showCancelButton: true, confirmButtonColor: '#f5365c', confirmButtonText: 'Terminate', cancelButtonText: 'Cancel', customClass: { popup: 'rounded-lg' } }).then((result) => { if (result.isConfirmed) this.submit(); })">
                                                    @csrf
                                                    @method('DELETE')
                                                    <button type="submit"
                                                        class="btn size-8 rounded-full p-0 text-slate-400 hover:bg-error/10 hover:text-error focus:bg-error/10 focus:text-error dark:text-navy-300 dark:hover:bg-error/15 dark:hover:text-error dark:focus:bg-error/15">
                                                        <svg xmlns="http://www.w3.org/2000/svg" class="size-4.5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.5">
                                                            <path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12" />
                                                        </svg>
                                                    </button>
                                                </form>
                                            @endunless
                                        </div>
                                    @endforeach
                                </div>
                            @else
                                <div class="text-center py-8">
                                    <svg xmlns="http://www.w3.org/2000/svg" class="mx-auto size-12 text-slate-300 dark:text-navy-400" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1">
                                        <path stroke-linecap="round" stroke-linejoin="round" d="M9 17.25v1.007a3 3 0 01-.879 2.122L7.5 21h9l-.621-.621A3 3 0 0115 18.257V17.25m6-12V15a2.25 2.25 0 01-2.25 2.25H5.25A2.25 2.25 0 013 15V5.25m18 0A2.25 2.25 0 0018.75 3H5.25A2.25 2.25 0 003 5.25m18 0V12a2.25 2.25 0 01-2.25 2.25H5.25A2.25 2.25 0 013 12V5.25" />
                                    </svg>
                                    <p class="mt-3 text-slate-400 dark:text-navy-300">No active sessions found.</p>
                                </div>
                            @endif
                        </div>

                        {{-- Terminate All Other Sessions Modal --}}
                        <template x-teleport="#x-teleport-target">
                            <div class="fixed inset-0 z-100 flex flex-col items-center justify-center overflow-hidden px-4 py-6 sm:px-5"
                                x-show="showTerminateAll" role="dialog" @keydown.window.escape="showTerminateAll = false">
                                <div class="absolute inset-0 bg-slate-900/60 transition-opacity duration-300"
                                    @click="showTerminateAll = false" x-show="showTerminateAll"
                                    x-transition:enter="ease-out" x-transition:enter-start="opacity-0" x-transition:enter-end="opacity-100"
                                    x-transition:leave="ease-in" x-transition:leave-start="opacity-100" x-transition:leave-end="opacity-0"></div>
                                <div class="relative w-full max-w-md rounded-lg bg-white transition-opacity duration-300 dark:bg-navy-700 overflow-y-auto"
                                    x-show="showTerminateAll"
                                    x-transition:enter="ease-out" x-transition:enter-start="opacity-0" x-transition:enter-end="opacity-100"
                                    x-transition:leave="ease-in" x-transition:leave-start="opacity-100" x-transition:leave-end="opacity-0">

                                    <div class="flex items-center justify-between border-b border-slate-200 px-4 py-3 dark:border-navy-500 sm:px-5">
                                        <h3 class="text-lg font-medium text-slate-700 dark:text-navy-100">Terminate Other Sessions</h3>
                                        <button @click="showTerminateAll = false" class="btn size-7 rounded-full p-0 hover:bg-slate-300/20 focus:bg-slate-300/20 active:bg-slate-300/25 dark:hover:bg-navy-300/20 dark:focus:bg-navy-300/20 dark:active:bg-navy-300/25">
                                            <svg xmlns="http://www.w3.org/2000/svg" class="size-4.5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                                <path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12" />
                                            </svg>
                                        </button>
                                    </div>

                                    <form method="POST" action="{{ route('agent.profile/destroy-other-sessions') }}" class="px-4 py-4 sm:px-5">
                                        @csrf
                                        @method('DELETE')
                                        <div class="text-center">
                                            <div class="mx-auto flex size-16 items-center justify-center rounded-full bg-warning/10">
                                                <svg xmlns="http://www.w3.org/2000/svg" class="size-8 text-warning" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.5">
                                                    <path stroke-linecap="round" stroke-linejoin="round" d="M12 9v3.75m-9.303 3.376c-.866 1.5.217 3.374 1.948 3.374h14.71c1.73 0 2.813-1.874 1.948-3.374L13.949 3.378c-.866-1.5-3.032-1.5-3.898 0L2.697 16.126zM12 15.75h.007v.008H12v-.008z" />
                                                </svg>
                                            </div>
                                            <p class="mt-3 text-sm text-slate-500 dark:text-navy-200">
                                                This will log you out of all other active sessions. Enter your password to confirm.
                                            </p>
                                        </div>
                                        <label class="mt-4 block">
                                            <span class="font-medium text-slate-600 dark:text-navy-100">Password</span>
                                            <input class="form-input mt-1.5 w-full rounded-full border border-slate-300 bg-transparent px-3 py-2 placeholder:text-slate-400/70 hover:border-slate-400 focus:border-primary dark:border-navy-450 dark:hover:border-navy-400 dark:focus:border-accent"
                                                type="password" name="password" placeholder="Enter your password" required />
                                            @error('password')
                                                <span class="mt-1 block text-tiny-plus text-error">{{ $message }}</span>
                                            @enderror
                                        </label>
                                        <div class="mt-4 flex space-x-2">
                                            <button type="submit"
                                                class="btn flex-1 rounded-full bg-error font-medium text-white hover:bg-error/80 focus:bg-error/80">
                                                Terminate All Others
                                            </button>
                                            <button type="button" @click="showTerminateAll = false"
                                                class="btn rounded-full border border-slate-300 font-medium text-slate-700 hover:bg-slate-150 dark:border-navy-450 dark:text-navy-100 dark:hover:bg-navy-500">
                                                Cancel
                                            </button>
                                        </div>
                                    </form>
                                </div>
                            </div>
                        </template>
                    </div>
                </div>
            </div>
        </div>
    </main>
</x-app-layout>
