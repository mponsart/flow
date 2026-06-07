@extends('layouts.app')
@section('title', $service->name)
@section('page-title', $service->name)
@section('content')
<div class="flex items-center justify-between mb-6">
    <div>
        <p class="text-zinc-400 text-sm">{{ $service->type === 'monthly' ? 'Facturation mensuelle' : 'Facturation annuelle' }} — {{ number_format($service->price, 2, ',', ' ') }} €</p>
        @if($service->status === 'actif')
            <span class="badge badge-green mt-1">Actif</span>
        @else
            <span class="badge badge-zinc mt-1">Inactif</span>
        @endif
    </div>
    <a href="{{ route('services.edit', $service) }}" class="btn-secondary">Modifier</a>
</div>
<div class="grid grid-cols-1 md:grid-cols-3 gap-4 mb-6">
    <div class="kpi-card">
        <p class="text-xs text-zinc-500 uppercase tracking-wide mb-2">Abonnés actifs</p>
        <p class="text-xl font-bold text-white">{{ $service->subscriber_count }}</p>
    </div>
    <div class="kpi-card">
        <p class="text-xs text-zinc-500 uppercase tracking-wide mb-2">Revenus totaux</p>
        <p class="text-xl font-bold text-green-400">{{ number_format($service->total_revenue, 2, ',', ' ') }} €</p>
    </div>
    <div class="kpi-card">
        <p class="text-xs text-zinc-500 uppercase tracking-wide mb-2">Revenu mensuel</p>
        <p class="text-xl font-bold text-indigo-400">{{ number_format($service->monthly_revenue, 2, ',', ' ') }} €/mois</p>
    </div>
</div>

@if($service->description)
<div class="card mb-6">
    <h3 class="text-sm font-semibold text-zinc-300 mb-2">Description</h3>
    <p class="text-zinc-400 text-sm">{{ $service->description }}</p>
</div>
@endif

<div class="card">
    <h3 class="text-sm font-semibold text-zinc-300 mb-4">Clients abonnés</h3>
    @forelse($service->subscriptions as $sub)
    <div class="flex items-center justify-between py-2 border-b border-zinc-800 last:border-0 text-sm">
        <div>
            <p class="font-medium text-white">{{ $sub->client?->name }}</p>
            <p class="text-xs text-zinc-500">{{ $sub->cycle === 'monthly' ? 'Mensuel' : 'Annuel' }} — depuis {{ $sub->start_date->format('d/m/Y') }}</p>
        </div>
        <div class="text-right">
            @if($sub->status === 'actif')
                <span class="badge badge-green">Actif</span>
            @else
                <span class="badge badge-zinc">{{ $sub->status }}</span>
            @endif
        </div>
    </div>
    @empty
        <p class="text-zinc-500 text-sm">Aucun abonné.</p>
    @endforelse
</div>
@endsection
