@props([
    'id',
    'title' => null,
    'size'  => 'md',
])

@php
    $maxWidth = [
        'sm' => 'max-w-md',
        'md' => 'max-w-lg',
        'lg' => 'max-w-2xl',
        'xl' => 'max-w-4xl',
    ][$size] ?? 'max-w-lg';
@endphp

<div
    id="{{ $id }}"
    class="fixed inset-0 z-50 hidden overflow-y-auto"
    aria-labelledby="{{ $id }}-title"
    role="dialog"
    aria-modal="true"
>
    {{-- Backdrop --}}
    <div
        class="fixed inset-0 bg-gray-900/50 backdrop-blur-xs transition-opacity"
        onclick="document.getElementById('{{ $id }}').classList.add('hidden')"
    ></div>

    {{-- Dialog Box --}}
    <div class="flex min-h-full items-center justify-center p-4 text-center sm:p-0">
        <div class="relative transform overflow-hidden rounded-xl bg-white text-left shadow-xl transition-all sm:my-8 sm:w-full {{ $maxWidth }}">
            {{-- Header --}}
            @if ($title)
                <div class="px-6 py-4 border-b border-gray-100 flex items-center justify-between">
                    <h3 id="{{ $id }}-title" class="text-base font-semibold text-gray-900">
                        {{ $title }}
                    </h3>
                    <button
                        type="button"
                        class="p-1.5 rounded-lg text-gray-400 hover:text-gray-600 hover:bg-gray-100 transition focus:outline-none"
                        onclick="document.getElementById('{{ $id }}').classList.add('hidden')"
                        aria-label="Tutup modal"
                    >
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                        </svg>
                    </button>
                </div>
            @endif

            {{-- Body --}}
            <div class="px-6 py-5">
                {{ $slot }}
            </div>

            {{-- Footer --}}
            @if (isset($footer))
                <div class="px-6 py-3 bg-gray-50 border-t border-gray-100 flex items-center justify-end gap-3">
                    {{ $footer }}
                </div>
            @endif
        </div>
    </div>
</div>

