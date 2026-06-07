@extends('layouts.app')
@section('title', 'Nouvel abonnement')
@section('page-title', 'Nouvel abonnement')
@section('content')
<div class="max-w-2xl">
    <div class="card">
        <form method="POST" action="{{ route('subscriptions.store') }}" class="space-y-4">
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
                    <label class="label">Service *</label>
                    <select name="service_id" required class="input">
                        <option value="">Sélectionner...</option>
                        @foreach($services as $service)
                            <option value="{{ $service->id }}" {{ old('service_id') == $service->id ? 'selected' : '' }}>{{ $service->name }} — {{ number_format($service->price, 2) }} €</option>
                        @endforeach
                    </select>
                </div>
                <div>
                    <label class="label">Cycle *</label>
                    <select name="cycle" required class="input">
                        <option value="monthly" {{ old('cycle') === 'monthly' ? 'selected' : '' }}>Mensuel</option>
                        <option value="annual" {{ old('cycle') === 'annual' ? 'selected' : '' }}>Annuel</option>
                    </select>
                </div>
                <div>
                    <label class="label">Statut *</label>
                    <select name="status" required class="input">
                        <option value="actif" {{ old('status','actif') === 'actif' ? 'selected' : '' }}>Actif</option>
                        <option value="inactif" {{ old('status') === 'inactif' ? 'selected' : '' }}>Inactif</option>
                        <option value="expiré" {{ old('status') === 'expiré' ? 'selected' : '' }}>Expiré</option>
                    </select>
                </div>
                <div>
                    <label class="label">Date de début *</label>
                    <input name="start_date" type="date" value="{{ old('start_date', now()->format('Y-m-d')) }}" required class="input">
                </div>
                <div>
                    <label class="label">Date de fin</label>
                    <input name="end_date" type="date" value="{{ old('end_date') }}" class="input">
                </div>
                <div class="flex items-center gap-3 pt-2">
                    <input name="auto_renewal" type="checkbox" id="auto_renewal" value="1" {{ old('auto_renewal', '1') ? 'checked' : '' }} class="w-4 h-4 rounded border-zinc-600 bg-zinc-800 text-indigo-600">
                    <label for="auto_renewal" class="text-sm text-zinc-300">Renouvellement automatique</label>
                </div>
            </div>
            <div>
                <label class="label">Notes</label>
                <textarea name="notes" rows="2" class="input">{{ old('notes') }}</textarea>
            </div>
            <div class="flex gap-3 pt-2">
                <button type="submit" class="btn-primary">Créer l'abonnement</button>
                <a href="{{ route('subscriptions.index') }}" class="btn-secondary">Annuler</a>
            </div>
        </form>
    </div>
</div>
@endsection
