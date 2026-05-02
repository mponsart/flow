<?php
require_once __DIR__ . '/../models/BaseModel.php';
require_once __DIR__ . '/../models/Expense.php';

class ExpensesController
{
    private Expense $model;

    public function __construct()
    {
        $this->model = new Expense();
    }

    public function index(): void
    {
        $expenses       = $this->model->findAllOrdered();
        $monthlyTotal   = $this->model->getMonthlyEquivalent();
        $annualTotal    = $this->model->getAnnualEquivalent();
        $byCategory     = $this->model->getByCategory();

        // Revenus réels des 12 derniers mois pour comparaison
        $pdo = getDB();
        $stmt = $pdo->query(
            "SELECT COALESCE(SUM(total_ht), 0) AS total
             FROM invoices
             WHERE status = 2
               AND date_invoice >= DATE_FORMAT(DATE_SUB(CURDATE(), INTERVAL 11 MONTH), '%Y-%m-01')
               AND date_invoice <= LAST_DAY(CURDATE())"
        );
        $revenueYear = (float)$stmt->fetchColumn();
        $revenueMonth = $revenueYear / 12;

        $profitMonth  = $revenueMonth - $monthlyTotal;
        $profitYear   = $revenueYear  - $annualTotal;

        $user = $_SESSION['user'];

        require_once __DIR__ . '/../views/expenses.php';
    }

    public function store(): void
    {
        $label       = trim($_POST['label'] ?? '');
        $amount      = (float)str_replace(',', '.', $_POST['amount'] ?? '0');
        $category    = trim($_POST['category'] ?? 'Autre');
        $recurrence  = $_POST['recurrence'] ?? 'monthly';
        $expenseDate = $_POST['expense_date'] ?? null;
        $note        = trim($_POST['note'] ?? '');

        if ($label === '' || $amount <= 0) {
            header('Location: ' . APP_URL . '/expenses?error=' . urlencode('Libellé et montant obligatoires.'));
            exit;
        }

        if (!in_array($recurrence, ['monthly', 'annual', 'one_time'], true)) {
            $recurrence = 'monthly';
        }

        $this->model->create($label, $amount, $category ?: 'Autre', $recurrence, $expenseDate ?: null, $note);
        header('Location: ' . APP_URL . '/expenses?message=' . urlencode('Dépense ajoutée.'));
        exit;
    }

    public function update(int $id): void
    {
        $expense = $this->model->findById($id);
        if (!$expense) {
            header('Location: ' . APP_URL . '/expenses?error=' . urlencode('Dépense introuvable.'));
            exit;
        }

        $label       = trim($_POST['label'] ?? '');
        $amount      = (float)str_replace(',', '.', $_POST['amount'] ?? '0');
        $category    = trim($_POST['category'] ?? 'Autre');
        $recurrence  = $_POST['recurrence'] ?? 'monthly';
        $expenseDate = $_POST['expense_date'] ?? null;
        $note        = trim($_POST['note'] ?? '');

        if ($label === '' || $amount <= 0) {
            header('Location: ' . APP_URL . '/expenses?error=' . urlencode('Libellé et montant obligatoires.'));
            exit;
        }

        if (!in_array($recurrence, ['monthly', 'annual', 'one_time'], true)) {
            $recurrence = 'monthly';
        }

        $this->model->update($id, $label, $amount, $category ?: 'Autre', $recurrence, $expenseDate ?: null, $note);
        header('Location: ' . APP_URL . '/expenses?message=' . urlencode('Dépense mise à jour.'));
        exit;
    }

    public function destroy(int $id): void
    {
        $this->model->delete($id);
        header('Location: ' . APP_URL . '/expenses?message=' . urlencode('Dépense supprimée.'));
        exit;
    }
}
