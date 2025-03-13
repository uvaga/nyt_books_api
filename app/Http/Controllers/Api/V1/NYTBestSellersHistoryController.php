<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Http\Requests\NYTBestSellersHistoryApiRequest;
use App\Services\NYTBestSellersHistoryApiService;
use Illuminate\Support\Facades\Log;

class NYTBestSellersHistoryController extends Controller
{
    protected NYTBestSellersHistoryApiService $nytBestSellersHistoryApiService;

    public function __construct(NYTBestSellersHistoryApiService $nytBestSellersHistoryApiService)
    {
        $this->nytBestSellersHistoryApiService = $nytBestSellersHistoryApiService;
    }

    public function index(NYTBestSellersHistoryApiRequest $request)
    {
        $validatedParams = $request->validated();

        try {
            $data = $this->nytBestSellersHistoryApiService->getNytBestSellersHistory($validatedParams);
            return response()->json($data);
        } catch (\Exception $e) {
            return response()->json(['error' => $e->getMessage()], 500);
        }
    }
}
