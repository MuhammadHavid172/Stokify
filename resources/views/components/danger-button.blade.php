<button {{ $attributes->merge(['type' => 'submit', 'class' => 'inline-flex items-center justify-center gap-1.5 px-4 py-2 bg-[#DC2626] hover:bg-[#B91C1C] text-white rounded-lg font-semibold text-xs tracking-wider shadow-sm focus:outline-none focus:ring-2 focus:ring-[#DC2626] focus:ring-offset-2 disabled:opacity-50 transition min-h-[40px]']) }}>
    {{ $slot }}
</button>
