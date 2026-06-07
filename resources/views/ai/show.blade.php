@extends('layouts.app')
@section('title', $report->title)
@section('page-title', $report->getTypeLabel())
@section('content')
<div class="flex items-center justify-between mb-6">
    <div class="flex items-center gap-3">
        <span class="badge {{ $report->getTypeBadgeClass() }}">{{ $report->getTypeLabel() }}</span>
        <span class="text-zinc-500 text-sm">{{ $report->created_at->format('d/m/Y à H:i') }}</span>
    </div>
    <a href="{{ route('ai.index') }}" class="btn-secondary">← Retour</a>
</div>
<div class="card">
    <h2 class="text-lg font-semibold text-white mb-6">{{ $report->title }}</h2>
    <div id="report-content" class="prose prose-invert max-w-none text-zinc-300 text-sm leading-relaxed"></div>
</div>
@endsection

@push('scripts')
<script>
    // Simple Markdown renderer
    const content = @json($report->content);
    function renderMarkdown(md) {
        return md
            .replace(/^#{3}\s+(.+)$/gm, '<h3 class="text-base font-semibold text-white mt-6 mb-2">$1</h3>')
            .replace(/^#{2}\s+(.+)$/gm, '<h2 class="text-lg font-bold text-white mt-8 mb-3 border-b border-zinc-800 pb-2">$1</h2>')
            .replace(/^#{1}\s+(.+)$/gm, '<h1 class="text-xl font-bold text-white mt-8 mb-4">$1</h1>')
            .replace(/\*\*(.+?)\*\*/g, '<strong class="text-white font-semibold">$1</strong>')
            .replace(/\*(.+?)\*/g, '<em class="text-zinc-200">$1</em>')
            .replace(/^>\s+(.+)$/gm, '<blockquote class="border-l-2 border-indigo-500 pl-4 my-4 text-zinc-400 italic">$1</blockquote>')
            .replace(/^---$/gm, '<hr class="border-zinc-700 my-6">')
            .replace(/^[-*]\s+(.+)$/gm, '<li class="ml-4 list-disc text-zinc-300">$1</li>')
            .replace(/^(\d+)\.\s+(.+)$/gm, '<li class="ml-4 list-decimal text-zinc-300">$2</li>')
            .replace(/\|(.+)\|/g, (match) => {
                const cols = match.split('|').filter(s => s.trim());
                if (cols.every(c => c.trim().match(/^-+$/))) return '';
                return '<tr>' + cols.map(c => `<td class="px-3 py-2 border border-zinc-700 text-zinc-300">${c.trim()}</td>`).join('') + '</tr>';
            })
            .replace(/(<tr>.*<\/tr>)/gs, '<table class="w-full border-collapse my-4 text-sm">$1</table>')
            .replace(/\n\n/g, '</p><p class="mb-3 text-zinc-300">')
            .replace(/\n/g, '<br>');
    }
    document.getElementById('report-content').innerHTML = '<p class="mb-3 text-zinc-300">' + renderMarkdown(content) + '</p>';
</script>
@endpush
