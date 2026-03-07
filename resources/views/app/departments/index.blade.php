<x-app-layout title="Departments" is-sidebar-open="true" is-header-blur="true">
    @slot('script')
    <script>
        function confirmDelete(departmentUuid, departmentName) {
            Swal.fire({
                title: 'Delete Department?',
                html: `<p class="text-slate-500">Are you sure you want to delete <strong>${departmentName}</strong>?<br>This action cannot be undone.</p>`,
                icon: 'warning',
                showCancelButton: true,
                confirmButtonColor: '#ff5724',
                cancelButtonColor: '#64748b',
                confirmButtonText: 'Yes, delete',
                cancelButtonText: 'Cancel',
                reverseButtons: true,
                customClass: { popup: 'rounded-lg' }
            }).then((result) => {
                if (result.isConfirmed) {
                    document.getElementById('delete-department-' + departmentUuid).submit();
                }
            });
        }

        document.addEventListener('DOMContentLoaded', function () {
            @if (session('success'))
                Swal.fire({
                    title: 'Success!',
                    text: @json(session('success')),
                    icon: 'success',
                    confirmButtonColor: '#4f46e5',
                    confirmButtonText: 'OK',
                    timer: 3000,
                    timerProgressBar: true,
                    customClass: { popup: 'rounded-lg' }
                });
            @endif
        });
    </script>
    @endslot
    <main class="main-content w-full px-[var(--margin-x)] pb-8">
        <div class="flex items-center space-x-4 py-5 lg:py-6">
            <h2 class="text-xl font-medium text-slate-800 dark:text-navy-50 lg:text-2xl">
                Departments
            </h2>
            <div class="hidden h-full py-1 sm:flex">
                <div class="h-full w-px bg-slate-300 dark:bg-navy-600"></div>
            </div>
            <ul class="hidden flex-wrap items-center space-x-2 sm:flex">
                <li class="flex items-center space-x-2">
                    <a class="text-primary transition-colors hover:text-primary-focus dark:text-accent-light dark:hover:text-accent"
                        href="{{ route('config/departments') }}">Configuration</a>
                    <svg x-ignore xmlns="http://www.w3.org/2000/svg" class="size-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7" />
                    </svg>
                </li>
                <li>Departments</li>
            </ul>
        </div>

        <div class="grid grid-cols-1 gap-4 sm:gap-5 lg:gap-6">
            <div>
                <div class="flex items-center justify-between">
                    <h2 class="text-base font-medium tracking-wide text-slate-700 line-clamp-1 dark:text-navy-100">
                        Departments Table
                    </h2>
                    <a href="{{ route('config/departments-create') }}"
                        class="btn space-x-2 bg-primary font-medium text-white shadow-lg shadow-primary/50 hover:bg-primary-focus focus:bg-primary-focus active:bg-primary-focus/90 dark:bg-accent dark:shadow-accent/50 dark:hover:bg-accent-focus dark:focus:bg-accent-focus dark:active:bg-accent/90">
                        <svg xmlns="http://www.w3.org/2000/svg" class="size-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M12 4v16m8-8H4" />
                        </svg>
                        <span>Add Department</span>
                    </a>
                </div>

                <div class="card mt-3">
                    <div class="flex flex-col space-y-4 px-4 py-4 sm:flex-row sm:items-center sm:space-y-0 sm:space-x-4 sm:px-5">
                        <form method="GET" action="{{ route('config/departments') }}" class="flex flex-1 flex-wrap items-center gap-3">
                            <label class="relative flex flex-1">
                                <input name="search" value="{{ request('search') }}"
                                    class="form-input peer w-full rounded-lg border border-slate-300 bg-transparent px-3 py-2 pl-9 placeholder:text-slate-400/70 hover:border-slate-400 focus:border-primary dark:border-navy-450 dark:hover:border-navy-400 dark:focus:border-accent"
                                    placeholder="Search departments..." type="text" />
                                <span class="pointer-events-none absolute flex h-full w-10 items-center justify-center text-slate-400 peer-focus:text-primary dark:text-navy-300 dark:peer-focus:text-accent">
                                    <svg xmlns="http://www.w3.org/2000/svg" class="size-4.5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.5">
                                        <path stroke-linecap="round" stroke-linejoin="round" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z" />
                                    </svg>
                                </span>
                            </label>
                            <label class="block">
                                <select name="status"
                                    class="form-select rounded-lg border border-slate-300 bg-white px-3 py-2 hover:border-slate-400 focus:border-primary dark:border-navy-450 dark:bg-navy-700 dark:hover:border-navy-400 dark:focus:border-accent">
                                    <option value="">All Status</option>
                                    <option value="1" {{ request('status') === '1' ? 'selected' : '' }}>Active</option>
                                    <option value="0" {{ request('status') === '0' ? 'selected' : '' }}>Inactive</option>
                                </select>
                            </label>
                            <label class="block">
                                <select name="branch_id"
                                    class="form-select rounded-lg border border-slate-300 bg-white px-3 py-2 hover:border-slate-400 focus:border-primary dark:border-navy-450 dark:bg-navy-700 dark:hover:border-navy-400 dark:focus:border-accent">
                                    <option value="">All Branches</option>
                                    @foreach ($branches as $branch)
                                        <option value="{{ $branch->id }}" {{ request('branch_id') == $branch->id ? 'selected' : '' }}>{{ $branch->name }}</option>
                                    @endforeach
                                </select>
                            </label>
                            <button type="submit"
                                class="btn bg-slate-150 font-medium text-slate-800 hover:bg-slate-200 focus:bg-slate-200 active:bg-slate-200/80 dark:bg-navy-500 dark:text-navy-50 dark:hover:bg-navy-450 dark:focus:bg-navy-450 dark:active:bg-navy-450/90">
                                Filter
                            </button>
                            @if(request()->hasAny(['search', 'status', 'branch_id']))
                                <a href="{{ route('config/departments') }}"
                                    class="btn bg-slate-150 font-medium text-slate-800 hover:bg-slate-200 focus:bg-slate-200 active:bg-slate-200/80 dark:bg-navy-500 dark:text-navy-50 dark:hover:bg-navy-450 dark:focus:bg-navy-450 dark:active:bg-navy-450/90">
                                    Clear
                                </a>
                            @endif
                        </form>
                    </div>

                    <div class="is-scrollbar-hidden min-w-full overflow-x-auto">
                        <table class="is-hoverable w-full text-left">
                            <thead>
                                <tr>
                                    <th class="whitespace-nowrap rounded-tl-lg bg-slate-200 px-4 py-3 font-semibold uppercase text-slate-800 dark:bg-navy-800 dark:text-navy-100 lg:px-5">#</th>
                                    <th class="whitespace-nowrap bg-slate-200 px-4 py-3 font-semibold uppercase text-slate-800 dark:bg-navy-800 dark:text-navy-100 lg:px-5">Name</th>
                                    <th class="whitespace-nowrap bg-slate-200 px-4 py-3 font-semibold uppercase text-slate-800 dark:bg-navy-800 dark:text-navy-100 lg:px-5">Code</th>
                                    <th class="whitespace-nowrap bg-slate-200 px-4 py-3 font-semibold uppercase text-slate-800 dark:bg-navy-800 dark:text-navy-100 lg:px-5">Branch</th>
                                    <th class="whitespace-nowrap bg-slate-200 px-4 py-3 font-semibold uppercase text-slate-800 dark:bg-navy-800 dark:text-navy-100 lg:px-5">Status</th>
                                    <th class="whitespace-nowrap rounded-tr-lg bg-slate-200 px-4 py-3 font-semibold uppercase text-slate-800 dark:bg-navy-800 dark:text-navy-100 lg:px-5">Action</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse ($departments as $department)
                                    <tr class="border-y border-transparent border-b-slate-200 dark:border-b-navy-500">
                                        <td class="whitespace-nowrap px-4 py-3 sm:px-5">{{ $departments->firstItem() + $loop->index }}</td>
                                        <td class="whitespace-nowrap px-3 py-3 font-medium text-slate-700 dark:text-navy-100 lg:px-5">
                                            <div class="flex items-center space-x-3">
                                                <div class="avatar flex size-10">
                                                    <div class="is-initial rounded-full bg-primary/10 text-primary dark:bg-accent/15 dark:text-accent-light">
                                                        {{ strtoupper(substr($department->name, 0, 1)) }}
                                                    </div>
                                                </div>
                                                <div>
                                                    <p>{{ $department->name }}</p>
                                                    @if($department->description)
                                                        <p class="text-xs text-slate-400 line-clamp-1">{{ $department->description }}</p>
                                                    @endif
                                                </div>
                                            </div>
                                        </td>
                                        <td class="whitespace-nowrap px-4 py-3 sm:px-5">
                                            {{ $department->code ?? '-' }}
                                        </td>
                                        <td class="whitespace-nowrap px-4 py-3 sm:px-5">
                                            {{ $department->branch?->name ?? '-' }}
                                        </td>
                                        <td class="whitespace-nowrap px-4 py-3 sm:px-5">
                                            @if ($department->status)
                                                <div class="badge rounded-full bg-success/10 text-success dark:bg-success/15">Active</div>
                                            @else
                                                <div class="badge rounded-full bg-error/10 text-error dark:bg-error/15">Inactive</div>
                                            @endif
                                        </td>
                                        <td class="whitespace-nowrap px-4 py-3 sm:px-5">
                                            <div x-data="usePopper({placement:'bottom-end',offset:4})"
                                                @click.outside="if(isShowPopper) isShowPopper = false" class="inline-flex">
                                                <button x-ref="popperRef" @click="isShowPopper = !isShowPopper"
                                                    class="btn size-8 rounded-full p-0 hover:bg-slate-300/20 focus:bg-slate-300/20 active:bg-slate-300/25 dark:hover:bg-navy-300/20 dark:focus:bg-navy-300/20 dark:active:bg-navy-300/25">
                                                    <svg xmlns="http://www.w3.org/2000/svg" class="size-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                                        <path stroke-linecap="round" stroke-linejoin="round" d="M5 12h.01M12 12h.01M19 12h.01M6 12a1 1 0 11-2 0 1 1 0 012 0zm7 0a1 1 0 11-2 0 1 1 0 012 0zm7 0a1 1 0 11-2 0 1 1 0 012 0z" />
                                                    </svg>
                                                </button>
                                                <div x-ref="popperRoot" class="popper-root" :class="isShowPopper && 'show'">
                                                    <div class="popper-box rounded-md border border-slate-150 bg-white py-1.5 font-inter dark:border-navy-500 dark:bg-navy-700">
                                                        <ul>
                                                            <li>
                                                                <a href="{{ route('config/departments-edit', $department) }}"
                                                                    class="flex h-8 items-center px-3 pr-8 font-medium tracking-wide outline-hidden transition-all hover:bg-slate-100 hover:text-slate-800 focus:bg-slate-100 focus:text-slate-800 dark:hover:bg-navy-600 dark:hover:text-navy-100 dark:focus:bg-navy-600 dark:focus:text-navy-100">
                                                                    Edit
                                                                </a>
                                                            </li>
                                                            <li>
                                                                <form id="delete-department-{{ $department->uuid }}" method="POST" action="{{ route('config/departments-destroy', $department) }}">
                                                                    @csrf
                                                                    @method('DELETE')
                                                                    <button type="button"
                                                                        onclick="confirmDelete('{{ $department->uuid }}', '{{ e($department->name) }}')"
                                                                        class="flex h-8 w-full items-center px-3 pr-8 font-medium tracking-wide text-error outline-hidden transition-all hover:bg-slate-100 focus:bg-slate-100 dark:hover:bg-navy-600 dark:focus:bg-navy-600">
                                                                        Delete
                                                                    </button>
                                                                </form>
                                                            </li>
                                                        </ul>
                                                    </div>
                                                </div>
                                            </div>
                                        </td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="6" class="px-4 py-6 text-center text-slate-400 dark:text-navy-300">No departments found.</td>
                                    </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>

                    <div class="flex flex-col justify-between space-y-4 px-4 py-4 sm:flex-row sm:items-center sm:space-y-0 sm:px-5">
                        <div class="flex items-center space-x-2 text-xs-plus">
                            <span>Showing</span>
                            <span class="font-semibold">{{ $departments->firstItem() ?? 0 }} - {{ $departments->lastItem() ?? 0 }}</span>
                            <span>of {{ $departments->total() }} entries</span>
                        </div>
                        @if ($departments->hasPages())
                            <ol class="pagination">
                                <li class="rounded-l-lg bg-slate-150 dark:bg-navy-500">
                                    @if ($departments->onFirstPage())
                                        <span class="flex size-8 items-center justify-center rounded-lg text-slate-400 dark:text-navy-300">
                                            <svg xmlns="http://www.w3.org/2000/svg" class="size-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M15 19l-7-7 7-7" /></svg>
                                        </span>
                                    @else
                                        <a href="{{ $departments->previousPageUrl() }}" class="flex size-8 items-center justify-center rounded-lg text-slate-500 transition-colors hover:bg-slate-300 focus:bg-slate-300 active:bg-slate-300/80 dark:text-navy-200 dark:hover:bg-navy-450 dark:focus:bg-navy-450 dark:active:bg-navy-450/90">
                                            <svg xmlns="http://www.w3.org/2000/svg" class="size-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M15 19l-7-7 7-7" /></svg>
                                        </a>
                                    @endif
                                </li>
                                @foreach ($departments->getUrlRange(1, $departments->lastPage()) as $page => $url)
                                    <li class="bg-slate-150 dark:bg-navy-500">
                                        <a href="{{ $url }}"
                                            class="{{ $page == $departments->currentPage() ? 'flex size-8 items-center justify-center rounded-lg bg-primary font-medium text-white transition-colors hover:bg-primary-focus focus:bg-primary-focus active:bg-primary-focus/90 dark:bg-accent dark:hover:bg-accent-focus dark:focus:bg-accent-focus dark:active:bg-accent/90' : 'flex size-8 items-center justify-center rounded-lg text-slate-500 transition-colors hover:bg-slate-300 focus:bg-slate-300 active:bg-slate-300/80 dark:text-navy-200 dark:hover:bg-navy-450 dark:focus:bg-navy-450 dark:active:bg-navy-450/90' }}">
                                            {{ $page }}
                                        </a>
                                    </li>
                                @endforeach
                                <li class="rounded-r-lg bg-slate-150 dark:bg-navy-500">
                                    @if ($departments->hasMorePages())
                                        <a href="{{ $departments->nextPageUrl() }}" class="flex size-8 items-center justify-center rounded-lg text-slate-500 transition-colors hover:bg-slate-300 focus:bg-slate-300 active:bg-slate-300/80 dark:text-navy-200 dark:hover:bg-navy-450 dark:focus:bg-navy-450 dark:active:bg-navy-450/90">
                                            <svg xmlns="http://www.w3.org/2000/svg" class="size-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M9 5l7 7-7 7" /></svg>
                                        </a>
                                    @else
                                        <span class="flex size-8 items-center justify-center rounded-lg text-slate-400 dark:text-navy-300">
                                            <svg xmlns="http://www.w3.org/2000/svg" class="size-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M9 5l7 7-7 7" /></svg>
                                        </span>
                                    @endif
                                </li>
                            </ol>
                        @endif
                    </div>
                </div>
            </div>
        </div>
    </main>
</x-app-layout>
