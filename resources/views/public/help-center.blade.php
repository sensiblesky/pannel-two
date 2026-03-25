<x-base-layout title="{{ $settings['help_page_title'] ?? 'Help Center' }}">
    {{-- Minimal public header --}}
    <div class="min-h-100vh flex w-full flex-col bg-slate-50 dark:bg-navy-900">
        {{-- Header --}}
        <header class="sticky top-0 z-20 border-b border-slate-150 bg-white/80 backdrop-blur dark:border-navy-700 dark:bg-navy-800/80">
            <div class="mx-auto flex h-16 max-w-5xl items-center justify-between px-4 sm:px-6">
                <a href="{{ route('help-center') }}" class="flex items-center space-x-2">
                    <img class="size-8" src="{{ asset('images/app-logo.svg') }}" alt="logo" />
                    <span class="text-lg font-semibold text-slate-700 dark:text-navy-100">{{ config('app.name') }}</span>
                </a>
                <div class="flex items-center space-x-3">
                    @if(!empty($settings['help_page_show_track']) && $settings['help_page_show_track'] === '1')
                        <a href="{{ route('help-center.track') }}" class="btn space-x-1.5 rounded-full border border-slate-300 px-4 py-1.5 text-sm font-medium text-slate-600 hover:bg-slate-100 dark:border-navy-500 dark:text-navy-200 dark:hover:bg-navy-600">
                            <svg xmlns="http://www.w3.org/2000/svg" class="size-4" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/></svg>
                            <span>Track Ticket</span>
                        </a>
                    @endif
                    <a href="{{ route('loginView') }}" class="btn rounded-full bg-primary px-4 py-1.5 text-sm font-medium text-white hover:bg-primary-focus dark:bg-accent dark:hover:bg-accent-focus">
                        Sign In
                    </a>
                </div>
            </div>
        </header>

        {{-- Hero --}}
        <section class="relative overflow-hidden bg-gradient-to-br from-primary to-primary-focus px-4 py-16 dark:from-navy-700 dark:to-navy-900 sm:py-20">
            <div class="absolute inset-0 opacity-10">
                <svg class="h-full w-full" xmlns="http://www.w3.org/2000/svg"><defs><pattern id="hero-pattern" x="0" y="0" width="40" height="40" patternUnits="userSpaceOnUse"><path d="M0 20h40M20 0v40" stroke="currentColor" stroke-width="0.5" fill="none" class="text-white"/></pattern></defs><rect width="100%" height="100%" fill="url(#hero-pattern)"/></svg>
            </div>
            <div class="relative mx-auto max-w-3xl text-center">
                <h1 class="text-3xl font-bold text-white sm:text-4xl">{{ $settings['help_page_title'] ?? 'How can we help you?' }}</h1>
                @if(!empty($settings['help_page_subtitle']))
                    <p class="mt-4 text-lg text-blue-100 dark:text-navy-200">{{ $settings['help_page_subtitle'] }}</p>
                @endif
            </div>
        </section>

        {{-- Main Content --}}
        <main class="mx-auto -mt-8 w-full max-w-4xl px-4 pb-16 sm:px-6">
            <div class="card overflow-hidden rounded-xl shadow-lg">
                <div class="border-b border-slate-200 bg-white p-5 dark:border-navy-500 dark:bg-navy-700 sm:p-6">
                    <div class="flex items-center space-x-3">
                        <div class="flex size-10 items-center justify-center rounded-lg bg-primary/10 text-primary dark:bg-accent/10 dark:text-accent-light">
                            <svg xmlns="http://www.w3.org/2000/svg" class="size-5" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 5v2m0 4v2m0 4v2M5 5a2 2 0 00-2 2v3a2 2 0 110 4v3a2 2 0 002 2h14a2 2 0 002-2v-3a2 2 0 110-4V7a2 2 0 00-2-2H5z"/></svg>
                        </div>
                        <div>
                            <h2 class="text-lg font-semibold text-slate-700 dark:text-navy-100">Submit a Support Ticket</h2>
                            <p class="text-sm text-slate-400 dark:text-navy-300">Fill out the form below and we'll get back to you as soon as possible.</p>
                        </div>
                    </div>
                </div>

                <form method="POST" action="{{ route('help-center.store') }}" enctype="multipart/form-data" class="space-y-5 p-5 sm:p-6">
                    @csrf

                    @if($errors->any())
                        <div class="rounded-lg border border-error/30 bg-error/10 p-4">
                            <div class="flex items-center space-x-2 text-error">
                                <svg xmlns="http://www.w3.org/2000/svg" class="size-5" viewBox="0 0 20 20" fill="currentColor"><path fill-rule="evenodd" d="M18 10a8 8 0 11-16 0 8 8 0 0116 0zm-7 4a1 1 0 11-2 0 1 1 0 012 0zm-1-9a1 1 0 00-1 1v4a1 1 0 102 0V6a1 1 0 00-1-1z" clip-rule="evenodd"/></svg>
                                <p class="text-sm font-medium">Please fix the following errors:</p>
                            </div>
                            <ul class="mt-2 list-inside list-disc space-y-1 text-sm text-error">
                                @foreach($errors->all() as $error)
                                    <li>{{ $error }}</li>
                                @endforeach
                            </ul>
                        </div>
                    @endif

                    {{-- Contact Info --}}
                    <div class="grid grid-cols-1 gap-4 sm:grid-cols-2">
                        <label class="block">
                            <span class="text-sm font-medium text-slate-600 dark:text-navy-100">Full Name <span class="text-error">*</span></span>
                            <input class="form-input mt-1.5 w-full rounded-lg border border-slate-300 bg-transparent px-3 py-2 placeholder:text-slate-400/70 hover:border-slate-400 focus:border-primary dark:border-navy-450 dark:hover:border-navy-400 dark:focus:border-accent @error('name') !border-error @enderror"
                                   type="text" name="name" value="{{ old('name') }}" placeholder="John Doe" required>
                        </label>
                        <label class="block">
                            <span class="text-sm font-medium text-slate-600 dark:text-navy-100">Email Address <span class="text-error">*</span></span>
                            <input class="form-input mt-1.5 w-full rounded-lg border border-slate-300 bg-transparent px-3 py-2 placeholder:text-slate-400/70 hover:border-slate-400 focus:border-primary dark:border-navy-450 dark:hover:border-navy-400 dark:focus:border-accent @error('email') !border-error @enderror"
                                   type="email" name="email" value="{{ old('email') }}" placeholder="john@example.com" required>
                        </label>
                    </div>

                    <label class="block">
                        <span class="text-sm font-medium text-slate-600 dark:text-navy-100">Phone Number</span>
                        <input class="form-input mt-1.5 w-full rounded-lg border border-slate-300 bg-transparent px-3 py-2 placeholder:text-slate-400/70 hover:border-slate-400 focus:border-primary dark:border-navy-450 dark:hover:border-navy-400 dark:focus:border-accent"
                               type="tel" name="phone" value="{{ old('phone') }}" placeholder="+1 (555) 000-0000">
                    </label>

                    {{-- Ticket Details --}}
                    <label class="block">
                        <span class="text-sm font-medium text-slate-600 dark:text-navy-100">Subject <span class="text-error">*</span></span>
                        <input class="form-input mt-1.5 w-full rounded-lg border border-slate-300 bg-transparent px-3 py-2 placeholder:text-slate-400/70 hover:border-slate-400 focus:border-primary dark:border-navy-450 dark:hover:border-navy-400 dark:focus:border-accent @error('subject') !border-error @enderror"
                               type="text" name="subject" value="{{ old('subject') }}" placeholder="Brief description of your issue" required>
                    </label>

                    <div class="grid grid-cols-1 gap-4 sm:grid-cols-2">
                        @if(!empty($settings['help_page_show_categories']) && $settings['help_page_show_categories'] === '1')
                            <label class="block">
                                <span class="text-sm font-medium text-slate-600 dark:text-navy-100">Category</span>
                                <select name="category_id" class="form-select mt-1.5 w-full rounded-lg border border-slate-300 bg-white px-3 py-2 hover:border-slate-400 focus:border-primary dark:border-navy-450 dark:bg-navy-700 dark:hover:border-navy-400 dark:focus:border-accent">
                                    <option value="">Select a category</option>
                                    @foreach($categories as $category)
                                        <option value="{{ $category->id }}" @selected(old('category_id') == $category->id)>{{ $category->name }}</option>
                                    @endforeach
                                </select>
                            </label>
                        @endif

                        @if(!empty($settings['help_page_show_priority']) && $settings['help_page_show_priority'] === '1')
                            <label class="block">
                                <span class="text-sm font-medium text-slate-600 dark:text-navy-100">Priority</span>
                                <select name="priority_id" class="form-select mt-1.5 w-full rounded-lg border border-slate-300 bg-white px-3 py-2 hover:border-slate-400 focus:border-primary dark:border-navy-450 dark:bg-navy-700 dark:hover:border-navy-400 dark:focus:border-accent">
                                    <option value="">Select priority</option>
                                    @foreach($priorities as $priority)
                                        <option value="{{ $priority->id }}" @selected(old('priority_id') == $priority->id)>{{ $priority->name }}</option>
                                    @endforeach
                                </select>
                            </label>
                        @endif
                    </div>

                    <label class="block">
                        <span class="text-sm font-medium text-slate-600 dark:text-navy-100">Description <span class="text-error">*</span></span>
                        <textarea class="form-textarea mt-1.5 w-full rounded-lg border border-slate-300 bg-transparent px-3 py-2 placeholder:text-slate-400/70 hover:border-slate-400 focus:border-primary dark:border-navy-450 dark:hover:border-navy-400 dark:focus:border-accent @error('description') !border-error @enderror"
                                  name="description" rows="5" placeholder="Describe your issue in detail..." required>{{ old('description') }}</textarea>
                    </label>

                    @if(!empty($settings['help_page_show_attachments']) && $settings['help_page_show_attachments'] === '1')
                        <label class="block">
                            <span class="text-sm font-medium text-slate-600 dark:text-navy-100">Attachments</span>
                            <input class="form-input mt-1.5 w-full rounded-lg border border-slate-300 bg-transparent px-3 py-2 file:mr-4 file:rounded-full file:border-0 file:bg-primary/10 file:px-4 file:py-1 file:text-sm file:font-medium file:text-primary hover:border-slate-400 focus:border-primary dark:border-navy-450 dark:hover:border-navy-400 dark:focus:border-accent"
                                   type="file" name="attachments[]" multiple accept=".jpg,.jpeg,.png,.gif,.pdf,.doc,.docx,.txt,.zip">
                            <span class="mt-1 text-xs text-slate-400 dark:text-navy-300">Max 5 files, 10MB each. Accepted: images, PDF, DOC, TXT, ZIP.</span>
                        </label>
                    @endif

                    <button type="submit" class="btn w-full space-x-2 rounded-lg bg-primary py-2.5 font-medium text-white shadow-lg shadow-primary/50 hover:bg-primary-focus focus:bg-primary-focus active:bg-primary-focus/90 dark:bg-accent dark:shadow-accent/50 dark:hover:bg-accent-focus dark:focus:bg-accent-focus dark:active:bg-accent/90">
                        <svg xmlns="http://www.w3.org/2000/svg" class="size-5" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 19l9 2-9-18-9 18 9-2zm0 0v-8"/></svg>
                        <span>Submit Ticket</span>
                    </button>
                </form>
            </div>
        </main>

        {{-- Footer --}}
        <footer class="mt-auto border-t border-slate-200 bg-white py-6 dark:border-navy-700 dark:bg-navy-800">
            <div class="mx-auto max-w-5xl px-4 text-center text-sm text-slate-400 dark:text-navy-300 sm:px-6">
                <p>&copy; {{ date('Y') }} {{ config('app.name') }}. All rights reserved.</p>
            </div>
        </footer>
    </div>

    @if(!empty($settings['help_page_custom_css']))
        <style>{!! $settings['help_page_custom_css'] !!}</style>
    @endif
</x-base-layout>
