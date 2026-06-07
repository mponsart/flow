@extends('layouts.app')
@section('title', 'Rapports')
@section('page-title', 'Rapports financiers')
@section('content')
<div class="flex items-center justify-between mb-6">
    <p class="text-zinc-400 text-sm">Rapport généré le {{ $data['generated_at'] }} — Période : {{ $data['period'] }}</p>
    <a href="{{ route('reports.pdf') }}" target="_blank" class="btn-primary">
        <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-4l-4 4m0 0l-4-4m4 4V4"/></svg>
        Télécharger le rapport
    </a>
</div>

<!-- KPIs Summary -->
@php $kpis = $data['kpis']; @endphp
<div class="grid grid-cols-2 md:grid-cols-4 gap-4 mb-6">
    <div class="kpi-card">
        <p class="text-xs text-zinc-500 uppercase mb-2">MRR</p>
        <p class="text-xl font-bold text-indigo-400">{{ number_format($kpis['mrr'], 2, ',', ' ') }} €</p>
    </div>
    <div class="kpi-card">
        <p class="text-xs text-zinc-500 uppercase mb-2">ARR</p>
        <p class="text-xl font-bold text-teal-400">{{ number_format($kpis['arr'], 2, ',', ' ') }} €</p>
    </div>
    <div class="kpi-card">
        <p class="text-xs text-zinc-500 uppercase mb-2">Profit du mois</p>
        <p class="text-xl font-bold {{ $kpis['net_profit_month'] >= 0 ? 'text-green-400' : 'text-red-400' }}">{{ number_format($kpis['net_profit_month'], 2, ',', ' ') }} €</p>
    </div>
    <div class="kpi-card">
        <p class="text-xs text-zinc-500 uppercase mb-2">Marge</p>
        <p class="text-xl font-bold {{ $kpis['margin_month'] >= 20 ? 'text-green-400' : 'text-yellow-400' }}">{{ $kpis['margin_month'] }} %</p>
    </div>
</div>

<!-- Stats -->
<div class="grid grid-cols-3 gap-4 mb-6">
    <div class="card text-center">
        <p class="text-3xl font-bold text-white">{{ $data['clients_count'] }}</p>
        <p class="text-sm text-zinc-500 mt-1">Clients actifs</p>
    </div>
    <div class="card text-center">
        <p class="text-3xl font-bold text-white">{{ $data['services_count'] }}</p>
        <p class="text-sm text-zinc-500 mt-1">Services actifs</p>
    </div>
    <div class="card text-center">
        <p class="text-3xl font-bold text-white">{{ $data['subscriptions_count'] }}</p>
        <p class="text-sm text-zinc-500 mt-1">Abonnements actifs</p>
    </div>
</div>

<!-- Top clients -->
<div class="grid grid-cols-1 md:grid-cols-2 gap-6 mb-6">
    <div class="card">
        <h3 class="text-sm font-semibold text-zinc-300 mb-4">Top 5 clients par profit</h3>
        @foreach($data['top_clients'] as $client)
        <div class="flex items-center justify-between py-2 border-b border-zinc-800 last:border-0 text-sm">
            <span class="text-white">{{ $client->name }}</span>
            <span class="{{ $client->net_profit >= 0 ? 'text-green-400' : 'text-red-400' }} font-medium">{{ number_format($client->net_profit, 2, ',', ' ') }} €</span>
        </div>
        @endforeach
    </div>
    <div class="card">
        <h3 class="text-sm font-semibold text-zinc-300 mb-4">Top 5 services par revenus</h3>
        @foreach($data['top_services'] as $service)
        <div class="flex items-center justify-between py-2 border-b border-zinc-800 last:border-0 text-sm">
            <span class="text-white">{{ $service->name }}</span>
            <span class="text-teal-400 font-medium">{{ number_format($service->total_revenue, 2, ',', ' ') }} €</span>
        </div>
        @endforeach
    </div>
</div>

<!-- Recent revenues -->
<div class="card overflow-hidden p-0">
    <div class="px-4 py-3 border-b border-zinc-800">
        <h3 class="text-sm font-semibold text-zinc-300">10 derniers revenus</h3>
    </div>
    <table class="w-full text-sm">
        <thead class="bg-zinc-800/50">
            <tr>
                <th class="text-left px-4 py-2 text-xs font-medium text-zinc-500 uppercase">Client</th>
                <th class="text-left px-4 py-2 text-xs font-medium text-zinc-500 uppercase">Description</th>
                <th class="text-right px-4 py-2 text-xs font-medium text-zinc-500 uppercase">Montant</th>
                <th class="text-right px-4 py-2 text-xs font-medium text-zinc-500 uppercase">Date</th>
            </tr>
        </thead>
        <tbody>
            @foreach($data['recent_revenues'] as $rev)
            <tr class="table-row border-zinc-800">
                <td class="px-4 py-2 text-zinc-300">{{ $rev->client?->name }}</td>
                <td class="px-4 py-2 text-zinc-400">{{ $rev->description ?: '—' }}</td>
                <td class="px-4 py-2 text-right text-green-400 font-medium">+{{ number_format($rev->amount, 2, ',', ' ') }} €</td>
                <td class="px-4 py-2 text-right text-zinc-500">{{ $rev->date->format('d/m/Y') }}</td>
            </tr>
            @endforeach
        </tbody>
    </table>
</div>
@endsection
