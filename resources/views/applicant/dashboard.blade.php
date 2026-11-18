<x-layouts.app title="Dasbor Pendaftar — PPDB MIN 1 Kota Madiun">
    <div class="max-w-5xl mx-auto py-8 px-4 sm:px-6 lg:px-8 space-y-8">
        <!-- Welcome Banner -->
        <div class="bg-gradient-to-r from-primary-800 via-primary-700 to-secondary-800 rounded-2xl shadow-lg p-6 sm:p-8 text-white">
            <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
                <div>
                    <div class="inline-flex items-center gap-2 px-3 py-1 rounded-full text-xs font-semibold bg-white/20 backdrop-blur text-white mb-3">
                        <svg class="w-3.5 h-3.5 text-accent-300" fill="currentColor" viewBox="0 0 20 20">
                            <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd" />
                        </svg>
                        Akun Terverifikasi & Aktif
                    </div>
                    <h1 class="text-2xl sm:text-3xl font-extrabold tracking-tight">
                        Selamat Datang, {{ $applicant->full_name ?? $user->name }}
                    </h1>
                    <p class="mt-1 text-sm text-primary-100">
                        Portal Pendaftaran Peserta Didik Baru MIN 1 Kota Madiun
                    </p>
                </div>
                <div class="shrink-0">
                    <form method="POST" action="{{ route('logout') }}">
                        @csrf
                        <button
                            type="submit"
                            class="inline-flex items-center gap-2 px-4 py-2 rounded-xl text-xs font-bold uppercase tracking-wider bg-white/10 hover:bg-white/20 text-white border border-white/20 transition shadow-sm"
                        >
                            <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 16l4-4m0 0l-4-4m4 4H7m6 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h7a3 3 0 013 3v1" />
                            </svg>
                            Keluar
                        </button>
                    </form>
                </div>
            </div>
        </div>

        <!-- Alerts -->
        @if (session('success'))
            <x-ui.alert type="success" dismissible>{{ session('success') }}</x-ui.alert>
        @endif

        @if (session('error'))
            <x-ui.alert type="danger" dismissible>{{ session('error') }}</x-ui.alert>
        @endif

        @if ($registration?->activeReopenWindow)
            <x-ui.alert type="warning">
                Akses perubahan data dibuka sampai {{ $registration->activeReopenWindow->expires_at->translatedFormat('d F Y, H:i') }} WIB.
                {{ $registration->activeReopenWindow->reason }}
            </x-ui.alert>
        @endif

        <!-- Informasi Akun & Calon Siswa -->
        <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
            <x-ui.card class="p-5 border-neutral-200">
                <div class="flex items-center gap-3">
                    <div class="w-11 h-11 rounded-xl bg-primary-50 text-primary-600 flex items-center justify-center shrink-0">
                        <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 6H5a2 2 0 00-2 2v9a2 2 0 002 2h14a2 2 0 002-2V8a2 2 0 00-2-2h-5m-4 0V5a2 2 0 114 0v1m-4 0a2 2 0 104 0m-5 8a2 2 0 100-4 2 2 0 000 4zm0 0c1.306 0 2.417.835 2.83 2M9 14a3.001 3.001 0 00-2.83 2M15 11h3m-3 4h2" />
                        </svg>
                    </div>
                    <div>
                        <p class="text-[11px] text-neutral-500 font-semibold uppercase tracking-wider">NIK Calon Siswa</p>
                        <p class="text-sm font-mono font-bold text-neutral-800">{{ $maskedNik }}</p>
                    </div>
                </div>
            </x-ui.card>

            <x-ui.card class="p-5 border-neutral-200">
                <div class="flex items-center gap-3">
                    <div class="w-11 h-11 rounded-xl bg-secondary-50 text-secondary-600 flex items-center justify-center shrink-0">
                        <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 5a2 2 0 012-2h3.28a1 1 0 01.948.684l1.498 4.493a1 1 0 01-.502 1.21l-2.257 1.13a11.042 11.042 0 005.516 5.516l1.13-2.257a1 1 0 011.21-.502l4.493 1.498a1 1 0 01.684.949V19a2 2 0 01-2 2h-1C9.716 21 3 14.284 3 6V5z" />
                        </svg>
                    </div>
                    <div>
                        <p class="text-[11px] text-neutral-500 font-semibold uppercase tracking-wider">WhatsApp Terdaftar</p>
                        <p class="text-sm font-mono font-bold text-neutral-800">{{ $user->phone }}</p>
                    </div>
                </div>
            </x-ui.card>

            <x-ui.card class="p-5 border-neutral-200">
                <div class="flex items-center gap-3">
                    <div class="w-11 h-11 rounded-xl bg-accent-100 text-neutral-800 flex items-center justify-center shrink-0">
                        <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z" />
                        </svg>
                    </div>
                    <div>
                        <p class="text-[11px] text-neutral-500 font-semibold uppercase tracking-wider">Status Pendaftaran</p>
                        @if ($registration?->isSubmitted())
                            <span class="inline-flex items-center px-2 py-0.5 rounded-full bg-green-100 text-xs font-bold text-green-800">
                                {{ $registration->status->label() }}
                            </span>
                            <p class="mt-1 font-mono text-xs font-semibold text-neutral-700">{{ $registration->registration_number }}</p>
                        @elseif ($registration?->isDraft())
                            <span class="inline-flex items-center px-2 py-0.5 rounded-full bg-amber-100 text-xs font-bold text-amber-800">DRAFT</span>
                        @else
                            <span class="inline-flex items-center px-2 py-0.5 rounded-full {{ $completion['is_all_complete'] ? 'bg-green-100 text-green-800' : 'bg-amber-100 text-amber-800' }} text-xs font-bold">
                                {{ $completion['is_all_complete'] ? 'Data Lengkap' : $completion['overall_percentage'].'% Terisi' }}
                            </span>
                        @endif
                    </div>
                </div>
            </x-ui.card>
        </div>

        <!-- Periode Pendaftaran Aktif & Usia Terhitung -->
        @if ($activePeriod)
            <x-ui.card class="p-6 border-primary-200 bg-gradient-to-br from-primary-50/50 to-white">
                <div class="flex flex-col md:flex-row md:items-center justify-between gap-4">
                    <div>
                        <div class="flex items-center gap-2 mb-1.5">
                            <span class="inline-flex items-center gap-1.5 px-2.5 py-0.5 rounded-full text-[11px] font-bold bg-green-100 text-green-800">
                                <span class="w-2 h-2 rounded-full bg-green-500 animate-pulse"></span> Periode Dibuka
                            </span>
                            <span class="text-xs text-neutral-500">Tahun Ajaran {{ $activePeriod->academic_year }}</span>
                        </div>
                        <h2 class="text-lg font-bold text-neutral-800">{{ $activePeriod->name }}</h2>
                        <p class="text-xs text-neutral-600 mt-1">
                            Batas Pendaftaran: <strong>{{ $activePeriod->end_date?->translatedFormat('d F Y') ?? $activePeriod->end_date?->format('d F Y') }}</strong>
                        </p>
                    </div>

                    <!-- Usia Terhitung Dinamis -->
                    @if ($applicant->birth_date)
                        <div class="bg-white p-4 rounded-xl border border-primary-200/80 shadow-xs text-left md:text-right shrink-0">
                            <span class="text-[11px] text-neutral-500 font-medium block">Usia Calon Siswa (per {{ $age['reference_date'] }}):</span>
                            <span class="text-base font-extrabold text-primary-700 block font-mono">
                                {{ $age['formatted'] }}
                            </span>
                            <span class="text-[10px] text-neutral-400">Dihitung otomatis sesuai acuan seleksi madrasah</span>
                        </div>
                    @else
                        <div class="bg-amber-50 p-4 rounded-xl border border-amber-200 text-left md:text-right shrink-0">
                            <span class="text-[11px] text-amber-800 font-medium block">Usia Belum Terhitung</span>
                            <span class="text-xs text-amber-700">Lengkapi tanggal lahir di biodata</span>
                        </div>
                    @endif
                </div>
            </x-ui.card>
        @else
            <x-ui.card class="p-6 border-amber-200 bg-amber-50/50">
                <div class="flex items-center gap-3">
                    <svg class="w-6 h-6 text-amber-600 shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z" />
                    </svg>
                    <div>
                        <h3 class="text-sm font-bold text-neutral-800">Periode Pendaftaran Belum Dibuka</h3>
                        <p class="text-xs text-neutral-600">
                            @if ($canEdit)
                                Anda dapat memperbarui data yang terkait dengan pendaftaran selama akses edit ulang masih berlaku.
                            @else
                                Saat ini perubahan data pendaftaran terkunci. Anda dapat melihat status dan data pendaftaran yang telah dikirim.
                            @endif
                        </p>
                    </div>
                </div>
            </x-ui.card>
        @endif

        <!-- Progress Pengisian Formulir & Tombol Lanjutkan -->
        <x-ui.card class="p-6 sm:p-8 border-neutral-200/90 shadow-sm">
            <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4 mb-4">
                <div>
                    <h2 class="text-base sm:text-lg font-bold text-neutral-800">Kemajuan Pengisian Formulir Pendaftaran</h2>
                    <p class="text-xs text-neutral-500 mt-0.5">
                        Lengkapi lima bagian data dan unggah seluruh dokumen yang diwajibkan.
                    </p>
                </div>
                <div class="text-right shrink-0">
                    <span class="text-2xl font-extrabold font-mono {{ $completion['is_all_complete'] ? 'text-green-600' : 'text-primary-600' }}">
                        {{ $completion['overall_percentage'] }}%
                    </span>
                    <span class="block text-[11px] text-neutral-500 uppercase tracking-wider font-semibold">Terselesaikan</span>
                </div>
            </div>

            <!-- Progress Bar -->
            <div class="w-full bg-neutral-200 rounded-full h-3 overflow-hidden mb-6">
                <div
                    class="h-full rounded-full transition-all duration-500 {{ $completion['is_all_complete'] ? 'bg-green-500' : 'bg-primary-600' }}"
                    style="width: {{ $completion['overall_percentage'] }}%"
                ></div>
            </div>

            <!-- Checklist / Steps Grid -->
            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                <!-- Step 1: Biodata -->
                <div class="p-4 rounded-xl border {{ $completion['biodata']['is_complete'] ? 'border-green-200 bg-green-50/20' : 'border-neutral-200 bg-white' }} flex items-start justify-between gap-3">
                    <div class="flex items-start gap-3">
                        <div class="w-8 h-8 rounded-lg {{ $completion['biodata']['is_complete'] ? 'bg-green-100 text-green-700' : 'bg-neutral-100 text-neutral-500' }} flex items-center justify-center font-bold text-xs shrink-0 mt-0.5">
                            1
                        </div>
                        <div>
                            <h4 class="text-sm font-bold text-neutral-800">Biodata Calon Siswa</h4>
                            <p class="text-xs text-neutral-500 mt-0.5">
                                {{ $applicant->full_name ? $applicant->full_name : 'Nama, Tempat/Tanggal Lahir, Jenis Kelamin' }}
                            </p>
                            <span class="inline-flex items-center gap-1 mt-2 text-[11px] font-semibold {{ $completion['biodata']['is_complete'] ? 'text-green-700' : 'text-amber-700' }}">
                                <span class="w-1.5 h-1.5 rounded-full {{ $completion['biodata']['is_complete'] ? 'bg-green-500' : 'bg-amber-500' }}"></span>
                                {{ $completion['biodata']['label'] }}
                            </span>
                        </div>
                    </div>
                    @if ($canEdit)
                        <a href="{{ route('applicant.biodata.edit') }}" class="btn btn-outline text-xs px-3 py-1.5 shrink-0">
                            {{ $completion['biodata']['is_complete'] ? 'Ubah' : 'Isi' }}
                        </a>
                    @endif
                </div>

                <!-- Step 2: Alamat -->
                <div class="p-4 rounded-xl border {{ $completion['address']['is_complete'] ? 'border-green-200 bg-green-50/20' : 'border-neutral-200 bg-white' }} flex items-start justify-between gap-3">
                    <div class="flex items-start gap-3">
                        <div class="w-8 h-8 rounded-lg {{ $completion['address']['is_complete'] ? 'bg-green-100 text-green-700' : 'bg-neutral-100 text-neutral-500' }} flex items-center justify-center font-bold text-xs shrink-0 mt-0.5">
                            2
                        </div>
                        <div>
                            <h4 class="text-sm font-bold text-neutral-800">Alamat Domisili</h4>
                            <p class="text-xs text-neutral-500 mt-0.5">
                                {{ $applicant->address?->kelurahan ? $applicant->address->kelurahan . ', ' . $applicant->address->city : 'Alamat lengkap tempat tinggal, RT/RW, Kelurahan' }}
                            </p>
                            <span class="inline-flex items-center gap-1 mt-2 text-[11px] font-semibold {{ $completion['address']['is_complete'] ? 'text-green-700' : 'text-neutral-500' }}">
                                <span class="w-1.5 h-1.5 rounded-full {{ $completion['address']['is_complete'] ? 'bg-green-500' : 'bg-neutral-400' }}"></span>
                                {{ $completion['address']['label'] }}
                            </span>
                        </div>
                    </div>
                    @if ($canEdit)
                        <a href="{{ route('applicant.address.edit') }}" class="btn btn-outline text-xs px-3 py-1.5 shrink-0">
                            {{ $completion['address']['is_complete'] ? 'Ubah' : 'Isi' }}
                        </a>
                    @endif
                </div>

                <!-- Step 3: Orang Tua -->
                <div class="p-4 rounded-xl border {{ $completion['father']['is_complete'] && $completion['mother']['is_complete'] ? 'border-green-200 bg-green-50/20' : 'border-neutral-200 bg-white' }} flex items-start justify-between gap-3">
                    <div class="flex items-start gap-3">
                        <div class="w-8 h-8 rounded-lg {{ $completion['father']['is_complete'] && $completion['mother']['is_complete'] ? 'bg-green-100 text-green-700' : 'bg-neutral-100 text-neutral-500' }} flex items-center justify-center font-bold text-xs shrink-0 mt-0.5">
                            3
                        </div>
                        <div>
                            <h4 class="text-sm font-bold text-neutral-800">Data Orang Tua & Wali</h4>
                            <p class="text-xs text-neutral-500 mt-0.5">
                                {{ $applicant->father?->name ? 'Ayah: ' . $applicant->father->name : 'Data Ayah Kandung, Ibu Kandung, dan Wali' }}
                            </p>
                            <span class="inline-flex items-center gap-1 mt-2 text-[11px] font-semibold {{ $completion['father']['is_complete'] && $completion['mother']['is_complete'] ? 'text-green-700' : 'text-neutral-500' }}">
                                <span class="w-1.5 h-1.5 rounded-full {{ $completion['father']['is_complete'] && $completion['mother']['is_complete'] ? 'bg-green-500' : 'bg-neutral-400' }}"></span>
                                {{ $completion['father']['is_complete'] && $completion['mother']['is_complete'] ? 'Lengkap (Ayah & Ibu)' : 'Belum Lengkap' }}
                            </span>
                        </div>
                    </div>
                    @if ($canEdit)
                        <a href="{{ route('applicant.parents.edit') }}" class="btn btn-outline text-xs px-3 py-1.5 shrink-0">
                            {{ $completion['father']['is_complete'] && $completion['mother']['is_complete'] ? 'Ubah' : 'Isi' }}
                        </a>
                    @endif
                </div>

                <!-- Step 4: Pendidikan Asal -->
                <div class="p-4 rounded-xl border {{ $completion['education']['is_complete'] ? 'border-green-200 bg-green-50/20' : 'border-neutral-200 bg-white' }} flex items-start justify-between gap-3">
                    <div class="flex items-start gap-3">
                        <div class="w-8 h-8 rounded-lg {{ $completion['education']['is_complete'] ? 'bg-green-100 text-green-700' : 'bg-neutral-100 text-neutral-500' }} flex items-center justify-center font-bold text-xs shrink-0 mt-0.5">
                            4
                        </div>
                        <div>
                            <h4 class="text-sm font-bold text-neutral-800">Riwayat Pendidikan Asal</h4>
                            <p class="text-xs text-neutral-500 mt-0.5">
                                {{ $applicant->education?->school_name ? $applicant->education->school_name : 'RA, TK, PAUD, atau Belum Sekolah' }}
                            </p>
                            <span class="inline-flex items-center gap-1 mt-2 text-[11px] font-semibold {{ $completion['education']['is_complete'] ? 'text-green-700' : 'text-neutral-500' }}">
                                <span class="w-1.5 h-1.5 rounded-full {{ $completion['education']['is_complete'] ? 'bg-green-500' : 'bg-neutral-400' }}"></span>
                                {{ $completion['education']['label'] }}
                            </span>
                        </div>
                    </div>
                    @if ($canEdit)
                        <a href="{{ route('applicant.education.edit') }}" class="btn btn-outline text-xs px-3 py-1.5 shrink-0">
                            {{ $completion['education']['is_complete'] ? 'Ubah' : 'Isi' }}
                        </a>
                    @endif
                </div>

                <!-- Step 5: Pilihan Program -->
                <div class="p-4 rounded-xl border md:col-span-2 {{ $completion['program']['is_complete'] ? 'border-green-200 bg-green-50/20' : 'border-neutral-200 bg-white' }} flex items-start justify-between gap-3">
                    <div class="flex items-start gap-3">
                        <div class="w-8 h-8 rounded-lg {{ $completion['program']['is_complete'] ? 'bg-green-100 text-green-700' : 'bg-neutral-100 text-neutral-500' }} flex items-center justify-center font-bold text-xs shrink-0 mt-0.5">
                            5
                        </div>
                        <div>
                            <h4 class="text-sm font-bold text-neutral-800">Pilihan Program Pendidikan</h4>
                            <p class="text-xs text-neutral-500 mt-0.5">
                                {{ $completion['program']['selected_program'] ? 'Program Dipilih: ' . $completion['program']['selected_program'] : 'Pilihan Program Unggulan atau Reguler' }}
                            </p>
                            <span class="inline-flex items-center gap-1 mt-2 text-[11px] font-semibold {{ $completion['program']['is_complete'] ? 'text-green-700' : 'text-amber-700' }}">
                                <span class="w-1.5 h-1.5 rounded-full {{ $completion['program']['is_complete'] ? 'bg-green-500' : 'bg-amber-500' }}"></span>
                                {{ $completion['program']['label'] }}
                            </span>
                        </div>
                    </div>
                    @if ($canEditProgram)
                        <a href="{{ route('applicant.program.edit') }}" class="btn btn-outline text-xs px-3 py-1.5 shrink-0">
                            {{ $completion['program']['is_complete'] ? 'Ubah Program' : 'Pilih Program' }}
                        </a>
                    @endif
                </div>

                <!-- Step 6: Dokumen Persyaratan -->
                <div class="p-4 rounded-xl border md:col-span-2 {{ $completion['documents']['is_complete'] ? 'border-green-200 bg-green-50/20' : 'border-neutral-200 bg-white' }} flex items-start justify-between gap-3">
                    <div class="flex items-start gap-3">
                        <div class="w-8 h-8 rounded-lg {{ $completion['documents']['is_complete'] ? 'bg-green-100 text-green-700' : 'bg-neutral-100 text-neutral-500' }} flex items-center justify-center font-bold text-xs shrink-0 mt-0.5">
                            6
                        </div>
                        <div>
                            <h4 class="text-sm font-bold text-neutral-800">Dokumen Persyaratan</h4>
                            <p class="text-xs text-neutral-500 mt-0.5">
                                @if (! $completion['program']['is_complete'])
                                    Pilih program pada periode aktif untuk melihat persyaratan dokumen.
                                @elseif ($completion['documents']['required_count'] === 0)
                                    Persyaratan dokumen belum tersedia untuk periode ini.
                                @else
                                    {{ $completion['documents']['uploaded_count'] }} dari {{ $completion['documents']['required_count'] }} dokumen wajib telah diunggah.
                                @endif
                            </p>
                            <span class="inline-flex items-center gap-1 mt-2 text-[11px] font-semibold {{ $completion['documents']['is_complete'] ? 'text-green-700' : 'text-amber-700' }}">
                                <span class="w-1.5 h-1.5 rounded-full {{ $completion['documents']['is_complete'] ? 'bg-green-500' : 'bg-amber-500' }}"></span>
                                {{ $completion['documents']['is_complete'] ? 'Lengkap' : 'Belum Lengkap' }}
                            </span>
                        </div>
                    </div>
                    @if ($canEdit)
                        <a href="{{ route('applicant.documents.index') }}" class="btn btn-outline text-xs px-3 py-1.5 shrink-0">
                            Kelola Dokumen
                        </a>
                    @endif
                </div>
            </div>

            <section class="mt-8 rounded-xl border border-blue-200 bg-blue-50/40 p-4 sm:p-5" aria-labelledby="registration-pdf-heading">
                <div class="flex flex-col gap-3 sm:flex-row sm:items-start sm:justify-between">
                    <div>
                        <h2 id="registration-pdf-heading" class="text-base font-bold text-neutral-800">Dokumen PDF Pendaftaran</h2>
                        <p class="mt-1 text-xs leading-5 text-neutral-600">
                            PDF disimpan secara privat. Draf bukan bukti resmi; bukti final tersedia setelah pendaftaran dikirim.
                        </p>
                    </div>

                    @if ($registration?->isDraft())
                        <form method="POST" action="{{ route('applicant.registration.pdf.draft') }}">
                            @csrf
                            <button type="submit" class="btn btn-primary w-full text-xs font-semibold sm:w-auto">
                                Buat PDF Draf
                            </button>
                        </form>
                    @elseif ($registration?->isSubmitted())
                        @php
                            $latestFinalPdf = $pdfVersions
                                ->filter(fn ($version) => $version->document_type->value === 'final')
                                ->first();
                            $latestFinalStatus = $latestFinalPdf ? $pdfVersionStatuses[$latestFinalPdf->id] : null;
                        @endphp

                        @if ($latestFinalStatus !== 'current')
                            <form method="POST" action="{{ route('applicant.registration.pdf.final') }}">
                                @csrf
                                <button type="submit" class="btn btn-primary w-full text-xs font-semibold sm:w-auto">
                                    {{ $latestFinalPdf ? 'Perbarui PDF Final' : 'Buat Bukti PDF Final' }}
                                </button>
                            </form>
                        @endif
                    @endif
                </div>

                @if (! $registration)
                    <p class="mt-3 text-xs text-neutral-600">
                        Pilih program pada periode pendaftaran agar sistem dapat mencatat dan membuat versi PDF draf.
                    </p>
                @elseif ($registration->isSubmitted())
                    <p class="mt-3 text-xs text-neutral-600">
                        Pendaftaran telah dikirim. Dokumen ini hanya bukti pengiriman dan bukan keputusan seleksi.
                    </p>
                @else
                    <p class="mt-3 text-xs text-neutral-600">
                        Draf dapat dibuat meskipun data belum lengkap dan akan menampilkan bagian yang belum diisi.
                    </p>
                @endif

                @if ($pdfVersions->isNotEmpty())
                    <div class="mt-4 border-t border-blue-100 pt-3">
                        <h3 class="text-xs font-bold uppercase tracking-wide text-neutral-700">Riwayat versi PDF</h3>
                        <ul class="mt-2 divide-y divide-blue-100">
                            @foreach ($pdfVersions as $pdfVersion)
                                @php
                                    $pdfStatusLabels = [
                                        'current' => 'Versi terbaru sesuai data',
                                        'outdated' => 'Data berubah - perlu diperbarui',
                                        'superseded' => 'Versi terdahulu',
                                        'archived' => 'Arsip draf sebelum pengiriman',
                                    ];
                                @endphp
                                <li class="flex flex-col gap-2 py-2 text-xs sm:flex-row sm:items-center sm:justify-between">
                                    <span class="text-neutral-700">
                                        {{ $pdfVersion->document_type->label() }} v{{ $pdfVersion->version_number }}
                                        <span class="text-neutral-500">- {{ $pdfStatusLabels[$pdfVersionStatuses[$pdfVersion->id]] }}</span>
                                    </span>
                                    <a
                                        href="{{ route('applicant.registration.pdf.download', $pdfVersion) }}"
                                        class="font-semibold text-blue-800 underline"
                                    >
                                        Unduh PDF
                                    </a>
                                </li>
                            @endforeach
                        </ul>
                    </div>
                @endif
            </section>

            <!-- CTA Button -->
            <div class="mt-8 pt-6 border-t border-neutral-100 flex flex-col sm:flex-row items-center justify-between gap-4">
                <div class="text-xs text-neutral-500">
                    @if ($registration?->isSubmitted())
                        <span class="font-semibold text-green-700">Pendaftaran telah dikirim. Anda dapat meninjau kembali status dan ringkasannya.</span>
                    @elseif (! $activePeriod)
                        <span>Perubahan pendaftaran terkunci sampai periode berikutnya dibuka.</span>
                    @elseif ($completion['is_all_complete'])
                        <span class="inline-flex items-center gap-1 text-green-700 font-semibold">
                            <svg class="w-4 h-4 text-green-600" fill="currentColor" viewBox="0 0 20 20">
                                <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd" />
                            </svg>
                            Data profil dan seluruh dokumen wajib telah lengkap.
                        </span>
                    @else
                        <span>Masih ada data formulir atau dokumen wajib yang belum lengkap.</span>
                    @endif
                </div>

                @if ($registration)
                    <a href="{{ route('applicant.registration.review') }}" class="btn btn-primary inline-flex w-full items-center justify-center gap-2 font-semibold shadow-md sm:w-auto">
                        {{ $registration->isSubmitted() ? 'Lihat Status Pendaftaran' : 'Tinjau Pendaftaran' }}
                        <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M14 5l7 7m0 0l-7 7m7-7H3" />
                        </svg>
                    </a>
                @elseif ($activePeriod)
                    <a href="{{ route('applicant.biodata.edit') }}" class="btn btn-primary inline-flex w-full items-center justify-center gap-2 font-semibold shadow-md sm:w-auto">
                        <span>{{ $completion['overall_percentage'] > 0 ? 'Lanjutkan Pengisian Formulir' : 'Mulai Pengisian Formulir' }}</span>
                        <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M14 5l7 7m0 0l-7 7m7-7H3" />
                        </svg>
                    </a>
                @endif
            </div>
        </x-ui.card>
    </div>
</x-layouts.app>
