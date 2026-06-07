<?php

namespace App\Services;

use App\Models\AiReport;
use Illuminate\Support\Facades\Http;

class OllamaCloudService
{
    protected string $apiUrl;
    protected string $model;
    protected string $systemPrompt = 'Tu es un analyste financier expert pour associations. Réponds en français de manière claire et structurée.';

    public function __construct()
    {
        $this->apiUrl = config('services.ollama_cloud.url', env('OLLAMA_CLOUD_API_URL', 'https://api.ollama.cloud/v1'));
        $this->model = env('OLLAMA_MODEL', 'llama3.2');
    }

    public function generateSummary(array $financialData): AiReport
    {
        $prompt = $this->buildPrompt('summary', $financialData);
        $content = $this->callApi($prompt, $financialData, 'summary');
        return $this->saveReport('summary', 'Résumé financier - ' . now()->format('d/m/Y'), $content, $financialData);
    }

    public function generateAnalysis(array $financialData): AiReport
    {
        $prompt = $this->buildPrompt('analysis', $financialData);
        $content = $this->callApi($prompt, $financialData, 'analysis');
        return $this->saveReport('analysis', 'Analyse approfondie - ' . now()->format('d/m/Y'), $content, $financialData);
    }

    public function detectAnomalies(array $financialData): AiReport
    {
        $prompt = $this->buildPrompt('anomalies', $financialData);
        $content = $this->callApi($prompt, $financialData, 'anomalies');
        return $this->saveReport('anomalies', 'Détection d\'anomalies - ' . now()->format('d/m/Y'), $content, $financialData);
    }

    private function buildPrompt(string $type, array $data): string
    {
        $dataStr = json_encode($data, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE);
        return match($type) {
            'summary' => "Voici les données financières de notre association:\n\n{$dataStr}\n\nGénère un résumé financier concis et clair en français. Inclus: MRR, ARR, revenus du mois, dépenses, profit net, marge. Utilise des titres Markdown.",
            'analysis' => "Voici les données financières de notre association:\n\n{$dataStr}\n\nFais une analyse financière approfondie en français. Identifie les tendances, points forts, points faibles, et donne des recommandations concrètes. Utilise des titres Markdown.",
            'anomalies' => "Voici les données financières de notre association:\n\n{$dataStr}\n\nDétecte les anomalies financières, les dépenses inhabituelles, les baisses de revenus suspectes, et les risques. Explique chaque anomalie. Utilise des titres Markdown.",
            default => "Analyse ces données financières: {$dataStr}",
        };
    }

    private function callApi(string $prompt, array $data, string $type): string
    {
        try {
            $response = Http::timeout(60)
                ->withHeaders([
                    'Authorization' => 'Bearer ' . env('OLLAMA_CLOUD_API_KEY'),
                    'Content-Type' => 'application/json',
                ])
                ->post($this->apiUrl . '/chat/completions', [
                    'model' => $this->model,
                    'messages' => [
                        ['role' => 'system', 'content' => $this->systemPrompt],
                        ['role' => 'user', 'content' => $prompt],
                    ],
                    'temperature' => 0.7,
                ]);

            if ($response->successful()) {
                return $response->json('choices.0.message.content', $this->getDemoReport($type, $data));
            }
            return $this->getDemoReport($type, $data);
        } catch (\Exception $e) {
            return $this->getDemoReport($type, $data);
        }
    }

    private function saveReport(string $type, string $title, string $content, array $data): AiReport
    {
        return AiReport::create([
            'type' => $type,
            'title' => $title,
            'content' => $content,
            'data' => $data,
        ]);
    }

