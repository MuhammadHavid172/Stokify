@props([
    'name', // Unique identifier to dispatch/listen: e.g. open-modal="confirm-delete"
    'title' => 'Konfirmasi Tindakan',
    'confirmText' => 'Lanjutkan',
    'confirmVariant' => 'danger', // danger, primary, warning
])

@php
    $btnClasses = [
        'danger' => 'bg-rose-600 hover:bg-rose-700 text-white',
        'primary' => 'bg-[#166534] hover:bg-[#EA580C] text-white',
        'warning' => 'bg-amber-600 hover:bg-amber-700 text-white',
    ][$confirmVariant] ?? 'bg-rose-600 hover:bg-rose-700 text-white';
@endphp

<div x-data="{ open: false, data: {} }"
     x-on:open-modal.window="if ($event.detail.name === '{{ $name }}') { open = true; data = $event.detail.data || {}; }"
     x-on:close-modal.window="if ($event.detail === '{{ $name }}') { open = false; }"
     x-on:keydown.escape.window="open = false"
     x-show="open"
     x-cloak
     class="relative z-50">
    
    <!-- Backdrop -->
    <div x-show="open" 
         x-transition:enter="ease-out duration-200" 
         x-transition:enter-start="opacity-0" 
         x-transition:enter-end="opacity-100" 
         x-transition:leave="ease-in duration-150" 
         x-transition:leave-start="opacity-100" 
         x-transition:leave-end="opacity-0"
         @click="open = false"
         class="fixed inset-0 bg-black/50 backdrop-blur-xs"></div>

    <!-- Modal Dialog -->
    <div class="fixed inset-0 z-10 overflow-y-auto">
        <div class="flex min-h-full items-center justify-center p-4 text-center sm:p-0">
            <div x-show="open" 
                 x-transition:enter="ease-out duration-200" 
                 x-transition:enter-start="opacity-0 translate-y-4 sm:translate-y-0 sm:scale-95" 
                 x-transition:enter-end="opacity-100 translate-y-0 sm:scale-100" 
                 x-transition:leave="ease-in duration-150" 
                 x-transition:leave-start="opacity-100 translate-y-0 sm:scale-100" 
                 x-transition:leave-end="opacity-0 translate-y-4 sm:translate-y-0 sm:scale-95"
                 class="relative transform overflow-hidden rounded-3xl bg-white border border-slate-200 p-6 sm:p-8 text-left shadow-2xl transition-all w-full sm:max-w-md">
                
                <div class="flex items-start gap-4">
                    <div class="w-12 h-12 rounded-2xl bg-amber-50 text-amber-600 flex items-center justify-center shrink-0 border border-amber-200/60">
                        <svg class="w-6 h-6" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z" />
                        </svg>
                    </div>

                    <div>
                        <h3 class="text-lg font-bold text-[#1E2A4A] tracking-tight">
                            {{ $title }}
                        </h3>
                        <div class="mt-2 text-xs sm:text-sm text-slate-500 leading-relaxed">
                            {{ $slot }}
                        </div>
                    </div>
                </div>

                <div class="mt-6 flex flex-col-reverse sm:flex-row sm:justify-end gap-3 pt-4 border-t border-slate-100">
                    <button type="button" 
                            @click="open = false" 
                            class="w-full sm:w-auto px-4 py-2.5 rounded-xl border border-slate-300 text-slate-700 hover:bg-slate-50 font-bold text-xs transition">
                        Batal
                    </button>
                    @if (isset($action))
                        {{ $action }}
                    @endif
                </div>

            </div>
        </div>
    </div>
</div>
