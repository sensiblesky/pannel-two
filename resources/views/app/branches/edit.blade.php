<x-app-layout title="Edit Branch" is-sidebar-open="true" is-header-blur="true">
    <main class="main-content w-full px-[var(--margin-x)] pb-8">
        <div class="flex items-center space-x-4 py-5 lg:py-6">
            <h2 class="text-xl font-medium text-slate-800 dark:text-navy-50 lg:text-2xl">Edit Branch</h2>
            <div class="hidden h-full py-1 sm:flex"><div class="h-full w-px bg-slate-300 dark:bg-navy-600"></div></div>
            <ul class="hidden flex-wrap items-center space-x-2 sm:flex">
                <li class="flex items-center space-x-2">
                    <a class="text-primary transition-colors hover:text-primary-focus dark:text-accent-light dark:hover:text-accent" href="{{ route('config/branches') }}">Branches</a>
                    <svg x-ignore xmlns="http://www.w3.org/2000/svg" class="size-4" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7" /></svg>
                </li>
                <li>Edit Branch</li>
            </ul>
        </div>

        <div class="grid grid-cols-1 gap-4 sm:gap-5 lg:gap-6">
            <div>
                <div class="card">
                    <div class="border-b border-slate-200 p-4 dark:border-navy-500 sm:px-5">
                        <h2 class="text-lg font-medium tracking-wide text-slate-700 dark:text-navy-100">Edit: {{ $branch->name }}</h2>
                    </div>
                    <form method="POST" action="{{ route('config/branches-update', $branch) }}" class="space-y-4 p-4 sm:p-5">
                        @csrf
                        @method('PUT')

                        <div class="grid grid-cols-1 gap-4 sm:grid-cols-2">
                            <label class="block">
                                <span class="font-medium text-slate-600 dark:text-navy-100">Branch Name</span>
                                <input class="form-input mt-1.5 w-full rounded-lg border border-slate-300 bg-transparent px-3 py-2 placeholder:text-slate-400/70 hover:border-slate-400 focus:border-primary dark:border-navy-450 dark:hover:border-navy-400 dark:focus:border-accent @error('name') border-error @enderror"
                                    placeholder="e.g. Main Campus" type="text" name="name" value="{{ old('name', $branch->name) }}" />
                                @error('name')<span class="text-tiny-plus text-error">{{ $message }}</span>@enderror
                            </label>
                            <label class="block">
                                <span class="font-medium text-slate-600 dark:text-navy-100">Branch Code</span>
                                <input class="form-input mt-1.5 w-full rounded-lg border border-slate-300 bg-transparent px-3 py-2 placeholder:text-slate-400/70 hover:border-slate-400 focus:border-primary dark:border-navy-450 dark:hover:border-navy-400 dark:focus:border-accent @error('code') border-error @enderror"
                                    placeholder="e.g. HQ-001" type="text" name="code" value="{{ old('code', $branch->code) }}" />
                                @error('code')<span class="text-tiny-plus text-error">{{ $message }}</span>@enderror
                            </label>
                        </div>

                        <div class="grid grid-cols-1 gap-4 sm:grid-cols-2">
                            <label class="block">
                                <span class="font-medium text-slate-600 dark:text-navy-100">Email</span>
                                <input class="form-input mt-1.5 w-full rounded-lg border border-slate-300 bg-transparent px-3 py-2 placeholder:text-slate-400/70 hover:border-slate-400 focus:border-primary dark:border-navy-450 dark:hover:border-navy-400 dark:focus:border-accent @error('email') border-error @enderror"
                                    placeholder="Branch email" type="email" name="email" value="{{ old('email', $branch->email) }}" />
                                @error('email')<span class="text-tiny-plus text-error">{{ $message }}</span>@enderror
                            </label>
                            <label class="block">
                                <span class="font-medium text-slate-600 dark:text-navy-100">Phone</span>
                                <input class="form-input mt-1.5 w-full rounded-lg border border-slate-300 bg-transparent px-3 py-2 placeholder:text-slate-400/70 hover:border-slate-400 focus:border-primary dark:border-navy-450 dark:hover:border-navy-400 dark:focus:border-accent @error('phone') border-error @enderror"
                                    placeholder="Branch phone" type="text" name="phone" value="{{ old('phone', $branch->phone) }}" />
                                @error('phone')<span class="text-tiny-plus text-error">{{ $message }}</span>@enderror
                            </label>
                        </div>

                        <label class="block">
                            <span class="font-medium text-slate-600 dark:text-navy-100">Address</span>
                            <textarea name="address" rows="2"
                                class="form-textarea mt-1.5 w-full rounded-lg border border-slate-300 bg-transparent px-3 py-2 placeholder:text-slate-400/70 hover:border-slate-400 focus:border-primary dark:border-navy-450 dark:hover:border-navy-400 dark:focus:border-accent"
                                placeholder="Full address (optional)">{{ old('address', $branch->address) }}</textarea>
                        </label>

                        <div class="grid grid-cols-1 gap-4 sm:grid-cols-3">
                            <label class="block">
                                <span class="font-medium text-slate-600 dark:text-navy-100">City</span>
                                <input class="form-input mt-1.5 w-full rounded-lg border border-slate-300 bg-transparent px-3 py-2 placeholder:text-slate-400/70 hover:border-slate-400 focus:border-primary dark:border-navy-450 dark:hover:border-navy-400 dark:focus:border-accent"
                                    placeholder="City" type="text" name="city" value="{{ old('city', $branch->city) }}" />
                            </label>
                            <label class="block">
                                <span class="font-medium text-slate-600 dark:text-navy-100">Country</span>
                                <input class="form-input mt-1.5 w-full rounded-lg border border-slate-300 bg-transparent px-3 py-2 placeholder:text-slate-400/70 hover:border-slate-400 focus:border-primary dark:border-navy-450 dark:hover:border-navy-400 dark:focus:border-accent"
                                    placeholder="Country" type="text" name="country" value="{{ old('country', $branch->country) }}" />
                            </label>
                            <label class="block">
                                <span class="font-medium text-slate-600 dark:text-navy-100">Status</span>
                                <select name="status"
                                    class="form-select mt-1.5 w-full rounded-lg border border-slate-300 bg-white px-3 py-2 hover:border-slate-400 focus:border-primary dark:border-navy-450 dark:bg-navy-700 dark:hover:border-navy-400 dark:focus:border-accent">
                                    <option value="1" {{ old('status', (string)$branch->status) === '1' ? 'selected' : '' }}>Active</option>
                                    <option value="0" {{ old('status', (string)$branch->status) === '0' ? 'selected' : '' }}>Inactive</option>
                                </select>
                            </label>
                        </div>

                        <div class="flex justify-end space-x-2 pt-4">
                            <a href="{{ route('config/branches') }}"
                                class="btn space-x-2 border border-slate-300 font-medium text-slate-800 hover:bg-slate-150 focus:bg-slate-150 active:bg-slate-150/80 dark:border-navy-450 dark:text-navy-50 dark:hover:bg-navy-500 dark:focus:bg-navy-500 dark:active:bg-navy-500/90">
                                <span>Cancel</span>
                            </a>
                            <button type="submit"
                                class="btn space-x-2 bg-primary font-medium text-white shadow-lg shadow-primary/50 hover:bg-primary-focus focus:bg-primary-focus active:bg-primary-focus/90 dark:bg-accent dark:shadow-accent/50 dark:hover:bg-accent-focus dark:focus:bg-accent-focus dark:active:bg-accent/90">
                                <span>Update Branch</span>
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </main>
</x-app-layout>
