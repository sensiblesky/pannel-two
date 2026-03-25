<x-app-layout title="{{ $ticket->ticket_no }} - {{ $ticket->subject }}" is-sidebar-open="true" is-header-blur="true">

    @php
        $attachmentsByMsg = $attachments->groupBy('ticket_message_id');
        $messagesJson = $messages->map(function($msg) use ($ticket, $attachmentsByMsg) {
            $msgAttachments = ($attachmentsByMsg[$msg->id] ?? collect())->map(fn($a) => [
                'id' => $a->id, 'file_name' => $a->file_name, 'file_size' => $a->file_size,
                'mime_type' => $a->mime_type, 'url' => asset('storage/' . $a->file_path),
            ])->values();
            return [
                'id' => $msg->id,
                'message' => $msg->message,
                'sender_type' => $msg->sender_type,
                'message_type' => $msg->message_type ?? 'reply',
                'is_internal' => (bool) $msg->is_internal,
                'sender_name' => $msg->sender_type === 'system'
                    ? 'System'
                    : ($msg->sender_type === 'user' ? ($msg->user_name ?? 'Agent') : ($msg->customer_sender_name ?? ($ticket->customer_name ?? 'Customer'))),
                'created_at' => $msg->created_at,
                'time' => \Carbon\Carbon::parse($msg->created_at)->format('g:i A'),
                'date' => \Carbon\Carbon::parse($msg->created_at)->format('M d, Y'),
                'initial' => $msg->sender_type === 'system'
                    ? 'S'
                    : strtoupper(substr($msg->sender_type === 'user' ? ($msg->user_name ?? 'A') : ($msg->customer_sender_name ?? 'C'), 0, 1)),
                'attachments' => $msgAttachments,
                'reads' => $messageReads[$msg->id] ?? [],
            ];
        })->values();
    @endphp

    @slot('script')
        @if(session('success'))
            <script>
                Swal.fire({ toast: true, position: 'top-end', icon: 'success', title: @json(session('success')), showConfirmButton: false, timer: 3000 });
            </script>
        @endif
        <script>
            function confirmDelete(uuid) {
                Swal.fire({
                    title: 'Are you sure?',
                    text: 'This ticket will be deleted.',
                    icon: 'warning',
                    showCancelButton: true,
                    confirmButtonColor: '#e53e3e',
                    confirmButtonText: 'Yes, delete it!',
                }).then((result) => {
                    if (result.isConfirmed) {
                        document.getElementById('delete-ticket-form').submit();
                    }
                });
            }

            function ticketChat() {
                return {
                    messages: @json($messagesJson),
                    newMessage: '',
                    messageType: 'reply',
                    sending: false,
                    selectedFiles: [],
                    showCannedPanel: false,
                    cannedSearch: '',
                    cannedResults: [],
                    cannedLoading: false,
                    customerTyping: false,
                    customerOnline: false,
                    customerLastSeen: null,
                    alertSoundUrl: null,
                    soundUnlocked: false,
                    lastMessageIds: new Set(),
                    rt: null,
                    ticketClosed: {{ $ticket->is_closed ? 'true' : 'false' }},
                    readersPopover: { open: false, msgId: null, readers: [], loading: false },

                    async showReaders(msgId) {
                        this.readersPopover = { open: true, msgId, readers: [], loading: true };
                        const res = await fetch(`{{ url('/agent/tickets/' . $ticket->uuid . '/messages') }}/${msgId}/readers`, {
                            headers: { 'X-CSRF-TOKEN': document.querySelector('meta[name=csrf-token]').content }
                        });
                        const data = await res.json();
                        const msg = this.messages.find(m => m.id === msgId);
                        if (msg) msg.reads = data;
                        this.readersPopover = { open: true, msgId, readers: data, loading: false };
                    },

                    markRead(msgId) {
                        fetch(`{{ url('/agent/tickets/' . $ticket->uuid . '/messages') }}/${msgId}/read`, {
                            method: 'POST',
                            headers: {
                                'X-CSRF-TOKEN': document.querySelector('meta[name=csrf-token]').content,
                                'Content-Type': 'application/json',
                            },
                        }).catch(() => {});
                    },

                    init() {
                        this.messages.forEach(m => this.lastMessageIds.add(m.id));
                        Alpine.store('ticketChat', this);

                        fetch('{{ route("api/alert-sound") }}?type=ticket')
                            .then(r => r.json())
                            .then(data => { if (data.url) this.alertSoundUrl = data.url; })
                            .catch(() => {});

                        const unlock = () => {
                            this.soundUnlocked = true;
                            document.removeEventListener('click', unlock);
                            document.removeEventListener('keydown', unlock);
                        };
                        document.addEventListener('click', unlock);
                        document.addEventListener('keydown', unlock);

                        this.rt = new RealtimeClient({
                            ticketId: {{ $ticket->id }},
                            ticketUuid: '{{ $ticket->uuid }}',
                            configUrl: '{{ route("api/realtime-config") }}',
                            authUrl: '{{ route("api/realtime-auth") }}',
                            pollMessagesUrl: '{{ route("agent.tickets/poll-messages", $ticket->uuid) }}',
                            typingUrl: '{{ route("agent.tickets/typing", $ticket->uuid) }}',
                            role: 'agent',
                        });

                        this.rt.onMessage((msgs) => {
                            let hasIncoming = false;
                            msgs.forEach(msg => {
                                if (!this.lastMessageIds.has(msg.id)) {
                                    msg.date = new Date(msg.created_at).toLocaleDateString('en-US', { month: 'short', day: 'numeric', year: 'numeric' });
                                    msg.reads = msg.reads || [];
                                    this.messages.push(msg);
                                    this.lastMessageIds.add(msg.id);
                                    if (msg.sender_type === 'customer') hasIncoming = true;
                                    this.markRead(msg.id);
                                }
                            });
                            if (hasIncoming) this.playAlert();
                            this.$nextTick(() => this.scrollToBottom());
                        });

                        this.rt.onTyping((data) => {
                            if (typeof data === 'boolean') {
                                this.customerTyping = data;
                            } else if (data && typeof data === 'object') {
                                this.customerTyping = !!data.typing;
                            } else {
                                this.customerTyping = !!data;
                            }
                        });

                        this.rt.onPresence((data) => {
                            if (data.customer_online !== undefined) this.customerOnline = data.customer_online;
                            if (data.customer_last_seen !== undefined) this.customerLastSeen = data.customer_last_seen;
                        });

                        this.rt.onStatusChange((data) => {
                            if (data.is_closed !== undefined && data.is_closed !== this.ticketClosed) {
                                this.ticketClosed = data.is_closed;
                            }
                        });

                        this.rt.init();
                        this.$nextTick(() => this.scrollToBottom());
                    },

                    addFiles(event) {
                        const files = Array.from(event.target.files);
                        this.selectedFiles = [...this.selectedFiles, ...files];
                        event.target.value = '';
                    },

                    removeFile(index) {
                        this.selectedFiles.splice(index, 1);
                    },

                    formatFileSize(bytes) {
                        if (bytes < 1024) return bytes + ' B';
                        if (bytes < 1048576) return (bytes / 1024).toFixed(1) + ' KB';
                        return (bytes / 1048576).toFixed(1) + ' MB';
                    },

                    async send() {
                        if ((!this.newMessage.trim() && this.selectedFiles.length === 0) || this.sending) return;
                        this.sending = true;

                        try {
                            const formData = new FormData();
                            formData.append('message', this.newMessage);
                            formData.append('message_type', this.messageType);
                            this.selectedFiles.forEach(f => formData.append('attachments[]', f));

                            const res = await fetch('{{ route("agent.tickets/ajax-reply", $ticket->uuid) }}', {
                                method: 'POST',
                                headers: {
                                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
                                    'Accept': 'application/json',
                                },
                                body: formData,
                            });
                            const data = await res.json();
                            if (data.is_closed) {
                                this.ticketClosed = true;
                                Swal.fire({ icon: 'warning', title: 'Ticket Closed', text: 'This ticket is closed. Reopen it before sending a reply.', confirmButtonColor: '#4f46e5' });
                            } else if (data.success) {
                                const msgs = data.messages || [data.message];
                                msgs.forEach(m => {
                                    m.date = new Date(m.created_at).toLocaleDateString('en-US', { month: 'short', day: 'numeric', year: 'numeric' });
                                    if (!this.lastMessageIds.has(m.id)) {
                                        this.messages.push(m);
                                        this.lastMessageIds.add(m.id);
                                    }
                                });
                                if (this.rt) this.rt.updateSince(data.message.created_at);
                                this.newMessage = '';
                                this.selectedFiles = [];
                                this.$nextTick(() => this.scrollToBottom());
                            }
                        } catch (e) {}
                        this.sending = false;
                        if (this.rt) this.rt.stopTyping();
                    },

                    onInput() {
                        if (this.rt) this.rt.sendTyping(this.newMessage.trim().length > 0);
                    },

                    async searchCanned() {
                        const q = this.cannedSearch.trim();
                        if (q.length < 1) { this.cannedResults = []; return; }
                        this.cannedLoading = true;
                        try {
                            const resp = await fetch(`{{ route('agent.tickets/canned-responses-search') }}?q=${encodeURIComponent(q)}`);
                            const data = await resp.json();
                            this.cannedResults = data;
                        } catch (e) {
                            this.cannedResults = [];
                        }
                        this.cannedLoading = false;
                    },

                    insertCanned(item) {
                        this.newMessage = item.message;
                        this.showCannedPanel = false;
                        this.cannedSearch = '';
                        this.cannedResults = [];
                        this.$nextTick(() => this.$refs.replyTextarea?.focus());
                    },

                    playAlert() {
                        if (this.alertSoundUrl && this.soundUnlocked) {
                            try {
                                const audio = new Audio(this.alertSoundUrl);
                                audio.play().catch(() => {});
                            } catch (e) {}
                        }
                    },

                    scrollToBottom() {
                        const el = this.$refs.messageContainer;
                        if (el) el.scrollTop = el.scrollHeight;
                    },

                    get customerPresence() {
                        if (this.customerOnline) return 'Online';
                        if (this.customerLastSeen) {
                            return 'Last seen ' + dayjs(this.customerLastSeen).fromNow();
                        }
                        return 'Offline';
                    },

                    destroy() {
                        if (this.rt) this.rt.destroy();
                    }
                };
            }
        </script>
    @endslot

    <main class="main-content w-full px-[var(--margin-x)] pb-8" x-data="{ showInfoPanel: false, activeTab: 'details' }">
        {{-- Page Header --}}
        <div class="flex items-center justify-between py-5 lg:py-6">
            <div class="flex items-center space-x-4">
                <a href="{{ route('agent.tickets/index') }}" class="btn size-8 rounded-full p-0 hover:bg-slate-300/20 focus:bg-slate-300/20 dark:hover:bg-navy-300/20 dark:focus:bg-navy-300/20">
                    <svg xmlns="http://www.w3.org/2000/svg" class="size-5" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7"/></svg>
                </a>
                <div>
                    <div class="flex items-center space-x-3">
                        <h2 class="text-xl font-medium text-slate-800 dark:text-navy-50 lg:text-2xl">{{ $ticket->ticket_no }}</h2>
                        <div class="badge rounded-full px-2.5 py-1 text-xs font-medium" style="background-color: {{ $ticket->status_color }}20; color: {{ $ticket->status_color }}">{{ $ticket->status_name }}</div>
                        <div class="badge rounded-full px-2.5 py-1 text-xs font-medium" style="background-color: {{ $ticket->priority_color }}20; color: {{ $ticket->priority_color }}">{{ $ticket->priority_name }}</div>
                        @if($ticket->due_at && \Carbon\Carbon::parse($ticket->due_at)->isPast() && !$ticket->is_closed)
                            <div class="badge rounded-full bg-error/10 px-2.5 py-1 text-xs font-medium text-error">Overdue</div>
                        @endif
                    </div>
                    <p class="mt-0.5 text-sm text-slate-500 dark:text-navy-300">{{ $ticket->subject }}</p>
                </div>
            </div>
            <div class="flex items-center space-x-2">
                <button @click="showInfoPanel = !showInfoPanel" class="btn size-9 rounded-full p-0 hover:bg-slate-300/20 focus:bg-slate-300/20 dark:hover:bg-navy-300/20 dark:focus:bg-navy-300/20" x-tooltip="'Ticket Info'">
                    <svg xmlns="http://www.w3.org/2000/svg" class="size-5.5" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                </button>
                <div x-data="usePopper({placement:'bottom-end',offset:4})" @click.outside="if(isShowPopper) isShowPopper = false" class="inline-flex">
                    <button x-ref="popperRef" @click="isShowPopper = !isShowPopper" class="btn size-9 rounded-full p-0 hover:bg-slate-300/20 focus:bg-slate-300/20 dark:hover:bg-navy-300/20 dark:focus:bg-navy-300/20">
                        <svg xmlns="http://www.w3.org/2000/svg" class="size-5.5" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 12h.01M12 12h.01M19 12h.01M6 12a1 1 0 11-2 0 1 1 0 012 0zm7 0a1 1 0 11-2 0 1 1 0 012 0zm7 0a1 1 0 11-2 0 1 1 0 012 0z"/></svg>
                    </button>
                    <div x-ref="popperRoot" class="popper-root" :class="isShowPopper && 'show'">
                        <div class="popper-box rounded-md border border-slate-150 bg-white py-1.5 font-inter dark:border-navy-500 dark:bg-navy-700">
                            <ul>
                                <li>
                                    <button @click="confirmDelete('{{ $ticket->uuid }}')" class="flex h-8 w-full items-center px-3 pr-8 font-medium tracking-wide text-error outline-hidden transition-all hover:bg-error/10 focus:bg-error/10">Delete Ticket</button>
                                </li>
                            </ul>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <form id="delete-ticket-form" action="{{ route('agent.tickets/destroy', $ticket->uuid) }}" method="POST" class="hidden">@csrf @method('DELETE')</form>

        <div class="flex space-x-4 lg:space-x-6">
            {{-- Main Content - Message Thread --}}
            <div class="flex-1 space-y-4 sm:space-y-5 lg:space-y-6">
                {{-- Quick Actions Bar --}}
                <div class="card">
                    <div class="flex flex-wrap items-center gap-3 p-3 sm:px-5">
                        {{-- Status --}}
                        <form method="POST" action="{{ route('agent.tickets/update-status', $ticket->uuid) }}" class="inline-flex">
                            @csrf @method('PUT')
                            <select name="status_id" onchange="this.form.submit()" class="form-select rounded-lg border border-slate-300 bg-white px-3 py-1.5 text-sm hover:border-slate-400 focus:border-primary dark:border-navy-450 dark:bg-navy-700 dark:hover:border-navy-400 dark:focus:border-accent">
                                @foreach($statuses as $s)
                                    <option value="{{ $s->id }}" @selected($ticket->status_id == $s->id)>{{ $s->name }}</option>
                                @endforeach
                            </select>
                        </form>

                        {{-- Priority --}}
                        <form method="POST" action="{{ route('agent.tickets/update-priority', $ticket->uuid) }}" class="inline-flex">
                            @csrf @method('PUT')
                            <select name="priority_id" onchange="this.form.submit()" class="form-select rounded-lg border border-slate-300 bg-white px-3 py-1.5 text-sm hover:border-slate-400 focus:border-primary dark:border-navy-450 dark:bg-navy-700 dark:hover:border-navy-400 dark:focus:border-accent">
                                @foreach($priorities as $p)
                                    <option value="{{ $p->id }}" @selected($ticket->priority_id == $p->id)>{{ $p->name }}</option>
                                @endforeach
                            </select>
                        </form>

                        {{-- Assign --}}
                        <form method="POST" action="{{ route('agent.tickets/assign', $ticket->uuid) }}" class="inline-flex">
                            @csrf @method('PUT')
                            <select name="assigned_to" onchange="if(this.value) this.form.submit()" class="form-select rounded-lg border border-slate-300 bg-white px-3 py-1.5 text-sm hover:border-slate-400 focus:border-primary dark:border-navy-450 dark:bg-navy-700 dark:hover:border-navy-400 dark:focus:border-accent">
                                <option value="">Assign to...</option>
                                @foreach($agents as $a)
                                    <option value="{{ $a->id }}" @selected($ticket->assigned_to == $a->id)>{{ $a->name }}</option>
                                @endforeach
                            </select>
                        </form>

                        {{-- Close / Reopen Button --}}
                        @php
                            $closedStatus = $statuses->firstWhere('is_closed', true);
                            $openStatus = $statuses->firstWhere('code', 'open') ?? $statuses->first();
                        @endphp
                        @if($ticket->is_closed)
                            <form method="POST" action="{{ route('agent.tickets/update-status', $ticket->uuid) }}" class="inline-flex">
                                @csrf @method('PUT')
                                <input type="hidden" name="status_id" value="{{ $openStatus->id }}">
                                <button type="submit" class="btn space-x-1.5 rounded-lg bg-success px-4 py-1.5 text-sm font-medium text-white hover:bg-success/80 focus:bg-success/80">
                                    <svg xmlns="http://www.w3.org/2000/svg" class="size-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15"/></svg>
                                    <span>Reopen Ticket</span>
                                </button>
                            </form>
                        @elseif($closedStatus)
                            <form method="POST" action="{{ route('agent.tickets/update-status', $ticket->uuid) }}" class="inline-flex" onsubmit="event.preventDefault(); Swal.fire({ title: 'Close Ticket?', text: 'Are you sure you want to close this ticket?', icon: 'warning', showCancelButton: true, confirmButtonColor: '#e53e3e', confirmButtonText: 'Yes, close it!', cancelButtonText: 'Cancel', reverseButtons: true }).then((r) => { if (r.isConfirmed) this.submit(); })">
                                @csrf @method('PUT')
                                <input type="hidden" name="status_id" value="{{ $closedStatus->id }}">
                                <button type="submit" class="btn space-x-1.5 rounded-lg bg-error px-4 py-1.5 text-sm font-medium text-white hover:bg-error/80 focus:bg-error/80">
                                    <svg xmlns="http://www.w3.org/2000/svg" class="size-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M5 13l4 4L19 7"/></svg>
                                    <span>Close Ticket</span>
                                </button>
                            </form>
                        @endif
                    </div>
                </div>

                {{-- Description (if present) --}}
                @if($ticket->description)
                    <div class="card">
                        <div class="border-b border-slate-200 p-4 dark:border-navy-500 sm:px-5">
                            <h3 class="text-sm font-medium text-slate-600 dark:text-navy-100">Original Description</h3>
                        </div>
                        <div class="p-4 sm:p-5">
                            <div class="prose max-w-none text-sm text-slate-600 dark:text-navy-200">
                                {!! nl2br(e($ticket->description)) !!}
                            </div>
                        </div>
                    </div>
                @endif

                {{-- Messages Thread (Alpine Real-time Chat) --}}
                <div class="card" x-data="ticketChat()" x-on:destroy.window="destroy()">
                    <div class="border-b border-slate-200 p-4 dark:border-navy-500 sm:px-5">
                        <div class="flex items-center justify-between">
                            <h3 class="text-sm font-medium text-slate-600 dark:text-navy-100">Conversation</h3>
                            <div class="flex items-center space-x-2">
                                <span class="size-2 rounded-full" :class="customerOnline ? 'bg-success' : 'bg-slate-300 dark:bg-navy-400'"></span>
                                <span class="text-xs text-slate-400 dark:text-navy-300" x-text="customerPresence"></span>
                            </div>
                        </div>
                    </div>

                    <div x-ref="messageContainer" class="space-y-4 p-4 sm:p-5" style="max-height: 600px; overflow-y: auto;">
                        <template x-for="(msg, idx) in messages" :key="msg.id">
                            <div>
                                <template x-if="idx === 0 || msg.date !== messages[idx-1].date">
                                    <div class="flex items-center space-x-3 py-2">
                                        <div class="h-px flex-1 bg-slate-200 dark:bg-navy-500"></div>
                                        <span class="text-xs font-medium text-slate-400 dark:text-navy-300" x-text="msg.date"></span>
                                        <div class="h-px flex-1 bg-slate-200 dark:bg-navy-500"></div>
                                    </div>
                                </template>

                                <template x-if="msg.sender_type === 'system'">
                                    <div class="flex items-center justify-center py-1.5">
                                        <div class="inline-flex items-center space-x-2 rounded-full bg-slate-100 px-4 py-1.5 dark:bg-navy-600">
                                            <svg xmlns="http://www.w3.org/2000/svg" class="size-3.5 text-slate-400 dark:text-navy-300" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                                <path stroke-linecap="round" stroke-linejoin="round" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                                            </svg>
                                            <span class="text-xs font-medium text-slate-500 dark:text-navy-200" x-text="msg.message"></span>
                                            <span class="text-xs text-slate-400 dark:text-navy-300" x-text="msg.time"></span>
                                        </div>
                                    </div>
                                </template>

                                <template x-if="msg.is_internal">
                                    <div class="flex items-start space-x-2.5">
                                        <div class="avatar flex size-8 shrink-0">
                                            <div class="is-initial rounded-full bg-warning/10 text-xs font-medium uppercase text-warning" x-text="msg.initial"></div>
                                        </div>
                                        <div class="flex-1">
                                            <div class="rounded-2xl rounded-tl-none border-2 border-dashed border-warning/30 bg-warning/5 p-3 dark:bg-warning/10">
                                                <div class="mb-1 flex items-center space-x-2">
                                                    <span class="text-xs font-semibold text-warning">Internal Note</span>
                                                    <span class="text-xs text-slate-400 dark:text-navy-300" x-text="'by ' + msg.sender_name"></span>
                                                </div>
                                                <div class="text-sm text-slate-700 dark:text-navy-100" x-html="msg.message.replace(/\n/g, '<br>')"></div>
                                                <template x-if="msg.attachments && msg.attachments.length > 0">
                                                    <div class="mt-2 space-y-1">
                                                        <template x-for="att in msg.attachments" :key="att.id">
                                                            <a :href="att.url" target="_blank" class="flex items-center space-x-2 rounded-lg bg-warning/10 px-2.5 py-1.5 text-xs transition-colors hover:bg-warning/20">
                                                                <svg xmlns="http://www.w3.org/2000/svg" class="size-3.5 text-warning" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M15.172 7l-6.586 6.586a2 2 0 102.828 2.828l6.414-6.586a4 4 0 00-5.656-5.656l-6.415 6.585a6 6 0 108.486 8.486L20.5 13" /></svg>
                                                                <span class="max-w-[160px] truncate text-slate-600 dark:text-navy-200" x-text="att.file_name"></span>
                                                            </a>
                                                        </template>
                                                    </div>
                                                </template>
                                            </div>
                                            <p class="mt-1 text-xs text-slate-400 dark:text-navy-300" x-text="msg.time"></p>
                                        </div>
                                    </div>
                                </template>

                                <template x-if="!msg.is_internal && msg.sender_type === 'user'">
                                    <div class="flex items-start justify-end space-x-2.5">
                                        <div class="flex-1 text-right">
                                            <div class="inline-block text-left">
                                                <div class="rounded-2xl rounded-tr-none bg-info/10 p-3 text-slate-700 shadow-xs dark:bg-accent dark:text-white">
                                                    <div class="text-sm" x-html="msg.message.replace(/\n/g, '<br>')"></div>
                                                    <template x-if="msg.attachments && msg.attachments.length > 0">
                                                        <div class="mt-2 space-y-1">
                                                            <template x-for="att in msg.attachments" :key="att.id">
                                                                <a :href="att.url" target="_blank" class="flex items-center space-x-2 rounded-lg bg-white/30 px-2.5 py-1.5 text-xs transition-colors hover:bg-white/50 dark:bg-white/10 dark:hover:bg-white/20">
                                                                    <svg xmlns="http://www.w3.org/2000/svg" class="size-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M15.172 7l-6.586 6.586a2 2 0 102.828 2.828l6.414-6.586a4 4 0 00-5.656-5.656l-6.415 6.585a6 6 0 108.486 8.486L20.5 13" /></svg>
                                                                    <span class="max-w-[160px] truncate" x-text="att.file_name"></span>
                                                                </a>
                                                            </template>
                                                        </div>
                                                    </template>
                                                </div>
                                                <div class="mt-1 flex items-center justify-end space-x-2">
                                                    <p class="text-xs text-slate-400 dark:text-navy-300">
                                                        <span x-text="msg.sender_name"></span> &middot; <span x-text="msg.time"></span>
                                                    </p>
                                                    {{-- Read receipt badge --}}
                                                    <button type="button" @click="showReaders(msg.id)"
                                                        class="inline-flex items-center space-x-1 text-xs text-slate-400 hover:text-info transition-colors dark:text-navy-300 dark:hover:text-accent-light">
                                                        <svg xmlns="http://www.w3.org/2000/svg" class="size-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                                            <path stroke-linecap="round" stroke-linejoin="round" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/>
                                                            <path stroke-linecap="round" stroke-linejoin="round" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"/>
                                                        </svg>
                                                        <span x-text="msg.reads ? msg.reads.length : 0"></span>
                                                    </button>
                                                </div>
                                            </div>
                                        </div>
                                        <div class="avatar flex size-8 shrink-0">
                                            <div class="is-initial rounded-full bg-info/10 text-xs font-medium uppercase text-info" x-text="msg.initial"></div>
                                        </div>
                                    </div>
                                </template>

                                <template x-if="!msg.is_internal && msg.sender_type !== 'user' && msg.sender_type !== 'system'">
                                    <div class="flex items-start space-x-2.5">
                                        <div class="avatar flex size-8 shrink-0">
                                            <div class="is-initial rounded-full bg-primary/10 text-xs font-medium uppercase text-primary dark:bg-accent/10 dark:text-accent-light" x-text="msg.initial"></div>
                                        </div>
                                        <div class="flex-1">
                                            <div class="rounded-2xl rounded-tl-none bg-white p-3 text-slate-700 shadow-xs dark:bg-navy-700 dark:text-navy-100">
                                                <div class="text-sm" x-html="msg.message.replace(/\n/g, '<br>')"></div>
                                                <template x-if="msg.attachments && msg.attachments.length > 0">
                                                    <div class="mt-2 space-y-1">
                                                        <template x-for="att in msg.attachments" :key="att.id">
                                                            <a :href="att.url" target="_blank" class="flex items-center space-x-2 rounded-lg bg-slate-100 px-2.5 py-1.5 text-xs transition-colors hover:bg-slate-200 dark:bg-navy-600 dark:hover:bg-navy-500">
                                                                <svg xmlns="http://www.w3.org/2000/svg" class="size-3.5 text-slate-400" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M15.172 7l-6.586 6.586a2 2 0 102.828 2.828l6.414-6.586a4 4 0 00-5.656-5.656l-6.415 6.585a6 6 0 108.486 8.486L20.5 13" /></svg>
                                                                <span class="max-w-[160px] truncate text-slate-600 dark:text-navy-200" x-text="att.file_name"></span>
                                                            </a>
                                                        </template>
                                                    </div>
                                                </template>
                                            </div>
                                            <p class="mt-1 text-xs text-slate-400 dark:text-navy-300">
                                                <span x-text="msg.sender_name"></span> &middot; <span x-text="msg.time"></span>
                                            </p>
                                        </div>
                                    </div>
                                </template>
                            </div>
                        </template>

                        <template x-if="messages.length === 0">
                            <div class="flex flex-col items-center py-8">
                                <svg xmlns="http://www.w3.org/2000/svg" class="size-12 text-slate-300 dark:text-navy-400" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1" d="M8 12h.01M12 12h.01M16 12h.01M21 12c0 4.418-4.03 8-9 8a9.863 9.863 0 01-4.255-.949L3 20l1.395-3.72C3.512 15.042 3 13.574 3 12c0-4.418 4.03-8 9-8s9 3.582 9 8z"/></svg>
                                <p class="mt-2 text-sm text-slate-400 dark:text-navy-300">No messages yet. Start the conversation below.</p>
                            </div>
                        </template>

                        <div x-show="customerTyping" x-transition class="flex items-center space-x-2 py-1">
                            <div class="flex space-x-1">
                                <span class="size-1.5 animate-bounce rounded-full bg-slate-400" style="animation-delay: -0.3s"></span>
                                <span class="size-1.5 animate-bounce rounded-full bg-slate-400" style="animation-delay: -0.15s"></span>
                                <span class="size-1.5 animate-bounce rounded-full bg-slate-400"></span>
                            </div>
                            <span class="text-xs text-slate-400 dark:text-navy-300">Customer is typing...</span>
                        </div>
                    </div>

                    <div x-show="!ticketClosed" x-cloak>
                        <div class="border-t border-slate-200 p-4 dark:border-navy-500 sm:p-5">
                            <div class="mb-3 flex space-x-1 rounded-lg bg-slate-100 p-1 dark:bg-navy-600">
                                <button type="button" @click="messageType = 'reply'" :class="messageType === 'reply' ? 'bg-white shadow-sm dark:bg-navy-500 text-slate-700 dark:text-navy-100' : 'text-slate-500 dark:text-navy-300'" class="flex-1 rounded-md px-3 py-1.5 text-sm font-medium transition-colors">
                                    Reply
                                </button>
                                <button type="button" @click="messageType = 'note'" :class="messageType === 'note' ? 'bg-white shadow-sm dark:bg-navy-500 text-warning' : 'text-slate-500 dark:text-navy-300'" class="flex-1 rounded-md px-3 py-1.5 text-sm font-medium transition-colors">
                                    Internal Note
                                </button>
                            </div>

                            <div class="relative">
                                <textarea x-ref="replyTextarea" x-model="newMessage" @input="onInput()" @keydown.meta.enter="send()" @keydown.ctrl.enter="send()" rows="3"
                                          class="form-textarea w-full rounded-lg border border-slate-300 bg-transparent px-3 py-2 placeholder:text-slate-400/70 hover:border-slate-400 focus:border-primary dark:border-navy-450 dark:hover:border-navy-400 dark:focus:border-accent"
                                          :placeholder="messageType === 'note' ? 'Write an internal note (not visible to customer)...' : 'Type your reply...'"></textarea>
                            </div>

                            <template x-if="selectedFiles.length > 0">
                                <div class="mt-2 flex flex-wrap gap-2">
                                    <template x-for="(file, idx) in selectedFiles" :key="idx">
                                        <div class="flex items-center space-x-1.5 rounded-lg bg-slate-100 px-2.5 py-1.5 text-xs dark:bg-navy-600">
                                            <svg xmlns="http://www.w3.org/2000/svg" class="size-3.5 text-slate-400" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M15.172 7l-6.586 6.586a2 2 0 102.828 2.828l6.414-6.586a4 4 0 00-5.656-5.656l-6.415 6.585a6 6 0 108.486 8.486L20.5 13" /></svg>
                                            <span class="max-w-[120px] truncate text-slate-600 dark:text-navy-200" x-text="file.name"></span>
                                            <span class="text-slate-400" x-text="formatFileSize(file.size)"></span>
                                            <button type="button" @click="removeFile(idx)" class="ml-1 text-slate-400 hover:text-error">
                                                <svg xmlns="http://www.w3.org/2000/svg" class="size-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12" /></svg>
                                            </button>
                                        </div>
                                    </template>
                                </div>
                            </template>

                            <div class="mt-3 flex items-center justify-between">
                                <div class="flex items-center space-x-1">
                                    <div class="relative" @click.outside="showCannedPanel = false">
                                        <button type="button" @click="showCannedPanel = !showCannedPanel; if(showCannedPanel) { cannedSearch = ''; cannedResults = []; $nextTick(() => $refs.cannedInput?.focus()); }" class="btn size-9 rounded-full p-0 hover:bg-slate-300/20 focus:bg-slate-300/20 dark:hover:bg-navy-300/20 dark:focus:bg-navy-300/20" title="Canned Responses">
                                            <svg xmlns="http://www.w3.org/2000/svg" class="size-5 text-slate-500 dark:text-navy-200" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.5"><path stroke-linecap="round" stroke-linejoin="round" d="M13 10V3L4 14h7v7l9-11h-7z" /></svg>
                                        </button>

                                        <div x-show="showCannedPanel" x-transition.origin.bottom.left class="absolute bottom-full left-0 z-50 mb-2 w-80 rounded-lg border border-slate-200 bg-white shadow-lg dark:border-navy-500 dark:bg-navy-700">
                                            <div class="max-h-60 overflow-y-auto">
                                                <template x-if="cannedLoading">
                                                    <div class="flex items-center justify-center py-6">
                                                        <div class="spinner size-5 animate-spin rounded-full border-[3px] border-primary/30 border-r-primary dark:border-accent/30 dark:border-r-accent"></div>
                                                    </div>
                                                </template>
                                                <template x-if="!cannedLoading && cannedSearch.trim().length > 0 && cannedResults.length === 0">
                                                    <p class="py-6 text-center text-xs text-slate-400 dark:text-navy-300">No responses found</p>
                                                </template>
                                                <template x-if="!cannedLoading && cannedSearch.trim().length === 0">
                                                    <p class="py-6 text-center text-xs text-slate-400 dark:text-navy-300">Type to search responses...</p>
                                                </template>
                                                <template x-for="item in cannedResults" :key="item.id">
                                                    <button type="button" @click="insertCanned(item)" class="flex w-full flex-col px-3 py-2.5 text-left transition-colors hover:bg-slate-50 dark:hover:bg-navy-600">
                                                        <div class="flex items-center space-x-2">
                                                            <span class="text-sm font-medium text-slate-700 dark:text-navy-100" x-text="item.title"></span>
                                                            <template x-if="item.shortcut">
                                                                <code class="rounded bg-slate-100 px-1 py-0.5 text-[10px] font-mono text-slate-500 dark:bg-navy-600 dark:text-navy-300" x-text="'/' + item.shortcut"></code>
                                                            </template>
                                                        </div>
                                                        <span class="mt-0.5 line-clamp-2 text-xs text-slate-400 dark:text-navy-300" x-text="item.message"></span>
                                                    </button>
                                                </template>
                                            </div>
                                            <div class="border-t border-slate-200 p-3 dark:border-navy-500">
                                                <div class="flex items-center space-x-2">
                                                    <svg xmlns="http://www.w3.org/2000/svg" class="size-4 text-slate-400" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.5"><path stroke-linecap="round" stroke-linejoin="round" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z" /></svg>
                                                    <input x-ref="cannedInput" x-model="cannedSearch" @input.debounce.300ms="searchCanned()" type="text" placeholder="Search canned responses..." class="w-full bg-transparent text-sm text-slate-700 placeholder:text-slate-400 outline-none dark:text-navy-100 dark:placeholder:text-navy-300">
                                                </div>
                                            </div>
                                        </div>
                                    </div>

                                    <input type="file" x-ref="fileInput" @change="addFiles($event)" multiple class="hidden">
                                    <button type="button" @click="$refs.fileInput.click()" class="btn size-9 rounded-full p-0 hover:bg-slate-300/20 focus:bg-slate-300/20 dark:hover:bg-navy-300/20 dark:focus:bg-navy-300/20" title="Attach Files">
                                        <svg xmlns="http://www.w3.org/2000/svg" class="size-5 text-slate-500 dark:text-navy-200" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.5"><path stroke-linecap="round" stroke-linejoin="round" d="M15.172 7l-6.586 6.586a2 2 0 102.828 2.828l6.414-6.586a4 4 0 00-5.656-5.656l-6.415 6.585a6 6 0 108.486 8.486L20.5 13" /></svg>
                                    </button>
                                </div>

                                <button type="button" @click="send()" :disabled="sending || (!newMessage.trim() && selectedFiles.length === 0)" class="btn space-x-2 bg-primary font-medium text-white shadow-lg shadow-primary/50 hover:bg-primary-focus focus:bg-primary-focus active:bg-primary-focus/90 dark:bg-accent dark:shadow-accent/50 dark:hover:bg-accent-focus dark:focus:bg-accent-focus dark:active:bg-accent/90 disabled:opacity-50 disabled:cursor-not-allowed">
                                    <span x-text="sending ? 'Sending...' : (messageType === 'note' ? 'Add Note' : 'Send Reply')"></span>
                                    <svg xmlns="http://www.w3.org/2000/svg" class="size-5" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 19l9 2-9-18-9 18 9-2zm0 0v-8"/></svg>
                                </button>
                            </div>
                        </div>
                    </div>
                    <div x-show="ticketClosed" x-cloak>
                        <div class="border-t border-slate-200 p-4 dark:border-navy-500 sm:p-5">
                            <p class="text-center text-sm text-slate-400 dark:text-navy-300">This ticket is closed. Reopen it to reply.</p>
                        </div>
                    </div>
                </div>
            </div>

            {{-- Right Sidebar Panel --}}
            <div class="hidden w-80 shrink-0 xl:block" x-show="true" x-cloak>
                <div class="card sticky top-20 space-y-0">
                    <div class="flex border-b border-slate-200 dark:border-navy-500">
                        <button @click="activeTab = 'details'" :class="activeTab === 'details' ? 'border-b-2 border-primary text-primary dark:border-accent dark:text-accent-light' : 'text-slate-500 dark:text-navy-300'" class="flex-1 px-3 py-3 text-xs font-medium transition-colors">Details</button>
                        <button @click="activeTab = 'timeline'" :class="activeTab === 'timeline' ? 'border-b-2 border-primary text-primary dark:border-accent dark:text-accent-light' : 'text-slate-500 dark:text-navy-300'" class="flex-1 px-3 py-3 text-xs font-medium transition-colors">Timeline</button>
                        <button @click="activeTab = 'files'" :class="activeTab === 'files' ? 'border-b-2 border-primary text-primary dark:border-accent dark:text-accent-light' : 'text-slate-500 dark:text-navy-300'" class="flex-1 px-3 py-3 text-xs font-medium transition-colors">Files ({{ $attachments->count() }})</button>
                    </div>

                    <div x-show="activeTab === 'details'" class="is-scrollbar-hidden space-y-4 overflow-y-auto p-4 sm:p-5" style="max-height: calc(100vh - 220px);">
                        <div>
                            <p class="text-xs font-medium uppercase text-slate-400 dark:text-navy-300">Customer</p>
                            @if($ticket->customer_name && trim($ticket->customer_name))
                                <div class="mt-2 flex items-center space-x-3">
                                    <div class="avatar flex size-10">
                                        <div class="is-initial rounded-full bg-primary/10 text-sm font-medium uppercase text-primary dark:bg-accent/10 dark:text-accent-light">{{ strtoupper(substr(trim($ticket->customer_name), 0, 1)) }}</div>
                                    </div>
                                    <div>
                                        <p class="font-medium text-slate-700 dark:text-navy-100">{{ trim($ticket->customer_name) }}</p>
                                        <p class="text-xs text-slate-400 dark:text-navy-300">{{ $ticket->customer_email }}</p>
                                        @if($ticket->customer_phone)
                                            <p class="text-xs text-slate-400 dark:text-navy-300">{{ $ticket->customer_phone }}</p>
                                        @endif
                                    </div>
                                </div>
                            @else
                                <p class="mt-1 text-sm italic text-slate-400 dark:text-navy-300">No customer linked</p>
                            @endif
                        </div>

                        <div class="h-px bg-slate-200 dark:bg-navy-500"></div>

                        <div class="space-y-3">
                            <div class="flex items-center justify-between">
                                <span class="text-xs text-slate-400 dark:text-navy-300">Status</span>
                                <div class="badge rounded-full px-2 py-0.5 text-xs" style="background-color: {{ $ticket->status_color }}20; color: {{ $ticket->status_color }}">{{ $ticket->status_name }}</div>
                            </div>
                            <div class="flex items-center justify-between">
                                <span class="text-xs text-slate-400 dark:text-navy-300">Priority</span>
                                <div class="badge rounded-full px-2 py-0.5 text-xs" style="background-color: {{ $ticket->priority_color }}20; color: {{ $ticket->priority_color }}">{{ $ticket->priority_name }}</div>
                            </div>
                            <div class="flex items-center justify-between">
                                <span class="text-xs text-slate-400 dark:text-navy-300">Category</span>
                                <span class="text-xs font-medium text-slate-600 dark:text-navy-100">{{ $ticket->category_name ?? '—' }}</span>
                            </div>
                            <div class="flex items-center justify-between">
                                <span class="text-xs text-slate-400 dark:text-navy-300">Agent</span>
                                <span class="text-xs font-medium text-slate-600 dark:text-navy-100">{{ $ticket->agent_name ?? 'Unassigned' }}</span>
                            </div>
                            <div class="flex items-center justify-between">
                                <span class="text-xs text-slate-400 dark:text-navy-300">Source</span>
                                <span class="text-xs font-medium text-slate-600 dark:text-navy-100 capitalize">{{ $ticket->source ?? '—' }}</span>
                            </div>
                            <div class="flex items-center justify-between">
                                <span class="text-xs text-slate-400 dark:text-navy-300">Department</span>
                                <span class="text-xs font-medium text-slate-600 dark:text-navy-100">{{ $ticket->department_name ?? '—' }}</span>
                            </div>
                            <div class="flex items-center justify-between">
                                <span class="text-xs text-slate-400 dark:text-navy-300">Branch</span>
                                <span class="text-xs font-medium text-slate-600 dark:text-navy-100">{{ $ticket->branch_name ?? '—' }}</span>
                            </div>
                            <div class="flex items-center justify-between">
                                <span class="text-xs text-slate-400 dark:text-navy-300">Created</span>
                                <span class="text-xs font-medium text-slate-600 dark:text-navy-100">{{ \Carbon\Carbon::parse($ticket->created_at)->format('M d, Y g:i A') }}</span>
                            </div>
                            @if($ticket->due_at)
                                <div class="flex items-center justify-between">
                                    <span class="text-xs text-slate-400 dark:text-navy-300">Due</span>
                                    <span class="text-xs font-medium {{ $ticket->due_at && \Carbon\Carbon::parse($ticket->due_at)->isPast() && !$ticket->is_closed ? 'text-error' : 'text-slate-600 dark:text-navy-100' }}">{{ \Carbon\Carbon::parse($ticket->due_at)->format('M d, Y g:i A') }}</span>
                                </div>
                            @endif
                            @if($ticket->first_response_at)
                                <div class="flex items-center justify-between">
                                    <span class="text-xs text-slate-400 dark:text-navy-300">First Response</span>
                                    <span class="text-xs font-medium text-slate-600 dark:text-navy-100">{{ \Carbon\Carbon::parse($ticket->first_response_at)->diffForHumans() }}</span>
                                </div>
                            @endif
                            @if($ticket->resolved_at)
                                <div class="flex items-center justify-between">
                                    <span class="text-xs text-slate-400 dark:text-navy-300">Resolved</span>
                                    <span class="text-xs font-medium text-success">{{ \Carbon\Carbon::parse($ticket->resolved_at)->format('M d, Y g:i A') }}</span>
                                </div>
                            @endif
                            <div class="flex items-center justify-between">
                                <span class="text-xs text-slate-400 dark:text-navy-300">Created By</span>
                                <span class="text-xs font-medium text-slate-600 dark:text-navy-100">{{ $ticket->creator_name ?? '—' }}</span>
                            </div>
                        </div>

                        <div class="h-px bg-slate-200 dark:bg-navy-500"></div>

                        <div>
                            <p class="text-xs font-medium uppercase text-slate-400 dark:text-navy-300">Tags</p>
                            <div class="mt-2 flex flex-wrap gap-1.5">
                                @forelse($ticketTags as $tag)
                                    <span class="inline-flex items-center rounded-full px-2.5 py-0.5 text-xs font-medium" style="background-color: {{ $tag->color }}15; color: {{ $tag->color }}">
                                        {{ $tag->name }}
                                        <form method="POST" action="{{ route('agent.tickets/remove-tag', [$ticket->uuid, $tag->id]) }}" class="ml-1 inline">
                                            @csrf @method('DELETE')
                                            <button type="submit" class="hover:opacity-70">&times;</button>
                                        </form>
                                    </span>
                                @empty
                                    <span class="text-xs italic text-slate-400 dark:text-navy-300">No tags</span>
                                @endforelse
                            </div>
                            @if($allTags->count() > $ticketTags->count())
                                <form method="POST" action="{{ route('agent.tickets/add-tag', $ticket->uuid) }}" class="mt-2 flex items-center space-x-1">
                                    @csrf
                                    <select name="tag_id" class="form-select rounded-lg border border-slate-300 bg-white px-2 py-1 text-xs hover:border-slate-400 focus:border-primary dark:border-navy-450 dark:bg-navy-700 dark:hover:border-navy-400 dark:focus:border-accent">
                                        @foreach($allTags as $tag)
                                            @unless($ticketTags->contains('id', $tag->id))
                                                <option value="{{ $tag->id }}">{{ $tag->name }}</option>
                                            @endunless
                                        @endforeach
                                    </select>
                                    <button type="submit" class="btn size-7 rounded-full bg-slate-150 p-0 text-xs hover:bg-slate-200 dark:bg-navy-500 dark:hover:bg-navy-450">+</button>
                                </form>
                            @endif
                        </div>

                        <div class="h-px bg-slate-200 dark:bg-navy-500"></div>

                        <div>
                            <p class="text-xs font-medium uppercase text-slate-400 dark:text-navy-300">Watchers</p>
                            <div class="mt-2 space-y-2">
                                @forelse($watchers as $watcher)
                                    <div class="flex items-center justify-between">
                                        <span class="text-xs font-medium text-slate-600 dark:text-navy-100">{{ $watcher->user_name }}</span>
                                        <form method="POST" action="{{ route('agent.tickets/remove-watcher', [$ticket->uuid, $watcher->user_id]) }}" class="inline">
                                            @csrf @method('DELETE')
                                            <button type="submit" class="text-xs text-error hover:text-error-focus">&times;</button>
                                        </form>
                                    </div>
                                @empty
                                    <span class="text-xs italic text-slate-400 dark:text-navy-300">No watchers</span>
                                @endforelse
                            </div>
                            <form method="POST" action="{{ route('agent.tickets/add-watcher', $ticket->uuid) }}" class="mt-2 flex items-center space-x-1">
                                @csrf
                                <select name="user_id" class="form-select rounded-lg border border-slate-300 bg-white px-2 py-1 text-xs hover:border-slate-400 focus:border-primary dark:border-navy-450 dark:bg-navy-700 dark:hover:border-navy-400 dark:focus:border-accent">
                                    @foreach($agents as $a)
                                        @unless($watchers->contains('user_id', $a->id))
                                            <option value="{{ $a->id }}">{{ $a->name }}</option>
                                        @endunless
                                    @endforeach
                                </select>
                                <button type="submit" class="btn size-7 rounded-full bg-slate-150 p-0 text-xs hover:bg-slate-200 dark:bg-navy-500 dark:hover:bg-navy-450">+</button>
                            </form>
                        </div>
                    </div>

                    <div x-show="activeTab === 'timeline'" x-cloak class="is-scrollbar-hidden overflow-y-auto p-4 sm:p-5" style="max-height: calc(100vh - 220px);">
                        <ol class="timeline">
                            @forelse($events as $event)
                                <li class="timeline-item">
                                    <div class="timeline-item-point rounded-full {{ $event->event_type === 'created' ? 'bg-success' : ($event->event_type === 'status_changed' ? 'bg-info' : 'bg-slate-300 dark:bg-navy-400') }}"></div>
                                    <div class="timeline-item-content">
                                        <div class="flex items-center justify-between">
                                            <p class="text-xs font-medium text-slate-600 dark:text-navy-100">
                                                @switch($event->event_type)
                                                    @case('created') Ticket created @break
                                                    @case('status_changed') Status: <span class="font-semibold">{{ $event->old_value }}</span> → <span class="font-semibold">{{ $event->new_value }}</span> @break
                                                    @case('priority_changed') Priority: <span class="font-semibold">{{ $event->old_value }}</span> → <span class="font-semibold">{{ $event->new_value }}</span> @break
                                                    @case('assigned') Assigned to <span class="font-semibold">{{ $event->new_value }}</span> @break
                                                    @case('reply_sent') Reply sent @break
                                                    @case('note_added') Internal note added @break
                                                    @default {{ ucfirst(str_replace('_', ' ', $event->event_type)) }}
                                                @endswitch
                                            </p>
                                        </div>
                                        <p class="mt-0.5 text-xs text-slate-400 dark:text-navy-300">
                                            {{ $event->actor_name ?? 'System' }} &middot; {{ \Carbon\Carbon::parse($event->created_at)->diffForHumans() }}
                                        </p>
                                    </div>
                                </li>
                            @empty
                                <p class="text-center text-xs text-slate-400 dark:text-navy-300">No events recorded</p>
                            @endforelse
                        </ol>
                    </div>

                    <div x-show="activeTab === 'files'" x-cloak class="is-scrollbar-hidden overflow-y-auto p-4 sm:p-5" style="max-height: calc(100vh - 220px);">
                        @forelse($attachments as $attachment)
                            <div class="flex items-center justify-between rounded-lg border border-slate-200 p-3 dark:border-navy-500 {{ !$loop->first ? 'mt-2' : '' }}">
                                <div class="flex items-center space-x-3">
                                    <div class="flex size-10 items-center justify-center rounded-lg bg-slate-100 dark:bg-navy-600">
                                        <svg xmlns="http://www.w3.org/2000/svg" class="size-5 text-slate-400" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 21h10a2 2 0 002-2V9.414a1 1 0 00-.293-.707l-5.414-5.414A1 1 0 0012.586 3H7a2 2 0 00-2 2v14a2 2 0 002 2z"/></svg>
                                    </div>
                                    <div>
                                        <p class="text-xs font-medium text-slate-700 dark:text-navy-100 truncate max-w-[150px]">{{ $attachment->file_name }}</p>
                                        <p class="text-xs text-slate-400 dark:text-navy-300">{{ number_format($attachment->file_size / 1024, 1) }} KB</p>
                                    </div>
                                </div>
                                <a href="{{ asset('storage/' . $attachment->file_path) }}" target="_blank" class="btn size-7 rounded-full p-0 hover:bg-slate-300/20 focus:bg-slate-300/20 dark:hover:bg-navy-300/20 dark:focus:bg-navy-300/20">
                                    <svg xmlns="http://www.w3.org/2000/svg" class="size-4" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-4l-4 4m0 0l-4-4m4 4V4"/></svg>
                                </a>
                            </div>
                        @empty
                            <p class="text-center text-xs text-slate-400 dark:text-navy-300">No attachments</p>
                        @endforelse
                    </div>
                </div>
            </div>
        </div>
    </main>

    {{-- Read Receipts Modal --}}
    <div x-data x-show="$store.ticketChat && $store.ticketChat.readersPopover && $store.ticketChat.readersPopover.open"
         style="display:none"
         x-transition.opacity
         class="fixed inset-0 z-[200] flex items-center justify-center bg-slate-900/50 p-4"
         @keydown.escape.window="$store.ticketChat && ($store.ticketChat.readersPopover.open = false)">
        <div @click.away="$store.ticketChat && ($store.ticketChat.readersPopover.open = false)"
             class="w-full max-w-sm rounded-xl bg-white shadow-xl dark:bg-navy-700">
            <div class="flex items-center justify-between border-b border-slate-200 px-5 py-4 dark:border-navy-500">
                <div class="flex items-center space-x-2">
                    <svg xmlns="http://www.w3.org/2000/svg" class="size-5 text-info" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/>
                        <path stroke-linecap="round" stroke-linejoin="round" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"/>
                    </svg>
                    <h3 class="text-base font-semibold text-slate-700 dark:text-navy-100">Read by</h3>
                </div>
                <button type="button" @click="$store.ticketChat.readersPopover.open = false"
                    class="text-slate-400 hover:text-slate-600 dark:hover:text-navy-200">
                    <svg class="size-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12"/>
                    </svg>
                </button>
            </div>
            <div class="px-5 py-4" x-data="{ get popover() { return $store.ticketChat ? $store.ticketChat.readersPopover : {}; } }">
                <template x-if="popover.loading">
                    <div class="flex items-center justify-center py-6">
                        <div class="spinner size-6 animate-spin rounded-full border-[3px] border-primary/30 border-r-primary dark:border-accent/30 dark:border-r-accent"></div>
                    </div>
                </template>
                <template x-if="!popover.loading && popover.readers && popover.readers.length === 0">
                    <p class="py-4 text-center text-sm text-slate-400 dark:text-navy-300">No one has read this message yet.</p>
                </template>
                <template x-if="!popover.loading && popover.readers && popover.readers.length > 0">
                    <div>
                        <p class="mb-2 text-xs text-slate-400 dark:text-navy-300" x-text="popover.readers.length + ' viewer' + (popover.readers.length !== 1 ? 's' : '')"></p>
                        <ul class="divide-y divide-slate-100 overflow-y-auto dark:divide-navy-600" style="max-height: 360px;">
                            <template x-for="r in popover.readers" :key="r.name">
                                <li class="flex items-center justify-between py-3">
                                    <div class="flex items-center space-x-3">
                                        <div class="avatar flex size-8 shrink-0">
                                            <div class="is-initial rounded-full bg-info/10 text-xs font-semibold uppercase text-info"
                                                 x-text="r.name ? r.name.charAt(0) : '?'"></div>
                                        </div>
                                        <span class="text-sm font-medium text-slate-700 dark:text-navy-100" x-text="r.name"></span>
                                    </div>
                                    <span class="ml-3 shrink-0 text-xs text-slate-400 dark:text-navy-300"
                                          x-text="new Date(r.read_at).toLocaleString('en-US', {month:'short',day:'numeric',hour:'numeric',minute:'2-digit'})"></span>
                                </li>
                            </template>
                        </ul>
                    </div>
                </template>
            </div>
        </div>
    </div>

</x-app-layout>
