@extends('layouts.app')
@section('title', 'Modifier la dépense')
@section('page-title', 'Modifier la dépense')
@section('content')
<div class="max-w-2xl">
    <div class="card">
        <form method="POST" action="{{ route('expenses.update', $expense) }}" class="space-y-4">
            @csrf @method('PUT')
            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                <div>
                    <label class="label">Catégorie *</label>
                    <select name="category" required class="input">
                        @foreach($categories as $cat)
                            <option value="{{ $cat }}" {{ $expense->category === $cat ? 'selected' : '' }}>{{ $cat }}</option>
                        @endforeach
                    </select>
                </div>
                <div>
                    <label class="label">Montant (€) *</label>
                    <input name="amount" type="number" step="0.01" min="0" value="{{ $expense->amount }}" required class="input">
                </div>
                <div>
                    <label class="label">Date *</label>
                    <input name="date" type="date" value="{{ $expense->date->format('Y-m-d') }}" required class="input">
                </div>
                <div>
                    <label class="label">Client</label>
                    <select name="client_id" class="input">
                        <option value="">Aucun</option>
                        @foreach($clients as $client)
                            <option value="{{ $client->id }}" {{ $expense->client_id == $client->id ? 'selected' : '' }}>{{ $client->name }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="md:col-span-2">
                    <label class="label">Description</label>
                    <input name="description" value="{{ $expense->description }}" class="input">
                </div>
            </div>
            <div class="flex gap-3 pt-2">
                <button type="submit" class="btn-primary">Enregistrer</button>
                <a href="{{ route('expenses.index') }}" class="btn-secondary">Annuler</a>
            </div>
        </form>
    </div>
</div>
@endsection
