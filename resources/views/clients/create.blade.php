@extends('layouts.app')
@section('title', 'Nouveau client')
@section('page-title', 'Nouveau client')
@section('content')
<div class="max-w-2xl">
    <div class="card">
        <form method="POST" action="{{ route('clients.store') }}" class="space-y-4">
            @csrf
            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                <div>
                    <label class="label">Nom *</label>
                    <input name="name" value="{{ old('name') }}" required class="input" placeholder="Jean Dupont">
                </div>
                <div>
                    <label class="label">Organisation</label>
                    <input name="company" value="{{ old('company') }}" class="input" placeholder="Association XYZ">
                </div>
                <div>
                    <label class="label">Email</label>
                    <input name="email" type="email" value="{{ old('email') }}" class="input" placeholder="contact@asso.fr">
                </div>
                <div>
                    <label class="label">Téléphone</label>
                    <input name="phone" value="{{ old('phone') }}" class="input" placeholder="+33 6 00 00 00 00">
                </div>
                <div>
                    <label class="label">Statut *</label>
                    <select name="status" required class="input">
                        <option value="actif" {{ old('status') === 'actif' ? 'selected' : '' }}>Actif</option>
                        <option value="inactif" {{ old('status') === 'inactif' ? 'selected' : '' }}>Inactif</option>
                        <option value="suspendu" {{ old('status') === 'suspendu' ? 'selected' : '' }}>Suspendu</option>
                    </select>
                </div>
            </div>
            <div>
                <label class="label">Notes</label>
                <textarea name="notes" rows="3" class="input" placeholder="Informations complémentaires...">{{ old('notes') }}</textarea>
            </div>
            <div class="flex gap-3 pt-2">
                <button type="submit" class="btn-primary">Créer le client</button>
                <a href="{{ route('clients.index') }}" class="btn-secondary">Annuler</a>
            </div>
        </form>
    </div>
</div>
@endsection
