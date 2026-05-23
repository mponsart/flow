@extends('layouts.app')

@section('content')
<div class="container mx-auto py-8 max-w-lg">
    <h1 class="text-2xl font-bold mb-6">Détail de la dépense</h1>
    <div class="bg-white shadow rounded-lg p-6">
        <div class="mb-4">
            <span class="font-semibold">Service :</span> {{ $expense->service->name ?? '-' }}
        </div>
        <div class="mb-4">
            <span class="font-semibold">Catégorie :</span> {{ $expense->category }}
        </div>
        <div class="mb-4">
            <span class="font-semibold">Montant :</span> {{ number_format($expense->amount, 2, ',', ' ') }} €
        </div>
        <div class="mb-4">
            <span class="font-semibold">Date :</span> {{ $expense->date }}
        </div>
        <div class="mb-4">
            <span class="font-semibold">Note :</span> {{ $expense->note ?? '-' }}
        </div>
        <div class="flex gap-4 mt-6">
            <a href="{{ route('expenses.edit', $expense) }}" class="bg-yellow-500 text-white px-4 py-2 rounded hover:bg-yellow-600">Modifier</a>
            <form action="{{ route('expenses.destroy', $expense) }}" method="POST" onsubmit="return confirm('Supprimer cette dépense ?');">
                @csrf
                @method('DELETE')
                <button type="submit" class="bg-red-500 text-white px-4 py-2 rounded hover:bg-red-600">Supprimer</button>
            </form>
            <a href="{{ route('expenses.index') }}" class="ml-auto text-blue-500 hover:underline">Retour à la liste</a>
        </div>
    </div>
</div>
@endsection
