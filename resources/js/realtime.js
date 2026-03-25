/**
 * Unified Realtime Client
 *
 * Supports three transport drivers: polling, pusher, ably.
 * The active driver is resolved from the backend config endpoint.
 * All drivers expose the same API to the consuming Alpine.js components.
 *
 * Usage:
 *   const rt = new RealtimeClient({ ticketId, ticketUuid, ... });
 *   await rt.init();
 *   rt.onMessage(callback);
 *   rt.onTyping(callback);
 *   rt.onPresence(callback);
 *   rt.sendTyping(true);
 *   rt.destroy();
 */

export class RealtimeClient {
    constructor({
        ticketId,
        ticketUuid,
        configUrl = '/api/realtime/config',
        authUrl = '/api/realtime/auth',
        pollMessagesUrl,
        typingUrl,
        alertSoundUrl = null,
        role = 'agent', // 'agent' or 'customer'
    }) {
        this.ticketId = ticketId;
        this.ticketUuid = ticketUuid;
        this.configUrl = configUrl;
        this.authUrl = authUrl;
        this.pollMessagesUrl = pollMessagesUrl;
        this.typingUrl = typingUrl;
        this.alertSoundUrl = alertSoundUrl;
        this.role = role;

        this.config = null;
        this.driver = null; // 'polling' | 'pusher' | 'ably'
        this.transport = null; // Pusher/Ably instance

        // Callbacks
        this._onMessage = [];
        this._onTyping = [];
        this._onPresence = [];
        this._onStatusChange = [];

        // Polling state
        this._pollInterval = null;
        this._since = new Date().toISOString();

        // Typing debounce
        this._typingTimeout = null;
        this._lastTypingSent = false;
    }

    /**
     * Initialize: fetch config, start the correct transport.
     */
    async init() {
        try {
            const res = await fetch(this.configUrl, {
                headers: { 'Accept': 'application/json', 'X-Requested-With': 'XMLHttpRequest' },
            });
            this.config = await res.json();
        } catch (e) {
            // Fallback to polling defaults if config fetch fails
            this.config = { driver: 'polling', polling_interval: 3000, typing_timeout: 5 };
        }

        this.driver = this.config.driver;

        switch (this.driver) {
            case 'pusher':
                await this._initPusher();
                break;
            case 'ably':
                await this._initAbly();
                break;
            default:
                this.driver = 'polling';
                break;
        }

        // Always start polling as baseline (for presence/typing in polling mode,
        // or as background sync for pusher/ably)
        if (this.driver === 'polling') {
            this._startPolling(this.config.polling_interval || 3000);
        } else {
            // Even with push drivers, poll at a slower rate for message sync safety
            this._startPolling(this.config.polling_idle_interval || 10000);
        }
    }

    // ─── Public API ────────────────────────────────────

    onMessage(callback) { this._onMessage.push(callback); }
    onTyping(callback) { this._onTyping.push(callback); }
    onPresence(callback) { this._onPresence.push(callback); }
    onStatusChange(callback) { this._onStatusChange.push(callback); }

    async sendTyping(isTyping) {
        // Debounce: don't spam the server
        if (this._lastTypingSent === isTyping) return;
        this._lastTypingSent = isTyping;

        if (this._typingTimeout) clearTimeout(this._typingTimeout);

        try {
            fetch(this.typingUrl, {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': this._csrfToken(),
                    'X-Requested-With': 'XMLHttpRequest',
                },
                body: JSON.stringify({ typing: isTyping }),
            });
        } catch (e) { /* silent */ }

