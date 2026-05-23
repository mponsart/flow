@extends('layouts.app')

@section('content')
<div class="container mx-auto py-8 max-w-lg">
    <h1 class="text-2xl font-bold mb-6">Modifier la dépense</h1>
    <form action="{{ route('expenses.update', $expense) }}" method="POST" class="bg-white shadow rounded-lg p-6">
        @csrf
        @method('PUT')
        <div class="mb-4">
            <label class="block text-gray-700">Service</label>
            <input type="text" value="{{ $expense->service->name ?? '-' }}" class="form-input w-full bg-gray-100" disabled>
        </div>
        <div class="mb-4">
            <label class="block text-gray-700">Catégorie</label>
            <input type="text" name="category" value="{{ old('category', $expense->category) }}" class="form-input w-full" required>
        </div>
        <div class="mb-4">
            <label class="block text-gray-700">Montant (€)</label>
            <input type="number" step="0.01" name="amount" value="{{ old('amount', $expense->amount) }}" class="form-input w-full" required>
        </div>
        <div class="mb-4">
            <label class="block text-gray-700">Date</label>
            <input type="date" name="date" value="{{ old('date', $expense->date) }}" class="form-input w-full" required>
        </div>
        <div class="mb-4">
            <label class="block text-gray-700">Note</label>
            <textarea name="note" class="form-input w-full">{{ old('note', $expense->note) }}</textarea>
        </div>
        <div class="flex gap-4 mt-6">
            <button type="submit" class="bg-blue-500 text-white px-4 py-2 rounded hover:bg-blue-600">Enregistrer</button>
            <a href="{{ route('expenses.show', $expense) }}" class="text-gray-600 hover:underline ml-auto">Annuler</a>
        </div>
    </form>
</div>
@endsection
