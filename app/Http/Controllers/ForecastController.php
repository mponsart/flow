<?php

namespace App\Http\Controllers;

use App\Models\Forecast;
use App\Services\ForecastService;
use App\Services\FinanceService;
use Illuminate\Http\Request;

class ForecastController extends Controller
{
    public function index(ForecastService $forecast, FinanceService $finance)
    {
        $comparisons = $forecast->compare();
        $forecasts = Forecast::orderBy('month')->get();
        $revenueByMonth = $finance->getRevenueByMonth(6);
        return view('forecasts.index', compact('comparisons', 'forecasts', 'revenueByMonth'));
    }

    public function generate(ForecastService $forecast)
    {
        $data = $forecast->generate6MonthForecast();
        $forecast->saveForecast($data);
        return redirect()->route('forecasts.index')->with('success', '6 mois de prévisions générées.');
    }
}
