<x-app-layout title="Create User" is-sidebar-open="true" is-header-blur="true">
    <main class="main-content w-full px-[var(--margin-x)] pb-8">
        <div class="flex items-center space-x-4 py-5 lg:py-6">
            <h2 class="text-xl font-medium text-slate-800 dark:text-navy-50 lg:text-2xl">
                Create User
            </h2>
            <div class="hidden h-full py-1 sm:flex">
                <div class="h-full w-px bg-slate-300 dark:bg-navy-600"></div>
            </div>
            <ul class="hidden flex-wrap items-center space-x-2 sm:flex">
                <li class="flex items-center space-x-2">
                    <a class="text-primary transition-colors hover:text-primary-focus dark:text-accent-light dark:hover:text-accent"
                        href="{{ route('users/index') }}">Users</a>
                    <svg x-ignore xmlns="http://www.w3.org/2000/svg" class="size-4" fill="none" viewBox="0 0 24 24"
                        stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7" />
                    </svg>
                </li>
                <li>Create User</li>
            </ul>
        </div>

        <div class="grid grid-cols-1 gap-4 sm:gap-5 lg:gap-6">
            <div>
                <div class="card">
                    <div class="border-b border-slate-200 p-4 dark:border-navy-500 sm:px-5">
                        <h2 class="text-lg font-medium tracking-wide text-slate-700 dark:text-navy-100">
                            User Information
                        </h2>
                    </div>
                    <form method="POST" action="{{ route('users/store') }}" class="space-y-4 p-4 sm:p-5"
                        x-data="{ role: '{{ old('role', 'customer') }}' }">
                        @csrf

                        <label class="block">
                            <span class="font-medium text-slate-600 dark:text-navy-100">Full Name</span>
                            <input
                                class="form-input mt-1.5 w-full rounded-lg border border-slate-300 bg-transparent px-3 py-2 placeholder:text-slate-400/70 hover:border-slate-400 focus:border-primary dark:border-navy-450 dark:hover:border-navy-400 dark:focus:border-accent @error('name') border-error @enderror"
                                placeholder="Enter full name"
                                type="text"
                                name="name"
                                value="{{ old('name') }}"
                            />
                            @error('name')
                                <span class="text-tiny-plus text-error">{{ $message }}</span>
                            @enderror
                        </label>

                        <label class="block">
                            <span class="font-medium text-slate-600 dark:text-navy-100">Email</span>
                            <input
                                class="form-input mt-1.5 w-full rounded-lg border border-slate-300 bg-transparent px-3 py-2 placeholder:text-slate-400/70 hover:border-slate-400 focus:border-primary dark:border-navy-450 dark:hover:border-navy-400 dark:focus:border-accent @error('email') border-error @enderror"
                                placeholder="Enter email address"
                                type="email"
                                name="email"
                                value="{{ old('email') }}"
                            />
                            @error('email')
                                <span class="text-tiny-plus text-error">{{ $message }}</span>
                            @enderror
                        </label>

                        <label class="block">
                            <span class="font-medium text-slate-600 dark:text-navy-100">Password</span>
                            <input
                                class="form-input mt-1.5 w-full rounded-lg border border-slate-300 bg-transparent px-3 py-2 placeholder:text-slate-400/70 hover:border-slate-400 focus:border-primary dark:border-navy-450 dark:hover:border-navy-400 dark:focus:border-accent @error('password') border-error @enderror"
                                placeholder="Enter password (min 7 characters)"
                                type="password"
                                name="password"
                            />
                            @error('password')
                                <span class="text-tiny-plus text-error">{{ $message }}</span>
                            @enderror
                        </label>

                        <label class="block">
                            <span class="font-medium text-slate-600 dark:text-navy-100">Phone</span>
                            <input
                                class="form-input mt-1.5 w-full rounded-lg border border-slate-300 bg-transparent px-3 py-2 placeholder:text-slate-400/70 hover:border-slate-400 focus:border-primary dark:border-navy-450 dark:hover:border-navy-400 dark:focus:border-accent @error('phone') border-error @enderror"
                                placeholder="Enter phone number"
                                type="text"
                                name="phone"
                                value="{{ old('phone') }}"
                            />
                            @error('phone')
                                <span class="text-tiny-plus text-error">{{ $message }}</span>
                            @enderror
                        </label>

                        <div class="grid grid-cols-1 gap-4 sm:grid-cols-2">
                            <label class="block">
                                <span class="font-medium text-slate-600 dark:text-navy-100">Role</span>
                                <select name="role" x-model="role"
                                    class="form-select mt-1.5 w-full rounded-lg border border-slate-300 bg-white px-3 py-2 hover:border-slate-400 focus:border-primary dark:border-navy-450 dark:bg-navy-700 dark:hover:border-navy-400 dark:focus:border-accent @error('role') border-error @enderror">
                                    <option value="customer">Customer</option>
                                    <option value="agent">Agent</option>
                                    <option value="admin">Admin</option>
                                </select>
                                @error('role')
                                    <span class="text-tiny-plus text-error">{{ $message }}</span>
                                @enderror
                            </label>

                            <label class="block">
                                <span class="font-medium text-slate-600 dark:text-navy-100">Status</span>
                                <select name="status"
                                    class="form-select mt-1.5 w-full rounded-lg border border-slate-300 bg-white px-3 py-2 hover:border-slate-400 focus:border-primary dark:border-navy-450 dark:bg-navy-700 dark:hover:border-navy-400 dark:focus:border-accent @error('status') border-error @enderror">
                                    <option value="1" {{ old('status', '1') === '1' ? 'selected' : '' }}>Active</option>
                                    <option value="0" {{ old('status') === '0' ? 'selected' : '' }}>Inactive</option>
                                </select>
                                @error('status')
                                    <span class="text-tiny-plus text-error">{{ $message }}</span>
                                @enderror
                            </label>
                        </div>

                        <label class="block">
                            <span class="font-medium text-slate-600 dark:text-navy-100">Branch / Campus</span>
                            <select name="branch_id"
                                class="form-select mt-1.5 w-full rounded-lg border border-slate-300 bg-white px-3 py-2 hover:border-slate-400 focus:border-primary dark:border-navy-450 dark:bg-navy-700 dark:hover:border-navy-400 dark:focus:border-accent">
                                <option value="">— No Branch —</option>
                                @foreach($branches as $branch)
                                    <option value="{{ $branch->id }}" {{ old('branch_id') == $branch->id ? 'selected' : '' }}>{{ $branch->name }} {{ $branch->code ? '('.$branch->code.')' : '' }}</option>
                                @endforeach
                            </select>
                        </label>

                        {{-- Customer-specific fields --}}
                        <template x-if="role === 'customer'">
                            <div class="space-y-4 rounded-lg border border-slate-200 p-4 dark:border-navy-500">
                                <h3 class="text-base font-medium text-slate-700 dark:text-navy-100">Customer Details</h3>
                                <label class="block">
                                    <span class="font-medium text-slate-600 dark:text-navy-100">Company</span>
                                    <input class="form-input mt-1.5 w-full rounded-lg border border-slate-300 bg-transparent px-3 py-2 placeholder:text-slate-400/70 hover:border-slate-400 focus:border-primary dark:border-navy-450 dark:hover:border-navy-400 dark:focus:border-accent"
                                        placeholder="Company name" type="text" name="company" value="{{ old('company') }}" />
                                </label>
                                <div class="grid grid-cols-1 gap-4 sm:grid-cols-2">
                                    <label class="block">
                                        <span class="font-medium text-slate-600 dark:text-navy-100">Country</span>
                                        <input class="form-input mt-1.5 w-full rounded-lg border border-slate-300 bg-transparent px-3 py-2 placeholder:text-slate-400/70 hover:border-slate-400 focus:border-primary dark:border-navy-450 dark:hover:border-navy-400 dark:focus:border-accent"
                                            placeholder="Country" type="text" name="country" value="{{ old('country') }}" />
                                    </label>
                                    <label class="block">
                                        <span class="font-medium text-slate-600 dark:text-navy-100">City</span>
                                        <input class="form-input mt-1.5 w-full rounded-lg border border-slate-300 bg-transparent px-3 py-2 placeholder:text-slate-400/70 hover:border-slate-400 focus:border-primary dark:border-navy-450 dark:hover:border-navy-400 dark:focus:border-accent"
                                            placeholder="City" type="text" name="city" value="{{ old('city') }}" />
                                    </label>
                                </div>
                                <label class="block">
                                    <span class="font-medium text-slate-600 dark:text-navy-100">Notes</span>
                                    <textarea name="customer_notes" rows="2"
                                        class="form-textarea mt-1.5 w-full rounded-lg border border-slate-300 bg-transparent px-3 py-2 placeholder:text-slate-400/70 hover:border-slate-400 focus:border-primary dark:border-navy-450 dark:hover:border-navy-400 dark:focus:border-accent"
                                        placeholder="Additional notes about this customer...">{{ old('customer_notes') }}</textarea>
                                </label>
                            </div>
                        </template>

                        {{-- Agent-specific fields --}}
                        <template x-if="role === 'agent'">
                            <div class="space-y-4 rounded-lg border border-slate-200 p-4 dark:border-navy-500">
                                <h3 class="text-base font-medium text-slate-700 dark:text-navy-100">Agent Details</h3>
                                <div class="grid grid-cols-1 gap-4 sm:grid-cols-2">
                                    <label class="block">
                                        <span class="font-medium text-slate-600 dark:text-navy-100">Display Name</span>
                                        <input class="form-input mt-1.5 w-full rounded-lg border border-slate-300 bg-transparent px-3 py-2 placeholder:text-slate-400/70 hover:border-slate-400 focus:border-primary dark:border-navy-450 dark:hover:border-navy-400 dark:focus:border-accent"
                                            placeholder="Agent display name" type="text" name="display_name" value="{{ old('display_name') }}" />
                                    </label>
                                    <label class="block">
                                        <span class="font-medium text-slate-600 dark:text-navy-100">Specialization</span>
                                        <input class="form-input mt-1.5 w-full rounded-lg border border-slate-300 bg-transparent px-3 py-2 placeholder:text-slate-400/70 hover:border-slate-400 focus:border-primary dark:border-navy-450 dark:hover:border-navy-400 dark:focus:border-accent"
                                            placeholder="e.g. Technical Support, Billing" type="text" name="specialization" value="{{ old('specialization') }}" />
                                    </label>
                                </div>
                                <label class="block">
                                    <span class="font-medium text-slate-600 dark:text-navy-100">Max Tickets</span>
                                    <input class="form-input mt-1.5 w-full rounded-lg border border-slate-300 bg-transparent px-3 py-2 placeholder:text-slate-400/70 hover:border-slate-400 focus:border-primary dark:border-navy-450 dark:hover:border-navy-400 dark:focus:border-accent"
                                        placeholder="Maximum concurrent tickets" type="number" min="1" max="999" name="max_tickets" value="{{ old('max_tickets') }}" />
                                </label>
                                <div>
                                    <span class="font-medium text-slate-600 dark:text-navy-100">Departments</span>
                                    <div class="mt-2 grid grid-cols-1 gap-2 sm:grid-cols-2 lg:grid-cols-3">
                                        @foreach($departments as $dept)
                                            <label class="inline-flex items-center space-x-2">
                                                <input type="checkbox" name="departments[]" value="{{ $dept->id }}"
                                                    {{ in_array($dept->id, old('departments', [])) ? 'checked' : '' }}
                                                    class="form-checkbox is-outline size-5 rounded-full border-slate-400/70 before:bg-primary checked:border-primary hover:border-primary focus:border-primary dark:border-navy-400 dark:before:bg-accent dark:checked:border-accent dark:hover:border-accent dark:focus:border-accent" />
                                                <span class="text-sm text-slate-600 dark:text-navy-200">{{ $dept->name }}@if(!$dept->status) <span class="text-xs text-slate-400">(Inactive)</span>@endif</span>
                                            </label>
                                        @endforeach
                                    </div>
                                    @error('departments')
                                        <span class="text-tiny-plus text-error">{{ $message }}</span>
                                    @enderror
                                </div>
                            </div>
                        </template>

                        <div class="flex justify-end space-x-2 pt-4">
                            <a href="{{ route('users/index') }}"
                                class="btn space-x-2 border border-slate-300 font-medium text-slate-800 hover:bg-slate-150 focus:bg-slate-150 active:bg-slate-150/80 dark:border-navy-450 dark:text-navy-50 dark:hover:bg-navy-500 dark:focus:bg-navy-500 dark:active:bg-navy-500/90">
                                <span>Cancel</span>
                            </a>
                            <button type="submit"
                                class="btn space-x-2 bg-primary font-medium text-white shadow-lg shadow-primary/50 hover:bg-primary-focus focus:bg-primary-focus active:bg-primary-focus/90 dark:bg-accent dark:shadow-accent/50 dark:hover:bg-accent-focus dark:focus:bg-accent-focus dark:active:bg-accent/90">
                                <span>Create User</span>
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </main>
</x-app-layout>
