<button {{ $attributes->merge(['type' => 'button', 'class' => 'inline-flex items-center justify-center gap-1.5 px-4 py-2 bg-white hover:bg-gray-50 border border-gray-300 rounded-lg font-semibold text-xs text-gray-700 tracking-wider shadow-sm focus:outline-none focus:ring-2 focus:ring-gray-300 focus:ring-offset-2 disabled:opacity-50 transition min-h-[40px]']) }}>
    {{ $slot }}
</button>
