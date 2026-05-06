<?php
class ForecastService
{
    private PDO $pdo;
    private ?array $recurringCache = null;
    private ?array $subscriptionsCache = null;
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
        $labels = [];
        for ($i = 1; $i <= $months; $i++) {
            $labels[] = date('M Y', strtotime("+$i months"));
        }

        // Subscription-based projection (primary source of truth).
        // Historical payments already include subscription payments — adding linear
        // regression on top would double-count them.
        $subValues = $this->getSubscriptionProjection($months);
        $hasSubs   = array_sum($subValues) > 0;

        if ($hasSubs) {
            $projValues = $subValues;
        } else {
            // Fallback: linear regression on historical payments when no subscriptions.
            $ys = array_map(static fn($v): float => (float)$v, array_column($revenues, 'revenue'));
            $n  = count($ys);
            $projValues = array_fill(0, $months, 0.0);

            if ($n > 0 && max($ys) > 0) {
                $xs    = range(0, $n - 1);
                $sumX  = array_sum($xs);
                $sumY  = array_sum($ys);
                $sumXY = 0;
                $sumX2 = 0;
                foreach ($xs as $i => $x) {
                    $sumXY += $x * $ys[$i];
                    $sumX2 += $x * $x;
                }
                $slope     = ($n * $sumXY - $sumX * $sumY) / max(1, ($n * $sumX2 - $sumX * $sumX));
                $intercept = ($sumY - $slope * $sumX) / max(1, $n);

                for ($i = 1; $i <= $months; $i++) {
                    $projValues[$i - 1] = max(0.0, round($intercept + $slope * ($n + $i - 1), 2));
                }
            }
        }

