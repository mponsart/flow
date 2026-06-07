@extends('layouts.app')
@section('title', $client->name)
@section('page-title', $client->name)
@section('content')
<div class="flex items-center justify-between mb-6">
    <div>
        <p class="text-zinc-400 text-sm">{{ $client->company }}</p>
        @if($client->status === 'actif')
            <span class="badge badge-green mt-1">Actif</span>
        @elseif($client->status === 'inactif')
            <span class="badge badge-zinc mt-1">Inactif</span>
        @else
            <span class="badge badge-yellow mt-1">Suspendu</span>
        @endif
    </div>
    <a href="{{ route('clients.edit', $client) }}" class="btn-secondary">Modifier</a>
</div>

<!-- Stats -->
<div class="grid grid-cols-1 md:grid-cols-3 gap-4 mb-6">
    <div class="kpi-card">
        <p class="text-xs text-zinc-500 uppercase tracking-wide mb-2">Revenus totaux</p>
        <p class="text-xl font-bold text-green-400">{{ number_format($client->total_revenue, 2, ',', ' ') }} €</p>
    </div>
    <div class="kpi-card">
        <p class="text-xs text-zinc-500 uppercase tracking-wide mb-2">Dépenses totales</p>
        <p class="text-xl font-bold text-red-400">{{ number_format($client->total_expenses, 2, ',', ' ') }} €</p>
    </div>
    <div class="kpi-card">
        <p class="text-xs text-zinc-500 uppercase tracking-wide mb-2">Profit net</p>
        <p class="text-xl font-bold {{ $client->net_profit >= 0 ? 'text-green-400' : 'text-red-400' }}">{{ number_format($client->net_profit, 2, ',', ' ') }} €</p>
    </div>
</div>

<div class="grid grid-cols-1 md:grid-cols-2 gap-6">
    <!-- Infos -->
    <div class="card">
        <h3 class="text-sm font-semibold text-zinc-300 mb-4">Informations</h3>
        <dl class="space-y-3 text-sm">
            @if($client->email)
            <div class="flex justify-between">
                <dt class="text-zinc-500">Email</dt>
                <dd class="text-zinc-200">{{ $client->email }}</dd>
            </div>
            @endif
            @if($client->phone)
            <div class="flex justify-between">
                <dt class="text-zinc-500">Téléphone</dt>
                <dd class="text-zinc-200">{{ $client->phone }}</dd>
            </div>
            @endif
            @if($client->notes)
            <div>
                <dt class="text-zinc-500 mb-1">Notes</dt>
                <dd class="text-zinc-300">{{ $client->notes }}</dd>
            </div>
            @endif
        </dl>
    </div>

    <!-- Subscriptions -->
    <div class="card">
        <div class="flex items-center justify-between mb-4">
            <h3 class="text-sm font-semibold text-zinc-300">Abonnements</h3>
            <a href="{{ route('subscriptions.create') }}?client_id={{ $client->id }}" class="text-xs text-indigo-400 hover:text-indigo-300">+ Ajouter</a>
        </div>
        @forelse($client->subscriptions as $sub)
        <div class="flex items-center justify-between py-2 border-b border-zinc-800 last:border-0">
            <div>
                <p class="text-sm font-medium text-white">{{ $sub->service?->name }}</p>
                <p class="text-xs text-zinc-500">{{ $sub->cycle === 'monthly' ? 'Mensuel' : 'Annuel' }} — depuis {{ $sub->start_date->format('d/m/Y') }}</p>
            </div>
            <div class="text-right">
                <p class="text-sm font-medium text-white">{{ number_format($sub->service?->price ?? 0, 2, ',', ' ') }} €</p>
                @if($sub->status === 'actif')
                    <span class="badge badge-green">Actif</span>
                @else
                    <span class="badge badge-zinc">{{ $sub->status }}</span>
                @endif
            </div>
        </div>
        @empty
            <p class="text-zinc-500 text-sm">Aucun abonnement.</p>
        @endforelse
    </div>
</div>

<!-- Revenus récents -->
<div class="card mt-6">
    <div class="flex items-center justify-between mb-4">
        <h3 class="text-sm font-semibold text-zinc-300">Revenus récents</h3>
        <a href="{{ route('revenues.create') }}?client_id={{ $client->id }}" class="text-xs text-indigo-400">+ Ajouter</a>
    </div>
    @forelse($client->revenues->sortByDesc('date')->take(5) as $rev)
    <div class="flex items-center justify-between py-2 border-b border-zinc-800 last:border-0 text-sm">
        <span class="text-zinc-300">{{ $rev->description ?: 'Revenu' }}</span>
        <div class="flex items-center gap-4">
            @if($rev->status === 'paid')
                <span class="badge badge-green">Payé</span>
            @else
                <span class="badge badge-yellow">En attente</span>
            @endif
            <span class="text-green-400 font-medium">+{{ number_format($rev->amount, 2, ',', ' ') }} €</span>
            <span class="text-zinc-500">{{ $rev->date->format('d/m/Y') }}</span>
        </div>
    </div>
    @empty
        <p class="text-zinc-500 text-sm">Aucun revenu enregistré.</p>
    @endforelse
</div>
@endsection
