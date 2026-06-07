@extends('layouts.app')
@section('title', 'Modifier l\'abonnement')
@section('page-title', 'Modifier l\'abonnement')
@section('content')
<div class="max-w-2xl">
    <div class="card">
        <form method="POST" action="{{ route('subscriptions.update', $subscription) }}" class="space-y-4">
            @csrf @method('PUT')
            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                <div>
                    <label class="label">Client *</label>
                    <select name="client_id" required class="input">
                        @foreach($clients as $client)
                            <option value="{{ $client->id }}" {{ $subscription->client_id == $client->id ? 'selected' : '' }}>{{ $client->name }}</option>
                        @endforeach
                    </select>
                </div>
                <div>
                    <label class="label">Service *</label>
                    <select name="service_id" required class="input">
                        @foreach($services as $service)
                            <option value="{{ $service->id }}" {{ $subscription->service_id == $service->id ? 'selected' : '' }}>{{ $service->name }} — {{ number_format($service->price, 2) }} €</option>
                        @endforeach
                    </select>
                </div>
                <div>
                    <label class="label">Cycle *</label>
                    <select name="cycle" required class="input">
                        <option value="monthly" {{ $subscription->cycle === 'monthly' ? 'selected' : '' }}>Mensuel</option>
                        <option value="annual" {{ $subscription->cycle === 'annual' ? 'selected' : '' }}>Annuel</option>
                    </select>
                </div>
                <div>
                    <label class="label">Statut *</label>
                    <select name="status" required class="input">
                        <option value="actif" {{ $subscription->status === 'actif' ? 'selected' : '' }}>Actif</option>
                        <option value="inactif" {{ $subscription->status === 'inactif' ? 'selected' : '' }}>Inactif</option>
                        <option value="expiré" {{ $subscription->status === 'expiré' ? 'selected' : '' }}>Expiré</option>
                    </select>
                </div>
                <div>
                    <label class="label">Date de début *</label>
                    <input name="start_date" type="date" value="{{ $subscription->start_date->format('Y-m-d') }}" required class="input">
                </div>
                <div>
                    <label class="label">Date de fin</label>
                    <input name="end_date" type="date" value="{{ $subscription->end_date?->format('Y-m-d') }}" class="input">
                </div>
                <div class="flex items-center gap-3 pt-2">
                    <input name="auto_renewal" type="checkbox" id="auto_renewal" value="1" {{ $subscription->auto_renewal ? 'checked' : '' }} class="w-4 h-4 rounded border-zinc-600 bg-zinc-800 text-indigo-600">
                    <label for="auto_renewal" class="text-sm text-zinc-300">Renouvellement automatique</label>
                </div>
            </div>
            <div>
                <label class="label">Notes</label>
                <textarea name="notes" rows="2" class="input">{{ $subscription->notes }}</textarea>
            </div>
            <div class="flex gap-3 pt-2">
                <button type="submit" class="btn-primary">Enregistrer</button>
                <a href="{{ route('subscriptions.index') }}" class="btn-secondary">Annuler</a>
            </div>
        </form>
    </div>
</div>
@endsection
