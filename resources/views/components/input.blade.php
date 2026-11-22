@props([
    'label' => null,
    'id' => null,
    'name' => null,
    'type' => 'text',
    'error' => null,
    'hint' => null,
    'required' => false,
])

<div class="space-y-1">
    @if ($label)
        <label @if($id) for="{{ $id }}" @endif class="block text-xs font-semibold text-gray-700">
            {{ $label }}
            @if ($required)
                <span class="text-rose-600 font-bold">*</span>
            @endif
        </label>
    @endif

    <input type="{{ $type }}"
           @if($id) id="{{ $id }}" @endif
           @if($name) name="{{ $name }}" @endif
           {{ $attributes->merge(['class' => 'w-full rounded-lg border-gray-300 shadow-sm text-xs sm:text-sm text-gray-900 focus:border-[#166534] focus:ring-1 focus:ring-[#166534] disabled:bg-gray-50 disabled:text-gray-500 transition']) }} />

    @if ($hint)
        <p class="text-[11px] text-gray-500">{{ $hint }}</p>
    @endif

    @if ($error)
        <p class="text-xs text-rose-600 font-medium">{{ $error }}</p>
    @endif
</div>
