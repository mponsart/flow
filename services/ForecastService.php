<?php
class ForecastService
{
    private PDO    $pdo;
    private ?array $recurringCache   = null;
    private ?array $expenseInputs    = null;
    private ?bool  $usePayments      = null;

    public function __construct()
    {
        $this->pdo = getDB();
    }

    private function shouldUsePayments(): bool
    {
        if ($this->usePayments === null) {
            $stmt = $this->pdo->query('SELECT COUNT(*) FROM payments WHERE date_payment IS NOT NULL');
            $this->usePayments = (int)$stmt->fetchColumn() > 0;
        }
        return $this->usePayments;
    }

    public function getMonthlyRevenues(int $months = 18): array
    {
        $data = [];
        $usePayments = $this->shouldUsePayments();

        for ($i = $months - 1; $i >= 0; $i--) {
            $ts    = strtotime("-$i months");
            $year  = (int)date('Y', $ts);
            $month = (int)date('m', $ts);

            if ($usePayments) {
                $stmt = $this->pdo->prepare(
                    'SELECT COALESCE(SUM(amount), 0) FROM payments
                     WHERE YEAR(date_payment) = ? AND MONTH(date_payment) = ?'
                );
            } else {
                $stmt = $this->pdo->prepare(
                    'SELECT COALESCE(SUM(total_ht), 0) FROM invoices
                     WHERE status = 2 AND YEAR(date_invoice) = ? AND MONTH(date_invoice) = ?'
                );
            }
            $stmt->execute([$year, $month]);
            $data[] = [
                'label'   => date('M Y', $ts),
                'year'    => $year,
                'month'   => $month,
                'revenue' => (float)$stmt->fetchColumn(),
            ];
        }
        return $data;
    }

    public function getMovingAverage(array $revenues, int $window = 3): array
    {
        $result = [];
        $values = array_column($revenues, 'revenue');

        for ($i = 0; $i < count($values); $i++) {
            if ($i < $window - 1) {
                $result[] = null;
            } else {
                $slice    = array_slice($values, $i - $window + 1, $window);
                $result[] = array_sum($slice) / $window;
            }
        }
        return $result;
    }

    public function getProjections(array $revenues, int $months = 12): array
    {
        $values = array_filter(array_column($revenues, 'revenue'), fn($v) => $v > 0);
        if (empty($values)) {
            $labels = [];
            for ($i = 1; $i <= $months; $i++) {
                $labels[] = date('M Y', strtotime("+$i months"));
            }

            $recurringProjection = $this->getRecurringProjection($months);
            return [
                'values' => $recurringProjection['values'],
                'labels' => $labels,
                'linear_values' => array_fill(0, $months, 0.0),
                'recurring_values' => $recurringProjection['values'],
            ];
        }

        // Linear regression
        $n  = count($values);
        $xs = range(0, $n - 1);
        $sumX  = array_sum($xs);
        $sumY  = array_sum($values);
        $sumXY = 0;
        $sumX2 = 0;

        foreach ($xs as $i => $x) {
            $sumXY += $x * array_values($values)[$i];
            $sumX2 += $x * $x;
        }

        $slope     = ($n * $sumXY - $sumX * $sumY) / max(1, ($n * $sumX2 - $sumX * $sumX));
        $intercept = ($sumY - $slope * $sumX) / $n;

        $linearProjections = [];
        $labels      = [];
        for ($i = 1; $i <= $months; $i++) {
            $ts = strtotime("+$i months");
            $projectedValue = $intercept + $slope * ($n + $i - 1);
            $linearProjections[]  = max(0, round($projectedValue, 2));
            $labels[]       = date('M Y', $ts);
        }

        $recurringProjection = $this->getRecurringProjection($months);
        $projections = [];
        foreach ($linearProjections as $i => $value) {
            $recurringValue = $recurringProjection['values'][$i] ?? 0;
            $projections[] = max($value, $recurringValue);
        }

        return [
            'values' => $projections,
            'labels' => $labels,
            'linear_values' => $linearProjections,
            'recurring_values' => $recurringProjection['values'],
        ];
    }

    public function getTrendIndicator(array $revenues): string
    {
        $values = array_column($revenues, 'revenue');
        $recent = array_slice($values, -3);

        if (count($recent) < 2) return 'stable';

        $first = array_shift($recent);
        $last  = end($recent);

        if ($last > $first * 1.05)  return 'up';
        if ($last < $first * 0.95)  return 'down';
        return 'stable';
    }

