<?php

namespace App\Services;

use Illuminate\Support\Facades\Log;

class SmsService
{
    private string $apiKey;
    private string $authId;
    private string $serviceId;
    private string $senderId;
    private string $apiUrl;

    public function __construct()
    {
        $this->apiKey = env('EGA_SMS_KEY', 'e1WisEhOtfVvBaVQ2j3p5wJPa4X6xj6Jp6C83Jd0');
        $this->authId = env('EGA_SMS_ID', 'schibelenje@iaa.ac.tz');
        $this->serviceId = '507';
        $this->senderId = 'IAA';
        $this->apiUrl = 'http://msdg.ega.go.tz/msdg/public/quick_sms';
    }

    public function send(string $recipient, string $message): bool
    {
        Log::info('Starting SMS send process');

        // Normalise to 255XXXXXXXXX regardless of input format:
        // +255XXXXXXXXX → 255XXXXXXXXX
        // 0XXXXXXXXX    → 255XXXXXXXXX
        // 255XXXXXXXXX  → 255XXXXXXXXX (unchanged)
        $phone = preg_replace('/^\+/', '', $recipient); // strip leading +
        if (str_starts_with($phone, '255')) {
            // already correct
        } elseif (str_starts_with($phone, '0')) {
            $phone = '255' . substr($phone, 1);
        } else {
            $phone = '255' . $phone;
        }
        Log::info('Formatted phone number: ' . $phone);

        $datetime = (new \DateTime('now', new \DateTimeZone('Africa/Nairobi')))
            ->format('Y-m-d H:i:s');
        Log::info('Datetime in EAT: ' . $datetime);

        $payload = [
            'recipients' => $phone,
            'message' => $message,
            'datetime' => $datetime,
            'mobile_service_id' => $this->serviceId,
            'sender_id' => $this->senderId,
        ];

        $jsonData = json_encode($payload);
        Log::info('JSON Payload: ' . $jsonData);

        $hash = base64_encode(hash_hmac('sha256', $jsonData, $this->apiKey, true));
        Log::info('Generated HMAC Hash: ' . $hash);

        $ch = curl_init($this->apiUrl);
        curl_setopt($ch, CURLOPT_POST, true);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_HTTPHEADER, [
            'X-Auth-Request-Hash: ' . $hash,
            'X-Auth-Request-Id: ' . $this->authId,
            'X-Auth-Request-Type: api',
        ]);
        curl_setopt($ch, CURLOPT_POSTFIELDS, http_build_query([
            'data' => $jsonData,
            'datetime' => $datetime,
        ]));

        $response = curl_exec($ch);
        $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        curl_close($ch);

        Log::info('HTTP Status Code: ' . $httpCode);
        Log::info('Response Body: ' . $response);

        $respData = json_decode($response, true);

        if (!$respData || !isset($respData['status']) || $respData['status'] !== 'success') {
            Log::error('SMS sending failed', $respData ?? []);
            return false;
        }

        Log::info('SMS sent successfully');
        return true;
    }
}
