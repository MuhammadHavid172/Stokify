@props([
    'variant' => 'primary', // primary, secondary, danger, ghost
    'size' => 'md',        // sm, md, lg
    'type' => 'button',
    'icon' => null,
])

@php
    $variants = [
        'primary' => 'bg-[#166534] hover:bg-[#14532D] text-white border border-transparent shadow-sm focus:ring-[#166534]',
        'secondary' => 'bg-white hover:bg-gray-50 text-gray-700 border border-gray-300 shadow-sm focus:ring-gray-300',
        'danger' => 'bg-[#DC2626] hover:bg-[#B91C1C] text-white border border-transparent shadow-sm focus:ring-[#DC2626]',
        'ghost' => 'bg-transparent hover:bg-gray-100 text-gray-600 hover:text-gray-900 border border-transparent focus:ring-gray-200',
    ];

    $sizes = [
        'sm' => 'text-xs px-2.5 py-1.5 rounded-lg min-h-[36px]',
        'md' => 'text-xs sm:text-sm px-3.5 py-2 rounded-lg min-h-[40px]',
        'lg' => 'text-sm sm:text-base px-4 py-2.5 rounded-lg min-h-[44px]',
    ];

    $varClass = $variants[$variant] ?? $variants['primary'];
    $sizeClass = $sizes[$size] ?? $sizes['md'];
@endphp

<button type="{{ $type }}" {{ $attributes->merge(['class' => "inline-flex items-center justify-center gap-2 font-semibold transition focus:outline-none focus:ring-2 focus:ring-offset-1 disabled:opacity-50 disabled:cursor-not-allowed {$varClass} {$sizeClass}"]) }}>
    @if ($icon)
        {{ $icon }}
    @endif
    {{ $slot }}
</button>
