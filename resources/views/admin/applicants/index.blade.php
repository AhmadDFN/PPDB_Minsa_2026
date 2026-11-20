<x-layouts.admin title="Data Pendaftar PPDB">
    <x-slot:header>
        <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
            <div>
                <h1 class="text-2xl font-bold text-gray-900 tracking-tight">Data Pendaftar PPDB</h1>
                <p class="text-sm text-gray-500 mt-1">
                    Daftar seluruh calon peserta didik baru dan pemantauan status berkas formulir pendaftaran.
                </p>
            </div>
            <div class="flex items-center gap-2">
                <span class="inline-flex items-center px-3 py-1.5 rounded-lg text-xs font-semibold bg-primary-50 text-primary-700 border border-primary-200">
                    Total: {{ number_format($registrations->total(), 0, ',', '.') }} Pendaftar
                </span>
            </div>
        </div>
    </x-slot:header>

    <div class="space-y-6">
        {{-- Section 1: Filter & Pencarian Komprehensif --}}
        <div class="bg-white rounded-xl border border-gray-200 p-5 shadow-xs">
            <form method="GET" action="{{ route('admin.applicants.index') }}" class="space-y-4">
                <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4">
                    {{-- Pencarian Teks (Nama, No. Pendaftaran, NIK) --}}
                    <div class="sm:col-span-2 lg:col-span-1">
                        <label for="search" class="block text-xs font-semibold uppercase tracking-wider text-gray-600 mb-1.5">
                            Cari Pendaftar
                        </label>
                        <div class="relative">
                            <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none text-gray-400">
                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z" />
                                </svg>
                            </div>
                            <input
                                type="text"
                                id="search"
                                name="search"
                                value="{{ $filters['search'] }}"
                                placeholder="Nama, No. Pendaftaran, NIK..."
                                class="w-full pl-9 pr-3 py-2 text-sm bg-gray-50 border border-gray-300 rounded-lg focus:ring-2 focus:ring-primary-500 focus:border-primary-500 transition-colors"
                            >
                        </div>
                    </div>

                    {{-- Filter Periode --}}
                    <div>
                        <label for="period_id" class="block text-xs font-semibold uppercase tracking-wider text-gray-600 mb-1.5">
                            Periode PPDB
                        </label>
                        <select
                            id="period_id"
                            name="period_id"
                            class="w-full px-3 py-2 text-sm bg-gray-50 border border-gray-300 rounded-lg focus:ring-2 focus:ring-primary-500 focus:border-primary-500 transition-colors"
                        >
                            <option value="">Semua Periode</option>
                            @foreach ($periods as $period)
                                <option value="{{ $period->id }}" @selected((string) $period->id === (string) $filters['period_id'])>
                                    TP {{ $period->academic_year }} - {{ $period->name }} {{ $period->is_active ? '(Aktif)' : '' }}
                                </option>
                            @endforeach
                        </select>
                    </div>

                    {{-- Filter Program --}}
                    <div>
                        <label for="program_id" class="block text-xs font-semibold uppercase tracking-wider text-gray-600 mb-1.5">
                            Program Pendidikan
                        </label>
                        <select
                            id="program_id"
                            name="program_id"
                            class="w-full px-3 py-2 text-sm bg-gray-50 border border-gray-300 rounded-lg focus:ring-2 focus:ring-primary-500 focus:border-primary-500 transition-colors"
                        >
                            <option value="">Semua Program</option>
                            @foreach ($programs as $prog)
                                <option value="{{ $prog->id }}" @selected((string) $prog->id === (string) $filters['program_id'])>
                                    {{ $prog->name }}
                                </option>
                            @endforeach
                        </select>
                    </div>

                    {{-- Filter Status Formulir --}}
                    <div>
                        <label for="status" class="block text-xs font-semibold uppercase tracking-wider text-gray-600 mb-1.5">
                            Status Formulir
                        </label>
                        <select
                            id="status"
                            name="status"
                            class="w-full px-3 py-2 text-sm bg-gray-50 border border-gray-300 rounded-lg focus:ring-2 focus:ring-primary-500 focus:border-primary-500 transition-colors"
                        >
                            <option value="">Semua Status</option>
                            <option value="SUBMITTED" @selected($filters['status'] === 'SUBMITTED')>Terkirim (Resmi)</option>
                            <option value="DRAFT" @selected($filters['status'] === 'DRAFT')>Draf (Belum Dikirim)</option>
                        </select>
                    </div>
                </div>

                <div class="flex items-center justify-between pt-2 border-t border-gray-100">
                    <p class="text-xs text-gray-500">
                        @if ($filters['search'] || $filters['period_id'] || $filters['program_id'] || $filters['status'])
                            Filter aktif diterapkan. Ditemukan <strong class="text-gray-900">{{ $registrations->total() }}</strong> hasil.
                        @else
                            Menampilkan seluruh riwayat pendaftaran.
                        @endif
                    </p>
                    <div class="flex items-center gap-2">
                        @if ($filters['search'] || $filters['period_id'] || $filters['program_id'] || $filters['status'])
                            <a
                                href="{{ route('admin.applicants.index') }}"
                                class="px-3 py-2 rounded-lg text-xs font-medium text-gray-600 hover:text-gray-900 hover:bg-gray-100 transition-colors"
                            >
                                Reset Filter
                            </a>
                        @endif
                        <button
                            type="submit"
                            class="inline-flex items-center gap-1.5 px-4 py-2 rounded-lg text-xs font-semibold bg-primary-600 text-white hover:bg-primary-700 shadow-xs transition-colors"
                        >
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 4a1 1 0 011-1h16a1 1 0 011 1v2.586a1 1 0 01-.293.707l-6.414 6.414a1 1 0 00-.293.707V17l-4 4v-6.586a1 1 0 00-.293-.707L3.293 7.293A1 1 0 013 6.586V4z" />
                            </svg>
                            <span>Terapkan</span>
                        </button>
                    </div>
                </div>
            </form>
        </div>

        {{-- Section 2: Tabel Daftar Pendaftar --}}
        <div class="bg-white rounded-xl border border-gray-200 shadow-xs overflow-hidden">
            @if ($registrations->isNotEmpty())
                <div class="overflow-x-auto">
                    <table class="w-full text-left text-sm">
                        <thead class="bg-gray-50 text-[11px] font-bold text-gray-500 uppercase tracking-wider border-b border-gray-100">
                            <tr>
                                <th scope="col" class="py-3 px-4 sm:px-6">No. Pendaftaran</th>
                                <th scope="col" class="py-3 px-4">Calon Siswa</th>
                                <th scope="col" class="py-3 px-4">Program & Periode</th>
                                <th scope="col" class="py-3 px-4">Status</th>
                                <th scope="col" class="py-3 px-4">Waktu Registrasi</th>
                                <th scope="col" class="py-3 px-4 sm:px-6 text-right">Aksi</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-gray-100">
                            @foreach ($registrations as $reg)
                                <tr class="hover:bg-gray-50/70 transition-colors">
                                    {{-- No. Pendaftaran --}}
                                    <td class="py-3.5 px-4 sm:px-6 font-mono text-xs font-bold text-gray-900 whitespace-nowrap">
                                        @if ($reg->registration_number)
                                            <span class="inline-flex items-center px-2 py-0.5 rounded bg-gray-100 text-gray-800 border border-gray-200">
                                                {{ $reg->registration_number }}
                                            </span>
                                        @else
                                            <span class="text-gray-400 font-normal italic">Belum terbit</span>
                                        @endif
                                    </td>

                                    {{-- Nama & NIK Bermasker --}}
                                    <td class="py-3.5 px-4">
                                        <div class="font-semibold text-gray-900">
                                            {{ $reg->applicant?->full_name ?? 'Tanpa Nama' }}
                                        </div>
                                        <div class="text-[11px] font-mono text-gray-500 mt-0.5">
                                            NIK: {{ $reg->applicant?->user?->getMaskedNik() ?? '—' }}
                                        </div>
                                    </td>

                                    {{-- Program & Periode --}}
                                    <td class="py-3.5 px-4">
                                        @php
                                            $progCode = strtoupper($reg->admissionPeriodProgram?->program?->code ?? '');
                                            $progName = $reg->admissionPeriodProgram?->program?->name ?? 'Belum Pilih Program';
                                        @endphp
                                        @if ($progCode === 'UNGGULAN')
                                            <span class="inline-flex items-center px-2 py-0.5 rounded text-[11px] font-medium bg-emerald-50 text-emerald-700 border border-emerald-200">
                                                {{ $progName }}
                                            </span>
                                        @elseif ($progCode === 'REGULER')
                                            <span class="inline-flex items-center px-2 py-0.5 rounded text-[11px] font-medium bg-sky-50 text-sky-700 border border-sky-200">
                                                {{ $progName }}
                                            </span>
                                        @else
                                            <span class="inline-flex items-center px-2 py-0.5 rounded text-[11px] font-medium bg-gray-100 text-gray-600 border border-gray-200">
                                                {{ $progName }}
                                            </span>
                                        @endif
                                        <div class="text-[11px] text-gray-400 mt-0.5">
                                            TP {{ $reg->admissionPeriod?->academic_year ?? '-' }} &bull; {{ $reg->admissionPeriod?->name ?? '-' }}
                                        </div>
                                    </td>

                                    {{-- Status Pendaftaran --}}
                                    <td class="py-3.5 px-4 whitespace-nowrap">
                                        @if ($reg->isSubmitted())
                                            <x-ui.badge variant="success" dot>Terkirim (Resmi)</x-ui.badge>
                                        @else
                                            <x-ui.badge variant="warning" dot>Draf</x-ui.badge>
                                        @endif
                                    </td>

                                    {{-- Tanggal Registrasi / Submit --}}
                                    <td class="py-3.5 px-4 text-xs text-gray-500 whitespace-nowrap">
                                        @if ($reg->submitted_at)
                                            <div>Kirim: <strong class="text-gray-700">{{ $reg->submitted_at->setTimezone('Asia/Jakarta')->translatedFormat('d M Y, H:i') }} WIB</strong></div>
                                        @else
                                            <div>Dibuat: {{ $reg->created_at ? $reg->created_at->setTimezone('Asia/Jakarta')->translatedFormat('d M Y, H:i') . ' WIB' : '-' }}</div>
                                        @endif
                                    </td>

                                    {{-- Tombol Aksi Detail --}}
                                    <td class="py-3.5 px-4 sm:px-6 text-right whitespace-nowrap">
                                        <a
                                            href="{{ route('admin.applicants.show', $reg) }}"
                                            class="inline-flex items-center gap-1.5 px-3 py-1.5 rounded-lg text-xs font-semibold text-primary-700 bg-primary-50 hover:bg-primary-100 border border-primary-200 transition-colors"
                                        >
                                            <svg class="w-3.5 h-3.5 text-primary-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z" />
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z" />
                                            </svg>
                                            <span>Detail</span>
                                        </a>
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>

                {{-- Pagination Footer --}}
                <div class="px-6 py-4 border-t border-gray-100">
                    {{ $registrations->links() }}
                </div>
            @else
                <div class="p-12 text-center">
                    <div class="w-14 h-14 rounded-full bg-gray-100 text-gray-400 flex items-center justify-center mx-auto mb-3">
                        <svg class="w-7 h-7" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" />
                        </svg>
                    </div>
                    <h3 class="text-base font-bold text-gray-900">Tidak Ada Pendaftar Ditemukan</h3>
                    <p class="text-xs sm:text-sm text-gray-500 mt-1 max-w-md mx-auto">
                        @if ($filters['search'] || $filters['period_id'] || $filters['program_id'] || $filters['status'])
                            Tidak ditemukan pendaftaran yang cocok dengan kriteria filter pencarian Anda. Silakan ubah filter atau gunakan tombol reset.
                        @else
                            Belum ada calon peserta didik baru yang melakukan pendaftaran pada sistem.
                        @endif
                    </p>
                    @if ($filters['search'] || $filters['period_id'] || $filters['program_id'] || $filters['status'])
                        <div class="mt-4">
                            <a
                                href="{{ route('admin.applicants.index') }}"
                                class="inline-flex items-center px-4 py-2 rounded-lg text-xs font-semibold bg-primary-600 text-white hover:bg-primary-700 transition-colors shadow-xs"
                            >
                                Reset Pencarian
                            </a>
                        </div>
                    @endif
                </div>
            @endif
        </div>
    </div>
</x-layouts.admin>
