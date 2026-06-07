<?php

namespace App\Http\Controllers;

use App\Services\ReportService;

class ReportController extends Controller
{
    public function index(ReportService $report)
    {
        $data = $report->getReportData();
        return view('reports.index', compact('data'));
    }

    public function downloadPDF(ReportService $report)
    {
        $data = $report->getReportData();
        $html = view('reports.pdf', compact('data'))->render();

        return response($html, 200, [
            'Content-Type' => 'text/html',
            'Content-Disposition' => 'inline; filename="rapport-financier-' . now()->format('Y-m-d') . '.html"',
        ]);
    }
}
