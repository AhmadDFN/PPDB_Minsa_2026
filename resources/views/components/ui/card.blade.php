@props([
    'title' => null,
    'subtitle' => null,
    'footer' => null,
])

<div {{ $attributes->merge(['class' => 'card overflow-hidden']) }}>
    @if ($title || isset($header))
        <div class="card-header flex items-center justify-between">
            @if (isset($header))
                {{ $header }}
            @else
                <div>
                    <h3 class="text-base font-semibold text-gray-900">{{ $title }}</h3>
                    @if ($subtitle)
                        <p class="text-xs text-gray-500 mt-0.5">{{ $subtitle }}</p>
                    @endif
                </div>
            @endif
        </div>
    @endif

    <div class="card-body">
        {{ $slot }}
    </div>

    @if ($footer || isset($footerSlot))
        <div class="card-footer">
            {{ $footer ?? $footerSlot }}
        </div>
    @endif
</div>

