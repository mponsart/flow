@extends('layouts.app')
@section('title', 'Nouveau service')
@section('page-title', 'Nouveau service')
@section('content')
<div class="max-w-2xl">
    <div class="card">
        <form method="POST" action="{{ route('services.store') }}" class="space-y-4">
            @csrf
            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                <div class="md:col-span-2">
                    <label class="label">Nom *</label>
                    <input name="name" value="{{ old('name') }}" required class="input" placeholder="Hébergement VPS Pro">
                </div>
                <div>
                    <label class="label">Type *</label>
                    <select name="type" required class="input">
                        <option value="monthly" {{ old('type') === 'monthly' ? 'selected' : '' }}>Mensuel</option>
                        <option value="annual" {{ old('type') === 'annual' ? 'selected' : '' }}>Annuel</option>
                    </select>
                </div>
                <div>
                    <label class="label">Statut *</label>
                    <select name="status" required class="input">
                        <option value="actif" {{ old('status','actif') === 'actif' ? 'selected' : '' }}>Actif</option>
                        <option value="inactif" {{ old('status') === 'inactif' ? 'selected' : '' }}>Inactif</option>
                    </select>
                </div>
                <div>
                    <label class="label">Prix (€) *</label>
                    <input name="price" type="number" step="0.01" min="0" value="{{ old('price') }}" required class="input" placeholder="49.99">
                </div>
                <div>
                    <label class="label">Coût interne (€)</label>
                    <input name="cost" type="number" step="0.01" min="0" value="{{ old('cost') }}" class="input" placeholder="20.00">
                </div>
                <div class="md:col-span-2">
                    <label class="label">Description</label>
                    <textarea name="description" rows="3" class="input" placeholder="Description du service...">{{ old('description') }}</textarea>
                </div>
            </div>
            <div class="flex gap-3 pt-2">
                <button type="submit" class="btn-primary">Créer le service</button>
                <a href="{{ route('services.index') }}" class="btn-secondary">Annuler</a>
            </div>
        </form>
    </div>
</div>
@endsection
