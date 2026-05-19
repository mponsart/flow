<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Services\FinanceService;
use Illuminate\Http\JsonResponse;

class DashboardController extends Controller
{
    public function index(FinanceService $finance): JsonResponse
    {
        return response()->json($finance->getDashboardKPIs());
    }
}
