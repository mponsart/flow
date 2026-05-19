@extends('layouts.app')

@section('content')
    <x-notification />
    <div class="grid grid-cols-1 md:grid-cols-3 gap-6 mb-8">
        <x-kpi label="Revenus mensuels" :value="$kpis['revenus_mensuels'] . ' €'" :trend="$kpis['croissance_revenus']" />
        <x-kpi label="MRR" :value="$kpis['MRR'] . ' €'" />
        <x-kpi label="ARR" :value="$kpis['ARR'] . ' €'" />
        <x-kpi label="Bénéfice net" :value="$kpis['bénéfices'] . ' €'" />
        <x-kpi label="Cashflow" :value="$kpis['cashflow'] . ' €'" />
        <x-kpi label="Clients actifs" :value="$kpis['clients_actifs']" />
        <x-kpi label="Service le + rentable" :value="$kpis['service_plus_rentable']" />
        <x-kpi label="Client le + rentable" :value="$kpis['client_plus_rentable']" />
        <x-kpi label="Marge moyenne" :value="$kpis['marge_moyenne'] . ' %'" />
    </div>
    <div class="grid grid-cols-1 md:grid-cols-2 gap-6 mb-8">
        <x-card>
            <div class="mb-2 font-semibold text-lg">Évolution des revenus</div>
            <canvas id="revenusChart" height="120"></canvas>
        </x-card>
        <x-card>
            <div class="mb-2 font-semibold text-lg">Évolution des dépenses</div>
            <canvas id="depensesChart" height="120"></canvas>
        </x-card>
    </div>
    <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
        <x-card>
            <div class="mb-2 font-semibold text-lg">Répartition services</div>
            <canvas id="servicesChart" height="120"></canvas>
        </x-card>
        <x-card>
            <div class="mb-2 font-semibold text-lg">Cashflow</div>
            <canvas id="cashflowChart" height="120"></canvas>
        </x-card>
    </div>
    <script>
        // Exemple de données fictives pour les graphiques (à remplacer par des données dynamiques)
        const revenusData = [1200, 1400, 1600, 1800, 2000, 2200];
        const depensesData = [800, 900, 950, 1000, 1100, 1200];
        const servicesData = [40, 30, 20, 10];
        const cashflowData = [400, 500, 650, 800, 900, 1000];
        new Chart(document.getElementById('revenusChart'), {
            type: 'line',
            data: { labels: ['Jan', 'Fév', 'Mar', 'Avr', 'Mai', 'Juin'], datasets: [{ label: 'Revenus', data: revenusData, borderColor: '#6366f1', backgroundColor: 'rgba(99,102,241,0.2)', tension: 0.4 }] },
            options: { plugins: { legend: { display: false } }, scales: { x: { grid: { display: false } }, y: { grid: { color: '#23232a' } } } }
        });
        new Chart(document.getElementById('depensesChart'), {
            type: 'line',
            data: { labels: ['Jan', 'Fév', 'Mar', 'Avr', 'Mai', 'Juin'], datasets: [{ label: 'Dépenses', data: depensesData, borderColor: '#14b8a6', backgroundColor: 'rgba(20,184,166,0.2)', tension: 0.4 }] },
            options: { plugins: { legend: { display: false } }, scales: { x: { grid: { display: false } }, y: { grid: { color: '#23232a' } } } }
        });
        new Chart(document.getElementById('servicesChart'), {
            type: 'doughnut',
            data: { labels: ['VPS Starter', 'VPS Pro', 'Email Business', 'Cloud Backup'], datasets: [{ data: servicesData, backgroundColor: ['#6366f1', '#14b8a6', '#f59e42', '#e11d48'] }] },
            options: { plugins: { legend: { display: true, position: 'bottom' } } }
        });
        new Chart(document.getElementById('cashflowChart'), {
            type: 'bar',
            data: { labels: ['Jan', 'Fév', 'Mar', 'Avr', 'Mai', 'Juin'], datasets: [{ label: 'Cashflow', data: cashflowData, backgroundColor: '#6366f1' }] },
            options: { plugins: { legend: { display: false } }, scales: { x: { grid: { display: false } }, y: { grid: { color: '#23232a' } } } }
        });
    </script>
@endsection
