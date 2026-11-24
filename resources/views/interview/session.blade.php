@extends('layouts.interviewer', ['title' => 'Penilaian: ' . ($session->registration->applicant?->full_name ?? 'Calon Siswa')])

@section('content')
<div class="space-y-5 pb-12">
    {{-- Header Bar --}}
    <div class="flex items-center justify-between gap-3">
        <a
            href="{{ route('interview.index') }}"
            class="inline-flex items-center gap-1.5 px-3 py-2 rounded-xl text-xs font-semibold text-gray-600 hover:text-gray-900 hover:bg-gray-100 transition min-h-11"
        >
            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18" />
            </svg>
            Kembali
        </a>

        <form
            method="POST"
            action="{{ route('interview.session.cancel', $session) }}"
            onsubmit="return confirm('Apakah Anda yakin ingin membatalkan sesi penilaian ini? Data yang belum disimpan akan terhapus.');"
        >
            @csrf
            <button
                type="submit"
                class="inline-flex items-center gap-1 px-3 py-2 rounded-xl text-xs font-semibold text-red-600 hover:bg-red-50 transition min-h-11"
            >
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                </svg>
                Batalkan Sesi
            </button>
        </form>
    </div>

    {{-- Candidate Profile Summary Card --}}
    <div class="p-5 rounded-2xl bg-white border border-gray-200 shadow-sm">
        <div class="flex items-start gap-4">
            {{-- Pas Foto 3x4 --}}
            <div class="shrink-0">
                <img
                    src="{{ route('interview.session.photo', $session) }}"
                    alt="Pas Foto Calon Siswa"
                    class="w-20 h-28 object-cover rounded-xl border border-gray-200 bg-gray-100 shadow-xs"
                    loading="lazy"
                >
            </div>

            {{-- Candidate Details --}}
            <div class="flex-1 min-w-0">
                <div class="flex items-center gap-2 mb-1 flex-wrap">
                    <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-bold font-mono bg-emerald-100 text-emerald-800">
                        No. {{ $session->registration->registration_number ?? '-' }}
                    </span>
                    <span class="inline-flex items-center px-2 py-0.5 rounded-full text-[11px] font-medium bg-gray-100 text-gray-700">
                        {{ $session->registration->admissionPeriodProgram?->program?->name ?? 'Reguler' }}
                    </span>
                </div>

                <h1 class="text-base sm:text-lg font-bold text-gray-900 truncate">
                    {{ $session->registration->applicant?->full_name ?? 'Nama Calon Siswa' }}
                </h1>

                <div class="mt-2 text-xs text-gray-500 space-y-0.5">
                    @if ($session->registration->interviewBatch)
                        <div>Jadwal: <span class="font-medium text-gray-700">{{ $session->registration->interviewBatch->title }}</span></div>
                    @endif
                    <div>Tahap: <span class="font-semibold text-emerald-700">{{ $session->stage->title }}</span></div>
                </div>
            </div>
        </div>
    </div>

    {{-- Scoring Form --}}
    <form id="scoring-form" method="POST" action="{{ route('interview.session.submit', $session) }}" class="space-y-4">
        @csrf

        {{-- Form Card --}}
        <div class="p-5 rounded-2xl bg-white border border-gray-200 shadow-sm space-y-6">
            <div class="border-b border-gray-100 pb-3">
                <h2 class="text-base font-bold text-gray-900">Formulir Penilaian</h2>
                <p class="text-xs text-gray-500">Beri nilai antara 0 hingga 100 untuk setiap kriteria evaluasi di bawah ini.</p>
            </div>

            @if ($errors->any())
                <div class="p-3 rounded-xl bg-red-50 border border-red-200 text-xs text-red-800 space-y-1">
                    <div class="font-bold">Mohon perbaiki kesalahan berikut:</div>
                    <ul class="list-disc list-inside">
                        @foreach ($errors->all() as $err)
                            <li>{{ $err }}</li>
                        @endforeach
                    </ul>
                </div>
            @endif

            {{-- Criteria List --}}
            <div class="space-y-5">
                @forelse ($session->stage->criteria as $index => $criterion)
                    <div class="p-4 rounded-xl bg-gray-50/75 border border-gray-200/80 space-y-3">
                        <div>
                            <div class="flex items-center justify-between gap-2">
                                <label for="score-{{ $criterion->id }}" class="text-sm font-bold text-gray-900">
                                    {{ $index + 1 }}. {{ $criterion->title }}
                                </label>
                                <span class="text-[11px] font-semibold text-gray-400">0 - 100</span>
                            </div>
                            @if ($criterion->description)
                                <p class="text-xs text-gray-500 mt-0.5 leading-relaxed">
                                    {{ $criterion->description }}
                                </p>
                            @endif
                        </div>

                        {{-- Number Input with Touch Controls --}}
                        <div class="flex items-center gap-2">
                            <button
                                type="button"
                                onclick="adjustScore({{ $criterion->id }}, -5)"
                                class="shrink-0 w-11 h-11 rounded-xl bg-white border border-gray-300 text-gray-700 font-bold text-lg hover:bg-gray-100 active:bg-gray-200 transition select-none flex items-center justify-center shadow-2xs"
                                aria-label="Kurangi 5"
                            >
                                -5
                            </button>
                            <button
                                type="button"
                                onclick="adjustScore({{ $criterion->id }}, -1)"
                                class="shrink-0 w-11 h-11 rounded-xl bg-white border border-gray-300 text-gray-700 font-bold text-lg hover:bg-gray-100 active:bg-gray-200 transition select-none flex items-center justify-center shadow-2xs"
                                aria-label="Kurangi 1"
                            >
                                -1
                            </button>

                            <input
                                type="number"
                                id="score-{{ $criterion->id }}"
                                name="scores[{{ $criterion->id }}]"
                                min="0"
                                max="100"
                                step="1"
                                inputmode="numeric"
                                value="{{ old('scores.' . $criterion->id, '') }}"
                                placeholder="0–100"
                                required
                                class="flex-1 px-3 py-2.5 rounded-xl border @error('scores.' . $criterion->id) border-red-400 bg-red-50 @else border-gray-300 bg-white @enderror text-center font-mono text-xl font-bold text-gray-900 focus:outline-hidden focus:ring-2 focus:ring-emerald-500 focus:border-emerald-500 transition min-h-11 shadow-2xs"
                            >

                            <button
                                type="button"
                                onclick="adjustScore({{ $criterion->id }}, 1)"
                                class="shrink-0 w-11 h-11 rounded-xl bg-white border border-gray-300 text-gray-700 font-bold text-lg hover:bg-gray-100 active:bg-gray-200 transition select-none flex items-center justify-center shadow-2xs"
                                aria-label="Tambah 1"
                            >
                                +1
                            </button>
                            <button
                                type="button"
                                onclick="adjustScore({{ $criterion->id }}, 5)"
                                class="shrink-0 w-11 h-11 rounded-xl bg-white border border-gray-300 text-gray-700 font-bold text-lg hover:bg-gray-100 active:bg-gray-200 transition select-none flex items-center justify-center shadow-2xs"
                                aria-label="Tambah 5"
                            >
                                +5
                            </button>
                        </div>
                    </div>
                @empty
                    <p class="text-sm text-gray-500 italic py-4 text-center">
                        Belum ada kriteria penilaian aktif untuk tahap ini.
                    </p>
                @endforelse
            </div>

            {{-- Notes Input --}}
            <div class="pt-2 border-t border-gray-100">
                <label for="notes" class="block text-xs font-bold text-gray-700 uppercase tracking-wider mb-1.5">
                    Catatan Penguji <span class="text-gray-400 font-normal lowercase">(opsional)</span>
                </label>
                <textarea
                    id="notes"
                    name="notes"
                    rows="3"
                    maxlength="1000"
                    placeholder="Tuliskan catatan khusus atau rekomendasi terkait calon siswa..."
                    class="w-full px-3.5 py-2.5 rounded-xl border border-gray-300 text-sm text-gray-900 focus:outline-hidden focus:ring-2 focus:ring-emerald-500 focus:border-emerald-500 transition"
                >{{ old('notes', $session->notes) }}</textarea>
            </div>
        </div>

        {{-- Submit Button (Triggers Modal) --}}
        <div class="sticky bottom-4 z-20">
            <button
                type="button"
                id="open-confirm-modal-btn"
                class="w-full inline-flex items-center justify-center gap-2 px-5 py-4 rounded-2xl bg-emerald-600 hover:bg-emerald-700 text-white font-bold text-base shadow-lg hover:shadow-xl transition active:scale-[0.99] min-h-12"
            >
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m5.618-4.016A11.955 11.955 0 0112 2.944a11.955 11.955 0 01-8.618 3.04A12.02 12.02 0 003 9c0 5.591 3.824 10.29 9 11.622 5.176-1.332 9-6.03 9-11.622 0-1.042-.133-2.052-.382-3.016z" />
                </svg>
                <span>Simpan & Kunci Nilai</span>
            </button>
        </div>
    </form>

    {{-- Confirmation Modal --}}
    <div
        id="confirm-modal"
        class="hidden fixed inset-0 z-50 overflow-y-auto bg-gray-900/60 backdrop-blur-xs flex items-center justify-center p-4"
        role="dialog"
        aria-modal="true"
        aria-labelledby="modal-headline"
    >
        <div class="bg-white rounded-3xl max-w-sm w-full p-6 text-center shadow-2xl space-y-4 animate-in fade-in zoom-in-95 duration-150">
            <div class="w-14 h-14 mx-auto rounded-full bg-amber-100 text-amber-600 flex items-center justify-center">
                <svg class="w-8 h-8" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z" />
                </svg>
            </div>

            <div>
                <h3 id="modal-headline" class="text-lg font-bold text-gray-900">
                    Kunci & Simpan Nilai?
                </h3>
                <p class="text-xs text-gray-600 mt-2 leading-relaxed">
                    Apakah Anda yakin nilai yang diinput sudah sesuai? Setelah disimpan, nilai ini akan <strong>terkunci mutlak</strong> dan tidak dapat diubah kembali oleh pewawancara.
                </p>
            </div>

            <div class="space-y-2 pt-2">
                <button
                    type="button"
                    id="confirm-submit-btn"
                    class="w-full inline-flex items-center justify-center gap-2 px-4 py-3 rounded-xl bg-emerald-600 hover:bg-emerald-700 text-white font-bold text-sm shadow-md transition min-h-11"
                >
                    Ya, Simpan & Kunci Nilai
                </button>
                <button
                    type="button"
                    id="close-confirm-modal-btn"
                    class="w-full inline-flex items-center justify-center px-4 py-2.5 rounded-xl bg-gray-100 hover:bg-gray-200 text-gray-700 font-semibold text-sm transition min-h-11"
                >
                    Periksa Kembali
                </button>
            </div>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script>
    function adjustScore(criterionId, delta) {
        const input = document.getElementById('score-' + criterionId);
        if (!input) return;

        let currentVal = parseInt(input.value, 10);
        if (isNaN(currentVal)) {
            currentVal = 0;
        }

        let newVal = currentVal + delta;
        if (newVal < 0) newVal = 0;
        if (newVal > 100) newVal = 100;

        input.value = newVal;
    }

    document.addEventListener('DOMContentLoaded', function () {
        const openBtn = document.getElementById('open-confirm-modal-btn');
        const closeBtn = document.getElementById('close-confirm-modal-btn');
        const confirmBtn = document.getElementById('confirm-submit-btn');
        const modal = document.getElementById('confirm-modal');
        const form = document.getElementById('scoring-form');

        if (openBtn && modal && closeBtn && confirmBtn && form) {
            openBtn.addEventListener('click', function () {
                // Client-side quick check
                const inputs = form.querySelectorAll('input[type="number"]');
                let allFilled = true;
                inputs.forEach(function (inp) {
                    if (inp.value === '' || isNaN(parseInt(inp.value, 10))) {
                        allFilled = false;
                    }
                });

                if (!allFilled) {
                    alert('Harap isi semua poin nilai kriteria penilaian sebelum mengunci nilai.');
                    return;
                }

                modal.classList.remove('hidden');
            });

            closeBtn.addEventListener('click', function () {
                modal.classList.add('hidden');
            });

            confirmBtn.addEventListener('click', function () {
                confirmBtn.disabled = true;
                confirmBtn.textContent = 'Menyimpan...';
                form.submit();
            });
        }
    });
</script>
@endpush
