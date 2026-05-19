<?php

namespace App\Services;

use App\Models\Forecast;
use App\Models\Revenue;
use App\Models\Expense;

class ForecastService
{
    /**
     * Génère une prévision simple de trésorerie sur 6 mois.
     */
    public function cashflowForecast(): array
    {
        $now = now();
        $forecast = [];
        for ($i = 0; $i < 6; $i++) {
            $month = $now->copy()->addMonths($i);
            $revenus = Revenue::whereMonth('date', $month->month)->whereYear('date', $month->year)->sum('amount');
            $dépenses = Expense::whereMonth('date', $month->month)->whereYear('date', $month->year)->sum('amount');
            $forecast[] = [
                'mois' => $month->format('F Y'),
                'revenus' => $revenus,
                'dépenses' => $dépenses,
                'cashflow' => $revenus - $dépenses,
            ];
        }
        return $forecast;
    }
}
