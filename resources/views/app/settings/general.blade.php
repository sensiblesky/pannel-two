<x-app-layout title="General Settings" is-sidebar-open="true" is-header-blur="true">
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
            <h2 class="text-xl font-medium text-slate-800 dark:text-navy-50 lg:text-2xl">General Settings</h2>
            <div class="hidden h-full py-1 sm:flex"><div class="h-full w-px bg-slate-300 dark:bg-navy-600"></div></div>
            <ul class="hidden flex-wrap items-center space-x-2 sm:flex">
                <li class="flex items-center space-x-2">
                    <a class="text-primary transition-colors hover:text-primary-focus dark:text-accent-light dark:hover:text-accent" href="{{ route('config/general') }}">Configuration</a>
                    <svg x-ignore xmlns="http://www.w3.org/2000/svg" class="size-4" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7" /></svg>
                </li>
                <li>General</li>
            </ul>
        </div>

        <form method="POST" action="{{ route('config/general-update') }}" enctype="multipart/form-data">
            @csrf
            @method('PUT')

            <div class="grid grid-cols-1 gap-4 sm:gap-5 lg:gap-6">
                {{-- Site Information --}}
                <div>
                    <div class="card">
                        <div class="border-b border-slate-200 p-4 dark:border-navy-500 sm:px-5">
                            <h2 class="text-lg font-medium tracking-wide text-slate-700 dark:text-navy-100">Site Information</h2>
                        </div>
                        <div class="space-y-4 p-4 sm:p-5">
                            <label class="block">
                                <span class="font-medium text-slate-600 dark:text-navy-100">Site Name <span class="text-error">*</span></span>
                                <input class="form-input mt-1.5 w-full rounded-lg border border-slate-300 bg-transparent px-3 py-2 placeholder:text-slate-400/70 hover:border-slate-400 focus:border-primary dark:border-navy-450 dark:hover:border-navy-400 dark:focus:border-accent @error('site_name') border-error @enderror"
                                    placeholder="Your site name" type="text" name="site_name" value="{{ old('site_name', $settings['site_name'] ?? '') }}" />
                                @error('site_name')<span class="text-tiny-plus text-error">{{ $message }}</span>@enderror
                            </label>

                            <div class="grid grid-cols-1 gap-4 sm:grid-cols-2">
                                <label class="block">
                                    <span class="font-medium text-slate-600 dark:text-navy-100">Site Email</span>
                                    <input class="form-input mt-1.5 w-full rounded-lg border border-slate-300 bg-transparent px-3 py-2 placeholder:text-slate-400/70 hover:border-slate-400 focus:border-primary dark:border-navy-450 dark:hover:border-navy-400 dark:focus:border-accent @error('site_email') border-error @enderror"
                                        placeholder="info@example.com" type="email" name="site_email" value="{{ old('site_email', $settings['site_email'] ?? '') }}" />
                                    @error('site_email')<span class="text-tiny-plus text-error">{{ $message }}</span>@enderror
                                </label>
                                <label class="block">
                                    <span class="font-medium text-slate-600 dark:text-navy-100">Site Phone</span>
                                    <input class="form-input mt-1.5 w-full rounded-lg border border-slate-300 bg-transparent px-3 py-2 placeholder:text-slate-400/70 hover:border-slate-400 focus:border-primary dark:border-navy-450 dark:hover:border-navy-400 dark:focus:border-accent @error('site_phone') border-error @enderror"
                                        placeholder="+1 234 567 890" type="text" name="site_phone" value="{{ old('site_phone', $settings['site_phone'] ?? '') }}" />
                                    @error('site_phone')<span class="text-tiny-plus text-error">{{ $message }}</span>@enderror
                                </label>
                            </div>

                            <div x-data="{ maintenanceOn: {{ old('maintenance_mode', $settings['maintenance_mode'] ?? '0') === '1' ? 'true' : 'false' }} }">
                                <div class="flex items-center space-x-3 pt-2">
                                    <label class="inline-flex items-center space-x-2">
                                        <input name="maintenance_mode" value="1" type="checkbox"
                                            class="form-checkbox is-outline size-5 rounded border-slate-400/70 before:bg-warning checked:border-warning hover:border-warning focus:border-warning dark:border-navy-400 dark:before:bg-warning dark:checked:border-warning dark:hover:border-warning dark:focus:border-warning"
                                            x-model="maintenanceOn"
                                            {{ old('maintenance_mode', $settings['maintenance_mode'] ?? '0') === '1' ? 'checked' : '' }} />
                                        <span class="font-medium text-slate-600 dark:text-navy-100">Maintenance Mode</span>
                                    </label>
                                    <span class="text-xs text-slate-400">(Site will show maintenance page to visitors)</span>
                                </div>

                                <div x-show="maintenanceOn" x-collapse class="mt-3 rounded-lg border border-warning/30 bg-warning/5 p-4 space-y-3">
                                    <label class="block">
                                        <span class="font-medium text-slate-600 dark:text-navy-100">Scheduled End Time</span>
                                        <p class="text-xs text-slate-400 mb-1.5">When maintenance ends, users will see a countdown timer.</p>
                                        <input type="datetime-local" name="maintenance_end_at"
                                            class="form-input mt-1 w-full rounded-lg border border-slate-300 bg-transparent px-3 py-2 placeholder:text-slate-400/70 hover:border-slate-400 focus:border-primary dark:border-navy-450 dark:hover:border-navy-400 dark:focus:border-accent sm:w-80"
                                            value="{{ old('maintenance_end_at', $settings['maintenance_end_at'] ?? '') }}" />
                                        @error('maintenance_end_at')<span class="text-tiny-plus text-error">{{ $message }}</span>@enderror
                                    </label>

                                    @if(!empty($settings['maintenance_end_at']))
                                        <div class="flex items-center space-x-2">
                                            <svg class="size-4 text-warning" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z" /></svg>
                                            <span class="text-sm text-warning font-medium">Scheduled until: {{ \Carbon\Carbon::parse($settings['maintenance_end_at'])->format('M d, Y \a\t H:i') }}</span>
                                        </div>
                                    @endif
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                {{-- Logos & Branding --}}
                <div x-data="{
                    logoLightPreview: '{{ ($settings['logo_light'] ?? '') ? asset('storage/' . $settings['logo_light']) : '' }}',
                    logoDarkPreview: '{{ ($settings['logo_dark'] ?? '') ? asset('storage/' . $settings['logo_dark']) : '' }}',
                    logoCompactPreview: '{{ ($settings['logo_compact'] ?? '') ? asset('storage/' . $settings['logo_compact']) : '' }}',
                    faviconPreview: '{{ ($settings['favicon'] ?? '') ? asset('storage/' . $settings['favicon']) : '' }}',
                    previewFile(event, field) {
                        const file = event.target.files[0];
                        if (file) {
                            const reader = new FileReader();
                            reader.onload = (e) => this[field] = e.target.result;
                            reader.readAsDataURL(file);
                        }
                    }
                }">
                    <div class="card">
                        <div class="border-b border-slate-200 p-4 dark:border-navy-500 sm:px-5">
                            <h2 class="text-lg font-medium tracking-wide text-slate-700 dark:text-navy-100">Logos & Branding</h2>
                        </div>
                        <div class="space-y-5 p-4 sm:p-5">
                            {{-- Logo Light --}}
                            <div>
                                <span class="font-medium text-slate-600 dark:text-navy-100">Logo Light</span>
                                <p class="text-xs text-slate-400 mb-2">Used on dark backgrounds. Recommended: PNG with transparent background.</p>
                                <div class="flex items-center space-x-4">
                                    <div class="flex size-20 items-center justify-center rounded-lg border-2 border-dashed border-slate-300 bg-slate-800 p-2 dark:border-navy-400">
                                        <img x-show="logoLightPreview" :src="logoLightPreview" class="max-h-full max-w-full object-contain" alt="Logo Light" />
                                        <svg x-show="!logoLightPreview" class="size-8 text-slate-400" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z" /></svg>
                                    </div>
                                    <label class="btn cursor-pointer border border-slate-300 font-medium text-slate-800 hover:bg-slate-150 dark:border-navy-450 dark:text-navy-50 dark:hover:bg-navy-500">
                                        <span>Choose File</span>
                                        <input type="file" name="logo_light" accept="image/*" class="hidden" @change="previewFile($event, 'logoLightPreview')" />
                                    </label>
                                </div>
                                @error('logo_light')<span class="text-tiny-plus text-error">{{ $message }}</span>@enderror
                            </div>

                            {{-- Logo Dark --}}
                            <div>
                                <span class="font-medium text-slate-600 dark:text-navy-100">Logo Dark</span>
                                <p class="text-xs text-slate-400 mb-2">Used on light backgrounds. Recommended: PNG with transparent background.</p>
                                <div class="flex items-center space-x-4">
                                    <div class="flex size-20 items-center justify-center rounded-lg border-2 border-dashed border-slate-300 bg-white p-2 dark:border-navy-400">
                                        <img x-show="logoDarkPreview" :src="logoDarkPreview" class="max-h-full max-w-full object-contain" alt="Logo Dark" />
                                        <svg x-show="!logoDarkPreview" class="size-8 text-slate-300" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z" /></svg>
                                    </div>
                                    <label class="btn cursor-pointer border border-slate-300 font-medium text-slate-800 hover:bg-slate-150 dark:border-navy-450 dark:text-navy-50 dark:hover:bg-navy-500">
                                        <span>Choose File</span>
                                        <input type="file" name="logo_dark" accept="image/*" class="hidden" @change="previewFile($event, 'logoDarkPreview')" />
                                    </label>
                                </div>
                                @error('logo_dark')<span class="text-tiny-plus text-error">{{ $message }}</span>@enderror
                            </div>

                            {{-- Compact Logo --}}
                            <div>
                                <span class="font-medium text-slate-600 dark:text-navy-100">Compact Logo</span>
                                <p class="text-xs text-slate-400 mb-2">Small icon used when sidebar is collapsed. Recommended: Square PNG 64x64.</p>
                                <div class="flex items-center space-x-4">
                                    <div class="flex size-16 items-center justify-center rounded-lg border-2 border-dashed border-slate-300 bg-slate-50 p-2 dark:border-navy-400 dark:bg-navy-600">
                                        <img x-show="logoCompactPreview" :src="logoCompactPreview" class="max-h-full max-w-full object-contain" alt="Compact Logo" />
                                        <svg x-show="!logoCompactPreview" class="size-6 text-slate-300" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z" /></svg>
                                    </div>
                                    <label class="btn cursor-pointer border border-slate-300 font-medium text-slate-800 hover:bg-slate-150 dark:border-navy-450 dark:text-navy-50 dark:hover:bg-navy-500">
                                        <span>Choose File</span>
                                        <input type="file" name="logo_compact" accept="image/*" class="hidden" @change="previewFile($event, 'logoCompactPreview')" />
                                    </label>
                                </div>
                                @error('logo_compact')<span class="text-tiny-plus text-error">{{ $message }}</span>@enderror
                            </div>

                            {{-- Favicon --}}
                            <div>
                                <span class="font-medium text-slate-600 dark:text-navy-100">Favicon</span>
                                <p class="text-xs text-slate-400 mb-2">Browser tab icon. Recommended: PNG or ICO 32x32.</p>
                                <div class="flex items-center space-x-4">
                                    <div class="flex size-14 items-center justify-center rounded-lg border-2 border-dashed border-slate-300 bg-slate-50 p-2 dark:border-navy-400 dark:bg-navy-600">
                                        <img x-show="faviconPreview" :src="faviconPreview" class="max-h-full max-w-full object-contain" alt="Favicon" />
                                        <svg x-show="!faviconPreview" class="size-5 text-slate-300" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z" /></svg>
                                    </div>
                                    <label class="btn cursor-pointer border border-slate-300 font-medium text-slate-800 hover:bg-slate-150 dark:border-navy-450 dark:text-navy-50 dark:hover:bg-navy-500">
                                        <span>Choose File</span>
                                        <input type="file" name="favicon" accept="image/*" class="hidden" @change="previewFile($event, 'faviconPreview')" />
                                    </label>
                                </div>
                                @error('favicon')<span class="text-tiny-plus text-error">{{ $message }}</span>@enderror
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
