<?php

namespace App\Realtime\Contracts;

interface RealtimeDriverInterface
{
    /**
     * Get the driver name identifier.
     */
    public function name(): string;

    /**
     * Broadcast a new message event to the ticket channel.
     */
    public function broadcastMessage(int $ticketId, array $messageData): void;

    /**
     * Broadcast typing indicator to the ticket channel.
     */
    public function broadcastTyping(int $ticketId, array $typingData): void;

    /**
     * Broadcast presence update to the ticket channel.
     */
    public function broadcastPresence(int $ticketId, array $presenceData): void;

    /**
     * Check whether this driver is properly configured and usable.
     */
    public function isConfigured(): bool;

    /**
     * Return frontend config needed for this driver (credentials, cluster, etc.).
     * Must never expose server-side secrets.
     */
    public function frontendConfig(): array;

    /**
     * Authenticate a user for a private/presence channel.
     * Returns auth response array or null if not applicable.
     */
    public function authorizeChannel(string $channelName, string $socketId, $user): ?array;
}
