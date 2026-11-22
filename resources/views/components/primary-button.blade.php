<button {{ $attributes->merge(['type' => 'submit', 'class' => 'inline-flex items-center justify-center gap-1.5 px-4 py-2 bg-[#166534] hover:bg-[#14532D] text-white rounded-lg font-semibold text-xs tracking-wider focus:outline-none focus:ring-2 focus:ring-[#166534] focus:ring-offset-2 transition shadow-sm disabled:opacity-50 min-h-[40px]']) }}>
    {{ $slot }}
</button>
