@extends('layouts.app')
@section('title', 'Modifier ' . $service->name)
@section('page-title', 'Modifier ' . $service->name)
@section('content')
<div class="max-w-2xl">
    <div class="card">
        <form method="POST" action="{{ route('services.update', $service) }}" class="space-y-4">
            @csrf @method('PUT')
            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                <div class="md:col-span-2">
                    <label class="label">Nom *</label>
                    <input name="name" value="{{ old('name', $service->name) }}" required class="input">
                </div>
                <div>
                    <label class="label">Type *</label>
                    <select name="type" required class="input">
                        <option value="monthly" {{ $service->type === 'monthly' ? 'selected' : '' }}>Mensuel</option>
                        <option value="annual" {{ $service->type === 'annual' ? 'selected' : '' }}>Annuel</option>
                    </select>
                </div>
                <div>
                    <label class="label">Statut *</label>
                    <select name="status" required class="input">
                        <option value="actif" {{ $service->status === 'actif' ? 'selected' : '' }}>Actif</option>
                        <option value="inactif" {{ $service->status === 'inactif' ? 'selected' : '' }}>Inactif</option>
                    </select>
                </div>
                <div>
                    <label class="label">Prix (€) *</label>
                    <input name="price" type="number" step="0.01" min="0" value="{{ old('price', $service->price) }}" required class="input">
                </div>
                <div>
                    <label class="label">Coût interne (€)</label>
                    <input name="cost" type="number" step="0.01" min="0" value="{{ old('cost', $service->cost) }}" class="input">
                </div>
                <div class="md:col-span-2">
                    <label class="label">Description</label>
                    <textarea name="description" rows="3" class="input">{{ old('description', $service->description) }}</textarea>
                </div>
            </div>
            <div class="flex gap-3 pt-2">
                <button type="submit" class="btn-primary">Enregistrer</button>
                <a href="{{ route('services.show', $service) }}" class="btn-secondary">Annuler</a>
            </div>
        </form>
    </div>
</div>
@endsection
