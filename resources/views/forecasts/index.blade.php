@extends('layouts.app')
@section('title', 'Prévisions')
@section('page-title', 'Prévisions financières')
@section('content')
<div class="flex items-center justify-between mb-6">
    <p class="text-zinc-400 text-sm">Prévisions basées sur la tendance des 6 derniers mois</p>
    <form method="POST" action="{{ route('forecasts.generate') }}">
        @csrf
        <button type="submit" class="btn-primary">
            <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15"/></svg>
            Générer 6 mois de prévisions
        </button>
    </form>
</div>

@if(count($comparisons) > 0)
<!-- Chart -->
<div class="card mb-6">
    <h3 class="text-sm font-semibold text-zinc-300 mb-4">Prévisions vs Réel</h3>
    <canvas id="forecastChart" height="120"></canvas>
</div>

<!-- Table -->
<div class="card overflow-hidden p-0">
    <table class="w-full text-sm">
        <thead class="bg-zinc-800/50">
            <tr>
                <th class="text-left px-4 py-3 text-xs font-medium text-zinc-500 uppercase">Mois</th>
                <th class="text-right px-4 py-3 text-xs font-medium text-zinc-500 uppercase">Rev. prévu</th>
                <th class="text-right px-4 py-3 text-xs font-medium text-zinc-500 uppercase">Rev. réel</th>
                <th class="text-right px-4 py-3 text-xs font-medium text-zinc-500 uppercase">Dép. prévues</th>
                <th class="text-right px-4 py-3 text-xs font-medium text-zinc-500 uppercase">Dép. réelles</th>
                <th class="text-right px-4 py-3 text-xs font-medium text-zinc-500 uppercase">Profit prévu</th>
                <th class="text-right px-4 py-3 text-xs font-medium text-zinc-500 uppercase">Profit réel</th>
            </tr>
        </thead>
        <tbody>
            @foreach($comparisons as $row)
            <tr class="table-row border-zinc-800">
                <td class="px-4 py-3 text-white font-medium">{{ \Carbon\Carbon::parse($row['month'])->format('M Y') }}</td>
                <td class="px-4 py-3 text-right text-zinc-400">{{ number_format($row['projected_revenue'], 2, ',', ' ') }} €</td>
                <td class="px-4 py-3 text-right text-green-400">{{ number_format($row['actual_revenue'], 2, ',', ' ') }} €</td>
                <td class="px-4 py-3 text-right text-zinc-400">{{ number_format($row['projected_expenses'], 2, ',', ' ') }} €</td>
                <td class="px-4 py-3 text-right text-red-400">{{ number_format($row['actual_expenses'], 2, ',', ' ') }} €</td>
                <td class="px-4 py-3 text-right {{ $row['projected_profit'] >= 0 ? 'text-indigo-400' : 'text-red-400' }}">{{ number_format($row['projected_profit'], 2, ',', ' ') }} €</td>
                <td class="px-4 py-3 text-right {{ $row['actual_profit'] >= 0 ? 'text-green-400' : 'text-red-400' }}">{{ number_format($row['actual_profit'], 2, ',', ' ') }} €</td>
            </tr>
            @endforeach
        </tbody>
    </table>
</div>
@else
<div class="card text-center py-16">
    <svg class="w-16 h-16 mx-auto mb-4 text-zinc-700" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z"/></svg>
    <p class="text-zinc-400 mb-2">Aucune prévision générée.</p>
    <p class="text-zinc-500 text-sm">Cliquez sur "Générer 6 mois de prévisions" pour commencer.</p>
</div>
@endif
@endsection

@push('scripts')
@if(count($comparisons) > 0)
<script>
    const labels = @json(collect($comparisons)->map(fn($r) => \Carbon\Carbon::parse($r['month'])->format('M Y')));
    const projRev = @json(collect($comparisons)->pluck('projected_revenue'));
    const actualRev = @json(collect($comparisons)->pluck('actual_revenue'));
    const projProfit = @json(collect($comparisons)->pluck('projected_profit'));
    const actualProfit = @json(collect($comparisons)->pluck('actual_profit'));
    new Chart(document.getElementById('forecastChart'), {
        type: 'line',
        data: {
            labels,
            datasets: [
                { label: 'Rev. prévu', data: projRev, borderColor: '#6366f1', borderDash: [5,5], tension: 0.4, pointRadius: 4 },
                { label: 'Rev. réel', data: actualRev, borderColor: '#34d399', tension: 0.4, pointRadius: 4 },
                { label: 'Profit prévu', data: projProfit, borderColor: '#a78bfa', borderDash: [3,3], tension: 0.4, pointRadius: 3 },
                { label: 'Profit réel', data: actualProfit, borderColor: '#14b8a6', tension: 0.4, pointRadius: 3 },
            ]
        },
        options: {
            scales: {
                x: { grid: { color: 'rgba(255,255,255,0.05)' }, ticks: { color: '#71717a', font: { size: 11 } } },
                y: { grid: { color: 'rgba(255,255,255,0.05)' }, ticks: { color: '#71717a', font: { size: 11 } } }
            },
            plugins: { legend: { display: true, position: 'bottom', labels: { color: '#a1a1aa', font: { size: 11 } } } }
        }
    });
</script>
@endif
@endpush
