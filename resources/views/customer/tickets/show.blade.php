<x-customer-layout title="{{ $ticket->ticket_no }} - {{ $ticket->subject }}">

    @php
        $attachmentsByMsg = $attachments->groupBy('ticket_message_id');
        $messagesJson = $messages->map(function($msg) use ($attachmentsByMsg) {
            $msgAttachments = ($attachmentsByMsg[$msg->id] ?? collect())->map(fn($a) => [
                'id' => $a->id, 'file_name' => $a->file_name, 'file_size' => $a->file_size,
                'mime_type' => $a->mime_type, 'url' => asset('storage/' . $a->file_path),
            ])->values();
            return [
                'id' => $msg->id,
                'message' => $msg->message,
                'sender_type' => $msg->sender_type,
                'sender_name' => $msg->sender_type === 'system'
                    ? 'System'
                    : ($msg->sender_type === 'customer' ? 'You' : ($msg->sender_name ?? 'Support')),
                'created_at' => $msg->created_at,
                'time' => \Carbon\Carbon::parse($msg->created_at)->format('g:i A'),
                'date' => \Carbon\Carbon::parse($msg->created_at)->format('M d, Y'),
                'initial' => strtoupper(substr($msg->sender_name ?? 'S', 0, 1)),
                'attachments' => $msgAttachments,
            ];
        })->values();
    @endphp

    @slot('script')
        <script>
            function customerTicketChat() {
                return {
                    messages: @json($messagesJson),
                    newMessage: '',
                    sending: false,
                    selectedFiles: [],
                    agentTyping: null,
                    agentOnline: false,
                    agentLastSeen: null,
                    alertSoundUrl: null,
                    soundUnlocked: false,
                    lastMessageIds: new Set(),
                    rt: null,
                    ticketClosed: {{ $ticket->is_closed ? 'true' : 'false' }},

                    init() {
                        this.messages.forEach(m => this.lastMessageIds.add(m.id));

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

                        // Initialize RealtimeClient
                        this.rt = new RealtimeClient({
                            ticketId: {{ $ticket->id }},
                            ticketUuid: '{{ $ticket->uuid }}',
                            configUrl: '{{ route("api/realtime-config") }}',
                            authUrl: '{{ route("api/realtime-auth") }}',
                            pollMessagesUrl: '{{ route("customer.tickets.poll-messages", $ticket->uuid) }}',
                            typingUrl: '{{ route("customer.tickets.typing", $ticket->uuid) }}',
                            role: 'customer',
                        });

                        this.rt.onMessage((msgs) => {
                            let hasIncoming = false;
                            msgs.forEach(msg => {
                                if (!this.lastMessageIds.has(msg.id)) {
                                    msg.date = new Date(msg.created_at).toLocaleDateString('en-US', { month: 'short', day: 'numeric', year: 'numeric' });
                                    this.messages.push(msg);
                                    this.lastMessageIds.add(msg.id);
                                    if (msg.sender_type !== 'customer') hasIncoming = true;
                                }
                            });
                            if (hasIncoming) this.playAlert();
                            this.$nextTick(() => this.scrollToBottom());
                        });

                        this.rt.onTyping((data) => {
                            if (typeof data === 'string') {
                                this.agentTyping = data;
                            } else if (data && typeof data === 'object') {
                                this.agentTyping = data.typing ? (data.sender_name || 'Agent') : null;
                            } else {
                                this.agentTyping = null;
                            }
                        });

                        this.rt.onPresence((data) => {
                            if (data.agent_online !== undefined) this.agentOnline = data.agent_online;
                            if (data.agent_last_seen !== undefined) this.agentLastSeen = data.agent_last_seen;
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
                            this.selectedFiles.forEach(f => formData.append('attachments[]', f));

                            const res = await fetch('{{ route("customer.tickets.ajax-reply", $ticket->uuid) }}', {
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
                                Swal.fire({ icon: 'warning', title: 'Ticket Closed', text: 'This ticket has been closed.', confirmButtonColor: '#4f46e5' });
                            } else if (data.success) {
                                data.message.date = new Date(data.message.created_at).toLocaleDateString('en-US', { month: 'short', day: 'numeric', year: 'numeric' });
                                this.messages.push(data.message);
                                this.lastMessageIds.add(data.message.id);
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

                    get agentPresence() {
                        if (this.agentOnline) return 'Online';
                        if (this.agentLastSeen) {
                            const d = new Date(this.agentLastSeen);
                            const now = new Date();
                            const diff = Math.floor((now - d) / 1000);
                            if (diff < 60) return 'Last seen just now';
                            if (diff < 3600) return 'Last seen ' + Math.floor(diff / 60) + 'm ago';
                            if (diff < 86400) return 'Last seen ' + Math.floor(diff / 3600) + 'h ago';
                            return 'Last seen ' + d.toLocaleDateString();
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

    {{-- Header --}}
    <div class="flex items-center space-x-4">
        <a href="{{ route('customer.tickets') }}" class="rounded-lg p-2 text-slate-400 hover:bg-slate-100 hover:text-slate-600 dark:hover:bg-navy-600 dark:hover:text-navy-100">
            <svg xmlns="http://www.w3.org/2000/svg" class="size-5" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7"/></svg>
        </a>
        <div>
            <h2 class="text-xl font-medium text-slate-800 dark:text-navy-50">{{ $ticket->subject }}</h2>
            <div class="mt-1 flex items-center space-x-3 text-sm text-slate-400 dark:text-navy-300">
                <span>{{ $ticket->ticket_no }}</span>
                <span>&middot;</span>
                <span>{{ \Carbon\Carbon::parse($ticket->created_at)->format('M d, Y \a\t h:i A') }}</span>
            </div>
        </div>
    </div>

    <div class="mt-5 grid grid-cols-1 gap-5 lg:grid-cols-3" x-data="customerTicketChat()">
        {{-- Conversation --}}
        <div class="lg:col-span-2">
            {{-- Description --}}
            @if ($ticket->description)
                <div class="card p-4 sm:p-5">
                    <h3 class="text-sm font-medium text-slate-700 dark:text-navy-100">Description</h3>
                    <div class="mt-2 text-sm leading-relaxed text-slate-600 dark:text-navy-200">{!! nl2br(e($ticket->description)) !!}</div>
                </div>
            @endif

            {{-- Real-time Messages --}}
            <div class="card mt-4">
                <div class="flex items-center justify-between border-b border-slate-200 p-4 dark:border-navy-500 sm:px-5">
                    <h3 class="text-sm font-medium text-slate-600 dark:text-navy-100">Messages</h3>
                    @if($ticket->agent_name)
                        <div class="flex items-center space-x-2">
                            <span class="size-2 rounded-full" :class="agentOnline ? 'bg-success' : 'bg-slate-300 dark:bg-navy-400'"></span>
                            <span class="text-xs text-slate-400 dark:text-navy-300">{{ $ticket->agent_name }} &middot; <span x-text="agentPresence"></span></span>
                        </div>
                    @endif
                </div>

                <div x-ref="messageContainer" class="space-y-3 p-4 sm:p-5" style="max-height: 600px; overflow-y: auto;">
                    <template x-for="(msg, idx) in messages" :key="msg.id">
                        <div>
                            {{-- Date separator --}}
                            <template x-if="idx === 0 || msg.date !== messages[idx-1].date">
                                <div class="flex items-center space-x-3 py-2">
                                    <div class="h-px flex-1 bg-slate-200 dark:bg-navy-500"></div>
                                    <span class="text-xs font-medium text-slate-400 dark:text-navy-300" x-text="msg.date"></span>
                                    <div class="h-px flex-1 bg-slate-200 dark:bg-navy-500"></div>
                                </div>
                            </template>

                            {{-- System Message (centered notification) --}}
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

                            {{-- Customer message (right) --}}
                            <template x-if="msg.sender_type === 'customer'">
                                <div class="flex items-start justify-end space-x-2.5">
                                    <div class="flex-1 text-right">
                                        <div class="inline-block text-left">
                                            <div class="rounded-2xl rounded-tr-none bg-primary/10 p-3 text-slate-700 shadow-xs dark:bg-accent dark:text-white">
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
                                            <p class="mt-1 text-right text-xs text-slate-400 dark:text-navy-300">
                                                You &middot; <span x-text="msg.time"></span>
                                            </p>
                                        </div>
                                    </div>
                                    <div class="flex size-7 shrink-0 items-center justify-center rounded-full bg-primary/10 text-xs font-medium text-primary dark:bg-accent/10 dark:text-accent-light" x-text="msg.initial"></div>
                                </div>
                            </template>

                            {{-- Agent/Support message (left) --}}
                            <template x-if="msg.sender_type !== 'customer' && msg.sender_type !== 'system'">
                                <div class="flex items-start space-x-2.5">
                                    <div class="flex size-7 shrink-0 items-center justify-center rounded-full bg-slate-200 text-xs font-medium text-slate-600 dark:bg-navy-500 dark:text-navy-200" x-text="msg.initial"></div>
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

                    {{-- Empty state --}}
                    <template x-if="messages.length === 0">
                        <div class="flex flex-col items-center py-8">
                            <svg xmlns="http://www.w3.org/2000/svg" class="size-12 text-slate-300 dark:text-navy-400" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1" d="M8 12h.01M12 12h.01M16 12h.01M21 12c0 4.418-4.03 8-9 8a9.863 9.863 0 01-4.255-.949L3 20l1.395-3.72C3.512 15.042 3 13.574 3 12c0-4.418 4.03-8 9-8s9 3.582 9 8z"/></svg>
                            <p class="mt-2 text-sm text-slate-400 dark:text-navy-300">No messages yet.</p>
                        </div>
                    </template>

                    {{-- Agent typing indicator --}}
                    <div x-show="agentTyping" x-transition class="flex items-center space-x-2 py-1">
                        <div class="flex space-x-1">
                            <span class="size-1.5 animate-bounce rounded-full bg-slate-400" style="animation-delay: -0.3s"></span>
                            <span class="size-1.5 animate-bounce rounded-full bg-slate-400" style="animation-delay: -0.15s"></span>
                            <span class="size-1.5 animate-bounce rounded-full bg-slate-400"></span>
                        </div>
                        <span class="text-xs text-slate-400 dark:text-navy-300" x-text="agentTyping + ' is typing...'"></span>
                    </div>
                </div>

                {{-- Reply Input (AJAX) --}}
                <div x-show="!ticketClosed" x-cloak>
                    <div class="border-t border-slate-200 p-4 dark:border-navy-500 sm:p-5">
                        <div class="relative">
                            <textarea x-model="newMessage" @input="onInput()" @keydown.meta.enter="send()" @keydown.ctrl.enter="send()" rows="3"
                                      class="form-textarea w-full rounded-lg border border-slate-300 bg-transparent px-3 py-2 placeholder:text-slate-400 hover:border-slate-400 focus:border-primary dark:border-navy-450 dark:hover:border-navy-400 dark:focus:border-accent"
                                      placeholder="Type your reply..."></textarea>
                        </div>

                        @if($allowCustomerAttachments)
                        {{-- File Preview --}}
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
                        @endif

                        <div class="mt-3 flex items-center justify-between">
                            <div class="flex items-center space-x-2">
                                @if($allowCustomerAttachments)
                                <input type="file" x-ref="fileInput" @change="addFiles($event)" multiple class="hidden">
                                <button type="button" @click="$refs.fileInput.click()" class="btn size-9 rounded-full p-0 hover:bg-slate-300/20 focus:bg-slate-300/20 dark:hover:bg-navy-300/20 dark:focus:bg-navy-300/20" title="Attach Files">
                                    <svg xmlns="http://www.w3.org/2000/svg" class="size-5 text-slate-500 dark:text-navy-200" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.5"><path stroke-linecap="round" stroke-linejoin="round" d="M15.172 7l-6.586 6.586a2 2 0 102.828 2.828l6.414-6.586a4 4 0 00-5.656-5.656l-6.415 6.585a6 6 0 108.486 8.486L20.5 13" /></svg>
                                </button>
                                @endif

                                @if($allowCustomerClose)
                                <form method="POST" action="{{ route('customer.tickets.close', $ticket->uuid) }}" class="inline-flex" onsubmit="event.preventDefault(); Swal.fire({ title: 'Close Ticket?', text: 'Are you sure you want to close this ticket?', icon: 'warning', showCancelButton: true, confirmButtonColor: '#e53e3e', confirmButtonText: 'Yes, close it!', cancelButtonText: 'Cancel', reverseButtons: true }).then((r) => { if (r.isConfirmed) this.submit(); })">
                                    @csrf
                                    <button type="submit" class="btn size-9 rounded-full p-0 hover:bg-slate-300/20 focus:bg-slate-300/20 dark:hover:bg-navy-300/20 dark:focus:bg-navy-300/20" title="Close Ticket">
                                        <svg xmlns="http://www.w3.org/2000/svg" class="size-5 text-error" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.5"><path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12"/></svg>
                                    </button>
                                </form>
                                @endif
                            </div>
                            <button type="button" @click="send()" :disabled="sending || (!newMessage.trim() && selectedFiles.length === 0)" class="btn bg-primary font-medium text-white hover:bg-primary-focus focus:bg-primary-focus active:bg-primary-focus/90 dark:bg-accent dark:hover:bg-accent-focus dark:focus:bg-accent-focus dark:active:bg-accent/90 disabled:opacity-50 disabled:cursor-not-allowed">
                                <span x-text="sending ? 'Sending...' : 'Send Reply'"></span>
                            </button>
                        </div>
                    </div>
                </div>
                <div x-show="ticketClosed" x-cloak>
                    <div class="border-t border-slate-200 p-4 dark:border-navy-500 sm:p-5">
                        <div class="flex flex-col items-center space-y-3 py-2">
                            <p class="text-sm text-slate-400 dark:text-navy-300">This ticket is closed.</p>
                            @if($allowCustomerReopen)
                            <form method="POST" action="{{ route('customer.tickets.reopen', $ticket->uuid) }}">
                                @csrf
                                <button type="submit" class="btn space-x-2 bg-success font-medium text-white hover:bg-success/80 focus:bg-success/80">
                                    <svg xmlns="http://www.w3.org/2000/svg" class="size-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15"/></svg>
                                    <span>Reopen Ticket</span>
                                </button>
                            </form>
                            @endif
                        </div>
                    </div>
                </div>
            </div>
        </div>

        {{-- Sidebar Info --}}
        <div>
            <div class="card p-4 sm:p-5">
                <h3 class="text-sm font-medium text-slate-700 dark:text-navy-100">Ticket Details</h3>
                <div class="mt-4 space-y-3">
                    <div class="flex items-center justify-between">
                        <span class="text-xs text-slate-400 dark:text-navy-300">Status</span>
                        <span class="badge rounded-full px-2.5 py-1 text-xs font-medium" style="background-color: {{ $ticket->status_color }}20; color: {{ $ticket->status_color }}">{{ $ticket->status_name }}</span>
                    </div>
                    <div class="flex items-center justify-between">
                        <span class="text-xs text-slate-400 dark:text-navy-300">Priority</span>
                        @if ($ticket->priority_name)
                            <span class="badge rounded-full px-2.5 py-1 text-xs font-medium" style="background-color: {{ $ticket->priority_color }}20; color: {{ $ticket->priority_color }}">{{ $ticket->priority_name }}</span>
                        @else
                            <span class="text-xs text-slate-400">-</span>
                        @endif
                    </div>
                    @if ($ticket->category_name)
                        <div class="flex items-center justify-between">
                            <span class="text-xs text-slate-400 dark:text-navy-300">Category</span>
                            <span class="text-xs text-slate-600 dark:text-navy-200">{{ $ticket->category_name }}</span>
                        </div>
                    @endif
                    @if ($ticket->agent_name)
                        <div class="flex items-center justify-between">
                            <span class="text-xs text-slate-400 dark:text-navy-300">Assigned To</span>
                            <div class="flex items-center space-x-1.5">
                                <span class="size-1.5 rounded-full" :class="agentOnline ? 'bg-success' : 'bg-slate-300'"></span>
                                <span class="text-xs text-slate-600 dark:text-navy-200">{{ $ticket->agent_name }}</span>
                            </div>
                        </div>
                    @endif
                    <div class="flex items-center justify-between">
                        <span class="text-xs text-slate-400 dark:text-navy-300">Created</span>
                        <span class="text-xs text-slate-600 dark:text-navy-200">{{ \Carbon\Carbon::parse($ticket->created_at)->format('M d, Y') }}</span>
                    </div>
                    @if ($ticket->due_at)
                        <div class="flex items-center justify-between">
                            <span class="text-xs text-slate-400 dark:text-navy-300">Due Date</span>
                            <span class="text-xs text-slate-600 dark:text-navy-200">{{ \Carbon\Carbon::parse($ticket->due_at)->format('M d, Y') }}</span>
                        </div>
                    @endif
                </div>
            </div>

            {{-- Attachments --}}
            @if ($attachments->isNotEmpty())
                <div class="card mt-4 p-4 sm:p-5">
                    <h3 class="text-sm font-medium text-slate-700 dark:text-navy-100">Attachments</h3>
                    <div class="mt-3 space-y-2">
                        @foreach ($attachments as $attachment)
                            <a href="{{ asset('storage/' . $attachment->file_path) }}" target="_blank" class="flex items-center space-x-2 rounded-lg border border-slate-200 p-2 transition-colors hover:bg-slate-50 dark:border-navy-500 dark:hover:bg-navy-600">
                                <svg xmlns="http://www.w3.org/2000/svg" class="size-5 text-slate-400" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15.172 7l-6.586 6.586a2 2 0 102.828 2.828l6.414-6.586a4 4 0 00-5.656-5.656l-6.415 6.585a6 6 0 108.486 8.486L20.5 13"/></svg>
                                <div class="min-w-0 flex-1">
                                    <p class="truncate text-xs font-medium text-slate-600 dark:text-navy-200">{{ $attachment->file_name }}</p>
                                    <p class="text-xs text-slate-400 dark:text-navy-300">{{ number_format($attachment->file_size / 1024, 1) }} KB</p>
                                </div>
                            </a>
                        @endforeach
                    </div>
                </div>
            @endif
        </div>
    </div>
</x-customer-layout>
