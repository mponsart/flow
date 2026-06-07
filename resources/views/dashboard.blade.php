@extends('layouts.app')
@section('title', 'Dashboard')
@section('page-title', 'Dashboard')

@section('content')
<!-- KPI Grid -->
<div class="grid grid-cols-2 md:grid-cols-3 xl:grid-cols-6 gap-4 mb-6">
    @php
        $kpiCards = [
            ['label' => 'MRR', 'value' => number_format($kpis['mrr'], 2, ',', ' ') . ' €', 'color' => 'indigo'],
            ['label' => 'ARR', 'value' => number_format($kpis['arr'], 2, ',', ' ') . ' €', 'color' => 'teal'],
            ['label' => 'Revenus du mois', 'value' => number_format($kpis['revenue_month'], 2, ',', ' ') . ' €', 'color' => 'green'],
            ['label' => 'Dépenses du mois', 'value' => number_format($kpis['expenses_month'], 2, ',', ' ') . ' €', 'color' => 'red'],
            ['label' => 'Profit net', 'value' => number_format($kpis['net_profit_month'], 2, ',', ' ') . ' €', 'color' => $kpis['net_profit_month'] >= 0 ? 'green' : 'red'],
            ['label' => 'Marge', 'value' => $kpis['margin_month'] . ' %', 'color' => $kpis['margin_month'] >= 20 ? 'green' : 'yellow'],
        ];
        $colorMap = [
            'indigo' => ['bg' => 'bg-indigo-900/50', 'text' => 'text-indigo-400'],
            'teal' => ['bg' => 'bg-teal-900/50', 'text' => 'text-teal-400'],
            'green' => ['bg' => 'bg-green-900/50', 'text' => 'text-green-400'],
            'red' => ['bg' => 'bg-red-900/50', 'text' => 'text-red-400'],
            'yellow' => ['bg' => 'bg-yellow-900/50', 'text' => 'text-yellow-400'],
        ];
    @endphp
    @foreach($kpiCards as $card)
    <div class="kpi-card">
        <div class="flex items-center justify-between mb-3">
            <p class="text-xs font-medium text-zinc-500 uppercase tracking-wide">{{ $card['label'] }}</p>
            <div class="w-7 h-7 {{ $colorMap[$card['color']]['bg'] }} rounded-lg flex items-center justify-center">
                <svg class="w-4 h-4 {{ $colorMap[$card['color']]['text'] }}" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 7h8m0 0v8m0-8l-8 8-4-4-6 6"/></svg>
            </div>
        </div>
        <p class="text-xl font-bold text-white">{{ $card['value'] }}</p>
        @if($loop->first)
            <p class="text-xs mt-1 {{ $kpis['growth_rate'] >= 0 ? 'text-green-400' : 'text-red-400' }}">
                {{ $kpis['growth_rate'] >= 0 ? '▲' : '▼' }} {{ abs($kpis['growth_rate']) }}% vs mois préc.
            </p>
        @endif
    </div>
    @endforeach
</div>

<!-- Client & Service les plus rentables -->
<div class="grid grid-cols-1 md:grid-cols-2 gap-4 mb-6">
    <div class="card flex items-center gap-4">
        <div class="w-12 h-12 bg-indigo-900/50 rounded-xl flex items-center justify-center flex-shrink-0">
            <svg class="w-6 h-6 text-indigo-400" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"/></svg>
        </div>
        <div>
            <p class="text-xs text-zinc-500 uppercase tracking-wide font-medium">Client le plus rentable</p>
            <p class="text-lg font-semibold text-white mt-0.5">{{ $kpis['best_client']?->name ?? 'N/A' }}</p>
            @if($kpis['best_client'])
                <p class="text-sm text-green-400">{{ number_format($kpis['best_client']->net_profit, 2, ',', ' ') }} € de profit</p>
            @endif
        </div>
    </div>
    <div class="card flex items-center gap-4">
        <div class="w-12 h-12 bg-teal-900/50 rounded-xl flex items-center justify-center flex-shrink-0">
            <svg class="w-6 h-6 text-teal-400" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20 7l-8-4-8 4m16 0l-8 4m8-4v10l-8 4m0-10L4 7m8 4v10M4 7v10l8 4"/></svg>
        </div>
        <div>
            <p class="text-xs text-zinc-500 uppercase tracking-wide font-medium">Service le plus rentable</p>
            <p class="text-lg font-semibold text-white mt-0.5">{{ $kpis['best_service']?->name ?? 'N/A' }}</p>
            @if($kpis['best_service'])
                <p class="text-sm text-teal-400">{{ number_format($kpis['best_service']->total_revenue, 2, ',', ' ') }} € de revenus</p>
            @endif
        </div>
    </div>
</div>

