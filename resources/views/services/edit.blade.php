@extends('layouts.app')

@section('content')
<div class="max-w-xl mx-auto py-8">
    <h1 class="text-2xl font-bold mb-6">Modifier le service</h1>
    <form method="POST" action="{{ route('services.update', $service) }}" class="bg-card rounded shadow p-6 space-y-4">
        @csrf
        @method('PUT')
        <div>
            <label class="block text-sm font-medium mb-1">Nom</label>
            <input type="text" name="name" value="{{ $service->name }}" class="w-full rounded bg-zinc-900 border border-zinc-700 p-2" required>
        </div>
        <div>
            <label class="block text-sm font-medium mb-1">Type</label>
            <select name="type" class="w-full rounded bg-zinc-900 border border-zinc-700 p-2">
                <option value="mensuel" @if($service->type=='mensuel') selected @endif>Mensuel</option>
                <option value="annuel" @if($service->type=='annuel') selected @endif>Annuel</option>
            </select>
        </div>
        <div>
            <label class="block text-sm font-medium mb-1">Prix (€)</label>
            <input type="number" step="0.01" name="price" value="{{ $service->price }}" class="w-full rounded bg-zinc-900 border border-zinc-700 p-2" required>
        </div>
        <div>
            <label class="block text-sm font-medium mb-1">Coût mensuel (€)</label>
            <input type="number" step="0.01" name="monthly_cost" value="{{ $service->monthly_cost }}" class="w-full rounded bg-zinc-900 border border-zinc-700 p-2" required>
        </div>
        <div>
            <label class="block text-sm font-medium mb-1">Coût annuel (€)</label>
            <input type="number" step="0.01" name="annual_cost" value="{{ $service->annual_cost }}" class="w-full rounded bg-zinc-900 border border-zinc-700 p-2" required>
        </div>
        <div>
            <label class="block text-sm font-medium mb-1">Statut</label>
            <select name="status" class="w-full rounded bg-zinc-900 border border-zinc-700 p-2">
                <option value="actif" @if($service->status=='actif') selected @endif>Actif</option>
                <option value="inactif" @if($service->status=='inactif') selected @endif>Inactif</option>
            </select>
        </div>
        <button type="submit" class="bg-primary hover:bg-accent text-white px-4 py-2 rounded">Enregistrer</button>
    </form>
</div>
@endsection
