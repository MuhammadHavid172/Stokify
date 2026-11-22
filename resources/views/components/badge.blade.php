@props([
    'variant' => 'neutral', // success, warning, danger, info, neutral
    'size' => 'md',       // sm, md
])

@php
    $variants = [
        'success' => 'bg-[#DCFCE7] text-[#166534] border border-[#86EFAC]/60',
        'green' => 'bg-[#DCFCE7] text-[#166534] border border-[#86EFAC]/60',
        'aktif' => 'bg-[#DCFCE7] text-[#166534] border border-[#86EFAC]/60',
        'warning' => 'bg-[#FEF3C7] text-[#92400E] border border-[#FCD34D]/60',
        'amber' => 'bg-[#FEF3C7] text-[#92400E] border border-[#FCD34D]/60',
        'danger' => 'bg-[#FEE2E2] text-[#991B1B] border border-[#FCA5A5]/60',
        'red' => 'bg-[#FEE2E2] text-[#991B1B] border border-[#FCA5A5]/60',
        'nonaktif' => 'bg-[#FEE2E2] text-[#991B1B] border border-[#FCA5A5]/60',
        'info' => 'bg-[#DBEAFE] text-[#1E40AF] border border-[#93C5FD]/60',
        'blue' => 'bg-[#DBEAFE] text-[#1E40AF] border border-[#93C5FD]/60',
        'neutral' => 'bg-gray-100 text-gray-700 border border-gray-200',
        'gray' => 'bg-gray-100 text-gray-700 border border-gray-200',
        'abu' => 'bg-gray-100 text-gray-700 border border-gray-200',
    ];

    $sizes = [
        'sm' => 'text-[10px] px-2 py-0.5',
        'md' => 'text-xs px-2.5 py-1',
    ];

    $classes = $variants[$variant] ?? $variants['neutral'];
    $sizeClass = $sizes[$size] ?? $sizes['md'];
@endphp

<span {{ $attributes->merge(['class' => "inline-flex items-center gap-1 font-semibold rounded-full {$classes} {$sizeClass}"]) }}>
    {{ $slot }}
</span>
