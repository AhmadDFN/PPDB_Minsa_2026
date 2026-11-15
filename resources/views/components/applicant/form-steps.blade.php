@props([
    'currentStep' => 1,
])

@php
    $steps = [
        1 => ['label' => 'Biodata Siswa', 'route' => 'applicant.biodata.edit'],
        2 => ['label' => 'Alamat Domisili', 'route' => 'applicant.address.edit'],
        3 => ['label' => 'Orang Tua / Wali', 'route' => 'applicant.parents.edit'],
        4 => ['label' => 'Pendidikan Asal', 'route' => 'applicant.education.edit'],
        5 => ['label' => 'Pilihan Program', 'route' => 'applicant.program.edit'],
    ];
@endphp

<div class="mb-8">
    <!-- Desktop Step Indicator -->
    <nav aria-label="Progress" class="hidden sm:block">
        <ol class="flex items-center justify-between border border-neutral-200/80 rounded-2xl bg-white p-2 shadow-xs">
            @foreach ($steps as $stepNum => $step)
                @php
                    $isCurrent = $currentStep === $stepNum;
                    $isPast = $currentStep > $stepNum;
                @endphp
                <li class="relative flex-1 {{ !$loop->last ? 'pr-4' : '' }}">
                    <a
                        href="{{ route($step['route']) }}"
                        class="group flex items-center gap-3 p-2.5 rounded-xl transition {{ $isCurrent ? 'bg-primary-50 text-primary-800 font-bold' : ($isPast ? 'text-neutral-700 hover:bg-neutral-50' : 'text-neutral-400 hover:text-neutral-600') }}"
                    >
                        <span class="flex h-8 w-8 shrink-0 items-center justify-center rounded-lg text-xs font-bold transition {{ $isCurrent ? 'bg-primary-600 text-white shadow-xs' : ($isPast ? 'bg-secondary-600 text-white' : 'bg-neutral-100 text-neutral-500 border border-neutral-200') }}">
                            @if ($isPast)
                                <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M5 13l4 4L19 7" />
                                </svg>
                            @else
                                {{ $stepNum }}
                            @endif
                        </span>
                        <span class="text-xs font-semibold truncate">{{ $step['label'] }}</span>
                    </a>
                </li>
            @endforeach
        </ol>
    </nav>

    <!-- Mobile Step Indicator -->
    <div class="sm:hidden bg-white border border-neutral-200/80 rounded-xl p-4 shadow-xs">
        <div class="flex items-center justify-between mb-2">
            <span class="text-xs font-bold text-primary-700 uppercase tracking-wider">
                Langkah {{ $currentStep }} dari {{ count($steps) }}
            </span>
            <span class="text-xs font-bold text-neutral-800">
                {{ $steps[$currentStep]['label'] ?? '' }}
            </span>
        </div>
        <div class="w-full bg-neutral-100 rounded-full h-2">
            <div class="bg-primary-600 h-2 rounded-full transition-all duration-300" style="width: {{ ($currentStep / count($steps)) * 100 }}%"></div>
        </div>
    </div>
</div>
