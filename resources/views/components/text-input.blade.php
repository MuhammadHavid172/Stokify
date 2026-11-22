@props(['disabled' => false])

<input {{ $disabled ? 'disabled' : '' }} {!! $attributes->merge(['class' => 'w-full border-gray-300 rounded-lg shadow-sm text-xs sm:text-sm text-gray-900 focus:border-[#166534] focus:ring-[#166534] disabled:bg-gray-50 disabled:text-gray-500']) !!}>
