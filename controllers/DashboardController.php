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
        $monthlyRevenue = (float)($kpis['monthly_revenue'] ?? 0);
        $monthlyGrowthPct = (float)($kpis['growth_rate'] ?? 0);
        $unpaidAmount  = (float)$kpiService->getUnpaidAmount();
        $overdueAmount = (float)$kpiService->getOverdueAmount();

        $unpaidAmountYear = 0.0;
        $overdueAmountYear = 0.0;
        $top1SharePct = 0.0;
        $top3SharePct = 0.0;
        try {
            $pdo = getDB();

            $stmt = $pdo->prepare(
                'SELECT COALESCE(SUM(total_ttc), 0)
                 FROM invoices
                 WHERE status IN (0,1)
                   AND YEAR(date_invoice) = ?'
            );
            $stmt->execute([$year]);
            $unpaidAmountYear = (float)$stmt->fetchColumn();

            $stmt = $pdo->prepare(
                'SELECT COALESCE(SUM(total_ttc), 0)
                 FROM invoices
                 WHERE is_overdue = 1
                   AND YEAR(date_invoice) = ?'
            );
            $stmt->execute([$year]);
            $overdueAmountYear = (float)$stmt->fetchColumn();

            $stmt = $pdo->prepare(
                'SELECT tiers_id, COALESCE(SUM(amount), 0) AS revenue
                 FROM payments
                 WHERE tiers_id IS NOT NULL
                   AND YEAR(date_payment) = ?
                 GROUP BY tiers_id
                 ORDER BY revenue DESC
                 LIMIT 3'
            );
            $stmt->execute([$year]);
            $topCashRows = $stmt->fetchAll();

            if (!empty($topCashRows) && $annualRevenue > 0) {
                $top1SharePct = round(((float)$topCashRows[0]['revenue'] / $annualRevenue) * 100, 1);
                $top3Revenue = 0.0;
                foreach ($topCashRows as $row) {
                    $top3Revenue += (float)$row['revenue'];
                }
                $top3SharePct = round(($top3Revenue / $annualRevenue) * 100, 1);
            }
        } catch (Throwable $e) {
            error_log('Dashboard yearly consistency metrics unavailable: ' . $e->getMessage());
        }

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
        $monthlyProfit  = $monthlyRevenue - $monthlyExpenses;
        $marginPct      = $annualRevenue > 0 ? round(($annualProfit / $annualRevenue) * 100, 1) : 0.0;
        $expenseRatePct = $annualRevenue > 0 ? round(($annualExpenses / $annualRevenue) * 100, 1) : 0.0;
        $overdueRiskPct = $annualRevenue > 0 ? round(($overdueAmountYear / $annualRevenue) * 100, 1) : 0.0;

        $openAmount = $annualRevenue + $unpaidAmountYear;
        $collectionRateAmountPct = $openAmount > 0 ? round(($annualRevenue / $openAmount) * 100, 1) : 0.0;
        $overdueOnOpenPct = $unpaidAmountYear > 0 ? round(($overdueAmountYear / $unpaidAmountYear) * 100, 1) : 0.0;

        $revenueSeries = array_map(static fn(array $item): float => (float)($item['revenue'] ?? 0), $kpis['revenue_evolution'] ?? []);
        $avgMonthlyRevenue = !empty($revenueSeries) ? array_sum($revenueSeries) / count($revenueSeries) : 0.0;
        $runRateAnnual = $avgMonthlyRevenue * 12;

        $variance = 0.0;
        if (!empty($revenueSeries) && $avgMonthlyRevenue > 0) {
            foreach ($revenueSeries as $value) {
                $variance += (($value - $avgMonthlyRevenue) ** 2);
            }
            $variance /= count($revenueSeries);
        }
        $stdDev = sqrt($variance);
        $volatilityPct = $avgMonthlyRevenue > 0 ? round(($stdDev / $avgMonthlyRevenue) * 100, 1) : 0.0;

        $runwayMonths = null;
        if ($monthlyExpenses > 0 && $monthlyProfit < 0) {
            $runwayMonths = round($annualRevenue / $monthlyExpenses, 1);
        }

        $cashCoverageRatio = $monthlyExpenses > 0 ? round($monthlyRevenue / $monthlyExpenses, 2) : null;
        $cashCoverageStatus = $cashCoverageRatio === null
            ? 'solide'
            : ($cashCoverageRatio >= 1.2 ? 'solide' : ($cashCoverageRatio >= 1.0 ? 'fragile' : 'critique'));

        $activeClientsMonth = 0;
        $avgOverdueDays = 0.0;
        $dueSoonAmount = 0.0;
        try {
            $pdo = getDB();

            $stmt = $pdo->query(
                'SELECT COUNT(DISTINCT tiers_id)
                 FROM payments
                 WHERE tiers_id IS NOT NULL
                   AND YEAR(date_payment) = YEAR(CURDATE())
                   AND MONTH(date_payment) = MONTH(CURDATE())'
            );
            $activeClientsMonth = (int)$stmt->fetchColumn();

            $stmt = $pdo->query(
                'SELECT COALESCE(AVG(DATEDIFF(CURDATE(), date_due)), 0)
                 FROM invoices
                 WHERE is_overdue = 1
                   AND status IN (0,1)
                     AND date_due IS NOT NULL
                     AND YEAR(date_invoice) = YEAR(CURDATE())'
            );
            $avgOverdueDays = (float)$stmt->fetchColumn();

            $stmt = $pdo->query(
                'SELECT COALESCE(SUM(total_ttc), 0)
                 FROM invoices
                 WHERE status IN (0,1)
                   AND date_due IS NOT NULL
                                     AND YEAR(date_invoice) = YEAR(CURDATE())
                   AND date_due >= CURDATE()
                   AND date_due <= DATE_ADD(CURDATE(), INTERVAL 15 DAY)'
            );
            $dueSoonAmount = (float)$stmt->fetchColumn();
        } catch (Throwable $e) {
            error_log('Dashboard operational KPI error: ' . $e->getMessage());
        }

        $delayRiskStatus = $avgOverdueDays <= 10 ? 'solide' : ($avgOverdueDays <= 30 ? 'fragile' : 'critique');

        $marginStatus = $marginPct >= 20 ? 'solide' : ($marginPct >= 5 ? 'fragile' : 'critique');
        $collectionStatus = $collectionRateAmountPct >= 90 ? 'solide' : ($collectionRateAmountPct >= 75 ? 'fragile' : 'critique');
        $volatilityStatus = $volatilityPct <= 15 ? 'solide' : ($volatilityPct <= 30 ? 'fragile' : 'critique');
        $concentrationStatus = $top3SharePct <= 40 ? 'solide' : ($top3SharePct <= 65 ? 'fragile' : 'critique');

        $alerts = [];
        if ($annualProfit < 0) {
            $alerts[] = 'Résultat annuel négatif: ajuster les charges ou augmenter le CA.';
        }
        if ($collectionRateAmountPct < 80) {
            $alerts[] = 'Encaissement faible: accélérer les relances sur les impayés.';
        }
        if ($overdueOnOpenPct > 50) {
            $alerts[] = 'Part des retards élevée dans les impayés: risque cash court terme.';
        }
        if ($top3SharePct > 65) {
            $alerts[] = 'Forte dépendance clients (top 3): diversifier le portefeuille.';
        }
        if ($volatilityPct > 30) {
            $alerts[] = 'CA instable: lisser la facturation et sécuriser des revenus récurrents.';
        }
        if ($cashCoverageRatio !== null && $cashCoverageRatio < 1) {
            $alerts[] = 'Couverture des charges inférieure à 1: le mois en cours ne couvre pas les coûts.';
        }
        if ($avgOverdueDays > 30) {
            $alerts[] = 'Retard moyen de paiement élevé: action de recouvrement ciblée recommandée.';
        }

        $kpis['annual_summary'] = [
            'year'              => $year,
            'annual_revenue'    => $annualRevenue,
            'annual_expenses'   => $annualExpenses,
            'monthly_expenses'  => $monthlyExpenses,
            'annual_profit'     => $annualProfit,
            'monthly_profit'    => $monthlyProfit,
            'monthly_revenue'   => $monthlyRevenue,
            'monthly_growth_pct'=> $monthlyGrowthPct,
            'margin_pct'        => $marginPct,
            'expense_rate_pct'  => $expenseRatePct,
            'paid_rate_pct'     => $paidRatePct,
            'overdue_rate_pct'  => $overdueRatePct,
            'collection_rate_amount_pct' => $collectionRateAmountPct,
            'overdue_on_open_pct' => $overdueOnOpenPct,
            'avg_monthly_revenue' => $avgMonthlyRevenue,
            'run_rate_annual'  => $runRateAnnual,
            'volatility_pct'   => $volatilityPct,
            'volatility_status'=> $volatilityStatus,
            'top1_share_pct'   => $top1SharePct,
            'top3_share_pct'   => $top3SharePct,
            'concentration_status' => $concentrationStatus,
            'runway_months'    => $runwayMonths,
            'cash_coverage_ratio' => $cashCoverageRatio,
            'cash_coverage_status' => $cashCoverageStatus,
            'active_clients_month' => $activeClientsMonth,
            'avg_overdue_days' => round($avgOverdueDays, 1),
            'delay_risk_status' => $delayRiskStatus,
            'due_soon_amount' => $dueSoonAmount,
            'unpaid_amount'     => $unpaidAmount,
            'overdue_amount'    => $overdueAmount,
            'unpaid_amount_year' => $unpaidAmountYear,
            'overdue_amount_year' => $overdueAmountYear,
            'overdue_risk_pct'  => $overdueRiskPct,
            'margin_status'     => $marginStatus,
            'collection_status' => $collectionStatus,
            'alerts'            => $alerts,
            'expenses_available'=> $expensesAvailable,
            'expense_categories'=> $expenseCategories,
        ];

        $user       = $_SESSION['user'];

        require_once __DIR__ . '/../views/dashboard.php';
    }
}
