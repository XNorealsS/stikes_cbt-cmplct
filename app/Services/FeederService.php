<?php

namespace App\Services;

use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Cache;

class FeederService
{
    protected string $url;
    protected string $username;
    protected string $password;
    protected int $timeout;

    public function __construct()
    {
        $this->url = config('services.feeder.url', 'https://feeder.stikeslhokseumawe.ac.id/ws/live2.php');
        $this->username = config('services.feeder.username', '');
        $this->password = config('services.feeder.password', '');
        $this->timeout = 60;
    }

    /**
     * Get Token from Neo Feeder
     */
    public function getToken(bool $forceRefresh = false): ?string
    {
        $cacheKey = 'feeder_token_' . md5($this->username);

        if (!$forceRefresh && Cache::has($cacheKey)) {
            return Cache::get($cacheKey);
        }

        try {
            $response = Http::timeout($this->timeout)
                ->post($this->url, [
                    'act' => 'GetToken',
                    'username' => $this->username,
                    'password' => $this->password,
                ]);

            if ($response->successful()) {
                $body = $response->json();
                if (isset($body['error_code']) && $body['error_code'] == 0 && isset($body['data']['token'])) {
                    $token = $body['data']['token'];
                    Cache::put($cacheKey, $token, now()->addHours(2));
                    return $token;
                } else {
                    Log::error('Feeder GetToken Error: ' . ($body['error_desc'] ?? 'Unknown Error'));
                }
            } else {
                Log::error('Feeder GetToken connection failed status code: ' . $response->status());
            }
        } catch (\Exception $e) {
            Log::error('Feeder GetToken exception: ' . $e->getMessage());
        }

        return null;
    }

    /**
     * Send POST request to Feeder API action
     */
    public function request(string $act, array $params = [], int $retryCount = 0): array
    {
        $token = $this->getToken();
        if (!$token) {
            return [
                'success' => false,
                'error_code' => 999,
                'error_desc' => 'Failed to retrieve auth token from Feeder.',
                'data' => []
            ];
        }

        $payload = array_merge([
            'act' => $act,
            'token' => $token,
        ], $params);

        try {
            $response = Http::timeout($this->timeout)
                ->post($this->url, $payload);

            if ($response->successful()) {
                $body = $response->json();
                
                if (isset($body['error_code']) && ($body['error_code'] == 100 || str_contains(strtolower($body['error_desc'] ?? ''), 'token expired') || str_contains(strtolower($body['error_desc'] ?? ''), 'invalid token'))) {
                    if ($retryCount < 2) {
                        Log::warning("Feeder token expired/invalid. Refreshing and retrying... (Attempt " . ($retryCount + 1) . ")");
                        $this->getToken(true); 
                        return $this->request($act, $params, $retryCount + 1);
                    }
                }
                
                return [
                    'success' => isset($body['error_code']) && $body['error_code'] == 0,
                    'error_code' => $body['error_code'] ?? -1,
                    'error_desc' => $body['error_desc'] ?? '',
                    'data' => $body['data'] ?? []
                ];
            } else {
                return [
                    'success' => false,
                    'error_code' => $response->status(),
                    'error_desc' => 'HTTP Request failed with status code ' . $response->status(),
                    'data' => []
                ];
            }
        } catch (\Exception $e) {
            return [
                'success' => false,
                'error_code' => 500,
                'error_desc' => 'Exception occurred: ' . $e->getMessage(),
                'data' => []
            ];
        }
    }
}
