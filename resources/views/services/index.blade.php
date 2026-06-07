@extends('layouts.app')
@section('title', 'Services')
@section('page-title', 'Services')
@section('content')
<div class="flex items-center justify-between mb-6">
    <p class="text-zinc-400 text-sm">{{ $services->total() }} service(s) au total</p>
    <a href="{{ route('services.create') }}" class="btn-primary">
        <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/></svg>
        Nouveau service
    </a>
</div>
<div class="card overflow-hidden p-0">
    <table class="w-full text-sm">
        <thead class="bg-zinc-800/50">
            <tr>
                <th class="text-left px-4 py-3 text-xs font-medium text-zinc-500 uppercase">Nom</th>
                <th class="text-left px-4 py-3 text-xs font-medium text-zinc-500 uppercase">Type</th>
                <th class="text-right px-4 py-3 text-xs font-medium text-zinc-500 uppercase">Prix</th>
                <th class="text-center px-4 py-3 text-xs font-medium text-zinc-500 uppercase">Statut</th>
                <th class="text-right px-4 py-3 text-xs font-medium text-zinc-500 uppercase">Abonnés</th>
                <th class="text-right px-4 py-3 text-xs font-medium text-zinc-500 uppercase">Actions</th>
            </tr>
        </thead>
        <tbody>
            @forelse($services as $service)
            <tr class="table-row border-zinc-800">
                <td class="px-4 py-3 font-medium text-white">
                    <a href="{{ route('services.show', $service) }}" class="hover:text-indigo-400">{{ $service->name }}</a>
                </td>
                <td class="px-4 py-3 text-zinc-400">{{ $service->type === 'monthly' ? 'Mensuel' : 'Annuel' }}</td>
                <td class="px-4 py-3 text-right text-white">{{ number_format($service->price, 2, ',', ' ') }} €</td>
                <td class="px-4 py-3 text-center">
                    @if($service->status === 'actif')
                        <span class="badge badge-green">Actif</span>
                    @else
                        <span class="badge badge-zinc">Inactif</span>
                    @endif
                </td>
                <td class="px-4 py-3 text-right text-zinc-400">{{ $service->active_subscriptions_count }}</td>
                <td class="px-4 py-3 text-right">
                    <div class="flex items-center justify-end gap-2">
                        <a href="{{ route('services.show', $service) }}" class="text-zinc-400 hover:text-white">
                            <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"/></svg>
                        </a>
                        <a href="{{ route('services.edit', $service) }}" class="text-zinc-400 hover:text-indigo-400">
                            <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"/></svg>
                        </a>
                        <form method="POST" action="{{ route('services.destroy', $service) }}" onsubmit="return confirm('Supprimer ce service ?')">
                            @csrf @method('DELETE')
                            <button type="submit" class="text-zinc-400 hover:text-red-400">
                                <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/></svg>
                            </button>
                        </form>
                    </div>
                </td>
            </tr>
            @empty
            <tr><td colspan="6" class="px-4 py-10 text-center text-zinc-500">Aucun service. <a href="{{ route('services.create') }}" class="text-indigo-400">Créez le premier.</a></td></tr>
            @endforelse
        </tbody>
    </table>
    @if($services->hasPages())
    <div class="px-4 py-3 border-t border-zinc-800">{{ $services->links() }}</div>
    @endif
</div>
@endsection
