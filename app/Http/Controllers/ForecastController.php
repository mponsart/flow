<?php
namespace App\Http\Controllers;
use App\Services\FinanceService;
use Carbon\Carbon;

class ForecastController extends Controller
{
    public function __construct(private FinanceService $finance) {}

    public function index()
    {
        $now = Carbon::now();
        $year = $now->year;
        $currentMonth = $now->month;

        // Historique : Jan → mois courant
        $history = [];
        for ($m = 1; $m <= $currentMonth; $m++) {
            $rev = $this->finance->getTotalRevenueForMonth($year, $m);
            $exp = $this->finance->getTotalExpensesForMonth($year, $m);
            $history[$m] = [
                'month' => $m,
                'revenue' => $rev,
                'expenses' => $exp,
                'profit' => $rev - $exp,
                'margin' => $rev > 0 ? round(($rev - $exp) / $rev * 100, 1) : 0,
                'is_current' => $m === $currentMonth,
            ];
        }

        // Projection : mois+1 → décembre
        $projectionMonths = $this->finance->getYearRevenueProjection(); // [month => amount]
        $monthlyExpenses = $this->finance->getTotalExpensesForMonth($year, $currentMonth); // dépenses de référence

        $forecast = [];
        foreach ($projectionMonths as $m => $projRevenue) {
            $forecast[$m] = [
                'month' => $m,
                'revenue' => $projRevenue,
                'expenses' => $monthlyExpenses, // on suppose les dépenses constantes
                'profit' => $projRevenue - $monthlyExpenses,
                'margin' => $projRevenue > 0 ? round(($projRevenue - $monthlyExpenses) / $projRevenue * 100, 1) : 0,
            ];
        }

        // KPIs annuels
        $ytdRevenue = $this->finance->getYTDRevenue();
        $ytdExpenses = $this->finance->getYTDExpenses();
        $ytdProfit = $this->finance->getYTDProfit();
        $projectedAnnualRevenue = $this->finance->getProjectedAnnualRevenue();
        $projectedAnnualExpenses = $ytdExpenses + ($monthlyExpenses * (12 - $currentMonth));
        $projectedAnnualProfit = $projectedAnnualRevenue - $projectedAnnualExpenses;

        // Données pour le graphique annuel (12 mois)
        $chartLabels = [];
        $chartRevHistory = [];
        $chartRevForecast = [];
        $chartExpenses = [];
        $chartProfit = [];

        $monthNames = ['', 'Jan', 'Fév', 'Mar', 'Avr', 'Mai', 'Jun', 'Jul', 'Aoû', 'Sep', 'Oct', 'Nov', 'Déc'];

        for ($m = 1; $m <= 12; $m++) {
            $chartLabels[] = $monthNames[$m];
            if ($m <= $currentMonth) {
                $rev = $history[$m]['revenue'];
                $exp = $history[$m]['expenses'];
                $chartRevHistory[] = $rev;
                $chartRevForecast[] = null; // pas de projection pour les mois passés
                $chartExpenses[] = $exp;
                $chartProfit[] = $rev - $exp;
            } else {
                $rev = $forecast[$m]['revenue'] ?? 0;
                $exp = $monthlyExpenses;
                $chartRevHistory[] = null; // pas d'historique pour les mois futurs
                $chartRevForecast[] = $rev;
                $chartExpenses[] = $exp;
                $chartProfit[] = $rev - $exp;
            }
        }

        // Point de jonction : le mois courant apparaît dans les deux datasets pour la continuité visuelle
        $chartRevForecast[$currentMonth - 1] = $history[$currentMonth]['revenue'];

        $chartData = [
            'labels' => $chartLabels,
            'datasets' => [
                [
                    'label' => 'Revenus réels',
                    'data' => $chartRevHistory,
                    'borderColor' => '#6366f1',
                    'backgroundColor' => 'rgba(99,102,241,0.08)',
                    'borderWidth' => 2,
                    'tension' => 0.3,
                    'fill' => true,
                    'spanGaps' => false,
                ],
                [
                    'label' => 'Revenus projetés',
                    'data' => $chartRevForecast,
                    'borderColor' => '#6366f1',
                    'backgroundColor' => 'rgba(99,102,241,0.03)',
                    'borderWidth' => 2,
                    'borderDash' => [6, 4],
                    'tension' => 0.3,
                    'fill' => true,
                    'spanGaps' => false,
                ],
                [
                    'label' => 'Dépenses',
                    'data' => $chartExpenses,
                    'borderColor' => '#f43f5e',
                    'backgroundColor' => 'transparent',
                    'borderWidth' => 1.5,
                    'tension' => 0.1,
                    'fill' => false,
                    'borderDash' => [3, 3],
                ],
            ],
        ];

        return view('forecasts.index', compact(
            'history', 'forecast', 'currentMonth', 'year',
            'ytdRevenue', 'ytdExpenses', 'ytdProfit',
            'projectedAnnualRevenue', 'projectedAnnualExpenses', 'projectedAnnualProfit',
            'chartData', 'monthNames'
        ));
    }
}
