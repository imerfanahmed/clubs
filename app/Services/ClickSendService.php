<?php

namespace App\Services;

use Illuminate\Support\Facades\Http;

class ClickSendService
{
    protected string $username;

    protected string $apiKey;

    protected string $baseUrl = 'https://rest.clicksend.com/v3';

    public function __construct()
    {
        $this->username = config('services.clicksend.username');
        $this->apiKey = config('services.clicksend.api_key');
    }

    public function send(array $messages): array
    {
        $payload = [
            'messages' => array_map(fn ($msg) => [
                'to' => $msg['phone'],
                'body' => $msg['message'],
                'source' => 'php',
            ], $messages),
        ];

        $response = Http::withBasicAuth($this->username, $this->apiKey)
            ->post("{$this->baseUrl}/sms/send", $payload);

        if ($response->failed()) {
            return [
                'success' => false,
                'error' => $response->body(),
            ];
        }

        $result = $response->json();

        $messageResults = [];
        foreach ($result['data']['messages'] ?? [] as $msg) {
            $messageResults[] = [
                'message_id' => $msg['message_id'] ?? null,
                'status' => $msg['status'] ?? 'failed',
                'cost' => isset($msg['cost']) ? (int) (floatval($msg['cost']) * 100) : null,
                'error' => $msg['error_text'] ?? null,
            ];
        }

        return [
            'success' => true,
            'total_cost' => isset($result['data']['total_price'])
                ? (int) (floatval($result['data']['total_price']) * 100)
                : null,
            'messages' => $messageResults,
        ];
    }

    public function getBalance(): array
    {
        $response = Http::withBasicAuth($this->username, $this->apiKey)
            ->get("{$this->baseUrl}/sms/balance");

        if ($response->failed()) {
            return ['success' => false];
        }

        return $response->json();
    }
}
