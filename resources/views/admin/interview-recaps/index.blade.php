<x-layouts.admin title="Rekap Nilai Wawancara">
    <div class="space-y-6">
        {{-- Page Header --}}
        <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
            <div>
                <h1 class="text-2xl font-bold text-gray-900 tracking-tight">Rekap Nilai Wawancara</h1>
                <p class="text-sm text-gray-500 mt-1">
                    Hasil penilaian terpisah per tahap wawancara untuk pendaftar terverifikasi
                    @if($period)
                        ({{ $period->name }} — {{ $period->academic_year }})
                    @endif
                </p>
            </div>

            @if($period)
                <a
                    href="{{ route('admin.interview-recaps.export', request()->query()) }}"
                    class="inline-flex items-center justify-center gap-2 px-4 py-2.5 rounded-xl bg-emerald-700 hover:bg-emerald-800 text-white text-sm font-semibold shadow-xs transition min-h-11"
                >
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 10v6m0 0l-3-3m3 3l3-3m2 8H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" />
                    </svg>
                    <span>Ekspor CSV</span>
                </a>
            @endif
        </div>

        {{-- Flash Messages --}}
        @if(session('success'))
            <div class="p-4 rounded-xl bg-emerald-50 border border-emerald-200 text-emerald-900 text-sm font-medium">
                {{ session('success') }}
            </div>
        @endif

        @if(session('warning'))
            <div class="p-4 rounded-xl bg-amber-50 border border-amber-200 text-amber-900 text-sm font-medium">
                {{ session('warning') }}
            </div>
        @endif

        @if($errors->any())
            <div class="p-4 rounded-xl bg-red-50 border border-red-200 text-red-900 text-sm space-y-1">
                <div class="font-bold">Gagal memproses permintaan:</div>
                <ul class="list-disc list-inside text-xs">
                    @foreach($errors->all() as $error)
                        <li>{{ $error }}</li>
                    @endforeach
                </ul>
            </div>
        @endif

        {{-- Summary Cards --}}
        <div class="grid grid-cols-1 sm:grid-cols-3 gap-4">
            <div class="bg-white p-4 rounded-2xl border border-gray-200 shadow-2xs">
                <div class="text-xs font-semibold text-gray-500 uppercase tracking-wider">Pendaftar Terverifikasi</div>
                <div class="text-2xl font-bold text-gray-900 mt-1 tabular-nums">{{ $summary['total_verified'] }}</div>
            </div>
            <div class="bg-white p-4 rounded-2xl border border-emerald-200 shadow-2xs">
                <div class="text-xs font-semibold text-emerald-700 uppercase tracking-wider">Selesai Seluruh Tahap</div>
                <div class="text-2xl font-bold text-emerald-700 mt-1 tabular-nums">{{ $summary['total_completed'] }}</div>
            </div>
            <div class="bg-white p-4 rounded-2xl border border-amber-200 shadow-2xs">
                <div class="text-xs font-semibold text-amber-700 uppercase tracking-wider">Belum Lengkap</div>
                <div class="text-2xl font-bold text-amber-700 mt-1 tabular-nums">{{ $summary['total_incomplete'] }}</div>
            </div>
        </div>

        {{-- Filter Panel --}}
        <form method="GET" action="{{ route('admin.interview-recaps.index') }}" class="bg-white p-4 rounded-2xl border border-gray-200 shadow-2xs">
            <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-5 gap-3">
                <div>
                    <label for="search" class="block text-xs font-semibold text-gray-600 mb-1">Cari Nama / No. Urut</label>
                    <input
                        type="text"
                        id="search"
                        name="search"
                        value="{{ $filters['search'] ?? '' }}"
                        placeholder="Contoh: 270001 atau Ahmad"
                        class="w-full px-3 py-2 rounded-xl border border-gray-300 text-sm focus:outline-hidden focus:ring-2 focus:ring-emerald-600"
                    >
                </div>

                <div>
                    <label for="batch_id" class="block text-xs font-semibold text-gray-600 mb-1">Jadwal / Batch</label>
                    <select id="batch_id" name="batch_id" class="w-full px-3 py-2 rounded-xl border border-gray-300 text-sm focus:outline-hidden focus:ring-2 focus:ring-emerald-600">
                        <option value="">Semua Jadwal</option>
                        @foreach($batches as $batch)
                            <option value="{{ $batch->id }}" @selected((int) ($filters['batch_id'] ?? 0) === $batch->id)>
                                {{ $batch->title }} ({{ $batch->sequence_start }}–{{ $batch->sequence_end }})
                            </option>
                        @endforeach
                    </select>
                </div>

                <div>
                    <label for="program_id" class="block text-xs font-semibold text-gray-600 mb-1">Program</label>
                    <select id="program_id" name="program_id" class="w-full px-3 py-2 rounded-xl border border-gray-300 text-sm focus:outline-hidden focus:ring-2 focus:ring-emerald-600">
                        <option value="">Semua Program</option>
                        @foreach($programs as $program)
                            <option value="{{ $program->id }}" @selected((int) ($filters['program_id'] ?? 0) === $program->id)>
                                {{ $program->name }}
                            </option>
                        @endforeach
                    </select>
                </div>

                <div>
                    <label for="completion_status" class="block text-xs font-semibold text-gray-600 mb-1">Status Kelengkapan</label>
                    <select id="completion_status" name="completion_status" class="w-full px-3 py-2 rounded-xl border border-gray-300 text-sm focus:outline-hidden focus:ring-2 focus:ring-emerald-600">
                        <option value="all" @selected(($filters['completion_status'] ?? 'all') === 'all')>Semua Status</option>
                        <option value="completed" @selected(($filters['completion_status'] ?? '') === 'completed')>Selesai Seluruh Tahap</option>
                        <option value="incomplete" @selected(($filters['completion_status'] ?? '') === 'incomplete')>Belum Lengkap</option>
                    </select>
                </div>

                <div class="flex items-end gap-2">
                    <button
                        type="submit"
                        class="flex-1 px-4 py-2 rounded-xl bg-emerald-700 hover:bg-emerald-800 text-white text-sm font-semibold transition min-h-[38px]"
                    >
                        Terapkan
                    </button>
                    <a
                        href="{{ route('admin.interview-recaps.index') }}"
                        class="px-3 py-2 rounded-xl border border-gray-300 text-gray-600 hover:bg-gray-50 text-sm font-medium transition min-h-[38px] inline-flex items-center"
                    >
                        Reset
                    </a>
                </div>
            </div>
        </form>

        {{-- Recap Table --}}
        <div class="bg-white rounded-2xl border border-gray-200 shadow-2xs overflow-hidden">
            @if(!$period)
                <div class="p-8 text-center text-sm text-gray-500">
                    Belum ada periode pendaftaran yang aktif.
                </div>
            @elseif($registrations->isEmpty())
                <div class="p-10 text-center space-y-2">
                    <div class="text-sm font-semibold text-gray-800">Belum ada data pendaftar terverifikasi yang sesuai filter.</div>
                    <p class="text-xs text-gray-500">Pastikan berkas pendaftar sudah disahkan pada menu Verifikasi Berkas atau ubah filter pencarian.</p>
                </div>
            @else
                <div class="overflow-x-auto">
                    <table class="min-w-full divide-y divide-gray-200 text-sm">
                        <thead class="bg-gray-50">
                            <tr>
                                <th class="px-4 py-3 text-left text-xs font-bold text-gray-600 uppercase tracking-wider">Peserta Didik</th>
                                @foreach($stages as $stage)
                                    <th class="px-4 py-3 text-left text-xs font-bold text-gray-600 uppercase tracking-wider min-w-[230px]">
                                        <div>Tahap {{ $stage->stage_order }}: {{ $stage->title }}</div>
                                        <div class="text-[10px] font-normal text-gray-400 normal-case">
                                            {{ $stage->program_scope === 'UNGGULAN' ? 'Khusus Unggulan' : 'Semua Program' }}
                                        </div>
                                    </th>
                                @endforeach
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-gray-100 bg-white">
                            @foreach($registrations as $registration)
                                @php
                                    $programCode = strtoupper((string) ($registration->admissionPeriodProgram?->program?->code ?? ''));
                                    $programName = $registration->admissionPeriodProgram?->program?->name ?? 'Reguler';
                                    $isUnggulan = str_contains($programCode, 'UNGGULAN');
                                    $sessionsByStage = $registration->interviewSessions->keyBy('interview_stage_id');
                                    $studentName = $registration->applicant?->full_name ?? 'Calon Siswa';
                                @endphp
                                <tr class="hover:bg-gray-50/60 align-top">
                                    {{-- Identity Column --}}
                                    <td class="px-4 py-4 whitespace-nowrap">
                                        <div class="flex flex-col gap-1">
                                            <span class="inline-flex w-fit items-center px-2 py-0.5 rounded-md text-xs font-bold font-mono tabular-nums bg-emerald-50 text-emerald-800 border border-emerald-200">
                                                {{ $registration->registration_number ?? '-' }}
                                            </span>
                                            <span class="font-bold text-gray-900">{{ $studentName }}</span>
                                            <div class="flex items-center gap-1.5 flex-wrap">
                                                <span class="inline-flex items-center px-2 py-0.5 rounded-full text-[11px] font-semibold {{ $isUnggulan ? 'bg-amber-100 text-amber-800' : 'bg-gray-100 text-gray-700' }}">
                                                    {{ $programName }}
                                                </span>
                                                <span class="text-[11px] text-gray-500">
                                                    {{ $registration->interviewBatch?->title ?? 'Tanpa Batch' }}
                                                </span>
                                            </div>
                                        </div>
                                    </td>

                                    {{-- Per Stage Columns --}}
                                    @foreach($stages as $stage)
                                        @php
                                            $session = $sessionsByStage->get($stage->id);
                                            $notRequired = $stage->program_scope === 'UNGGULAN' && !$isUnggulan;
                                            $scoresByCriterion = $session ? $session->scores->keyBy('interview_criterion_id') : collect();
                                        @endphp
                                        <td class="px-4 py-4">
                                            @if($notRequired)
                                                <span class="inline-flex items-center px-2.5 py-1 rounded-lg text-xs font-medium bg-gray-100 text-gray-500">
                                                    Tidak Wajib (Reguler)
                                                </span>
                                            @elseif(!$session)
                                                <span class="inline-flex items-center px-2.5 py-1 rounded-lg text-xs font-medium bg-gray-100 text-gray-500">
                                                    Belum Dinilai
                                                </span>
                                            @elseif($session->status === 'IN_PROGRESS')
                                                <div class="space-y-1">
                                                    <span class="inline-flex items-center px-2.5 py-1 rounded-lg text-xs font-semibold bg-cyan-50 text-cyan-800 border border-cyan-200">
                                                        Sedang Dinilai
                                                    </span>
                                                    <div class="text-[11px] text-gray-500">
                                                        Penguji: {{ $session->interviewer?->name ?? '-' }}
                                                    </div>
                                                </div>
                                            @else
                                                <div class="space-y-2">
                                                    {{-- Total & Average --}}
                                                    <div class="flex items-center justify-between gap-2 bg-emerald-50/70 border border-emerald-200 rounded-xl px-3 py-2">
                                                        <div>
                                                            <div class="text-[10px] uppercase tracking-wider text-emerald-700 font-semibold">Jumlah</div>
                                                            <div class="text-sm font-bold text-gray-900 tabular-nums">{{ $session->total_score ?? 0 }}</div>
                                                        </div>
                                                        <div class="text-right">
                                                            <div class="text-[10px] uppercase tracking-wider text-emerald-700 font-semibold">Rata-rata</div>
                                                            <div class="text-base font-extrabold text-emerald-800 tabular-nums">
                                                                {{ number_format((float) ($session->average_score ?? 0), 2) }}
                                                            </div>
                                                        </div>
                                                    </div>

                                                    {{-- Breakdown Criteria --}}
                                                    <ul class="space-y-0.5 text-xs text-gray-600">
                                                        @foreach($stage->criteria as $criterion)
                                                            @php
                                                                $cScore = $scoresByCriterion->get($criterion->id)?->score;
                                                            @endphp
                                                            <li class="flex items-center justify-between gap-2">
                                                                <span class="truncate max-w-[160px]" title="{{ $criterion->title }}">{{ $criterion->title }}</span>
                                                                <span class="font-semibold text-gray-900 tabular-nums">{{ $cScore ?? '-' }}</span>
                                                            </li>
                                                        @endforeach
                                                    </ul>

                                                    {{-- Metadata & Override Button --}}
                                                    <div class="pt-1 border-t border-gray-100 flex items-center justify-between gap-2 text-[11px] text-gray-500">
                                                        <div>
                                                            <div>Penguji: <span class="font-medium text-gray-700">{{ $session->interviewer?->name ?? '-' }}</span></div>
                                                            @if($session->last_edited_by)
                                                                <div class="text-amber-700 font-medium" title="{{ $session->last_edit_reason }}">
                                                                    Dikoreksi oleh {{ $session->lastEditor?->name ?? 'Admin' }}
                                                                </div>
                                                            @endif
                                                        </div>

                                                        @can('override_interview_scores')
                                                            @php
                                                                $modalPayload = [
                                                                    'actionUrl' => route('admin.interview-recaps.override', $session),
                                                                    'studentName' => $studentName,
                                                                    'registrationNumber' => $registration->registration_number,
                                                                    'stageTitle' => "Tahap {$stage->stage_order}: {$stage->title}",
                                                                    'notes' => $session->notes ?? '',
                                                                    'criteria' => $stage->criteria->map(fn ($c) => [
                                                                        'id' => $c->id,
                                                                        'title' => $c->title,
                                                                        'score' => $scoresByCriterion->get($c->id)?->score ?? 0,
                                                                    ])->values(),
                                                                ];
                                                            @endphp
                                                            <button
                                                                type="button"
                                                                onclick='openOverrideModal(@json($modalPayload))'
                                                                class="shrink-0 px-2.5 py-1 rounded-lg border border-amber-300 bg-amber-50 hover:bg-amber-100 text-amber-900 font-semibold text-[11px] transition"
                                                            >
                                                                Koreksi Nilai
                                                            </button>
                                                        @endcan
                                                    </div>
                                                </div>
                                            @endif
                                        </td>
                                    @endforeach
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>

                @if($registrations->hasPages())
                    <div class="px-4 py-3 border-t border-gray-200 bg-gray-50">
                        {{ $registrations->links() }}
                    </div>
                @endif
            @endif
        </div>
    </div>

    {{-- Modal Koreksi Nilai Admin --}}
    @can('override_interview_scores')
        <div
            id="override-modal"
            class="hidden fixed inset-0 z-50 overflow-y-auto bg-gray-900/60 backdrop-blur-xs flex items-center justify-center p-4"
            role="dialog"
            aria-modal="true"
            aria-labelledby="override-modal-title"
        >
            <div class="bg-white rounded-2xl max-w-lg w-full p-6 shadow-2xl space-y-5">
                <div class="flex items-start justify-between gap-3 border-b border-gray-100 pb-3">
                    <div>
                        <h3 id="override-modal-title" class="text-lg font-bold text-gray-900">Koreksi Nilai Wawancara</h3>
                        <p id="override-modal-subtitle" class="text-xs text-gray-500 mt-0.5"></p>
                    </div>
                    <button
                        type="button"
                        onclick="closeOverrideModal()"
                        class="p-1.5 rounded-lg text-gray-400 hover:text-gray-600 hover:bg-gray-100"
                        aria-label="Tutup modal"
                    >
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                        </svg>
                    </button>
                </div>

                <form id="override-form" method="POST" action="" class="space-y-4">
                    @csrf
                    @method('PUT')

                    <div id="override-criteria-container" class="space-y-3"></div>

                    <div>
                        <label for="override-notes" class="block text-xs font-semibold text-gray-700 mb-1">
                            Catatan Penilaian <span class="text-gray-400 font-normal">(opsional)</span>
                        </label>
                        <textarea
                            id="override-notes"
                            name="notes"
                            rows="2"
                            maxlength="1000"
                            class="w-full px-3 py-2 rounded-xl border border-gray-300 text-sm focus:outline-hidden focus:ring-2 focus:ring-emerald-600"
                        ></textarea>
                    </div>

                    <div>
                        <label for="override-reason" class="block text-xs font-bold text-gray-800 mb-1">
                            Alasan Koreksi <span class="text-red-600">*</span>
                            <span class="text-gray-400 font-normal">(wajib diisi minimal 10 karakter, tercatat di Audit Log)</span>
                        </label>
                        <textarea
                            id="override-reason"
                            name="reason"
                            rows="2"
                            minlength="10"
                            maxlength="500"
                            required
                            placeholder="Contoh: Salah ketik skor oleh penguji dari 85 menjadi 58 sesuai berita acara fisik."
                            class="w-full px-3 py-2 rounded-xl border border-gray-300 text-sm focus:outline-hidden focus:ring-2 focus:ring-amber-500"
                        ></textarea>
                    </div>

                    <div class="flex items-center justify-end gap-2 pt-3 border-t border-gray-100">
                        <button
                            type="button"
                            onclick="closeOverrideModal()"
                            class="px-4 py-2.5 rounded-xl border border-gray-300 text-gray-700 hover:bg-gray-50 text-sm font-semibold min-h-11"
                        >
                            Batal
                        </button>
                        <button
                            type="submit"
                            id="override-submit-btn"
                            class="px-5 py-2.5 rounded-xl bg-amber-600 hover:bg-amber-700 text-white text-sm font-bold shadow-xs transition min-h-11"
                        >
                            Simpan Koreksi & Catat Audit
                        </button>
                    </div>
                </form>
            </div>
        </div>

        @push('scripts')
        <script>
            function openOverrideModal(payload) {
                const modal = document.getElementById('override-modal');
                const form = document.getElementById('override-form');
                const subtitle = document.getElementById('override-modal-subtitle');
                const container = document.getElementById('override-criteria-container');
                const notesEl = document.getElementById('override-notes');
                const reasonEl = document.getElementById('override-reason');

                form.action = payload.actionUrl;
                subtitle.textContent = `${payload.studentName} (${payload.registrationNumber}) — ${payload.stageTitle}`;
                notesEl.value = payload.notes || '';
                reasonEl.value = '';

                container.innerHTML = '';
                payload.criteria.forEach(function (criterion) {
                    const row = document.createElement('div');
                    row.className = 'flex items-center justify-between gap-3 p-3 rounded-xl bg-gray-50 border border-gray-200';

                    const label = document.createElement('label');
                    label.htmlFor = 'override-score-' + criterion.id;
                    label.className = 'text-sm font-semibold text-gray-800 flex-1';
                    label.textContent = criterion.title;

                    const input = document.createElement('input');
                    input.type = 'number';
                    input.id = 'override-score-' + criterion.id;
                    input.name = `scores[${criterion.id}]`;
                    input.min = '0';
                    input.max = '100';
                    input.step = '1';
                    input.inputMode = 'numeric';
                    input.required = true;
                    input.value = criterion.score;
                    input.className = 'w-24 px-3 py-2 rounded-xl border border-gray-300 text-center font-mono font-bold text-gray-900 tabular-nums focus:outline-hidden focus:ring-2 focus:ring-emerald-600 min-h-11';

                    row.appendChild(label);
                    row.appendChild(input);
                    container.appendChild(row);
                });

                modal.classList.remove('hidden');
            }

            function closeOverrideModal() {
                document.getElementById('override-modal').classList.add('hidden');
            }

            document.getElementById('override-form')?.addEventListener('submit', function () {
                const btn = document.getElementById('override-submit-btn');
                if (btn) {
                    btn.disabled = true;
                    btn.textContent = 'Menyimpan...';
                }
            });
        </script>
        @endpush
    @endcan
</x-layouts.admin>
