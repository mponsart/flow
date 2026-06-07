@extends('layouts.app')
@section('title', 'Intelligence Artificielle')
@section('page-title', 'Intelligence IA')
@section('content')
<!-- Action buttons -->
<div class="grid grid-cols-1 md:grid-cols-3 gap-4 mb-8">
    <div class="card text-center">
        <div class="w-12 h-12 bg-indigo-900/50 rounded-xl flex items-center justify-center mx-auto mb-3">
            <svg class="w-6 h-6 text-indigo-400" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/></svg>
        </div>
        <h3 class="font-semibold text-white mb-1">Résumé financier</h3>
        <p class="text-zinc-500 text-sm mb-4">Vue d'ensemble des KPIs et performances du mois.</p>
        <form method="POST" action="{{ route('ai.summary') }}">
            @csrf
            <button type="submit" id="btn-summary" onclick="showLoading(this)" class="btn-primary w-full justify-center">
                <span class="btn-text">Générer le résumé</span>
                <span class="btn-loading hidden">
                    <svg class="w-4 h-4 animate-spin" fill="none" viewBox="0 0 24 24"><circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"/><path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4z"/></svg>
                    Génération...
                </span>
            </button>
        </form>
    </div>
    <div class="card text-center">
        <div class="w-12 h-12 bg-teal-900/50 rounded-xl flex items-center justify-center mx-auto mb-3">
            <svg class="w-6 h-6 text-teal-400" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z"/></svg>
        </div>
        <h3 class="font-semibold text-white mb-1">Analyse approfondie</h3>
        <p class="text-zinc-500 text-sm mb-4">Tendances, recommandations et opportunités de croissance.</p>
        <form method="POST" action="{{ route('ai.analysis') }}">
            @csrf
            <button type="submit" onclick="showLoading(this)" class="btn-primary w-full justify-center" style="background: #0d9488; --tw-bg-opacity: 1">
                <span class="btn-text">Analyser les finances</span>
                <span class="btn-loading hidden">
                    <svg class="w-4 h-4 animate-spin" fill="none" viewBox="0 0 24 24"><circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"/><path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4z"/></svg>
                    Analyse en cours...
                </span>
            </button>
        </form>
    </div>
    <div class="card text-center">
        <div class="w-12 h-12 bg-red-900/50 rounded-xl flex items-center justify-center mx-auto mb-3">
            <svg class="w-6 h-6 text-red-400" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"/></svg>
        </div>
        <h3 class="font-semibold text-white mb-1">Détection d'anomalies</h3>
        <p class="text-zinc-500 text-sm mb-4">Identifie les dépenses inhabituelles et risques financiers.</p>
        <form method="POST" action="{{ route('ai.anomalies') }}">
            @csrf
            <button type="submit" onclick="showLoading(this)" class="btn-primary w-full justify-center bg-red-700 hover:bg-red-800">
                <span class="btn-text">Détecter les anomalies</span>
                <span class="btn-loading hidden">
                    <svg class="w-4 h-4 animate-spin" fill="none" viewBox="0 0 24 24"><circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"/><path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4z"/></svg>
                    Détection...
                </span>
            </button>
        </form>
    </div>
</div>

<!-- Historique des rapports -->
<div class="card overflow-hidden p-0">
    <div class="px-4 py-3 border-b border-zinc-800 flex items-center justify-between">
        <h3 class="text-sm font-semibold text-zinc-300">Historique des rapports IA</h3>
        <span class="text-xs text-zinc-500">{{ $reports->total() }} rapport(s)</span>
    </div>
    @forelse($reports as $report)
    <div class="px-4 py-3 border-b border-zinc-800 last:border-0 flex items-center justify-between hover:bg-zinc-800/30">
        <div class="flex items-center gap-3">
            <span class="badge {{ $report->getTypeBadgeClass() }}">{{ $report->getTypeLabel() }}</span>
            <span class="text-sm text-zinc-300">{{ $report->title }}</span>
        </div>
        <div class="flex items-center gap-4">
            <span class="text-xs text-zinc-500">{{ $report->created_at->format('d/m/Y H:i') }}</span>
            <a href="{{ route('ai.show', $report->id) }}" class="text-xs text-indigo-400 hover:text-indigo-300">Voir →</a>
        </div>
    </div>
    @empty
    <div class="px-4 py-12 text-center text-zinc-500">
        <svg class="w-12 h-12 mx-auto mb-3 text-zinc-700" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9.663 17h4.673M12 3v1m6.364 1.636l-.707.707M21 12h-1M4 12H3m3.343-5.657l-.707-.707m2.828 9.9a5 5 0 117.072 0l-.548.547A3.374 3.374 0 0014 18.469V19a2 2 0 11-4 0v-.531c0-.895-.356-1.754-.988-2.386l-.548-.547z"/></svg>
        <p>Aucun rapport généré. Cliquez sur un bouton ci-dessus pour démarrer.</p>
    </div>
    @endforelse
    @if($reports->hasPages())
    <div class="px-4 py-3 border-t border-zinc-800">{{ $reports->links() }}</div>
    @endif
</div>
@endsection

@push('scripts')
<script>
function showLoading(btn) {
    btn.disabled = true;
    btn.querySelector('.btn-text').classList.add('hidden');
    btn.querySelector('.btn-loading').classList.remove('hidden');
}
</script>
@endpush
