<?php

namespace App\Http\Controllers;

use App\Services\FinanceService;
use Illuminate\Support\Facades\Auth;

class DashboardController extends Controller
{
    public function index(FinanceService $finance)
    {
        $kpis = $finance->getDashboardKPIs();
        $user = Auth::user();
        return view('dashboard', compact('kpis', 'user'));
    }
}
