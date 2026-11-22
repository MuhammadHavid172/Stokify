@props([
    'title',
    'description',
    'icon' => null,
    'buttonText' => null,
    'buttonAction' => null,
])

<div {{ $attributes->merge(['class' => 'rounded-xl bg-white border border-gray-200 p-8 sm:p-12 text-center flex flex-col items-center justify-center shadow-sm']) }}>
    <div class="w-12 h-12 rounded-xl bg-gray-50 border border-gray-200 text-gray-400 flex items-center justify-center mb-3.5">
        @if (isset($icon))
            {{ $icon }}
        @else
            <svg class="w-6 h-6 text-gray-400" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.75">
                <path stroke-linecap="round" stroke-linejoin="round" d="M20 13V6a2 2 0 00-2-2H6a2 2 0 00-2 2v7m16 0v5a2 2 0 01-2 2H6a2 2 0 01-2-2v-5m16 0h-2.586a1 1 0 00-.707.293l-2.414 2.414a1 1 0 01-.707.293h-3.172a1 1 0 01-.707-.293l-2.414-2.414A1 1 0 006.586 13H4" />
            </svg>
        @endif
    </div>

    <h3 class="text-base font-bold text-gray-900 tracking-tight text-balance">
        {{ $title }}
    </h3>

    <p class="text-xs sm:text-sm text-gray-500 mt-1 max-w-md leading-relaxed text-pretty">
        {{ $description }}
    </p>

    @if ($buttonText && $buttonAction)
        <div class="mt-4">
            <a href="{{ $buttonAction }}" class="inline-flex items-center gap-2 px-4 py-2 rounded-lg bg-[#166534] hover:bg-[#14532D] text-white text-xs font-semibold shadow-xs transition">
                <span>{{ $buttonText }}</span>
            </a>
        </div>
    @elseif ($slot->isNotEmpty())
        <div class="mt-4">
            {{ $slot }}
        </div>
    @endif
</div>
