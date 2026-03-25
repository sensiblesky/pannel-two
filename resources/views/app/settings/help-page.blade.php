<x-app-layout title="Help Page Settings" is-sidebar-open="true" is-header-blur="true">
    @slot('script')
    <script>
        document.addEventListener('DOMContentLoaded', function () {
            @if (session('success'))
                Swal.fire({ title: 'Success!', text: @json(session('success')), icon: 'success', confirmButtonColor: '#4f46e5', timer: 3000, timerProgressBar: true, customClass: { popup: 'rounded-lg' } });
            @endif
        });
    </script>
    @endslot

    <main class="main-content w-full px-[var(--margin-x)] pb-8">
        {{-- Page Header --}}
        <div class="flex items-center space-x-4 py-5 lg:py-6">
            <h2 class="text-xl font-medium text-slate-800 dark:text-navy-50 lg:text-2xl">Help Page Settings</h2>
            <div class="hidden h-full py-1 sm:flex"><div class="h-full w-px bg-slate-300 dark:bg-navy-600"></div></div>
            <ul class="hidden flex-wrap items-center space-x-2 sm:flex">
                <li class="flex items-center space-x-2">
                    <a class="text-primary transition-colors hover:text-primary-focus dark:text-accent-light dark:hover:text-accent" href="{{ route('config/help-page') }}">Configuration</a>
                    <svg xmlns="http://www.w3.org/2000/svg" class="size-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/></svg>
                </li>
                <li>Help Page</li>
            </ul>
        </div>

        <form method="POST" action="{{ route('config/help-page-update') }}">
            @csrf
            @method('PUT')

            {{-- Enable / URL --}}
            <div class="card p-4 sm:p-5">
                <div class="flex items-center justify-between">
                    <div>
                        <h3 class="text-base font-medium text-slate-700 dark:text-navy-100">Public Help Page</h3>
                        <p class="mt-1 text-sm text-slate-400 dark:text-navy-300">Enable a public-facing page where visitors can submit support tickets without logging in.</p>
                    </div>
                    <label class="inline-flex items-center space-x-2">
                        <input class="form-switch h-5 w-10 rounded-full bg-slate-300 before:rounded-full before:bg-white checked:bg-primary checked:before:bg-white dark:bg-navy-900 dark:before:bg-navy-300 dark:checked:bg-accent dark:checked:before:bg-white"
                               type="checkbox" name="help_page_enabled" value="1" {{ ($settings['help_page_enabled'] ?? '0') === '1' ? 'checked' : '' }}>
                        <span class="text-sm font-medium text-slate-600 dark:text-navy-200">Enabled</span>
                    </label>
                </div>

                @if(($settings['help_page_enabled'] ?? '0') === '1')
                    <div class="mt-4 rounded-lg bg-success/10 p-3">
                        <div class="flex items-center space-x-2 text-success">
                            <svg xmlns="http://www.w3.org/2000/svg" class="size-4" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13.828 10.172a4 4 0 00-5.656 0l-4 4a4 4 0 105.656 5.656l1.102-1.101m-.758-4.899a4 4 0 005.656 0l4-4a4 4 0 00-5.656-5.656l-1.1 1.1"/></svg>
                            <span class="text-sm font-medium">Public URL:</span>
                            <a href="{{ route('help-center') }}" target="_blank" class="text-sm underline">{{ route('help-center') }}</a>
                        </div>
                    </div>
                @endif
            </div>

            {{-- Content Settings --}}
            <div class="card mt-4 p-4 sm:p-5 lg:mt-5">
                <h3 class="text-base font-medium text-slate-700 dark:text-navy-100">Page Content</h3>
                <div class="mt-4 space-y-4">
                    <label class="block">
                        <span class="font-medium text-slate-600 dark:text-navy-100">Page Title</span>
                        <input class="form-input mt-1.5 w-full rounded-lg border border-slate-300 bg-transparent px-3 py-2 placeholder:text-slate-400/70 hover:border-slate-400 focus:border-primary dark:border-navy-450 dark:hover:border-navy-400 dark:focus:border-accent"
                               type="text" name="help_page_title" value="{{ $settings['help_page_title'] ?? 'How can we help you?' }}" placeholder="How can we help you?">
                    </label>
                    <label class="block">
                        <span class="font-medium text-slate-600 dark:text-navy-100">Subtitle</span>
                        <input class="form-input mt-1.5 w-full rounded-lg border border-slate-300 bg-transparent px-3 py-2 placeholder:text-slate-400/70 hover:border-slate-400 focus:border-primary dark:border-navy-450 dark:hover:border-navy-400 dark:focus:border-accent"
                               type="text" name="help_page_subtitle" value="{{ $settings['help_page_subtitle'] ?? '' }}" placeholder="Describe your help center in a sentence.">
                    </label>
                    <label class="block">
                        <span class="font-medium text-slate-600 dark:text-navy-100">Success Message</span>
                        <textarea class="form-textarea mt-1.5 w-full rounded-lg border border-slate-300 bg-transparent px-3 py-2 placeholder:text-slate-400/70 hover:border-slate-400 focus:border-primary dark:border-navy-450 dark:hover:border-navy-400 dark:focus:border-accent"
                                  name="help_page_success_message" rows="3" placeholder="Message shown after ticket is submitted successfully.">{{ $settings['help_page_success_message'] ?? '' }}</textarea>
                    </label>
                </div>
            </div>

            {{-- Form Field Visibility --}}
            <div class="card mt-4 p-4 sm:p-5 lg:mt-5">
                <h3 class="text-base font-medium text-slate-700 dark:text-navy-100">Form Fields</h3>
                <p class="mt-1 text-sm text-slate-400 dark:text-navy-300">Choose which fields are visible to visitors on the ticket submission form.</p>
                <div class="mt-4 space-y-4">
                    <div class="flex items-center justify-between">
                        <div>
                            <p class="font-medium text-slate-600 dark:text-navy-100">Show Category Selector</p>
                            <p class="text-xs text-slate-400 dark:text-navy-300">Let visitors choose a ticket category.</p>
                        </div>
                        <input class="form-switch h-5 w-10 rounded-full bg-slate-300 before:rounded-full before:bg-white checked:bg-primary checked:before:bg-white dark:bg-navy-900 dark:before:bg-navy-300 dark:checked:bg-accent dark:checked:before:bg-white"
                               type="checkbox" name="help_page_show_categories" value="1" {{ ($settings['help_page_show_categories'] ?? '0') === '1' ? 'checked' : '' }}>
                    </div>
                    <div class="h-px bg-slate-200 dark:bg-navy-500"></div>
                    <div class="flex items-center justify-between">
                        <div>
                            <p class="font-medium text-slate-600 dark:text-navy-100">Show Priority Selector</p>
                            <p class="text-xs text-slate-400 dark:text-navy-300">Let visitors set ticket priority level.</p>
                        </div>
                        <input class="form-switch h-5 w-10 rounded-full bg-slate-300 before:rounded-full before:bg-white checked:bg-primary checked:before:bg-white dark:bg-navy-900 dark:before:bg-navy-300 dark:checked:bg-accent dark:checked:before:bg-white"
                               type="checkbox" name="help_page_show_priority" value="1" {{ ($settings['help_page_show_priority'] ?? '0') === '1' ? 'checked' : '' }}>
                    </div>
                    <div class="h-px bg-slate-200 dark:bg-navy-500"></div>
                    <div class="flex items-center justify-between">
                        <div>
                            <p class="font-medium text-slate-600 dark:text-navy-100">Allow File Attachments</p>
                            <p class="text-xs text-slate-400 dark:text-navy-300">Let visitors upload files with their tickets.</p>
                        </div>
                        <input class="form-switch h-5 w-10 rounded-full bg-slate-300 before:rounded-full before:bg-white checked:bg-primary checked:before:bg-white dark:bg-navy-900 dark:before:bg-navy-300 dark:checked:bg-accent dark:checked:before:bg-white"
                               type="checkbox" name="help_page_show_attachments" value="1" {{ ($settings['help_page_show_attachments'] ?? '0') === '1' ? 'checked' : '' }}>
                    </div>
                    <div class="h-px bg-slate-200 dark:bg-navy-500"></div>
                    <div class="flex items-center justify-between">
                        <div>
                            <p class="font-medium text-slate-600 dark:text-navy-100">Enable Ticket Tracking</p>
                            <p class="text-xs text-slate-400 dark:text-navy-300">Allow visitors to look up their ticket status using ticket number and email.</p>
                        </div>
                        <input class="form-switch h-5 w-10 rounded-full bg-slate-300 before:rounded-full before:bg-white checked:bg-primary checked:before:bg-white dark:bg-navy-900 dark:before:bg-navy-300 dark:checked:bg-accent dark:checked:before:bg-white"
                               type="checkbox" name="help_page_show_track" value="1" {{ ($settings['help_page_show_track'] ?? '0') === '1' ? 'checked' : '' }}>
                    </div>
                </div>
            </div>

            {{-- Custom CSS --}}
            <div class="card mt-4 p-4 sm:p-5 lg:mt-5">
                <h3 class="text-base font-medium text-slate-700 dark:text-navy-100">Custom CSS</h3>
                <p class="mt-1 text-sm text-slate-400 dark:text-navy-300">Add custom CSS styles to the public help page.</p>
                <textarea class="form-textarea mt-3 w-full rounded-lg border border-slate-300 bg-transparent px-3 py-2 font-mono text-sm placeholder:text-slate-400/70 hover:border-slate-400 focus:border-primary dark:border-navy-450 dark:hover:border-navy-400 dark:focus:border-accent"
                          name="help_page_custom_css" rows="4" placeholder=".card { border-radius: 1rem; }">{{ $settings['help_page_custom_css'] ?? '' }}</textarea>
            </div>

            {{-- Save --}}
            <div class="mt-4 flex justify-end lg:mt-5">
                <button type="submit" class="btn space-x-2 bg-primary font-medium text-white shadow-lg shadow-primary/50 hover:bg-primary-focus focus:bg-primary-focus active:bg-primary-focus/90 dark:bg-accent dark:shadow-accent/50 dark:hover:bg-accent-focus dark:focus:bg-accent-focus dark:active:bg-accent/90">
                    <svg xmlns="http://www.w3.org/2000/svg" class="size-5" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/></svg>
                    <span>Save Settings</span>
                </button>
            </div>
        </form>
    </main>
</x-app-layout>
