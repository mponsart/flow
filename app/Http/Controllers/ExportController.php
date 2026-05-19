<?php

namespace App\Http\Controllers;

use App\Services\ReportService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Response;

class ExportController extends Controller
{
    public function pdf(Request $request, ReportService $report)
    {
        // Génération PDF simplifiée (exemple, à adapter avec dompdf ou snappy)
        $data = $report->monthlyReport(now()->month, now()->year);
        $html = view('exports.pdf', compact('data'))->render();
        // Utiliser un package PDF réel en production
        return Response::make($html, 200, [
            'Content-Type' => 'application/pdf',
            'Content-Disposition' => 'inline; filename="rapport.pdf"'
        ]);
    }

    public function excel(Request $request, ReportService $report)
    {
        // Génération Excel simplifiée (exemple, à adapter avec Laravel Excel)
        $data = $report->monthlyReport(now()->month, now()->year);
        $csv = "KPI, Valeur\n";
        foreach ($data as $k => $v) {
            $csv .= "$k, $v\n";
        }
        return Response::make($csv, 200, [
            'Content-Type' => 'text/csv',
            'Content-Disposition' => 'attachment; filename="rapport.csv"'
        ]);
    }
}
