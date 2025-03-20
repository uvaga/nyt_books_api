<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\{Controllers\Controller, Requests\NYTBestSellersHistoryApiRequest};
use App\Services\NYTBestSellersHistoryApiService;
use Illuminate\{Http\JsonResponse, Support\Facades\Log};

class NYTBestSellersHistoryController extends Controller
{
    protected NYTBestSellersHistoryApiService $nytBestSellersHistoryApiService;

    public function __construct(NYTBestSellersHistoryApiService $nytBestSellersHistoryApiService)
    {
        $this->nytBestSellersHistoryApiService = $nytBestSellersHistoryApiService;
    }

    public function index(NYTBestSellersHistoryApiRequest $request): JsonResponse
    {
        $validatedParams = $request->validated();

        try {
            $data = $this->nytBestSellersHistoryApiService->getNytBestSellersHistory($validatedParams);
            if (!empty($data['errors'])) {
                $response = ['message' => 'Bad request to NYC Books API', 'errors' => $data['errors']];
                Log::error($response['message'], $data['errors']);
                return response()->json($response, 400);
            }
            return response()->json($data);
        } catch (\Exception $e) {
            Log::error('Error NYC Books API: ' . $e->getMessage());
            return response()->json(['error' => $e->getMessage()], 500);
        }
    }
}
