<x-app-layout title="Create Ticket" is-sidebar-open="true" is-header-blur="true">

    <main class="main-content w-full px-[var(--margin-x)] pb-8">
        {{-- Page Header --}}
        <div class="flex items-center space-x-4 py-5 lg:py-6">
            <h2 class="text-xl font-medium text-slate-800 dark:text-navy-50 lg:text-2xl">Create Ticket</h2>
            <div class="hidden h-full py-1 sm:flex">
                <div class="h-full w-px bg-slate-300 dark:bg-navy-600"></div>
            </div>
            <ul class="hidden flex-wrap items-center space-x-2 sm:flex">
                <li class="flex items-center space-x-2">
                    <a class="text-primary transition-colors hover:text-primary-focus dark:text-accent-light dark:hover:text-accent" href="{{ route('agent.tickets/dashboard') }}">Tickets</a>
                    <svg xmlns="http://www.w3.org/2000/svg" class="size-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/></svg>
                </li>
                <li>Create</li>
            </ul>
        </div>

        <div class="grid grid-cols-1 gap-4 sm:gap-5 lg:grid-cols-3 lg:gap-6">
            {{-- Main Form --}}
            <div class="lg:col-span-2">
                <div class="card">
                    <div class="border-b border-slate-200 p-4 dark:border-navy-500 sm:px-5">
                        <h2 class="text-lg font-medium tracking-wide text-slate-700 dark:text-navy-100">Ticket Details</h2>
                    </div>
                    <form id="create-ticket" method="POST" action="{{ route('agent.tickets/store') }}" enctype="multipart/form-data" class="space-y-4 p-4 sm:p-5">
                        @csrf

                        {{-- Subject --}}
                        <label class="block">
                            <span class="font-medium text-slate-600 dark:text-navy-100">Subject <span class="text-error">*</span></span>
                            <input class="form-input mt-1.5 w-full rounded-lg border border-slate-300 bg-transparent px-3 py-2 placeholder:text-slate-400/70 hover:border-slate-400 focus:border-primary dark:border-navy-450 dark:hover:border-navy-400 dark:focus:border-accent @error('subject') border-error @enderror"
                                   placeholder="Brief description of the issue" type="text" name="subject" value="{{ old('subject') }}" required>
                            @error('subject') <span class="text-tiny-plus text-error">{{ $message }}</span> @enderror
                        </label>

                        {{-- Description --}}
                        <label class="block">
                            <span class="font-medium text-slate-600 dark:text-navy-100">Description</span>
                            <textarea class="form-textarea mt-1.5 w-full rounded-lg border border-slate-300 bg-transparent px-3 py-2 placeholder:text-slate-400/70 hover:border-slate-400 focus:border-primary dark:border-navy-450 dark:hover:border-navy-400 dark:focus:border-accent @error('description') border-error @enderror"
                                      rows="6" name="description" placeholder="Detailed description of the issue...">{{ old('description') }}</textarea>
                            @error('description') <span class="text-tiny-plus text-error">{{ $message }}</span> @enderror
                        </label>

                        {{-- Customer (Searchable AJAX Select) --}}
                        <div class="block" x-data="searchableSelect({
                            searchUrl: '{{ route('agent.tickets/search-customers') }}',
                            name: 'customer_id',
                            placeholder: 'Search customer by name, email, phone...',
                            oldValue: '{{ old('customer_id') }}',
                        })">
                            <span class="font-medium text-slate-600 dark:text-navy-100">Customer</span>
                            <div class="relative mt-1.5">
                                <input type="hidden" :name="name" :value="selectedId">
                                <div class="relative">
                                    <input type="text"
                                           x-model="query"
                                           @input.debounce.300ms="search()"
                                           @focus="open = true; if(query.length >= 1) search()"
                                           @click.away="open = false"
                                           @keydown.escape="open = false"
                                           @keydown.arrow-down.prevent="highlightNext()"
                                           @keydown.arrow-up.prevent="highlightPrev()"
                                           @keydown.enter.prevent="selectHighlighted()"
                                           :placeholder="selectedText || placeholder"
                                           :class="selectedId ? 'pr-8' : ''"
                                           class="form-input w-full rounded-lg border border-slate-300 bg-transparent px-3 py-2 placeholder:text-slate-400/70 hover:border-slate-400 focus:border-primary dark:border-navy-450 dark:hover:border-navy-400 dark:focus:border-accent @error('customer_id') border-error @enderror"
                                           autocomplete="off">
                                    <button type="button" x-show="selectedId" @click="clear()" class="absolute inset-y-0 right-0 flex items-center pr-3 text-slate-400 hover:text-slate-600 dark:hover:text-navy-200">
                                        <svg class="size-4" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg>
                                    </button>
                                </div>
                                <div x-show="selectedId && !open" class="mt-1">
                                    <span class="inline-flex items-center rounded-full bg-primary/10 px-2.5 py-0.5 text-xs font-medium text-primary dark:bg-accent/10 dark:text-accent-light" x-text="selectedText"></span>
                                </div>
                                <div x-show="open" x-transition class="absolute z-50 mt-1 max-h-60 w-full overflow-auto rounded-lg border border-slate-200 bg-white shadow-lg dark:border-navy-500 dark:bg-navy-700">
                                    <div x-show="loading" class="flex items-center justify-center p-3">
                                        <div class="spinner size-5 animate-spin rounded-full border-[3px] border-primary/30 border-r-primary dark:border-accent/30 dark:border-r-accent"></div>
                                        <span class="ml-2 text-xs text-slate-500">Searching...</span>
                                    </div>
                                    <template x-for="(item, index) in results" :key="item.id">
                                        <div @click="select(item)" @mouseenter="highlighted = index"
                                             :class="highlighted === index ? 'bg-primary/10 dark:bg-accent/10' : ''"
                                             class="cursor-pointer px-3 py-2 text-sm text-slate-700 hover:bg-slate-100 dark:text-navy-100 dark:hover:bg-navy-600">
                                            <span x-text="item.text"></span>
                                        </div>
                                    </template>
                                    <div x-show="!loading && results.length === 0 && query.length >= 1" class="p-3 text-center">
                                        <p class="text-xs text-slate-500 dark:text-navy-300">No customers found.</p>
                                        <button type="button" @click="showCreateCustomer = true; open = false"
                                                class="mt-2 btn text-xs bg-primary font-medium text-white hover:bg-primary-focus dark:bg-accent dark:hover:bg-accent-focus">
                                            <svg class="mr-1 size-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/></svg>
                                            Create New Customer
                                        </button>
                                    </div>
                                </div>
                            </div>
                            @error('customer_id') <span class="text-tiny-plus text-error">{{ $message }}</span> @enderror

                            {{-- Full Create Customer Modal --}}
                            <template x-teleport="body">
                                <div x-show="showCreateCustomer" x-transition.opacity
                                     class="fixed inset-0 z-[100] flex items-start justify-center overflow-y-auto bg-slate-900/60 p-4 py-8"
                                     @keydown.escape.window="showCreateCustomer = false">
                                    <div x-show="showCreateCustomer" x-transition
                                         @click.away="showCreateCustomer = false"
                                         class="relative w-full max-w-2xl rounded-xl bg-white shadow-xl dark:bg-navy-700">

                                        {{-- Modal Header --}}
                                        <div class="flex items-center justify-between border-b border-slate-200 px-6 py-4 dark:border-navy-500">
                                            <div class="flex items-center gap-3">
                                                <div class="flex size-9 items-center justify-center rounded-full bg-primary/10">
                                                    <svg class="size-5 text-primary" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M18 9v3m0 0v3m0-3h3m-3 0h-3m-2-5a4 4 0 11-8 0 4 4 0 018 0zM3 20a6 6 0 0112 0v1H3v-1z"/></svg>
                                                </div>
                                                <h3 class="text-base font-semibold text-slate-700 dark:text-navy-100">Create New Customer</h3>
                                            </div>
                                            <button type="button" @click="showCreateCustomer = false" class="text-slate-400 hover:text-slate-600 dark:hover:text-navy-200">
                                                <svg class="size-5" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg>
                                            </button>
                                        </div>

                                        {{-- Modal Body --}}
                                        <div class="space-y-4 px-6 py-5">

                                            {{-- Name --}}
                                            <label class="block">
                                                <span class="text-sm font-medium text-slate-600 dark:text-navy-100">Full Name <span class="text-error">*</span></span>
                                                <input type="text" x-model="newCustomer.name"
                                                       class="form-input mt-1.5 w-full rounded-lg border border-slate-300 bg-transparent px-3 py-2 text-sm placeholder:text-slate-400/70 hover:border-slate-400 focus:border-primary dark:border-navy-450 dark:hover:border-navy-400 dark:focus:border-accent"
                                                       placeholder="Enter full name">
                                            </label>

                                            {{-- Email --}}
                                            <label class="block">
                                                <span class="text-sm font-medium text-slate-600 dark:text-navy-100">Email</span>
                                                <input type="email" x-model="newCustomer.email"
                                                       class="form-input mt-1.5 w-full rounded-lg border border-slate-300 bg-transparent px-3 py-2 text-sm placeholder:text-slate-400/70 hover:border-slate-400 focus:border-primary dark:border-navy-450 dark:hover:border-navy-400 dark:focus:border-accent"
                                                       placeholder="Enter email address">
                                            </label>

                                            {{-- Password --}}
                                            <label class="block">
                                                <span class="text-sm font-medium text-slate-600 dark:text-navy-100">Password <span class="text-slate-400 font-normal text-xs">(leave blank to auto-generate)</span></span>
                                                <input type="password" x-model="newCustomer.password"
                                                       class="form-input mt-1.5 w-full rounded-lg border border-slate-300 bg-transparent px-3 py-2 text-sm placeholder:text-slate-400/70 hover:border-slate-400 focus:border-primary dark:border-navy-450 dark:hover:border-navy-400 dark:focus:border-accent"
                                                       placeholder="Min 7 characters">
                                            </label>

                                            {{-- Phone --}}
                                            <label class="block">
                                                <span class="text-sm font-medium text-slate-600 dark:text-navy-100">Phone</span>
                                                <input type="text" x-model="newCustomer.phone"
                                                       class="form-input mt-1.5 w-full rounded-lg border border-slate-300 bg-transparent px-3 py-2 text-sm placeholder:text-slate-400/70 hover:border-slate-400 focus:border-primary dark:border-navy-450 dark:hover:border-navy-400 dark:focus:border-accent"
                                                       placeholder="Phone number">
                                            </label>

                                            {{-- Role (locked to customer) + Status --}}
                                            <div class="grid grid-cols-2 gap-4">
                                                <label class="block">
                                                    <span class="text-sm font-medium text-slate-600 dark:text-navy-100">Role</span>
                                                    <input type="text" value="Customer" disabled
                                                           class="form-input mt-1.5 w-full rounded-lg border border-slate-200 bg-slate-50 px-3 py-2 text-sm text-slate-400 dark:border-navy-500 dark:bg-navy-600 dark:text-navy-300 cursor-not-allowed">
                                                </label>
                                                <label class="block">
                                                    <span class="text-sm font-medium text-slate-600 dark:text-navy-100">Status</span>
                                                    <select x-model="newCustomer.status"
                                                            class="form-select mt-1.5 w-full rounded-lg border border-slate-300 bg-white px-3 py-2 text-sm hover:border-slate-400 focus:border-primary dark:border-navy-450 dark:bg-navy-700 dark:hover:border-navy-400 dark:focus:border-accent">
                                                        <option value="1">Active</option>
                                                        <option value="0">Inactive</option>
                                                    </select>
                                                </label>
                                            </div>

                                            {{-- Branch --}}
                                            <label class="block">
                                                <span class="text-sm font-medium text-slate-600 dark:text-navy-100">Branch / Campus</span>
                                                <select x-model="newCustomer.branch_id"
                                                        class="form-select mt-1.5 w-full rounded-lg border border-slate-300 bg-white px-3 py-2 text-sm hover:border-slate-400 focus:border-primary dark:border-navy-450 dark:bg-navy-700 dark:hover:border-navy-400 dark:focus:border-accent">
                                                    <option value="">— No Branch —</option>
                                                    @foreach($branches as $branch)
                                                        <option value="{{ $branch->id }}">{{ $branch->name }}{{ $branch->code ? ' ('.$branch->code.')' : '' }}</option>
                                                    @endforeach
                                                </select>
                                            </label>

                                            {{-- Customer Details --}}
                                            <div class="rounded-lg border border-slate-200 p-4 space-y-4 dark:border-navy-500">
                                                <h4 class="text-sm font-medium text-slate-700 dark:text-navy-100">Customer Details</h4>
                                                <label class="block">
                                                    <span class="text-sm font-medium text-slate-600 dark:text-navy-100">Company</span>
                                                    <input type="text" x-model="newCustomer.company"
                                                           class="form-input mt-1.5 w-full rounded-lg border border-slate-300 bg-transparent px-3 py-2 text-sm placeholder:text-slate-400/70 hover:border-slate-400 focus:border-primary dark:border-navy-450 dark:hover:border-navy-400 dark:focus:border-accent"
                                                           placeholder="Company name">
                                                </label>
                                                <div class="grid grid-cols-2 gap-4">
                                                    <label class="block">
                                                        <span class="text-sm font-medium text-slate-600 dark:text-navy-100">Country</span>
                                                        <input type="text" x-model="newCustomer.country"
                                                               class="form-input mt-1.5 w-full rounded-lg border border-slate-300 bg-transparent px-3 py-2 text-sm placeholder:text-slate-400/70 hover:border-slate-400 focus:border-primary dark:border-navy-450 dark:hover:border-navy-400 dark:focus:border-accent"
                                                               placeholder="Country">
                                                    </label>
                                                    <label class="block">
                                                        <span class="text-sm font-medium text-slate-600 dark:text-navy-100">City</span>
                                                        <input type="text" x-model="newCustomer.city"
                                                               class="form-input mt-1.5 w-full rounded-lg border border-slate-300 bg-transparent px-3 py-2 text-sm placeholder:text-slate-400/70 hover:border-slate-400 focus:border-primary dark:border-navy-450 dark:hover:border-navy-400 dark:focus:border-accent"
                                                               placeholder="City">
                                                    </label>
                                                </div>
                                                <label class="block">
                                                    <span class="text-sm font-medium text-slate-600 dark:text-navy-100">Notes</span>
                                                    <textarea x-model="newCustomer.customer_notes" rows="2"
                                                              class="form-textarea mt-1.5 w-full rounded-lg border border-slate-300 bg-transparent px-3 py-2 text-sm placeholder:text-slate-400/70 hover:border-slate-400 focus:border-primary dark:border-navy-450 dark:hover:border-navy-400 dark:focus:border-accent"
                                                              placeholder="Additional notes about this customer..."></textarea>
                                                </label>
                                            </div>

                                            <p x-show="createError" class="rounded-lg bg-error/10 px-3 py-2 text-xs text-error" x-text="createError"></p>
                                        </div>

                                        {{-- Modal Footer --}}
                                        <div class="flex justify-end gap-2 border-t border-slate-200 px-6 py-4 dark:border-navy-500">
                                            <button type="button" @click="showCreateCustomer = false"
                                                    class="btn border border-slate-300 font-medium text-slate-800 hover:bg-slate-150 dark:border-navy-450 dark:text-navy-50 dark:hover:bg-navy-500">
                                                Cancel
                                            </button>
                                            <button type="button" @click="createCustomer('{{ route('agent.tickets/quick-customer') }}')" :disabled="savingCustomer"
                                                    class="btn bg-primary font-medium text-white hover:bg-primary-focus dark:bg-accent dark:hover:bg-accent-focus disabled:opacity-60">
                                                <template x-if="savingCustomer">
                                                    <div class="spinner mr-2 size-4 animate-spin rounded-full border-[3px] border-white/30 border-r-white"></div>
                                                </template>
                                                <span x-text="savingCustomer ? 'Creating...' : 'Create & Select'"></span>
                                            </button>
                                        </div>
                                    </div>
                                </div>
                            </template>
                        </div>

                        {{-- Contact Phone & Email --}}
                        <div class="grid grid-cols-1 gap-4 sm:grid-cols-2">
                            {{-- Phone with +255 prefix --}}
                            <div x-data="{
                                digits: '{{ old('contact_phone') ? preg_replace('/^\+?255/', '', old('contact_phone')) : '' }}',
                                get full() { return '+255' + this.digits; },
                                get valid() { return /^\d{9}$/.test(this.digits); },
                                onInput(e) {
                                    let v = e.target.value.replace(/\D/g, '');
                                    if (v.startsWith('255')) v = v.slice(3);
                                    if (v.startsWith('0')) v = v.slice(1);
                                    this.digits = v.slice(0, 9);
                                }
                            }">
                                <span class="font-medium text-slate-600 dark:text-navy-100">Phone <span class="text-error">*</span></span>
                                <input type="hidden" name="contact_phone" :value="full">
                                <div class="mt-1.5 flex rounded-lg border @error('contact_phone') border-error @else border-slate-300 dark:border-navy-450 @enderror overflow-hidden hover:border-slate-400 focus-within:border-primary dark:hover:border-navy-400 dark:focus-within:border-accent">
                                    <span class="flex items-center bg-slate-100 px-3 text-sm font-medium text-slate-600 dark:bg-navy-600 dark:text-navy-200 shrink-0">+255</span>
                                    <input type="text" inputmode="numeric" x-model="digits" @input="onInput($event)"
                                           placeholder="748859172" maxlength="9"
                                           class="form-input w-full border-0 bg-transparent px-3 py-2 placeholder:text-slate-400/70 focus:ring-0 focus:outline-none"
                                           required>
                                </div>
                                <p x-show="digits.length > 0 && !valid" class="mt-1 text-tiny-plus text-error">Must be exactly 9 digits after +255</p>
                                @error('contact_phone') <span class="text-tiny-plus text-error">{{ $message }}</span> @enderror
                            </div>

                            {{-- Email with domain suggestions --}}
                            <div x-data="{
                                value: '{{ old('contact_email') }}',
                                suggestions: [],
                                domains: ['gmail.com','yahoo.com','outlook.com','hotmail.com','icloud.com','protonmail.com','mail.com','aol.com'],
                                open: false,
                                onInput() {
                                    const at = this.value.indexOf('@');
                                    if (at === -1) { this.suggestions = []; this.open = false; return; }
                                    const prefix = this.value.slice(0, at + 1);
                                    const typed = this.value.slice(at + 1).toLowerCase();
                                    this.suggestions = this.domains
                                        .filter(d => d.startsWith(typed))
                                        .map(d => prefix + d);
                                    this.open = this.suggestions.length > 0;
                                },
                                pick(s) { this.value = s; this.open = false; }
                            }" class="relative">
                                <span class="font-medium text-slate-600 dark:text-navy-100">Email</span>
                                <input type="email" name="contact_email" x-model="value" @input="onInput()" @click.away="open = false"
                                       placeholder="contact@example.com"
                                       class="form-input mt-1.5 w-full rounded-lg border border-slate-300 bg-transparent px-3 py-2 placeholder:text-slate-400/70 hover:border-slate-400 focus:border-primary dark:border-navy-450 dark:hover:border-navy-400 dark:focus:border-accent @error('contact_email') border-error @enderror"
                                       autocomplete="off">
                                <div x-show="open" x-transition class="absolute z-50 mt-1 w-full rounded-lg border border-slate-200 bg-white shadow-lg dark:border-navy-500 dark:bg-navy-700">
                                    <template x-for="s in suggestions" :key="s">
                                        <div @click="pick(s)" class="cursor-pointer px-3 py-2 text-sm text-slate-700 hover:bg-primary/10 dark:text-navy-100 dark:hover:bg-accent/10" x-text="s"></div>
                                    </template>
                                </div>
                                @error('contact_email') <span class="text-tiny-plus text-error">{{ $message }}</span> @enderror
                            </div>
                        </div>

                        <div class="grid grid-cols-1 gap-4 sm:grid-cols-2">
                            {{-- Category --}}
                            <label class="block">
                                <span class="font-medium text-slate-600 dark:text-navy-100">Category</span>
                                <select name="category_id" class="form-select mt-1.5 w-full rounded-lg border border-slate-300 bg-white px-3 py-2 hover:border-slate-400 focus:border-primary dark:border-navy-450 dark:bg-navy-700 dark:hover:border-navy-400 dark:focus:border-accent">
                                    <option value="">Select Category</option>
                                    @foreach($categories as $cat)
                                        <option value="{{ $cat->id }}" @selected(old('category_id') == $cat->id)>{{ $cat->name }}</option>
                                    @endforeach
                                </select>
                            </label>

                            {{-- Source --}}
                            <label class="block">
                                <span class="font-medium text-slate-600 dark:text-navy-100">Source</span>
                                <select name="source" class="form-select mt-1.5 w-full rounded-lg border border-slate-300 bg-white px-3 py-2 hover:border-slate-400 focus:border-primary dark:border-navy-450 dark:bg-navy-700 dark:hover:border-navy-400 dark:focus:border-accent">
                                    <option value="manual" @selected(old('source', 'manual') === 'manual')>Manual</option>
                                    <option value="email" @selected(old('source') === 'email')>Email</option>
                                    <option value="phone" @selected(old('source') === 'phone')>Phone</option>
                                    <option value="web" @selected(old('source') === 'web')>Web Widget</option>
                                    <option value="chat" @selected(old('source') === 'chat')>Chat</option>
                                    <option value="api" @selected(old('source') === 'api')>API</option>
                                </select>
                            </label>
                        </div>

                        {{-- Attachments --}}
                        <label class="block">
                            <span class="font-medium text-slate-600 dark:text-navy-100">Attachments</span>
                            <input class="form-input mt-1.5 w-full rounded-lg border border-slate-300 bg-transparent px-3 py-2 file:mr-4 file:rounded-full file:border-0 file:bg-primary/10 file:px-4 file:py-1 file:text-sm file:font-medium file:text-primary hover:border-slate-400 focus:border-primary dark:border-navy-450 dark:hover:border-navy-400 dark:focus:border-accent"
                                   type="file" name="attachments[]" multiple>
                            <span class="mt-1 text-xs text-slate-400 dark:text-navy-300">Max 10MB per file. Multiple files allowed.</span>
                        </label>

                        {{-- Tags --}}
                        @if($tags->isNotEmpty())
                            <div class="block">
                                <span class="font-medium text-slate-600 dark:text-navy-100">Tags</span>
                                <div class="mt-2 flex flex-wrap gap-2">
                                    @foreach($tags as $tag)
                                        <label class="tag cursor-pointer">
                                            <input type="checkbox" name="tags[]" value="{{ $tag->id }}" class="peer hidden" @checked(is_array(old('tags')) && in_array($tag->id, old('tags')))>
                                            <span class="inline-flex items-center rounded-full border border-slate-300 px-3 py-1 text-xs font-medium text-slate-600 transition-colors peer-checked:border-primary peer-checked:bg-primary/10 peer-checked:text-primary dark:border-navy-450 dark:text-navy-200 dark:peer-checked:border-accent dark:peer-checked:bg-accent/10 dark:peer-checked:text-accent-light"
                                                  style="border-color: {{ $tag->color }}30; color: {{ $tag->color }}">
                                                {{ $tag->name }}
                                            </span>
                                        </label>
                                    @endforeach
                                </div>
                            </div>
                        @endif

                        {{-- Buttons --}}
                        <div class="flex justify-end space-x-2 pt-4">
                            <a href="{{ route('agent.tickets/index') }}" class="btn space-x-2 border border-slate-300 font-medium text-slate-800 hover:bg-slate-150 focus:bg-slate-150 active:bg-slate-150/80 dark:border-navy-450 dark:text-navy-50 dark:hover:bg-navy-500 dark:focus:bg-navy-500 dark:active:bg-navy-500/90">Cancel</a>
                            <button type="submit" class="btn space-x-2 bg-primary font-medium text-white shadow-lg shadow-primary/50 hover:bg-primary-focus focus:bg-primary-focus active:bg-primary-focus/90 dark:bg-accent dark:shadow-accent/50 dark:hover:bg-accent-focus dark:focus:bg-accent-focus dark:active:bg-accent/90">
                                <svg xmlns="http://www.w3.org/2000/svg" class="size-5" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/></svg>
                                <span>Create Ticket</span>
                            </button>
                        </div>
                    </form>
                </div>
            </div>

            {{-- Sidebar - Assignment & Meta --}}
            <div class="space-y-4 sm:space-y-5 lg:space-y-6">
                <div class="card">
                    <div class="border-b border-slate-200 p-4 dark:border-navy-500 sm:px-5">
                        <h2 class="text-lg font-medium tracking-wide text-slate-700 dark:text-navy-100">Assignment</h2>
                    </div>
                    <div class="space-y-4 p-4 sm:p-5">
                        {{-- Status --}}
                        <label class="block">
                            <span class="font-medium text-slate-600 dark:text-navy-100">Status</span>
                            <select name="status_id" form="create-ticket" class="form-select mt-1.5 w-full rounded-lg border border-slate-300 bg-white px-3 py-2 hover:border-slate-400 focus:border-primary dark:border-navy-450 dark:bg-navy-700 dark:hover:border-navy-400 dark:focus:border-accent">
                                @foreach($statuses as $status)
                                    <option value="{{ $status->id }}" @selected($defaultStatus === $status->code)>{{ $status->name }}</option>
                                @endforeach
                            </select>
                        </label>

                        {{-- Priority --}}
                        <label class="block">
                            <span class="font-medium text-slate-600 dark:text-navy-100">Priority</span>
                            <select name="priority_id" form="create-ticket" class="form-select mt-1.5 w-full rounded-lg border border-slate-300 bg-white px-3 py-2 hover:border-slate-400 focus:border-primary dark:border-navy-450 dark:bg-navy-700 dark:hover:border-navy-400 dark:focus:border-accent">
                                @foreach($priorities as $priority)
                                    <option value="{{ $priority->id }}" @selected($defaultPriority === $priority->code)>{{ $priority->name }}</option>
                                @endforeach
                            </select>
                        </label>

                        {{-- Assigned To (Searchable AJAX Select) --}}
                        <div class="block" x-data="searchableSelect({
                            searchUrl: '{{ route('agent.tickets/search-agents') }}',
                            name: 'assigned_to',
                            formId: 'create-ticket',
                            placeholder: 'Search agent by name or email...',
                            oldValue: '{{ old('assigned_to') }}',
                        })">
                            <span class="font-medium text-slate-600 dark:text-navy-100">Assign To</span>
                            <div class="relative mt-1.5">
                                <input type="hidden" :name="name" :value="selectedId" :form="formId">
                                <div class="relative">
                                    <input type="text"
                                           x-model="query"
                                           @input.debounce.300ms="search()"
                                           @focus="open = true; if(query.length >= 1) search()"
                                           @click.away="open = false"
                                           @keydown.escape="open = false"
                                           @keydown.arrow-down.prevent="highlightNext()"
                                           @keydown.arrow-up.prevent="highlightPrev()"
                                           @keydown.enter.prevent="selectHighlighted()"
                                           :placeholder="selectedText || placeholder"
                                           :class="selectedId ? 'pr-8' : ''"
                                           class="form-input w-full rounded-lg border border-slate-300 bg-transparent px-3 py-2 placeholder:text-slate-400/70 hover:border-slate-400 focus:border-primary dark:border-navy-450 dark:hover:border-navy-400 dark:focus:border-accent"
                                           autocomplete="off">
                                    <button type="button" x-show="selectedId" @click="clear()" class="absolute inset-y-0 right-0 flex items-center pr-3 text-slate-400 hover:text-slate-600 dark:hover:text-navy-200">
                                        <svg class="size-4" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg>
                                    </button>
                                </div>
                                <div x-show="selectedId && !open" class="mt-1">
                                    <span class="inline-flex items-center rounded-full bg-primary/10 px-2.5 py-0.5 text-xs font-medium text-primary dark:bg-accent/10 dark:text-accent-light" x-text="selectedText"></span>
                                </div>
                                <div x-show="open" x-transition class="absolute z-50 mt-1 max-h-60 w-full overflow-auto rounded-lg border border-slate-200 bg-white shadow-lg dark:border-navy-500 dark:bg-navy-700">
                                    <div x-show="loading" class="flex items-center justify-center p-3">
                                        <div class="spinner size-5 animate-spin rounded-full border-[3px] border-primary/30 border-r-primary dark:border-accent/30 dark:border-r-accent"></div>
                                        <span class="ml-2 text-xs text-slate-500">Searching...</span>
                                    </div>
                                    <template x-for="(item, index) in results" :key="item.id">
                                        <div @click="select(item)" @mouseenter="highlighted = index"
                                             :class="highlighted === index ? 'bg-primary/10 dark:bg-accent/10' : ''"
                                             class="cursor-pointer px-3 py-2 text-sm text-slate-700 hover:bg-slate-100 dark:text-navy-100 dark:hover:bg-navy-600">
                                            <span x-text="item.text"></span>
                                        </div>
                                    </template>
                                    <div x-show="!loading && results.length === 0 && query.length >= 1" class="p-3 text-center">
                                        <p class="text-xs text-slate-500 dark:text-navy-300">No agents found.</p>
                                    </div>
                                </div>
                            </div>
                        </div>

                        {{-- Due Date --}}
                        <label class="block">
                            <span class="font-medium text-slate-600 dark:text-navy-100">Due Date</span>
                            <input class="form-input mt-1.5 w-full rounded-lg border border-slate-300 bg-transparent px-3 py-2 hover:border-slate-400 focus:border-primary dark:border-navy-450 dark:hover:border-navy-400 dark:focus:border-accent"
                                   type="datetime-local" name="due_at" form="create-ticket" value="{{ old('due_at') }}" min="{{ now()->format('Y-m-d\TH:i') }}">
                        </label>
                    </div>
                </div>

                <div class="card">
                    <div class="border-b border-slate-200 p-4 dark:border-navy-500 sm:px-5">
                        <h2 class="text-lg font-medium tracking-wide text-slate-700 dark:text-navy-100">Organization</h2>
                    </div>
                    <div class="space-y-4 p-4 sm:p-5">
                        {{-- Branch --}}
                        <label class="block">
                            <span class="font-medium text-slate-600 dark:text-navy-100">Branch</span>
                            <select name="branch_id" form="create-ticket" class="form-select mt-1.5 w-full rounded-lg border border-slate-300 bg-white px-3 py-2 hover:border-slate-400 focus:border-primary dark:border-navy-450 dark:bg-navy-700 dark:hover:border-navy-400 dark:focus:border-accent">
                                <option value="">No Branch</option>
                                @foreach($branches as $branch)
                                    <option value="{{ $branch->id }}" @selected(old('branch_id') == $branch->id)>{{ $branch->name }}</option>
                                @endforeach
                            </select>
                        </label>

                        {{-- Department --}}
                        <label class="block">
                            <span class="font-medium text-slate-600 dark:text-navy-100">Department</span>
                            <select name="department_id" form="create-ticket" class="form-select mt-1.5 w-full rounded-lg border border-slate-300 bg-white px-3 py-2 hover:border-slate-400 focus:border-primary dark:border-navy-450 dark:bg-navy-700 dark:hover:border-navy-400 dark:focus:border-accent">
                                <option value="">No Department</option>
                                @foreach($departments as $dept)
                                    <option value="{{ $dept->id }}" @selected(old('department_id') == $dept->id)>{{ $dept->name }}</option>
                                @endforeach
                            </select>
                        </label>
                    </div>
                </div>
            </div>
        </div>
    </main>

</x-app-layout>
