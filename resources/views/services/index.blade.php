@extends('layouts.app')

@section('content')
<div class="max-w-4xl mx-auto py-8">
    <h1 class="text-2xl font-bold mb-6">Services</h1>
    <div class="mb-4 flex justify-end">
        <a href="#" class="bg-primary hover:bg-accent text-white px-4 py-2 rounded">Ajouter un service</a>
    </div>
    <div class="bg-card rounded shadow p-4">
        <table class="min-w-full text-sm">
            <thead>
                <tr class="border-b border-zinc-700">
                    <th class="py-2 px-3 text-left">Nom</th>
                    <th class="py-2 px-3 text-left">Type</th>
                    <th class="py-2 px-3 text-left">Prix</th>
                    <th class="py-2 px-3 text-left">Coût mensuel</th>
                    <th class="py-2 px-3 text-left">Coût annuel</th>
                    <th class="py-2 px-3 text-left">Statut</th>
                    <th class="py-2 px-3 text-left">Actions</th>
                </tr>
            </thead>
            <tbody>
                @forelse($services as $service)
                <tr class="border-b border-zinc-800 hover:bg-zinc-800 transition">
                    <td class="py-2 px-3">{{ $service->name }}</td>
                    <td class="py-2 px-3">{{ ucfirst($service->type) }}</td>
                    <td class="py-2 px-3">{{ $service->price }} €</td>
                    <td class="py-2 px-3">{{ $service->monthly_cost }} €</td>
                    <td class="py-2 px-3">{{ $service->annual_cost }} €</td>
                    <td class="py-2 px-3">
                        <span class="px-2 py-1 rounded text-xs {{ $service->status === 'actif' ? 'bg-green-600' : 'bg-red-600' }}">
                            {{ ucfirst($service->status) }}
                        </span>
                    </td>
                    <td class="py-2 px-3">
                        <a href="#" class="text-primary hover:underline">Voir</a>
                    </td>
                </tr>
                @empty
                <tr>
                    <td colspan="7" class="py-4 text-center text-zinc-400">Aucun service enregistré.</td>
                </tr>
                @endforelse
            </tbody>
        </table>
    </div>
</div>
@endsection
