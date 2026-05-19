<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Services\FinanceService;
use App\Services\ForecastService;
use Illuminate\Http\JsonResponse;

class FinanceController extends Controller
{
    public function kpi(FinanceService $finance): JsonResponse
    {
        return response()->json($finance->getDashboardKPIs());
    }

    public function forecast(ForecastService $forecast): JsonResponse
    {
        return response()->json($forecast->cashflowForecast());
    }
}
