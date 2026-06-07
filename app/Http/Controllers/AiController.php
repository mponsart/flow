<?php

namespace App\Http\Controllers;

use App\Models\AiReport;
use App\Services\FinanceService;
use App\Services\OllamaCloudService;
use Illuminate\Http\Request;

class AiController extends Controller
{
    public function index()
    {
        $reports = AiReport::orderByDesc('created_at')->paginate(10);
        return view('ai.index', compact('reports'));
    }

    public function summary(Request $request, FinanceService $finance, OllamaCloudService $ollama)
    {
        $data = array_merge($finance->getKPIs(), [
            'revenue_by_month' => $finance->getRevenueByMonth(6),
            'expenses_by_month' => $finance->getExpensesByMonth(6),
        ]);
        $data['best_client'] = $data['best_client']?->name;
        $data['best_service'] = $data['best_service']?->name;
        $report = $ollama->generateSummary($data);
        return redirect()->route('ai.show', $report->id)->with('success', 'Résumé généré avec succès.');
    }

    public function analysis(Request $request, FinanceService $finance, OllamaCloudService $ollama)
    {
        $data = array_merge($finance->getKPIs(), [
            'revenue_by_month' => $finance->getRevenueByMonth(12),
            'expenses_by_month' => $finance->getExpensesByMonth(12),
            'cashflow_by_month' => $finance->getCashflowByMonth(12),
            'service_distribution' => $finance->getServiceDistribution(),
        ]);
        $data['best_client'] = $data['best_client']?->name;
        $data['best_service'] = $data['best_service']?->name;
        $report = $ollama->generateAnalysis($data);
        return redirect()->route('ai.show', $report->id)->with('success', 'Analyse générée avec succès.');
    }

    public function anomalies(Request $request, FinanceService $finance, OllamaCloudService $ollama)
    {
        $data = array_merge($finance->getKPIs(), [
            'revenue_by_month' => $finance->getRevenueByMonth(6),
            'expenses_by_month' => $finance->getExpensesByMonth(6),
        ]);
        $data['best_client'] = $data['best_client']?->name;
        $data['best_service'] = $data['best_service']?->name;
        $report = $ollama->detectAnomalies($data);
        return redirect()->route('ai.show', $report->id)->with('success', 'Analyse des anomalies générée.');
    }

    public function show(AiReport $report)
    {
        return view('ai.show', compact('report'));
    }
}
