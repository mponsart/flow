<?php

namespace App\Services;

use App\Models\Client;
use App\Models\Service;
use App\Models\Subscription;
use App\Models\Revenue;
use App\Models\Expense;
use App\Models\Forecast;
use Illuminate\Support\Carbon;

class FinanceService
{
    /**
     * Calcule les principaux KPIs financiers pour le dashboard.
     */
    public function getDashboardKPIs(): array
    {
        $now = now();
        $month = $now->month;
        $year = $now->year;

        $revenusMensuels = Revenue::whereMonth('date', $month)->whereYear('date', $year)->sum('amount');
        $revenusAnnuels = Revenue::whereYear('date', $year)->sum('amount');
        $MRR = Subscription::where('cycle', 'mensuel')->where('status', 'actif')->with('service')->get()->sum(fn($s) => $s->service->price ?? 0);
        $ARR = Subscription::where('cycle', 'annuel')->where('status', 'actif')->with('service')->get()->sum(fn($s) => $s->service->price ?? 0);
        $dépenses = Expense::whereYear('date', $year)->sum('amount');
        $bénéfices = $revenusAnnuels - $dépenses;
        $cashflow = $revenusAnnuels - $dépenses;
        $trésorerie = $cashflow; // simplifié
        $clientsActifs = Client::where('status', 'actif')->count();
        $servicePlusRentable = Service::with('subscriptions')->get()->sortByDesc(function($service) {
            return $service->subscriptions->sum(fn($s) => $s->service->price ?? 0) - $service->subscriptions->sum(fn($s) => $s->service->monthly_cost ?? 0);
        })->first();
        $clientPlusRentable = Client::with('revenues', 'expenses')->get()->sortByDesc(function($client) {
            return $client->revenues->sum('amount') - $client->expenses->sum('amount');
        })->first();
        $croissanceRevenus = $this->getRevenueGrowth();
        $margeMoyenne = $revenusAnnuels > 0 ? round((($revenusAnnuels - $dépenses) / $revenusAnnuels) * 100, 2) : 0;

        return [
            'revenus_mensuels' => $revenusMensuels,
            'revenus_annuels' => $revenusAnnuels,
            'MRR' => $MRR,
            'ARR' => $ARR,
            'dépenses' => $dépenses,
            'bénéfices' => $bénéfices,
            'cashflow' => $cashflow,
            'trésorerie' => $trésorerie,
            'clients_actifs' => $clientsActifs,
            'service_plus_rentable' => $servicePlusRentable?->name,
            'client_plus_rentable' => $clientPlusRentable?->name,
            'croissance_revenus' => $croissanceRevenus,
            'marge_moyenne' => $margeMoyenne,
        ];
    }

    /**
     * Calcule la croissance des revenus sur 2 mois.
     */
    public function getRevenueGrowth(): float
    {
        $now = now();
        $moisActuel = Revenue::whereMonth('date', $now->month)->whereYear('date', $now->year)->sum('amount');
        $moisPrecedent = Revenue::whereMonth('date', $now->subMonth()->month)->whereYear('date', $now->year)->sum('amount');
        if ($moisPrecedent == 0) return 0;
        return round((($moisActuel - $moisPrecedent) / $moisPrecedent) * 100, 2);
    }

    // Autres méthodes de calculs financiers, rentabilité, projections, etc.
}