        // Auto-stop typing after timeout
        if (isTyping) {
            const timeout = (this.config?.typing_timeout || 5) * 1000;
            this._typingTimeout = setTimeout(() => {
                this._lastTypingSent = false;
                this.sendTyping(false);
            }, timeout + 3000);
        }
    }

    stopTyping() {
        if (this._typingTimeout) clearTimeout(this._typingTimeout);
        this._lastTypingSent = true; // force the next sendTyping(false) to go through
        this.sendTyping(false);
    }

    updateSince(timestamp) {
        this._since = timestamp;
    }

    destroy() {
        if (this._pollInterval) clearInterval(this._pollInterval);
        if (this._typingTimeout) clearTimeout(this._typingTimeout);

        if (this.driver === 'pusher' && this.transport) {
            try { this.transport.unsubscribe(`private-ticket.${this.ticketId}`); } catch (e) {}
            try { this.transport.disconnect(); } catch (e) {}
        }

        if (this.driver === 'ably' && this.transport) {
            try { this.transport.close(); } catch (e) {}
        }

        this.transport = null;
    }

    // ─── Polling Transport ─────────────────────────────

    _startPolling(intervalMs) {
        this._poll(); // Immediate first poll
        this._pollInterval = setInterval(() => this._poll(), intervalMs);
    }

    async _poll() {
        try {
            const url = `${this.pollMessagesUrl}?since=${encodeURIComponent(this._since)}`;
            const res = await fetch(url, {
                headers: { 'Accept': 'application/json', 'X-Requested-With': 'XMLHttpRequest' },
            });
            const data = await res.json();

            // Messages
            if (data.messages && data.messages.length > 0) {
                this._emit('message', data.messages);
                this._since = data.timestamp;
            }

            // Typing (polling response includes current typing state)
            if (data.typing !== undefined) {
                this._emit('typing', data.typing);
            }

            // Presence
            const presence = {};
            if (data.customer_online !== undefined) {
                presence.customer_online = data.customer_online;
                presence.customer_last_seen = data.customer_last_seen;
            }
            if (data.agent_online !== undefined) {
                presence.agent_online = data.agent_online;
                presence.agent_last_seen = data.agent_last_seen;
            }
            if (Object.keys(presence).length > 0) {
                this._emit('presence', presence);
            }

            // Ticket status change (closed/reopened)
            if (data.is_closed !== undefined) {
                this._emit('statusChange', { is_closed: data.is_closed });
            }
        } catch (e) { /* silent — next poll will retry */ }
    }

    // ─── Pusher Transport ──────────────────────────────

    async _initPusher() {
        const pusherConfig = this.config.pusher;
        if (!pusherConfig?.key) {
            this.driver = 'polling';
            return;
        }

        // Load Pusher from CDN if not already available
        if (!window.Pusher) {
            try {
                await this._loadScript('https://js.pusher.com/8.2.0/pusher.min.js');
            } catch (e) {
                console.warn('[Realtime] Pusher library not available, falling back to polling');
                this.driver = 'polling';
                return;
            }
        }

        try {
            this.transport = new window.Pusher(pusherConfig.key, {
                cluster: pusherConfig.cluster,
                authEndpoint: this.authUrl,
                auth: {
                    headers: {
                        'X-CSRF-TOKEN': this._csrfToken(),
                        'X-Requested-With': 'XMLHttpRequest',
                    },
                },
            });

            const channel = this.transport.subscribe(`private-ticket.${this.ticketId}`);

            channel.bind('message.created', (data) => {
                this._emit('message', [data]);
            });

            channel.bind('typing.update', (data) => {
                this._emit('typing', data);
            });

            channel.bind('presence.update', (data) => {
                this._emit('presence', data);
            });
        } catch (e) {
            console.warn('[Realtime] Pusher init failed, falling back to polling', e);
            this.driver = 'polling';
        }
    }

    // ─── Ably Transport ────────────────────────────────

    async _initAbly() {
        const ablyConfig = this.config.ably;
        if (!ablyConfig?.key) {
            this.driver = 'polling';
            return;
        }

        // Load Ably from CDN if not already available
        if (!window.Ably) {
            try {
                await this._loadScript('https://cdn.ably.com/lib/ably.min-1.js');
            } catch (e) {
                console.warn('[Realtime] Ably library not available, falling back to polling');
                this.driver = 'polling';
                return;
            }
        }

        try {
            this.transport = new window.Ably.Realtime({
                authUrl: this.authUrl,
                authMethod: 'POST',
                authHeaders: {
                    'X-CSRF-TOKEN': this._csrfToken(),
                    'X-Requested-With': 'XMLHttpRequest',
                    'Content-Type': 'application/json',
                },
                authParams: { channel_name: `private:ticket.${this.ticketId}` },
            });

            const channel = this.transport.channels.get(`private:ticket.${this.ticketId}`);

            channel.subscribe('message.created', (msg) => {
                const data = JSON.parse(msg.data);
                this._emit('message', [data]);
            });

            channel.subscribe('typing.update', (msg) => {
                const data = JSON.parse(msg.data);
                this._emit('typing', data);
            });

            channel.subscribe('presence.update', (msg) => {
                const data = JSON.parse(msg.data);
                this._emit('presence', data);
            });
        } catch (e) {
            console.warn('[Realtime] Ably init failed, falling back to polling', e);
            this.driver = 'polling';
        }
    }

    // ─── Internal Helpers ──────────────────────────────

    _emit(type, data) {
        const callbacks = {
            message: this._onMessage,
            typing: this._onTyping,
            presence: this._onPresence,
            statusChange: this._onStatusChange,
        }[type] || [];

        callbacks.forEach(cb => {
            try { cb(data); } catch (e) { console.error('[Realtime] callback error', e); }
        });
    }

    _loadScript(src) {
        return new Promise((resolve, reject) => {
            if (document.querySelector(`script[src="${src}"]`)) {
                return resolve();
            }
            const script = document.createElement('script');
            script.src = src;
            script.onload = resolve;
            script.onerror = () => reject(new Error(`Failed to load ${src}`));
            document.head.appendChild(script);
        });
    }

    _csrfToken() {
        const meta = document.querySelector('meta[name="csrf-token"]');
        return meta ? meta.content : '';
    }
}

// Make it globally accessible for inline Alpine scripts
window.RealtimeClient = RealtimeClient;
