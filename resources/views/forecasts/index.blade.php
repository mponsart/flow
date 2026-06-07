@extends('layouts.app')

@section('title', 'Prévisionnel ' . $year)
@section('page-title', 'Prévisionnel ' . $year)

@push('scripts')
<script>
document.addEventListener('DOMContentLoaded', function () {
    const ctx = document.getElementById('forecastChart').getContext('2d');
    const currentMonthIndex = {{ $currentMonth - 1 }};
    const chartData = @json($chartData);

    // Plugin to draw the "future zone" background
    const futureZonePlugin = {
        id: 'futureZone',
        beforeDraw(chart) {
            const { ctx, chartArea, scales } = chart;
            if (!chartArea) return;
            const xScale = scales.x;
            // Start from the border between current and next month
            const xStart = xScale.getPixelForValue(currentMonthIndex + 0.5);
            const xEnd = chartArea.right;
            if (xStart >= xEnd) return;
            ctx.save();
            ctx.fillStyle = 'rgba(255,255,255,0.015)';
            ctx.fillRect(xStart, chartArea.top, xEnd - xStart, chartArea.bottom - chartArea.top);
            // Dashed vertical separator
            ctx.strokeStyle = 'rgba(99,102,241,0.25)';
            ctx.lineWidth = 1;
            ctx.setLineDash([4, 4]);
            ctx.beginPath();
            ctx.moveTo(xStart, chartArea.top);
            ctx.lineTo(xStart, chartArea.bottom);
            ctx.stroke();
            ctx.setLineDash([]);
            ctx.restore();
        }
    };

    new Chart(ctx, {
        type: 'line',
        data: chartData,
        plugins: [futureZonePlugin],
        options: {
            responsive: true,
            maintainAspectRatio: false,
            interaction: { mode: 'index', intersect: false },
            plugins: {
                legend: {
                    display: true,
                    position: 'top',
                    align: 'end',
                    labels: {
                        color: '#888',
                        font: { size: 11, family: 'Inter, sans-serif' },
                        boxWidth: 24,
                        boxHeight: 2,
                        padding: 16,
                        usePointStyle: false,
                    }
                },
                tooltip: {
                    backgroundColor: '#1a1a1a',
                    borderColor: '#2a2a2a',
                    borderWidth: 1,
                    titleColor: '#ffffff',
                    bodyColor: '#888888',
                    padding: 12,
                    callbacks: {
                        label: function(ctx) {
                            if (ctx.raw === null || ctx.raw === undefined) return null;
                            return ' ' + ctx.dataset.label + ' : ' + new Intl.NumberFormat('fr-FR', { style: 'currency', currency: 'EUR', maximumFractionDigits: 0 }).format(ctx.raw);
                        }
                    }
                }
            },
            scales: {
                x: {
                    grid: { color: 'rgba(255,255,255,0.03)' },
                    ticks: { color: '#555', font: { size: 11 } },
                    border: { color: '#1e1e1e' }
                },
                y: {
                    grid: { color: 'rgba(255,255,255,0.04)' },
                    ticks: {
                        color: '#555',
                        font: { size: 11 },
                        callback: v => new Intl.NumberFormat('fr-FR', { notation: 'compact', maximumFractionDigits: 0 }).format(v) + ' €'
                    },
                    border: { color: '#1e1e1e' }
                }
            }
        }
    });
});
</script>
@endpush

