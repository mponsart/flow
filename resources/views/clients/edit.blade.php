@extends('layouts.app')
@section('title', 'Modifier ' . $client->name)
@section('page-title', 'Modifier ' . $client->name)
@section('content')
<div class="max-w-2xl">
    <div class="card">
        <form method="POST" action="{{ route('clients.update', $client) }}" class="space-y-4">
            @csrf @method('PUT')
            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                <div>
                    <label class="label">Nom *</label>
                    <input name="name" value="{{ old('name', $client->name) }}" required class="input">
                </div>
                <div>
                    <label class="label">Organisation</label>
                    <input name="company" value="{{ old('company', $client->company) }}" class="input">
                </div>
                <div>
                    <label class="label">Email</label>
                    <input name="email" type="email" value="{{ old('email', $client->email) }}" class="input">
                </div>
                <div>
                    <label class="label">Téléphone</label>
                    <input name="phone" value="{{ old('phone', $client->phone) }}" class="input">
                </div>
                <div>
                    <label class="label">Statut *</label>
                    <select name="status" required class="input">
                        <option value="actif" {{ $client->status === 'actif' ? 'selected' : '' }}>Actif</option>
                        <option value="inactif" {{ $client->status === 'inactif' ? 'selected' : '' }}>Inactif</option>
                        <option value="suspendu" {{ $client->status === 'suspendu' ? 'selected' : '' }}>Suspendu</option>
                    </select>
                </div>
            </div>
            <div>
                <label class="label">Notes</label>
                <textarea name="notes" rows="3" class="input">{{ old('notes', $client->notes) }}</textarea>
            </div>
            <div class="flex gap-3 pt-2">
                <button type="submit" class="btn-primary">Enregistrer</button>
                <a href="{{ route('clients.show', $client) }}" class="btn-secondary">Annuler</a>
            </div>
        </form>
    </div>
</div>
@endsection
