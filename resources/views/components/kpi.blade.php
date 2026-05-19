<div class="bg-kpi rounded-lg p-5 flex flex-col items-start shadow-md min-w-[160px]">
    <div class="text-xs text-zinc-400 mb-1">{{ $label }}</div>
    <div class="text-2xl font-bold text-primary">{{ $value }}</div>
    @if(isset($trend))
        <div class="text-xs mt-1 {{ $trend > 0 ? 'text-green-400' : ($trend < 0 ? 'text-red-400' : 'text-zinc-400') }}">
            {{ $trend > 0 ? '+' : '' }}{{ $trend }}%
        </div>
    @endif
</div>
