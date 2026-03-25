<x-app-layout title="Realtime Settings" is-sidebar-open="true" is-header-blur="true">
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
                    title: 'Error!',
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
        <div class="flex items-center space-x-4 py-5 lg:py-6">
            <h2 class="text-xl font-medium text-slate-800 dark:text-navy-50 lg:text-2xl">Realtime Settings</h2>
            <div class="hidden h-full py-1 sm:flex"><div class="h-full w-px bg-slate-300 dark:bg-navy-600"></div></div>
            <ul class="hidden flex-wrap items-center space-x-2 sm:flex">
                <li class="flex items-center space-x-2">
                    <a class="text-primary transition-colors hover:text-primary-focus dark:text-accent-light dark:hover:text-accent" href="{{ route('config/realtime') }}">Configuration</a>
                    <svg x-ignore xmlns="http://www.w3.org/2000/svg" class="size-4" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7" /></svg>
                </li>
                <li>Realtime</li>
            </ul>
        </div>

        <form method="POST" action="{{ route('config/realtime-update') }}" x-data="{
            driver: '{{ old('realtime_driver', $settings['realtime_driver'] ?? 'polling') }}',
            fallback: '{{ old('realtime_fallback_driver', $settings['realtime_fallback_driver'] ?? 'polling') }}',
            testing: false,
            testResult: null,
            testConnection() {
                this.testing = true;
                this.testResult = null;
                fetch('{{ route('config/realtime-test') }}', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': document.querySelector('meta[name=csrf-token]').content,
                        'Accept': 'application/json',
                    },
                })
                .then(r => r.json())
                .then(data => {
                    this.testResult = data;
                    this.testing = false;
                })
                .catch(() => {
                    this.testResult = { success: false, message: 'Network error. Check your connection.' };
                    this.testing = false;
                });
            }
        }">
            @csrf
            @method('PUT')

            <div class="grid grid-cols-1 gap-4 sm:gap-5 lg:gap-6">

                {{-- Driver Selection --}}
                <div class="card">
                    <div class="flex flex-col items-center space-y-4 border-b border-slate-200 p-4 dark:border-navy-500 sm:flex-row sm:justify-between sm:space-y-0 sm:px-5">
                        <div>
                            <h2 class="text-lg font-medium tracking-wide text-slate-700 dark:text-navy-100">Realtime Driver</h2>
                            <p class="text-xs-plus text-slate-400 dark:text-navy-300">Choose how messages, typing indicators, and presence updates are delivered in real time.</p>
                        </div>
                        <div class="flex items-center space-x-3">
                            <button type="button" @click="testConnection()" :disabled="testing"
                                class="btn space-x-2 border border-slate-300 font-medium text-slate-700 hover:bg-slate-150 focus:bg-slate-150 active:bg-slate-150/80 dark:border-navy-450 dark:text-navy-100 dark:hover:bg-navy-500 dark:focus:bg-navy-500 dark:active:bg-navy-500/90 disabled:opacity-50">
                                <svg x-show="!testing" class="size-4.5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M13 10V3L4 14h7v7l9-11h-7z" /></svg>
                                <svg x-show="testing" x-cloak class="size-4.5 animate-spin" fill="none" viewBox="0 0 24 24"><circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle><path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4z"></path></svg>
                                <span x-text="testing ? 'Testing...' : 'Test Connection'"></span>
                            </button>
                            <button type="submit"
                                class="btn space-x-2 bg-primary font-medium text-white shadow-lg shadow-primary/50 hover:bg-primary-focus focus:bg-primary-focus active:bg-primary-focus/90 dark:bg-accent dark:shadow-accent/50 dark:hover:bg-accent-focus dark:focus:bg-accent-focus dark:active:bg-accent/90">
                                <svg class="size-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M5 13l4 4L19 7" /></svg>
                                <span>Save Settings</span>
                            </button>
                        </div>
                    </div>

                    {{-- Test result banner --}}
                    <template x-if="testResult">
                        <div class="mx-4 mt-4 sm:mx-5" :class="testResult.success ? 'alert flex items-center rounded-lg bg-success/15 px-4 py-3 text-success sm:px-5' : 'alert flex items-center rounded-lg bg-error/15 px-4 py-3 text-error sm:px-5'">
                            <svg x-show="testResult.success" class="size-5 shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z" /></svg>
                            <svg x-show="!testResult.success" class="size-5 shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z" /></svg>
                            <span class="ml-3 text-sm font-medium" x-text="testResult.message"></span>
                            <span x-show="testResult.driver" class="ml-2 text-xs opacity-70" x-text="'(Driver: ' + testResult.driver + ')'"></span>
                        </div>
                    </template>

                    <div class="space-y-5 p-4 sm:p-5">
                        {{-- Primary Driver --}}
                        <div>
                            <span class="font-medium text-slate-600 dark:text-navy-100">Primary Driver <span class="text-error">*</span></span>
                            <p class="mt-1 text-xs text-slate-400 dark:text-navy-300">Select the transport used for real-time communication. Polling works everywhere; Pusher and Ably require credentials.</p>
                            <input type="hidden" name="realtime_driver" :value="driver" />
                            <div class="mt-3 grid grid-cols-3 gap-3">
                                <button type="button" @click="driver = 'polling'"
                                    :class="driver === 'polling' ? 'border-primary bg-primary/5 text-primary dark:border-accent dark:bg-accent/10 dark:text-accent-light' : 'border-slate-200 text-slate-600 dark:border-navy-500 dark:text-navy-100'"
                                    class="flex flex-col items-center justify-center space-y-1.5 rounded-lg border-2 px-4 py-4 font-medium transition-all hover:bg-slate-50 dark:hover:bg-navy-600">
                                    <svg class="size-6" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.5"><path stroke-linecap="round" stroke-linejoin="round" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15" /></svg>
                                    <span>Polling</span>
                                    <span class="text-xs font-normal text-slate-400 dark:text-navy-300">No setup needed</span>
                                </button>
                                <button type="button" @click="driver = 'pusher'"
                                    :class="driver === 'pusher' ? 'border-primary bg-primary/5 text-primary dark:border-accent dark:bg-accent/10 dark:text-accent-light' : 'border-slate-200 text-slate-600 dark:border-navy-500 dark:text-navy-100'"
                                    class="flex flex-col items-center justify-center space-y-1.5 rounded-lg border-2 px-4 py-4 font-medium transition-all hover:bg-slate-50 dark:hover:bg-navy-600">
                                    <svg class="size-6" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.5"><path stroke-linecap="round" stroke-linejoin="round" d="M13 10V3L4 14h7v7l9-11h-7z" /></svg>
                                    <span>Pusher</span>
                                    <span class="text-xs font-normal text-slate-400 dark:text-navy-300">WebSocket push</span>
                                </button>
                                <button type="button" @click="driver = 'ably'"
                                    :class="driver === 'ably' ? 'border-primary bg-primary/5 text-primary dark:border-accent dark:bg-accent/10 dark:text-accent-light' : 'border-slate-200 text-slate-600 dark:border-navy-500 dark:text-navy-100'"
                                    class="flex flex-col items-center justify-center space-y-1.5 rounded-lg border-2 px-4 py-4 font-medium transition-all hover:bg-slate-50 dark:hover:bg-navy-600">
                                    <svg class="size-6" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.5"><path stroke-linecap="round" stroke-linejoin="round" d="M8.288 15.038a5.25 5.25 0 017.424 0M5.106 11.856c3.807-3.808 9.98-3.808 13.788 0M1.924 8.674c5.565-5.565 14.587-5.565 20.152 0M12.53 18.22l-.53.53-.53-.53a.75.75 0 011.06 0z" /></svg>
                                    <span>Ably</span>
                                    <span class="text-xs font-normal text-slate-400 dark:text-navy-300">WebSocket push</span>
                                </button>
                            </div>
                        </div>

                        {{-- Warning when non-polling selected without credentials --}}
                        <template x-if="driver === 'pusher'">
                            <div x-show="!('{{ $settings['pusher_app_id'] ?? '' }}' && '{{ $settings['pusher_key'] ?? '' }}' && '{{ $settings['pusher_secret'] ?? '' }}')" class="alert flex items-center rounded-lg bg-warning/15 px-4 py-3 text-warning sm:px-5">
                                <svg class="size-5 shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z" /></svg>
                                <span class="ml-3 text-sm">Pusher credentials are missing. Fill them below or the system will fall back to polling.</span>
                            </div>
                        </template>
                        <template x-if="driver === 'ably'">
                            <div x-show="!('{{ $settings['ably_key'] ?? '' }}')" class="alert flex items-center rounded-lg bg-warning/15 px-4 py-3 text-warning sm:px-5">
                                <svg class="size-5 shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z" /></svg>
                                <span class="ml-3 text-sm">Ably credentials are missing. Fill them below or the system will fall back to polling.</span>
                            </div>
                        </template>

                        <div class="h-px bg-slate-200 dark:bg-navy-500"></div>

                        {{-- Fallback Driver --}}
                        <div>
                            <span class="font-medium text-slate-600 dark:text-navy-100">Fallback Driver <span class="text-error">*</span></span>
                            <p class="mt-1 text-xs text-slate-400 dark:text-navy-300">Used automatically when the primary driver is misconfigured or unavailable.</p>
                            <input type="hidden" name="realtime_fallback_driver" :value="fallback" />
                            <div class="mt-3 grid grid-cols-3 gap-3">
                                <button type="button" @click="fallback = 'polling'"
                                    :class="fallback === 'polling' ? 'border-primary bg-primary/5 text-primary dark:border-accent dark:bg-accent/10 dark:text-accent-light' : 'border-slate-200 text-slate-600 dark:border-navy-500 dark:text-navy-100'"
                                    class="flex items-center justify-center space-x-2 rounded-lg border-2 px-4 py-3 font-medium transition-all hover:bg-slate-50 dark:hover:bg-navy-600">
                                    <span>Polling</span>
                                </button>
                                <button type="button" @click="fallback = 'pusher'"
                                    :class="fallback === 'pusher' ? 'border-primary bg-primary/5 text-primary dark:border-accent dark:bg-accent/10 dark:text-accent-light' : 'border-slate-200 text-slate-600 dark:border-navy-500 dark:text-navy-100'"
                                    class="flex items-center justify-center space-x-2 rounded-lg border-2 px-4 py-3 font-medium transition-all hover:bg-slate-50 dark:hover:bg-navy-600">
                                    <span>Pusher</span>
                                </button>
                                <button type="button" @click="fallback = 'ably'"
                                    :class="fallback === 'ably' ? 'border-primary bg-primary/5 text-primary dark:border-accent dark:bg-accent/10 dark:text-accent-light' : 'border-slate-200 text-slate-600 dark:border-navy-500 dark:text-navy-100'"
                                    class="flex items-center justify-center space-x-2 rounded-lg border-2 px-4 py-3 font-medium transition-all hover:bg-slate-50 dark:hover:bg-navy-600">
                                    <span>Ably</span>
                                </button>
                            </div>
                        </div>
                    </div>
                </div>

                {{-- Polling Settings --}}
                <div class="card">
                    <div class="border-b border-slate-200 p-4 dark:border-navy-500 sm:px-5">
                        <h2 class="text-lg font-medium tracking-wide text-slate-700 dark:text-navy-100">Polling & Timing</h2>
                        <p class="text-xs-plus text-slate-400 dark:text-navy-300">Control how often clients poll for updates and timeout durations.</p>
                    </div>
                    <div class="space-y-5 p-4 sm:p-5">
                        <div class="grid grid-cols-1 gap-4 sm:grid-cols-2">
                            <label class="block">
                                <span class="font-medium text-slate-600 dark:text-navy-100">Poll Interval (ms) <span class="text-error">*</span></span>
                                <p class="mt-0.5 text-xs text-slate-400 dark:text-navy-300">How often to check for new messages (polling mode).</p>
                                <input class="form-input mt-1.5 w-full rounded-lg border border-slate-300 bg-transparent px-3 py-2 placeholder:text-slate-400/70 hover:border-slate-400 focus:border-primary dark:border-navy-450 dark:hover:border-navy-400 dark:focus:border-accent" type="number" name="polling_interval_ms" value="{{ old('polling_interval_ms', $settings['polling_interval_ms'] ?? 3000) }}" min="1000" max="60000" />
                                @error('polling_interval_ms')<span class="text-tiny-plus text-error">{{ $message }}</span>@enderror
                            </label>
                            <label class="block">
                                <span class="font-medium text-slate-600 dark:text-navy-100">Idle Poll Interval (ms) <span class="text-error">*</span></span>
                                <p class="mt-0.5 text-xs text-slate-400 dark:text-navy-300">Background poll rate when using Pusher/Ably (for sync safety).</p>
                                <input class="form-input mt-1.5 w-full rounded-lg border border-slate-300 bg-transparent px-3 py-2 placeholder:text-slate-400/70 hover:border-slate-400 focus:border-primary dark:border-navy-450 dark:hover:border-navy-400 dark:focus:border-accent" type="number" name="polling_idle_interval_ms" value="{{ old('polling_idle_interval_ms', $settings['polling_idle_interval_ms'] ?? 10000) }}" min="5000" max="120000" />
                                @error('polling_idle_interval_ms')<span class="text-tiny-plus text-error">{{ $message }}</span>@enderror
                            </label>
                            <label class="block">
                                <span class="font-medium text-slate-600 dark:text-navy-100">Typing Timeout (seconds) <span class="text-error">*</span></span>
                                <p class="mt-0.5 text-xs text-slate-400 dark:text-navy-300">How long a typing indicator stays visible.</p>
                                <input class="form-input mt-1.5 w-full rounded-lg border border-slate-300 bg-transparent px-3 py-2 placeholder:text-slate-400/70 hover:border-slate-400 focus:border-primary dark:border-navy-450 dark:hover:border-navy-400 dark:focus:border-accent" type="number" name="typing_timeout_seconds" value="{{ old('typing_timeout_seconds', $settings['typing_timeout_seconds'] ?? 5) }}" min="1" max="30" />
                                @error('typing_timeout_seconds')<span class="text-tiny-plus text-error">{{ $message }}</span>@enderror
                            </label>
                            <label class="block">
                                <span class="font-medium text-slate-600 dark:text-navy-100">Online Timeout (seconds) <span class="text-error">*</span></span>
                                <p class="mt-0.5 text-xs text-slate-400 dark:text-navy-300">How long before a user is shown as offline.</p>
                                <input class="form-input mt-1.5 w-full rounded-lg border border-slate-300 bg-transparent px-3 py-2 placeholder:text-slate-400/70 hover:border-slate-400 focus:border-primary dark:border-navy-450 dark:hover:border-navy-400 dark:focus:border-accent" type="number" name="online_timeout_seconds" value="{{ old('online_timeout_seconds', $settings['online_timeout_seconds'] ?? 120) }}" min="30" max="600" />
                                @error('online_timeout_seconds')<span class="text-tiny-plus text-error">{{ $message }}</span>@enderror
                            </label>
                        </div>
                    </div>
                </div>

                {{-- Pusher Credentials --}}
                <div class="card" x-show="driver === 'pusher' || fallback === 'pusher'" x-transition>
                    <div class="border-b border-slate-200 p-4 dark:border-navy-500 sm:px-5">
                        <h2 class="text-lg font-medium tracking-wide text-slate-700 dark:text-navy-100">Pusher Credentials</h2>
                        <p class="text-xs-plus text-slate-400 dark:text-navy-300">Enter your Pusher Channels app credentials. Get them from <a href="https://dashboard.pusher.com" target="_blank" class="text-primary hover:underline dark:text-accent-light">dashboard.pusher.com</a>.</p>
                    </div>
                    <div class="space-y-5 p-4 sm:p-5">
                        <div class="grid grid-cols-1 gap-4 sm:grid-cols-2">
                            <label class="block">
                                <span class="font-medium text-slate-600 dark:text-navy-100">App ID</span>
                                <input class="form-input mt-1.5 w-full rounded-lg border border-slate-300 bg-transparent px-3 py-2 placeholder:text-slate-400/70 hover:border-slate-400 focus:border-primary dark:border-navy-450 dark:hover:border-navy-400 dark:focus:border-accent" type="text" name="pusher_app_id" value="{{ old('pusher_app_id', $settings['pusher_app_id'] ?? '') }}" placeholder="123456" />
                            </label>
                            <label class="block">
                                <span class="font-medium text-slate-600 dark:text-navy-100">App Key</span>
                                <input class="form-input mt-1.5 w-full rounded-lg border border-slate-300 bg-transparent px-3 py-2 placeholder:text-slate-400/70 hover:border-slate-400 focus:border-primary dark:border-navy-450 dark:hover:border-navy-400 dark:focus:border-accent" type="text" name="pusher_key" value="{{ old('pusher_key', $settings['pusher_key'] ?? '') }}" placeholder="your-pusher-key" />
                            </label>
                            <label class="block">
                                <span class="font-medium text-slate-600 dark:text-navy-100">App Secret</span>
                                <input class="form-input mt-1.5 w-full rounded-lg border border-slate-300 bg-transparent px-3 py-2 placeholder:text-slate-400/70 hover:border-slate-400 focus:border-primary dark:border-navy-450 dark:hover:border-navy-400 dark:focus:border-accent" type="password" name="pusher_secret" value="{{ old('pusher_secret', $settings['pusher_secret'] ?? '') }}" placeholder="••••••••" />
                            </label>
                            <label class="block">
                                <span class="font-medium text-slate-600 dark:text-navy-100">Cluster</span>
                                <select class="form-select mt-1.5 w-full rounded-lg border border-slate-300 bg-transparent px-3 py-2 hover:border-slate-400 focus:border-primary dark:border-navy-450 dark:hover:border-navy-400 dark:focus:border-accent" name="pusher_cluster">
                                    @php $currentCluster = old('pusher_cluster', $settings['pusher_cluster'] ?? 'mt1'); @endphp
                                    @foreach(['mt1' => 'US East (mt1)', 'us2' => 'US East (us2)', 'us3' => 'US West (us3)', 'eu' => 'Europe (eu)', 'ap1' => 'Asia Pacific (ap1)', 'ap2' => 'Asia Pacific (ap2)', 'ap3' => 'Asia Pacific (ap3)', 'ap4' => 'Asia Pacific (ap4)', 'sa1' => 'South America (sa1)'] as $code => $label)
                                        <option value="{{ $code }}" @selected($currentCluster === $code)>{{ $label }}</option>
                                    @endforeach
                                </select>
                            </label>
                        </div>
                    </div>
                </div>

                {{-- Ably Credentials --}}
                <div class="card" x-show="driver === 'ably' || fallback === 'ably'" x-transition>
                    <div class="border-b border-slate-200 p-4 dark:border-navy-500 sm:px-5">
                        <h2 class="text-lg font-medium tracking-wide text-slate-700 dark:text-navy-100">Ably Credentials</h2>
                        <p class="text-xs-plus text-slate-400 dark:text-navy-300">Enter your Ably app credentials. Get them from <a href="https://ably.com/accounts" target="_blank" class="text-primary hover:underline dark:text-accent-light">ably.com/accounts</a>.</p>
                    </div>
                    <div class="space-y-5 p-4 sm:p-5">
                        <div class="grid grid-cols-1 gap-4 sm:grid-cols-2">
                            <label class="block">
                                <span class="font-medium text-slate-600 dark:text-navy-100">API Key (Server)</span>
                                <p class="mt-0.5 text-xs text-slate-400 dark:text-navy-300">Full key with publish permissions (e.g., xVLyHw.gVAbJQ:secret...).</p>
                                <input class="form-input mt-1.5 w-full rounded-lg border border-slate-300 bg-transparent px-3 py-2 placeholder:text-slate-400/70 hover:border-slate-400 focus:border-primary dark:border-navy-450 dark:hover:border-navy-400 dark:focus:border-accent" type="password" name="ably_key" value="{{ old('ably_key', $settings['ably_key'] ?? '') }}" placeholder="appId.keyId:keySecret" />
                            </label>
                            <label class="block">
                                <span class="font-medium text-slate-600 dark:text-navy-100">Client Key (Public)</span>
                                <p class="mt-0.5 text-xs text-slate-400 dark:text-navy-300">Key portion without the secret (e.g., xVLyHw.gVAbJQ). Sent to browser.</p>
                                <input class="form-input mt-1.5 w-full rounded-lg border border-slate-300 bg-transparent px-3 py-2 placeholder:text-slate-400/70 hover:border-slate-400 focus:border-primary dark:border-navy-450 dark:hover:border-navy-400 dark:focus:border-accent" type="text" name="ably_client_key" value="{{ old('ably_client_key', $settings['ably_client_key'] ?? '') }}" placeholder="appId.keyId" />
                            </label>
                        </div>
                    </div>
                </div>

            </div>
        </form>
    </main>
</x-app-layout>
