<?php

namespace App\Services;

use Illuminate\Support\Facades\Http;

class PostcodeService
{
    protected string $apiKey;

    protected string $baseUrl = 'https://api.ideal-postcodes.co.uk/v1';

    public function __construct()
    {
        $this->apiKey = config('services.ideal_postcodes.key');
    }

    public function lookup(string $postcode): array
    {
        $response = Http::get("{$this->baseUrl}/postcodes/{$postcode}", [
            'api_key' => $this->apiKey,
        ]);

        if ($response->failed()) {
            return ['success' => false, 'message' => 'Postcode lookup failed.'];
        }

        $result = $response->json();

        if (! ($result['result']['hits'] ?? false)) {
            return ['success' => false, 'message' => 'No addresses found for this postcode.'];
        }

        $addresses = collect($result['result']['hits'])->map(function ($hit) {
            return [
                'line_1' => $hit['line_1'] ?? '',
                'line_2' => $hit['line_2'] ?? null,
                'city' => $hit['post_town'] ?? '',
                'county' => $hit['county'] ?? null,
                'postcode' => $hit['postcode'] ?? $postcode,
            ];
        });

        return ['success' => true, 'addresses' => $addresses];
    }

    public static function isValidUkPostcode(string $postcode): bool
    {
        return (bool) preg_match(
            '/^([Gg][Ii][Rr] 0[Aa]{2})|((([A-Za-z][0-9]{1,2})|(([A-Za-z][A-Ha-hJ-Yj-y][0-9]{1,2})|(([A-Za-z][0-9][A-Za-z])|([A-Za-z][A-Ha-hJ-Yj-y][0-9][A-Za-z]?))))\s?[0-9][A-Za-z]{2})$/',
            trim($postcode)
        );
    }
}
