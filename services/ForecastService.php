<?php
class ForecastService
{
    private PDO $pdo;
    private ?array $recurringCache = null;

    public function __construct()
    {
        $this->pdo = getDB();
    }

    public function getMonthlyRevenues(int $months = 18): array
    {
        $data = [];
        for ($i = $months - 1; $i >= 0; $i--) {
            $ts    = strtotime("-$i months");
            $year  = (int)date('Y', $ts);
            $month = (int)date('m', $ts);

            $stmt = $this->pdo->prepare(
                'SELECT COALESCE(SUM(total_ht), 0) FROM invoices
                 WHERE status = 2 AND YEAR(date_invoice) = ? AND MONTH(date_invoice) = ?'
            );
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

        return [
            'historical'  => $revenues,
            'ma3'         => $ma3,
            'ma6'         => $ma6,
            'proj3'       => $proj3,
            'proj6'       => $proj6,
            'proj12'      => $proj12,
            'recurring'   => $this->detectRecurringInvoices(),
            'trend'       => $this->getTrendIndicator($revenues),
            'health'      => $this->getFinancialHealthScore(),
        ];
    }

    public function detectRecurringInvoices(): array
    {
        if ($this->recurringCache !== null) {
            return $this->recurringCache;
        }

        $stmt = $this->pdo->query(
            'SELECT
               i.id,
               i.tiers_id,
               COALESCE(t.name, "Sans tiers") AS tiers_name,
               i.date_invoice,
               i.total_ht,
               COALESCE(p.label, "Service non identifié") AS service_label
             FROM invoices i
             LEFT JOIN tiers t ON t.id = i.tiers_id
             LEFT JOIN (
               SELECT invoice_id, MIN(product_id) AS product_id
               FROM invoice_lines
               WHERE product_id IS NOT NULL
               GROUP BY invoice_id
             ) il ON il.invoice_id = i.id
             LEFT JOIN products p ON p.id = il.product_id
             WHERE i.status = 2
               AND i.date_invoice IS NOT NULL
               AND i.total_ht > 0
             ORDER BY i.tiers_id, service_label, i.date_invoice'
        );

        $groups = [];
        foreach ($stmt->fetchAll() as $row) {
            $key = ($row['tiers_id'] ?? 0) . '|' . $row['service_label'];
            $groups[$key][] = $row;
        }

        $recurring = [];
        foreach ($groups as $rows) {
            $intervals = [];
            for ($i = 1; $i < count($rows); $i++) {
                $intervals[] = (strtotime($rows[$i]['date_invoice']) - strtotime($rows[$i - 1]['date_invoice'])) / 86400;
            }

            $period = $this->classifyPeriod($intervals);
            if ($period === null) continue; // intervalle irrégulier, non projeté

            $amounts = array_map(static fn(array $row): float => (float)$row['total_ht'], $rows);
            $last = end($rows);
            $recurring[] = [
                'tiers_name' => $last['tiers_name'],
                'service_label' => $last['service_label'],
                'period' => $period,
                'period_label' => ['monthly' => 'Mensuelle', 'quarterly' => 'Trimestrielle', 'annual' => 'Annuelle'][$period] ?? ucfirst($period),
                'amount' => round(array_sum($amounts) / count($amounts), 2),
                'last_date' => $last['date_invoice'],
                'next_date' => $this->nextOccurrenceDate($last['date_invoice'], $period),
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

    private function classifyPeriod(array $intervals): ?string
    {
        if (!$intervals) return 'annual';

        $avg = array_sum($intervals) / count($intervals);

        // Tolérance large : le décalage d'une facture de quelques semaines
        // ne doit pas faire basculer une récurrence mensuelle en annuelle.
        if ($avg >= 20 && $avg <= 50)  return 'monthly';    // ~30 j ±50%
        if ($avg >= 75 && $avg <= 115) return 'quarterly';  // ~90 j ±25%
        if ($avg >= 300 && $avg <= 420) return 'annual';    // ~365 j ±15%

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
