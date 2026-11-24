<x-layouts.admin title="Tahap & Poin Wawancara">
    <x-slot:header>
        <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
            <div>
                <div class="flex items-center gap-2">
                    <h1 class="text-2xl font-bold text-gray-900">Tahap & Poin Wawancara Pemetaan</h1>
                    @if ($period)
                        <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-semibold bg-primary-50 text-primary-800 border border-primary-200">
                            TP {{ $period->academic_year }}
                        </span>
                    @endif
                </div>
                <p class="text-sm text-gray-500 mt-1">Konfigurasi 3 tahap wawancara sequential dan rubrik poin penilaian (customizable) untuk pewawancara.</p>
            </div>
        </div>
    </x-slot:header>

    <div class="space-y-6">
        {{-- Flash Messages --}}
        @if (session('success'))
            <div class="rounded-xl bg-emerald-50 border border-emerald-200 p-4 text-sm text-emerald-800 flex items-center justify-between">
                <div class="flex items-center gap-2">
                    <svg class="w-5 h-5 text-emerald-600 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/></svg>
                    <span>{{ session('success') }}</span>
                </div>
            </div>
        @endif
        @if (session('error'))
            <div class="rounded-xl bg-red-50 border border-red-200 p-4 text-sm text-red-800 flex items-center justify-between">
                <div class="flex items-center gap-2">
                    <svg class="w-5 h-5 text-red-600 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                    <span>{{ session('error') }}</span>
                </div>
            </div>
        @endif
        @if ($errors->any())
            <div class="rounded-xl bg-red-50 border border-red-200 p-4 text-sm text-red-800 space-y-1">
                <div class="font-bold flex items-center gap-2">
                    <svg class="w-5 h-5 text-red-600 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                    <span>Mohon periksa kesalahan input berikut:</span>
                </div>
                <ul class="list-disc list-inside ml-6 text-xs space-y-0.5">
                    @foreach ($errors->all() as $err)
                        <li>{{ $err }}</li>
                    @endforeach
                </ul>
            </div>
        @endif

        {{-- 3 Tahap Cards --}}
        <div class="space-y-6">
            @forelse ($stages as $stage)
                <section class="rounded-2xl border border-gray-200 bg-white shadow-xs overflow-hidden">
                    {{-- Header Tahap --}}
                    <div class="p-5 sm:px-6 sm:py-5 border-b border-gray-100 bg-gray-50/60 flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
                        <div class="space-y-1.5">
                            <div class="flex flex-wrap items-center gap-2">
                                <span class="inline-flex items-center px-2.5 py-1 rounded-lg text-xs font-bold bg-primary-700 text-white">
                                    Tahap {{ $stage->stage_order }}
                                </span>
                                <h2 class="text-base font-bold text-gray-900">{{ $stage->title }}</h2>
                                @if ($stage->is_active)
                                    <span class="inline-flex items-center px-2 py-0.5 rounded-full text-xs font-semibold bg-emerald-100 text-emerald-800">Aktif</span>
                                @else
                                    <span class="inline-flex items-center px-2 py-0.5 rounded-full text-xs font-semibold bg-gray-100 text-gray-600">Nonaktif</span>
                                @endif
                                @if ($stage->is_confidential)
                                    <span class="inline-flex items-center gap-1 px-2 py-0.5 rounded-full text-xs font-semibold bg-amber-50 text-amber-800 border border-amber-200">
                                        <svg class="w-3 h-3 text-amber-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z"/></svg>
                                        Rahasia / Confidential
                                    </span>
                                @endif
                            </div>
                            <div class="flex flex-wrap items-center gap-x-3 gap-y-1 text-xs text-gray-500 font-mono">
                                <span>Kode: <strong>{{ $stage->code }}</strong></span>
                                <span>&bull;</span>
                                <span>Cakupan: <strong class="text-gray-700">{{ $stage->program_scope === 'UNGGULAN' ? 'Khusus Kelas Unggulan' : 'Semua Program (Reguler & Unggulan)' }}</strong></span>
                                <span>&bull;</span>
                                <span>Role Penguji: <span class="bg-gray-100 text-gray-800 px-1.5 py-0.5 rounded">{{ $stage->required_role }}</span></span>
                            </div>
                            @if ($stage->description)
                                <p class="text-xs text-gray-600 mt-2 bg-white/80 p-2.5 rounded-lg border border-gray-100">{{ $stage->description }}</p>
                            @endif
                        </div>

                        <div class="flex items-center gap-2 shrink-0 self-start sm:self-center">
                            <button
                                type="button"
                                onclick="openEditStageModal({{ json_encode($stage) }})"
                                class="inline-flex items-center gap-1.5 px-3 py-1.5 rounded-lg border border-gray-300 bg-white text-xs font-semibold text-gray-700 hover:bg-gray-50 shadow-2xs"
                            >
                                <svg class="w-3.5 h-3.5 text-gray-500" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"/></svg>
                                <span>Edit Tahap</span>
                            </button>
                            <button
                                type="button"
                                onclick="openAddCriterionModal({{ json_encode($stage) }})"
                                class="inline-flex items-center gap-1.5 px-3 py-1.5 rounded-lg bg-primary-700 text-xs font-semibold text-white hover:bg-primary-800 shadow-2xs"
                            >
                                <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/></svg>
                                <span>Tambah Poin</span>
                            </button>
                        </div>
                    </div>

                    {{-- Daftar Kriteria Penilaian --}}
                    <div class="p-5 sm:p-6">
                        <div class="overflow-x-auto">
                            <table class="min-w-full text-sm divide-y divide-gray-100">
                                <thead class="bg-gray-50 text-left text-xs uppercase text-gray-500 font-semibold tracking-wider">
                                    <tr>
                                        <th class="px-4 py-2.5 w-12 text-center">Urutan</th>
                                        <th class="px-4 py-2.5">Nama Poin Penilaian</th>
                                        <th class="px-4 py-2.5">Deskripsi / Indikator</th>
                                        <th class="px-4 py-2.5 text-center">Rentang Skor</th>
                                        <th class="px-4 py-2.5 text-center">Status</th>
                                        <th class="px-4 py-2.5 text-center">Riwayat</th>
                                        <th class="px-4 py-2.5 text-right">Aksi</th>
                                    </tr>
                                </thead>
                                <tbody class="divide-y divide-gray-100 bg-white">
                                    @forelse ($stage->criteria as $criterion)
                                        <tr class="hover:bg-gray-50/50">
                                            <td class="px-4 py-3 text-center text-xs font-bold text-gray-500 tabular-nums">
                                                #{{ $criterion->sort_order }}
                                            </td>
                                            <td class="px-4 py-3 font-semibold text-gray-900">
                                                {{ $criterion->title }}
                                            </td>
                                            <td class="px-4 py-3 text-xs text-gray-500 max-w-md">
                                                {{ $criterion->description ?: '-' }}
                                            </td>
                                            <td class="px-4 py-3 text-center text-xs font-mono text-gray-700 tabular-nums">
                                                {{ $criterion->min_score }} – {{ $criterion->max_score }}
                                            </td>
                                            <td class="px-4 py-3 text-center">
                                                @if ($criterion->is_active)
                                                    <span class="inline-flex items-center px-2 py-0.5 rounded-full text-[11px] font-semibold bg-emerald-100 text-emerald-800">Aktif</span>
                                                @else
                                                    <span class="inline-flex items-center px-2 py-0.5 rounded-full text-[11px] font-semibold bg-gray-100 text-gray-600">Nonaktif</span>
                                                @endif
                                            </td>
                                            <td class="px-4 py-3 text-center text-xs text-gray-600 tabular-nums">
                                                <span class="px-2 py-0.5 rounded bg-gray-50 border border-gray-200">
                                                    {{ $criterion->scores_count }} nilai
                                                </span>
                                            </td>
                                            <td class="px-4 py-3 text-right text-xs font-medium space-x-2 whitespace-nowrap">
                                                <button
                                                    type="button"
                                                    onclick="openEditCriterionModal({{ json_encode($criterion) }})"
                                                    class="text-primary-700 hover:text-primary-900 font-semibold hover:underline"
                                                >
                                                    Edit
                                                </button>
                                                @if ($criterion->scores_count === 0)
                                                    <form
                                                        method="POST"
                                                        action="{{ route('admin.interview-criteria.destroy', $criterion) }}"
                                                        class="inline"
                                                        onsubmit="return confirm('Apakah Anda yakin ingin menghapus poin penilaian \'{{ $criterion->title }}\'?');"
                                                    >
                                                        @csrf
                                                        @method('DELETE')
                                                        <button type="submit" class="text-red-600 hover:text-red-800 font-semibold hover:underline">
                                                            Hapus
                                                        </button>
                                                    </form>
                                                @else
                                                    <span class="text-gray-400 cursor-not-allowed text-[11px]" title="Sudah memiliki riwayat penilaian dari pewawancara, tidak dapat dihapus (nonaktifkan saja)">
                                                        Hapus (Terkunci)
                                                    </span>
                                                @endif
                                            </td>
                                        </tr>
                                    @empty
                                        <tr>
                                            <td colspan="7" class="px-4 py-8 text-center text-xs text-gray-500 italic">
                                                Belum ada poin penilaian pada tahap ini. Silakan klik tombol "Tambah Poin".
                                            </td>
                                        </tr>
                                    @endforelse
                                </tbody>
                            </table>
                        </div>
                    </div>
                </section>
            @empty
                <div class="rounded-xl border border-dashed border-gray-300 bg-white p-12 text-center text-gray-500 text-sm">
                    Belum ada master tahap wawancara untuk periode aktif.
                </div>
            @endforelse
        </div>
    </div>

    {{-- MODAL EDIT TAHAP --}}
    <div id="edit-stage-modal" class="fixed inset-0 z-50 hidden overflow-y-auto bg-gray-900/50 backdrop-blur-xs flex items-center justify-center p-4">
        <div class="relative w-full max-w-lg rounded-2xl bg-white shadow-xl overflow-hidden border border-gray-100">
            <div class="px-6 py-4 border-b border-gray-100 flex items-center justify-between bg-gray-50/70">
                <div>
                    <h3 class="text-base font-bold text-gray-900">Edit Judul & Panduan Tahap</h3>
                    <p id="edit-stage-subtitle" class="text-xs text-gray-500 mt-0.5"></p>
                </div>
                <button type="button" onclick="closeEditStageModal()" class="text-gray-400 hover:text-gray-600 p-1 rounded-lg">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg>
                </button>
            </div>
            <form id="edit-stage-form" method="POST" class="p-6 space-y-4 text-sm" onsubmit="this.querySelector('button[type=submit]').disabled=true">
                @csrf
                @method('PUT')

                <div>
                    <label class="block text-xs font-semibold text-gray-700 mb-1">Judul Tahap Wawancara</label>
                    <input type="text" id="edit-stage-title" name="title" required class="w-full rounded-lg border-gray-300 text-sm focus:border-primary-500 focus:ring-primary-500">
                </div>

                <div>
                    <label class="block text-xs font-semibold text-gray-700 mb-1">Deskripsi & Panduan Ringkas Penguji</label>
                    <textarea id="edit-stage-description" name="description" rows="3" placeholder="Panduan umum bagi pewawancara pada tahap ini" class="w-full rounded-lg border-gray-300 text-sm focus:border-primary-500 focus:ring-primary-500"></textarea>
                </div>

                <div class="flex items-center gap-2 pt-1">
                    <input type="checkbox" id="edit-stage-is-active" name="is_active" value="1" class="rounded border-gray-300 text-primary-600 focus:ring-primary-500">
                    <label for="edit-stage-is-active" class="text-xs text-gray-700 font-medium">Tahap wawancara aktif</label>
                </div>

                <div class="p-3 bg-amber-50 rounded-lg border border-amber-200 text-amber-800 text-[11px] leading-relaxed">
                    <strong>Catatan:</strong> Kode tahap, nomor urutan, cakupan program, dan role pewawancara dikunci sistem untuk menjaga integritas Sequential Gate.
                </div>

                <div class="pt-4 border-t border-gray-100 flex items-center justify-end gap-2">
                    <button type="button" onclick="closeEditStageModal()" class="px-4 py-2 rounded-lg border border-gray-300 text-xs font-semibold text-gray-700 hover:bg-gray-50">Batal</button>
                    <button type="submit" class="px-4 py-2 rounded-lg bg-primary-700 text-xs font-semibold text-white hover:bg-primary-800 shadow-xs">Simpan Perubahan</button>
                </div>
            </form>
        </div>
    </div>

    {{-- MODAL TAMBAH POIN PENILAIAN --}}
    <div id="add-criterion-modal" class="fixed inset-0 z-50 hidden overflow-y-auto bg-gray-900/50 backdrop-blur-xs flex items-center justify-center p-4">
        <div class="relative w-full max-w-lg rounded-2xl bg-white shadow-xl overflow-hidden border border-gray-100">
            <div class="px-6 py-4 border-b border-gray-100 flex items-center justify-between bg-gray-50/70">
                <div>
                    <h3 class="text-base font-bold text-gray-900">Tambah Poin Penilaian Baru</h3>
                    <p id="add-criterion-subtitle" class="text-xs text-gray-500 mt-0.5"></p>
                </div>
                <button type="button" onclick="closeAddCriterionModal()" class="text-gray-400 hover:text-gray-600 p-1 rounded-lg">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg>
                </button>
            </div>
            <form id="add-criterion-form" method="POST" class="p-6 space-y-4 text-sm" onsubmit="this.querySelector('button[type=submit]').disabled=true">
                @csrf

                <div class="grid grid-cols-1 sm:grid-cols-4 gap-4">
                    <div class="sm:col-span-3">
                        <label class="block text-xs font-semibold text-gray-700 mb-1">Nama Poin Penilaian</label>
                        <input type="text" name="title" placeholder="Contoh: Makharijul Huruf & Kelancaran" required class="w-full rounded-lg border-gray-300 text-sm focus:border-primary-500 focus:ring-primary-500">
                    </div>
                    <div>
                        <label class="block text-xs font-semibold text-gray-700 mb-1">Nomor Urut</label>
                        <input type="number" id="add-criterion-sort" name="sort_order" min="1" max="100" class="w-full rounded-lg border-gray-300 text-sm focus:border-primary-500 focus:ring-primary-500">
                    </div>
                </div>

                <div>
                    <label class="block text-xs font-semibold text-gray-700 mb-1">Deskripsi Rubrik / Indikator Penilaian</label>
                    <textarea name="description" rows="3" placeholder="Panduan kriteria penskoran skala 0 s.d. 100 bagi penguji..." class="w-full rounded-lg border-gray-300 text-sm focus:border-primary-500 focus:ring-primary-500"></textarea>
                </div>

                <div class="flex items-center gap-2 pt-1">
                    <input type="checkbox" id="add-criterion-is-active" name="is_active" value="1" checked class="rounded border-gray-300 text-primary-600 focus:ring-primary-500">
                    <label for="add-criterion-is-active" class="text-xs text-gray-700 font-medium">Poin penilaian aktif</label>
                </div>

                <div class="pt-4 border-t border-gray-100 flex items-center justify-end gap-2">
                    <button type="button" onclick="closeAddCriterionModal()" class="px-4 py-2 rounded-lg border border-gray-300 text-xs font-semibold text-gray-700 hover:bg-gray-50">Batal</button>
                    <button type="submit" class="px-4 py-2 rounded-lg bg-primary-700 text-xs font-semibold text-white hover:bg-primary-800 shadow-xs">Simpan Poin</button>
                </div>
            </form>
        </div>
    </div>

    {{-- MODAL EDIT POIN PENILAIAN --}}
    <div id="edit-criterion-modal" class="fixed inset-0 z-50 hidden overflow-y-auto bg-gray-900/50 backdrop-blur-xs flex items-center justify-center p-4">
        <div class="relative w-full max-w-lg rounded-2xl bg-white shadow-xl overflow-hidden border border-gray-100">
            <div class="px-6 py-4 border-b border-gray-100 flex items-center justify-between bg-gray-50/70">
                <div>
                    <h3 class="text-base font-bold text-gray-900">Edit Poin Penilaian</h3>
                    <p id="edit-criterion-subtitle" class="text-xs text-gray-500 mt-0.5"></p>
                </div>
                <button type="button" onclick="closeEditCriterionModal()" class="text-gray-400 hover:text-gray-600 p-1 rounded-lg">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg>
                </button>
            </div>
            <form id="edit-criterion-form" method="POST" class="p-6 space-y-4 text-sm" onsubmit="this.querySelector('button[type=submit]').disabled=true">
                @csrf
                @method('PUT')

                <div class="grid grid-cols-1 sm:grid-cols-4 gap-4">
                    <div class="sm:col-span-3">
                        <label class="block text-xs font-semibold text-gray-700 mb-1">Nama Poin Penilaian</label>
                        <input type="text" id="edit-criterion-title" name="title" required class="w-full rounded-lg border-gray-300 text-sm focus:border-primary-500 focus:ring-primary-500">
                    </div>
                    <div>
                        <label class="block text-xs font-semibold text-gray-700 mb-1">Nomor Urut</label>
                        <input type="number" id="edit-criterion-sort" name="sort_order" min="1" max="100" class="w-full rounded-lg border-gray-300 text-sm focus:border-primary-500 focus:ring-primary-500">
                    </div>
                </div>

                <div>
                    <label class="block text-xs font-semibold text-gray-700 mb-1">Deskripsi Rubrik / Indikator Penilaian</label>
                    <textarea id="edit-criterion-description" name="description" rows="3" class="w-full rounded-lg border-gray-300 text-sm focus:border-primary-500 focus:ring-primary-500"></textarea>
                </div>

                <div class="flex items-center gap-2 pt-1">
                    <input type="checkbox" id="edit-criterion-is-active" name="is_active" value="1" class="rounded border-gray-300 text-primary-600 focus:ring-primary-500">
                    <label for="edit-criterion-is-active" class="text-xs text-gray-700 font-medium">Poin penilaian aktif</label>
                </div>

                <div class="pt-4 border-t border-gray-100 flex items-center justify-end gap-2">
                    <button type="button" onclick="closeEditCriterionModal()" class="px-4 py-2 rounded-lg border border-gray-300 text-xs font-semibold text-gray-700 hover:bg-gray-50">Batal</button>
                    <button type="submit" class="px-4 py-2 rounded-lg bg-primary-700 text-xs font-semibold text-white hover:bg-primary-800 shadow-xs">Simpan Perubahan</button>
                </div>
            </form>
        </div>
    </div>

    <script>
        function openEditStageModal(stage) {
            const form = document.getElementById('edit-stage-form');
            form.action = `/admin/interview-stages/${stage.id}`;
            document.getElementById('edit-stage-subtitle').textContent = `Tahap ${stage.stage_order} - ${stage.code}`;
            document.getElementById('edit-stage-title').value = stage.title;
            document.getElementById('edit-stage-description').value = stage.description || '';
            document.getElementById('edit-stage-is-active').checked = !!stage.is_active;

            document.getElementById('edit-stage-modal').classList.remove('hidden');
        }

        function closeEditStageModal() {
            document.getElementById('edit-stage-modal').classList.add('hidden');
        }

        function openAddCriterionModal(stage) {
            const form = document.getElementById('add-criterion-form');
            form.action = `/admin/interview-stages/${stage.id}/criteria`;
            document.getElementById('add-criterion-subtitle').textContent = `Untuk: ${stage.title}`;
            const criteriaCount = (stage.criteria || []).length;
            document.getElementById('add-criterion-sort').value = criteriaCount + 1;

            document.getElementById('add-criterion-modal').classList.remove('hidden');
        }

        function closeAddCriterionModal() {
            document.getElementById('add-criterion-modal').classList.add('hidden');
        }

        function openEditCriterionModal(criterion) {
            const form = document.getElementById('edit-criterion-form');
            form.action = `/admin/interview-criteria/${criterion.id}`;
            document.getElementById('edit-criterion-subtitle').textContent = criterion.title;
            document.getElementById('edit-criterion-title').value = criterion.title;
            document.getElementById('edit-criterion-sort').value = criterion.sort_order;
            document.getElementById('edit-criterion-description').value = criterion.description || '';
            document.getElementById('edit-criterion-is-active').checked = !!criterion.is_active;

            document.getElementById('edit-criterion-modal').classList.remove('hidden');
        }

        function closeEditCriterionModal() {
            document.getElementById('edit-criterion-modal').classList.add('hidden');
        }

        // Close on ESC
        window.addEventListener('keydown', function (e) {
            if (e.key === 'Escape') {
                closeEditStageModal();
                closeAddCriterionModal();
                closeEditCriterionModal();
            }
        });
    </script>
</x-layouts.admin>
