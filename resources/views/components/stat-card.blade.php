@props([
    'title',
    'value' => '—',
    'description' => null,
    'icon' => null,
    'badge' => null,
])

<div {{ $attributes->merge(['class' => 'rounded-xl bg-white border border-gray-200 p-5 shadow-sm hover:border-gray-300 transition flex flex-col justify-between']) }}>
    <div>
        <div class="flex items-center justify-between">
            <span class="text-xs font-semibold text-gray-500">{{ $title }}</span>
            @if ($icon || isset($iconSlot))
                <div class="w-8 h-8 rounded-lg bg-[#DCFCE7] text-[#166534] flex items-center justify-center shrink-0">
                    {{ $icon ?? $iconSlot }}
                </div>
            @endif
        </div>
        <div class="mt-2.5">
            <h3 class="text-xl sm:text-2xl font-bold text-gray-900 tracking-tight tabular-nums">
                {{ $value }}
            </h3>
        </div>
    </div>
    @if ($description || $badge)
        <div class="mt-3 pt-2.5 border-t border-gray-100 flex items-center justify-between text-xs text-gray-500">
            @if ($description)
                <div class="truncate text-[11px] sm:text-xs">{{ $description }}</div>
            @endif
            @if ($badge)
                <span class="text-[10px] font-semibold px-2 py-0.5 rounded-full bg-gray-100 text-gray-700">{{ $badge }}</span>
            @endif
        </div>
    @endif
</div>
