<?php

namespace App\Services;

use App\Models\Client;
use App\Models\Service;
use App\Models\Subscription;
use App\Models\Revenue;
use App\Models\Expense;

class ReportService
{
    /**
     * Génère un rapport mensuel avec synthèse IA.
     */
    public function monthlyReport(int $month, int $year): array
    {
        $revenus = Revenue::whereMonth('date', $month)->whereYear('date', $year)->sum('amount');
        $dépenses = Expense::whereMonth('date', $month)->whereYear('date', $year)->sum('amount');
        $bénéfice = $revenus - $dépenses;
        $clients = Client::count();
        $services = Service::count();
        $subscriptions = Subscription::whereMonth('start_date', '<=', now())->whereMonth('end_date', '>=', now())->count();
        return [
            'revenus' => $revenus,
            'dépenses' => $dépenses,
            'bénéfice' => $bénéfice,
            'clients' => $clients,
            'services' => $services,
            'abonnements' => $subscriptions,
        ];
    }

    // Génération PDF, Excel, synthèse IA, etc.
}
