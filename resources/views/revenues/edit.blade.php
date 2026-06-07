@extends('layouts.app')
@section('title', 'Modifier le revenu')
@section('page-title', 'Modifier le revenu')
@section('content')
<div class="max-w-2xl">
    <div class="card">
        <form method="POST" action="{{ route('revenues.update', $revenue) }}" class="space-y-4">
            @csrf @method('PUT')
            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                <div>
                    <label class="label">Client *</label>
                    <select name="client_id" required class="input">
                        @foreach($clients as $client)
                            <option value="{{ $client->id }}" {{ $revenue->client_id == $client->id ? 'selected' : '' }}>{{ $client->name }}</option>
                        @endforeach
                    </select>
                </div>
                <div>
                    <label class="label">Abonnement</label>
                    <select name="subscription_id" class="input">
                        <option value="">Aucun</option>
                        @foreach($subscriptions as $sub)
                            <option value="{{ $sub->id }}" {{ $revenue->subscription_id == $sub->id ? 'selected' : '' }}>{{ $sub->client?->name }} — {{ $sub->service?->name }}</option>
                        @endforeach
                    </select>
                </div>
                <div>
                    <label class="label">Montant (€) *</label>
                    <input name="amount" type="number" step="0.01" min="0" value="{{ $revenue->amount }}" required class="input">
                </div>
                <div>
                    <label class="label">Date *</label>
                    <input name="date" type="date" value="{{ $revenue->date->format('Y-m-d') }}" required class="input">
                </div>
                <div>
                    <label class="label">Statut *</label>
                    <select name="status" required class="input">
                        <option value="paid" {{ $revenue->status === 'paid' ? 'selected' : '' }}>Payé</option>
                        <option value="pending" {{ $revenue->status === 'pending' ? 'selected' : '' }}>En attente</option>
                    </select>
                </div>
                <div>
                    <label class="label">Description</label>
                    <input name="description" value="{{ $revenue->description }}" class="input">
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
