<!DOCTYPE html>
<html lang="fr">
<head>
<meta charset="UTF-8">
<title>Rapport Financier Flow — {{ $data['period'] }}</title>
<style>
body { font-family: Arial, sans-serif; color: #1a1a1a; margin: 40px; font-size: 13px; }
h1 { color: #4f46e5; font-size: 22px; }
h2 { color: #374151; font-size: 16px; border-bottom: 2px solid #e5e7eb; padding-bottom: 6px; margin-top: 30px; }
table { width: 100%; border-collapse: collapse; margin: 12px 0; }
th { background: #f3f4f6; text-align: left; padding: 8px 10px; font-size: 11px; text-transform: uppercase; color: #6b7280; }
td { padding: 7px 10px; border-bottom: 1px solid #e5e7eb; }
.kpi-grid { display: grid; grid-template-columns: repeat(4, 1fr); gap: 12px; margin: 16px 0; }
.kpi { background: #f9fafb; border: 1px solid #e5e7eb; border-radius: 8px; padding: 12px; }
.kpi-label { font-size: 10px; color: #9ca3af; text-transform: uppercase; }
.kpi-value { font-size: 20px; font-weight: bold; color: #4f46e5; }
</style>
</head>
<body>
<h1>Rapport Financier — Flow</h1>
<p style="color: #6b7280">Généré le {{ $data['generated_at'] }} | Période : {{ $data['period'] }}</p>

<h2>Indicateurs Clés</h2>
<table>
    <tr><th>Indicateur</th><th>Valeur</th></tr>
    <tr><td>MRR (Revenu Mensuel Récurrent)</td><td>{{ number_format($data['kpis']['mrr'], 2, ',', ' ') }} €</td></tr>
    <tr><td>ARR (Revenu Annuel Récurrent)</td><td>{{ number_format($data['kpis']['arr'], 2, ',', ' ') }} €</td></tr>
    <tr><td>Revenus du mois</td><td>{{ number_format($data['kpis']['revenue_month'], 2, ',', ' ') }} €</td></tr>
    <tr><td>Dépenses du mois</td><td>{{ number_format($data['kpis']['expenses_month'], 2, ',', ' ') }} €</td></tr>
    <tr><td>Profit net</td><td>{{ number_format($data['kpis']['net_profit_month'], 2, ',', ' ') }} €</td></tr>
    <tr><td>Marge</td><td>{{ $data['kpis']['margin_month'] }} %</td></tr>
    <tr><td>Croissance</td><td>{{ $data['kpis']['growth_rate'] }} %</td></tr>
    <tr><td>Clients actifs</td><td>{{ $data['clients_count'] }}</td></tr>
    <tr><td>Services actifs</td><td>{{ $data['services_count'] }}</td></tr>
    <tr><td>Abonnements actifs</td><td>{{ $data['subscriptions_count'] }}</td></tr>
</table>

<h2>Top Clients</h2>
<table>
    <tr><th>Client</th><th>Revenus totaux</th><th>Dépenses</th><th>Profit net</th></tr>
    @foreach($data['top_clients'] as $client)
    <tr><td>{{ $client->name }}</td><td>{{ number_format($client->total_revenue, 2, ',', ' ') }} €</td><td>{{ number_format($client->total_expenses, 2, ',', ' ') }} €</td><td>{{ number_format($client->net_profit, 2, ',', ' ') }} €</td></tr>
    @endforeach
</table>

<h2>Top Services</h2>
<table>
    <tr><th>Service</th><th>Revenus</th><th>Abonnés</th></tr>
    @foreach($data['top_services'] as $service)
    <tr><td>{{ $service->name }}</td><td>{{ number_format($service->total_revenue, 2, ',', ' ') }} €</td><td>{{ $service->subscriber_count }}</td></tr>
    @endforeach
</table>

<h2>Derniers Revenus</h2>
<table>
    <tr><th>Date</th><th>Client</th><th>Description</th><th>Montant</th></tr>
    @foreach($data['recent_revenues'] as $rev)
    <tr><td>{{ $rev->date->format('d/m/Y') }}</td><td>{{ $rev->client?->name }}</td><td>{{ $rev->description }}</td><td>{{ number_format($rev->amount, 2, ',', ' ') }} €</td></tr>
    @endforeach
</table>
<p style="color: #9ca3af; margin-top: 40px; font-size: 11px; text-align: center">Rapport généré par Flow — Application de gestion financière pour associations</p>
</body>
</html>
