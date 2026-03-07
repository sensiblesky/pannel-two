<x-app-layout title="Communication Settings" is-sidebar-open="true" is-header-blur="true">
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
            <h2 class="text-xl font-medium text-slate-800 dark:text-navy-50 lg:text-2xl">Communication Settings</h2>
            <div class="hidden h-full py-1 sm:flex"><div class="h-full w-px bg-slate-300 dark:bg-navy-600"></div></div>
            <ul class="hidden flex-wrap items-center space-x-2 sm:flex">
                <li class="flex items-center space-x-2">
                    <a class="text-primary transition-colors hover:text-primary-focus dark:text-accent-light dark:hover:text-accent" href="{{ route('config/communication') }}">Configuration</a>
                    <svg x-ignore xmlns="http://www.w3.org/2000/svg" class="size-4" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7" /></svg>
                </li>
                <li>Communication</li>
            </ul>
        </div>

        <div class="grid grid-cols-1 gap-4 sm:gap-5 lg:gap-6">

            {{-- SMTP Configuration --}}
            <form method="POST" action="{{ route('config/communication-update') }}">
                @csrf
                @method('PUT')
                <div class="card">
                    <div class="flex flex-col items-center space-y-4 border-b border-slate-200 p-4 dark:border-navy-500 sm:flex-row sm:justify-between sm:space-y-0 sm:px-5">
                        <div>
                            <h2 class="text-lg font-medium tracking-wide text-slate-700 dark:text-navy-100">SMTP / Mail Configuration</h2>
                            <p class="text-xs-plus text-slate-400 dark:text-navy-300">Configure the outgoing mail server for sending emails.</p>
                        </div>
                        <button type="submit"
                            class="btn space-x-2 bg-primary font-medium text-white shadow-lg shadow-primary/50 hover:bg-primary-focus focus:bg-primary-focus active:bg-primary-focus/90 dark:bg-accent dark:shadow-accent/50 dark:hover:bg-accent-focus dark:focus:bg-accent-focus dark:active:bg-accent/90">
                            <svg class="size-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M5 13l4 4L19 7" /></svg>
                            <span>Save Settings</span>
                        </button>
                    </div>
                    <div class="space-y-5 p-4 sm:p-5">

                        {{-- Mail Driver --}}
                        <div>
                            <span class="font-medium text-slate-600 dark:text-navy-100">Mail Driver <span class="text-error">*</span></span>
                            <div class="mt-2 grid grid-cols-3 gap-3" x-data="{ driver: '{{ old('mail_driver', $settings['mail_driver'] ?? 'smtp') }}' }">
                                <input type="hidden" name="mail_driver" :value="driver" />
                                <button type="button" @click="driver = 'smtp'"
                                    :class="driver === 'smtp' ? 'border-primary bg-primary/5 text-primary dark:border-accent dark:bg-accent/10 dark:text-accent-light' : 'border-slate-200 text-slate-600 dark:border-navy-500 dark:text-navy-100'"
                                    class="flex items-center justify-center space-x-2 rounded-lg border-2 px-4 py-3 font-medium transition-all hover:bg-slate-50 dark:hover:bg-navy-600">
                                    <svg xmlns="http://www.w3.org/2000/svg" class="size-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.5">
                                        <path stroke-linecap="round" stroke-linejoin="round" d="M5 12h14M5 12a2 2 0 01-2-2V6a2 2 0 012-2h14a2 2 0 012 2v4a2 2 0 01-2 2M5 12a2 2 0 00-2 2v4a2 2 0 002 2h14a2 2 0 002-2v-4a2 2 0 00-2-2m-2-4h.01M17 16h.01" />
                                    </svg>
                                    <span>SMTP</span>
                                </button>
                                <button type="button" @click="driver = 'sendmail'"
                                    :class="driver === 'sendmail' ? 'border-primary bg-primary/5 text-primary dark:border-accent dark:bg-accent/10 dark:text-accent-light' : 'border-slate-200 text-slate-600 dark:border-navy-500 dark:text-navy-100'"
                                    class="flex items-center justify-center space-x-2 rounded-lg border-2 px-4 py-3 font-medium transition-all hover:bg-slate-50 dark:hover:bg-navy-600">
                                    <svg xmlns="http://www.w3.org/2000/svg" class="size-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.5">
                                        <path stroke-linecap="round" stroke-linejoin="round" d="M6 12L3.269 3.126A59.768 59.768 0 0121.485 12 59.77 59.77 0 013.27 20.876L5.999 12zm0 0h7.5" />
                                    </svg>
                                    <span>Sendmail</span>
                                </button>
                                <button type="button" @click="driver = 'log'"
                                    :class="driver === 'log' ? 'border-primary bg-primary/5 text-primary dark:border-accent dark:bg-accent/10 dark:text-accent-light' : 'border-slate-200 text-slate-600 dark:border-navy-500 dark:text-navy-100'"
                                    class="flex items-center justify-center space-x-2 rounded-lg border-2 px-4 py-3 font-medium transition-all hover:bg-slate-50 dark:hover:bg-navy-600">
                                    <svg xmlns="http://www.w3.org/2000/svg" class="size-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.5">
                                        <path stroke-linecap="round" stroke-linejoin="round" d="M19.5 14.25v-2.625a3.375 3.375 0 00-3.375-3.375h-1.5A1.125 1.125 0 0113.5 7.125v-1.5a3.375 3.375 0 00-3.375-3.375H8.25m0 12.75h7.5m-7.5 3H12M10.5 2.25H5.625c-.621 0-1.125.504-1.125 1.125v17.25c0 .621.504 1.125 1.125 1.125h12.75c.621 0 1.125-.504 1.125-1.125V11.25a9 9 0 00-9-9z" />
                                    </svg>
                                    <span>Log</span>
                                </button>
                            </div>
                        </div>

                        <div class="h-px bg-slate-200 dark:bg-navy-500"></div>

                        {{-- SMTP Server Settings --}}
                        <div>
                            <h3 class="text-base font-medium text-slate-600 dark:text-navy-100">Server Settings</h3>
                            <div class="mt-3 grid grid-cols-1 gap-4 sm:grid-cols-2">
                                <label class="block">
                                    <span class="font-medium text-slate-600 dark:text-navy-100">SMTP Host <span class="text-error">*</span></span>
                                    <input class="form-input mt-1.5 w-full rounded-lg border border-slate-300 bg-transparent px-3 py-2 placeholder:text-slate-400/70 hover:border-slate-400 focus:border-primary dark:border-navy-450 dark:hover:border-navy-400 dark:focus:border-accent @error('mail_host') border-error @enderror"
                                        placeholder="smtp.gmail.com" type="text" name="mail_host" value="{{ old('mail_host', $settings['mail_host'] ?? '') }}" />
                                    @error('mail_host')<span class="text-tiny-plus text-error">{{ $message }}</span>@enderror
                                </label>
                                <label class="block">
                                    <span class="font-medium text-slate-600 dark:text-navy-100">SMTP Port <span class="text-error">*</span></span>
                                    <input class="form-input mt-1.5 w-full rounded-lg border border-slate-300 bg-transparent px-3 py-2 placeholder:text-slate-400/70 hover:border-slate-400 focus:border-primary dark:border-navy-450 dark:hover:border-navy-400 dark:focus:border-accent @error('mail_port') border-error @enderror"
                                        placeholder="587" type="number" name="mail_port" value="{{ old('mail_port', $settings['mail_port'] ?? '587') }}" />
                                    @error('mail_port')<span class="text-tiny-plus text-error">{{ $message }}</span>@enderror
                                </label>
                                <label class="block">
                                    <span class="font-medium text-slate-600 dark:text-navy-100">Username</span>
                                    <input class="form-input mt-1.5 w-full rounded-lg border border-slate-300 bg-transparent px-3 py-2 placeholder:text-slate-400/70 hover:border-slate-400 focus:border-primary dark:border-navy-450 dark:hover:border-navy-400 dark:focus:border-accent @error('mail_username') border-error @enderror"
                                        placeholder="your@email.com" type="text" name="mail_username" value="{{ old('mail_username', $settings['mail_username'] ?? '') }}" />
                                    @error('mail_username')<span class="text-tiny-plus text-error">{{ $message }}</span>@enderror
                                </label>
                                <label class="block">
                                    <span class="font-medium text-slate-600 dark:text-navy-100">Password</span>
                                    <input class="form-input mt-1.5 w-full rounded-lg border border-slate-300 bg-transparent px-3 py-2 placeholder:text-slate-400/70 hover:border-slate-400 focus:border-primary dark:border-navy-450 dark:hover:border-navy-400 dark:focus:border-accent @error('mail_password') border-error @enderror"
                                        placeholder="{{ !empty($settings['mail_password']) ? '••••••••' : 'Enter SMTP password' }}" type="password" name="mail_password" value="" />
                                    <span class="text-xs text-slate-400 dark:text-navy-300">Leave blank to keep current password.</span>
                                    @error('mail_password')<span class="text-tiny-plus text-error">{{ $message }}</span>@enderror
                                </label>
                            </div>
                        </div>

                        {{-- Encryption --}}
                        <div>
                            <span class="font-medium text-slate-600 dark:text-navy-100">Encryption</span>
                            <div class="mt-2 flex space-x-4" x-data="{ encryption: '{{ old('mail_encryption', $settings['mail_encryption'] ?? 'tls') }}' }">
                                <input type="hidden" name="mail_encryption" :value="encryption" />
                                @foreach (['tls' => 'TLS', 'ssl' => 'SSL', 'none' => 'None'] as $value => $label)
                                    <label class="flex cursor-pointer items-center space-x-2">
                                        <input type="radio" :checked="encryption === '{{ $value }}'" @click="encryption = '{{ $value }}'"
                                            class="form-radio is-basic size-5 rounded-full border-slate-400/70 checked:border-primary checked:bg-primary hover:border-primary focus:border-primary dark:border-navy-400 dark:checked:border-accent dark:checked:bg-accent dark:hover:border-accent dark:focus:border-accent" />
                                        <span class="text-slate-600 dark:text-navy-100">{{ $label }}</span>
                                    </label>
                                @endforeach
                            </div>
                        </div>

                        <div class="h-px bg-slate-200 dark:bg-navy-500"></div>

                        {{-- Sender Settings --}}
                        <div>
                            <h3 class="text-base font-medium text-slate-600 dark:text-navy-100">Sender Information</h3>
                            <p class="text-xs text-slate-400 dark:text-navy-300">This name and email will appear as the "From" on all outgoing emails.</p>
                            <div class="mt-3 grid grid-cols-1 gap-4 sm:grid-cols-2">
                                <label class="block">
                                    <span class="font-medium text-slate-600 dark:text-navy-100">From Email <span class="text-error">*</span></span>
                                    <input class="form-input mt-1.5 w-full rounded-lg border border-slate-300 bg-transparent px-3 py-2 placeholder:text-slate-400/70 hover:border-slate-400 focus:border-primary dark:border-navy-450 dark:hover:border-navy-400 dark:focus:border-accent @error('mail_from_address') border-error @enderror"
                                        placeholder="noreply@yoursite.com" type="email" name="mail_from_address" value="{{ old('mail_from_address', $settings['mail_from_address'] ?? '') }}" />
                                    @error('mail_from_address')<span class="text-tiny-plus text-error">{{ $message }}</span>@enderror
                                </label>
                                <label class="block">
                                    <span class="font-medium text-slate-600 dark:text-navy-100">From Name <span class="text-error">*</span></span>
                                    <input class="form-input mt-1.5 w-full rounded-lg border border-slate-300 bg-transparent px-3 py-2 placeholder:text-slate-400/70 hover:border-slate-400 focus:border-primary dark:border-navy-450 dark:hover:border-navy-400 dark:focus:border-accent @error('mail_from_name') border-error @enderror"
                                        placeholder="{{ config('app.name') }}" type="text" name="mail_from_name" value="{{ old('mail_from_name', $settings['mail_from_name'] ?? config('app.name')) }}" />
                                    @error('mail_from_name')<span class="text-tiny-plus text-error">{{ $message }}</span>@enderror
                                </label>
                            </div>
                        </div>
                    </div>
                </div>
            </form>

            {{-- Test Email --}}
            <div class="card" x-data="{ showTestModal: false }">
                <div class="flex items-center justify-between p-4 sm:px-5">
                    <div>
                        <h2 class="text-lg font-medium tracking-wide text-slate-700 dark:text-navy-100">Test Email Configuration</h2>
                        <p class="text-xs-plus text-slate-400 dark:text-navy-300">Send a test email to verify your SMTP settings are working correctly.</p>
                    </div>
                    <button type="button" @click="showTestModal = true"
                        class="btn space-x-2 border border-slate-300 font-medium text-slate-800 hover:bg-slate-150 focus:bg-slate-150 dark:border-navy-450 dark:text-navy-50 dark:hover:bg-navy-500 dark:focus:bg-navy-500">
                        <svg xmlns="http://www.w3.org/2000/svg" class="size-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.5">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M6 12L3.269 3.126A59.768 59.768 0 0121.485 12 59.77 59.77 0 013.27 20.876L5.999 12zm0 0h7.5" />
                        </svg>
                        <span>Send Test Email</span>
                    </button>
                </div>

                {{-- Test Email Modal --}}
                <template x-teleport="#x-teleport-target">
                    <div class="fixed inset-0 z-100 flex flex-col items-center justify-center overflow-hidden px-4 py-6 sm:px-5"
                        x-show="showTestModal" role="dialog" @keydown.window.escape="showTestModal = false">
                        <div class="absolute inset-0 bg-slate-900/60 transition-opacity duration-300"
                            @click="showTestModal = false" x-show="showTestModal"
                            x-transition:enter="ease-out" x-transition:enter-start="opacity-0" x-transition:enter-end="opacity-100"
                            x-transition:leave="ease-in" x-transition:leave-start="opacity-100" x-transition:leave-end="opacity-0"></div>
                        <div class="relative w-full max-w-md rounded-lg bg-white transition-opacity duration-300 dark:bg-navy-700 overflow-y-auto"
                            x-show="showTestModal"
                            x-transition:enter="ease-out" x-transition:enter-start="opacity-0" x-transition:enter-end="opacity-100"
                            x-transition:leave="ease-in" x-transition:leave-start="opacity-100" x-transition:leave-end="opacity-0">

                            <div class="flex items-center justify-between border-b border-slate-200 px-4 py-3 dark:border-navy-500 sm:px-5">
                                <h3 class="text-lg font-medium text-slate-700 dark:text-navy-100">Send Test Email</h3>
                                <button @click="showTestModal = false" class="btn size-7 rounded-full p-0 hover:bg-slate-300/20 focus:bg-slate-300/20 active:bg-slate-300/25 dark:hover:bg-navy-300/20 dark:focus:bg-navy-300/20 dark:active:bg-navy-300/25">
                                    <svg xmlns="http://www.w3.org/2000/svg" class="size-4.5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                        <path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12" />
                                    </svg>
                                </button>
                            </div>

                            <form method="POST" action="{{ route('config/communication-test') }}" class="px-4 py-4 sm:px-5">
                                @csrf
                                <p class="text-sm text-slate-400 dark:text-navy-300">
                                    Enter an email address to send a test message. Make sure you've saved your SMTP settings first.
                                </p>
                                <label class="mt-4 block">
                                    <span class="font-medium text-slate-600 dark:text-navy-100">Recipient Email</span>
                                    <input class="form-input mt-1.5 w-full rounded-lg border border-slate-300 bg-transparent px-3 py-2 placeholder:text-slate-400/70 hover:border-slate-400 focus:border-primary dark:border-navy-450 dark:hover:border-navy-400 dark:focus:border-accent"
                                        type="email" name="test_email" placeholder="test@example.com" value="{{ auth()->user()->email }}" required />
                                </label>
                                <div class="mt-4 flex space-x-2">
                                    <button type="submit"
                                        class="btn flex-1 rounded-full bg-primary font-medium text-white hover:bg-primary-focus focus:bg-primary-focus active:bg-primary-focus/90 dark:bg-accent dark:hover:bg-accent-focus dark:focus:bg-accent-focus dark:active:bg-accent/90">
                                        Send Test Email
                                    </button>
                                    <button type="button" @click="showTestModal = false"
                                        class="btn rounded-full border border-slate-300 font-medium text-slate-700 hover:bg-slate-150 dark:border-navy-450 dark:text-navy-100 dark:hover:bg-navy-500">
                                        Cancel
                                    </button>
                                </div>
                            </form>
                        </div>
                    </div>
                </template>
            </div>

            {{-- Quick Reference --}}
            <div class="card">
                <div class="border-b border-slate-200 p-4 dark:border-navy-500 sm:px-5">
                    <h2 class="text-lg font-medium tracking-wide text-slate-700 dark:text-navy-100">Common SMTP Settings</h2>
                </div>
                <div class="p-4 sm:p-5">
                    <div class="is-scrollbar-hidden min-w-full overflow-x-auto">
                        <table class="w-full text-left">
                            <thead>
                                <tr class="border-b border-slate-200 dark:border-navy-500">
                                    <th class="whitespace-nowrap px-3 py-3 font-semibold text-slate-800 dark:text-navy-100">Provider</th>
                                    <th class="whitespace-nowrap px-3 py-3 font-semibold text-slate-800 dark:text-navy-100">Host</th>
                                    <th class="whitespace-nowrap px-3 py-3 font-semibold text-slate-800 dark:text-navy-100">Port</th>
                                    <th class="whitespace-nowrap px-3 py-3 font-semibold text-slate-800 dark:text-navy-100">Encryption</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach ([
                                    ['Gmail', 'smtp.gmail.com', '587', 'TLS'],
                                    ['Outlook / Office 365', 'smtp.office365.com', '587', 'TLS'],
                                    ['Yahoo Mail', 'smtp.mail.yahoo.com', '465', 'SSL'],
                                    ['Mailgun', 'smtp.mailgun.org', '587', 'TLS'],
                                    ['SendGrid', 'smtp.sendgrid.net', '587', 'TLS'],
                                    ['Amazon SES', 'email-smtp.us-east-1.amazonaws.com', '587', 'TLS'],
                                ] as $provider)
                                    <tr class="border-b border-slate-150 dark:border-navy-500">
                                        <td class="whitespace-nowrap px-3 py-3 font-medium text-slate-700 dark:text-navy-100">{{ $provider[0] }}</td>
                                        <td class="whitespace-nowrap px-3 py-3 text-slate-500 dark:text-navy-200"><code class="rounded bg-slate-100 px-1.5 py-0.5 text-xs dark:bg-navy-600">{{ $provider[1] }}</code></td>
                                        <td class="whitespace-nowrap px-3 py-3 text-slate-500 dark:text-navy-200">{{ $provider[2] }}</td>
                                        <td class="whitespace-nowrap px-3 py-3"><span class="badge rounded-full bg-primary/10 text-primary dark:bg-accent/15 dark:text-accent-light">{{ $provider[3] }}</span></td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>

        </div>
    </main>
</x-app-layout>
