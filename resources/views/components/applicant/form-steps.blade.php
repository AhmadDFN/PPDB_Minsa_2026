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
        6 => ['label' => 'Unggah Dokumen', 'route' => 'applicant.documents.index'],
        7 => ['label' => 'Tinjau & Kirim', 'route' => 'applicant.registration.review'],
    ];
@endphp

<div class="mb-8">
    <!-- Desktop Step Indicator -->
    <nav aria-label="Progress" class="hidden sm:block">
        <ol class="flex items-center justify-between border border-slate-800 rounded-2xl bg-slate-900/90 p-2 shadow-lg backdrop-blur-md">
            @foreach ($steps as $stepNum => $step)
                @php
                    $isCurrent = $currentStep === $stepNum;
                    $isPast = $currentStep > $stepNum;
                @endphp
                <li class="relative flex-1 {{ !$loop->last ? 'pr-2' : '' }}">
                    <a
                        href="{{ route($step['route']) }}"
                        class="group flex items-center gap-2.5 p-2 rounded-xl transition-all duration-150 {{ $isCurrent ? 'bg-emerald-500/15 text-emerald-300 font-bold border border-emerald-500/30 shadow-xs' : ($isPast ? 'text-slate-300 hover:bg-slate-800/80 hover:text-white' : 'text-slate-500 hover:text-slate-300') }}"
                    >
                        <span class="flex h-7 w-7 shrink-0 items-center justify-center rounded-lg text-xs font-bold transition-all {{ $isCurrent ? 'bg-emerald-500 text-white shadow-md shadow-emerald-500/30 ring-2 ring-emerald-400/40' : ($isPast ? 'bg-emerald-950 text-emerald-400 border border-emerald-500/30' : 'bg-slate-800 text-slate-500 border border-slate-700') }}">
                            @if ($isPast)
                                <svg class="h-3.5 w-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
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
    <div class="sm:hidden bg-slate-900 border border-slate-800 rounded-2xl p-4 shadow-lg">
        <div class="flex items-center justify-between mb-2.5">
            <span class="text-[11px] font-bold text-emerald-400 uppercase tracking-wider flex items-center gap-1.5">
                <span class="w-1.5 h-1.5 rounded-full bg-emerald-400 animate-pulse"></span>
                Langkah {{ $currentStep }} dari {{ count($steps) }}
            </span>
            <span class="text-xs font-bold text-white">
                {{ $steps[$currentStep]['label'] ?? '' }}
            </span>
        </div>
        <div class="w-full bg-slate-800 rounded-full h-2 overflow-hidden">
            <div class="bg-gradient-to-r from-emerald-500 to-teal-400 h-2 rounded-full transition-all duration-300" style="width: {{ ($currentStep / count($steps)) * 100 }}%"></div>
        </div>
    </div>
</div>
