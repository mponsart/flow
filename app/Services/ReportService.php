<?php

namespace App\Services;

use App\Models\Client;
use App\Models\Service;
use App\Models\Subscription;
use App\Models\Revenue;
use App\Models\Expense;

class ReportService
{
    public function __construct(private FinanceService $finance) {}

    public function getReportData(): array
    {
        $now = now();
        return [
            'generated_at' => $now->format('d/m/Y H:i'),
            'period' => $now->format('F Y'),
            'kpis' => $this->finance->getKPIs(),
            'revenue_by_month' => $this->finance->getRevenueByMonth(12),
            'expenses_by_month' => $this->finance->getExpensesByMonth(12),
            'cashflow_by_month' => $this->finance->getCashflowByMonth(12),
            'service_distribution' => $this->finance->getServiceDistribution(),
            'top_clients' => Client::with('revenues', 'expenses')->get()
                ->sortByDesc(fn($c) => $c->total_revenue - $c->total_expenses)
                ->take(5)
                ->values(),
            'top_services' => Service::with(['subscriptions.revenues'])->get()
                ->sortByDesc(fn($s) => $s->total_revenue)
                ->take(5)
                ->values(),
            'clients_count' => Client::where('status', 'actif')->count(),
            'services_count' => Service::where('status', 'actif')->count(),
            'subscriptions_count' => Subscription::where('status', 'actif')->count(),
            'recent_revenues' => Revenue::with('client')->orderByDesc('date')->take(10)->get(),
            'recent_expenses' => Expense::orderByDesc('date')->take(10)->get(),
        ];
    }

    public function generatePDF(): string
    {
        $data = $this->getReportData();
        $html = view('reports.pdf', compact('data'))->render();

        // If barryvdh/laravel-dompdf is available
        if (class_exists(\Barryvdh\DomPDF\Facade\Pdf::class)) {
            return \Barryvdh\DomPDF\Facade\Pdf::loadHTML($html)->output();
        }

        // Fallback: return HTML as PDF-like content
        return $html;
    }
}
