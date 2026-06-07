<?php

namespace App\Services;

use App\Models\Forecast;
use App\Models\Revenue;
use App\Models\Expense;

class ForecastService
{
    public function generate6MonthForecast(): array
    {
        // Gather last 6 months of actuals
        $historicalRevenues = [];
        $historicalExpenses = [];
        for ($i = 5; $i >= 0; $i--) {
            $month = now()->subMonths($i);
            $historicalRevenues[] = (float) Revenue::whereMonth('date', $month->month)->whereYear('date', $month->year)->sum('amount');
            $historicalExpenses[] = (float) Expense::whereMonth('date', $month->month)->whereYear('date', $month->year)->sum('amount');
        }

        $revenueSlope = $this->linearRegressionSlope($historicalRevenues);
        $expenseSlope = $this->linearRegressionSlope($historicalExpenses);
        $lastRevenue = end($historicalRevenues);
        $lastExpense = end($historicalExpenses);

        $forecasts = [];
        for ($i = 1; $i <= 6; $i++) {
            $projRev = max(0, $lastRevenue + $revenueSlope * $i);
            $projExp = max(0, $lastExpense + $expenseSlope * $i);
            $forecasts[] = [
                'month' => now()->addMonths($i)->startOfMonth()->toDateString(),
                'projected_revenue' => round($projRev, 2),
                'projected_expenses' => round($projExp, 2),
                'projected_profit' => round($projRev - $projExp, 2),
            ];
        }
        return $forecasts;
    }

    public function saveForecast(array $data): void
    {
        foreach ($data as $item) {
            Forecast::updateOrCreate(
                ['month' => $item['month']],
                [
                    'projected_revenue' => $item['projected_revenue'],
                    'projected_expenses' => $item['projected_expenses'],
                    'projected_profit' => $item['projected_profit'],
                ]
            );
        }
    }

    public function getForecast(int $months = 6): array
    {
        return Forecast::orderBy('month')
            ->limit($months)
            ->where('month', '>=', now()->startOfMonth())
            ->get()
            ->toArray();
    }

    public function compare(): array
    {
        $forecasts = Forecast::orderBy('month')->get();
        return $forecasts->map(function ($f) {
            $actualRevenue = (float) Revenue::whereMonth('date', date('m', strtotime($f->month)))->whereYear('date', date('Y', strtotime($f->month)))->sum('amount');
            $actualExpenses = (float) Expense::whereMonth('date', date('m', strtotime($f->month)))->whereYear('date', date('Y', strtotime($f->month)))->sum('amount');
            return [
                'month' => $f->month,
                'projected_revenue' => (float) $f->projected_revenue,
                'projected_expenses' => (float) $f->projected_expenses,
                'projected_profit' => (float) $f->projected_profit,
                'actual_revenue' => $actualRevenue,
                'actual_expenses' => $actualExpenses,
                'actual_profit' => $actualRevenue - $actualExpenses,
            ];
        })->toArray();
    }

    private function linearRegressionSlope(array $values): float
    {
        $n = count($values);
        if ($n < 2) return 0;
        $sumX = 0; $sumY = 0; $sumXY = 0; $sumX2 = 0;
        for ($i = 0; $i < $n; $i++) {
            $sumX += $i;
            $sumY += $values[$i];
            $sumXY += $i * $values[$i];
            $sumX2 += $i * $i;
        }
        $denom = ($n * $sumX2 - $sumX * $sumX);
        if ($denom == 0) return 0;
        return ($n * $sumXY - $sumX * $sumY) / $denom;
    }
}
