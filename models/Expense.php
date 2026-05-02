<?php
require_once __DIR__ . '/BaseModel.php';

class Expense extends BaseModel
{
    protected string $table = 'expenses';

    public function findAllOrdered(): array
    {
        return $this->pdo->query(
            "SELECT * FROM expenses ORDER BY recurrence ASC, category ASC, label ASC"
        )->fetchAll();
    }

    public function create(string $label, float $amount, string $category, string $recurrence, ?string $expenseDate, ?string $note): int
    {
        $stmt = $this->pdo->prepare(
            'INSERT INTO expenses (label, amount, category, recurrence, expense_date, note)
             VALUES (?, ?, ?, ?, ?, ?)'
        );
        $stmt->execute([$label, $amount, $category, $recurrence, $expenseDate ?: null, $note ?: null]);
        return (int)$this->pdo->lastInsertId();
    }

    public function update(int $id, string $label, float $amount, string $category, string $recurrence, ?string $expenseDate, ?string $note): void
    {
        $stmt = $this->pdo->prepare(
            'UPDATE expenses SET label=?, amount=?, category=?, recurrence=?, expense_date=?, note=?, updated_at=NOW()
             WHERE id=?'
        );
        $stmt->execute([$label, $amount, $category, $recurrence, $expenseDate ?: null, $note ?: null, $id]);
    }

    public function delete(int $id): void
    {
        $this->pdo->prepare('DELETE FROM expenses WHERE id=?')->execute([$id]);
    }

    /**
     * Charge mensuel équivalent : monthly×1, annual÷12, one_time÷12 (annualisé).
     */
    public function getMonthlyEquivalent(): float
    {
        $stmt = $this->pdo->query(
            "SELECT recurrence, SUM(amount) AS total
             FROM expenses
             GROUP BY recurrence"
        );
        $total = 0.0;
        foreach ($stmt->fetchAll() as $row) {
            $total += match ($row['recurrence']) {
                'monthly'  => (float)$row['total'],
                'annual'   => (float)$row['total'] / 12,
                'one_time' => (float)$row['total'] / 12,
            };
        }
        return $total;
    }

    /**
     * Charge annuelle équivalente : monthly×12, annual×1, one_time×1.
     */
    public function getAnnualEquivalent(): float
    {
        $stmt = $this->pdo->query(
            "SELECT recurrence, SUM(amount) AS total
             FROM expenses
             GROUP BY recurrence"
        );
        $total = 0.0;
        foreach ($stmt->fetchAll() as $row) {
            $total += match ($row['recurrence']) {
                'monthly'  => (float)$row['total'] * 12,
                'annual'   => (float)$row['total'],
                'one_time' => (float)$row['total'],
            };
        }
        return $total;
    }

    /** Dépenses regroupées par catégorie (charge mensuelle équivalente). */
    public function getByCategory(): array
    {
        $rows = $this->findAllOrdered();
        $cats = [];
        foreach ($rows as $row) {
            $monthly = match ($row['recurrence']) {
                'monthly'  => (float)$row['amount'],
                'annual'   => (float)$row['amount'] / 12,
                'one_time' => (float)$row['amount'] / 12,
            };
            $cat = $row['category'];
            if (!isset($cats[$cat])) {
                $cats[$cat] = 0.0;
            }
            $cats[$cat] += $monthly;
        }
        arsort($cats);
        return $cats;
    }
}
