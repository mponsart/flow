@extends('layouts.app')

@section('content')
<div class="max-w-2xl mx-auto py-8">
    <h1 class="text-2xl font-bold mb-6">Résumé IA</h1>
    <div class="bg-card rounded shadow p-6">
        @if($summary)
            <div class="whitespace-pre-line text-zinc-200">{{ $summary }}</div>
        @else
            <div class="text-zinc-400">Aucun résumé généré.</div>
        @endif
    </div>
    <div class="mt-6">
        <a href="{{ route('ai.index') }}" class="text-primary hover:underline">Retour à l'IA</a>
    </div>
</div>
@endsection
