@props([
    'title' => null,
    'description' => null,
    'action' => null,
    'padding' => 'p-5 sm:p-6',
])

<div {{ $attributes->merge(['class' => "bg-white rounded-xl border border-gray-200 shadow-sm {$padding}"]) }}>
    @if ($title || $description || $action)
        <div class="flex items-center justify-between pb-4 mb-4 border-b border-gray-100">
            <div>
                @if ($title)
                    <h3 class="text-base font-semibold text-gray-900 tracking-tight">{{ $title }}</h3>
                @endif
                @if ($description)
                    <p class="text-xs text-gray-500 mt-0.5">{{ $description }}</p>
                @endif
            </div>
            @if ($action)
                <div>{{ $action }}</div>
            @endif
        </div>
    @endif
    {{ $slot }}
</div>
