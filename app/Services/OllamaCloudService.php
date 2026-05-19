<?php

namespace App\Services;

use Illuminate\Support\Facades\Http;

class OllamaCloudService
{
    protected string $apiUrl;
    protected string $apiKey;

    public function __construct()
    {
        $this->apiUrl = config('services.ollama_cloud.url', env('OLLAMA_CLOUD_API_URL'));
        $this->apiKey = config('services.ollama_cloud.key', env('OLLAMA_CLOUD_API_KEY'));
    }

    /**
     * Analyse les finances et génère un résumé IA.
     */
    public function analyzeFinances(array $data): array
    {
        $response = Http::withToken($this->apiKey)
            ->post($this->apiUrl . '/analyze', $data);
        return $response->json();
    }

    /**
     * Génère un résumé IA personnalisé.
     */
    public function summarize(array $data): string
    {
        $response = Http::withToken($this->apiKey)
            ->post($this->apiUrl . '/summarize', $data);
        return $response->json('summary', '');
    }

    /**
     * Détecte les anomalies financières.
     */
    public function detectAnomalies(array $data): array
    {
        $response = Http::withToken($this->apiKey)
            ->post($this->apiUrl . '/anomalies', $data);
        return $response->json('anomalies', []);
    }

    // Autres méthodes IA : conseils, prévisions, détection clients/services déficitaires...
}