    public function getFinancialHealthScore(): float
    {
        $pdo = $this->pdo;

        // Revenue trend
        $revenues = $this->getMonthlyRevenues(6);
        $trend    = $this->getTrendIndicator($revenues);
        $trendScore = match ($trend) {
            'up'     => 100,
            'stable' => 60,
            'down'   => 20,
        };

        // Paid ratio
        $stmt = $pdo->query(
            'SELECT
               COUNT(*) AS total,
               SUM(CASE WHEN status = 2 THEN 1 ELSE 0 END) AS paid
             FROM invoices'
        );
        $counts    = $stmt->fetch();
        $paidRatio = $counts['total'] > 0 ? $counts['paid'] / $counts['total'] : 1;
        $paidScore = $paidRatio * 100;

        // Overdue ratio (negative)
        $stmt = $pdo->query('SELECT COUNT(*) FROM invoices WHERE is_overdue = 1');
        $overdueCount = (int)$stmt->fetchColumn();
        $overdueScore = max(0, 100 - $overdueCount * 10);

        return round(($trendScore * 0.4 + $paidScore * 0.4 + $overdueScore * 0.2), 1);
    }

    public function getAllProjections(): array
    {
        $revenues = $this->getMonthlyRevenues(18);
        $ma3  = $this->getMovingAverage($revenues, 3);
        $ma6  = $this->getMovingAverage($revenues, 6);
        $proj3  = $this->getProjections($revenues, 3);
        $proj6  = $this->getProjections($revenues, 6);
        $proj12 = $this->getProjections($revenues, 12);

        $expenseProjection12 = $this->getExpenseProjection(12);
        $historicalExpenses  = $this->getHistoricalExpenseSeries($revenues);

        $proj3  = $this->mergeRevenueAndExpenses($proj3, $expenseProjection12['values']);
        $proj6  = $this->mergeRevenueAndExpenses($proj6, $expenseProjection12['values']);
        $proj12 = $this->mergeRevenueAndExpenses($proj12, $expenseProjection12['values']);

        return [
            'historical'  => $revenues,
            'historical_expenses' => $historicalExpenses,
            'ma3'         => $ma3,
            'ma6'         => $ma6,
            'proj3'       => $proj3,
            'proj6'       => $proj6,
            'proj12'      => $proj12,
            'expenses_available' => $expenseProjection12['available'],
            'expenses_monthly_base' => $expenseProjection12['monthly_base'],
            'expenses_annual_equivalent' => $expenseProjection12['annual_equivalent'],
            'recurring'   => $this->detectRecurringInvoices(),
            'trend'       => $this->getTrendIndicator($revenues),
            'health'      => $this->getFinancialHealthScore(),
        ];
    }

    private function mergeRevenueAndExpenses(array $projection, array $expenseProjection12): array
    {
        $count = count($projection['values'] ?? []);
        $expenseValues = array_slice($expenseProjection12, 0, $count);
        if (count($expenseValues) < $count) {
            $expenseValues = array_pad($expenseValues, $count, 0.0);
        }

        $netValues = [];
        foreach ($projection['values'] as $i => $gross) {
            $netValues[] = round((float)$gross - (float)$expenseValues[$i], 2);
        }

        $projection['expense_values'] = array_map(static fn($v): float => round((float)$v, 2), $expenseValues);
        $projection['net_values'] = $netValues;

        return $projection;
    }

    private function getExpenseProjection(int $months): array
    {
        $inputs = $this->loadExpenseInputs();
        if (!$inputs['available']) {
            return [
                'available' => false,
                'values' => array_fill(0, $months, 0.0),
                'monthly_base' => 0.0,
                'annual_equivalent' => 0.0,
            ];
        }

        $values = array_fill(0, $months, round($inputs['monthly_base'], 2));

        foreach ($inputs['one_time_rows'] as $row) {
            if (empty($row['expense_date'])) {
                continue;
            }

            $monthIndex = ((int)date('Y', strtotime($row['expense_date'])) - (int)date('Y')) * 12
                + ((int)date('m', strtotime($row['expense_date'])) - (int)date('m')) - 1;

            if ($monthIndex >= 0 && $monthIndex < $months) {
                $values[$monthIndex] += (float)$row['amount'];
            }
        }

        return [
            'available' => true,
            'values' => array_map(static fn($v): float => round((float)$v, 2), $values),
            'monthly_base' => round((float)$inputs['monthly_base'], 2),
            'annual_equivalent' => round((float)$inputs['annual_equivalent'], 2),
        ];
    }

