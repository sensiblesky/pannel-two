<x-app-layout title="Edit User" is-sidebar-open="true" is-header-blur="true">
    <main class="main-content w-full px-[var(--margin-x)] pb-8">
        <div class="flex items-center space-x-4 py-5 lg:py-6">
            <h2 class="text-xl font-medium text-slate-800 dark:text-navy-50 lg:text-2xl">
                Edit User
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
                <li>Edit User</li>
            </ul>
        </div>

        <div class="grid grid-cols-1 gap-4 sm:gap-5 lg:gap-6">
            <div>
                <div class="card">
                    <div class="border-b border-slate-200 p-4 dark:border-navy-500 sm:px-5">
                        <h2 class="text-lg font-medium tracking-wide text-slate-700 dark:text-navy-100">
                            Edit User: {{ $user->name }}
                        </h2>
                    </div>
                    <form method="POST" action="{{ route('users/update', $user) }}" class="space-y-4 p-4 sm:p-5"
                        x-data="{ role: '{{ old('role', $user->role) }}' }">
                        @csrf
                        @method('PUT')

                        <label class="block">
                            <span class="font-medium text-slate-600 dark:text-navy-100">Full Name</span>
                            <input
                                class="form-input mt-1.5 w-full rounded-lg border border-slate-300 bg-transparent px-3 py-2 placeholder:text-slate-400/70 hover:border-slate-400 focus:border-primary dark:border-navy-450 dark:hover:border-navy-400 dark:focus:border-accent @error('name') border-error @enderror"
                                placeholder="Enter full name"
                                type="text"
                                name="name"
                                value="{{ old('name', $user->name) }}"
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
                                value="{{ old('email', $user->email) }}"
                            />
                            @error('email')
                                <span class="text-tiny-plus text-error">{{ $message }}</span>
                            @enderror
                        </label>

                        <label class="block">
                            <span class="font-medium text-slate-600 dark:text-navy-100">Password</span>
                            <input
                                class="form-input mt-1.5 w-full rounded-lg border border-slate-300 bg-transparent px-3 py-2 placeholder:text-slate-400/70 hover:border-slate-400 focus:border-primary dark:border-navy-450 dark:hover:border-navy-400 dark:focus:border-accent @error('password') border-error @enderror"
                                placeholder="Leave blank to keep current password"
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
                                value="{{ old('phone', $user->phone) }}"
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
                                    <option value="1" {{ old('status', (string)$user->status) === '1' ? 'selected' : '' }}>Active</option>
                                    <option value="0" {{ old('status', (string)$user->status) === '0' ? 'selected' : '' }}>Inactive</option>
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
                                    <option value="{{ $branch->id }}" {{ old('branch_id', $user->branch_id) == $branch->id ? 'selected' : '' }}>{{ $branch->name }} {{ $branch->code ? '('.$branch->code.')' : '' }}</option>
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
                                        placeholder="Company name" type="text" name="company" value="{{ old('company', $customerInfo->company ?? '') }}" />
                                </label>
                                <div class="grid grid-cols-1 gap-4 sm:grid-cols-2">
                                    <label class="block">
                                        <span class="font-medium text-slate-600 dark:text-navy-100">Country</span>
                                        <input class="form-input mt-1.5 w-full rounded-lg border border-slate-300 bg-transparent px-3 py-2 placeholder:text-slate-400/70 hover:border-slate-400 focus:border-primary dark:border-navy-450 dark:hover:border-navy-400 dark:focus:border-accent"
                                            placeholder="Country" type="text" name="country" value="{{ old('country', $customerInfo->country ?? '') }}" />
                                    </label>
                                    <label class="block">
                                        <span class="font-medium text-slate-600 dark:text-navy-100">City</span>
                                        <input class="form-input mt-1.5 w-full rounded-lg border border-slate-300 bg-transparent px-3 py-2 placeholder:text-slate-400/70 hover:border-slate-400 focus:border-primary dark:border-navy-450 dark:hover:border-navy-400 dark:focus:border-accent"
                                            placeholder="City" type="text" name="city" value="{{ old('city', $customerInfo->city ?? '') }}" />
                                    </label>
                                </div>
                                <label class="block">
                                    <span class="font-medium text-slate-600 dark:text-navy-100">Notes</span>
                                    <textarea name="customer_notes" rows="2"
                                        class="form-textarea mt-1.5 w-full rounded-lg border border-slate-300 bg-transparent px-3 py-2 placeholder:text-slate-400/70 hover:border-slate-400 focus:border-primary dark:border-navy-450 dark:hover:border-navy-400 dark:focus:border-accent"
                                        placeholder="Additional notes about this customer...">{{ old('customer_notes', $customerInfo->notes ?? '') }}</textarea>
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
                                            placeholder="Agent display name" type="text" name="display_name" value="{{ old('display_name', $agentInfo->display_name ?? '') }}" />
                                    </label>
                                    <label class="block">
                                        <span class="font-medium text-slate-600 dark:text-navy-100">Specialization</span>
                                        <input class="form-input mt-1.5 w-full rounded-lg border border-slate-300 bg-transparent px-3 py-2 placeholder:text-slate-400/70 hover:border-slate-400 focus:border-primary dark:border-navy-450 dark:hover:border-navy-400 dark:focus:border-accent"
                                            placeholder="e.g. Technical Support, Billing" type="text" name="specialization" value="{{ old('specialization', $agentInfo->specialization ?? '') }}" />
                                    </label>
                                </div>
                                <label class="block">
                                    <span class="font-medium text-slate-600 dark:text-navy-100">Max Tickets</span>
                                    <input class="form-input mt-1.5 w-full rounded-lg border border-slate-300 bg-transparent px-3 py-2 placeholder:text-slate-400/70 hover:border-slate-400 focus:border-primary dark:border-navy-450 dark:hover:border-navy-400 dark:focus:border-accent"
                                        placeholder="Maximum concurrent tickets" type="number" min="1" max="999" name="max_tickets" value="{{ old('max_tickets', $agentInfo->max_tickets ?? '') }}" />
                                </label>
                                <div>
                                    <span class="font-medium text-slate-600 dark:text-navy-100">Departments</span>
                                    <div class="mt-2 grid grid-cols-1 gap-2 sm:grid-cols-2 lg:grid-cols-3">
                                        @foreach($departments as $dept)
                                            <label class="inline-flex items-center space-x-2">
                                                <input type="checkbox" name="departments[]" value="{{ $dept->id }}"
                                                    {{ in_array($dept->id, old('departments', $agentDepartments ?? [])) ? 'checked' : '' }}
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
                                <span>Update User</span>
                            </button>
                        </div>
                    </form>
                </div>
            </div>

            {{-- Suspension Card --}}
            <div class="card" x-data="{ showSuspendModal: false }">
                <div class="border-b border-slate-200 p-4 dark:border-navy-500 sm:px-5">
                    <div class="flex items-center justify-between">
                        <h2 class="text-lg font-medium tracking-wide text-slate-700 dark:text-navy-100">Account Suspension</h2>
                        @if ($user->is_suspended)
                            <div class="badge rounded-full bg-error/10 text-error dark:bg-error/15">Suspended</div>
                        @else
                            <div class="badge rounded-full bg-success/10 text-success dark:bg-success/15">Active</div>
                        @endif
                    </div>
                </div>

                <div class="p-4 sm:p-5">
                    @if ($user->is_suspended && $user->activeSuspension)
                        {{-- Current suspension info --}}
                        <div class="rounded-lg border border-error/30 bg-error/5 p-4 dark:border-error/20 dark:bg-error/10">
                            <div class="flex items-start space-x-3">
                                <svg xmlns="http://www.w3.org/2000/svg" class="size-5 shrink-0 text-error mt-0.5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M18.364 18.364A9 9 0 005.636 5.636m12.728 12.728A9 9 0 015.636 5.636m12.728 12.728L5.636 5.636" />
                                </svg>
                                <div class="flex-1">
                                    <p class="font-medium text-error">This user is currently suspended</p>
                                    <p class="mt-1 text-sm text-slate-600 dark:text-navy-200">
                                        <span class="font-medium">Reason:</span> {{ $user->activeSuspension->reason }}
                                    </p>
                                    <div class="mt-2 flex flex-wrap gap-x-4 gap-y-1 text-xs text-slate-500 dark:text-navy-300">
                                        <span>Suspended on {{ $user->activeSuspension->suspended_at->format('M d, Y \a\t H:i') }}</span>
                                        @if ($user->activeSuspension->suspendedByUser)
                                            <span>by {{ $user->activeSuspension->suspendedByUser->name }}</span>
                                        @endif
                                    </div>
                                </div>
                            </div>
                        </div>

                        <form method="POST" action="{{ route('users/unsuspend', $user) }}" class="mt-4">
                            @csrf
                            <button type="submit"
                                class="btn rounded-full bg-success font-medium text-white hover:bg-success/90 focus:bg-success/90">
                                Unsuspend User
                            </button>
                        </form>
                    @else
                        <p class="text-sm text-slate-500 dark:text-navy-300">
                            Suspending a user will block their access to the system. You must provide a reason for the suspension.
                        </p>
                        <button type="button" @click="showSuspendModal = true"
                            class="btn mt-4 rounded-full border border-error/30 px-5 text-error hover:bg-error/10 focus:bg-error/10">
                            Suspend User
                        </button>
                    @endif

                    {{-- Suspension History --}}
                    @if ($user->suspensions->count() > 0)
                        <div class="mt-6">
                            <h3 class="text-sm font-medium text-slate-600 dark:text-navy-100">Suspension History</h3>
                            <div class="mt-2 space-y-2">
                                @foreach ($user->suspensions as $suspension)
                                    <div class="rounded-lg bg-slate-50 px-4 py-3 dark:bg-navy-600">
                                        <p class="text-sm text-slate-700 dark:text-navy-100">{{ $suspension->reason }}</p>
                                        <div class="mt-1 flex flex-wrap gap-x-4 gap-y-1 text-xs text-slate-400 dark:text-navy-300">
                                            <span>Suspended: {{ $suspension->suspended_at->format('M d, Y H:i') }}
                                                @if ($suspension->suspendedByUser) by {{ $suspension->suspendedByUser->name }} @endif
                                            </span>
                                            @if ($suspension->unsuspended_at)
                                                <span>Unsuspended: {{ $suspension->unsuspended_at->format('M d, Y H:i') }}
                                                    @if ($suspension->unsuspendedByUser) by {{ $suspension->unsuspendedByUser->name }} @endif
                                                </span>
                                            @else
                                                <span class="font-medium text-error">Currently active</span>
                                            @endif
                                        </div>
                                    </div>
                                @endforeach
                            </div>
                        </div>
                    @endif
                </div>

                {{-- Suspend Modal --}}
                <template x-teleport="#x-teleport-target">
                    <div class="fixed inset-0 z-100 flex flex-col items-center justify-center overflow-hidden px-4 py-6 sm:px-5"
                        x-show="showSuspendModal" role="dialog" @keydown.window.escape="showSuspendModal = false">
                        <div class="absolute inset-0 bg-slate-900/60 transition-opacity duration-300"
                            @click="showSuspendModal = false" x-show="showSuspendModal"
                            x-transition:enter="ease-out" x-transition:enter-start="opacity-0" x-transition:enter-end="opacity-100"
                            x-transition:leave="ease-in" x-transition:leave-start="opacity-100" x-transition:leave-end="opacity-0"></div>
                        <div class="relative w-full max-w-lg rounded-lg bg-white transition-opacity duration-300 dark:bg-navy-700"
                            x-show="showSuspendModal"
                            x-transition:enter="ease-out" x-transition:enter-start="opacity-0" x-transition:enter-end="opacity-100"
                            x-transition:leave="ease-in" x-transition:leave-start="opacity-100" x-transition:leave-end="opacity-0">

                            <div class="flex items-center justify-between border-b border-slate-200 px-4 py-3 dark:border-navy-500 sm:px-5">
                                <h3 class="text-lg font-medium text-slate-700 dark:text-navy-100">Suspend User</h3>
                                <button @click="showSuspendModal = false" class="btn size-7 rounded-full p-0 hover:bg-slate-300/20 focus:bg-slate-300/20 dark:hover:bg-navy-300/20 dark:focus:bg-navy-300/20">
                                    <svg xmlns="http://www.w3.org/2000/svg" class="size-4.5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                        <path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12" />
                                    </svg>
                                </button>
                            </div>

                            <form method="POST" action="{{ route('users/suspend', $user) }}" class="px-4 py-4 sm:px-5">
                                @csrf
                                <div class="flex items-center space-x-3 rounded-lg bg-warning/10 px-4 py-3 text-warning">
                                    <svg xmlns="http://www.w3.org/2000/svg" class="size-5 shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                        <path stroke-linecap="round" stroke-linejoin="round" d="M12 9v3.75m-9.303 3.376c-.866 1.5.217 3.374 1.948 3.374h14.71c1.73 0 2.813-1.874 1.948-3.374L13.949 3.378c-.866-1.5-3.032-1.5-3.898 0L2.697 16.126zM12 15.75h.007v.008H12v-.008z" />
                                    </svg>
                                    <p class="text-sm">You are about to suspend <strong>{{ $user->name }}</strong>. They will not be able to access the system.</p>
                                </div>

                                <label class="mt-4 block">
                                    <span class="font-medium text-slate-600 dark:text-navy-100">Reason for suspension</span>
                                    <textarea name="reason" rows="3" required
                                        class="form-textarea mt-1.5 w-full rounded-lg border border-slate-300 bg-transparent px-3 py-2 placeholder:text-slate-400/70 hover:border-slate-400 focus:border-primary dark:border-navy-450 dark:hover:border-navy-400 dark:focus:border-accent @error('reason') border-error @enderror"
                                        placeholder="Provide a reason for suspending this user...">{{ old('reason') }}</textarea>
                                    @error('reason')
                                        <span class="text-tiny-plus text-error">{{ $message }}</span>
                                    @enderror
                                </label>

                                <div class="mt-4 flex space-x-2">
                                    <button type="submit"
                                        class="btn flex-1 rounded-full bg-error font-medium text-white hover:bg-error-focus focus:bg-error-focus">
                                        Suspend User
                                    </button>
                                    <button type="button" @click="showSuspendModal = false"
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
    </main>
</x-app-layout>
