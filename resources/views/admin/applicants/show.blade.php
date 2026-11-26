<x-layouts.admin title="Detail Pendaftar - {{ $registration->registration_number ?? 'Draf' }}">
    <x-slot:header>
        <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
            <div class="flex items-center gap-3">
                <a
                    href="{{ route('admin.applicants.index') }}"
                    class="p-2 rounded-lg text-gray-500 hover:text-gray-700 hover:bg-gray-100 transition-colors"
                    title="Kembali ke Daftar Pendaftar"
                >
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18" />
                    </svg>
                </a>
                <div>
                    <div class="flex items-center gap-2">
                        <h1 class="text-xl sm:text-2xl font-bold text-gray-900 tracking-tight">
                            {{ $applicant?->full_name ?? 'Calon Siswa' }}
                        </h1>
                        @if ($registration->isSubmitted())
                            <x-ui.badge variant="success" dot>Terkirim (Resmi)</x-ui.badge>
                            @if ($registration->isVerified())
                                <span class="inline-flex items-center gap-1 px-2 py-0.5 rounded-full text-xs font-semibold bg-emerald-100 text-emerald-800">
                                    <svg class="w-3.5 h-3.5 text-emerald-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/></svg>
                                    Terverifikasi (#{{ $registration->queue_sequence }})
                                </span>
                            @else
                                <span class="inline-flex items-center px-2 py-0.5 rounded-full text-xs font-semibold bg-amber-100 text-amber-800">
                                    Menunggu Pengesahan
                                </span>
                            @endif
                        @else
                            <x-ui.badge variant="warning" dot>Draf Pendaftaran</x-ui.badge>
                        @endif
                    </div>
                    <p class="text-xs sm:text-sm font-mono text-gray-500 mt-0.5">
                        No. Registrasi: <strong class="text-gray-800">{{ $registration->registration_number ?? 'Belum Terbit (Menunggu Pengesahan)' }}</strong>
                        &bull; Periode: TP {{ $period?->academic_year ?? '-' }}
                    </p>
                </div>
            </div>

            <div class="flex items-center gap-2">
                @if ($registration->isSubmitted() && !$registration->isVerified())
                    <form method="POST" action="{{ route('admin.document-verification.approve-registration', $registration) }}" onsubmit="return confirm('Sahkan seluruh berkas dan terbitkan nomor urut pendaftaran untuk calon siswa ini?');">
                        @csrf
                        <button type="submit" class="inline-flex items-center gap-1.5 px-3.5 py-2 rounded-lg text-xs font-semibold text-white bg-primary-700 hover:bg-primary-800 shadow-xs transition-colors">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                            <span>Sahkan Berkas & Terbitkan Nomor</span>
                        </button>
                    </form>
                @endif
                @can('reopen_registration')
                    @if ($registration->isSubmitted())
                        <a
                            href="{{ route('admin.registrations.reopen.create', $registration) }}"
                            class="inline-flex items-center gap-1.5 px-3 py-2 rounded-lg text-xs font-semibold text-amber-800 bg-amber-50 hover:bg-amber-100 border border-amber-200 transition-colors"
                        >
                            <svg class="w-4 h-4 text-amber-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 11V7a4 4 0 118 0m-4 8v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2z" />
                            </svg>
                            <span>Buka Ulang Akses</span>
                        </a>
                    @endif
                @endcan
                <a
                    href="{{ route('admin.applicants.index') }}"
                    class="inline-flex items-center gap-1.5 px-3.5 py-2 rounded-lg text-xs font-semibold bg-white border border-gray-300 text-gray-700 hover:bg-gray-50 transition-colors"
                >
                    <span>Daftar Pendaftar</span>
                </a>
            </div>
        </div>
    </x-slot:header>

    <div class="space-y-6">
        {{-- Banner Ringkasan Status & Program --}}
        <div class="rounded-xl border border-gray-200 bg-white p-5 shadow-xs">
            <div class="grid grid-cols-2 sm:grid-cols-4 gap-4 divide-y sm:divide-y-0 sm:divide-x divide-gray-100 text-center sm:text-left">
                <div class="sm:pr-4">
                    <span class="text-[11px] font-semibold uppercase tracking-wider text-gray-400">Status Pendaftaran</span>
                    <div class="mt-1 font-bold text-sm text-gray-900 flex items-center justify-center sm:justify-start gap-1.5">
                        @if ($registration->isSubmitted())
                            <span class="w-2 h-2 rounded-full bg-emerald-500"></span>
                            <span class="text-emerald-700">Terkirim Resmi</span>
                        @else
                            <span class="w-2 h-2 rounded-full bg-amber-500"></span>
                            <span class="text-amber-700">Draf Formulir</span>
                        @endif
                    </div>
                </div>
                <div class="pt-3 sm:pt-0 sm:px-4">
                    <span class="text-[11px] font-semibold uppercase tracking-wider text-gray-400">Program Pilihan</span>
                    <div class="mt-1 font-bold text-sm text-gray-900">
                        {{ $registration->admissionPeriodProgram?->program?->name ?? 'Belum Dipilih' }}
                    </div>
                </div>
                <div class="pt-3 sm:pt-0 sm:px-4">
                    <span class="text-[11px] font-semibold uppercase tracking-wider text-gray-400">Periode PPDB</span>
                    <div class="mt-1 font-bold text-sm text-gray-900">
                        TP {{ $period?->academic_year ?? '-' }}
                    </div>
                </div>
                <div class="pt-3 sm:pt-0 sm:pl-4">
                    <span class="text-[11px] font-semibold uppercase tracking-wider text-gray-400">Revisi Data</span>
                    <div class="mt-1 font-bold text-sm text-gray-900">
                        Versi {{ $registration->data_revision ?? 1 }}
                    </div>
                </div>
            </div>
        </div>

        {{-- Grid 2 Kolom untuk 11 Section --}}
        <div class="grid grid-cols-1 lg:grid-cols-2 gap-6 items-start">
            {{-- KOLOM KIRI --}}
            <div class="space-y-6">
                {{-- 1. IDENTITAS CALON SISWA --}}
                <div class="bg-white rounded-xl border border-gray-200 shadow-xs overflow-hidden">
                    <div class="px-5 py-3.5 bg-gray-50/70 border-b border-gray-200 flex items-center justify-between">
                        <div class="flex items-center gap-2">
                            <span class="w-6 h-6 rounded-md bg-primary-100 text-primary-700 flex items-center justify-center text-xs font-bold">1</span>
                            <h2 class="text-sm font-bold text-gray-900">Identitas Calon Siswa</h2>
                        </div>
                    </div>
                    <div class="p-5">
                        <dl class="grid grid-cols-1 sm:grid-cols-2 gap-x-4 gap-y-3.5 text-xs">
                            <div>
                                <dt class="text-gray-500 font-medium">Nama Lengkap</dt>
                                <dd class="mt-0.5 font-semibold text-gray-900 text-sm">{{ $applicant?->full_name ?? '-' }}</dd>
                            </div>
                            <div>
                                <dt class="text-gray-500 font-medium">NIK Siswa (Bermasker)</dt>
                                <dd class="mt-0.5 font-mono font-semibold text-gray-900">{{ $applicant?->user?->getMaskedNik() ?? '-' }}</dd>
                            </div>
                            <div>
                                <dt class="text-gray-500 font-medium">Jenis Kelamin</dt>
                                <dd class="mt-0.5 text-gray-900">
                                    @if ($applicant?->gender)
                                        {{ $applicant->gender->value === 'L' ? 'Laki-laki (L)' : 'Perempuan (P)' }}
                                    @else
                                        <span class="text-gray-400 italic">Belum diisi</span>
                                    @endif
                                </dd>
                            </div>
                            <div>
                                <dt class="text-gray-500 font-medium">NISN</dt>
                                <dd class="mt-0.5 font-mono text-gray-900">{{ $applicant?->nisn ?: '-' }}</dd>
                            </div>
                            <div>
                                <dt class="text-gray-500 font-medium">Tempat, Tanggal Lahir</dt>
                                <dd class="mt-0.5 text-gray-900">
                                    {{ $applicant?->birth_place ?? '-' }},
                                    {{ $applicant?->birth_date ? $applicant->birth_date->translatedFormat('d F Y') : '-' }}
                                </dd>
                            </div>
                            <div>
                                <dt class="text-gray-500 font-medium">Usia Saat Acuan PPDB ({{ $period?->age_reference_date ? \Carbon\Carbon::parse($period->age_reference_date)->translatedFormat('d M Y') : 'Mulai Periode' }})</dt>
                                <dd class="mt-0.5 font-semibold text-primary-700">
                                    @if ($ageInfo && !empty($ageInfo['formatted']))
                                        {{ $ageInfo['formatted'] }}
                                    @else
                                        <span class="text-gray-400 font-normal italic">Belum dapat dihitung</span>
                                    @endif
                                </dd>
                            </div>
                            <div>
                                <dt class="text-gray-500 font-medium">Anak Ke-</dt>
                                <dd class="mt-0.5 text-gray-900">Ke-{{ $applicant?->child_order ?? '-' }} dari {{ $applicant?->siblings_count ?? '-' }} bersaudara</dd>
                            </div>
                            <div>
                                <dt class="text-gray-500 font-medium">Nomor WhatsApp Akun</dt>
                                <dd class="mt-0.5 font-mono text-gray-900">{{ $applicant?->user?->phone ?? '-' }}</dd>
                            </div>
                            <div>
                                <dt class="text-gray-500 font-medium">No. Handphone Calon Siswa</dt>
                                <dd class="mt-0.5 font-mono text-gray-900">{{ $applicant?->student_phone ?? '-' }}</dd>
                            </div>
                        </dl>
                    </div>
                </div>

                {{-- 2. ALAMAT DOMISILI --}}
                <div class="bg-white rounded-xl border border-gray-200 shadow-xs overflow-hidden">
                    <div class="px-5 py-3.5 bg-gray-50/70 border-b border-gray-200 flex items-center justify-between">
                        <div class="flex items-center gap-2">
                            <span class="w-6 h-6 rounded-md bg-primary-100 text-primary-700 flex items-center justify-center text-xs font-bold">2</span>
                            <h2 class="text-sm font-bold text-gray-900">Alamat Tempat Tinggal</h2>
                        </div>
                    </div>
                    <div class="p-5">
                        @if ($applicant?->address)
                            <dl class="grid grid-cols-1 sm:grid-cols-2 gap-x-4 gap-y-3.5 text-xs">
                                <div class="sm:col-span-2">
                                    <dt class="text-gray-500 font-medium">Alamat Jalan / Dusun</dt>
                                    <dd class="mt-0.5 text-gray-900 font-medium">{{ $applicant->address->address_line ?? '-' }}</dd>
                                </div>
                                <div>
                                    <dt class="text-gray-500 font-medium">RT / RW</dt>
                                    <dd class="mt-0.5 text-gray-900">RT {{ $applicant->address->rt ?? '-' }} / RW {{ $applicant->address->rw ?? '-' }}</dd>
                                </div>
                                <div>
                                    <dt class="text-gray-500 font-medium">Kelurahan / Desa</dt>
                                    <dd class="mt-0.5 text-gray-900">{{ $applicant->address->kelurahan ?? '-' }}</dd>
                                </div>
                                <div>
                                    <dt class="text-gray-500 font-medium">Kecamatan</dt>
                                    <dd class="mt-0.5 text-gray-900">{{ $applicant->address->kecamatan ?? '-' }}</dd>
                                </div>
                                <div>
                                    <dt class="text-gray-500 font-medium">Kota / Kabupaten</dt>
                                    <dd class="mt-0.5 text-gray-900">{{ $applicant->address->city ?? '-' }}</dd>
                                </div>
                                <div>
                                    <dt class="text-gray-500 font-medium">Provinsi</dt>
                                    <dd class="mt-0.5 text-gray-900">{{ $applicant->address->province ?? '-' }}</dd>
                                </div>
                                <div>
                                    <dt class="text-gray-500 font-medium">Kode Pos</dt>
                                    <dd class="mt-0.5 font-mono text-gray-900">{{ $applicant->address->postal_code ?? '-' }}</dd>
                                </div>
                                <div>
                                    <dt class="text-gray-500 font-medium">Perkiraan Jarak ke Madrasah</dt>
                                    <dd class="mt-0.5 font-semibold text-gray-900">
                                        {{ $applicant->address->distance_to_school_km ? $applicant->address->distance_to_school_km . ' km' : '-' }}
                                    </dd>
                                </div>
                            </dl>
                        @else
                            <p class="text-xs text-gray-400 italic">Data alamat domisili belum dilengkapi oleh pendaftar.</p>
                        @endif
                    </div>
                </div>

                {{-- 3. DATA AYAH KANDUNG --}}
                <div class="bg-white rounded-xl border border-gray-200 shadow-xs overflow-hidden">
                    <div class="px-5 py-3.5 bg-gray-50/70 border-b border-gray-200 flex items-center justify-between">
                        <div class="flex items-center gap-2">
                            <span class="w-6 h-6 rounded-md bg-primary-100 text-primary-700 flex items-center justify-center text-xs font-bold">3</span>
                            <h2 class="text-sm font-bold text-gray-900">Data Ayah Kandung</h2>
                        </div>
                    </div>
                    <div class="p-5">
                        @if ($applicant?->father)
                            <dl class="grid grid-cols-1 sm:grid-cols-2 gap-x-4 gap-y-3.5 text-xs">
                                <div>
                                    <dt class="text-gray-500 font-medium">Nama Ayah</dt>
                                    <dd class="mt-0.5 font-semibold text-gray-900">{{ $applicant->father->name ?? '-' }}</dd>
                                </div>
                                <div>
                                    <dt class="text-gray-500 font-medium">NIK Ayah (Bermasker)</dt>
                                    <dd class="mt-0.5 font-mono text-gray-900">
                                        {{ $applicant->father->nik ? \App\Support\NikManager::mask($applicant->father->nik) : '-' }}
                                    </dd>
                                </div>
                                <div>
                                    <dt class="text-gray-500 font-medium">Status Keberadaan</dt>
                                    <dd class="mt-0.5 text-gray-900">
                                        <span class="inline-flex items-center px-2 py-0.5 rounded text-[11px] font-medium {{ $applicant->father->is_alive ? 'bg-green-50 text-green-700' : 'bg-gray-100 text-gray-600' }}">
                                            {{ $applicant->father->is_alive ? 'Masih Hidup' : 'Meninggal Dunia' }}
                                        </span>
                                    </dd>
                                </div>
                                <div>
                                    <dt class="text-gray-500 font-medium">Pendidikan Terakhir</dt>
                                    <dd class="mt-0.5 text-gray-900">{{ $applicant->father->education ?? '-' }}</dd>
                                </div>
                                <div>
                                    <dt class="text-gray-500 font-medium">Pekerjaan</dt>
                                    <dd class="mt-0.5 text-gray-900">{{ $applicant->father->occupation ?? '-' }}</dd>
                                </div>
                                <div>
                                    <dt class="text-gray-500 font-medium">Penghasilan Bulanan</dt>
                                    <dd class="mt-0.5 text-gray-900">{{ $applicant->father->monthly_income ?? '-' }}</dd>
                                </div>
                                <div>
                                    <dt class="text-gray-500 font-medium">Nomor Telepon / WhatsApp</dt>
                                    <dd class="mt-0.5 font-mono text-gray-900">{{ $applicant->father->phone ?? '-' }}</dd>
                                </div>
                            </dl>
                        @else
                            <p class="text-xs text-gray-400 italic">Data ayah kandung belum diisi.</p>
                        @endif
                    </div>
                </div>

                {{-- 4. DATA IBU KANDUNG --}}
                <div class="bg-white rounded-xl border border-gray-200 shadow-xs overflow-hidden">
                    <div class="px-5 py-3.5 bg-gray-50/70 border-b border-gray-200 flex items-center justify-between">
                        <div class="flex items-center gap-2">
                            <span class="w-6 h-6 rounded-md bg-primary-100 text-primary-700 flex items-center justify-center text-xs font-bold">4</span>
                            <h2 class="text-sm font-bold text-gray-900">Data Ibu Kandung</h2>
                        </div>
                    </div>
                    <div class="p-5">
                        @if ($applicant?->mother)
                            <dl class="grid grid-cols-1 sm:grid-cols-2 gap-x-4 gap-y-3.5 text-xs">
                                <div>
                                    <dt class="text-gray-500 font-medium">Nama Ibu</dt>
                                    <dd class="mt-0.5 font-semibold text-gray-900">{{ $applicant->mother->name ?? '-' }}</dd>
                                </div>
                                <div>
                                    <dt class="text-gray-500 font-medium">NIK Ibu (Bermasker)</dt>
                                    <dd class="mt-0.5 font-mono text-gray-900">
                                        {{ $applicant->mother->nik ? \App\Support\NikManager::mask($applicant->mother->nik) : '-' }}
                                    </dd>
                                </div>
                                <div>
                                    <dt class="text-gray-500 font-medium">Status Keberadaan</dt>
                                    <dd class="mt-0.5 text-gray-900">
                                        <span class="inline-flex items-center px-2 py-0.5 rounded text-[11px] font-medium {{ $applicant->mother->is_alive ? 'bg-green-50 text-green-700' : 'bg-gray-100 text-gray-600' }}">
                                            {{ $applicant->mother->is_alive ? 'Masih Hidup' : 'Meninggal Dunia' }}
                                        </span>
                                    </dd>
                                </div>
                                <div>
                                    <dt class="text-gray-500 font-medium">Pendidikan Terakhir</dt>
                                    <dd class="mt-0.5 text-gray-900">{{ $applicant->mother->education ?? '-' }}</dd>
                                </div>
                                <div>
                                    <dt class="text-gray-500 font-medium">Pekerjaan</dt>
                                    <dd class="mt-0.5 text-gray-900">{{ $applicant->mother->occupation ?? '-' }}</dd>
                                </div>
                                <div>
                                    <dt class="text-gray-500 font-medium">Penghasilan Bulanan</dt>
                                    <dd class="mt-0.5 text-gray-900">{{ $applicant->mother->monthly_income ?? '-' }}</dd>
                                </div>
                                <div>
                                    <dt class="text-gray-500 font-medium">Nomor Telepon / WhatsApp</dt>
                                    <dd class="mt-0.5 font-mono text-gray-900">{{ $applicant->mother->phone ?? '-' }}</dd>
                                </div>
                            </dl>
                        @else
                            <p class="text-xs text-gray-400 italic">Data ibu kandung belum diisi.</p>
                        @endif
                    </div>
                </div>

                {{-- 5. DATA WALI (OPSIONAL) --}}
                <div class="bg-white rounded-xl border border-gray-200 shadow-xs overflow-hidden">
                    <div class="px-5 py-3.5 bg-gray-50/70 border-b border-gray-200 flex items-center justify-between">
                        <div class="flex items-center gap-2">
                            <span class="w-6 h-6 rounded-md bg-primary-100 text-primary-700 flex items-center justify-center text-xs font-bold">5</span>
                            <h2 class="text-sm font-bold text-gray-900">Data Wali Murid</h2>
                        </div>
                    </div>
                    <div class="p-5">
                        @if ($applicant?->guardian)
                            <dl class="grid grid-cols-1 sm:grid-cols-2 gap-x-4 gap-y-3.5 text-xs">
                                <div>
                                    <dt class="text-gray-500 font-medium">Nama Wali</dt>
                                    <dd class="mt-0.5 font-semibold text-gray-900">{{ $applicant->guardian->name ?? '-' }}</dd>
                                </div>
                                <div>
                                    <dt class="text-gray-500 font-medium">NIK Wali (Bermasker)</dt>
                                    <dd class="mt-0.5 font-mono text-gray-900">
                                        {{ $applicant->guardian->nik ? \App\Support\NikManager::mask($applicant->guardian->nik) : '-' }}
                                    </dd>
                                </div>
                                <div>
                                    <dt class="text-gray-500 font-medium">Pendidikan Terakhir</dt>
                                    <dd class="mt-0.5 text-gray-900">{{ $applicant->guardian->education ?? '-' }}</dd>
                                </div>
                                <div>
                                    <dt class="text-gray-500 font-medium">Pekerjaan</dt>
                                    <dd class="mt-0.5 text-gray-900">{{ $applicant->guardian->occupation ?? '-' }}</dd>
                                </div>
                                <div>
                                    <dt class="text-gray-500 font-medium">Penghasilan Bulanan</dt>
                                    <dd class="mt-0.5 text-gray-900">{{ $applicant->guardian->monthly_income ?? '-' }}</dd>
                                </div>
                                <div>
                                    <dt class="text-gray-500 font-medium">Nomor Telepon / WhatsApp</dt>
                                    <dd class="mt-0.5 font-mono text-gray-900">{{ $applicant->guardian->phone ?? '-' }}</dd>
                                </div>
                            </dl>
                        @else
                            <div class="rounded-lg bg-gray-50 p-3 text-xs text-gray-500 flex items-center gap-2">
                                <svg class="w-4 h-4 text-gray-400 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                                </svg>
                                <span>Tidak menggunakan data wali (Siswa diasuh langsung oleh orang tua kandung).</span>
                            </div>
                        @endif
                    </div>
                </div>

                {{-- 6. PENDIDIKAN SEBELUMNYA --}}
                <div class="bg-white rounded-xl border border-gray-200 shadow-xs overflow-hidden">
                    <div class="px-5 py-3.5 bg-gray-50/70 border-b border-gray-200 flex items-center justify-between">
                        <div class="flex items-center gap-2">
                            <span class="w-6 h-6 rounded-md bg-primary-100 text-primary-700 flex items-center justify-center text-xs font-bold">6</span>
                            <h2 class="text-sm font-bold text-gray-900">Pendidikan Sebelumnya (TK/RA)</h2>
                        </div>
                    </div>
                    <div class="p-5">
                        @if ($applicant?->education)
                            <dl class="grid grid-cols-1 sm:grid-cols-2 gap-x-4 gap-y-3.5 text-xs">
                                <div>
                                    <dt class="text-gray-500 font-medium">Jenjang Pendidikan Asal</dt>
                                    <dd class="mt-0.5 font-semibold text-gray-900 uppercase">
                                        {{ $applicant->education->education_level?->value ?? '-' }}
                                    </dd>
                                </div>
                                <div>
                                    <dt class="text-gray-500 font-medium">Nama Sekolah / Lembaga Asal</dt>
                                    <dd class="mt-0.5 font-medium text-gray-900">{{ $applicant->education->school_name ?: '-' }}</dd>
                                </div>
                                <div>
                                    <dt class="text-gray-500 font-medium">NPSN Sekolah Asal</dt>
                                    <dd class="mt-0.5 font-mono text-gray-900">{{ $applicant->education->npsn ?: '-' }}</dd>
                                </div>
                                <div>
                                    <dt class="text-gray-500 font-medium">Kota / Kabupaten Sekolah</dt>
                                    <dd class="mt-0.5 text-gray-900">{{ $applicant->education->city ?: '-' }}</dd>
                                </div>
                            </dl>
                        @else
                            <p class="text-xs text-gray-400 italic">Riwayat pendidikan sebelumnya belum dilengkapi.</p>
                        @endif
                    </div>
                </div>
            </div>

            {{-- KOLOM KANAN --}}
            <div class="space-y-6">
                {{-- 7. PROGRAM & PERIODE PENDAFTARAN --}}
                <div class="bg-white rounded-xl border border-gray-200 shadow-xs overflow-hidden">
                    <div class="px-5 py-3.5 bg-gray-50/70 border-b border-gray-200 flex items-center justify-between">
                        <div class="flex items-center gap-2">
                            <span class="w-6 h-6 rounded-md bg-primary-100 text-primary-700 flex items-center justify-center text-xs font-bold">7</span>
                            <h2 class="text-sm font-bold text-gray-900">Program & Periode Pendaftaran</h2>
                        </div>
                    </div>
                    <div class="p-5">
                        <dl class="grid grid-cols-1 sm:grid-cols-2 gap-x-4 gap-y-3.5 text-xs">
                            <div>
                                <dt class="text-gray-500 font-medium">Nama Periode</dt>
                                <dd class="mt-0.5 font-semibold text-gray-900">{{ $period?->name ?? '-' }}</dd>
                            </div>
                            <div>
                                <dt class="text-gray-500 font-medium">Tahun Ajaran</dt>
                                <dd class="mt-0.5 text-gray-900 font-bold">TP {{ $period?->academic_year ?? '-' }}</dd>
                            </div>
                            <div>
                                <dt class="text-gray-500 font-medium">Program Pendidikan Pilihan</dt>
                                <dd class="mt-0.5 font-bold text-primary-800">
                                    {{ $registration->admissionPeriodProgram?->program?->name ?? 'Belum Memilih' }}
                                </dd>
                            </div>
                            <div>
                                <dt class="text-gray-500 font-medium">Kode Program</dt>
                                <dd class="mt-0.5 font-mono text-gray-700">
                                    {{ $registration->admissionPeriodProgram?->program?->code ?? '-' }}
                                </dd>
                            </div>
                            <div>
                                <dt class="text-gray-500 font-medium">Waktu Dibuka</dt>
                                <dd class="mt-0.5 text-gray-900">
                                    {{ $period?->start_date ? \Carbon\Carbon::parse($period->start_date)->setTimezone('Asia/Jakarta')->translatedFormat('d F Y, H:i') . ' WIB' : '-' }}
                                </dd>
                            </div>
                            <div>
                                <dt class="text-gray-500 font-medium">Batas Waktu Ditutup</dt>
                                <dd class="mt-0.5 text-gray-900">
                                    {{ $period?->end_date ? \Carbon\Carbon::parse($period->end_date)->setTimezone('Asia/Jakarta')->translatedFormat('d F Y, H:i') . ' WIB' : '-' }}
                                </dd>
                            </div>
                        </dl>
                    </div>
                </div>

                {{-- 8. DOKUMEN PERSYARATAN & STATUS KELENGKAPAN --}}
                <div class="bg-white rounded-xl border border-gray-200 shadow-xs overflow-hidden">
                    <div class="px-5 py-3.5 bg-gray-50/70 border-b border-gray-200 flex items-center justify-between">
                        <div class="flex items-center gap-2">
                            <span class="w-6 h-6 rounded-md bg-primary-100 text-primary-700 flex items-center justify-center text-xs font-bold">8</span>
                            <h2 class="text-sm font-bold text-gray-900">Berkas Dokumen Pendaftaran</h2>
                        </div>
                        <span class="text-xs font-semibold text-gray-600">
                            {{ $registration->documents->count() }} Terunggah
                        </span>
                    </div>

                    @if ($registration->documents->isNotEmpty())
                        <div class="divide-y divide-gray-100">
                            @foreach ($registration->documents as $doc)
                                <div class="p-4 hover:bg-gray-50/70 transition-colors flex flex-col sm:flex-row sm:items-center sm:justify-between gap-3 text-xs">
                                    <div class="space-y-1">
                                        <div class="flex items-center gap-2">
                                            <span class="font-bold text-gray-900">{{ $doc->requirement?->name ?? 'Dokumen' }}</span>
                                            @if ($doc->requirement?->is_required)
                                                <span class="px-1.5 py-0.5 rounded text-[10px] font-semibold bg-red-50 text-red-700 border border-red-200">Wajib</span>
                                            @else
                                                <span class="px-1.5 py-0.5 rounded text-[10px] font-semibold bg-gray-100 text-gray-600">Opsional</span>
                                            @endif
                                        </div>
                                        <div class="text-gray-500 text-[11px] flex flex-wrap items-center gap-x-2">
                                            <span>File: <strong class="text-gray-700">{{ $doc->original_name }}</strong></span>
                                            <span>&bull;</span>
                                            <span>Ukuran: {{ number_format($doc->file_size_bytes / 1024, 1, ',', '.') }} KB</span>
                                            <span>&bull;</span>
                                            <span>Unggah: {{ $doc->uploaded_at ? $doc->uploaded_at->setTimezone('Asia/Jakarta')->translatedFormat('d/m/Y H:i') : '-' }}</span>
                                        </div>
                                        @if ($doc->revisions->isNotEmpty())
                                            <div class="text-[11px] text-amber-700 font-medium">
                                                Terdapat {{ $doc->revisions->count() }} riwayat revisi berkas terdahulu.
                                            </div>
                                        @endif
                                    </div>

                                    <div class="flex items-center gap-2 shrink-0">
                                        {{-- Tombol Pratinjau --}}
                                        <a
                                            href="{{ route('admin.applicants.documents.preview', [$registration, $doc]) }}"
                                            target="_blank"
                                            class="inline-flex items-center gap-1 px-2.5 py-1.5 rounded-lg text-xs font-semibold text-primary-700 bg-primary-50 hover:bg-primary-100 border border-primary-200 transition-colors"
                                            title="Lihat Pratinjau Dokumen"
                                        >
                                            <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z" />
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z" />
                                            </svg>
                                            <span>Lihat</span>
                                        </a>

                                        {{-- Tombol Unduh --}}
                                        <a
                                            href="{{ route('admin.applicants.documents.download', [$registration, $doc]) }}"
                                            class="inline-flex items-center gap-1 px-2.5 py-1.5 rounded-lg text-xs font-semibold text-gray-700 bg-white hover:bg-gray-50 border border-gray-300 transition-colors"
                                            title="Unduh Berkas Asli"
                                        >
                                            <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-4l-4 4m0 0l-4-4m4 4V4" />
                                            </svg>
                                            <span>Unduh</span>
                                        </a>
                                    </div>
                                </div>
                            @endforeach
                        </div>
                    @else
                        <div class="p-6 text-center text-xs text-gray-500 italic">
                            Belum ada dokumen yang diunggah oleh calon siswa.
                        </div>
                    @endif
                </div>

                {{-- 9. STATUS PENDAFTARAN & PERSETUJUAN ORANG TUA --}}
                <div class="bg-white rounded-xl border border-gray-200 shadow-xs overflow-hidden">
                    <div class="px-5 py-3.5 bg-gray-50/70 border-b border-gray-200 flex items-center justify-between">
                        <div class="flex items-center gap-2">
                            <span class="w-6 h-6 rounded-md bg-primary-100 text-primary-700 flex items-center justify-center text-xs font-bold">9</span>
                            <h2 class="text-sm font-bold text-gray-900">Status & Persetujuan Orang Tua</h2>
                        </div>
                    </div>
                    <div class="p-5">
                        <dl class="grid grid-cols-1 sm:grid-cols-2 gap-x-4 gap-y-3.5 text-xs">
                            <div>
                                <dt class="text-gray-500 font-medium">Status Pendaftaran</dt>
                                <dd class="mt-0.5">
                                    @if ($registration->isSubmitted())
                                        <span class="inline-flex items-center px-2 py-0.5 rounded text-xs font-bold bg-green-50 text-green-700 border border-green-200">
                                            SUBMITTED (Terkirim Resmi)
                                        </span>
                                    @else
                                        <span class="inline-flex items-center px-2 py-0.5 rounded text-xs font-bold bg-amber-50 text-amber-700 border border-amber-200">
                                            DRAFT (Dalam Pengisian)
                                        </span>
                                    @endif
                                </dd>
                            </div>
                            <div>
                                <dt class="text-gray-500 font-medium">Waktu Pengiriman Resmi</dt>
                                <dd class="mt-0.5 font-medium text-gray-900">
                                    {{ $registration->submitted_at ? $registration->submitted_at->setTimezone('Asia/Jakarta')->translatedFormat('d F Y, H:i') . ' WIB' : 'Belum Dikirim' }}
                                </dd>
                            </div>

                            <div class="sm:col-span-2 pt-2 border-t border-gray-100">
                                <dt class="text-gray-500 font-medium">Status Pengesahan & Jadwal Wawancara</dt>
                                <dd class="mt-1 flex flex-wrap items-center gap-2">
                                    @if ($registration->isVerified())
                                        <span class="inline-flex items-center gap-1 px-2.5 py-1 rounded text-xs font-bold bg-emerald-50 text-emerald-800 border border-emerald-200">
                                            <svg class="w-3.5 h-3.5 text-emerald-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/></svg>
                                            Disahkan Panitia (No. Reg: {{ $registration->registration_number }})
                                        </span>
                                        <span class="text-xs text-gray-600">Urut Ke-{{ $registration->queue_sequence }}</span>
                                    @else
                                        <span class="inline-flex items-center px-2.5 py-1 rounded text-xs font-semibold bg-amber-50 text-amber-800 border border-amber-200">
                                            Menunggu Pengesahan Berkas oleh Panitia
                                        </span>
                                    @endif
                                </dd>
                            </div>

                            @if ($registration->isVerified())
                                <div>
                                    <dt class="text-gray-500 font-medium">Gelombang / Sesi Wawancara</dt>
                                    <dd class="mt-0.5 font-bold text-primary-700">
                                        {{ $registration->interviewBatch?->name ?? 'Belum Ditentukan' }}
                                    </dd>
                                </div>
                                <div>
                                    <dt class="text-gray-500 font-medium">Jadwal Wawancara</dt>
                                    <dd class="mt-0.5 text-gray-900">
                                        {{ $registration->interviewBatch?->interview_date ? $registration->interviewBatch->interview_date->setTimezone('Asia/Jakarta')->translatedFormat('l, d F Y') : '-' }}
                                        @if ($registration->interviewBatch?->start_time)
                                            ({{ substr((string) $registration->interviewBatch->start_time, 0, 5) }} - {{ substr((string) $registration->interviewBatch->end_time, 0, 5) }} WIB)
                                        @endif
                                    </dd>
                                </div>
                                <div>
                                    <dt class="text-gray-500 font-medium">Disahkan Oleh</dt>
                                    <dd class="mt-0.5 text-gray-700 font-medium">{{ $registration->verifier?->name ?? '-' }}</dd>
                                </div>
                                <div>
                                    <dt class="text-gray-500 font-medium">Waktu Pengesahan</dt>
                                    <dd class="mt-0.5 text-gray-700">{{ $registration->verified_at ? $registration->verified_at->setTimezone('Asia/Jakarta')->translatedFormat('d F Y, H:i') . ' WIB' : '-' }}</dd>
                                </div>
                            @endif

                            @if ($registration->consent)
                                <div class="sm:col-span-2 pt-2 border-t border-gray-100">
                                    <dt class="text-gray-500 font-medium">Nama Penyetuju Pernyataan</dt>
                                    <dd class="mt-0.5 font-bold text-gray-900">{{ $registration->consent->accepted_by_name ?? '-' }}</dd>
                                </div>
                                <div>
                                    <dt class="text-gray-500 font-medium">Waktu Persetujuan</dt>
                                    <dd class="mt-0.5 text-gray-700">
                                        {{ $registration->consent->agreed_at ? $registration->consent->agreed_at->setTimezone('Asia/Jakarta')->translatedFormat('d F Y, H:i') . ' WIB' : '-' }}
                                    </dd>
                                </div>
                                <div>
                                    <dt class="text-gray-500 font-medium">Versi Pernyataan</dt>
                                    <dd class="mt-0.5 font-mono text-gray-700">{{ $registration->consent->consent_version ?? '-' }}</dd>
                                </div>
                                <div class="sm:col-span-2">
                                    <dt class="text-gray-500 font-medium">Teks Pernyataan yang Disetujui</dt>
                                    <dd class="mt-1 p-2.5 rounded-lg bg-gray-50 text-[11px] text-gray-600 leading-relaxed border border-gray-100">
                                        {{ $registration->consent->statement_text ?? '-' }}
                                    </dd>
                                </div>
                            @endif
                        </dl>
                    </div>
                </div>

                {{-- 10. RIWAYAT PERUBAHAN DATA (AUDIT LOGS) --}}
                <div class="bg-white rounded-xl border border-gray-200 shadow-xs overflow-hidden">
                    <div class="px-5 py-3.5 bg-gray-50/70 border-b border-gray-200 flex items-center justify-between">
                        <div class="flex items-center gap-2">
                            <span class="w-6 h-6 rounded-md bg-primary-100 text-primary-700 flex items-center justify-center text-xs font-bold">10</span>
                            <h2 class="text-sm font-bold text-gray-900">Riwayat Perubahan Data</h2>
                        </div>
                        <span class="text-xs text-gray-500">{{ $registration->logs->count() }} Catatan</span>
                    </div>

                    @if ($registration->logs->isNotEmpty())
                        <div class="max-h-72 overflow-y-auto divide-y divide-gray-100">
                            @foreach ($registration->logs as $log)
                                <div class="p-4 text-xs space-y-1">
                                    <div class="flex items-center justify-between">
                                        <span class="font-bold text-gray-900 capitalize">{{ str_replace('_', ' ', $log->action) }}</span>
                                        <span class="text-[11px] text-gray-400 font-mono">
                                            {{ $log->created_at ? $log->created_at->setTimezone('Asia/Jakarta')->translatedFormat('d/m/Y H:i') : '-' }}
                                        </span>
                                    </div>
                                    <div class="text-gray-600 text-[11px]">
                                        Pelaku: <strong class="text-gray-800">{{ $log->user?->name ?? 'Sistem' }}</strong>
                                        &bull; Revisi: {{ $log->from_revision }} &rarr; {{ $log->to_revision }}
                                    </div>
                                    @if (!empty($log->changes['fields']))
                                        <div class="text-[11px] text-gray-500">
                                            Field diubah: <code class="px-1 py-0.5 rounded bg-gray-100 text-gray-700">{{ implode(', ', $log->changes['fields']) }}</code>
                                        </div>
                                    @endif
                                </div>
                            @endforeach
                        </div>
                    @else
                        <div class="p-6 text-center text-xs text-gray-500 italic">
                            Belum ada riwayat perubahan data pada formulir ini.
                        </div>
                    @endif
                </div>

                {{-- 11. RIWAYAT AKSES BUKA ULANG (REOPEN WINDOWS) --}}
                <div class="bg-white rounded-xl border border-gray-200 shadow-xs overflow-hidden">
                    <div class="px-5 py-3.5 bg-gray-50/70 border-b border-gray-200 flex items-center justify-between">
                        <div class="flex items-center gap-2">
                            <span class="w-6 h-6 rounded-md bg-primary-100 text-primary-700 flex items-center justify-center text-xs font-bold">11</span>
                            <h2 class="text-sm font-bold text-gray-900">Riwayat Buka Ulang Pendaftaran</h2>
                        </div>
                    </div>
                    <div class="p-5">
                        @if ($registration->reopenWindows->isNotEmpty())
                            <div class="divide-y divide-gray-100">
                                @foreach ($registration->reopenWindows as $window)
                                    <div class="py-3 text-xs space-y-1">
                                        <div class="flex items-center justify-between">
                                            <span class="font-bold text-gray-900">Akses Buka Ulang #{{ $window->id }}</span>
                                            @if ($window->closed_at)
                                                <span class="px-1.5 py-0.5 rounded text-[10px] font-semibold bg-gray-100 text-gray-600">Ditutup</span>
                                            @elseif ($window->expires_at && $window->expires_at->isPast())
                                                <span class="px-1.5 py-0.5 rounded text-[10px] font-semibold bg-red-50 text-red-700">Kedaluwarsa</span>
                                            @else
                                                <span class="px-1.5 py-0.5 rounded text-[10px] font-semibold bg-emerald-50 text-emerald-700">Aktif</span>
                                            @endif
                                        </div>
                                        <div class="text-gray-600">Alasan: {{ $window->reason }}</div>
                                        <div class="text-[11px] text-gray-400">
                                            Berlaku hingga: {{ $window->expires_at ? $window->expires_at->setTimezone('Asia/Jakarta')->translatedFormat('d M Y, H:i') . ' WIB' : '-' }}
                                        </div>
                                    </div>
                                @endforeach
                            </div>
                        @else
                            <div class="rounded-lg bg-gray-50 p-3 text-xs text-gray-500">
                                Formulir ini belum pernah diberikan dispensasi atau akses buka ulang pendaftaran oleh panitia.
                            </div>
                        @endif
                    </div>
                </div>
            </div>
        </div>
    </div>
</x-layouts.admin>
