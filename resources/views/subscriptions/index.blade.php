@extends('layouts.app')
@section('title', 'Abonnements')
@section('page-title', 'Abonnements')
@section('content')
<div class="flex items-center justify-between mb-6">
    <p class="text-zinc-400 text-sm">{{ $subscriptions->total() }} abonnement(s)</p>
    <a href="{{ route('subscriptions.create') }}" class="btn-primary">
        <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/></svg>
        Nouvel abonnement
    </a>
</div>
<div class="card overflow-hidden p-0">
    <table class="w-full text-sm">
        <thead class="bg-zinc-800/50">
            <tr>
                <th class="text-left px-4 py-3 text-xs font-medium text-zinc-500 uppercase">Client</th>
                <th class="text-left px-4 py-3 text-xs font-medium text-zinc-500 uppercase">Service</th>
                <th class="text-left px-4 py-3 text-xs font-medium text-zinc-500 uppercase">Cycle</th>
                <th class="text-right px-4 py-3 text-xs font-medium text-zinc-500 uppercase">Montant</th>
                <th class="text-left px-4 py-3 text-xs font-medium text-zinc-500 uppercase">Début</th>
                <th class="text-center px-4 py-3 text-xs font-medium text-zinc-500 uppercase">Statut</th>
                <th class="text-right px-4 py-3 text-xs font-medium text-zinc-500 uppercase">Actions</th>
            </tr>
        </thead>
        <tbody>
            @forelse($subscriptions as $sub)
            <tr class="table-row border-zinc-800">
                <td class="px-4 py-3 font-medium text-white">
                    <a href="{{ route('clients.show', $sub->client) }}" class="hover:text-indigo-400">{{ $sub->client?->name }}</a>
                </td>
                <td class="px-4 py-3 text-zinc-400">{{ $sub->service?->name }}</td>
                <td class="px-4 py-3 text-zinc-400">{{ $sub->cycle === 'monthly' ? 'Mensuel' : 'Annuel' }}</td>
                <td class="px-4 py-3 text-right text-white">{{ number_format($sub->service?->price ?? 0, 2, ',', ' ') }} €</td>
                <td class="px-4 py-3 text-zinc-400">{{ $sub->start_date->format('d/m/Y') }}</td>
                <td class="px-4 py-3 text-center">
                    @if($sub->status === 'actif')
                        <span class="badge badge-green">Actif</span>
                    @elseif($sub->status === 'expiré')
                        <span class="badge badge-red">Expiré</span>
                    @else
                        <span class="badge badge-zinc">Inactif</span>
                    @endif
                </td>
                <td class="px-4 py-3 text-right">
                    <div class="flex items-center justify-end gap-2">
                        <a href="{{ route('subscriptions.edit', $sub) }}" class="text-zinc-400 hover:text-indigo-400">
                            <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"/></svg>
                        </a>
                        <form method="POST" action="{{ route('subscriptions.destroy', $sub) }}" onsubmit="return confirm('Supprimer cet abonnement ?')">
                            @csrf @method('DELETE')
                            <button type="submit" class="text-zinc-400 hover:text-red-400">
                                <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/></svg>
                            </button>
                        </form>
                    </div>
                </td>
            </tr>
            @empty
            <tr><td colspan="7" class="px-4 py-10 text-center text-zinc-500">Aucun abonnement.</td></tr>
            @endforelse
        </tbody>
    </table>
    @if($subscriptions->hasPages())
    <div class="px-4 py-3 border-t border-zinc-800">{{ $subscriptions->links() }}</div>
    @endif
</div>
@endsection
