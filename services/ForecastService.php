<?php
class ForecastService
{
    private PDO $pdo;
    private ?array $recurringCache = null;
    private ?array $expenseInputs = null;
    private ?bool $usePayments = null;

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
            $ts = strtotime("-$i months");
            $year = (int)date('Y', $ts);
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
                'label' => date('M Y', $ts),
                'year' => $year,
                'month' => $month,
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
                continue;
            }

            $slice = array_slice($values, $i - $window + 1, $window);
            $result[] = array_sum($slice) / $window;
        }

        return $result;
    }

    public function getProjections(array $revenues, int $months = 12): array
    {
        $values = array_filter(array_column($revenues, 'revenue'), static fn($v) => $v > 0);
        $labels = [];
        for ($i = 1; $i <= $months; $i++) {
            $labels[] = date('M Y', strtotime("+$i months"));
        }

        if (empty($values)) {
            $recurringProjection = $this->getRecurringProjection($months);
            return [
                'values' => $recurringProjection['values'],
                'labels' => $labels,
                'linear_values' => array_fill(0, $months, 0.0),
                'recurring_values' => $recurringProjection['values'],
            ];
        }

        $n = count($values);
        $ys = array_values($values);
        $xs = range(0, $n - 1);

        $sumX = array_sum($xs);
        $sumY = array_sum($ys);
        $sumXY = 0;
        $sumX2 = 0;
        foreach ($xs as $i => $x) {
            $sumXY += $x * $ys[$i];
            $sumX2 += $x * $x;
        }

        $slope = ($n * $sumXY - $sumX * $sumY) / max(1, ($n * $sumX2 - $sumX * $sumX));
        $intercept = ($sumY - $slope * $sumX) / max(1, $n);

        $linear = [];
        for ($i = 1; $i <= $months; $i++) {
            $linear[] = max(0, round($intercept + $slope * ($n + $i - 1), 2));
        }

        $recurringProjection = $this->getRecurringProjection($months);
        $projections = [];
        foreach ($linear as $i => $value) {
            $projections[] = max($value, (float)($recurringProjection['values'][$i] ?? 0));
        }

        return [
            'values' => $projections,
            'labels' => $labels,
            'linear_values' => $linear,
            'recurring_values' => $recurringProjection['values'],
        ];
    }

    public function getTrendIndicator(array $revenues): string
    {
        $values = array_column($revenues, 'revenue');
        $recent = array_slice($values, -3);
        if (count($recent) < 2) {
            return 'stable';
        }

        $first = array_shift($recent);
        $last = end($recent);
        if ($last > $first * 1.05) {
            return 'up';
        }
        if ($last < $first * 0.95) {
            return 'down';
        }

        return 'stable';
    }

    public function getFinancialHealthScore(): float
    {
        $revenues = $this->getMonthlyRevenues(6);
        $trend = $this->getTrendIndicator($revenues);
        $trendScore = match ($trend) {
            'up' => 100,
            'stable' => 60,
            'down' => 20,
        };

        $stmt = $this->pdo->query(
            'SELECT
               COUNT(*) AS total,
               SUM(CASE WHEN status = 2 THEN 1 ELSE 0 END) AS paid
             FROM invoices'
        );
        $counts = $stmt->fetch();
        $paidRatio = $counts['total'] > 0 ? $counts['paid'] / $counts['total'] : 1;
        $paidScore = $paidRatio * 100;

        $stmt = $this->pdo->query('SELECT COUNT(*) FROM invoices WHERE is_overdue = 1');
        $overdueCount = (int)$stmt->fetchColumn();
        $overdueScore = max(0, 100 - $overdueCount * 10);

        return round(($trendScore * 0.4 + $paidScore * 0.4 + $overdueScore * 0.2), 1);
    }

    public function getAllProjections(): array
    {
        $revenues = $this->getMonthlyRevenues(18);
        $proj3 = $this->getProjections($revenues, 3);
        $proj6 = $this->getProjections($revenues, 6);
        $proj12 = $this->getProjections($revenues, 12);

        $expenseProjection12 = $this->getExpenseProjection(12);
        $historicalExpenses = $this->getHistoricalExpenseSeries($revenues);

        $proj3 = $this->mergeRevenueAndExpenses($proj3, $expenseProjection12['values']);
        $proj6 = $this->mergeRevenueAndExpenses($proj6, $expenseProjection12['values']);
        $proj12 = $this->mergeRevenueAndExpenses($proj12, $expenseProjection12['values']);

        return [
            'historical' => $revenues,
            'historical_expenses' => $historicalExpenses,
            'ma3' => $this->getMovingAverage($revenues, 3),
            'ma6' => $this->getMovingAverage($revenues, 6),
            'proj3' => $proj3,
            'proj6' => $proj6,
            'proj12' => $proj12,
            'expenses_available' => $expenseProjection12['available'],
            'expenses_monthly_base' => $expenseProjection12['monthly_base'],
            'expenses_annual_equivalent' => $expenseProjection12['annual_equivalent'],
            'recurring' => $this->detectRecurringInvoices(),
            'trend' => $this->getTrendIndicator($revenues),
            'health' => $this->getFinancialHealthScore(),
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
                "SELECT amount, expense_date FROM expenses WHERE recurrence = 'one_time'"
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

        $configured = $this->getConfiguredRecurrences();
        if (empty($configured)) {
            $this->recurringCache = [];
            return $this->recurringCache;
        }

        $periodLabels = [
            'monthly' => 'Mensuelle',
            'quarterly' => 'Trimestrielle',
            'annual' => 'Annuelle',
        ];

        $stmt = $this->pdo->prepare(
            'SELECT
               COALESCE(t.name, "Sans tiers") AS tiers_name,
               COALESCE(AVG(CASE WHEN p.amount > 0 THEN p.amount END), 0) AS avg_amount,
               COALESCE(MAX(p.date_payment), CURDATE()) AS last_payment,
               COUNT(CASE WHEN p.amount > 0 THEN 1 END) AS payment_count
             FROM tiers t
             LEFT JOIN payments p ON p.tiers_id = t.id AND p.date_payment IS NOT NULL
             WHERE t.id = ?
             GROUP BY t.id, t.name'
        );

        $rows = [];
        foreach ($configured as $cfg) {
            $stmt->execute([(int)$cfg['tiers_id']]);
            $row = $stmt->fetch();
            if (!$row) {
                continue;
            }

            $lastDate = !empty($row['last_payment']) ? (string)$row['last_payment'] : date('Y-m-d');
            $rows[] = [
                'tiers_id' => (int)$cfg['tiers_id'],
                'tiers_name' => $row['tiers_name'],
                'service_label' => 'Paiement récurrent',
                'period' => $cfg['period'],
                'period_label' => $periodLabels[$cfg['period']] ?? ucfirst($cfg['period']),
                'amount' => round((float)$row['avg_amount'], 2),
                'last_date' => $lastDate,
                'next_date' => $this->nextOccurrenceDate($lastDate, $cfg['period']),
                'invoice_count' => (int)$row['payment_count'],
            ];
        }

        usort($rows, static fn(array $a, array $b): int => strcmp($a['next_date'], $b['next_date']));
        $this->recurringCache = $rows;
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

    public function saveRecurringConfig(int $tiersId, string $period): void
    {
        $allowed = ['monthly', 'quarterly', 'annual'];
        if (!in_array($period, $allowed, true) || $tiersId <= 0) {
            return;
        }

        $rows = $this->getConfiguredRecurrences();
        $rows = array_values(array_filter($rows, static fn(array $r): bool => (int)$r['tiers_id'] !== $tiersId));
        $rows[] = ['tiers_id' => $tiersId, 'period' => $period];

        $this->persistConfiguredRecurrences($rows);
        $this->recurringCache = null;
    }

    public function deleteRecurringConfig(int $tiersId): void
    {
        if ($tiersId <= 0) {
            return;
        }

        $rows = $this->getConfiguredRecurrences();
        $rows = array_values(array_filter($rows, static fn(array $r): bool => (int)$r['tiers_id'] !== $tiersId));

        $this->persistConfiguredRecurrences($rows);
        $this->recurringCache = null;
    }

    public function getConfiguredRecurrences(): array
    {
        try {
            $stmt = $this->pdo->prepare('SELECT value FROM settings WHERE key_name = ? LIMIT 1');
            $stmt->execute(['forecast_recurring_config']);
            $raw = $stmt->fetchColumn();
            if (!$raw) {
                return [];
            }

            $decoded = json_decode((string)$raw, true);
            if (!is_array($decoded)) {
                return [];
            }

            $allowed = ['monthly', 'quarterly', 'annual'];
            $clean = [];
            foreach ($decoded as $row) {
                $tiersId = (int)($row['tiers_id'] ?? 0);
                $period = (string)($row['period'] ?? '');
                if ($tiersId > 0 && in_array($period, $allowed, true)) {
                    $clean[] = ['tiers_id' => $tiersId, 'period' => $period];
                }
            }

            return $clean;
        } catch (Throwable $e) {
            error_log('Forecast recurring config read error: ' . $e->getMessage());
            return [];
        }
    }

    private function persistConfiguredRecurrences(array $rows): void
    {
        $json = json_encode(array_values($rows), JSON_UNESCAPED_UNICODE);
        if ($json === false) {
            return;
        }

        $stmt = $this->pdo->prepare(
            'INSERT INTO settings (key_name, value)
             VALUES (?, ?)
             ON DUPLICATE KEY UPDATE value = VALUES(value), updated_at = CURRENT_TIMESTAMP'
        );
        $stmt->execute(['forecast_recurring_config', $json]);
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
