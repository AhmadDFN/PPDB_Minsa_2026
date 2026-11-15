@props([
    'type' => 'info',
    'title' => null,
    'dismissible' => false,
])

@php
    $typeClasses = [
        'success' => 'alert-success',
        'danger'  => 'alert-danger',
        'warning' => 'alert-warning',
        'info'    => 'alert-info',
    ][$type] ?? 'alert-info';

    $iconClasses = [
        'success' => 'text-green-600',
        'danger'  => 'text-red-600',
        'warning' => 'text-amber-600',
        'info'    => 'text-blue-600',
    ][$type] ?? 'text-blue-600';
@endphp

<div {{ $attributes->merge(['class' => "alert {$typeClasses} flex items-start gap-3 shadow-2xs " . ($dismissible ? 'alert-dismissible' : '')]) }} role="alert">
    {{-- Status Icon --}}
    <div class="shrink-0 mt-0.5 {{ $iconClasses }}">
        @if ($type === 'success')
            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z" />
            </svg>
        @elseif ($type === 'danger')
            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
            </svg>
        @elseif ($type === 'warning')
            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z" />
            </svg>
        @else
            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
            </svg>
        @endif
    </div>

    {{-- Content --}}
    <div class="flex-1 text-sm leading-relaxed">
        @if ($title)
            <h5 class="font-semibold mb-1">{{ $title }}</h5>
        @endif
        <div>
            {{ $slot }}
        </div>
    </div>

    {{-- Dismiss Button --}}
    @if ($dismissible)
        <button
            type="button"
            data-dismiss="alert"
            class="shrink-0 -mr-1 -mt-1 p-1 rounded-md text-gray-400 hover:text-gray-700 hover:bg-black/5 transition focus:outline-none focus:ring-2 focus:ring-offset-1 focus:ring-gray-400"
            aria-label="Tutup pemberitahuan"
        >
            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
            </svg>
        </button>
    @endif
</div>

