<x-customer-layout title="Create Ticket">
    <div class="flex items-center space-x-4">
        <a href="{{ route('customer.tickets') }}" class="rounded-lg p-2 text-slate-400 hover:bg-slate-100 hover:text-slate-600 dark:hover:bg-navy-600 dark:hover:text-navy-100">
            <svg xmlns="http://www.w3.org/2000/svg" class="size-5" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7"/></svg>
        </a>
        <h2 class="text-xl font-medium text-slate-800 dark:text-navy-50 lg:text-2xl">Create Ticket</h2>
    </div>

    <div class="card mt-5 p-4 sm:p-5">
        <form method="POST" action="{{ route('customer.tickets.store') }}" enctype="multipart/form-data">
            @csrf

            <div class="space-y-5">
                {{-- Subject --}}
                <div>
                    <label class="block text-sm font-medium text-slate-700 dark:text-navy-100">Subject <span class="text-error">*</span></label>
                    <input type="text" name="subject" value="{{ old('subject') }}" required class="form-input mt-1 w-full rounded-lg border border-slate-300 bg-transparent px-3 py-2 placeholder:text-slate-400 hover:border-slate-400 focus:border-primary dark:border-navy-450 dark:hover:border-navy-400 dark:focus:border-accent" placeholder="Brief description of your issue" />
                    @error('subject') <span class="text-tiny-plus text-error">{{ $message }}</span> @enderror
                </div>

                {{-- Description --}}
                <div>
                    <label class="block text-sm font-medium text-slate-700 dark:text-navy-100">Description</label>
                    <textarea name="description" rows="5" class="form-textarea mt-1 w-full rounded-lg border border-slate-300 bg-transparent px-3 py-2 placeholder:text-slate-400 hover:border-slate-400 focus:border-primary dark:border-navy-450 dark:hover:border-navy-400 dark:focus:border-accent" placeholder="Provide as much detail as possible...">{{ old('description') }}</textarea>
                    @error('description') <span class="text-tiny-plus text-error">{{ $message }}</span> @enderror
                </div>

                <div class="grid grid-cols-1 gap-4 sm:grid-cols-2">
                    {{-- Category --}}
                    <div>
                        <label class="block text-sm font-medium text-slate-700 dark:text-navy-100">Category</label>
                        <select name="category_id" class="form-select mt-1 w-full rounded-lg border border-slate-300 bg-transparent px-3 py-2 hover:border-slate-400 focus:border-primary dark:border-navy-450 dark:hover:border-navy-400 dark:focus:border-accent">
                            <option value="">Select category...</option>
                            @foreach ($categories as $category)
                                <option value="{{ $category->id }}" {{ old('category_id') == $category->id ? 'selected' : '' }}>{{ $category->name }}</option>
                            @endforeach
                        </select>
                        @error('category_id') <span class="text-tiny-plus text-error">{{ $message }}</span> @enderror
                    </div>

                    {{-- Priority --}}
                    <div>
                        <label class="block text-sm font-medium text-slate-700 dark:text-navy-100">Priority</label>
                        <select name="priority_id" class="form-select mt-1 w-full rounded-lg border border-slate-300 bg-transparent px-3 py-2 hover:border-slate-400 focus:border-primary dark:border-navy-450 dark:hover:border-navy-400 dark:focus:border-accent">
                            <option value="">Select priority...</option>
                            @foreach ($priorities as $priority)
                                <option value="{{ $priority->id }}" {{ old('priority_id') == $priority->id ? 'selected' : '' }}>{{ $priority->name }}</option>
                            @endforeach
                        </select>
                        @error('priority_id') <span class="text-tiny-plus text-error">{{ $message }}</span> @enderror
                    </div>
                </div>

                {{-- Attachments --}}
                <div>
                    <label class="block text-sm font-medium text-slate-700 dark:text-navy-100">Attachments</label>
                    <input type="file" name="attachments[]" multiple class="mt-1 block w-full text-sm text-slate-500 file:mr-4 file:rounded-full file:border-0 file:bg-primary/10 file:px-4 file:py-2 file:text-sm file:font-medium file:text-primary hover:file:bg-primary/20 dark:file:bg-accent/10 dark:file:text-accent-light" />
                    <p class="mt-1 text-xs text-slate-400 dark:text-navy-300">Max 10MB per file</p>
                    @error('attachments.*') <span class="text-tiny-plus text-error">{{ $message }}</span> @enderror
                </div>
            </div>

            <div class="mt-6 flex justify-end space-x-3">
                <a href="{{ route('customer.tickets') }}" class="btn border border-slate-300 font-medium text-slate-800 hover:bg-slate-150 focus:bg-slate-150 active:bg-slate-150/80 dark:border-navy-450 dark:text-navy-50 dark:hover:bg-navy-500 dark:focus:bg-navy-500 dark:active:bg-navy-500/90">
                    Cancel
                </a>
                <button type="submit" class="btn bg-primary font-medium text-white hover:bg-primary-focus focus:bg-primary-focus active:bg-primary-focus/90 dark:bg-accent dark:hover:bg-accent-focus dark:focus:bg-accent-focus dark:active:bg-accent/90">
                    Submit Ticket
                </button>
            </div>
        </form>
    </div>
</x-customer-layout>
