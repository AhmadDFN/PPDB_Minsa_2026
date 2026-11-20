<x-layouts.admin title="Dashboard Administrator">
    <x-slot:header>
        <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
            <div>
                <h1 class="text-2xl font-bold text-gray-900 tracking-tight">Dashboard Administrator</h1>
                <p class="text-sm text-gray-500 mt-1">
                    {{ \Carbon\Carbon::now('Asia/Jakarta')->translatedFormat('l, d F Y') }} &bull; Ringkasan dan metrik operasional pendaftaran peserta didik baru.
                </p>
            </div>
            <div class="flex items-center gap-3">
                @if ($activePeriod)
                    <x-ui.badge variant="success" dot>Periode Aktif</x-ui.badge>
                    <span class="text-xs font-semibold text-gray-600 bg-gray-100 px-2.5 py-1 rounded-md border border-gray-200">
                        TP {{ $activePeriod->academic_year }}
                    </span>
                @else
                    <x-ui.badge variant="danger" dot>Tidak Ada Periode Aktif</x-ui.badge>
                @endif

                @can('view_applicants')
                    <a
                        href="{{ route('admin.applicants.index') }}"
                        class="inline-flex items-center gap-1.5 px-3 py-1.5 rounded-lg text-xs font-semibold bg-primary-600 text-white hover:bg-primary-700 shadow-xs transition-colors"
                    >
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4.354a4 4 0 110 5.292M15 21H3v-1a6 6 0 0112 0v1zm0 0h6v-1a6 6 0 00-9-5.197M13 7a4 4 0 11-8 0 4 4 0 018 0z" />
                        </svg>
                        <span>Data Pendaftar</span>
                    </a>
                @endcan
            </div>
        </div>
    </x-slot:header>

    <div class="space-y-6">
        {{-- Section 1: Active Period Hero Banner --}}
        @if ($activePeriod)
            <div class="rounded-xl border border-primary-200/80 bg-gradient-to-r from-primary-900 via-primary-800 to-primary-700 text-white p-6 shadow-sm relative overflow-hidden">
                <div class="absolute -right-6 -bottom-6 w-48 h-48 bg-white/5 rounded-full pointer-events-none"></div>
                <div class="relative z-10 flex flex-col md:flex-row md:items-center md:justify-between gap-6">
                    <div class="space-y-2 max-w-2xl">
                        <div class="inline-flex items-center gap-2 px-2.5 py-1 rounded-full bg-white/15 text-primary-100 text-xs font-medium backdrop-blur-xs">
                            <span class="w-2 h-2 rounded-full bg-accent-400 animate-pulse"></span>
                            <span>Tahun Ajaran {{ $activePeriod->academic_year }}</span>
                        </div>
                        <h2 class="text-xl sm:text-2xl font-extrabold tracking-tight text-white">
                            {{ $activePeriod->name }}
                        </h2>
                        @if ($activePeriod->description)
                            <p class="text-xs sm:text-sm text-primary-100/90 leading-relaxed line-clamp-2">
                                {{ $activePeriod->description }}
                            </p>
                        @endif
                        <div class="flex flex-wrap items-center gap-y-1 gap-x-4 text-xs text-primary-100/80 pt-1">
                            <div class="flex items-center gap-1.5">
                                <svg class="w-4 h-4 text-accent-300 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z" />
                                </svg>
                                <span>Mulai: <strong class="text-white">{{ $periodTimeInfo['start_formatted'] ?? '-' }}</strong></span>
                            </div>
                            <span class="hidden sm:inline text-primary-300">&bull;</span>
                            <div class="flex items-center gap-1.5">
                                <svg class="w-4 h-4 text-accent-300 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z" />
                                </svg>
                                <span>Batas: <strong class="text-white">{{ $periodTimeInfo['end_formatted'] ?? '-' }}</strong></span>
                            </div>
                        </div>
                    </div>

                    <div class="shrink-0 flex flex-col items-start md:items-end gap-2 bg-white/10 backdrop-blur-xs px-5 py-3.5 rounded-xl border border-white/15">
                        <span class="text-[11px] font-semibold uppercase tracking-wider text-primary-200">Status Waktu</span>
                        <div class="text-sm sm:text-base font-bold text-accent-300 flex items-center gap-2">
                            <span>{{ $periodTimeInfo['countdown_text'] ?? 'Sedang Berlangsung' }}</span>
                        </div>
                        <span class="text-[10px] text-primary-200">Waktu Server: Asia/Jakarta (WIB)</span>
                    </div>
                </div>
            </div>
        @else
            <div class="rounded-xl border border-amber-200 bg-amber-50 p-6 text-amber-900 shadow-xs">
                <div class="flex items-start gap-4">
                    <div class="w-10 h-10 rounded-lg bg-amber-100 text-amber-600 flex items-center justify-center shrink-0">
                        <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z" />
                        </svg>
                    </div>
                    <div class="space-y-1">
                        <h2 class="text-base font-bold text-amber-900">Tidak Ada Periode Pendaftaran Aktif</h2>
                        <p class="text-xs sm:text-sm text-amber-800 leading-relaxed">
                            Saat ini belum ada periode penerimaan peserta didik baru yang berstatus <strong>Aktif</strong>. Seluruh metrik pendaftaran di bawah menampilkan angka nol.
                        </p>
                    </div>
                </div>
            </div>
        @endif

        {{-- Section 2: Real Data Statistics Cards --}}
        <div>
            <div class="flex items-center justify-between mb-3">
                <h2 class="text-sm font-bold uppercase tracking-wider text-gray-500">Statistik Pendaftaran Periode Ini</h2>
                <span class="text-xs text-gray-400">Data Teragregasi Real-Time</span>
            </div>
            <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-5 gap-4">
                {{-- Card 1: Total Pendaftar --}}
                <div class="bg-white rounded-xl border border-gray-200 p-5 shadow-xs hover:border-primary-300 transition-colors">
                    <div class="flex items-center justify-between">
                        <p class="text-xs font-semibold text-gray-500 uppercase tracking-wider">Total Pendaftar</p>
                        <div class="w-9 h-9 rounded-lg bg-primary-50 text-primary-600 flex items-center justify-center">
                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0zm6 3a2 2 0 11-4 0 2 2 0 014 0zM7 10a2 2 0 11-4 0 2 2 0 014 0z" />
                            </svg>
                        </div>
                    </div>
                    <div class="mt-3">
                        <p class="text-2xl font-extrabold text-gray-900 tracking-tight">{{ number_format($stats['total'], 0, ',', '.') }}</p>
                        <p class="text-[11px] text-gray-400 mt-0.5">Semua status akun pendaftar</p>
                    </div>
                </div>

                {{-- Card 2: Berkas Terkirim (SUBMITTED) --}}
                <div class="bg-white rounded-xl border border-green-200 p-5 shadow-xs hover:border-green-300 transition-colors">
                    <div class="flex items-center justify-between">
                        <p class="text-xs font-semibold text-green-700 uppercase tracking-wider">Berkas Terkirim</p>
                        <div class="w-9 h-9 rounded-lg bg-green-50 text-green-600 flex items-center justify-center">
                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z" />
                            </svg>
                        </div>
                    </div>
                    <div class="mt-3">
                        <p class="text-2xl font-extrabold text-green-800 tracking-tight">{{ number_format($stats['submitted'], 0, ',', '.') }}</p>
                        <p class="text-[11px] text-green-600 font-medium mt-0.5">Telah dikirim resmi & siap verifikasi</p>
                    </div>
                </div>

                {{-- Card 3: Draf (Belum Dikirim) --}}
                <div class="bg-white rounded-xl border border-amber-200 p-5 shadow-xs hover:border-amber-300 transition-colors">
                    <div class="flex items-center justify-between">
                        <p class="text-xs font-semibold text-amber-700 uppercase tracking-wider">Draf Pendaftaran</p>
                        <div class="w-9 h-9 rounded-lg bg-amber-50 text-amber-600 flex items-center justify-center">
                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z" />
                            </svg>
                        </div>
                    </div>
                    <div class="mt-3">
                        <p class="text-2xl font-extrabold text-amber-800 tracking-tight">{{ number_format($stats['draft'], 0, ',', '.') }}</p>
                        <p class="text-[11px] text-amber-600 font-medium mt-0.5">Pengisian formulir belum selesai</p>
                    </div>
                </div>

                {{-- Card 4: Program Reguler --}}
                <div class="bg-white rounded-xl border border-gray-200 p-5 shadow-xs hover:border-primary-300 transition-colors">
                    <div class="flex items-center justify-between">
                        <p class="text-xs font-semibold text-gray-600 uppercase tracking-wider">Program Reguler</p>
                        <div class="w-9 h-9 rounded-lg bg-sky-50 text-sky-600 flex items-center justify-center">
                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6.253v13m0-13C10.832 5.477 9.246 5 7.5 5S4.168 5.477 3 6.253v13C4.168 18.477 5.754 18 7.5 18s3.332.477 4.5 1.253m0-13C13.168 5.477 14.754 5 16.5 5c1.747 0 3.332.477 4.5 1.253v13C19.832 18.477 18.247 18 16.5 18c-1.746 0-3.332.477-4.5 1.253" />
                            </svg>
                        </div>
                    </div>
                    <div class="mt-3">
                        <p class="text-2xl font-extrabold text-gray-900 tracking-tight">{{ number_format($stats['reguler'], 0, ',', '.') }}</p>
                        <p class="text-[11px] text-gray-400 mt-0.5">Pendaftar jalur reguler</p>
                    </div>
                </div>

                {{-- Card 5: Program Unggulan --}}
                <div class="bg-white rounded-xl border border-secondary-200 p-5 shadow-xs hover:border-secondary-300 transition-colors">
                    <div class="flex items-center justify-between">
                        <p class="text-xs font-semibold text-secondary-700 uppercase tracking-wider">Program Unggulan</p>
                        <div class="w-9 h-9 rounded-lg bg-emerald-50 text-emerald-600 flex items-center justify-center">
                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11.049 2.927c.3-.921 1.603-.921 1.902 0l1.519 4.674a1 1 0 00.95.69h4.915c.969 0 1.371 1.24.588 1.81l-3.976 2.888a1 1 0 00-.363 1.118l1.518 4.674c.3.922-.755 1.688-1.538 1.118l-3.976-2.888a1 1 0 00-1.176 0l-3.976 2.888c-.783.57-1.838-.197-1.538-1.118l1.518-4.674a1 1 0 00-.363-1.118l-3.976-2.888c-.784-.57-.38-1.81.588-1.81h4.914a1 1 0 00.951-.69l1.519-4.674z" />
                            </svg>
                        </div>
                    </div>
                    <div class="mt-3">
                        <p class="text-2xl font-extrabold text-secondary-800 tracking-tight">{{ number_format($stats['unggulan'], 0, ',', '.') }}</p>
                        <p class="text-[11px] text-secondary-600 font-medium mt-0.5">Pendaftar jalur unggulan</p>
                    </div>
                </div>
            </div>
        </div>

        {{-- Section 3: Dual Column Grid (Recent Submissions + Quick Actions/System Info) --}}
        <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
            {{-- Left Column (2 Cols): Latest 5 Submitted Registrations --}}
            <div class="lg:col-span-2 space-y-6">
                <div class="bg-white rounded-xl border border-gray-200 shadow-xs overflow-hidden">
                    <div class="px-6 py-4 border-b border-gray-100 flex items-center justify-between">
                        <div>
                            <h2 class="text-base font-bold text-gray-900">Pendaftaran Terbaru</h2>
                            <p class="text-xs text-gray-500 mt-0.5">5 formulir pendaftaran terakhir yang dikirim secara resmi</p>
                        </div>
                        <div class="flex items-center gap-2">
                            @can('view_applicants')
                                <a
                                    href="{{ route('admin.applicants.index') }}"
                                    class="text-xs font-semibold text-primary-600 hover:text-primary-700 hover:underline flex items-center gap-1"
                                >
                                    <span>Lihat Semua</span>
                                    <span>&rarr;</span>
                                </a>
                            @endcan
                        </div>
                    </div>

                    @if ($latestSubmissions->isNotEmpty())
                        <div class="overflow-x-auto">
                            <table class="w-full text-left text-sm">
                                <thead class="bg-gray-50 text-[11px] font-bold text-gray-500 uppercase tracking-wider border-b border-gray-100">
                                    <tr>
                                        <th scope="col" class="py-3 px-4 sm:px-6">No. Pendaftaran</th>
                                        <th scope="col" class="py-3 px-4">Nama Calon Siswa</th>
                                        <th scope="col" class="py-3 px-4">Program</th>
                                        <th scope="col" class="py-3 px-4">Waktu Kirim</th>
                                        <th scope="col" class="py-3 px-4">Status</th>
                                        <th scope="col" class="py-3 px-4 sm:px-6 text-right">Aksi</th>
                                    </tr>
                                </thead>
                                <tbody class="divide-y divide-gray-100">
                                    @foreach ($latestSubmissions as $submission)
                                        <tr class="hover:bg-gray-50/70 transition-colors">
                                            <td class="py-3.5 px-4 sm:px-6 font-mono text-xs font-semibold text-gray-800">
                                                {{ $submission->registration_number ?? '-' }}
                                            </td>
                                            <td class="py-3.5 px-4 font-medium text-gray-900">
                                                {{ $submission->applicant?->full_name ?? '-' }}
                                            </td>
                                            <td class="py-3.5 px-4">
                                                @php
                                                    $programCode = strtoupper($submission->admissionPeriodProgram?->program?->code ?? '');
                                                    $programName = $submission->admissionPeriodProgram?->program?->name ?? '-';
                                                @endphp
                                                @if ($programCode === 'UNGGULAN')
                                                    <span class="inline-flex items-center px-2 py-0.5 rounded text-[11px] font-medium bg-emerald-50 text-emerald-700 border border-emerald-200">
                                                        {{ $programName }}
                                                    </span>
                                                @else
                                                    <span class="inline-flex items-center px-2 py-0.5 rounded text-[11px] font-medium bg-sky-50 text-sky-700 border border-sky-200">
                                                        {{ $programName }}
                                                    </span>
                                                @endif
                                            </td>
                                            <td class="py-3.5 px-4 text-xs text-gray-500 whitespace-nowrap">
                                                {{ $submission->submitted_at ? $submission->submitted_at->setTimezone('Asia/Jakarta')->translatedFormat('d M Y, H:i') . ' WIB' : '-' }}
                                            </td>
                                            <td class="py-3.5 px-4 whitespace-nowrap">
                                                <x-ui.badge variant="success" dot>Terkirim</x-ui.badge>
                                            </td>
                                            <td class="py-3.5 px-4 sm:px-6 text-right whitespace-nowrap">
                                                @can('view_applicants')
                                                    <a
                                                        href="{{ route('admin.applicants.show', $submission) }}"
                                                        class="inline-flex items-center gap-1 px-2.5 py-1 rounded-md text-xs font-semibold text-primary-700 bg-primary-50 hover:bg-primary-100 border border-primary-200 transition-colors"
                                                    >
                                                        <span>Detail</span>
                                                    </a>
                                                @endcan
                                            </td>
                                        </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>
                    @else
                        <div class="p-8 text-center">
                            <div class="w-12 h-12 rounded-full bg-gray-100 text-gray-400 flex items-center justify-center mx-auto mb-3">
                                <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" />
                                </svg>
                            </div>
                            <h3 class="text-sm font-semibold text-gray-900">Belum Ada Pendaftaran Terkirim</h3>
                            <p class="text-xs text-gray-500 mt-1 max-w-sm mx-auto">
                                Pendaftaran yang telah resmi dikirimkan oleh orang tua/wali calon peserta didik akan langsung muncul pada daftar ini.
                            </p>
                        </div>
                    @endif
                </div>

                {{-- Activity Log Summary Card --}}
                <div class="bg-white rounded-xl border border-gray-200 shadow-xs overflow-hidden">
                    <div class="px-6 py-4 border-b border-gray-100 flex items-center justify-between">
                        <div>
                            <h2 class="text-base font-bold text-gray-900">Aktivitas Pendaftaran Terbaru</h2>
                            <p class="text-xs text-gray-500 mt-0.5">Catatan audit riwayat pengisian dan perubahan data formulir</p>
                        </div>
                        <span class="text-xs font-semibold text-gray-500 bg-gray-100 px-2.5 py-1 rounded-md">
                            Audit Trail
                        </span>
                    </div>

                    @if ($recentActivities->isNotEmpty())
                        <div class="divide-y divide-gray-100">
                            @foreach ($recentActivities as $activity)
                                <div class="p-4 hover:bg-gray-50/70 transition-colors flex items-start justify-between gap-3 text-xs">
                                    <div class="space-y-1">
                                        <div class="flex items-center gap-2">
                                            <span class="font-bold text-gray-900 capitalize">{{ str_replace('_', ' ', $activity->action) }}</span>
                                            @if ($activity->registration?->registration_number)
                                                <span class="px-1.5 py-0.5 rounded text-[10px] font-mono bg-gray-100 text-gray-700">
                                                    {{ $activity->registration->registration_number }}
                                                </span>
                                            @endif
                                        </div>
                                        <div class="text-gray-600">
                                            Pendaftar: <strong>{{ $activity->registration?->applicant?->full_name ?? '-' }}</strong>
                                            &bull; Aktor: {{ $activity->user?->name ?? 'Sistem' }}
                                        </div>
                                        @if (!empty($activity->changes['fields']))
                                            <div class="text-[11px] text-gray-400">
                                                Field: {{ implode(', ', $activity->changes['fields']) }}
                                            </div>
                                        @endif
                                    </div>
                                    <span class="text-[11px] text-gray-400 whitespace-nowrap font-mono shrink-0">
                                        {{ $activity->created_at ? $activity->created_at->setTimezone('Asia/Jakarta')->diffForHumans() : '-' }}
                                    </span>
                                </div>
                            @endforeach
                        </div>
                    @else
                        <div class="p-6 text-center text-xs text-gray-400 italic">
                            Belum ada riwayat aktivitas pendaftaran di sistem.
                        </div>
                    @endif
                </div>
            </div>

            {{-- Right Column (1 Col): Quick Actions & System Information --}}
            <div class="space-y-6">
                {{-- Quick Actions Card --}}
                <div class="bg-white rounded-xl border border-gray-200 shadow-xs p-5">
                    <h2 class="text-sm font-bold uppercase tracking-wider text-gray-500 mb-4">Aksi Operasional Cepat</h2>
                    <div class="space-y-3">
                        @can('view_applicants')
                            <a
                                href="{{ route('admin.applicants.index') }}"
                                class="flex items-start gap-3 p-3 rounded-lg border border-primary-200 bg-primary-50/70 hover:bg-primary-100/70 transition group"
                            >
                                <div class="w-9 h-9 rounded-lg bg-primary-600 text-white flex items-center justify-center shrink-0 shadow-xs">
                                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4.354a4 4 0 110 5.292M15 21H3v-1a6 6 0 0112 0v1zm0 0h6v-1a6 6 0 00-9-5.197M13 7a4 4 0 11-8 0 4 4 0 018 0z" />
                                    </svg>
                                </div>
                                <div class="min-w-0">
                                    <span class="text-xs font-bold text-primary-900 group-hover:text-primary-700 transition block">
                                        Kelola Data Pendaftar
                                    </span>
                                    <span class="text-[11px] text-primary-700/80 line-clamp-2 mt-0.5">
                                        Pencarian pendaftar, penyaringan status, dan verifikasi berkas formulir.
                                    </span>
                                </div>
                            </a>
                        @endcan

                        @can('reopen_registration')
                            <a
                                href="{{ route('admin.registrations.index') }}"
                                class="flex items-start gap-3 p-3 rounded-lg border border-gray-100 bg-gray-50/60 hover:bg-primary-50 hover:border-primary-200 transition group"
                            >
                                <div class="w-9 h-9 rounded-lg bg-primary-100 text-primary-700 flex items-center justify-center shrink-0 group-hover:bg-primary-600 group-hover:text-white transition">
                                    <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v6h6M20 20v-6h-6M5 9a7 7 0 0112-3l3 3M19 15a7 7 0 01-12 3l-3-3" />
                                    </svg>
                                </div>
                                <div class="min-w-0">
                                    <span class="text-xs font-bold text-gray-900 group-hover:text-primary-700 transition block">
                                        Buka Ulang Pendaftaran
                                    </span>
                                    <span class="text-[11px] text-gray-500 line-clamp-2 mt-0.5">
                                        Beri akses perbaikan data sementara bagi berkas yang sudah terkirim.
                                    </span>
                                </div>
                            </a>
                        @endcan

                        <a
                            href="{{ url('/') }}"
                            target="_blank"
                            class="flex items-start gap-3 p-3 rounded-lg border border-gray-100 bg-gray-50/60 hover:bg-emerald-50 hover:border-emerald-200 transition group"
                        >
                            <div class="w-9 h-9 rounded-lg bg-emerald-100 text-emerald-700 flex items-center justify-center shrink-0 group-hover:bg-secondary-600 group-hover:text-white transition">
                                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 6H6a2 2 0 00-2 2v10a2 2 0 002 2h10a2 2 0 002-2v-4M14 4h6m0 0v6m0-6L10 14" />
                                </svg>
                            </div>
                            <div class="min-w-0">
                                <span class="text-xs font-bold text-gray-900 group-hover:text-secondary-700 transition block">
                                    Lihat Portal Publik PPDB
                                </span>
                                <span class="text-[11px] text-gray-500 line-clamp-2 mt-0.5">
                                    Buka landing page resmi yang diakses oleh masyarakat umum.
                                </span>
                            </div>
                        </a>
                    </div>
                </div>

                {{-- System Information Card --}}
                <div class="bg-white rounded-xl border border-gray-200 shadow-xs p-5">
                    <h2 class="text-sm font-bold uppercase tracking-wider text-gray-500 mb-4">Informasi Sistem & Akun</h2>
                    <dl class="space-y-3 text-xs">
                        <div class="flex items-center justify-between pb-2 border-b border-gray-100">
                            <dt class="text-gray-500 font-medium">Madrasah</dt>
                            <dd class="text-gray-900 font-semibold">MIN 1 Kota Madiun</dd>
                        </div>
                        <div class="flex items-center justify-between pb-2 border-b border-gray-100">
                            <dt class="text-gray-500 font-medium">Petugas Login</dt>
                            <dd class="text-gray-900 font-semibold">{{ auth()->user()?->name ?? 'Administrator' }}</dd>
                        </div>
                        <div class="flex items-center justify-between pb-2 border-b border-gray-100">
                            <dt class="text-gray-500 font-medium">Peran Pengguna</dt>
                            <dd>
                                @if (auth()->user()?->hasRole('superadmin'))
                                    <span class="inline-flex items-center px-2 py-0.5 rounded text-[10px] font-bold bg-purple-50 text-purple-700 border border-purple-200">
                                        Super Administrator
                                    </span>
                                @elseif (auth()->user()?->hasRole('admin_ppdb'))
                                    <span class="inline-flex items-center px-2 py-0.5 rounded text-[10px] font-bold bg-primary-50 text-primary-700 border border-primary-200">
                                        Admin PPDB
                                    </span>
                                @else
                                    <span class="inline-flex items-center px-2 py-0.5 rounded text-[10px] font-bold bg-gray-50 text-gray-700 border border-gray-200">
                                        Staff
                                    </span>
                                @endif
                            </dd>
                        </div>
                        <div class="flex items-center justify-between pb-2 border-b border-gray-100">
                            <dt class="text-gray-500 font-medium">Zona Waktu</dt>
                            <dd class="text-gray-900 font-medium">Asia/Jakarta (WIB)</dd>
                        </div>
                        <div class="flex items-center justify-between">
                            <dt class="text-gray-500 font-medium">Versi Aplikasi</dt>
                            <dd class="text-gray-600 font-mono">v1.0 (Laravel {{ app()->version() }})</dd>
                        </div>
                    </dl>
                </div>
            </div>
        </div>
    </div>
</x-layouts.admin>
