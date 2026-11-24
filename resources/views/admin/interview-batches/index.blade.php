<x-layouts.admin title="Jadwal Wawancara">
    <x-slot:header>
        <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
            <div>
                <div class="flex items-center gap-2">
                    <h1 class="text-2xl font-bold text-gray-900">Jadwal & Kuota Wawancara</h1>
                    @if ($period)
                        <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-semibold bg-primary-50 text-primary-800 border border-primary-200">
                            TP {{ $period->academic_year }}
                        </span>
                    @endif
                </div>
                <p class="text-sm text-gray-500 mt-1">Kelola gelombang tanggal, jam pelaksanaan, kuota harian, dan rentang alokasi nomor urut pendaftaran.</p>
            </div>
            @if ($period)
                <button
                    type="button"
                    onclick="openCreateModal()"
                    class="inline-flex items-center justify-center gap-2 rounded-lg bg-primary-700 px-4 py-2.5 text-sm font-semibold text-white shadow-xs hover:bg-primary-800 focus:outline-none focus:ring-2 focus:ring-primary-500 focus:ring-offset-2 transition-colors shrink-0"
                >
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/></svg>
                    <span>Tambah Jadwal Gelombang</span>
                </button>
            @endif
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

        {{-- Stat Summary Cards --}}
        <div class="grid grid-cols-1 sm:grid-cols-3 gap-4">
            <div class="rounded-xl border border-gray-200 bg-white p-5 shadow-xs">
                <span class="text-xs font-semibold uppercase tracking-wide text-gray-500">Total Gelombang</span>
                <p class="mt-2 text-2xl font-bold text-gray-900 tabular-nums">{{ $batches->count() }}</p>
                <p class="text-xs text-gray-500 mt-0.5">Hari pelaksanaan wawancara</p>
            </div>
            <div class="rounded-xl border border-gray-200 bg-white p-5 shadow-xs">
                <span class="text-xs font-semibold uppercase tracking-wide text-gray-500">Total Kapasitas Kuota</span>
                <p class="mt-2 text-2xl font-bold text-primary-700 tabular-nums">{{ number_format($batches->sum('quota')) }}</p>
                <p class="text-xs text-gray-500 mt-0.5">Total calon siswa terakomodasi</p>
            </div>
            <div class="rounded-xl border border-gray-200 bg-white p-5 shadow-xs">
                <span class="text-xs font-semibold uppercase tracking-wide text-gray-500">Pendaftar Terplot</span>
                <p class="mt-2 text-2xl font-bold text-emerald-700 tabular-nums">{{ number_format($batches->sum('registrations_count')) }}</p>
                <p class="text-xs text-gray-500 mt-0.5">Calon siswa yang disahkan panitia</p>
            </div>
        </div>

        {{-- Table Card --}}
        <div class="rounded-xl border border-gray-200 bg-white shadow-xs overflow-hidden">
            <div class="px-5 py-4 border-b border-gray-100 flex items-center justify-between bg-gray-50/50">
                <h2 class="font-bold text-gray-900 text-sm">Daftar Gelombang / Batch</h2>
                <span class="text-xs text-gray-500 font-medium">Periode: {{ $period?->name ?? 'Belum ada periode aktif' }}</span>
            </div>

            <div class="overflow-x-auto">
                <table class="min-w-full text-sm divide-y divide-gray-200">
                    <thead class="bg-gray-50 text-left text-xs uppercase text-gray-500 font-semibold tracking-wider">
                        <tr>
                            <th class="px-5 py-3">Gelombang</th>
                            <th class="px-5 py-3">Judul Jadwal</th>
                            <th class="px-5 py-3">Hari & Tanggal</th>
                            <th class="px-5 py-3">Waktu</th>
                            <th class="px-5 py-3 text-center">Kuota</th>
                            <th class="px-5 py-3 text-center">Rentang Urut</th>
                            <th class="px-5 py-3 text-center">Terplot</th>
                            <th class="px-5 py-3 text-center">Status</th>
                            <th class="px-5 py-3 text-right">Aksi</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-100 bg-white">
                        @forelse ($batches as $batch)
                            <tr class="hover:bg-gray-50/70 transition-colors">
                                <td class="px-5 py-4 whitespace-nowrap">
                                    <span class="inline-flex items-center justify-center w-7 h-7 rounded-lg bg-gray-100 text-gray-800 font-bold text-xs">
                                        #{{ $batch->batch_number }}
                                    </span>
                                </td>
                                <td class="px-5 py-4 whitespace-nowrap font-medium text-gray-900">
                                    {{ $batch->title }}
                                    @if ($batch->description)
                                        <div class="text-xs text-gray-500 font-normal truncate max-w-xs">{{ $batch->description }}</div>
                                    @endif
                                </td>
                                <td class="px-5 py-4 whitespace-nowrap text-gray-700">
                                    {{ $batch->interview_date ? $batch->interview_date->locale('id')->translatedFormat('l, d F Y') : '-' }}
                                </td>
                                <td class="px-5 py-4 whitespace-nowrap text-xs text-gray-600 font-mono">
                                    {{ substr((string) $batch->start_time, 0, 5) }} - {{ substr((string) $batch->end_time, 0, 5) }} WIB
                                </td>
                                <td class="px-5 py-4 whitespace-nowrap text-center text-sm font-semibold text-gray-900 tabular-nums">
                                    {{ $batch->quota }}
                                </td>
                                <td class="px-5 py-4 whitespace-nowrap text-center">
                                    <span class="inline-flex items-center px-2.5 py-1 rounded-md text-xs font-mono font-semibold bg-gray-50 text-gray-700 border border-gray-200 tabular-nums">
                                        {{ $batch->sequence_start }} – {{ $batch->sequence_end }}
                                    </span>
                                </td>
                                <td class="px-5 py-4 whitespace-nowrap text-center">
                                    <span class="inline-flex items-center px-2.5 py-1 rounded-full text-xs font-semibold tabular-nums {{ $batch->registrations_count >= $batch->quota ? 'bg-red-100 text-red-800' : ($batch->registrations_count > 0 ? 'bg-primary-50 text-primary-800' : 'bg-gray-100 text-gray-600') }}">
                                        {{ $batch->registrations_count }} / {{ $batch->quota }}
                                    </span>
                                </td>
                                <td class="px-5 py-4 whitespace-nowrap text-center">
                                    @if ($batch->is_active)
                                        <span class="inline-flex items-center px-2 py-0.5 rounded-full text-xs font-semibold bg-emerald-100 text-emerald-800">Aktif</span>
                                    @else
                                        <span class="inline-flex items-center px-2 py-0.5 rounded-full text-xs font-semibold bg-gray-100 text-gray-600">Nonaktif</span>
                                    @endif
                                </td>
                                <td class="px-5 py-4 whitespace-nowrap text-right text-xs font-medium space-x-2">
                                    <button
                                        type="button"
                                        onclick="openEditModal({{ json_encode($batch) }})"
                                        class="text-primary-700 hover:text-primary-900 font-semibold hover:underline"
                                    >
                                        Edit
                                    </button>
                                    @if ($batch->registrations_count === 0)
                                        <form
                                            method="POST"
                                            action="{{ route('admin.interview-batches.destroy', $batch) }}"
                                            class="inline"
                                            onsubmit="return confirm('Apakah Anda yakin ingin menghapus jadwal gelombang {{ $batch->title }}?');"
                                        >
                                            @csrf
                                            @method('DELETE')
                                            <button type="submit" class="text-red-600 hover:text-red-800 font-semibold hover:underline">
                                                Hapus
                                            </button>
                                        </form>
                                    @else
                                        <span class="text-gray-400 cursor-not-allowed" title="Sudah memiliki pendaftar terplot, tidak dapat dihapus">Hapus</span>
                                    @endif
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="9" class="px-5 py-12 text-center text-gray-500 text-sm">
                                    <div class="max-w-sm mx-auto space-y-2">
                                        <svg class="w-10 h-10 mx-auto text-gray-300" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"/></svg>
                                        <p class="font-semibold text-gray-700">Belum Ada Jadwal Gelombang Wawancara</p>
                                        <p class="text-xs text-gray-500">Tambahkan gelombang hari pertama untuk memulai alokasi kuota wawancara pendaftar.</p>
                                    </div>
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>

    {{-- MODAL TAMBAH JADWAL --}}
    @if ($period)
        <div id="create-batch-modal" class="fixed inset-0 z-50 hidden overflow-y-auto bg-gray-900/50 backdrop-blur-xs flex items-center justify-center p-4">
            <div class="relative w-full max-w-xl rounded-2xl bg-white shadow-xl overflow-hidden border border-gray-100">
                <div class="px-6 py-4 border-b border-gray-100 flex items-center justify-between bg-gray-50/70">
                    <div>
                        <h3 class="text-base font-bold text-gray-900">Tambah Jadwal Gelombang Wawancara</h3>
                        <p class="text-xs text-gray-500 mt-0.5">Periode {{ $period->academic_year }}</p>
                    </div>
                    <button type="button" onclick="closeCreateModal()" class="text-gray-400 hover:text-gray-600 p-1 rounded-lg">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg>
                    </button>
                </div>
                <form method="POST" action="{{ route('admin.interview-batches.store') }}" class="p-6 space-y-4 text-sm" onsubmit="this.querySelector('button[type=submit]').disabled=true">
                    @csrf
                    <input type="hidden" name="admission_period_id" value="{{ $period->id }}">

                    <div class="grid grid-cols-1 sm:grid-cols-3 gap-4">
                        <div>
                            <label class="block text-xs font-semibold text-gray-700 mb-1">Hari Ke- (Urutan)</label>
                            <input type="number" name="batch_number" value="{{ old('batch_number', $suggestedBatchNumber) }}" min="1" required class="w-full rounded-lg border-gray-300 text-sm focus:border-primary-500 focus:ring-primary-500">
                        </div>
                        <div class="sm:col-span-2">
                            <label class="block text-xs font-semibold text-gray-700 mb-1">Judul Gelombang / Sesi</label>
                            <input type="text" name="title" value="{{ old('title', 'Hari ' . $suggestedBatchNumber . ' - Gelombang ' . $suggestedBatchNumber) }}" placeholder="Contoh: Hari 1 - Gelombang Pagi" required class="w-full rounded-lg border-gray-300 text-sm focus:border-primary-500 focus:ring-primary-500">
                        </div>
                    </div>

                    <div class="grid grid-cols-1 sm:grid-cols-3 gap-4">
                        <div>
                            <label class="block text-xs font-semibold text-gray-700 mb-1">Tanggal Wawancara</label>
                            <input type="date" name="interview_date" value="{{ old('interview_date', now()->addDays(7)->format('Y-m-d')) }}" required class="w-full rounded-lg border-gray-300 text-sm focus:border-primary-500 focus:ring-primary-500">
                        </div>
                        <div>
                            <label class="block text-xs font-semibold text-gray-700 mb-1">Jam Mulai</label>
                            <input type="time" name="start_time" value="{{ old('start_time', '07:30') }}" required class="w-full rounded-lg border-gray-300 text-sm focus:border-primary-500 focus:ring-primary-500">
                        </div>
                        <div>
                            <label class="block text-xs font-semibold text-gray-700 mb-1">Jam Selesai</label>
                            <input type="time" name="end_time" value="{{ old('end_time', '14:00') }}" required class="w-full rounded-lg border-gray-300 text-sm focus:border-primary-500 focus:ring-primary-500">
                        </div>
                    </div>

                    <div class="grid grid-cols-1 sm:grid-cols-3 gap-4 pt-2 border-t border-gray-100">
                        <div>
                            <label class="block text-xs font-semibold text-gray-700 mb-1">Kuota Harian</label>
                            <input type="number" id="create-quota" name="quota" value="{{ old('quota', 70) }}" min="1" max="500" required class="w-full rounded-lg border-gray-300 text-sm focus:border-primary-500 focus:ring-primary-500" oninput="recalcCreateSequence()">
                        </div>
                        <div>
                            <label class="block text-xs font-semibold text-gray-700 mb-1">Nomor Urut Awal</label>
                            <input type="number" id="create-seq-start" name="sequence_start" value="{{ old('sequence_start', $suggestedStart) }}" min="1" required class="w-full rounded-lg border-gray-300 text-sm focus:border-primary-500 focus:ring-primary-500" oninput="recalcCreateSequence()">
                        </div>
                        <div>
                            <label class="block text-xs font-semibold text-gray-700 mb-1">Nomor Urut Akhir</label>
                            <input type="number" id="create-seq-end" name="sequence_end" value="{{ old('sequence_end', $suggestedEnd) }}" min="1" required class="w-full rounded-lg border-gray-300 text-sm focus:border-primary-500 focus:ring-primary-500">
                        </div>
                    </div>

                    <div>
                        <label class="block text-xs font-semibold text-gray-700 mb-1">Keterangan / Ruangan (Opsional)</label>
                        <textarea name="description" rows="2" placeholder="Contoh: Gedung A Lantai 1 Ruang Kelas 1A s.d. 1D" class="w-full rounded-lg border-gray-300 text-sm focus:border-primary-500 focus:ring-primary-500">{{ old('description') }}</textarea>
                    </div>

                    <div class="flex items-center gap-2 pt-2">
                        <input type="checkbox" id="create-is-active" name="is_active" value="1" checked class="rounded border-gray-300 text-primary-600 focus:ring-primary-500">
                        <label for="create-is-active" class="text-xs text-gray-700 font-medium">Jadwal gelombang aktif dan dapat menerima plotting pendaftar</label>
                    </div>

                    <div class="pt-4 border-t border-gray-100 flex items-center justify-end gap-2">
                        <button type="button" onclick="closeCreateModal()" class="px-4 py-2 rounded-lg border border-gray-300 text-xs font-semibold text-gray-700 hover:bg-gray-50">Batal</button>
                        <button type="submit" class="px-4 py-2 rounded-lg bg-primary-700 text-xs font-semibold text-white hover:bg-primary-800 shadow-xs">Simpan Jadwal</button>
                    </div>
                </form>
            </div>
        </div>
    @endif

    {{-- MODAL EDIT JADWAL --}}
    <div id="edit-batch-modal" class="fixed inset-0 z-50 hidden overflow-y-auto bg-gray-900/50 backdrop-blur-xs flex items-center justify-center p-4">
        <div class="relative w-full max-w-xl rounded-2xl bg-white shadow-xl overflow-hidden border border-gray-100">
            <div class="px-6 py-4 border-b border-gray-100 flex items-center justify-between bg-gray-50/70">
                <div>
                    <h3 class="text-base font-bold text-gray-900">Edit Jadwal Gelombang Wawancara</h3>
                    <p id="edit-batch-subtitle" class="text-xs text-gray-500 mt-0.5"></p>
                </div>
                <button type="button" onclick="closeEditModal()" class="text-gray-400 hover:text-gray-600 p-1 rounded-lg">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg>
                </button>
            </div>
            <form id="edit-batch-form" method="POST" class="p-6 space-y-4 text-sm" onsubmit="this.querySelector('button[type=submit]').disabled=true">
                @csrf
                @method('PUT')

                <div class="grid grid-cols-1 sm:grid-cols-3 gap-4">
                    <div>
                        <label class="block text-xs font-semibold text-gray-700 mb-1">Hari Ke- (Urutan)</label>
                        <input type="number" id="edit-batch-number" name="batch_number" min="1" required class="w-full rounded-lg border-gray-300 text-sm focus:border-primary-500 focus:ring-primary-500">
                    </div>
                    <div class="sm:col-span-2">
                        <label class="block text-xs font-semibold text-gray-700 mb-1">Judul Gelombang / Sesi</label>
                        <input type="text" id="edit-title" name="title" required class="w-full rounded-lg border-gray-300 text-sm focus:border-primary-500 focus:ring-primary-500">
                    </div>
                </div>

                <div class="grid grid-cols-1 sm:grid-cols-3 gap-4">
                    <div>
                        <label class="block text-xs font-semibold text-gray-700 mb-1">Tanggal Wawancara</label>
                        <input type="date" id="edit-date" name="interview_date" required class="w-full rounded-lg border-gray-300 text-sm focus:border-primary-500 focus:ring-primary-500">
                    </div>
                    <div>
                        <label class="block text-xs font-semibold text-gray-700 mb-1">Jam Mulai</label>
                        <input type="time" id="edit-start-time" name="start_time" required class="w-full rounded-lg border-gray-300 text-sm focus:border-primary-500 focus:ring-primary-500">
                    </div>
                    <div>
                        <label class="block text-xs font-semibold text-gray-700 mb-1">Jam Selesai</label>
                        <input type="time" id="edit-end-time" name="end_time" required class="w-full rounded-lg border-gray-300 text-sm focus:border-primary-500 focus:ring-primary-500">
                    </div>
                </div>

                <div class="grid grid-cols-1 sm:grid-cols-3 gap-4 pt-2 border-t border-gray-100">
                    <div>
                        <label class="block text-xs font-semibold text-gray-700 mb-1">Kuota Harian</label>
                        <input type="number" id="edit-quota" name="quota" min="1" max="500" required class="w-full rounded-lg border-gray-300 text-sm focus:border-primary-500 focus:ring-primary-500" oninput="recalcEditSequence()">
                    </div>
                    <div>
                        <label class="block text-xs font-semibold text-gray-700 mb-1">Nomor Urut Awal</label>
                        <input type="number" id="edit-seq-start" name="sequence_start" min="1" required class="w-full rounded-lg border-gray-300 text-sm focus:border-primary-500 focus:ring-primary-500" oninput="recalcEditSequence()">
                    </div>
                    <div>
                        <label class="block text-xs font-semibold text-gray-700 mb-1">Nomor Urut Akhir</label>
                        <input type="number" id="edit-seq-end" name="sequence_end" min="1" required class="w-full rounded-lg border-gray-300 text-sm focus:border-primary-500 focus:ring-primary-500">
                    </div>
                </div>

                <div>
                    <label class="block text-xs font-semibold text-gray-700 mb-1">Keterangan / Ruangan (Opsional)</label>
                    <textarea id="edit-description" name="description" rows="2" class="w-full rounded-lg border-gray-300 text-sm focus:border-primary-500 focus:ring-primary-500"></textarea>
                </div>

                <div class="flex items-center gap-2 pt-2">
                    <input type="checkbox" id="edit-is-active" name="is_active" value="1" class="rounded border-gray-300 text-primary-600 focus:ring-primary-500">
                    <label for="edit-is-active" class="text-xs text-gray-700 font-medium">Jadwal gelombang aktif</label>
                </div>

                <div class="pt-4 border-t border-gray-100 flex items-center justify-end gap-2">
                    <button type="button" onclick="closeEditModal()" class="px-4 py-2 rounded-lg border border-gray-300 text-xs font-semibold text-gray-700 hover:bg-gray-50">Batal</button>
                    <button type="submit" class="px-4 py-2 rounded-lg bg-primary-700 text-xs font-semibold text-white hover:bg-primary-800 shadow-xs">Simpan Perubahan</button>
                </div>
            </form>
        </div>
    </div>

    <script>
        function openCreateModal() {
            document.getElementById('create-batch-modal').classList.remove('hidden');
        }
        function closeCreateModal() {
            document.getElementById('create-batch-modal').classList.add('hidden');
        }

        function recalcCreateSequence() {
            const quota = parseInt(document.getElementById('create-quota').value) || 0;
            const start = parseInt(document.getElementById('create-seq-start').value) || 0;
            if (quota > 0 && start > 0) {
                document.getElementById('create-seq-end').value = start + quota - 1;
            }
        }

        function openEditModal(batch) {
            const form = document.getElementById('edit-batch-form');
            form.action = `/admin/interview-batches/${batch.id}`;
            document.getElementById('edit-batch-subtitle').textContent = `Batch #${batch.batch_number} - ${batch.title}`;
            document.getElementById('edit-batch-number').value = batch.batch_number;
            document.getElementById('edit-title').value = batch.title;
            document.getElementById('edit-date').value = batch.interview_date ? batch.interview_date.substring(0, 10) : '';
            document.getElementById('edit-start-time').value = batch.start_time ? batch.start_time.substring(0, 5) : '07:30';
            document.getElementById('edit-end-time').value = batch.end_time ? batch.end_time.substring(0, 5) : '14:00';
            document.getElementById('edit-quota').value = batch.quota;
            document.getElementById('edit-seq-start').value = batch.sequence_start;
            document.getElementById('edit-seq-end').value = batch.sequence_end;
            document.getElementById('edit-description').value = batch.description || '';
            document.getElementById('edit-is-active').checked = !!batch.is_active;

            document.getElementById('edit-batch-modal').classList.remove('hidden');
        }

        function closeEditModal() {
            document.getElementById('edit-batch-modal').classList.add('hidden');
        }

        function recalcEditSequence() {
            const quota = parseInt(document.getElementById('edit-quota').value) || 0;
            const start = parseInt(document.getElementById('edit-seq-start').value) || 0;
            if (quota > 0 && start > 0) {
                document.getElementById('edit-seq-end').value = start + quota - 1;
            }
        }

        // Close on ESC
        window.addEventListener('keydown', function (e) {
            if (e.key === 'Escape') {
                closeCreateModal();
                closeEditModal();
            }
        });
    </script>
</x-layouts.admin>
