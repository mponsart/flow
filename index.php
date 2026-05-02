<?php
require_once __DIR__ . '/config/app.php';
require_once __DIR__ . '/config/database.php';
require_once __DIR__ . '/config/auth.php';

// Parse request URI
$requestUri  = parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH);
$requestUri  = rtrim($requestUri, '/');
$requestUri  = $requestUri ?: '/';
$method      = $_SERVER['REQUEST_METHOD'];

// CSRF validation for all POST requests
function validateCsrf(): void
{
    $token = $_POST['csrf_token'] ?? ($_SERVER['HTTP_X_CSRF_TOKEN'] ?? '');
    if (!hash_equals($_SESSION['csrf_token'] ?? '', $token)) {
        if (($_SERVER['REQUEST_URI'] ?? '') === '/sync/force') {
            header('Location: ' . APP_URL . '/sync?error=' . urlencode('Session expirée ou formulaire invalide. Rechargez la page puis relancez la synchronisation.'));
            exit;
        }

        http_response_code(403);
        die('Session expirée ou formulaire invalide.');
    }
}

// Autoload controllers and services
function loadClass(string $class): void
{
    $paths = [
        __DIR__ . '/controllers/' . $class . '.php',
        __DIR__ . '/services/'    . $class . '.php',
        __DIR__ . '/models/'      . $class . '.php',
    ];
    foreach ($paths as $path) {
        if (file_exists($path)) {
            require_once $path;
            return;
        }
    }
}

// Public routes (no auth required)
$publicRoutes = [
    '/login',
    '/auth/google',
    '/auth/google/callback',
];

$isPublic = in_array($requestUri, $publicRoutes, true);

// Auth check
require_once __DIR__ . '/services/AuthService.php';
$authService = new AuthService();

if (!$isPublic && !$authService->isLoggedIn()) {
    header('Location: ' . APP_URL . '/login');
    exit;
}

// Router
switch (true) {
    // Auth routes
    case $requestUri === '/login' && $method === 'GET':
        loadClass('AuthController');
        (new AuthController($authService))->login();
        break;

    case $requestUri === '/auth/google' && $method === 'GET':
        loadClass('AuthController');
        (new AuthController($authService))->googleRedirect();
        break;

    case $requestUri === '/auth/google/callback' && $method === 'GET':
        loadClass('AuthController');
        (new AuthController($authService))->googleCallback();
        break;

    case $requestUri === '/logout' && $method === 'POST':
        validateCsrf();
        loadClass('AuthController');
        (new AuthController($authService))->logout();
        break;

    // Dashboard
    case $requestUri === '/' || $requestUri === '/dashboard':
        require_once __DIR__ . '/services/KPIService.php';
        require_once __DIR__ . '/models/BaseModel.php';
        require_once __DIR__ . '/models/Invoice.php';
        require_once __DIR__ . '/models/Tiers.php';
        loadClass('DashboardController');
        (new DashboardController())->index();
        break;

    // Tiers
    case $requestUri === '/tiers' && $method === 'GET':
        require_once __DIR__ . '/models/BaseModel.php';
        require_once __DIR__ . '/models/Tiers.php';
        require_once __DIR__ . '/services/RiskScoringService.php';
        loadClass('TiersController');
        (new TiersController())->index();
        break;

    case preg_match('#^/tiers/(\d+)$#', $requestUri, $m) && $method === 'GET':
        require_once __DIR__ . '/models/BaseModel.php';
        require_once __DIR__ . '/models/Tiers.php';
        require_once __DIR__ . '/models/Invoice.php';
        require_once __DIR__ . '/models/Payment.php';
        require_once __DIR__ . '/services/RiskScoringService.php';
        require_once __DIR__ . '/services/PaymentAnalyzerService.php';
        loadClass('TiersController');
        (new TiersController())->detail((int)$m[1]);
        break;

    // Payments
    case $requestUri === '/payments' && $method === 'GET':
        require_once __DIR__ . '/models/BaseModel.php';
        require_once __DIR__ . '/models/Payment.php';
        require_once __DIR__ . '/services/PaymentAnalyzerService.php';
        loadClass('PaymentsController');
        (new PaymentsController())->index();
        break;

    // Forecast
    case $requestUri === '/forecast' && $method === 'GET':
        require_once __DIR__ . '/models/BaseModel.php';
        require_once __DIR__ . '/models/Invoice.php';
        require_once __DIR__ . '/services/ForecastService.php';
        loadClass('ForecastController');
        (new ForecastController())->index();
        break;

    // Expenses
    case $requestUri === '/expenses' && $method === 'GET':
        require_once __DIR__ . '/models/BaseModel.php';
        require_once __DIR__ . '/models/Expense.php';
        loadClass('ExpensesController');
        (new ExpensesController())->index();
        break;

    case $requestUri === '/expenses/store' && $method === 'POST':
        validateCsrf();
        require_once __DIR__ . '/models/BaseModel.php';
        require_once __DIR__ . '/models/Expense.php';
        loadClass('ExpensesController');
        (new ExpensesController())->store();
        break;

    case preg_match('#^/expenses/update/(\d+)$#', $requestUri, $m) && $method === 'POST':
        validateCsrf();
        require_once __DIR__ . '/models/BaseModel.php';
        require_once __DIR__ . '/models/Expense.php';
        loadClass('ExpensesController');
        (new ExpensesController())->update((int)$m[1]);
        break;

    case preg_match('#^/expenses/delete/(\d+)$#', $requestUri, $m) && $method === 'POST':
        validateCsrf();
        require_once __DIR__ . '/models/BaseModel.php';
        require_once __DIR__ . '/models/Expense.php';
        loadClass('ExpensesController');
        (new ExpensesController())->destroy((int)$m[1]);
        break;

    // Sync
    case $requestUri === '/sync' && $method === 'GET':
        require_once __DIR__ . '/services/DolibarrService.php';
        loadClass('SyncController');
        (new SyncController())->index();
        break;

    case $requestUri === '/sync/force' && $method === 'POST':
        validateCsrf();
        require_once __DIR__ . '/services/DolibarrService.php';
        loadClass('SyncController');
        (new SyncController())->forceSync();
        break;

    // Export
    case $requestUri === '/export/csv' && $method === 'GET':
        require_once __DIR__ . '/models/BaseModel.php';
        require_once __DIR__ . '/models/Invoice.php';
        require_once __DIR__ . '/models/Tiers.php';
        require_once __DIR__ . '/models/Payment.php';
        loadClass('ExportController');
        (new ExportController())->exportCsv();
        break;

    case $requestUri === '/export/pdf' && $method === 'GET':
        require_once __DIR__ . '/models/BaseModel.php';
        require_once __DIR__ . '/models/Invoice.php';
        require_once __DIR__ . '/services/KPIService.php';
        loadClass('ExportController');
        (new ExportController())->exportPdf();
        break;

    // 404
    default:
        http_response_code(404);
        require_once __DIR__ . '/views/partials/header.php';
        echo '<div style="padding:2rem;text-align:center"><h1>404 – Page introuvable</h1><a href="' . APP_URL . '/">Retour au tableau de bord</a></div>';
        require_once __DIR__ . '/views/partials/footer.php';
        break;
}
