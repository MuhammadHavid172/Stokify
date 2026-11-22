@props([
    'title',
    'description' => null,
])

<div {{ $attributes->merge(['class' => 'flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4 mb-6 pb-2 border-b border-gray-200/60']) }}>
    <div class="min-w-0">
        <h1 class="text-xl sm:text-2xl font-bold text-gray-900 tracking-tight text-balance">
            {{ $title }}
        </h1>
        @if ($description)
            <p class="text-xs sm:text-sm text-gray-500 mt-1 max-w-3xl text-pretty">
                {{ $description }}
            </p>
        @endif
    </div>

    @if ($slot->isNotEmpty())
        <div class="flex items-center gap-2.5 shrink-0 flex-wrap sm:flex-nowrap w-full sm:w-auto [&>*]:w-full sm:[&>*]:w-auto">
            {{ $slot }}
        </div>
    @endif
</div>