    private function getHistoricalExpenseSeries(array $revenues): array
    {
        $inputs = $this->loadExpenseInputs();
        if (!$inputs['available']) {
            return array_fill(0, count($revenues), 0.0);
        }

        $values = [];
        foreach ($revenues as $monthData) {
            $year = (int)($monthData['year'] ?? 0);
            $month = (int)($monthData['month'] ?? 0);
            $value = (float)$inputs['monthly_base'];

            foreach ($inputs['one_time_rows'] as $row) {
                if (empty($row['expense_date'])) {
                    continue;
                }

                if ((int)date('Y', strtotime($row['expense_date'])) === $year
                    && (int)date('m', strtotime($row['expense_date'])) === $month
                ) {
                    $value += (float)$row['amount'];
                }
            }

            $values[] = round($value, 2);
        }

        return $values;
    }

    private function loadExpenseInputs(): array
    {
        if ($this->expenseInputs !== null) {
            return $this->expenseInputs;
        }

        try {
            $check = $this->pdo->query("SHOW TABLES LIKE 'expenses'");
            if (!$check || !$check->fetchColumn()) {
                $this->expenseInputs = [
                    'available' => false,
                    'monthly_base' => 0.0,
                    'annual_equivalent' => 0.0,
                    'one_time_rows' => [],
                ];
                return $this->expenseInputs;
            }

            $stmt = $this->pdo->query(
                'SELECT recurrence, COALESCE(SUM(amount), 0) AS total
                 FROM expenses
                 GROUP BY recurrence'
            );

            $monthly = 0.0;
            $annual = 0.0;
            $oneTime = 0.0;
            foreach ($stmt->fetchAll() as $row) {
                $total = (float)($row['total'] ?? 0);
                switch ($row['recurrence']) {
                    case 'monthly':
                        $monthly += $total;
                        break;
                    case 'annual':
                        $annual += $total;
                        break;
                    case 'one_time':
                        $oneTime += $total;
                        break;
                }
            }

            $oneTimeStmt = $this->pdo->query(
                "SELECT amount, expense_date
                 FROM expenses
                 WHERE recurrence = 'one_time'"
            );

            $this->expenseInputs = [
                'available' => true,
                'monthly_base' => $monthly + ($annual / 12) + ($oneTime / 12),
                'annual_equivalent' => ($monthly * 12) + $annual + $oneTime,
                'one_time_rows' => $oneTimeStmt ? $oneTimeStmt->fetchAll() : [],
            ];
        } catch (Throwable $e) {
            error_log('Forecast expenses unavailable: ' . $e->getMessage());
            $this->expenseInputs = [
                'available' => false,
                'monthly_base' => 0.0,
                'annual_equivalent' => 0.0,
                'one_time_rows' => [],
            ];
        }

        return $this->expenseInputs;
    }

