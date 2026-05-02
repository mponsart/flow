<?php
require_once __DIR__ . '/../models/BaseModel.php';
require_once __DIR__ . '/../models/Invoice.php';
require_once __DIR__ . '/../models/Tiers.php';
require_once __DIR__ . '/../services/KPIService.php';

class DashboardController
{
    public function index(): void
    {
        $kpiService = new KPIService();
        $kpis       = $kpiService->getAll();
        $year       = (int)date('Y');

        $invoiceCounts = $kpis['invoice_counts'] ?? ['total' => 0, 'paid' => 0, 'overdue' => 0];
        $totalInvoices = (int)($invoiceCounts['total'] ?? 0);
        $paidInvoices  = (int)($invoiceCounts['paid'] ?? 0);
        $overdueInvoices = (int)($invoiceCounts['overdue'] ?? 0);

        $paidRatePct    = $totalInvoices > 0 ? round(($paidInvoices / $totalInvoices) * 100, 1) : 0.0;
        $overdueRatePct = $totalInvoices > 0 ? round(($overdueInvoices / $totalInvoices) * 100, 1) : 0.0;

        $annualRevenue = (float)($kpis['annual_revenue'] ?? 0);
        $unpaidAmount  = (float)$kpiService->getUnpaidAmount();
        $overdueAmount = (float)$kpiService->getOverdueAmount();

        $annualExpenses = 0.0;
        $monthlyExpenses = 0.0;
        $expenseCategories = [];
        $expensesAvailable = false;

        try {
            require_once __DIR__ . '/../models/Expense.php';
            $expenseModel = new Expense();
            $annualExpenses   = $expenseModel->getAnnualEquivalent();
            $monthlyExpenses  = $expenseModel->getMonthlyEquivalent();
            $expenseCategories = $expenseModel->getByCategory();
            $expensesAvailable = true;
        } catch (Throwable $e) {
            error_log('Dashboard expenses metrics unavailable: ' . $e->getMessage());
        }

        $annualProfit   = $annualRevenue - $annualExpenses;
        $marginPct      = $annualRevenue > 0 ? round(($annualProfit / $annualRevenue) * 100, 1) : 0.0;
        $expenseRatePct = $annualRevenue > 0 ? round(($annualExpenses / $annualRevenue) * 100, 1) : 0.0;
        $overdueRiskPct = $annualRevenue > 0 ? round(($overdueAmount / $annualRevenue) * 100, 1) : 0.0;

        $kpis['annual_summary'] = [
            'year'              => $year,
            'annual_revenue'    => $annualRevenue,
            'annual_expenses'   => $annualExpenses,
            'monthly_expenses'  => $monthlyExpenses,
            'annual_profit'     => $annualProfit,
            'margin_pct'        => $marginPct,
            'expense_rate_pct'  => $expenseRatePct,
            'paid_rate_pct'     => $paidRatePct,
            'overdue_rate_pct'  => $overdueRatePct,
            'unpaid_amount'     => $unpaidAmount,
            'overdue_amount'    => $overdueAmount,
            'overdue_risk_pct'  => $overdueRiskPct,
            'expenses_available'=> $expensesAvailable,
            'expense_categories'=> $expenseCategories,
        ];

        $user       = $_SESSION['user'];

        require_once __DIR__ . '/../views/dashboard.php';
    }
}
