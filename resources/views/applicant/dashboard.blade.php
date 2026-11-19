<x-layouts.applicant title="Dasbor Pendaftar — PPDB MIN 1 Kota Madiun">
    <div class="max-w-6xl mx-auto px-4 sm:px-6 lg:px-8 space-y-8">
        {{-- =========================================================================
             FUTURISTIC COMMAND BANNER (Hero Station)
             ========================================================================= --}}
        <div class="relative overflow-hidden rounded-3xl bg-gradient-to-br from-slate-900 via-slate-900 to-emerald-950/80 border border-slate-800 p-6 sm:p-8 shadow-2xl">
            {{-- Ambient radial background glow --}}
            <div class="absolute -top-24 -right-24 w-80 h-80 bg-emerald-500/10 rounded-full blur-3xl pointer-events-none"></div>
            <div class="absolute -bottom-24 -left-24 w-80 h-80 bg-teal-500/10 rounded-full blur-3xl pointer-events-none"></div>

            <div class="relative z-10 flex flex-col md:flex-row md:items-center justify-between gap-6">
                <div class="space-y-3">
                    <div class="flex flex-wrap items-center gap-2">
                        <span class="inline-flex items-center gap-1.5 px-3 py-1 rounded-full text-[11px] font-bold bg-emerald-500/15 text-emerald-300 border border-emerald-500/30">
                            <span class="relative flex h-2 w-2">
                                <span class="animate-ping absolute inline-flex h-full w-full rounded-full bg-emerald-400 opacity-75"></span>
                                <span class="relative inline-flex rounded-full h-2 w-2 bg-emerald-500"></span>
                            </span>
                            Akun Terverifikasi & Aktif
                        </span>

                        @if ($activePeriod)
                            <span class="inline-flex items-center gap-1.5 px-3 py-1 rounded-full text-[11px] font-bold bg-slate-800 text-slate-300 border border-slate-700">
                                <span>Periode Aktif:</span>
                                <strong class="text-white">{{ $activePeriod->name }}</strong>
                                <span class="text-slate-500">&bull;</span>
                                <span class="text-emerald-400">T.A. {{ $activePeriod->academic_year }}</span>
                            </span>
                        @endif
                    </div>

                    <h1 class="text-2xl sm:text-3xl lg:text-4xl font-extrabold tracking-tight text-white">
                        Selamat Datang, <span class="text-emerald-400">{{ $applicant->full_name ?? $user->name }}</span>
                    </h1>

                    <p class="text-xs sm:text-sm text-slate-400 max-w-2xl leading-relaxed">
                        Portal Pendaftaran Peserta Didik Baru MIN 1 Kota Madiun. Lengkapi biodata, orang tua, riwayat pendidikan, pilihan program, dan unggah dokumen persyaratan secara mandiri.
                    </p>
                </div>

                {{-- Action / Status Chip --}}
                <div class="shrink-0 flex items-center gap-3">
                    @if ($registration?->isSubmitted())
                        <div class="p-3.5 rounded-2xl bg-emerald-950/60 border border-emerald-500/30 text-right">
                            <span class="text-[10px] uppercase font-bold text-emerald-400 tracking-wider block">No. Registrasi Resmi</span>
                            <span class="font-mono text-sm sm:text-base font-extrabold text-white block">{{ $registration->registration_number }}</span>
                            <span class="text-[10px] text-emerald-300/80 font-medium block mt-0.5">Sudah Terkirim ke Loket PTSP</span>
                        </div>
                    @else
                        <div class="p-3.5 rounded-2xl bg-slate-800/80 border border-slate-700 text-right">
                            <span class="text-[10px] uppercase font-bold text-slate-400 tracking-wider block">Status Formulir</span>
                            <span class="font-mono text-sm sm:text-base font-extrabold text-amber-400 block">
                                {{ $completion['is_all_complete'] ? 'Siap Dikirim' : $completion['overall_percentage'].'% Terisi' }}
                            </span>
                            <span class="text-[10px] text-slate-400 font-medium block mt-0.5">Draf Tersimpan Otomatis</span>
                        </div>
                    @endif
                </div>
            </div>
        </div>

        {{-- =========================================================================
             REOPEN ACCESS BANNER (If Active)
             ========================================================================= --}}
        @if ($registration?->activeReopenWindow)
            <div class="p-4 sm:p-5 rounded-2xl bg-amber-500/10 border border-amber-500/30 text-amber-200 flex items-start gap-3">
                <svg class="w-5 h-5 text-amber-400 shrink-0 mt-0.5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z" />
                </svg>
                <div class="text-xs sm:text-sm">
                    <strong class="font-bold text-white block">Akses Pembukaan Kembali Pendaftaran Aktif</strong>
                    <p class="mt-0.5 text-amber-200/90 leading-relaxed">
                        Akses perubahan data dibuka sampai {{ $registration->activeReopenWindow->expires_at->translatedFormat('d F Y, H:i') }} WIB.
                        {{ $registration->activeReopenWindow->reason }}
                    </p>
                </div>
            </div>
        @endif

        {{-- =========================================================================
             TELEMETRY 4-CARD DATA ROW (Crisp & Futuristic Metrics)
             ========================================================================= --}}
        <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4">
            {{-- Card 1: NIK Calon Siswa --}}
            <div class="p-4 sm:p-5 rounded-2xl bg-slate-900 border border-slate-800 interactive-card">
                <div class="flex items-center justify-between gap-3 mb-2">
                    <span class="text-[11px] font-bold text-slate-400 uppercase tracking-wider">NIK Calon Siswa</span>
                    <span class="p-1.5 rounded-lg bg-emerald-500/10 text-emerald-400 border border-emerald-500/20">
                        <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z" />
                        </svg>
                    </span>
                </div>
                <p class="text-base font-mono font-extrabold text-white tracking-tight">{{ $maskedNik }}</p>
                <span class="inline-block mt-2 text-[10px] font-semibold text-emerald-400 bg-emerald-500/10 px-2 py-0.5 rounded-md border border-emerald-500/20">
                    Enkripsi AES-256
                </span>
            </div>

            {{-- Card 2: WhatsApp Terdaftar --}}
            <div class="p-4 sm:p-5 rounded-2xl bg-slate-900 border border-slate-800 interactive-card">
                <div class="flex items-center justify-between gap-3 mb-2">
                    <span class="text-[11px] font-bold text-slate-400 uppercase tracking-wider">WhatsApp Terdaftar</span>
                    <span class="p-1.5 rounded-lg bg-teal-500/10 text-teal-400 border border-teal-500/20">
                        <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 5a2 2 0 012-2h3.28a1 1 0 01.948.684l1.498 4.493a1 1 0 01-.502 1.21l-2.257 1.13a11.042 11.042 0 005.516 5.516l1.13-2.257a1 1 0 011.21-.502l4.493 1.498a1 1 0 01.684.949V19a2 2 0 01-2 2h-1C9.716 21 3 14.284 3 6V5z" />
                        </svg>
                    </span>
                </div>
                <p class="text-base font-mono font-extrabold text-white tracking-tight">{{ $user->phone }}</p>
                <span class="inline-block mt-2 text-[10px] font-semibold text-teal-400 bg-teal-500/10 px-2 py-0.5 rounded-md border border-teal-500/20">
                    Terverifikasi OTP
                </span>
            </div>

            {{-- Card 3: Usia Calon Siswa --}}
            <div class="p-4 sm:p-5 rounded-2xl bg-slate-900 border border-slate-800 interactive-card">
                <div class="flex items-center justify-between gap-3 mb-2">
                    <span class="text-[11px] font-bold text-slate-400 uppercase tracking-wider">Usia Calon Siswa</span>
                    <span class="p-1.5 rounded-lg bg-cyan-500/10 text-cyan-400 border border-cyan-500/20">
                        <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z" />
                        </svg>
                    </span>
                </div>
                @if ($applicant->birth_date)
                    <p class="text-sm sm:text-base font-mono font-extrabold text-cyan-300 tracking-tight">{{ $age['formatted'] }}</p>
                    <span class="inline-block mt-2 text-[10px] text-slate-400">
                        Per {{ $age['reference_date'] }}
                    </span>
                @else
                    <p class="text-sm font-semibold text-amber-400">Belum Terhitung</p>
                    <span class="inline-block mt-2 text-[10px] text-slate-500">Lengkapi tanggal lahir di biodata</span>
                @endif
            </div>

            {{-- Card 4: Status Pendaftaran --}}
            <div class="p-4 sm:p-5 rounded-2xl bg-slate-900 border border-slate-800 interactive-card">
                <div class="flex items-center justify-between gap-3 mb-2">
                    <span class="text-[11px] font-bold text-slate-400 uppercase tracking-wider">Status Pendaftaran</span>
                    <span class="p-1.5 rounded-lg bg-purple-500/10 text-purple-400 border border-purple-500/20">
                        <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z" />
                        </svg>
                    </span>
                </div>
                @if ($registration?->isSubmitted())
                    <span class="inline-flex items-center px-2 py-0.5 rounded-full bg-emerald-500/20 text-xs font-bold text-emerald-300 border border-emerald-500/30">
                        {{ $registration->status->label() }}
                    </span>
                    <p class="mt-2 font-mono text-xs font-semibold text-slate-300 truncate">{{ $registration->registration_number }}</p>
                @elseif ($registration?->isDraft())
                    <span class="inline-flex items-center px-2 py-0.5 rounded-full bg-amber-500/20 text-xs font-bold text-amber-300 border border-amber-500/30">
                        DRAFT
                    </span>
                    <p class="mt-2 text-[11px] text-slate-400">Belum diserahkan</p>
                @else
                    <span class="inline-flex items-center px-2 py-0.5 rounded-full {{ $completion['is_all_complete'] ? 'bg-emerald-500/20 text-emerald-300 border border-emerald-500/30' : 'bg-amber-500/20 text-amber-300 border border-amber-500/30' }} text-xs font-bold">
                        {{ $completion['is_all_complete'] ? 'Data Lengkap' : $completion['overall_percentage'].'% Terisi' }}
                    </span>
                    <p class="mt-2 text-[11px] text-slate-400">{{ $completion['is_all_complete'] ? 'Siap difinalisasi' : 'Perlu dilengkapi' }}</p>
                @endif
            </div>
        </div>

        {{-- =========================================================================
             FORM WORKFLOW HUB (6 Clean Modular Steps)
             ========================================================================= --}}
        <div class="rounded-3xl bg-slate-900 border border-slate-800 p-6 sm:p-8 shadow-xl space-y-6">
            {{-- Header & Progress Bar --}}
            <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4">
                <div>
                    <h2 class="text-lg sm:text-xl font-extrabold text-white">Kemajuan Pengisian Formulir Pendaftaran</h2>
                    <p class="text-xs text-slate-400 mt-1">
                        Selesaikan pengisian 5 modul biodata dan unggah seluruh berkas dokumen digital yang diwajibkan.
                    </p>
                </div>
                <div class="text-right shrink-0">
                    <span class="text-2xl sm:text-3xl font-extrabold font-mono {{ $completion['is_all_complete'] ? 'text-emerald-400' : 'text-teal-400' }}">
                        {{ $completion['overall_percentage'] }}%
                    </span>
                    <span class="block text-[10px] text-slate-400 uppercase tracking-wider font-semibold">Terselesaikan</span>
                </div>
            </div>

            {{-- Progress Line --}}
            <div class="w-full bg-slate-800 rounded-full h-3 overflow-hidden">
                <div
                    class="h-full rounded-full transition-all duration-500 {{ $completion['is_all_complete'] ? 'bg-gradient-to-r from-emerald-500 to-teal-400' : 'bg-gradient-to-r from-teal-500 to-emerald-500' }}"
                    style="width: {{ $completion['overall_percentage'] }}%"
                ></div>
            </div>

            {{-- 6 Step Cards Grid --}}
            <div class="grid grid-cols-1 md:grid-cols-2 gap-4 pt-2">
                {{-- Step 1: Biodata Siswa --}}
                <div class="p-4 sm:p-5 rounded-2xl border {{ $completion['biodata']['is_complete'] ? 'border-emerald-500/30 bg-emerald-950/20' : 'border-slate-800 bg-slate-800/40' }} flex items-start justify-between gap-4 interactive-card">
                    <div class="flex items-start gap-3.5 min-w-0">
                        <div class="w-9 h-9 rounded-xl {{ $completion['biodata']['is_complete'] ? 'bg-emerald-500/20 text-emerald-400 border border-emerald-500/30' : 'bg-slate-800 text-slate-400 border border-slate-700' }} flex items-center justify-center font-bold text-xs shrink-0 mt-0.5">
                            @if ($completion['biodata']['is_complete'])
                                <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M5 13l4 4L19 7" />
                                </svg>
                            @else
                                1
                            @endif
                        </div>
                        <div class="min-w-0">
                            <h4 class="text-sm font-bold text-white">Biodata Calon Siswa</h4>
                            <p class="text-xs text-slate-400 mt-0.5 truncate">
                                {{ $applicant->full_name ? $applicant->full_name : 'Nama, Tempat/Tanggal Lahir, Jenis Kelamin' }}
                            </p>
                            <span class="inline-flex items-center gap-1.5 mt-2 text-[11px] font-semibold {{ $completion['biodata']['is_complete'] ? 'text-emerald-400' : 'text-amber-400' }}">
                                <span class="w-1.5 h-1.5 rounded-full {{ $completion['biodata']['is_complete'] ? 'bg-emerald-400' : 'bg-amber-400' }}"></span>
                                {{ $completion['biodata']['label'] }}
                            </span>
                        </div>
                    </div>
                    @if ($canEdit)
                        <a href="{{ route('applicant.biodata.edit') }}" class="px-3 py-1.5 rounded-xl text-xs font-semibold {{ $completion['biodata']['is_complete'] ? 'bg-slate-800 hover:bg-slate-700 text-slate-200 border border-slate-700' : 'bg-emerald-600 hover:bg-emerald-500 text-white shadow-sm' }} transition shrink-0">
                            {{ $completion['biodata']['is_complete'] ? 'Ubah' : 'Isi' }}
                        </a>
                    @endif
                </div>

                {{-- Step 2: Alamat Domisili --}}
                <div class="p-4 sm:p-5 rounded-2xl border {{ $completion['address']['is_complete'] ? 'border-emerald-500/30 bg-emerald-950/20' : 'border-slate-800 bg-slate-800/40' }} flex items-start justify-between gap-4 interactive-card">
                    <div class="flex items-start gap-3.5 min-w-0">
                        <div class="w-9 h-9 rounded-xl {{ $completion['address']['is_complete'] ? 'bg-emerald-500/20 text-emerald-400 border border-emerald-500/30' : 'bg-slate-800 text-slate-400 border border-slate-700' }} flex items-center justify-center font-bold text-xs shrink-0 mt-0.5">
                            @if ($completion['address']['is_complete'])
                                <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M5 13l4 4L19 7" />
                                </svg>
                            @else
                                2
                            @endif
                        </div>
                        <div class="min-w-0">
                            <h4 class="text-sm font-bold text-white">Alamat Domisili</h4>
                            <p class="text-xs text-slate-400 mt-0.5 truncate">
                                {{ $applicant->address?->kelurahan ? $applicant->address->kelurahan . ', ' . $applicant->address->city : 'Alamat lengkap tempat tinggal, RT/RW, Kelurahan' }}
                            </p>
                            <span class="inline-flex items-center gap-1.5 mt-2 text-[11px] font-semibold {{ $completion['address']['is_complete'] ? 'text-emerald-400' : 'text-slate-400' }}">
                                <span class="w-1.5 h-1.5 rounded-full {{ $completion['address']['is_complete'] ? 'bg-emerald-400' : 'bg-slate-500' }}"></span>
                                {{ $completion['address']['label'] }}
                            </span>
                        </div>
                    </div>
                    @if ($canEdit)
                        <a href="{{ route('applicant.address.edit') }}" class="px-3 py-1.5 rounded-xl text-xs font-semibold {{ $completion['address']['is_complete'] ? 'bg-slate-800 hover:bg-slate-700 text-slate-200 border border-slate-700' : 'bg-emerald-600 hover:bg-emerald-500 text-white shadow-sm' }} transition shrink-0">
                            {{ $completion['address']['is_complete'] ? 'Ubah' : 'Isi' }}
                        </a>
                    @endif
                </div>

                {{-- Step 3: Data Orang Tua & Wali --}}
                <div class="p-4 sm:p-5 rounded-2xl border {{ $completion['father']['is_complete'] && $completion['mother']['is_complete'] ? 'border-emerald-500/30 bg-emerald-950/20' : 'border-slate-800 bg-slate-800/40' }} flex items-start justify-between gap-4 interactive-card">
                    <div class="flex items-start gap-3.5 min-w-0">
                        <div class="w-9 h-9 rounded-xl {{ $completion['father']['is_complete'] && $completion['mother']['is_complete'] ? 'bg-emerald-500/20 text-emerald-400 border border-emerald-500/30' : 'bg-slate-800 text-slate-400 border border-slate-700' }} flex items-center justify-center font-bold text-xs shrink-0 mt-0.5">
                            @if ($completion['father']['is_complete'] && $completion['mother']['is_complete'])
                                <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M5 13l4 4L19 7" />
                                </svg>
                            @else
                                3
                            @endif
                        </div>
                        <div class="min-w-0">
                            <h4 class="text-sm font-bold text-white">Data Orang Tua & Wali</h4>
                            <p class="text-xs text-slate-400 mt-0.5 truncate">
                                {{ $applicant->father?->name ? 'Ayah: ' . $applicant->father->name : 'Data Ayah Kandung, Ibu Kandung, dan Wali' }}
                            </p>
                            <span class="inline-flex items-center gap-1.5 mt-2 text-[11px] font-semibold {{ $completion['father']['is_complete'] && $completion['mother']['is_complete'] ? 'text-emerald-400' : 'text-slate-400' }}">
                                <span class="w-1.5 h-1.5 rounded-full {{ $completion['father']['is_complete'] && $completion['mother']['is_complete'] ? 'bg-emerald-400' : 'bg-slate-500' }}"></span>
                                {{ $completion['father']['is_complete'] && $completion['mother']['is_complete'] ? 'Lengkap (Ayah & Ibu)' : 'Belum Lengkap' }}
                            </span>
                        </div>
                    </div>
                    @if ($canEdit)
                        <a href="{{ route('applicant.parents.edit') }}" class="px-3 py-1.5 rounded-xl text-xs font-semibold {{ $completion['father']['is_complete'] && $completion['mother']['is_complete'] ? 'bg-slate-800 hover:bg-slate-700 text-slate-200 border border-slate-700' : 'bg-emerald-600 hover:bg-emerald-500 text-white shadow-sm' }} transition shrink-0">
                            {{ $completion['father']['is_complete'] && $completion['mother']['is_complete'] ? 'Ubah' : 'Isi' }}
                        </a>
                    @endif
                </div>

                {{-- Step 4: Riwayat Pendidikan Asal --}}
                <div class="p-4 sm:p-5 rounded-2xl border {{ $completion['education']['is_complete'] ? 'border-emerald-500/30 bg-emerald-950/20' : 'border-slate-800 bg-slate-800/40' }} flex items-start justify-between gap-4 interactive-card">
                    <div class="flex items-start gap-3.5 min-w-0">
                        <div class="w-9 h-9 rounded-xl {{ $completion['education']['is_complete'] ? 'bg-emerald-500/20 text-emerald-400 border border-emerald-500/30' : 'bg-slate-800 text-slate-400 border border-slate-700' }} flex items-center justify-center font-bold text-xs shrink-0 mt-0.5">
                            @if ($completion['education']['is_complete'])
                                <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M5 13l4 4L19 7" />
                                </svg>
                            @else
                                4
                            @endif
                        </div>
                        <div class="min-w-0">
                            <h4 class="text-sm font-bold text-white">Riwayat Pendidikan Asal</h4>
                            <p class="text-xs text-slate-400 mt-0.5 truncate">
                                {{ $applicant->education?->school_name ? $applicant->education->school_name : 'RA, TK, PAUD, atau Belum Sekolah' }}
                            </p>
                            <span class="inline-flex items-center gap-1.5 mt-2 text-[11px] font-semibold {{ $completion['education']['is_complete'] ? 'text-emerald-400' : 'text-slate-400' }}">
                                <span class="w-1.5 h-1.5 rounded-full {{ $completion['education']['is_complete'] ? 'bg-emerald-400' : 'bg-slate-500' }}"></span>
                                {{ $completion['education']['label'] }}
                            </span>
                        </div>
                    </div>
                    @if ($canEdit)
                        <a href="{{ route('applicant.education.edit') }}" class="px-3 py-1.5 rounded-xl text-xs font-semibold {{ $completion['education']['is_complete'] ? 'bg-slate-800 hover:bg-slate-700 text-slate-200 border border-slate-700' : 'bg-emerald-600 hover:bg-emerald-500 text-white shadow-sm' }} transition shrink-0">
                            {{ $completion['education']['is_complete'] ? 'Ubah' : 'Isi' }}
                        </a>
                    @endif
                </div>

                {{-- Step 5: Pilihan Program Pendidikan --}}
                <div class="p-4 sm:p-5 rounded-2xl border md:col-span-2 {{ $completion['program']['is_complete'] ? 'border-emerald-500/30 bg-emerald-950/20' : 'border-slate-800 bg-slate-800/40' }} flex items-start justify-between gap-4 interactive-card">
                    <div class="flex items-start gap-3.5 min-w-0">
                        <div class="w-9 h-9 rounded-xl {{ $completion['program']['is_complete'] ? 'bg-emerald-500/20 text-emerald-400 border border-emerald-500/30' : 'bg-slate-800 text-slate-400 border border-slate-700' }} flex items-center justify-center font-bold text-xs shrink-0 mt-0.5">
                            @if ($completion['program']['is_complete'])
                                <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M5 13l4 4L19 7" />
                                </svg>
                            @else
                                5
                            @endif
                        </div>
                        <div class="min-w-0">
                            <h4 class="text-sm font-bold text-white">Pilihan Program Pendidikan</h4>
                            <p class="text-xs text-slate-400 mt-0.5 truncate">
                                {{ $completion['program']['selected_program'] ? 'Program Dipilih: ' . $completion['program']['selected_program'] : 'Pilihan Program Unggulan atau Reguler' }}
                            </p>
                            <span class="inline-flex items-center gap-1.5 mt-2 text-[11px] font-semibold {{ $completion['program']['is_complete'] ? 'text-emerald-400' : 'text-amber-400' }}">
                                <span class="w-1.5 h-1.5 rounded-full {{ $completion['program']['is_complete'] ? 'bg-emerald-400' : 'bg-amber-400' }}"></span>
                                {{ $completion['program']['label'] }}
                            </span>
                        </div>
                    </div>
                    @if ($canEditProgram)
                        <a href="{{ route('applicant.program.edit') }}" class="px-3 py-1.5 rounded-xl text-xs font-semibold {{ $completion['program']['is_complete'] ? 'bg-slate-800 hover:bg-slate-700 text-slate-200 border border-slate-700' : 'bg-emerald-600 hover:bg-emerald-500 text-white shadow-sm' }} transition shrink-0">
                            {{ $completion['program']['is_complete'] ? 'Ubah Program' : 'Pilih Program' }}
                        </a>
                    @endif
                </div>

                {{-- Step 6: Dokumen Persyaratan --}}
                <div class="p-4 sm:p-5 rounded-2xl border md:col-span-2 {{ $completion['documents']['is_complete'] ? 'border-emerald-500/30 bg-emerald-950/20' : 'border-slate-800 bg-slate-800/40' }} flex items-start justify-between gap-4 interactive-card">
                    <div class="flex items-start gap-3.5 min-w-0">
                        <div class="w-9 h-9 rounded-xl {{ $completion['documents']['is_complete'] ? 'bg-emerald-500/20 text-emerald-400 border border-emerald-500/30' : 'bg-slate-800 text-slate-400 border border-slate-700' }} flex items-center justify-center font-bold text-xs shrink-0 mt-0.5">
                            @if ($completion['documents']['is_complete'])
                                <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M5 13l4 4L19 7" />
                                </svg>
                            @else
                                6
                            @endif
                        </div>
                        <div class="min-w-0">
                            <h4 class="text-sm font-bold text-white">Dokumen Persyaratan</h4>
                            <p class="text-xs text-slate-400 mt-0.5">
                                @if (! $completion['program']['is_complete'])
                                    Pilih program pada periode aktif untuk melihat persyaratan dokumen.
                                @elseif ($completion['documents']['required_count'] === 0)
                                    Persyaratan dokumen belum tersedia untuk periode ini.
                                @else
                                    {{ $completion['documents']['uploaded_count'] }} dari {{ $completion['documents']['required_count'] }} dokumen wajib telah diunggah.
                                @endif
                            </p>
                            <span class="inline-flex items-center gap-1.5 mt-2 text-[11px] font-semibold {{ $completion['documents']['is_complete'] ? 'text-emerald-400' : 'text-amber-400' }}">
                                <span class="w-1.5 h-1.5 rounded-full {{ $completion['documents']['is_complete'] ? 'bg-emerald-400' : 'bg-amber-400' }}"></span>
                                {{ $completion['documents']['is_complete'] ? 'Lengkap' : 'Belum Lengkap' }}
                            </span>
                        </div>
                    </div>
                    @if ($canEdit)
                        <a href="{{ route('applicant.documents.index') }}" class="px-3.5 py-1.5 rounded-xl text-xs font-semibold bg-slate-800 hover:bg-slate-700 text-slate-200 border border-slate-700 transition shrink-0">
                            Kelola Dokumen
                        </a>
                    @endif
                </div>
            </div>

            {{-- PDF SECTION & PTSP INSTRUCTION --}}
            <section class="mt-8 rounded-2xl border border-slate-800 bg-slate-950/60 p-5 sm:p-6" aria-labelledby="registration-pdf-heading">
                <div class="flex flex-col gap-4 sm:flex-row sm:items-start sm:justify-between">
                    <div>
                        <div class="flex items-center gap-2">
                            <span class="p-1 rounded-md bg-teal-500/10 text-teal-400 border border-teal-500/20">
                                <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 21h10a2 2 0 002-2V9.414a1 1 0 00-.293-.707l-5.414-5.414A1 1 0 0012.586 3H7a2 2 0 00-2 2v14a2 2 0 002 2z" />
                                </svg>
                            </span>
                            <h2 id="registration-pdf-heading" class="text-base font-bold text-white">Dokumen PDF Pendaftaran</h2>
                        </div>
                        <p class="mt-1 text-xs leading-relaxed text-slate-400 max-w-2xl">
                            PDF disimpan secara privat. Draf bukan bukti resmi; bukti final tersedia setelah pendaftaran dikirim. Cetak dan bawa ke Loket PTSP MIN 1 Kota Madiun pada jam kerja resmi (Senin – Jumat 07.00 – 15.00 WIB).
                        </p>
                    </div>

                    @if ($registration?->isDraft())
                        <form method="POST" action="{{ route('applicant.registration.pdf.draft') }}">
                            @csrf
                            <button type="submit" class="w-full sm:w-auto px-4 py-2.5 rounded-xl text-xs font-bold text-white bg-teal-600 hover:bg-teal-500 transition shadow-md cursor-pointer">
                                Buat PDF Draf
                            </button>
                        </form>
                    @elseif ($registration?->isSubmitted())
                        @php
                            $latestFinalPdf = $pdfVersions
                                ? $pdfVersions->filter(fn ($version) => $version->document_type->value === 'final')->first()
                                : null;
                            $latestFinalStatus = $latestFinalPdf && isset($pdfVersionStatuses[$latestFinalPdf->id]) ? $pdfVersionStatuses[$latestFinalPdf->id] : null;
                        @endphp

                        @if ($latestFinalStatus !== 'current')
                            <form method="POST" action="{{ route('applicant.registration.pdf.final') }}">
                                @csrf
                                <button type="submit" class="w-full sm:w-auto px-4 py-2.5 rounded-xl text-xs font-bold text-white bg-emerald-600 hover:bg-emerald-500 transition shadow-md cursor-pointer">
                                    {{ $latestFinalPdf ? 'Perbarui PDF Final' : 'Buat Bukti PDF Final' }}
                                </button>
                            </form>
                        @endif
                    @endif
                </div>

                @if (! $registration)
                    <p class="mt-3 text-xs text-slate-400">
                        Pilih program pada periode pendaftaran agar sistem dapat mencatat dan membuat versi PDF draf.
                    </p>
                @elseif ($registration->isSubmitted())
                    <p class="mt-3 text-xs text-emerald-400/90 font-medium">
                        Pendaftaran telah dikirim. Dokumen ini hanya bukti pengiriman dan bukan keputusan seleksi.
                    </p>
                @else
                    <p class="mt-3 text-xs text-slate-400">
                        Draf dapat dibuat meskipun data belum lengkap dan akan menampilkan bagian yang belum diisi.
                    </p>
                @endif

                @if ($pdfVersions && $pdfVersions->isNotEmpty())
                    <div class="mt-4 border-t border-slate-800 pt-3">
                        <h3 class="text-xs font-bold uppercase tracking-wider text-slate-400">Riwayat versi PDF</h3>
                        <ul class="mt-2 divide-y divide-slate-800">
                            @foreach ($pdfVersions as $pdfVersion)
                                @php
                                    $pdfStatusLabels = [
                                        'current' => 'Versi terbaru sesuai data',
                                        'outdated' => 'Data berubah - perlu diperbarui',
                                        'superseded' => 'Versi terdahulu',
                                        'archived' => 'Arsip draf sebelum pengiriman',
                                    ];
                                @endphp
                                <li class="flex flex-col gap-2 py-2.5 text-xs sm:flex-row sm:items-center sm:justify-between">
                                    <span class="text-slate-200">
                                        {{ $pdfVersion->document_type->label() }} v{{ $pdfVersion->version_number }}
                                        <span class="text-slate-400">- {{ $pdfStatusLabels[$pdfVersionStatuses[$pdfVersion->id] ?? 'current'] ?? 'Tersimpan' }}</span>
                                    </span>
                                    <a
                                        href="{{ route('applicant.registration.pdf.download', $pdfVersion) }}"
                                        class="font-semibold text-teal-400 hover:text-teal-300 underline transition"
                                    >
                                        Unduh PDF
                                    </a>
                                </li>
                            @endforeach
                        </ul>
                    </div>
                @endif
            </section>

            {{-- CTA Action Bottom Bar --}}
            <div class="mt-8 pt-6 border-t border-slate-800 flex flex-col sm:flex-row items-center justify-between gap-4">
                <div class="text-xs text-slate-400">
                    @if ($registration?->isSubmitted())
                        <span class="font-semibold text-emerald-400">Pendaftaran telah dikirim. Anda dapat meninjau kembali status dan ringkasannya.</span>
                    @elseif (! $activePeriod)
                        <span>Perubahan pendaftaran terkunci sampai periode berikutnya dibuka.</span>
                    @elseif ($completion['is_all_complete'])
                        <span class="inline-flex items-center gap-1.5 text-emerald-400 font-semibold">
                            <svg class="w-4 h-4 text-emerald-400" fill="currentColor" viewBox="0 0 20 20">
                                <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd" />
                            </svg>
                            Data profil dan seluruh dokumen wajib telah lengkap.
                        </span>
                    @else
                        <span>Masih ada data formulir atau dokumen wajib yang belum lengkap.</span>
                    @endif
                </div>

                @if ($registration)
                    <a
                        href="{{ route('applicant.registration.review') }}"
                        class="inline-flex items-center justify-center gap-2 px-6 py-3 rounded-xl font-bold text-sm text-white bg-emerald-600 hover:bg-emerald-500 shadow-lg hover:shadow-emerald-600/30 transition-all duration-150 active:scale-[0.98] w-full sm:w-auto"
                    >
                        <span>{{ $registration->isSubmitted() ? 'Lihat Status Pendaftaran' : 'Tinjau Pendaftaran' }}</span>
                        <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M14 5l7 7m0 0l-7 7m7-7H3" />
                        </svg>
                    </a>
                @elseif ($activePeriod)
                    <a
                        href="{{ route('applicant.biodata.edit') }}"
                        class="inline-flex items-center justify-center gap-2 px-6 py-3 rounded-xl font-bold text-sm text-white bg-emerald-600 hover:bg-emerald-500 shadow-lg hover:shadow-emerald-600/30 transition-all duration-150 active:scale-[0.98] w-full sm:w-auto"
                    >
                        <span>{{ $completion['overall_percentage'] > 0 ? 'Lanjutkan Pengisian Formulir' : 'Mulai Pengisian Formulir' }}</span>
                        <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M14 5l7 7m0 0l-7 7m7-7H3" />
                        </svg>
                    </a>
                @endif
            </div>
        </div>
    </div>
</x-layouts.applicant>