        return [
            'values' => $projValues,
            'labels' => $labels,
            'total'  => round(array_sum($projValues), 2),
        ];
    }

    private function getSubscriptionProjection(int $months): array
    {
        $values = array_fill(0, $months, 0.0);

        foreach ($this->getActiveSubscriptions() as $sub) {
            $amount = (float)($sub['amount'] ?? 0);
            if ($amount <= 0) {
                continue;
            }

            $period    = (string)($sub['recurrence'] ?? 'monthly');
            $startDate = !empty($sub['start_date']) ? (string)$sub['start_date'] : date('Y-m-d');
            $endDate   = !empty($sub['end_date'])   ? (string)$sub['end_date']   : null;

            for ($i = 0; $i < $months; $i++) {
                $tTs = strtotime('+' . ($i + 1) . ' months');
                $tY  = (int)date('Y', $tTs);
                $tM  = (int)date('m', $tTs);

                if ($endDate !== null && $tTs > strtotime($endDate)) {
                    continue;
                }

                $sTs  = strtotime($startDate);
                $diff = ($tY - (int)date('Y', $sTs)) * 12 + ($tM - (int)date('m', $sTs));

                if ($diff < 0) {
                    continue;
                }

                $applies = match ($period) {
                    'monthly'   => true,
                    'quarterly' => $diff % 3 === 0,
                    'annual'    => $diff % 12 === 0,
                    'one_time'  => $diff === 0,
                    default     => false,
                };

                if ($applies) {
                    $values[$i] += $amount;
                }
            }
        }

        return array_map(static fn(float $v): float => round($v, 2), $values);
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
        $trend    = $this->getTrendIndicator($revenues);
        $trendScore = match ($trend) {
            'up'     => 100,
            'stable' => 60,
            'down'   => 20,
        };

        // Coverage: monthly revenue (or MRR) vs monthly expenses
        $inputs = $this->loadExpenseInputs();
        $monthlyExpenses = $inputs['available'] ? (float)$inputs['monthly_base'] : 0.0;

        $mrr = 0.0;
        foreach ($this->getActiveSubscriptions() as $sub) {
            $amount = (float)($sub['amount'] ?? 0);
            $mrr += match ((string)($sub['recurrence'] ?? 'monthly')) {
                'monthly'   => $amount,
                'quarterly' => $amount / 3,
                'annual'    => $amount / 12,
                default     => 0.0,
            };
        }

        $recentRevs = array_column(array_slice($revenues, -3), 'revenue');
        $avgRevenue = count($recentRevs) > 0 ? (float)(array_sum($recentRevs) / count($recentRevs)) : 0.0;
        $effectiveRevenue = max($avgRevenue, $mrr);

        $coverageScore = 100;
        if ($monthlyExpenses > 0) {
            $ratio = $effectiveRevenue / $monthlyExpenses;
            $coverageScore = (int)min(100, max(0, $ratio * 80));
        }

        return round($trendScore * 0.5 + $coverageScore * 0.5, 1);
    }

    public function getAllProjections(): array
    {
        $revenues = $this->getMonthlyRevenues(18);
        $proj3    = $this->getProjections($revenues, 3);
        $proj6    = $this->getProjections($revenues, 6);
        $proj12   = $this->getProjections($revenues, 12);

        $expenseProjection12 = $this->getExpenseProjection(12);

        $proj3  = $this->mergeRevenueAndExpenses($proj3,  $expenseProjection12['values']);
        $proj6  = $this->mergeRevenueAndExpenses($proj6,  $expenseProjection12['values']);
        $proj12 = $this->mergeRevenueAndExpenses($proj12, $expenseProjection12['values']);

        $mrr = 0.0;
        foreach ($this->getActiveSubscriptions() as $sub) {
            $amount = (float)($sub['amount'] ?? 0);
            $mrr += match ((string)($sub['recurrence'] ?? 'monthly')) {
                'monthly'   => $amount,
                'quarterly' => $amount / 3,
                'annual'    => $amount / 12,
                default     => 0.0,
            };
        }

        return [
            'historical'            => $revenues,
            'ma3'                   => $this->getMovingAverage($revenues, 3),
            'ma6'                   => $this->getMovingAverage($revenues, 6),
            'proj3'                 => $proj3,
            'proj6'                 => $proj6,
            'proj12'                => $proj12,
            'expenses_available'    => $expenseProjection12['available'],
            'expenses_monthly_base' => $expenseProjection12['monthly_base'],
            'mrr'                   => round($mrr, 2),
            'arr'                   => round($mrr * 12, 2),
            'subscriptions'         => $this->getActiveSubscriptions(),
            'recurring'             => $this->detectRecurringInvoices(),
            'trend'                 => $this->getTrendIndicator($revenues),
            'health'                => $this->getFinancialHealthScore(),
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
        $projection['net_values']     = $netValues;
        $projection['total']          = round(array_sum($projection['values']), 2);
        $projection['total_net']      = round(array_sum($netValues), 2);
        $projection['total_expenses'] = round(array_sum($expenseValues), 2);
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
            $quarterly = 0.0;
            $oneTime = 0.0;
            foreach ($stmt->fetchAll() as $row) {
                $total = (float)($row['total'] ?? 0);
                switch ($row['recurrence']) {
                    case 'monthly':
                        $monthly += $total;
                        break;
                    case 'quarterly':
                        $quarterly += $total;
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
                // one_time est injecté sur son mois réel, pas lissé mensuellement.
                'monthly_base' => $monthly + ($quarterly / 3) + ($annual / 12),
                'annual_equivalent' => ($monthly * 12) + ($quarterly * 4) + $annual + $oneTime,
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

        $rows = [];
        $rows = array_merge($rows, $this->getSubscriptionRecurringRows());

        $configured = $this->getConfiguredRecurrences();
        if (empty($configured)) {
            usort($rows, static fn(array $a, array $b): int => strcmp((string)($a['next_date'] ?? ''), (string)($b['next_date'] ?? '')));
            $this->recurringCache = $rows;
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
                'source' => 'payments',
            ];
        }

        usort($rows, static fn(array $a, array $b): int => strcmp((string)($a['next_date'] ?? ''), (string)($b['next_date'] ?? '')));
        $this->recurringCache = $rows;
        return $this->recurringCache;
    }

    private function getRecurringProjection(int $months): array
    {
        $values = array_fill(0, $months, 0.0);
        $recurring = $this->detectRecurringInvoices();

        foreach ($recurring as $item) {
            if (($item['source'] ?? '') === 'subscriptions') {
                continue;
            }

            $date = $item['next_date'];
            if (($item['period'] ?? '') === 'one_time') {
                $monthIndex = ((int)date('Y', strtotime($date)) - (int)date('Y')) * 12
                    + ((int)date('m', strtotime($date)) - (int)date('m')) - 1;

                if ($monthIndex >= 0 && $monthIndex < $months) {
                    $values[$monthIndex] += (float)$item['amount'];
                }
                continue;
            }

            while (strtotime($date) <= strtotime("+$months months")) {
                $monthIndex = ((int)date('Y', strtotime($date)) - (int)date('Y')) * 12
                    + ((int)date('m', strtotime($date)) - (int)date('m')) - 1;

                if ($monthIndex >= 0 && $monthIndex < $months) {
                    $values[$monthIndex] += (float)$item['amount'];
                }

                $date = $this->nextOccurrenceDate($date, $item['period']);
            }
        }

        // Ajoute les abonnements actifs (produits/clients) à la projection.
        foreach ($this->getActiveSubscriptions() as $subscription) {
            $startDate = !empty($subscription['start_date']) ? (string)$subscription['start_date'] : date('Y-m-01');
            $endDate = !empty($subscription['end_date']) ? (string)$subscription['end_date'] : null;
            $period = (string)($subscription['recurrence'] ?? 'monthly');
            $amount = (float)($subscription['amount'] ?? 0);

            if ($amount <= 0) {
                continue;
            }

            for ($monthIndex = 0; $monthIndex < $months; $monthIndex++) {
                $targetMonth = date('Y-m-01', strtotime('+' . ($monthIndex + 1) . ' month'));
                if (strtotime($targetMonth) < strtotime(date('Y-m-01', strtotime($startDate)))) {
                    continue;
                }
                if ($endDate !== null && strtotime($targetMonth) > strtotime(date('Y-m-01', strtotime($endDate)))) {
                    continue;
                }

                $diffMonths = ((int)date('Y', strtotime($targetMonth)) - (int)date('Y', strtotime($startDate))) * 12
                    + ((int)date('m', strtotime($targetMonth)) - (int)date('m', strtotime($startDate)));

                if ($diffMonths < 0) {
                    continue;
                }

                if ($period === 'monthly') {
                    $values[$monthIndex] += $amount;
                } elseif ($period === 'quarterly' && $diffMonths % 3 === 0) {
                    $values[$monthIndex] += $amount;
                } elseif ($period === 'annual' && $diffMonths % 12 === 0) {
                    $values[$monthIndex] += $amount;
                } elseif ($period === 'one_time' && $diffMonths === 0) {
                    $values[$monthIndex] += $amount;
                }
            }
        }

        return ['values' => array_map(static fn(float $value): float => round($value, 2), $values)];
    }

    private function getActiveSubscriptions(): array
    {
        if ($this->subscriptionsCache !== null) {
            return $this->subscriptionsCache;
        }

        try {
            $check = $this->pdo->query(
                "SELECT COUNT(*)
                 FROM information_schema.tables
                 WHERE table_schema = DATABASE()
                   AND table_name = 'subscriptions'"
            );
            if (!$check || (int)$check->fetchColumn() === 0) {
                $this->subscriptionsCache = [];
                return $this->subscriptionsCache;
            }

            $stmt = $this->pdo->query(
                "SELECT s.id, s.tiers_id, s.product_id, s.label, s.amount, s.recurrence, s.start_date, s.end_date,
                        t.name AS tiers_name,
                        COALESCE(p.label, s.label, 'Abonnement') AS service_label
                 FROM subscriptions s
                 JOIN tiers t ON t.id = s.tiers_id
                 LEFT JOIN products p ON p.id = s.product_id
                 WHERE s.is_active = 1
                   AND (s.end_date IS NULL OR s.end_date >= CURDATE())"
            );

            $this->subscriptionsCache = $stmt ? $stmt->fetchAll() : [];
        } catch (Throwable $e) {
            error_log('Forecast subscriptions unavailable: ' . $e->getMessage());
            $this->subscriptionsCache = [];
        }

        return $this->subscriptionsCache;
    }

    private function getSubscriptionRecurringRows(): array
    {
        $periodLabels = [
            'monthly' => 'Mensuelle',
            'quarterly' => 'Trimestrielle',
            'annual' => 'Annuelle',
            'one_time' => 'Unique',
        ];

        $rows = [];
        foreach ($this->getActiveSubscriptions() as $s) {
            $startDate = !empty($s['start_date']) ? (string)$s['start_date'] : date('Y-m-d');
            $nextDate = (string)$s['recurrence'] === 'one_time'
                ? $startDate
                : $this->nextOccurrenceDate($startDate, (string)$s['recurrence']);

            $rows[] = [
                'tiers_id' => (int)($s['tiers_id'] ?? 0),
                'tiers_name' => (string)($s['tiers_name'] ?? 'Sans tiers'),
                'service_label' => (string)($s['service_label'] ?? 'Abonnement'),
                'period' => (string)($s['recurrence'] ?? 'monthly'),
                'period_label' => $periodLabels[(string)($s['recurrence'] ?? 'monthly')] ?? ucfirst((string)($s['recurrence'] ?? 'monthly')),
                'amount' => round((float)($s['amount'] ?? 0), 2),
                'last_date' => $startDate,
                'next_date' => $nextDate,
                'invoice_count' => null,
                'source' => 'subscriptions',
            ];
        }

        return $rows;
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
