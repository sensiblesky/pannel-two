<x-app-layout title="Authentication Settings" is-sidebar-open="true" is-header-blur="true">
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
            <h2 class="text-xl font-medium text-slate-800 dark:text-navy-50 lg:text-2xl">Authentication</h2>
            <div class="hidden h-full py-1 sm:flex"><div class="h-full w-px bg-slate-300 dark:bg-navy-600"></div></div>
            <ul class="hidden flex-wrap items-center space-x-2 sm:flex">
                <li class="flex items-center space-x-2">
                    <a class="text-primary transition-colors hover:text-primary-focus dark:text-accent-light dark:hover:text-accent" href="{{ route('config/general') }}">Configuration</a>
                    <svg x-ignore xmlns="http://www.w3.org/2000/svg" class="size-4" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7" /></svg>
                </li>
                <li>Authentication</li>
            </ul>
        </div>

        <form method="POST" action="{{ route('config/authentication-update') }}">
            @csrf
            @method('PUT')

            <div class="grid grid-cols-1 gap-4 sm:gap-5 lg:gap-6"
                x-data="{
                    registrationEnabled: {{ ($settings['registration_enabled'] ?? '1') === '1' ? 'true' : 'false' }},
                    emailVerification: {{ ($settings['email_verification_required'] ?? '0') === '1' ? 'true' : 'false' }},
                    socialLogin: {{ ($settings['social_login_enabled'] ?? '0') === '1' ? 'true' : 'false' }},
                    socialOnlyConnected: {{ ($settings['social_login_only_connected'] ?? '0') === '1' ? 'true' : 'false' }},
                    singleDevice: {{ ($settings['single_device_login'] ?? '0') === '1' ? 'true' : 'false' }},
                    googleEnabled: {{ ($settings['google_login_enabled'] ?? '0') === '1' ? 'true' : 'false' }},
                    facebookEnabled: {{ ($settings['facebook_login_enabled'] ?? '0') === '1' ? 'true' : 'false' }},
                    twitterEnabled: {{ ($settings['twitter_login_enabled'] ?? '0') === '1' ? 'true' : 'false' }}
                }">

                {{-- Registration --}}
                <div>
                    <div class="card">
                        <div class="border-b border-slate-200 p-4 dark:border-navy-500 sm:px-5">
                            <div class="flex items-center justify-between">
                                <div>
                                    <h2 class="text-lg font-medium tracking-wide text-slate-700 dark:text-navy-100">Registration</h2>
                                    <p class="text-xs+ text-slate-400 dark:text-navy-300 mt-0.5">Configure user registration settings.</p>
                                </div>
                            </div>
                        </div>
                        <div class="space-y-4 p-4 sm:p-5">
                            <div class="flex items-center justify-between rounded-lg border border-slate-200 p-4 dark:border-navy-500">
                                <div>
                                    <p class="font-medium text-slate-700 dark:text-navy-100">Enable Registration</p>
                                    <p class="text-xs text-slate-400 dark:text-navy-300 mt-0.5">All registration related functionality will be disabled and hidden from users.</p>
                                </div>
                                <label class="inline-flex items-center">
                                    <input name="registration_enabled" value="1" type="checkbox"
                                        class="form-switch h-5 w-10 rounded-full bg-slate-300 before:rounded-full before:bg-slate-50 checked:bg-primary checked:before:bg-white dark:bg-navy-900 dark:before:bg-navy-300 dark:checked:bg-accent dark:checked:before:bg-white"
                                        x-model="registrationEnabled" />
                                </label>
                            </div>

                            <div class="flex items-center justify-between rounded-lg border border-slate-200 p-4 dark:border-navy-500" x-show="registrationEnabled" x-collapse>
                                <div>
                                    <p class="font-medium text-slate-700 dark:text-navy-100">Email Verification</p>
                                    <p class="text-xs text-slate-400 dark:text-navy-300 mt-0.5">Require newly registered users to validate their email address before being able to login.</p>
                                </div>
                                <label class="inline-flex items-center">
                                    <input name="email_verification_required" value="1" type="checkbox"
                                        class="form-switch h-5 w-10 rounded-full bg-slate-300 before:rounded-full before:bg-slate-50 checked:bg-primary checked:before:bg-white dark:bg-navy-900 dark:before:bg-navy-300 dark:checked:bg-accent dark:checked:before:bg-white"
                                        x-model="emailVerification" />
                                </label>
                            </div>
                        </div>
                    </div>
                </div>

                {{-- Social Login --}}
                <div>
                    <div class="card">
                        <div class="border-b border-slate-200 p-4 dark:border-navy-500 sm:px-5">
                            <div>
                                <h2 class="text-lg font-medium tracking-wide text-slate-700 dark:text-navy-100">Social Login Settings</h2>
                                <p class="text-xs+ text-slate-400 dark:text-navy-300 mt-0.5">Configure general settings for social login.</p>
                            </div>
                        </div>
                        <div class="space-y-4 p-4 sm:p-5">
                            <div class="flex items-center justify-between rounded-lg border border-slate-200 p-4 dark:border-navy-500">
                                <div>
                                    <p class="font-medium text-slate-700 dark:text-navy-100">Enable Social Login</p>
                                    <p class="text-xs text-slate-400 dark:text-navy-300 mt-0.5">Allow users to authenticate using third-party social accounts.</p>
                                </div>
                                <label class="inline-flex items-center">
                                    <input name="social_login_enabled" value="1" type="checkbox"
                                        class="form-switch h-5 w-10 rounded-full bg-slate-300 before:rounded-full before:bg-slate-50 checked:bg-primary checked:before:bg-white dark:bg-navy-900 dark:before:bg-navy-300 dark:checked:bg-accent dark:checked:before:bg-white"
                                        x-model="socialLogin" />
                                </label>
                            </div>

                            <div x-show="socialLogin" x-collapse class="space-y-4">
                                <div class="flex items-center justify-between rounded-lg border border-slate-200 p-4 dark:border-navy-500">
                                    <div>
                                        <p class="font-medium text-slate-700 dark:text-navy-100">Only Connected Accounts</p>
                                        <p class="text-xs text-slate-400 dark:text-navy-300 mt-0.5">User will only be able to login via socials, if they have connected it from their account settings page.</p>
                                    </div>
                                    <label class="inline-flex items-center">
                                        <input name="social_login_only_connected" value="1" type="checkbox"
                                            class="form-switch h-5 w-10 rounded-full bg-slate-300 before:rounded-full before:bg-slate-50 checked:bg-primary checked:before:bg-white dark:bg-navy-900 dark:before:bg-navy-300 dark:checked:bg-accent dark:checked:before:bg-white"
                                            x-model="socialOnlyConnected" />
                                    </label>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                {{-- Single Device Login --}}
                <div>
                    <div class="card">
                        <div class="border-b border-slate-200 p-4 dark:border-navy-500 sm:px-5">
                            <div>
                                <h2 class="text-lg font-medium tracking-wide text-slate-700 dark:text-navy-100">Single Device Login</h2>
                                <p class="text-xs+ text-slate-400 dark:text-navy-300 mt-0.5">Control how many devices can access an account simultaneously.</p>
                            </div>
                        </div>
                        <div class="p-4 sm:p-5">
                            <div class="flex items-center justify-between rounded-lg border border-slate-200 p-4 dark:border-navy-500">
                                <div>
                                    <p class="font-medium text-slate-700 dark:text-navy-100">Enforce Single Device</p>
                                    <p class="text-xs text-slate-400 dark:text-navy-300 mt-0.5">When enabled, logging in on a new device will automatically log out from all other devices.</p>
                                </div>
                                <label class="inline-flex items-center">
                                    <input name="single_device_login" value="1" type="checkbox"
                                        class="form-switch h-5 w-10 rounded-full bg-slate-300 before:rounded-full before:bg-slate-50 checked:bg-primary checked:before:bg-white dark:bg-navy-900 dark:before:bg-navy-300 dark:checked:bg-accent dark:checked:before:bg-white"
                                        x-model="singleDevice" />
                                </label>
                            </div>
                        </div>
                    </div>
                </div>

                {{-- Domain Blacklist --}}
                <div>
                    <div class="card">
                        <div class="border-b border-slate-200 p-4 dark:border-navy-500 sm:px-5">
                            <div>
                                <h2 class="text-lg font-medium tracking-wide text-slate-700 dark:text-navy-100">Domain Blacklist</h2>
                                <p class="text-xs+ text-slate-400 dark:text-navy-300 mt-0.5">Comma separated list of domains. Users will not be able to register or login using any email address from specified domains.</p>
                            </div>
                        </div>
                        <div class="p-4 sm:p-5">
                            <label class="block">
                                <span class="font-medium text-slate-600 dark:text-navy-100">Domains</span>
                                <textarea name="domain_blacklist" rows="3"
                                    class="form-textarea mt-1.5 w-full rounded-lg border border-slate-300 bg-transparent px-3 py-2 placeholder:text-slate-400/70 hover:border-slate-400 focus:border-primary dark:border-navy-450 dark:hover:border-navy-400 dark:focus:border-accent"
                                    placeholder="e.g. spam.com, throwaway.email, tempmail.org">{{ old('domain_blacklist', $settings['domain_blacklist'] ?? '') }}</textarea>
                                @error('domain_blacklist')<span class="text-tiny-plus text-error">{{ $message }}</span>@enderror
                            </label>
                        </div>
                    </div>
                </div>

                {{-- Google Login --}}
                <div>
                    <div class="card">
                        <div class="border-b border-slate-200 p-4 dark:border-navy-500 sm:px-5">
                            <div class="flex items-center justify-between">
                                <div class="flex items-center space-x-3">
                                    <div class="flex size-10 items-center justify-center rounded-lg bg-[#4285F4]/10">
                                        <svg class="size-5" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg">
                                            <path d="M22.56 12.25c0-.78-.07-1.53-.2-2.25H12v4.26h5.92a5.06 5.06 0 0 1-2.2 3.32v2.77h3.57c2.08-1.92 3.28-4.74 3.28-8.1z" fill="#4285F4"/>
                                            <path d="M12 23c2.97 0 5.46-.98 7.28-2.66l-3.57-2.77c-.98.66-2.23 1.06-3.71 1.06-2.86 0-5.29-1.93-6.16-4.53H2.18v2.84C3.99 20.53 7.7 23 12 23z" fill="#34A853"/>
                                            <path d="M5.84 14.09c-.22-.66-.35-1.36-.35-2.09s.13-1.43.35-2.09V7.07H2.18C1.43 8.55 1 10.22 1 12s.43 3.45 1.18 4.93l2.85-2.22.81-.62z" fill="#FBBC05"/>
                                            <path d="M12 5.38c1.62 0 3.06.56 4.21 1.64l3.15-3.15C17.45 2.09 14.97 1 12 1 7.7 1 3.99 3.47 2.18 7.07l3.66 2.84c.87-2.6 3.3-4.53 6.16-4.53z" fill="#EA4335"/>
                                        </svg>
                                    </div>
                                    <div>
                                        <h2 class="text-lg font-medium tracking-wide text-slate-700 dark:text-navy-100">Google Login</h2>
                                        <p class="text-xs+ text-slate-400 dark:text-navy-300 mt-0.5">Configure Google authentication settings.</p>
                                    </div>
                                </div>
                                <label class="inline-flex items-center">
                                    <input name="google_login_enabled" value="1" type="checkbox"
                                        class="form-switch h-5 w-10 rounded-full bg-slate-300 before:rounded-full before:bg-slate-50 checked:bg-primary checked:before:bg-white dark:bg-navy-900 dark:before:bg-navy-300 dark:checked:bg-accent dark:checked:before:bg-white"
                                        x-model="googleEnabled" />
                                </label>
                            </div>
                        </div>
                        <div x-show="googleEnabled" x-collapse>
                            <div class="space-y-4 p-4 sm:p-5">
                                <label class="block">
                                    <span class="font-medium text-slate-600 dark:text-navy-100">Google Client ID</span>
                                    <input class="form-input mt-1.5 w-full rounded-lg border border-slate-300 bg-transparent px-3 py-2 placeholder:text-slate-400/70 hover:border-slate-400 focus:border-primary dark:border-navy-450 dark:hover:border-navy-400 dark:focus:border-accent"
                                        placeholder="Enter your Google Client ID" type="text" name="google_client_id" value="{{ old('google_client_id', $settings['google_client_id'] ?? '') }}" />
                                </label>
                                <label class="block">
                                    <span class="font-medium text-slate-600 dark:text-navy-100">Google Client Secret</span>
                                    <input class="form-input mt-1.5 w-full rounded-lg border border-slate-300 bg-transparent px-3 py-2 placeholder:text-slate-400/70 hover:border-slate-400 focus:border-primary dark:border-navy-450 dark:hover:border-navy-400 dark:focus:border-accent"
                                        placeholder="Enter your Google Client Secret" type="password" name="google_client_secret" value="{{ old('google_client_secret', $settings['google_client_secret'] ?? '') }}" />
                                </label>
                            </div>
                        </div>
                    </div>
                </div>

                {{-- Facebook Login --}}
                <div>
                    <div class="card">
                        <div class="border-b border-slate-200 p-4 dark:border-navy-500 sm:px-5">
                            <div class="flex items-center justify-between">
                                <div class="flex items-center space-x-3">
                                    <div class="flex size-10 items-center justify-center rounded-lg bg-[#1877F2]/10">
                                        <svg class="size-5 text-[#1877F2]" fill="currentColor" viewBox="0 0 24 24"><path d="M24 12.073c0-6.627-5.373-12-12-12s-12 5.373-12 12c0 5.99 4.388 10.954 10.125 11.854v-8.385H7.078v-3.47h3.047V9.43c0-3.007 1.792-4.669 4.533-4.669 1.312 0 2.686.235 2.686.235v2.953H15.83c-1.491 0-1.956.925-1.956 1.874v2.25h3.328l-.532 3.47h-2.796v8.385C19.612 23.027 24 18.062 24 12.073z"/></svg>
                                    </div>
                                    <div>
                                        <h2 class="text-lg font-medium tracking-wide text-slate-700 dark:text-navy-100">Facebook Login</h2>
                                        <p class="text-xs+ text-slate-400 dark:text-navy-300 mt-0.5">Configure Facebook authentication settings.</p>
                                    </div>
                                </div>
                                <label class="inline-flex items-center">
                                    <input name="facebook_login_enabled" value="1" type="checkbox"
                                        class="form-switch h-5 w-10 rounded-full bg-slate-300 before:rounded-full before:bg-slate-50 checked:bg-primary checked:before:bg-white dark:bg-navy-900 dark:before:bg-navy-300 dark:checked:bg-accent dark:checked:before:bg-white"
                                        x-model="facebookEnabled" />
                                </label>
                            </div>
                        </div>
                        <div x-show="facebookEnabled" x-collapse>
                            <div class="space-y-4 p-4 sm:p-5">
                                <label class="block">
                                    <span class="font-medium text-slate-600 dark:text-navy-100">Facebook Client ID</span>
                                    <input class="form-input mt-1.5 w-full rounded-lg border border-slate-300 bg-transparent px-3 py-2 placeholder:text-slate-400/70 hover:border-slate-400 focus:border-primary dark:border-navy-450 dark:hover:border-navy-400 dark:focus:border-accent"
                                        placeholder="Enter your Facebook App ID" type="text" name="facebook_client_id" value="{{ old('facebook_client_id', $settings['facebook_client_id'] ?? '') }}" />
                                </label>
                                <label class="block">
                                    <span class="font-medium text-slate-600 dark:text-navy-100">Facebook Client Secret</span>
                                    <input class="form-input mt-1.5 w-full rounded-lg border border-slate-300 bg-transparent px-3 py-2 placeholder:text-slate-400/70 hover:border-slate-400 focus:border-primary dark:border-navy-450 dark:hover:border-navy-400 dark:focus:border-accent"
                                        placeholder="Enter your Facebook App Secret" type="password" name="facebook_client_secret" value="{{ old('facebook_client_secret', $settings['facebook_client_secret'] ?? '') }}" />
                                </label>
                            </div>
                        </div>
                    </div>
                </div>

                {{-- Twitter Login --}}
                <div>
                    <div class="card">
                        <div class="border-b border-slate-200 p-4 dark:border-navy-500 sm:px-5">
                            <div class="flex items-center justify-between">
                                <div class="flex items-center space-x-3">
                                    <div class="flex size-10 items-center justify-center rounded-lg bg-black/5 dark:bg-white/10">
                                        <svg class="size-5 text-slate-800 dark:text-white" fill="currentColor" viewBox="0 0 24 24"><path d="M18.244 2.25h3.308l-7.227 8.26 8.502 11.24H16.17l-5.214-6.817L4.99 21.75H1.68l7.73-8.835L1.254 2.25H8.08l4.713 6.231zm-1.161 17.52h1.833L7.084 4.126H5.117z"/></svg>
                                    </div>
                                    <div>
                                        <h2 class="text-lg font-medium tracking-wide text-slate-700 dark:text-navy-100">Twitter Login</h2>
                                        <p class="text-xs+ text-slate-400 dark:text-navy-300 mt-0.5">Configure Twitter authentication settings.</p>
                                    </div>
                                </div>
                                <label class="inline-flex items-center">
                                    <input name="twitter_login_enabled" value="1" type="checkbox"
                                        class="form-switch h-5 w-10 rounded-full bg-slate-300 before:rounded-full before:bg-slate-50 checked:bg-primary checked:before:bg-white dark:bg-navy-900 dark:before:bg-navy-300 dark:checked:bg-accent dark:checked:before:bg-white"
                                        x-model="twitterEnabled" />
                                </label>
                            </div>
                        </div>
                        <div x-show="twitterEnabled" x-collapse>
                            <div class="space-y-4 p-4 sm:p-5">
                                <label class="block">
                                    <span class="font-medium text-slate-600 dark:text-navy-100">Twitter Client ID</span>
                                    <input class="form-input mt-1.5 w-full rounded-lg border border-slate-300 bg-transparent px-3 py-2 placeholder:text-slate-400/70 hover:border-slate-400 focus:border-primary dark:border-navy-450 dark:hover:border-navy-400 dark:focus:border-accent"
                                        placeholder="Enter your Twitter API Key" type="text" name="twitter_client_id" value="{{ old('twitter_client_id', $settings['twitter_client_id'] ?? '') }}" />
                                </label>
                                <label class="block">
                                    <span class="font-medium text-slate-600 dark:text-navy-100">Twitter Client Secret</span>
                                    <input class="form-input mt-1.5 w-full rounded-lg border border-slate-300 bg-transparent px-3 py-2 placeholder:text-slate-400/70 hover:border-slate-400 focus:border-primary dark:border-navy-450 dark:hover:border-navy-400 dark:focus:border-accent"
                                        placeholder="Enter your Twitter API Secret" type="password" name="twitter_client_secret" value="{{ old('twitter_client_secret', $settings['twitter_client_secret'] ?? '') }}" />
                                </label>
                            </div>
                        </div>
                    </div>
                </div>

                {{-- Submit --}}
                <div>
                    <div class="flex justify-end space-x-2">
                        <button type="submit"
                            class="btn space-x-2 bg-primary font-medium text-white shadow-lg shadow-primary/50 hover:bg-primary-focus focus:bg-primary-focus active:bg-primary-focus/90 dark:bg-accent dark:shadow-accent/50 dark:hover:bg-accent-focus dark:focus:bg-accent-focus dark:active:bg-accent/90">
                            <svg class="size-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M5 13l4 4L19 7" /></svg>
                            <span>Save Settings</span>
                        </button>
                    </div>
                </div>
            </div>
        </form>
    </main>
</x-app-layout>
