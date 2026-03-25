<x-app-layout title="Ticket General Settings" is-sidebar-open="true" is-header-blur="true">

    @slot('script')
        @if(session('success'))
            <script>Swal.fire({ toast: true, position: 'top-end', icon: 'success', title: @json(session('success')), showConfirmButton: false, timer: 3000 });</script>
        @endif
    @endslot

    <main class="main-content w-full px-[var(--margin-x)] pb-8">
        <div class="flex items-center justify-between py-5 lg:py-6">
            <div class="flex items-center space-x-4">
                <h2 class="text-xl font-medium text-slate-800 dark:text-navy-50 lg:text-2xl">General Settings</h2>
                <div class="hidden h-full py-1 sm:flex"><div class="h-full w-px bg-slate-300 dark:bg-navy-600"></div></div>
                <ul class="hidden flex-wrap items-center space-x-2 sm:flex">
                    <li class="flex items-center space-x-2">
                        <a class="text-primary transition-colors hover:text-primary-focus dark:text-accent-light dark:hover:text-accent" href="{{ route('tickets/dashboard') }}">Tickets</a>
                        <svg xmlns="http://www.w3.org/2000/svg" class="size-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/></svg>
                    </li>
                    <li class="flex items-center space-x-2"><span>Settings</span><svg xmlns="http://www.w3.org/2000/svg" class="size-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/></svg></li>
                    <li>General</li>
                </ul>
            </div>
        </div>

        <form method="POST" action="{{ route('tickets/settings-general-update') }}">
            @csrf
            @method('PUT')

            {{-- Ticket Number --}}
            <div class="card mt-3 p-4 sm:p-5">
                <h3 class="text-base font-medium text-slate-700 dark:text-navy-100">Ticket Number</h3>
                <p class="mt-0.5 text-sm text-slate-400 dark:text-navy-300">Configure how ticket numbers are generated.</p>
                <div class="mt-4 max-w-md">
                    <label class="block">
                        <span class="font-medium text-slate-600 dark:text-navy-100">Number Prefix</span>
                        <input class="form-input mt-1.5 w-full rounded-lg border border-slate-300 bg-transparent px-3 py-2 placeholder:text-slate-400/70 hover:border-slate-400 focus:border-primary dark:border-navy-450 dark:hover:border-navy-400 dark:focus:border-accent" type="text" name="ticket_number_prefix" value="{{ $settings['ticket_number_prefix'] ?? 'TKT-' }}" placeholder="TKT-">
                        <span class="mt-1 text-xs text-slate-400">e.g. TKT-, SUP-, HELP-</span>
                    </label>
                </div>
            </div>

            {{-- Defaults --}}
            <div class="card mt-4 p-4 sm:p-5">
                <h3 class="text-base font-medium text-slate-700 dark:text-navy-100">Default Values</h3>
                <p class="mt-0.5 text-sm text-slate-400 dark:text-navy-300">Set default status and priority for new tickets.</p>
                <div class="mt-4 grid grid-cols-1 gap-4 sm:grid-cols-2 max-w-2xl">
                    <label class="block">
                        <span class="font-medium text-slate-600 dark:text-navy-100">Default Status</span>
                        <input class="form-input mt-1.5 w-full rounded-lg border border-slate-300 bg-transparent px-3 py-2 placeholder:text-slate-400/70 hover:border-slate-400 focus:border-primary dark:border-navy-450 dark:hover:border-navy-400 dark:focus:border-accent" type="text" name="default_status" value="{{ $settings['default_status'] ?? 'open' }}" placeholder="open">
                        <span class="mt-1 text-xs text-slate-400">Status code applied to new tickets</span>
                    </label>
                    <label class="block">
                        <span class="font-medium text-slate-600 dark:text-navy-100">Default Priority</span>
                        <input class="form-input mt-1.5 w-full rounded-lg border border-slate-300 bg-transparent px-3 py-2 placeholder:text-slate-400/70 hover:border-slate-400 focus:border-primary dark:border-navy-450 dark:hover:border-navy-400 dark:focus:border-accent" type="text" name="default_priority" value="{{ $settings['default_priority'] ?? 'medium' }}" placeholder="medium">
                        <span class="mt-1 text-xs text-slate-400">Priority code applied to new tickets</span>
                    </label>
                </div>
            </div>

            {{-- Automation --}}
            <div class="card mt-4 p-4 sm:p-5">
                <h3 class="text-base font-medium text-slate-700 dark:text-navy-100">Automation</h3>
                <p class="mt-0.5 text-sm text-slate-400 dark:text-navy-300">Configure automatic ticket handling behaviors.</p>
                <div class="mt-4 space-y-4 max-w-2xl">
                    <div class="flex items-center justify-between">
                        <div>
                            <p class="font-medium text-slate-600 dark:text-navy-100">Auto-assign Tickets</p>
                            <p class="text-xs text-slate-400 dark:text-navy-300">Automatically assign new tickets to available agents.</p>
                        </div>
                        <input class="form-switch h-5 w-10 rounded-full bg-slate-300 before:rounded-full before:bg-white checked:bg-primary checked:before:bg-white dark:bg-navy-900 dark:before:bg-navy-300 dark:checked:bg-accent dark:checked:before:bg-white" type="checkbox" name="auto_assign" value="1" {{ ($settings['auto_assign'] ?? '0') === '1' ? 'checked' : '' }}>
                    </div>
                    <div class="h-px bg-slate-200 dark:bg-navy-500"></div>
                    <div class="flex items-center justify-between">
                        <div>
                            <p class="font-medium text-slate-600 dark:text-navy-100">Require Category</p>
                            <p class="text-xs text-slate-400 dark:text-navy-300">Require a category when creating a ticket.</p>
                        </div>
                        <input class="form-switch h-5 w-10 rounded-full bg-slate-300 before:rounded-full before:bg-white checked:bg-primary checked:before:bg-white dark:bg-navy-900 dark:before:bg-navy-300 dark:checked:bg-accent dark:checked:before:bg-white" type="checkbox" name="require_category" value="1" {{ ($settings['require_category'] ?? '0') === '1' ? 'checked' : '' }}>
                    </div>
                    <div class="h-px bg-slate-200 dark:bg-navy-500"></div>
                    <label class="block max-w-xs">
                        <span class="font-medium text-slate-600 dark:text-navy-100">Auto-close After (days)</span>
                        <input class="form-input mt-1.5 w-full rounded-lg border border-slate-300 bg-transparent px-3 py-2 hover:border-slate-400 focus:border-primary dark:border-navy-450 dark:hover:border-navy-400 dark:focus:border-accent" type="number" name="auto_close_days" value="{{ $settings['auto_close_days'] ?? '0' }}" min="0">
                        <span class="mt-1 text-xs text-slate-400">0 = disabled. Auto-close resolved tickets after X days.</span>
                    </label>
                </div>
            </div>

            {{-- Customer Permissions --}}
            <div class="card mt-4 p-4 sm:p-5">
                <h3 class="text-base font-medium text-slate-700 dark:text-navy-100">Customer Permissions</h3>
                <p class="mt-0.5 text-sm text-slate-400 dark:text-navy-300">Control what customers can do with their tickets.</p>
                <div class="mt-4 space-y-4 max-w-2xl">
                    <div class="flex items-center justify-between">
                        <div>
                            <p class="font-medium text-slate-600 dark:text-navy-100">Allow Customers to Close Tickets</p>
                            <p class="text-xs text-slate-400 dark:text-navy-300">Customers can close their own tickets.</p>
                        </div>
                        <input class="form-switch h-5 w-10 rounded-full bg-slate-300 before:rounded-full before:bg-white checked:bg-primary checked:before:bg-white dark:bg-navy-900 dark:before:bg-navy-300 dark:checked:bg-accent dark:checked:before:bg-white" type="checkbox" name="allow_customer_close" value="1" {{ ($settings['allow_customer_close'] ?? '0') === '1' ? 'checked' : '' }}>
                    </div>
                    <div class="h-px bg-slate-200 dark:bg-navy-500"></div>
                    <div class="flex items-center justify-between">
                        <div>
                            <p class="font-medium text-slate-600 dark:text-navy-100">Allow Customers to Reopen Tickets</p>
                            <p class="text-xs text-slate-400 dark:text-navy-300">Customers can reopen closed tickets.</p>
                        </div>
                        <input class="form-switch h-5 w-10 rounded-full bg-slate-300 before:rounded-full before:bg-white checked:bg-primary checked:before:bg-white dark:bg-navy-900 dark:before:bg-navy-300 dark:checked:bg-accent dark:checked:before:bg-white" type="checkbox" name="allow_customer_reopen" value="1" {{ ($settings['allow_customer_reopen'] ?? '0') === '1' ? 'checked' : '' }}>
                    </div>
                    <div class="h-px bg-slate-200 dark:bg-navy-500"></div>
                    <div class="flex items-center justify-between">
                        <div>
                            <p class="font-medium text-slate-600 dark:text-navy-100">Enable Customer Portal</p>
                            <p class="text-xs text-slate-400 dark:text-navy-300">Allow customers to view and manage tickets via a portal.</p>
                        </div>
                        <input class="form-switch h-5 w-10 rounded-full bg-slate-300 before:rounded-full before:bg-white checked:bg-primary checked:before:bg-white dark:bg-navy-900 dark:before:bg-navy-300 dark:checked:bg-accent dark:checked:before:bg-white" type="checkbox" name="enable_customer_portal" value="1" {{ ($settings['enable_customer_portal'] ?? '0') === '1' ? 'checked' : '' }}>
                    </div>
                    <div class="h-px bg-slate-200 dark:bg-navy-500"></div>
                    <div class="flex items-center justify-between">
                        <div>
                            <p class="font-medium text-slate-600 dark:text-navy-100">Enable Satisfaction Survey</p>
                            <p class="text-xs text-slate-400 dark:text-navy-300">Send satisfaction survey after ticket resolution.</p>
                        </div>
                        <input class="form-switch h-5 w-10 rounded-full bg-slate-300 before:rounded-full before:bg-white checked:bg-primary checked:before:bg-white dark:bg-navy-900 dark:before:bg-navy-300 dark:checked:bg-accent dark:checked:before:bg-white" type="checkbox" name="enable_satisfaction_survey" value="1" {{ ($settings['enable_satisfaction_survey'] ?? '0') === '1' ? 'checked' : '' }}>
                    </div>
                </div>
            </div>

            {{-- Attachments --}}
            <div class="card mt-4 p-4 sm:p-5">
                <h3 class="text-base font-medium text-slate-700 dark:text-navy-100">Attachments</h3>
                <p class="mt-0.5 text-sm text-slate-400 dark:text-navy-300">Configure file attachment limits.</p>
                <div class="mt-4 grid grid-cols-1 gap-4 sm:grid-cols-2 max-w-2xl">
                    <label class="block">
                        <span class="font-medium text-slate-600 dark:text-navy-100">Max Attachments per Message</span>
                        <input class="form-input mt-1.5 w-full rounded-lg border border-slate-300 bg-transparent px-3 py-2 hover:border-slate-400 focus:border-primary dark:border-navy-450 dark:hover:border-navy-400 dark:focus:border-accent" type="number" name="max_attachments" value="{{ $settings['max_attachments'] ?? '5' }}" min="0" max="20">
                    </label>
                    <label class="block">
                        <span class="font-medium text-slate-600 dark:text-navy-100">Max File Size (MB)</span>
                        <input class="form-input mt-1.5 w-full rounded-lg border border-slate-300 bg-transparent px-3 py-2 hover:border-slate-400 focus:border-primary dark:border-navy-450 dark:hover:border-navy-400 dark:focus:border-accent" type="number" name="max_attachment_size_mb" value="{{ $settings['max_attachment_size_mb'] ?? '10' }}" min="1" max="50">
                    </label>
                </div>
                <div class="mt-4">
                    <label class="inline-flex items-center space-x-2">
                        <input class="form-switch h-5 w-10 rounded-full bg-slate-300 before:rounded-full before:bg-white checked:bg-primary checked:before:bg-white dark:bg-navy-900 dark:before:bg-navy-300 dark:checked:bg-accent dark:checked:before:bg-white" type="checkbox" name="allow_customer_attachments" value="1" {{ ($settings['allow_customer_attachments'] ?? '0') === '1' ? 'checked' : '' }}>
                        <span class="font-medium text-slate-600 dark:text-navy-100">Allow customers to upload attachments</span>
                    </label>
                    <p class="mt-1 text-xs text-slate-400 dark:text-navy-300">When disabled, only agents can attach files to messages.</p>
                </div>
            </div>

            {{-- SLA --}}
            <div class="card mt-4 p-4 sm:p-5">
                <h3 class="text-base font-medium text-slate-700 dark:text-navy-100">SLA (Service Level Agreement)</h3>
                <p class="mt-0.5 text-sm text-slate-400 dark:text-navy-300">Define response and resolution time targets.</p>
                <div class="mt-4 grid grid-cols-1 gap-4 sm:grid-cols-2 max-w-2xl">
                    <label class="block">
                        <span class="font-medium text-slate-600 dark:text-navy-100">Target Response Time (hours)</span>
                        <input class="form-input mt-1.5 w-full rounded-lg border border-slate-300 bg-transparent px-3 py-2 hover:border-slate-400 focus:border-primary dark:border-navy-450 dark:hover:border-navy-400 dark:focus:border-accent" type="number" name="sla_response_hours" value="{{ $settings['sla_response_hours'] ?? '4' }}" min="0">
                        <span class="mt-1 text-xs text-slate-400">0 = no SLA on first response</span>
                    </label>
                    <label class="block">
                        <span class="font-medium text-slate-600 dark:text-navy-100">Target Resolution Time (hours)</span>
                        <input class="form-input mt-1.5 w-full rounded-lg border border-slate-300 bg-transparent px-3 py-2 hover:border-slate-400 focus:border-primary dark:border-navy-450 dark:hover:border-navy-400 dark:focus:border-accent" type="number" name="sla_resolution_hours" value="{{ $settings['sla_resolution_hours'] ?? '24' }}" min="0">
                        <span class="mt-1 text-xs text-slate-400">0 = no SLA on resolution</span>
                    </label>
                </div>
            </div>

            {{-- Save Button --}}
            <div class="mt-6 flex justify-end">
                <button type="submit" class="btn space-x-2 bg-primary font-medium text-white shadow-lg shadow-primary/50 hover:bg-primary-focus focus:bg-primary-focus active:bg-primary-focus/90 dark:bg-accent dark:shadow-accent/50 dark:hover:bg-accent-focus dark:focus:bg-accent-focus dark:active:bg-accent/90">
                    <svg xmlns="http://www.w3.org/2000/svg" class="size-5" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/></svg>
                    <span>Save Settings</span>
                </button>
            </div>
        </form>
    </main>

</x-app-layout>
