<?php

namespace App\Services;

use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Cache;

class NYTBestSellersHistoryApiService
{
    protected string $apiKey;
    protected string $apiUrl;
    protected int $apiDataCacheTtl;

    public function __construct()
    {
        $this->apiKey = config('services.nyt.api_key');
        $this->apiUrl = config('services.nyt.api_url');
        $this->apiDataCacheTtl = config('services.nyt.api_cache_ttl', 10);
    }
    public function getNytBestSellersHistory(array $params = []): array
    {
        $cacheKey = $this->generateCacheKey($params);

        return Cache::store(config('cache.default'))
            ->remember($cacheKey, now()->addMinutes($this->apiDataCacheTtl),
                function () use ($params) {
                    $query = array_merge($params, ['api-key' => $this->apiKey]);
                    $response = Http::get($this->apiUrl, $query);

                    if (!$response->successful()) {
                        throw new \Exception('Failed to fetch data from NYT Books API: ' . $response->body());
                    }

                    return $response->json()['results'] ?? [];
                });
    }

    protected function generateCacheKey(array $params): string
    {
        return 'nyt_bestsellers_' . md5(json_encode($params));
    }
}
