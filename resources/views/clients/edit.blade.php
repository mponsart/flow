@extends('layouts.app')

@section('content')
<div class="max-w-xl mx-auto py-8">
    <h1 class="text-2xl font-bold mb-6">Modifier le client</h1>
    <form method="POST" action="{{ route('clients.update', $client) }}" class="bg-card rounded shadow p-6 space-y-4">
        @csrf
        @method('PUT')
        <div>
            <label class="block text-sm font-medium mb-1">Nom</label>
            <input type="text" name="name" value="{{ $client->name }}" class="w-full rounded bg-zinc-900 border border-zinc-700 p-2" required>
        </div>
        <div>
            <label class="block text-sm font-medium mb-1">Email</label>
            <input type="email" name="email" value="{{ $client->email }}" class="w-full rounded bg-zinc-900 border border-zinc-700 p-2" required>
        </div>
        <div>
            <label class="block text-sm font-medium mb-1">Entreprise</label>
            <input type="text" name="company" value="{{ $client->company }}" class="w-full rounded bg-zinc-900 border border-zinc-700 p-2">
        </div>
        <div>
            <label class="block text-sm font-medium mb-1">Téléphone</label>
            <input type="text" name="phone" value="{{ $client->phone }}" class="w-full rounded bg-zinc-900 border border-zinc-700 p-2">
        </div>
        <div>
            <label class="block text-sm font-medium mb-1">Statut</label>
            <select name="status" class="w-full rounded bg-zinc-900 border border-zinc-700 p-2">
                <option value="actif" @if($client->status=='actif') selected @endif>Actif</option>
                <option value="inactif" @if($client->status=='inactif') selected @endif>Inactif</option>
            </select>
        </div>
        <div>
            <label class="block text-sm font-medium mb-1">Notes</label>
            <textarea name="notes" class="w-full rounded bg-zinc-900 border border-zinc-700 p-2">{{ $client->notes }}</textarea>
        </div>
        <button type="submit" class="bg-primary hover:bg-accent text-white px-4 py-2 rounded">Enregistrer</button>
    </form>
</div>
@endsection