    public function detectRecurringInvoices(): array
    {
        if ($this->recurringCache !== null) {
            return $this->recurringCache;
        }

        // Analyser TOUTES les factures payées — pas de filtre temporel amont
        // Le filtrage se fait après, sur la date de prochaine occurrence
        $stmt = $this->pdo->query(
            'SELECT
               i.tiers_id,
               COALESCE(t.name, "Sans tiers") AS tiers_name,
               i.date_invoice,
               i.total_ht,
                             COALESCE(p.label, il_meta.line_description, "Service non identifié") AS service_label,
                             COALESCE(il_meta.nb_lines, 0) AS nb_lines
             FROM invoices i
             LEFT JOIN tiers t ON t.id = i.tiers_id
             LEFT JOIN (
                             SELECT
                                 invoice_id,
                                 MIN(product_id) AS product_id,
                                 MIN(NULLIF(TRIM(description), \'\')) AS line_description,
                                 COUNT(*) AS nb_lines
               FROM invoice_lines
               GROUP BY invoice_id
                         ) il_meta ON il_meta.invoice_id = i.id
                         LEFT JOIN products p ON p.id = il_meta.product_id
                         WHERE (i.status IN (1, 2) OR i.date_paid IS NOT NULL OR EXISTS (
                                         SELECT 1 FROM payments px WHERE px.invoice_id = i.id
                                     ))
               AND i.tiers_id IS NOT NULL
               AND i.date_invoice IS NOT NULL
                             AND i.date_invoice >= DATE_SUB(CURDATE(), INTERVAL 36 MONTH)
               AND i.total_ht > 0
             ORDER BY i.tiers_id, service_label, i.date_invoice'
        );

        if (!$stmt) {
            $this->recurringCache = [];
            return [];
        }

        $groups = [];
        foreach ($stmt->fetchAll() as $row) {
            $key = ($row['tiers_id'] ?? 0) . '|' . $row['service_label'];
            $groups[$key][] = $row;
        }

        $periodLabels = [
            'monthly'   => 'Mensuelle',
            'quarterly' => 'Trimestrielle',
            'annual'    => 'Annuelle',
        ];

        $recurring = [];
        foreach ($groups as $rows) {
            $intervals = [];
            for ($i = 1; $i < count($rows); $i++) {
                $intervals[] = (strtotime($rows[$i]['date_invoice']) - strtotime($rows[$i - 1]['date_invoice'])) / 86400;
            }

            $last    = end($rows);
            $amounts = array_map(static fn(array $r): float => (float)$r['total_ht'], $rows);
            $avgAmount  = array_sum($amounts) / count($amounts);
            $nbLines    = (int)($last['nb_lines'] ?? 0);

            $period  = $this->classifyPeriod($intervals, $last['date_invoice'], $avgAmount, $nbLines);
            if ($period === null) continue;

            $nextDate = $this->nextOccurrenceDate($last['date_invoice'], $period);

            $recurring[] = [
                'tiers_name'    => $last['tiers_name'],
                'service_label' => $last['service_label'],
                'period'        => $period,
                'period_label'  => $periodLabels[$period] ?? ucfirst($period),
                'amount'        => round($avgAmount, 2),
                'last_date'     => $last['date_invoice'],
                'next_date'     => $nextDate,
                'invoice_count' => count($rows),
            ];
        }

        usort($recurring, static fn(array $a, array $b): int => strcmp($a['next_date'], $b['next_date']));

        $this->recurringCache = $recurring;
        return $this->recurringCache;
    }

    private function getRecurringProjection(int $months): array
    {
        $values = array_fill(0, $months, 0.0);
        $recurring = $this->detectRecurringInvoices();

        foreach ($recurring as $item) {
            $date = $item['next_date'];
            while (strtotime($date) <= strtotime("+$months months")) {
                $monthIndex = ((int)date('Y', strtotime($date)) - (int)date('Y')) * 12
                    + ((int)date('m', strtotime($date)) - (int)date('m')) - 1;

                if ($monthIndex >= 0 && $monthIndex < $months) {
                    $values[$monthIndex] += (float)$item['amount'];
                }

                $date = $this->nextOccurrenceDate($date, $item['period']);
            }
        }

        return ['values' => array_map(static fn(float $value): float => round($value, 2), $values)];
    }

    private function classifyPeriod(array $intervals, string $lastDate = '', float $avgAmount = 0.0, int $nbLines = 0): ?string
    {
        if (!$intervals) {
            // Facture unique : pas de pattern prouvé, on essaie de deviner
            if ($lastDate !== '' && strtotime($lastDate) < strtotime('-14 months')) {
                // Trop ancienne → client probablement parti
                return null;
            }
            // Sans répétition observée, on ne classe jamais en mensuel.
            // On considère annuel par défaut pour une facture unique récente.
            return 'annual';
        }

        $avg = array_sum($intervals) / count($intervals);

        // Tolérance large : le décalage de quelques semaines ne doit pas
        // faire basculer une récurrence mensuelle en annuelle.
        if ($avg >= 20  && $avg <= 50)  return 'monthly';    // ~30 j ±50%
        if ($avg >= 75  && $avg <= 115) return 'quarterly';  // ~90 j ±25%
        if ($avg >= 300 && $avg <= 420) return 'annual';     // ~365 j ±15%

        // Intervalle ambigu ou irrégulier → on ne projette pas
        return null;
    }

    private function nextOccurrenceDate(string $date, string $period): string
    {
        $modifier = match ($period) {
            'monthly' => '+1 month',
            'quarterly' => '+3 months',
            'annual' => '+1 year',
            default => '+1 month',
        };

        $next = date('Y-m-d', strtotime($modifier, strtotime($date)));
        while (strtotime($next) < strtotime('first day of this month')) {
            $next = date('Y-m-d', strtotime($modifier, strtotime($next)));
        }

        return $next;
    }
}