<!-- Charts -->
<div class="grid grid-cols-1 md:grid-cols-2 gap-6 mb-6">
    <div class="card">
        <h3 class="text-sm font-semibold text-zinc-300 mb-4">Revenus — 12 derniers mois</h3>
        <canvas id="revenueChart" height="160"></canvas>
    </div>
    <div class="card">
        <h3 class="text-sm font-semibold text-zinc-300 mb-4">Dépenses — 12 derniers mois</h3>
        <canvas id="expensesChart" height="160"></canvas>
    </div>
    <div class="card">
        <h3 class="text-sm font-semibold text-zinc-300 mb-4">Répartition par service</h3>
        <canvas id="serviceChart" height="160"></canvas>
    </div>
    <div class="card">
        <h3 class="text-sm font-semibold text-zinc-300 mb-4">Cashflow mensuel</h3>
        <canvas id="cashflowChart" height="160"></canvas>
    </div>
</div>

<!-- Recent Transactions -->
<div class="card">
    <div class="flex items-center justify-between mb-4">
        <h3 class="text-sm font-semibold text-zinc-300">Dernières transactions</h3>
        <a href="{{ route('revenues.index') }}" class="text-xs text-indigo-400 hover:text-indigo-300">Voir tout →</a>
    </div>
    @if(count($recentTransactions) > 0)
    <table class="w-full text-sm">
        <thead>
            <tr class="border-b border-zinc-800">
                <th class="text-left pb-3 text-xs font-medium text-zinc-500 uppercase">Type</th>
                <th class="text-left pb-3 text-xs font-medium text-zinc-500 uppercase">Description</th>
                <th class="text-left pb-3 text-xs font-medium text-zinc-500 uppercase">Client</th>
                <th class="text-right pb-3 text-xs font-medium text-zinc-500 uppercase">Montant</th>
                <th class="text-right pb-3 text-xs font-medium text-zinc-500 uppercase">Date</th>
            </tr>
        </thead>
        <tbody>
            @foreach($recentTransactions as $tx)
            <tr class="table-row">
                <td class="py-3">
                    @if($tx['type'] === 'revenue')
                        <span class="badge badge-green">Revenu</span>
                    @else
                        <span class="badge badge-red">Dépense</span>
                    @endif
                </td>
                <td class="py-3 text-zinc-300">{{ $tx['label'] }}</td>
                <td class="py-3 text-zinc-400">{{ $tx['client'] ?? '—' }}</td>
                <td class="py-3 text-right font-medium {{ $tx['amount'] >= 0 ? 'text-green-400' : 'text-red-400' }}">
                    {{ $tx['amount'] >= 0 ? '+' : '' }}{{ number_format($tx['amount'], 2, ',', ' ') }} €
                </td>
                <td class="py-3 text-right text-zinc-500">{{ \Carbon\Carbon::parse($tx['date'])->format('d/m/Y') }}</td>
            </tr>
            @endforeach
        </tbody>
    </table>
    @else
        <p class="text-zinc-500 text-sm text-center py-8">Aucune transaction enregistrée.</p>
    @endif
</div>
@endsection

@push('scripts')
<script>
    const chartDefaults = {
        scales: {
            x: { grid: { color: 'rgba(255,255,255,0.05)' }, ticks: { color: '#71717a', font: { size: 11 } } },
            y: { grid: { color: 'rgba(255,255,255,0.05)' }, ticks: { color: '#71717a', font: { size: 11 } } }
        },
        plugins: { legend: { display: false } }
    };
    const revenueData = @json(collect($revenueByMonth)->pluck('value'));
    const revenueLabels = @json(collect($revenueByMonth)->pluck('label'));
    const expensesData = @json(collect($expensesByMonth)->pluck('value'));
    const cashflowData = @json(collect($cashflowByMonth)->pluck('value'));
    const serviceLabels = @json(collect($serviceDistribution)->pluck('label'));
    const serviceValues = @json(collect($serviceDistribution)->pluck('value'));

    new Chart(document.getElementById('revenueChart'), {
        type: 'line',
        data: { labels: revenueLabels, datasets: [{ data: revenueData, borderColor: '#6366f1', backgroundColor: 'rgba(99,102,241,0.1)', tension: 0.4, fill: true, pointRadius: 3 }] },
        options: chartDefaults
    });
    new Chart(document.getElementById('expensesChart'), {
        type: 'line',
        data: { labels: revenueLabels, datasets: [{ data: expensesData, borderColor: '#f43f5e', backgroundColor: 'rgba(244,63,94,0.1)', tension: 0.4, fill: true, pointRadius: 3 }] },
        options: chartDefaults
    });
    new Chart(document.getElementById('serviceChart'), {
        type: 'doughnut',
        data: {
            labels: serviceLabels.length > 0 ? serviceLabels : ['Aucun service'],
            datasets: [{ data: serviceValues.length > 0 ? serviceValues : [1], backgroundColor: ['#6366f1','#14b8a6','#f59e0b','#ef4444','#8b5cf6','#06b6d4'], borderWidth: 0 }]
        },
        options: { plugins: { legend: { display: true, position: 'bottom', labels: { color: '#a1a1aa', font: { size: 11 } } } }, cutout: '65%' }
    });
    new Chart(document.getElementById('cashflowChart'), {
        type: 'bar',
        data: {
            labels: revenueLabels,
            datasets: [{ data: cashflowData, backgroundColor: cashflowData.map(v => v >= 0 ? 'rgba(52,211,153,0.7)' : 'rgba(248,113,113,0.7)'), borderRadius: 4 }]
        },
        options: chartDefaults
    });
</script>
@endpush