    private function getDemoReport(string $type, array $data): string
    {
        $mrr = number_format($data['mrr'] ?? 0, 2, ',', ' ');
        $arr = number_format($data['arr'] ?? 0, 2, ',', ' ');
        $revenue = number_format($data['revenue_month'] ?? 0, 2, ',', ' ');
        $expenses = number_format($data['expenses_month'] ?? 0, 2, ',', ' ');
        $profit = number_format($data['net_profit_month'] ?? 0, 2, ',', ' ');
        $margin = $data['margin_month'] ?? 0;
        $growth = $data['growth_rate'] ?? 0;
        $bestClient = $data['best_client'] ?? 'N/A';
        $bestService = $data['best_service'] ?? 'N/A';

        return match($type) {
            'summary' => "# Résumé Financier — " . now()->format('F Y') . "\n\n> *Rapport généré en mode démonstration (API Ollama indisponible)*\n\n## Indicateurs Clés\n\n| Indicateur | Valeur |\n|---|---|\n| MRR (Revenu Mensuel Récurrent) | {$mrr} € |\n| ARR (Revenu Annuel Récurrent) | {$arr} € |\n| Revenus du mois | {$revenue} € |\n| Dépenses du mois | {$expenses} € |\n| Profit net | {$profit} € |\n| Marge | {$margin}% |\n| Croissance | {$growth}% |\n\n## Performance\n\n- **Meilleur client** : {$bestClient}\n- **Meilleur service** : {$bestService}\n\n## Conclusion\n\nL'association maintient une activité financière stable avec un MRR de {$mrr} €. La marge de {$margin}% reflète une gestion saine des coûts.",
            'analysis' => "# Analyse Financière Approfondie — " . now()->format('F Y') . "\n\n> *Rapport généré en mode démonstration (API Ollama indisponible)*\n\n## Tendances Observées\n\n### Revenus\nLes revenus du mois s'établissent à **{$revenue} €**, avec un taux de croissance de **{$growth}%** par rapport au mois précédent.\n\n### Dépenses\nLes dépenses totales du mois sont de **{$expenses} €**, représentant {$margin}% des revenus.\n\n### Rentabilité\nLe profit net est de **{$profit} €**, avec une marge de **{$margin}%**.\n\n## Points Forts\n\n- MRR solide de {$mrr} €\n- Client le plus rentable : {$bestClient}\n- Service le plus performant : {$bestService}\n\n## Recommandations\n\n1. **Développer les abonnements annuels** pour améliorer la prévisibilité des revenus\n2. **Optimiser les dépenses d'infrastructure** pour augmenter la marge\n3. **Fidéliser {$bestClient}** qui représente un client stratégique\n4. **Réduire le churn** en améliorant l'onboarding des nouveaux clients",
            'anomalies' => "# Détection d'Anomalies Financières — " . now()->format('F Y') . "\n\n> *Rapport généré en mode démonstration (API Ollama indisponible)*\n\n## Analyse des Anomalies\n\n### Revenus\n" . ($growth < -10 ? "⚠️ **ALERTE** : Baisse de revenus significative de {$growth}% détectée. Investigation recommandée." : "✅ Les revenus sont dans la normale ({$growth}% de variation).") . "\n\n### Dépenses\n" . ((float)($data['expenses_month'] ?? 0) > (float)($data['revenue_month'] ?? 1) * 0.8 ? "⚠️ **ALERTE** : Les dépenses ({$expenses} €) représentent plus de 80% des revenus ({$revenue} €). Risque de déficit." : "✅ Les dépenses sont sous contrôle ({$expenses} € vs {$revenue} € de revenus).") . "\n\n### Marge\n" . ((float)$margin < 20 ? "⚠️ **ATTENTION** : La marge de {$margin}% est faible. Un minimum de 20% est recommandé." : "✅ La marge de {$margin}% est satisfaisante.") . "\n\n## Résumé\n\nAnalyse basée sur les données du mois de " . now()->format('F Y') . ". Aucune anomalie critique détectée en dehors des alertes ci-dessus.",
            default => "Rapport de démonstration généré le " . now()->format('d/m/Y à H:i'),
        };
    }
}
