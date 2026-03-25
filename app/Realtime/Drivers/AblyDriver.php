<?php

namespace App\Realtime\Drivers;

use App\Realtime\Contracts\RealtimeDriverInterface;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class AblyDriver implements RealtimeDriverInterface
{
    protected string $apiKey;
    protected string $clientKey;

    public function __construct(string $apiKey, string $clientKey)
    {
        $this->apiKey = $apiKey;       // Full key: appId.keyId:keySecret
        $this->clientKey = $clientKey; // Public key for frontend: appId.keyId
    }

    public function name(): string
    {
        return 'ably';
    }

    public function broadcastMessage(int $ticketId, array $messageData): void
    {
        $this->publish("private:ticket.{$ticketId}", 'message.created', $messageData);
    }

    public function broadcastTyping(int $ticketId, array $typingData): void
    {
        $this->publish("private:ticket.{$ticketId}", 'typing.update', $typingData);
    }

    public function broadcastPresence(int $ticketId, array $presenceData): void
    {
        $this->publish("private:ticket.{$ticketId}", 'presence.update', $presenceData);
    }

    public function isConfigured(): bool
    {
        return $this->apiKey !== '' && $this->clientKey !== '' && str_contains($this->apiKey, ':');
    }

    public function frontendConfig(): array
    {
        return [
            'key' => $this->clientKey,
        ];
    }

    public function authorizeChannel(string $channelName, string $socketId, $user): ?array
    {
        // Ably uses token requests for auth via the REST API.
        try {
            [$keyName, $keySecret] = explode(':', $this->apiKey, 2);
            $capability = json_encode([$channelName => ['subscribe', 'presence', 'publish']]);

            $tokenRequest = [
                'keyName' => $keyName,
                'timestamp' => (int) (microtime(true) * 1000),
                'nonce' => bin2hex(random_bytes(8)),
                'capability' => $capability,
                'clientId' => (string) $user->id,
            ];

            ksort($tokenRequest);
            $signString = implode("\n", [
                $tokenRequest['keyName'] ?? '',
                $tokenRequest['timestamp'] ?? '',
                $tokenRequest['nonce'] ?? '',
                $tokenRequest['capability'] ?? '',
                $tokenRequest['clientId'] ?? '',
            ]) . "\n";

            $tokenRequest['mac'] = base64_encode(hash_hmac('sha256', $signString, $keySecret, true));

            return $tokenRequest;
        } catch (\Throwable $e) {
            Log::warning('Ably channel auth failed', ['error' => $e->getMessage()]);
            return null;
        }
    }

    /**
     * Publish a message to an Ably channel via the REST API.
     */
    protected function publish(string $channel, string $event, array $data): void
    {
        try {
            Http::withHeaders([
                'Authorization' => 'Basic ' . base64_encode($this->apiKey),
                'Content-Type' => 'application/json',
            ])->post("https://rest.ably.io/channels/" . urlencode($channel) . "/messages", [
                'name' => $event,
                'data' => json_encode($data),
            ]);
        } catch (\Throwable $e) {
            Log::warning('Ably broadcast failed', ['error' => $e->getMessage(), 'channel' => $channel, 'event' => $event]);
        }
    }
}
