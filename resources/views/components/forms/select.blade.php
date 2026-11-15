@props([
    'name',
    'label' => null,
    'options' => [],
    'selected' => null,
    'placeholder' => null,
    'required' => false,
    'disabled' => false,
    'help' => null,
])

@php
    $id = $attributes->get('id', $name);
    $hasError = $errors->has($name);
    $selectedValue = old($name, $selected);
@endphp

<div>
    @if ($label)
        <label for="{{ $id }}" class="block text-sm font-medium text-gray-700 mb-1.5">
            {{ $label }}
            @if ($required)
                <span class="text-red-500 font-bold" title="Wajib diisi">*</span>
            @endif
        </label>
    @endif

    <select
        name="{{ $name }}"
        id="{{ $id }}"
        {{ $required ? 'required' : '' }}
        {{ $disabled ? 'disabled' : '' }}
        {{ $attributes->merge([
            'class' => 'w-full rounded-lg px-4 py-2.5 text-sm transition focus:outline-none ' .
                ($hasError
                    ? 'border-red-400 text-red-900 focus:border-red-500 focus:ring-2 focus:ring-red-500/20 bg-red-50/20'
                    : 'border-gray-300 text-gray-900 focus:border-primary-600 focus:ring-2 focus:ring-primary-600/20 bg-white')
        ]) }}
    >
        @if ($placeholder)
            <option value="" disabled {{ $selectedValue === null || $selectedValue === '' ? 'selected' : '' }}>
                {{ $placeholder }}
            </option>
        @endif

        @if (!empty($options))
            @foreach ($options as $val => $text)
                <option value="{{ $val }}" {{ (string)$selectedValue === (string)$val ? 'selected' : '' }}>
                    {{ $text }}
                </option>
            @endforeach
        @else
            {{ $slot }}
        @endif
    </select>

    @if ($help && !$hasError)
        <p class="mt-1 text-xs text-gray-500">{{ $help }}</p>
    @endif

    <x-forms.error :name="$name" />
</div>

