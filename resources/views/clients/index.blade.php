@extends('layouts.app')

@section('content')
<div class="max-w-4xl mx-auto py-8">
    <h1 class="text-2xl font-bold mb-6">Clients</h1>
    <div class="mb-4 flex justify-end">
        <a href="{{ route('clients.create') }}" class="bg-primary hover:bg-accent text-white px-4 py-2 rounded">Ajouter un client</a>
    </div>
    <div class="bg-card rounded shadow p-4">
        <table class="min-w-full text-sm">
            <thead>
                <tr class="border-b border-zinc-700">
                    <th class="py-2 px-3 text-left">Nom</th>
                    <th class="py-2 px-3 text-left">Email</th>
                    <th class="py-2 px-3 text-left">Entreprise</th>
                    <th class="py-2 px-3 text-left">Statut</th>
                    <th class="py-2 px-3 text-left">Actions</th>
                </tr>
            </thead>
            <tbody>
                @forelse($clients as $client)
                <tr class="border-b border-zinc-800 hover:bg-zinc-800 transition">
                    <td class="py-2 px-3">{{ $client->name }}</td>
                    <td class="py-2 px-3">{{ $client->email }}</td>
                    <td class="py-2 px-3">{{ $client->company }}</td>
                    <td class="py-2 px-3">
                        <span class="px-2 py-1 rounded text-xs {{ $client->status === 'actif' ? 'bg-green-600' : 'bg-red-600' }}">
                            {{ ucfirst($client->status) }}
                        </span>
                    </td>
                    <td class="py-2 px-3 flex gap-2">
                        <a href="{{ route('clients.edit', $client) }}" class="text-primary hover:underline">Éditer</a>
                        <form action="{{ route('clients.destroy', $client) }}" method="POST" onsubmit="return confirm('Supprimer ce client ?');">
                            @csrf
                            @method('DELETE')
                            <button type="submit" class="text-red-500 hover:underline">Supprimer</button>
                        </form>
                    </td>
                </tr>
                @empty
                <tr>
                    <td colspan="5" class="py-4 text-center text-zinc-400">Aucun client enregistré.</td>
                </tr>
                @endforelse
            </tbody>
        </table>
    </div>
</div>
@endsection
