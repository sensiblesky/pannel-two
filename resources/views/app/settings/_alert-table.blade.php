<div class="is-scrollbar-hidden min-w-full overflow-x-auto">
    <table class="is-hoverable w-full text-left">
        <thead>
            <tr>
                <th class="whitespace-nowrap rounded-tl-lg bg-slate-200 px-4 py-3 font-semibold uppercase text-slate-800 dark:bg-navy-800 dark:text-navy-100 lg:px-5">#</th>
                <th class="whitespace-nowrap bg-slate-200 px-4 py-3 font-semibold uppercase text-slate-800 dark:bg-navy-800 dark:text-navy-100 lg:px-5">Name</th>
                <th class="whitespace-nowrap bg-slate-200 px-4 py-3 font-semibold uppercase text-slate-800 dark:bg-navy-800 dark:text-navy-100 lg:px-5">File</th>
                <th class="whitespace-nowrap bg-slate-200 px-4 py-3 font-semibold uppercase text-slate-800 dark:bg-navy-800 dark:text-navy-100 lg:px-5">Size</th>
                <th class="whitespace-nowrap bg-slate-200 px-4 py-3 font-semibold uppercase text-slate-800 dark:bg-navy-800 dark:text-navy-100 lg:px-5">Status</th>
                <th class="whitespace-nowrap rounded-tr-lg bg-slate-200 px-4 py-3 font-semibold uppercase text-slate-800 dark:bg-navy-800 dark:text-navy-100 lg:px-5">Actions</th>
            </tr>
        </thead>
        <tbody>
            @foreach($alertList as $alert)
                <tr class="border-y border-transparent border-b-slate-200 dark:border-b-navy-500" x-data="{ playing: false }">
                    <td class="whitespace-nowrap px-4 py-3 text-sm text-slate-500 dark:text-navy-200 sm:px-5">{{ $loop->iteration }}</td>
                    <td class="whitespace-nowrap px-4 py-3 sm:px-5">
                        <div class="flex items-center space-x-3">
                            {{-- Play/Stop button --}}
                            <button type="button"
                                    @click="
                                        const audio = $refs['audio_{{ $alert->id }}'];
                                        if (playing) { audio.pause(); audio.currentTime = 0; playing = false; }
                                        else { audio.play(); playing = true; }
                                    "
                                    class="flex size-8 shrink-0 items-center justify-center rounded-full transition-colors"
                                    :class="playing ? 'bg-primary text-white dark:bg-accent' : '{{ $alert->is_default ? 'bg-success/10 text-success hover:bg-success/20' : 'bg-slate-100 text-slate-500 hover:bg-slate-200 dark:bg-navy-500 dark:text-navy-200 dark:hover:bg-navy-450' }}'"
                                    title="Play / Stop">
                                <svg x-show="!playing" xmlns="http://www.w3.org/2000/svg" class="size-4" viewBox="0 0 24 24" fill="currentColor"><path d="M8 5v14l11-7z"/></svg>
                                <svg x-show="playing" xmlns="http://www.w3.org/2000/svg" class="size-4" viewBox="0 0 24 24" fill="currentColor"><path d="M6 19h4V5H6v14zm8-14v14h4V5h-4z"/></svg>
                            </button>
                            <audio x-ref="audio_{{ $alert->id }}" preload="none" @ended="playing = false">
                                <source src="{{ \Illuminate\Support\Facades\Storage::disk('public')->url($alert->file_path) }}" type="{{ $alert->mime_type }}">
                            </audio>
                            <div class="min-w-0">
                                <p class="font-medium text-slate-700 dark:text-navy-100">{{ $alert->name }}</p>
                            </div>
                        </div>
                    </td>
                    <td class="whitespace-nowrap px-4 py-3 sm:px-5">
                        <span class="text-xs text-slate-500 dark:text-navy-200">{{ $alert->file_name }}</span>
                    </td>
                    <td class="whitespace-nowrap px-4 py-3 sm:px-5">
                        <span class="text-xs text-slate-500 dark:text-navy-200">{{ number_format($alert->file_size / 1024, 1) }} KB</span>
                    </td>
                    <td class="whitespace-nowrap px-4 py-3 sm:px-5">
                        @if($alert->is_default)
                            <div class="badge rounded-full bg-success/10 text-success dark:bg-success/15">Default</div>
                        @else
                            <div class="badge rounded-full bg-slate-200 text-slate-500 dark:bg-navy-500 dark:text-navy-200">—</div>
                        @endif
                    </td>
                    <td class="whitespace-nowrap px-4 py-3 sm:px-5">
                        <div class="flex space-x-2">
                            @if(!$alert->is_default)
                                <form method="POST" action="{{ route('config/message-alerts-default', $alert->id) }}">
                                    @csrf
                                    @method('PUT')
                                    <button type="submit" class="btn size-8 rounded-full p-0 hover:bg-slate-300/20 focus:bg-slate-300/20 active:bg-slate-300/25 dark:hover:bg-navy-300/20 dark:focus:bg-navy-300/20 dark:active:bg-navy-300/25 text-primary dark:text-accent-light" title="Set as Default">
                                        <svg xmlns="http://www.w3.org/2000/svg" class="size-4.5" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/></svg>
                                    </button>
                                </form>
                                <form method="POST" action="{{ route('config/message-alerts-destroy', $alert->id) }}"
                                      onsubmit="event.preventDefault(); Swal.fire({ title: 'Are you sure?', text: 'This alert sound will be deleted.', icon: 'warning', showCancelButton: true, confirmButtonColor: '#e53e3e', confirmButtonText: 'Yes, delete it!', reverseButtons: true }).then((r) => { if (r.isConfirmed) this.submit(); });">
                                    @csrf
                                    @method('DELETE')
                                    <button type="submit" class="btn size-8 rounded-full p-0 hover:bg-error/20 focus:bg-error/20 active:bg-error/25 text-error" title="Delete">
                                        <svg xmlns="http://www.w3.org/2000/svg" class="size-4.5" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/></svg>
                                    </button>
                                </form>
                            @else
                                <span class="flex h-8 items-center text-xs text-success italic">
                                    <svg xmlns="http://www.w3.org/2000/svg" class="mr-1 size-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/></svg>
                                    Active
                                </span>
                            @endif
                        </div>
                    </td>
                </tr>
            @endforeach
        </tbody>
    </table>
</div>
