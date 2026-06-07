@extends('layouts.app')
@section('title', 'Nouveau revenu')
@section('page-title', 'Nouveau revenu')
@section('content')
<div class="max-w-2xl">
    <div class="card">
        <form method="POST" action="{{ route('revenues.store') }}" class="space-y-4">
            @csrf
            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                <div>
                    <label class="label">Client *</label>
                    <select name="client_id" required class="input">
                        <option value="">Sélectionner...</option>
                        @foreach($clients as $client)
                            <option value="{{ $client->id }}" {{ old('client_id', request('client_id')) == $client->id ? 'selected' : '' }}>{{ $client->name }}</option>
                        @endforeach
                    </select>
                </div>
                <div>
                    <label class="label">Abonnement (optionnel)</label>
                    <select name="subscription_id" class="input">
                        <option value="">Aucun</option>
                        @foreach($subscriptions as $sub)
                            <option value="{{ $sub->id }}" {{ old('subscription_id') == $sub->id ? 'selected' : '' }}>{{ $sub->client?->name }} — {{ $sub->service?->name }}</option>
                        @endforeach
                    </select>
                </div>
                <div>
                    <label class="label">Montant (€) *</label>
                    <input name="amount" type="number" step="0.01" min="0" value="{{ old('amount') }}" required class="input" placeholder="99.00">
                </div>
                <div>
                    <label class="label">Date *</label>
                    <input name="date" type="date" value="{{ old('date', now()->format('Y-m-d')) }}" required class="input">
                </div>
                <div>
                    <label class="label">Statut *</label>
                    <select name="status" required class="input">
                        <option value="paid" {{ old('status','paid') === 'paid' ? 'selected' : '' }}>Payé</option>
                        <option value="pending" {{ old('status') === 'pending' ? 'selected' : '' }}>En attente</option>
                    </select>
                </div>
                <div>
                    <label class="label">Description</label>
                    <input name="description" value="{{ old('description') }}" class="input" placeholder="Facture mensuelle...">
                </div>
            </div>
            <div class="flex gap-3 pt-2">
                <button type="submit" class="btn-primary">Enregistrer</button>
                <a href="{{ route('revenues.index') }}" class="btn-secondary">Annuler</a>
            </div>
        </form>
    </div>
</div>
@endsection
