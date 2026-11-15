@props([
    'variant' => 'gray',
    'dot' => false,
])

@php
    $variantClasses = [
        'primary'   => 'badge-primary',
        'secondary' => 'badge-secondary',
        'accent'    => 'badge-accent',
        'success'   => 'badge-success',
        'warning'   => 'badge-warning',
        'danger'    => 'badge-danger',
        'info'      => 'badge-info',
        'gray'      => 'badge-gray',
    ][$variant] ?? 'badge-gray';

    $dotColors = [
        'primary'   => 'bg-primary-600',
        'secondary' => 'bg-secondary-600',
        'accent'    => 'bg-accent-500',
        'success'   => 'bg-green-600',
        'warning'   => 'bg-amber-600',
        'danger'    => 'bg-red-600',
        'info'      => 'bg-blue-600',
        'gray'      => 'bg-gray-400',
    ][$variant] ?? 'bg-gray-400';
@endphp

<span {{ $attributes->merge(['class' => "badge {$variantClasses}"]) }}>
    @if ($dot)
        <span class="w-1.5 h-1.5 rounded-full {{ $dotColors }}"></span>
    @endif
    {{ $slot }}
</span>

