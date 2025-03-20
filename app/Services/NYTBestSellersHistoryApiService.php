<?php

namespace App\Services;

use Illuminate\Support\Facades\{Http, Cache};

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
                    $response = Http::retry(5, 500)->get($this->apiUrl, $this->prepareRequestParams($params));

                    if ($response->serverError()) {
                        throw new \Exception('Failed to fetch data from NYT Books API: ' . $response->body());
                    }
                    $json = $response->json();
                    return [
                        'results' => $json['results'] ?? [],
                        'errors' => $json['errors'] ?? [],
                    ];
                });
    }

    protected function generateCacheKey(array $params): string
    {
        return 'nyt_bestsellers_' . md5(json_encode($params));
    }

    protected function prepareRequestParams(array $params): array
    {
        if (isset($params['isbn']) && is_array($params['isbn'])) {
            $params['isbn'] = implode(';', $params['isbn']);
        }
        return array_merge($params, ['api-key' => $this->apiKey]);
    }
}
