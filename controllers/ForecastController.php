<?php
require_once __DIR__ . '/../models/BaseModel.php';
require_once __DIR__ . '/../models/Invoice.php';
require_once __DIR__ . '/../models/Tiers.php';
require_once __DIR__ . '/../services/ForecastService.php';

class ForecastController
{
    public function index(): void
    {
        $data = [
            'historical' => [],
            'ma3' => [],
            'ma6' => [],
            'proj3'  => ['values' => [], 'labels' => [], 'expense_values' => [], 'net_values' => [], 'total' => 0.0, 'total_net' => 0.0, 'total_expenses' => 0.0],
            'proj6'  => ['values' => [], 'labels' => [], 'expense_values' => [], 'net_values' => [], 'total' => 0.0, 'total_net' => 0.0, 'total_expenses' => 0.0],
            'proj12' => ['values' => [], 'labels' => [], 'expense_values' => [], 'net_values' => [], 'total' => 0.0, 'total_net' => 0.0, 'total_expenses' => 0.0],
            'expenses_available'    => false,
            'expenses_monthly_base' => 0.0,
            'mrr'                   => 0.0,
            'arr'                   => 0.0,
            'subscriptions'         => [],
            'recurring'             => [],
            'trend'                 => 'stable',
            'health'                => 0.0,
            'error_message'         => null,
        ];

        $tiersAll = [];
        try {
            $forecastService = new ForecastService();
            $data = array_merge($data, $forecastService->getAllProjections());
            $tiersAll = (new Tiers())->findAll([], 'name ASC');
        } catch (Throwable $e) {
            error_log('ForecastController::index error: ' . $e->getMessage());
            $data['error_message'] = 'Impossible de charger les prévisions pour le moment.';
        }
        $user            = $_SESSION['user'];

        require_once __DIR__ . '/../views/forecast.php';
    }

    public function storeRecurring(): void
    {
        $tiersId = (int)($_POST['tiers_id'] ?? 0);
        $period = trim((string)($_POST['period'] ?? 'monthly'));

        if ($tiersId <= 0) {
            header('Location: ' . APP_URL . '/forecast?error=' . urlencode('Sélectionnez un client.'));
            exit;
        }

        if (!in_array($period, ['monthly', 'quarterly', 'annual'], true)) {
            $period = 'monthly';
        }

        try {
            (new ForecastService())->saveRecurringConfig($tiersId, $period);
            header('Location: ' . APP_URL . '/forecast?message=' . urlencode('Récurrence enregistrée.'));
            exit;
        } catch (Throwable $e) {
            error_log('ForecastController::storeRecurring error: ' . $e->getMessage());
            header('Location: ' . APP_URL . '/forecast?error=' . urlencode('Impossible d\'enregistrer la récurrence.'));
            exit;
        }
    }

    public function deleteRecurring(int $tiersId): void
    {
        try {
            (new ForecastService())->deleteRecurringConfig($tiersId);
            header('Location: ' . APP_URL . '/forecast?message=' . urlencode('Récurrence supprimée.'));
            exit;
        } catch (Throwable $e) {
            error_log('ForecastController::deleteRecurring error: ' . $e->getMessage());
            header('Location: ' . APP_URL . '/forecast?error=' . urlencode('Impossible de supprimer la récurrence.'));
            exit;
        }
    }
}
