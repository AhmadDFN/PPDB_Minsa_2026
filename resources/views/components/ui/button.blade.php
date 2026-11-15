@props([
    'variant' => 'primary',
    'size'    => 'md',
    'type'    => 'button',
    'href'    => null,
    'disabled' => false,
])

@php
    $variantClasses = [
        'primary'   => 'btn-primary',
        'secondary' => 'btn-secondary',
        'accent'    => 'btn-accent',
        'outline'   => 'btn-outline',
        'danger'    => 'btn-danger',
        'ghost'     => 'btn-ghost',
    ][$variant] ?? 'btn-primary';

    $sizeClasses = [
        'sm' => 'btn-sm',
        'md' => '',
        'lg' => 'btn-lg',
    ][$size] ?? '';

    $classes = "btn {$variantClasses} {$sizeClasses}";
@endphp

@if ($href)
    <a href="{{ $href }}" {{ $attributes->merge(['class' => $classes]) }}>
        {{ $slot }}
    </a>
@else
    <button type="{{ $type }}" {{ $disabled ? 'disabled' : '' }} {{ $attributes->merge(['class' => $classes]) }}>
        {{ $slot }}
    </button>
@endif