@section('content')
<style>
    .forecast-future { opacity: 0.72; }
    .forecast-future .amt { font-style: italic; }
    .forecast-current { background: rgba(99,102,241,0.06) !important; border-left: 2px solid var(--accent); }
    .forecast-total { background: #0f0f0f !important; }
    .progress-bar-wrap { height: 4px; background: rgba(255,255,255,0.06); border-radius: 2px; margin-top: 10px; overflow: hidden; }
    .progress-bar-fill { height: 100%; border-radius: 2px; transition: width 0.6s ease; }
    .proj-badge { display: inline-flex; align-items: center; gap: 5px; padding: 3px 9px; border-radius: 20px; font-size: 10px; font-weight: 600; letter-spacing: 0.06em; text-transform: uppercase; background: rgba(245,158,11,0.1); color: #f59e0b; border: 1px solid rgba(245,158,11,0.2); }
    .kpi-row { display: grid; grid-template-columns: repeat(3, 1fr); gap: 16px; margin-bottom: 24px; }
    @media (max-width: 900px) { .kpi-row { grid-template-columns: 1fr; } }
</style>

<!-- Hero Header -->
<div class="page-header">
    <div class="page-header-left">
        <div>
            <div class="page-title">Prévisionnel {{ $year }}</div>
            <div class="page-subtitle">Projection basée sur la moyenne des 3 derniers mois réels</div>
        </div>
    </div>
</div>

<!-- Section 1 — KPI Cards -->
<div class="kpi-row">

    {{-- Card Revenus YTD --}}
    <div class="kpi-card">
        <div style="display:flex;align-items:flex-start;justify-content:space-between;margin-bottom:12px;">
            <div class="kpi-label" style="margin-bottom:0;">Revenus YTD</div>
            <div class="kpi-icon" style="background:var(--accent-bg);">
                <svg fill="none" viewBox="0 0 24 24" stroke="#6366f1" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M13 7h8m0 0v8m0-8l-8 8-4-4-6 6"/></svg>
            </div>
        </div>
        <div class="kpi-value" style="color:var(--accent);">
            {{ number_format($ytdRevenue, 0, ',', ' ') }} €
        </div>
        <div class="kpi-sub">
            Jan → {{ $monthNames[$currentMonth] }} réels
        </div>
        <div class="progress-bar-wrap">
            <div class="progress-bar-fill" style="width:{{ round($currentMonth / 12 * 100) }}%;background:var(--accent);"></div>
        </div>
        <div style="display:flex;justify-content:space-between;margin-top:5px;">
            <span style="font-size:10px;color:var(--text-3);">{{ $currentMonth }} mois sur 12</span>
            <span style="font-size:10px;color:var(--text-3);">{{ round($currentMonth / 12 * 100) }}%</span>
        </div>
    </div>

    {{-- Card Projection fin d'année --}}
    <div class="kpi-card" style="border-color:rgba(99,102,241,0.2);">
        <div style="display:flex;align-items:flex-start;justify-content:space-between;margin-bottom:12px;">
            <div class="kpi-label" style="margin-bottom:0;">Projection fin d'année</div>
            <span class="proj-badge">Projection</span>
        </div>
        <div class="kpi-value" style="color:var(--accent);font-size:28px;">
            {{ number_format($projectedAnnualRevenue, 0, ',', ' ') }} €
        </div>
        <div style="margin-top:10px;display:flex;flex-direction:column;gap:4px;">
            <div style="display:flex;justify-content:space-between;font-size:12px;">
                <span style="color:var(--text-3);">Réels</span>
                <span style="color:var(--text-2);">{{ number_format($ytdRevenue, 0, ',', ' ') }} €</span>
            </div>
            @php
                $forecastSum = array_sum(array_column($forecast, 'revenue'));
            @endphp
            <div style="display:flex;justify-content:space-between;font-size:12px;">
                <span style="color:var(--text-3);">Projetés</span>
                <span style="color:var(--accent);font-style:italic;">{{ number_format($forecastSum, 0, ',', ' ') }} €</span>
            </div>
        </div>
    </div>

    {{-- Card Profit projeté --}}
    <div class="kpi-card">
        <div style="display:flex;align-items:flex-start;justify-content:space-between;margin-bottom:12px;">
            <div class="kpi-label" style="margin-bottom:0;">Profit projeté</div>
            <div class="kpi-icon" style="background:{{ $projectedAnnualProfit >= 0 ? 'rgba(16,185,129,0.1)' : 'rgba(239,68,68,0.1)' }};">
                @if($projectedAnnualProfit >= 0)
                    <svg fill="none" viewBox="0 0 24 24" stroke="#10b981" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M5 10l7-7m0 0l7 7m-7-7v18"/></svg>
                @else
                    <svg fill="none" viewBox="0 0 24 24" stroke="#ef4444" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M19 14l-7 7m0 0l-7-7m7 7V3"/></svg>
                @endif
            </div>
        </div>
        <div class="kpi-value" style="color:{{ $projectedAnnualProfit >= 0 ? 'var(--green)' : 'var(--red)' }};">
            {{ $projectedAnnualProfit >= 0 ? '+' : '' }}{{ number_format($projectedAnnualProfit, 0, ',', ' ') }} €
        </div>
        @php
            $projMargin = $projectedAnnualRevenue > 0 ? round($projectedAnnualProfit / $projectedAnnualRevenue * 100, 1) : 0;
        @endphp
        <div class="kpi-sub" style="color:{{ $projMargin >= 0 ? 'var(--green)' : 'var(--red)' }};font-weight:600;">
            {{ $projMargin }}% de marge
        </div>
        <div class="kpi-sub">Sur l'année complète</div>
    </div>

</div>

<!-- Section 2 — Graphique -->
<div class="card-flush" style="margin-bottom:24px;">
    <div class="card-header">
        <div>
            <div class="card-title">Revenus & Dépenses — {{ $year }}</div>
            <div class="card-subtitle">Historique + Projection</div>
        </div>
        <div style="display:flex;align-items:center;gap:8px;">
            <span style="display:flex;align-items:center;gap:5px;font-size:11px;color:var(--text-3);">
                <span style="display:inline-block;width:20px;height:2px;background:#6366f1;border-radius:1px;"></span> Réels
            </span>
            <span style="display:flex;align-items:center;gap:5px;font-size:11px;color:var(--text-3);">
                <span style="display:inline-block;width:20px;height:2px;background:#6366f1;border-radius:1px;border-top:2px dashed #6366f1;"></span> Projetés
            </span>
        </div>
    </div>
    <div style="padding:20px;">
        <div style="height:280px;position:relative;">
            <canvas id="forecastChart"></canvas>
        </div>
    </div>
</div>

<!-- Section 3 — Tableau annuel -->
<div class="card-flush">
    <div class="card-header">
        <div>
            <div class="card-title">Détail mensuel {{ $year }}</div>
            <div class="card-subtitle">Historique Jan → {{ $monthNames[$currentMonth] }}, projection {{ $currentMonth < 12 ? $monthNames[$currentMonth + 1] . ' → Déc' : '' }}</div>
        </div>
    </div>
    <table class="data-table">
        <thead>
            <tr>
                <th>Mois</th>
                <th class="text-right">Revenus</th>
                <th class="text-right">Dépenses</th>
                <th class="text-right">Profit</th>
                <th class="text-right">Marge</th>
            </tr>
        </thead>
        <tbody>
            @php
                $totalRevenue = 0;
                $totalExpenses = 0;
                $totalProfit = 0;
            @endphp
            @for ($m = 1; $m <= 12; $m++)
                @php
                    $isCurrent = $m === $currentMonth;
                    $isFuture = $m > $currentMonth;
                    $isPast = $m < $currentMonth;

                    if ($m <= $currentMonth) {
                        $row = $history[$m];
                        $isProjected = false;
                    } elseif (isset($forecast[$m])) {
                        $row = $forecast[$m];
                        $isProjected = true;
                    } else {
                        $row = ['revenue' => 0, 'expenses' => 0, 'profit' => 0, 'margin' => 0];
                        $isProjected = true;
                    }

                    $totalRevenue += $row['revenue'];
                    $totalExpenses += $row['expenses'];
                    $totalProfit += $row['profit'];
                @endphp
                <tr class="{{ $isCurrent ? 'forecast-current' : ($isFuture ? 'forecast-future' : '') }}">
                    <td>
                        <div style="display:flex;align-items:center;gap:8px;">
                            <span style="font-weight:{{ $isCurrent ? '600' : '400' }};color:{{ $isCurrent ? 'var(--text)' : 'var(--text-2)' }};">
                                {{ $monthNames[$m] }}
                            </span>
                            @if($isCurrent)
                                <span class="badge badge-indigo" style="font-size:10px;padding:2px 7px;">En cours</span>
                            @elseif($isFuture)
                                <span class="badge badge-yellow" style="font-size:10px;padding:2px 7px;background:transparent;">Projection</span>
                            @endif
                        </div>
                    </td>
                    <td class="text-right">
                        @if($row['revenue'] > 0)
                            <span class="amt" style="color:{{ $isProjected ? 'var(--accent)' : 'var(--green)' }};">
                                {{ number_format($row['revenue'], 0, ',', ' ') }} €
                            </span>
                        @else
                            <span style="color:var(--text-3);">—</span>
                        @endif
                    </td>
                    <td class="text-right">
                        @if($row['expenses'] > 0)
                            <span class="amt" style="color:var(--red);">
                                {{ number_format($row['expenses'], 0, ',', ' ') }} €
                            </span>
                        @else
                            <span style="color:var(--text-3);">—</span>
                        @endif
                    </td>
                    <td class="text-right">
                        @if($row['profit'] >= 0)
                            <span class="trend-up amt">▲ +{{ number_format($row['profit'], 0, ',', ' ') }} €</span>
                        @else
                            <span class="trend-down amt">▼ {{ number_format($row['profit'], 0, ',', ' ') }} €</span>
                        @endif
                    </td>
                    <td class="text-right">
                        <span style="color:{{ $row['margin'] >= 30 ? 'var(--green)' : ($row['margin'] >= 10 ? 'var(--yellow)' : 'var(--red)') }};font-weight:500;">
                            {{ $row['margin'] }}%
                        </span>
                    </td>
                </tr>
            @endfor
        </tbody>
        <tfoot>
            <tr class="forecast-total">
                <td style="font-weight:700;color:var(--text);">TOTAL {{ $year }}</td>
                <td class="text-right" style="font-weight:700;color:var(--accent);">
                    {{ number_format($totalRevenue, 0, ',', ' ') }} €
                </td>
                <td class="text-right" style="font-weight:700;color:var(--red);">
                    {{ number_format($totalExpenses, 0, ',', ' ') }} €
                </td>
                <td class="text-right">
                    @if($totalProfit >= 0)
                        <span style="font-weight:700;color:var(--green);">▲ +{{ number_format($totalProfit, 0, ',', ' ') }} €</span>
                    @else
                        <span style="font-weight:700;color:var(--red);">▼ {{ number_format($totalProfit, 0, ',', ' ') }} €</span>
                    @endif
                </td>
                <td class="text-right" style="font-weight:700;">
                    @php
                        $totalMargin = $totalRevenue > 0 ? round($totalProfit / $totalRevenue * 100, 1) : 0;
                    @endphp
                    <span style="color:{{ $totalMargin >= 30 ? 'var(--green)' : ($totalMargin >= 10 ? 'var(--yellow)' : 'var(--red)') }};">
                        {{ $totalMargin }}%
                    </span>
                </td>
            </tr>
        </tfoot>
    </table>
    <div style="padding:12px 16px;border-top:1px solid var(--border);font-size:11px;color:var(--text-3);">
        * Les projections sont basées sur la moyenne des 3 derniers mois avec données.
    </div>
</div>
@endsection
