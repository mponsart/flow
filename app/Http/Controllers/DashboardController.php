<?php

namespace App\Http\Controllers;

use App\Models\Revenue;
use App\Models\Expense;
use App\Services\FinanceService;
use Illuminate\Support\Facades\Auth;

class DashboardController extends Controller
{
    public function index(FinanceService $finance)
    {
        $kpis = $finance->getKPIs();
        $revenueByMonth = $finance->getRevenueByMonth(12);
        $expensesByMonth = $finance->getExpensesByMonth(12);
        $cashflowByMonth = $finance->getCashflowByMonth(12);
        $serviceDistribution = $finance->getServiceDistribution();
        $recentTransactions = $finance->getRecentTransactions(5);
        $user = Auth::user();

        return view('dashboard', compact(
            'kpis', 'revenueByMonth', 'expensesByMonth',
            'cashflowByMonth', 'serviceDistribution', 'recentTransactions', 'user'
        ));
    }
}
