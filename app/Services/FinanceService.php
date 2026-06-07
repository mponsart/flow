<?php

namespace App\Services;

use App\Models\Client;
use App\Models\Service;
use App\Models\Subscription;
use App\Models\Revenue;
use App\Models\Expense;
use Illuminate\Support\Carbon;

class FinanceService
{
    public function getMRR(): float
    {
        $monthly = Subscription::where('status', 'actif')->where('cycle', 'monthly')
            ->with('service')->get()->sum(fn($s) => (float) ($s->service->price ?? 0));
        $annual = Subscription::where('status', 'actif')->where('cycle', 'annual')
            ->with('service')->get()->sum(fn($s) => (float) ($s->service->price ?? 0) / 12);
        return round($monthly + $annual, 2);
    }

    public function getARR(): float
    {
        return round($this->getMRR() * 12, 2);
    }

    public function getTotalRevenue(?Carbon $month = null): float
    {
        $query = Revenue::query();
        if ($month) {
            $query->whereMonth('date', $month->month)->whereYear('date', $month->year);
        }
        return (float) $query->sum('amount');
    }

    public function getTotalExpenses(?Carbon $month = null): float
    {
        $query = Expense::query();
        if ($month) {
            $query->whereMonth('date', $month->month)->whereYear('date', $month->year);
        }
        return (float) $query->sum('amount');
    }

    public function getNetProfit(?Carbon $month = null): float
    {
        return $this->getTotalRevenue($month) - $this->getTotalExpenses($month);
    }

    public function getMargin(?Carbon $month = null): float
    {
        $revenue = $this->getTotalRevenue($month);
        if ($revenue <= 0) return 0;
        return round(($this->getNetProfit($month) / $revenue) * 100, 2);
    }

    public function getGrowthRate(): float
    {
        $now = now();
        $currentMonth = $this->getTotalRevenue($now);
        $prevMonth = $this->getTotalRevenue($now->copy()->subMonth());
        if ($prevMonth <= 0) return 0;
        return round((($currentMonth - $prevMonth) / $prevMonth) * 100, 2);
    }

    public function getMostProfitableClient(): ?Client
    {
        return Client::with('revenues', 'expenses')->get()
            ->sortByDesc(fn($c) => $c->total_revenue - $c->total_expenses)
            ->first();
    }

    public function getMostProfitableService(): ?Service
    {
        return Service::with(['subscriptions.revenues'])->get()
            ->sortByDesc(fn($s) => $s->total_revenue)
            ->first();
    }

    public function getKPIs(): array
    {
        $now = now();
        return [
            'mrr' => $this->getMRR(),
            'arr' => $this->getARR(),
            'revenue_month' => $this->getTotalRevenue($now),
            'expenses_month' => $this->getTotalExpenses($now),
            'net_profit_month' => $this->getNetProfit($now),
            'margin_month' => $this->getMargin($now),
            'growth_rate' => $this->getGrowthRate(),
            'best_client' => $this->getMostProfitableClient(),
            'best_service' => $this->getMostProfitableService(),
        ];
    }

    public function getRevenueByMonth(int $months = 12): array
    {
        $result = [];
        for ($i = $months - 1; $i >= 0; $i--) {
            $month = now()->subMonths($i);
            $result[] = [
                'label' => $month->format('M Y'),
                'value' => $this->getTotalRevenue($month),
            ];
        }
        return $result;
    }

    public function getExpensesByMonth(int $months = 12): array
    {
        $result = [];
        for ($i = $months - 1; $i >= 0; $i--) {
            $month = now()->subMonths($i);
            $result[] = [
                'label' => $month->format('M Y'),
                'value' => $this->getTotalExpenses($month),
            ];
        }
        return $result;
    }

    public function getCashflowByMonth(int $months = 12): array
    {
        $result = [];
        for ($i = $months - 1; $i >= 0; $i--) {
            $month = now()->subMonths($i);
            $result[] = [
                'label' => $month->format('M Y'),
                'value' => $this->getNetProfit($month),
            ];
        }
        return $result;
    }

    public function getServiceDistribution(): array
    {
        return Service::with(['subscriptions.revenues'])->get()
            ->filter(fn($s) => $s->total_revenue > 0)
            ->map(fn($s) => [
                'label' => $s->name,
                'value' => round($s->total_revenue, 2),
            ])
            ->values()
            ->toArray();
    }

    public function getRecentTransactions(int $limit = 5): array
    {
        $revenues = Revenue::with('client')->orderByDesc('date')->limit($limit)->get()
            ->map(fn($r) => [
                'type' => 'revenue',
                'label' => $r->description ?: 'Revenu',
                'client' => $r->client?->name,
                'amount' => (float) $r->amount,
                'date' => $r->date,
            ]);
        $expenses = Expense::orderByDesc('date')->limit($limit)->get()
            ->map(fn($e) => [
                'type' => 'expense',
                'label' => $e->description ?: $e->category,
                'client' => null,
                'amount' => -(float) $e->amount,
                'date' => $e->date,
            ]);
        return $revenues->concat($expenses)->sortByDesc('date')->take($limit)->values()->toArray();
    }
}
