<?php

namespace App\Realtime\Drivers;

use App\Realtime\Contracts\RealtimeDriverInterface;

class PollingDriver implements RealtimeDriverInterface
{
    public function name(): string
    {
        return 'polling';
    }

    /**
     * Polling is pull-based — no server push needed.
     * The client fetches messages via GET /tickets/{uuid}/messages?since=...
     */
    public function broadcastMessage(int $ticketId, array $messageData): void
    {
        // No-op: polling clients pull messages themselves.
    }

    public function broadcastTyping(int $ticketId, array $typingData): void
    {
        // No-op: typing state is read from cache during poll.
    }

    public function broadcastPresence(int $ticketId, array $presenceData): void
    {
        // No-op: presence is read from cache during poll.
    }

    public function isConfigured(): bool
    {
        return true; // Always available.
    }

    public function frontendConfig(): array
    {
        return [];
    }

    public function authorizeChannel(string $channelName, string $socketId, $user): ?array
    {
        return null; // Not applicable for polling.
    }
}
