<x-customer-layout title="Profile">
    <h2 class="text-xl font-medium text-slate-800 dark:text-navy-50 lg:text-2xl">Profile</h2>

    {{-- Tabs --}}
    <div class="mt-4 flex space-x-1 rounded-lg bg-slate-100 p-1 dark:bg-navy-700">
        <a href="{{ route('customer.profile', ['tab' => 'account']) }}" class="rounded-md px-4 py-2 text-sm font-medium {{ $tab === 'account' ? 'bg-white text-slate-800 shadow dark:bg-navy-500 dark:text-navy-100' : 'text-slate-500 hover:text-slate-700 dark:text-navy-300' }}">
            Account
        </a>
        <a href="{{ route('customer.profile', ['tab' => 'security']) }}" class="rounded-md px-4 py-2 text-sm font-medium {{ $tab === 'security' ? 'bg-white text-slate-800 shadow dark:bg-navy-500 dark:text-navy-100' : 'text-slate-500 hover:text-slate-700 dark:text-navy-300' }}">
            Security
        </a>
    </div>

    @if ($tab === 'account')
        {{-- Account Settings --}}
        <div class="card mt-5 p-4 sm:p-5">
            <h3 class="text-base font-medium text-slate-700 dark:text-navy-100">Account Information</h3>
            <form method="POST" action="{{ route('customer.profile.update-account') }}" enctype="multipart/form-data" class="mt-5">
                @csrf @method('PUT')

                <div class="space-y-4">
                    {{-- Avatar --}}
                    <div class="flex items-center space-x-4">
                        <div class="flex size-16 items-center justify-center rounded-full bg-primary/10 text-xl font-semibold text-primary dark:bg-accent/10 dark:text-accent-light">
                            @if ($user->avatar)
                                <img class="size-16 rounded-full object-cover" src="{{ asset('storage/' . $user->avatar) }}" alt="{{ $user->name }}" />
                            @else
                                {{ substr($user->name, 0, 1) }}
                            @endif
                        </div>
                        <div>
                            <input type="file" name="avatar" accept="image/*" class="text-sm text-slate-500 file:mr-4 file:rounded-full file:border-0 file:bg-primary/10 file:px-4 file:py-2 file:text-sm file:font-medium file:text-primary hover:file:bg-primary/20 dark:file:bg-accent/10 dark:file:text-accent-light" />
                            <p class="mt-1 text-xs text-slate-400 dark:text-navy-300">JPG, PNG or WebP. Max 2MB.</p>
                        </div>
                    </div>

                    {{-- Name --}}
                    <div>
                        <label class="block text-sm font-medium text-slate-700 dark:text-navy-100">Name</label>
                        <input type="text" name="name" value="{{ old('name', $user->name) }}" required class="form-input mt-1 w-full rounded-lg border border-slate-300 bg-transparent px-3 py-2 placeholder:text-slate-400 hover:border-slate-400 focus:border-primary dark:border-navy-450 dark:hover:border-navy-400 dark:focus:border-accent" />
                        @error('name') <span class="text-tiny-plus text-error">{{ $message }}</span> @enderror
                    </div>

                    {{-- Email --}}
                    <div>
                        <label class="block text-sm font-medium text-slate-700 dark:text-navy-100">Email</label>
                        <input type="email" name="email" value="{{ old('email', $user->email) }}" required class="form-input mt-1 w-full rounded-lg border border-slate-300 bg-transparent px-3 py-2 placeholder:text-slate-400 hover:border-slate-400 focus:border-primary dark:border-navy-450 dark:hover:border-navy-400 dark:focus:border-accent" />
                        @error('email') <span class="text-tiny-plus text-error">{{ $message }}</span> @enderror
                    </div>

                    {{-- Phone --}}
                    <div>
                        <label class="block text-sm font-medium text-slate-700 dark:text-navy-100">Phone</label>
                        <input type="text" name="phone" value="{{ old('phone', $user->phone) }}" class="form-input mt-1 w-full rounded-lg border border-slate-300 bg-transparent px-3 py-2 placeholder:text-slate-400 hover:border-slate-400 focus:border-primary dark:border-navy-450 dark:hover:border-navy-400 dark:focus:border-accent" />
                        @error('phone') <span class="text-tiny-plus text-error">{{ $message }}</span> @enderror
                    </div>
                </div>

                <div class="mt-6 flex justify-end">
                    <button type="submit" class="btn bg-primary font-medium text-white hover:bg-primary-focus focus:bg-primary-focus active:bg-primary-focus/90 dark:bg-accent dark:hover:bg-accent-focus dark:focus:bg-accent-focus dark:active:bg-accent/90">
                        Save Changes
                    </button>
                </div>
            </form>
        </div>
    @else
        {{-- Security / Change Password --}}
        <div class="card mt-5 p-4 sm:p-5">
            <h3 class="text-base font-medium text-slate-700 dark:text-navy-100">Change Password</h3>
            <form method="POST" action="{{ route('customer.profile.update-password') }}" class="mt-5">
                @csrf @method('PUT')

                <div class="space-y-4">
                    <div>
                        <label class="block text-sm font-medium text-slate-700 dark:text-navy-100">Current Password</label>
                        <input type="password" name="current_password" required class="form-input mt-1 w-full rounded-lg border border-slate-300 bg-transparent px-3 py-2 placeholder:text-slate-400 hover:border-slate-400 focus:border-primary dark:border-navy-450 dark:hover:border-navy-400 dark:focus:border-accent" />
                        @error('current_password') <span class="text-tiny-plus text-error">{{ $message }}</span> @enderror
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-slate-700 dark:text-navy-100">New Password</label>
                        <input type="password" name="password" required class="form-input mt-1 w-full rounded-lg border border-slate-300 bg-transparent px-3 py-2 placeholder:text-slate-400 hover:border-slate-400 focus:border-primary dark:border-navy-450 dark:hover:border-navy-400 dark:focus:border-accent" />
                        @error('password') <span class="text-tiny-plus text-error">{{ $message }}</span> @enderror
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-slate-700 dark:text-navy-100">Confirm New Password</label>
                        <input type="password" name="password_confirmation" required class="form-input mt-1 w-full rounded-lg border border-slate-300 bg-transparent px-3 py-2 placeholder:text-slate-400 hover:border-slate-400 focus:border-primary dark:border-navy-450 dark:hover:border-navy-400 dark:focus:border-accent" />
                    </div>
                </div>

                <div class="mt-6 flex justify-end">
                    <button type="submit" class="btn bg-primary font-medium text-white hover:bg-primary-focus focus:bg-primary-focus active:bg-primary-focus/90 dark:bg-accent dark:hover:bg-accent-focus dark:focus:bg-accent-focus dark:active:bg-accent/90">
                        Update Password
                    </button>
                </div>
            </form>
        </div>
    @endif
</x-customer-layout>
