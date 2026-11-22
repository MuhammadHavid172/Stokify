@props([
    'headers' => [],
])

<div class="overflow-x-auto rounded-xl border border-gray-200 bg-white shadow-sm">
    <table class="min-w-full divide-y divide-gray-200 text-left text-xs sm:text-sm">
        @if (!empty($headers))
            <thead class="bg-gray-50 text-gray-600 uppercase text-[11px] font-bold tracking-wider">
                <tr>
                    @foreach ($headers as $header)
                        <th scope="col" class="px-4 py-3.5 {{ is_array($header) && ($header['align'] ?? '') === 'right' ? 'text-right' : '' }}">
                            {{ is_array($header) ? $header['label'] : $header }}
                        </th>
                    @endforeach
                </tr>
            </thead>
        @endif
        <tbody class="divide-y divide-gray-100 bg-white text-gray-900">
            {{ $slot }}
        </tbody>
    </table>
</div>
