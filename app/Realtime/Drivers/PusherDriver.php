<?php

namespace App\Realtime\Drivers;

use App\Realtime\Contracts\RealtimeDriverInterface;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class PusherDriver implements RealtimeDriverInterface
{
    protected string $appId;
    protected string $key;
    protected string $secret;
    protected string $cluster;

    public function __construct(string $appId, string $key, string $secret, string $cluster)
    {
        $this->appId = $appId;
        $this->key = $key;
        $this->secret = $secret;
        $this->cluster = $cluster;
    }

    public function name(): string
    {
        return 'pusher';
    }

    public function broadcastMessage(int $ticketId, array $messageData): void
    {
        $this->trigger("private-ticket.{$ticketId}", 'message.created', $messageData);
    }

    public function broadcastTyping(int $ticketId, array $typingData): void
    {
        $this->trigger("private-ticket.{$ticketId}", 'typing.update', $typingData);
    }

    public function broadcastPresence(int $ticketId, array $presenceData): void
    {
        $this->trigger("private-ticket.{$ticketId}", 'presence.update', $presenceData);
    }

    public function isConfigured(): bool
    {
        return $this->appId !== '' && $this->key !== '' && $this->secret !== '' && $this->cluster !== '';
    }

    public function frontendConfig(): array
    {
        return [
            'key' => $this->key,
            'cluster' => $this->cluster,
        ];
    }

    public function authorizeChannel(string $channelName, string $socketId, $user): ?array
    {
        $stringToSign = "{$socketId}:{$channelName}";
        $signature = hash_hmac('sha256', $stringToSign, $this->secret);

        return [
            'auth' => "{$this->key}:{$signature}",
        ];
    }

    /**
     * Trigger an event on a Pusher channel using the REST API directly.
     * No SDK dependency required.
     */
    protected function trigger(string $channel, string $event, array $data): void
    {
        try {
            $body = json_encode([
                'name' => $event,
                'channel' => $channel,
                'data' => json_encode($data),
            ]);

            $path = "/apps/{$this->appId}/events";
            $timestamp = time();

            $params = [
                'auth_key' => $this->key,
                'auth_timestamp' => $timestamp,
                'auth_version' => '1.0',
                'body_md5' => md5($body),
            ];

            ksort($params);
            $queryString = http_build_query($params);
            $stringToSign = "POST\n{$path}\n{$queryString}";
            $signature = hash_hmac('sha256', $stringToSign, $this->secret);

            $url = "https://api-{$this->cluster}.pusher.com{$path}?{$queryString}&auth_signature={$signature}";

            Http::withBody($body, 'application/json')->post($url);
        } catch (\Throwable $e) {
            Log::warning('Pusher broadcast failed', ['error' => $e->getMessage(), 'channel' => $channel, 'event' => $event]);
        }
    }
}
