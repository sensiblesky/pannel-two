<x-app-layout title="Canned Responses" is-sidebar-open="true" is-header-blur="true">

    @slot('script')
        <script>
            function confirmDelete(id) {
                Swal.fire({
                    title: 'Are you sure?',
                    text: 'This canned response will be deleted.',
                    icon: 'warning',
                    showCancelButton: true,
                    confirmButtonColor: '#e53e3e',
                    confirmButtonText: 'Yes, delete it!',
                    reverseButtons: true,
                }).then((result) => {
                    if (result.isConfirmed) {
                        document.getElementById('delete-form-' + id).submit();
                    }
                });
            }
        </script>
        @if(session('success'))
            <script>
                Swal.fire({ toast: true, position: 'top-end', icon: 'success', title: @json(session('success')), showConfirmButton: false, timer: 3000 });
            </script>
        @endif
    @endslot

    <main class="main-content w-full px-[var(--margin-x)] pb-8" x-data="{ showCreateModal: false, editItem: null }">
        <div class="flex items-center justify-between py-5 lg:py-6">
            <div class="flex items-center space-x-4">
                <h2 class="text-xl font-medium text-slate-800 dark:text-navy-50 lg:text-2xl">Canned Responses</h2>
                <div class="hidden h-full py-1 sm:flex">
                    <div class="h-full w-px bg-slate-300 dark:bg-navy-600"></div>
                </div>
                <ul class="hidden flex-wrap items-center space-x-2 sm:flex">
                    <li class="flex items-center space-x-2">
                        <a class="text-primary transition-colors hover:text-primary-focus dark:text-accent-light dark:hover:text-accent" href="{{ route('tickets/dashboard') }}">Tickets</a>
                        <svg xmlns="http://www.w3.org/2000/svg" class="size-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/></svg>
                    </li>
                    <li class="flex items-center space-x-2">
                        <span>Settings</span>
                        <svg xmlns="http://www.w3.org/2000/svg" class="size-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/></svg>
                    </li>
                    <li>Canned Responses</li>
                </ul>
            </div>
            <button @click="showCreateModal = true; editItem = null" class="btn space-x-2 bg-primary font-medium text-white shadow-lg shadow-primary/50 hover:bg-primary-focus focus:bg-primary-focus active:bg-primary-focus/90 dark:bg-accent dark:shadow-accent/50 dark:hover:bg-accent-focus dark:focus:bg-accent-focus dark:active:bg-accent/90">
                <svg xmlns="http://www.w3.org/2000/svg" class="size-5" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/></svg>
                <span>Add Response</span>
            </button>
        </div>

        <div class="card mt-3">
            {{-- Search & Filters --}}
            <div class="flex flex-col space-y-4 px-4 py-4 sm:flex-row sm:items-center sm:space-y-0 sm:space-x-4 sm:px-5">
                <form method="GET" action="{{ route('tickets/settings-canned-responses') }}" class="flex flex-1 flex-wrap items-center gap-3">
                    <label class="relative flex flex-1">
                        <input name="search" value="{{ request('search') }}"
                            class="form-input peer w-full rounded-lg border border-slate-300 bg-transparent px-3 py-2 pl-9 placeholder:text-slate-400/70 hover:border-slate-400 focus:border-primary dark:border-navy-450 dark:hover:border-navy-400 dark:focus:border-accent"
                            placeholder="Search responses..." type="text" />
                        <span class="pointer-events-none absolute flex h-full w-10 items-center justify-center text-slate-400 peer-focus:text-primary dark:text-navy-300 dark:peer-focus:text-accent">
                            <svg xmlns="http://www.w3.org/2000/svg" class="size-4.5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.5"><path stroke-linecap="round" stroke-linejoin="round" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z" /></svg>
                        </span>
                    </label>
                    <select name="category"
                        class="form-select rounded-lg border border-slate-300 bg-white px-3 py-2 hover:border-slate-400 focus:border-primary dark:border-navy-450 dark:bg-navy-700 dark:hover:border-navy-400 dark:focus:border-accent">
                        <option value="">All Categories</option>
                        @foreach($categories as $cat)
                            <option value="{{ $cat }}" {{ request('category') === $cat ? 'selected' : '' }}>{{ $cat }}</option>
                        @endforeach
                    </select>
                    <button type="submit" class="btn bg-slate-150 font-medium text-slate-800 hover:bg-slate-200 focus:bg-slate-200 active:bg-slate-200/80 dark:bg-navy-500 dark:text-navy-50 dark:hover:bg-navy-450 dark:focus:bg-navy-450 dark:active:bg-navy-450/90">Filter</button>
                    @if(request()->hasAny(['search', 'category']))
                        <a href="{{ route('tickets/settings-canned-responses') }}" class="btn bg-slate-150 font-medium text-slate-800 hover:bg-slate-200 focus:bg-slate-200 active:bg-slate-200/80 dark:bg-navy-500 dark:text-navy-50 dark:hover:bg-navy-450 dark:focus:bg-navy-450 dark:active:bg-navy-450/90">Clear</a>
                    @endif
                </form>
            </div>

            {{-- Table --}}
            <div class="is-scrollbar-hidden min-w-full overflow-x-auto">
                <table class="is-hoverable w-full text-left">
                    <thead>
                        <tr>
                            <th class="whitespace-nowrap rounded-tl-lg bg-slate-200 px-4 py-3 font-semibold uppercase text-slate-800 dark:bg-navy-800 dark:text-navy-100 lg:px-5">#</th>
                            <th class="whitespace-nowrap bg-slate-200 px-4 py-3 font-semibold uppercase text-slate-800 dark:bg-navy-800 dark:text-navy-100 lg:px-5">Title</th>
                            <th class="whitespace-nowrap bg-slate-200 px-4 py-3 font-semibold uppercase text-slate-800 dark:bg-navy-800 dark:text-navy-100 lg:px-5">Shortcut</th>
                            <th class="whitespace-nowrap bg-slate-200 px-4 py-3 font-semibold uppercase text-slate-800 dark:bg-navy-800 dark:text-navy-100 lg:px-5">Category</th>
                            <th class="whitespace-nowrap bg-slate-200 px-4 py-3 font-semibold uppercase text-slate-800 dark:bg-navy-800 dark:text-navy-100 lg:px-5">Visibility</th>
                            <th class="whitespace-nowrap bg-slate-200 px-4 py-3 font-semibold uppercase text-slate-800 dark:bg-navy-800 dark:text-navy-100 lg:px-5">Created By</th>
                            <th class="whitespace-nowrap rounded-tr-lg bg-slate-200 px-4 py-3 font-semibold uppercase text-slate-800 dark:bg-navy-800 dark:text-navy-100 lg:px-5">Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($responses as $resp)
                            <tr class="border-y border-transparent border-b-slate-200 dark:border-b-navy-500">
                                <td class="whitespace-nowrap px-4 py-3 text-sm text-slate-500 dark:text-navy-200 sm:px-5">{{ $responses->firstItem() + $loop->index }}</td>
                                <td class="px-4 py-3 sm:px-5">
                                    <p class="font-medium text-slate-700 dark:text-navy-100">{{ $resp->title }}</p>
                                    <p class="mt-0.5 max-w-xs truncate text-xs text-slate-400 dark:text-navy-300">{{ Str::limit($resp->message, 80) }}</p>
                                </td>
                                <td class="whitespace-nowrap px-4 py-3 sm:px-5">
                                    @if($resp->shortcut)
                                        <code class="rounded bg-slate-100 px-1.5 py-0.5 text-xs font-mono text-slate-600 dark:bg-navy-600 dark:text-navy-200">/{{ $resp->shortcut }}</code>
                                    @else
                                        <span class="text-xs text-slate-400 dark:text-navy-300">—</span>
                                    @endif
                                </td>
                                <td class="whitespace-nowrap px-4 py-3 text-sm text-slate-500 dark:text-navy-200 sm:px-5">{{ $resp->category ?? '—' }}</td>
                                <td class="whitespace-nowrap px-4 py-3 sm:px-5">
                                    <div class="badge rounded-full {{ $resp->is_shared ? 'bg-info/10 text-info dark:bg-info/15' : 'bg-slate-200 text-slate-500 dark:bg-navy-500 dark:text-navy-200' }}">{{ $resp->is_shared ? 'Shared' : 'Personal' }}</div>
                                </td>
                                <td class="whitespace-nowrap px-4 py-3 text-sm text-slate-500 dark:text-navy-200 sm:px-5">{{ $resp->creator_name ?? '—' }}</td>
                                <td class="whitespace-nowrap px-4 py-3 sm:px-5">
                                    <div class="flex space-x-2">
                                        <button @click="showCreateModal = true; editItem = { id: {{ $resp->id }}, title: {{ json_encode($resp->title) }}, shortcut: {{ json_encode($resp->shortcut ?? '') }}, message: {{ json_encode($resp->message) }}, category: {{ json_encode($resp->category ?? '') }}, is_shared: {{ $resp->is_shared ? 'true' : 'false' }} }"
                                                class="btn size-8 rounded-full p-0 hover:bg-slate-300/20 focus:bg-slate-300/20 active:bg-slate-300/25 dark:hover:bg-navy-300/20 dark:focus:bg-navy-300/20 dark:active:bg-navy-300/25">
                                            <svg xmlns="http://www.w3.org/2000/svg" class="size-4.5" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"/></svg>
                                        </button>
                                        <button @click="confirmDelete({{ $resp->id }})" class="btn size-8 rounded-full p-0 hover:bg-error/20 focus:bg-error/20 active:bg-error/25 text-error">
                                            <svg xmlns="http://www.w3.org/2000/svg" class="size-4.5" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/></svg>
                                        </button>
                                        <form id="delete-form-{{ $resp->id }}" action="{{ route('tickets/settings-canned-responses-destroy', $resp->id) }}" method="POST" class="hidden">@csrf @method('DELETE')</form>
                                    </div>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="7" class="px-4 py-8 text-center">
                                    <div class="flex flex-col items-center space-y-2">
                                        <svg xmlns="http://www.w3.org/2000/svg" class="size-10 text-slate-300 dark:text-navy-400" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1">
                                            <path stroke-linecap="round" stroke-linejoin="round" d="M8 10h.01M12 10h.01M16 10h.01M9 16H5a2 2 0 01-2-2V6a2 2 0 012-2h14a2 2 0 012 2v8a2 2 0 01-2 2h-5l-5 5v-5z" />
                                        </svg>
                                        <p class="text-slate-400 dark:text-navy-300">No canned responses found</p>
                                        @if(request()->hasAny(['search', 'category']))
                                            <a href="{{ route('tickets/settings-canned-responses') }}" class="text-sm text-primary hover:text-primary-focus dark:text-accent-light dark:hover:text-accent">Clear filters</a>
                                        @endif
                                    </div>
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            {{-- Pagination --}}
            <div class="flex flex-col justify-between space-y-4 px-4 py-4 sm:flex-row sm:items-center sm:space-y-0 sm:px-5">
                <div class="flex items-center space-x-2 text-xs-plus">
                    <span>Showing</span>
                    <span class="font-semibold">{{ $responses->firstItem() ?? 0 }} - {{ $responses->lastItem() ?? 0 }}</span>
                    <span>of {{ $responses->total() }} entries</span>
                </div>

                @if ($responses->hasPages())
                    <ol class="pagination">
                        <li class="rounded-l-lg bg-slate-150 dark:bg-navy-500">
                            @if ($responses->onFirstPage())
                                <span class="flex size-8 items-center justify-center rounded-lg text-slate-400 dark:text-navy-300">
                                    <svg xmlns="http://www.w3.org/2000/svg" class="size-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M15 19l-7-7 7-7" /></svg>
                                </span>
                            @else
                                <a href="{{ $responses->previousPageUrl() }}" class="flex size-8 items-center justify-center rounded-lg text-slate-500 transition-colors hover:bg-slate-300 focus:bg-slate-300 active:bg-slate-300/80 dark:text-navy-200 dark:hover:bg-navy-450 dark:focus:bg-navy-450 dark:active:bg-navy-450/90">
                                    <svg xmlns="http://www.w3.org/2000/svg" class="size-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M15 19l-7-7 7-7" /></svg>
                                </a>
                            @endif
                        </li>
                        @foreach ($responses->getUrlRange(1, $responses->lastPage()) as $page => $url)
                            <li class="bg-slate-150 dark:bg-navy-500">
                                <a href="{{ $url }}" class="{{ $page == $responses->currentPage() ? 'flex size-8 items-center justify-center rounded-lg bg-primary font-medium text-white transition-colors hover:bg-primary-focus focus:bg-primary-focus active:bg-primary-focus/90 dark:bg-accent dark:hover:bg-accent-focus dark:focus:bg-accent-focus dark:active:bg-accent/90' : 'flex size-8 items-center justify-center rounded-lg text-slate-500 transition-colors hover:bg-slate-300 focus:bg-slate-300 active:bg-slate-300/80 dark:text-navy-200 dark:hover:bg-navy-450 dark:focus:bg-navy-450 dark:active:bg-navy-450/90' }}">
                                    {{ $page }}
                                </a>
                            </li>
                        @endforeach
                        <li class="rounded-r-lg bg-slate-150 dark:bg-navy-500">
                            @if ($responses->hasMorePages())
                                <a href="{{ $responses->nextPageUrl() }}" class="flex size-8 items-center justify-center rounded-lg text-slate-500 transition-colors hover:bg-slate-300 focus:bg-slate-300 active:bg-slate-300/80 dark:text-navy-200 dark:hover:bg-navy-450 dark:focus:bg-navy-450 dark:active:bg-navy-450/90">
                                    <svg xmlns="http://www.w3.org/2000/svg" class="size-4" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7" /></svg>
                                </a>
                            @else
                                <span class="flex size-8 items-center justify-center rounded-lg text-slate-400 dark:text-navy-300">
                                    <svg xmlns="http://www.w3.org/2000/svg" class="size-4" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7" /></svg>
                                </span>
                            @endif
                        </li>
                    </ol>
                @endif
            </div>
        </div>

        {{-- Create / Edit Modal --}}
        <template x-teleport="body">
            <div x-show="showCreateModal" x-transition.opacity class="fixed inset-0 z-100 flex items-center justify-center bg-slate-900/60" @click.self="showCreateModal = false">
                <div class="relative w-full max-w-lg rounded-lg bg-white p-6 shadow-xl dark:bg-navy-700" @click.stop>
                    <h3 class="text-lg font-medium text-slate-700 dark:text-navy-100" x-text="editItem ? 'Edit Canned Response' : 'Add Canned Response'"></h3>

                    {{-- Create Form --}}
                    <form x-show="!editItem" method="POST" action="{{ route('tickets/settings-canned-responses-store') }}" class="mt-4 space-y-4">
                        @csrf
                        <label class="block">
                            <span class="font-medium text-slate-600 dark:text-navy-100">Title <span class="text-error">*</span></span>
                            <input class="form-input mt-1.5 w-full rounded-lg border border-slate-300 bg-transparent px-3 py-2 placeholder:text-slate-400/70 hover:border-slate-400 focus:border-primary dark:border-navy-450 dark:hover:border-navy-400 dark:focus:border-accent" type="text" name="title" placeholder="e.g. Greeting" required>
                        </label>
                        <label class="block">
                            <span class="font-medium text-slate-600 dark:text-navy-100">Shortcut</span>
                            <p class="text-xs text-slate-400 dark:text-navy-300">Type <code class="rounded bg-slate-100 px-1 text-xs dark:bg-navy-600">/shortcut</code> in reply box to quick-insert.</p>
                            <input class="form-input mt-1.5 w-full rounded-lg border border-slate-300 bg-transparent px-3 py-2 placeholder:text-slate-400/70 hover:border-slate-400 focus:border-primary dark:border-navy-450 dark:hover:border-navy-400 dark:focus:border-accent" type="text" name="shortcut" placeholder="e.g. greeting">
                        </label>
                        <label class="block">
                            <span class="font-medium text-slate-600 dark:text-navy-100">Category</span>
                            <input class="form-input mt-1.5 w-full rounded-lg border border-slate-300 bg-transparent px-3 py-2 placeholder:text-slate-400/70 hover:border-slate-400 focus:border-primary dark:border-navy-450 dark:hover:border-navy-400 dark:focus:border-accent" type="text" name="category" placeholder="e.g. Greetings, Billing, Technical" list="category-list">
                            <datalist id="category-list">
                                @foreach($categories as $cat)
                                    <option value="{{ $cat }}">
                                @endforeach
                            </datalist>
                        </label>
                        <label class="block">
                            <span class="font-medium text-slate-600 dark:text-navy-100">Message <span class="text-error">*</span></span>
                            <textarea class="form-textarea mt-1.5 w-full rounded-lg border border-slate-300 bg-transparent px-3 py-2 placeholder:text-slate-400/70 hover:border-slate-400 focus:border-primary dark:border-navy-450 dark:hover:border-navy-400 dark:focus:border-accent" rows="4" name="message" placeholder="Type the response message..." required></textarea>
                        </label>
                        <label class="inline-flex items-center space-x-2">
                            <input class="form-switch h-5 w-10 rounded-full bg-slate-300 before:rounded-full before:bg-white checked:bg-primary checked:before:bg-white dark:bg-navy-900 dark:before:bg-navy-300 dark:checked:bg-accent dark:checked:before:bg-white" type="checkbox" name="is_shared" value="1" checked>
                            <span class="text-sm text-slate-600 dark:text-navy-100">Shared with all agents</span>
                        </label>
                        <div class="flex justify-end space-x-2 pt-2">
                            <button type="button" @click="showCreateModal = false" class="btn border border-slate-300 font-medium text-slate-800 hover:bg-slate-150 dark:border-navy-450 dark:text-navy-50 dark:hover:bg-navy-500">Cancel</button>
                            <button type="submit" class="btn bg-primary font-medium text-white hover:bg-primary-focus dark:bg-accent dark:hover:bg-accent-focus">Create</button>
                        </div>
                    </form>

                    {{-- Edit Form --}}
                    <template x-if="editItem">
                        <form method="POST" :action="`{{ url('/app/tickets/settings/canned-responses') }}/${editItem.id}`" class="mt-4 space-y-4">
                            @csrf @method('PUT')
                            <label class="block">
                                <span class="font-medium text-slate-600 dark:text-navy-100">Title <span class="text-error">*</span></span>
                                <input class="form-input mt-1.5 w-full rounded-lg border border-slate-300 bg-transparent px-3 py-2 placeholder:text-slate-400/70 hover:border-slate-400 focus:border-primary dark:border-navy-450 dark:hover:border-navy-400 dark:focus:border-accent" type="text" name="title" x-model="editItem.title" required>
                            </label>
                            <label class="block">
                                <span class="font-medium text-slate-600 dark:text-navy-100">Shortcut</span>
                                <input class="form-input mt-1.5 w-full rounded-lg border border-slate-300 bg-transparent px-3 py-2 placeholder:text-slate-400/70 hover:border-slate-400 focus:border-primary dark:border-navy-450 dark:hover:border-navy-400 dark:focus:border-accent" type="text" name="shortcut" x-model="editItem.shortcut">
                            </label>
                            <label class="block">
                                <span class="font-medium text-slate-600 dark:text-navy-100">Category</span>
                                <input class="form-input mt-1.5 w-full rounded-lg border border-slate-300 bg-transparent px-3 py-2 placeholder:text-slate-400/70 hover:border-slate-400 focus:border-primary dark:border-navy-450 dark:hover:border-navy-400 dark:focus:border-accent" type="text" name="category" x-model="editItem.category" list="category-list-edit">
                                <datalist id="category-list-edit">
                                    @foreach($categories as $cat)
                                        <option value="{{ $cat }}">
                                    @endforeach
                                </datalist>
                            </label>
                            <label class="block">
                                <span class="font-medium text-slate-600 dark:text-navy-100">Message <span class="text-error">*</span></span>
                                <textarea class="form-textarea mt-1.5 w-full rounded-lg border border-slate-300 bg-transparent px-3 py-2 placeholder:text-slate-400/70 hover:border-slate-400 focus:border-primary dark:border-navy-450 dark:hover:border-navy-400 dark:focus:border-accent" rows="4" name="message" x-model="editItem.message" required></textarea>
                            </label>
                            <label class="inline-flex items-center space-x-2">
                                <input class="form-switch h-5 w-10 rounded-full bg-slate-300 before:rounded-full before:bg-white checked:bg-primary checked:before:bg-white dark:bg-navy-900 dark:before:bg-navy-300 dark:checked:bg-accent dark:checked:before:bg-white" type="checkbox" name="is_shared" value="1" :checked="editItem.is_shared">
                                <span class="text-sm text-slate-600 dark:text-navy-100">Shared with all agents</span>
                            </label>
                            <div class="flex justify-end space-x-2 pt-2">
                                <button type="button" @click="showCreateModal = false" class="btn border border-slate-300 font-medium text-slate-800 hover:bg-slate-150 dark:border-navy-450 dark:text-navy-50 dark:hover:bg-navy-500">Cancel</button>
                                <button type="submit" class="btn bg-primary font-medium text-white hover:bg-primary-focus dark:bg-accent dark:hover:bg-accent-focus">Update</button>
                            </div>
                        </form>
                    </template>
                </div>
            </div>
        </template>
    </main>

</x-app-layout>
