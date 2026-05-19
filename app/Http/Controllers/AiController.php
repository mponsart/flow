<?php

namespace App\Http\Controllers;

use App\Services\OllamaCloudService;
use Illuminate\Http\Request;

class AiController extends Controller
{
    public function summary(Request $request, OllamaCloudService $ollama)
    {
        $data = $request->all();
        $summary = $ollama->summarize($data);
        return view('ai.summary', compact('summary'));
    }

    public function analyze(Request $request, OllamaCloudService $ollama)
    {
        $data = $request->all();
        $result = $ollama->analyzeFinances($data);
        return view('ai.analyze', compact('result'));
    }

    public function anomalies(Request $request, OllamaCloudService $ollama)
    {
        $data = $request->all();
        $anomalies = $ollama->detectAnomalies($data);
        return view('ai.anomalies', compact('anomalies'));
    }
}
