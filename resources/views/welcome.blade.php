<x-layouts.app title="PPDB 2026/2027 — MIN 1 Kota Madiun">
    {{-- =========================================================================
         SECTION 1: HERO SCREEN (Commanding Academic Entrance)
         ========================================================================= --}}
    <section class="relative bg-gradient-to-br from-[#07321C] via-[#0B4D36] to-[#0A3644] text-white overflow-hidden py-16 sm:py-24 lg:py-28 border-b border-secondary-900/50">
        {{-- Subtle Islamic geometric lattice background --}}
        <div class="absolute inset-0 opacity-[0.04] pointer-events-none">
            <svg class="w-full h-full" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 80 80" preserveAspectRatio="none">
                <pattern id="islamic-grid" width="20" height="20" patternUnits="userSpaceOnUse">
                    <path d="M10 0 L20 10 L10 20 L0 10 Z" fill="none" stroke="currentColor" stroke-width="0.8" />
                    <circle cx="10" cy="10" r="3" fill="none" stroke="currentColor" stroke-width="0.5" />
                </pattern>
                <rect width="100%" height="100%" fill="url(#islamic-grid)" />
            </svg>
        </div>

        {{-- Ambient decorative glow --}}
        <div class="absolute -top-32 -right-32 w-96 h-96 bg-accent-400/10 rounded-full blur-3xl pointer-events-none"></div>
        <div class="absolute -bottom-32 -left-32 w-96 h-96 bg-primary-500/10 rounded-full blur-3xl pointer-events-none"></div>

        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 relative z-10">
            <div class="grid grid-cols-1 lg:grid-cols-12 gap-12 items-center">
                {{-- Left Column: Headline & Action --}}
                <div class="lg:col-span-7 space-y-6">
                    {{-- Official Badges --}}
                    <div class="inline-flex items-center gap-2.5 px-3.5 py-1.5 rounded-full bg-white/10 backdrop-blur-md text-xs font-semibold text-secondary-200 border border-white/15 shadow-xs">
                        <span class="w-2 h-2 rounded-full bg-accent-400 animate-pulse"></span>
                        <span class="text-white font-bold">PPDB 2026/2027</span>
                        <span class="text-white/40">&bull;</span>
                        <span class="text-secondary-100">Terakreditasi A BAN-S/M</span>
                    </div>

                    {{-- Main Category H1 --}}
                    <h1 class="text-3xl sm:text-5xl lg:text-[3.25rem] font-extrabold tracking-tight text-white leading-[1.15]">
                        Madrasah Unggul Pencetak Generasi <span class="text-accent-300 underline decoration-accent-400/60 decoration-wavy decoration-2 underline-offset-8">Qur’ani & Cerdas</span>
                    </h1>

                    {{-- Truthful Hard Fact Subtitle --}}
                    <p class="text-base sm:text-lg text-primary-100/90 font-normal leading-relaxed max-w-2xl">
                        Pendaftaran Peserta Didik Baru (PPDB) Kelas 1 MIN 1 Kota Madiun dibuka secara terpadu melalui <strong class="text-white">Program Unggulan Tahfidz & Sains</strong> serta <strong class="text-white">Program Reguler</strong>.
                    </p>

                    {{-- Core Actions --}}
                    <div class="pt-2 flex flex-col sm:flex-row items-stretch sm:items-center gap-4">
                        <a
                            href="{{ route('register') }}"
                            class="inline-flex items-center justify-center gap-2.5 px-7 py-3.5 rounded-xl font-bold text-sm text-neutral-900 bg-accent-400 hover:bg-accent-300 shadow-lg hover:shadow-xl transition-all duration-150 active:scale-[0.98]"
                        >
                            <span>Daftar Akun Calon Siswa</span>
                            <svg class="w-4 h-4 text-neutral-900" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M14 5l7 7m0 0l-7 7m7-7H3" />
                            </svg>
                        </a>

                        <a
                            href="{{ route('login') }}"
                            class="inline-flex items-center justify-center gap-2 px-6 py-3.5 rounded-xl font-semibold text-sm text-white bg-white/10 hover:bg-white/20 border border-white/20 backdrop-blur-md transition-all duration-150 active:scale-[0.98]"
                        >
                            <svg class="w-4 h-4 text-accent-300" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 16l-4-4m0 0l4-4m-4 4h14m-5 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h7a3 3 0 013 3v1" />
                            </svg>
                            <span>Masuk dengan NIK</span>
                        </a>
                    </div>

                    {{-- Micro Trust Indicators --}}
                    <div class="pt-4 flex flex-wrap items-center gap-y-2 gap-x-6 text-xs text-primary-200/80">
                        <div class="flex items-center gap-1.5">
                            <svg class="w-4 h-4 text-accent-400" fill="currentColor" viewBox="0 0 20 20">
                                <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd" />
                            </svg>
                            <span>1 NIK untuk 1 Siswa</span>
                        </div>
                        <div class="flex items-center gap-1.5">
                            <svg class="w-4 h-4 text-accent-400" fill="currentColor" viewBox="0 0 20 20">
                                <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd" />
                            </svg>
                            <span>Enkripsi Data Pribadi (AES-256)</span>
                        </div>
                        <div class="flex items-center gap-1.5">
                            <svg class="w-4 h-4 text-accent-400" fill="currentColor" viewBox="0 0 20 20">
                                <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd" />
                            </svg>
                            <span>Tanpa Pembatasan Kuota Pendaftaran</span>
                        </div>
                    </div>
                </div>

                {{-- Right Column: Live Status & Portal Card --}}
                <div class="lg:col-span-5">
                    <div class="rounded-2xl bg-white/10 backdrop-blur-xl border border-white/20 p-6 sm:p-7 shadow-2xl text-white space-y-5">
                        {{-- Card Header --}}
                        <div class="flex items-center justify-between pb-4 border-b border-white/10">
                            <div>
                                <span class="text-[11px] font-bold text-accent-300 uppercase tracking-wider block">Status Sistem</span>
                                <h3 class="text-base font-extrabold text-white">Informasi Periode PPDB</h3>
                            </div>
                            <span class="inline-flex items-center gap-1 px-2.5 py-1 rounded-full text-xs font-bold bg-green-500/20 text-green-300 border border-green-400/30">
                                <span class="w-2 h-2 rounded-full bg-green-400 animate-pulse"></span>
                                Online & Aktif
                            </span>
                        </div>

                        {{-- Period Facts --}}
                        <div class="space-y-3.5 text-xs text-primary-100">
                            <div class="flex items-start justify-between gap-2">
                                <span class="text-white/60">Tahun Ajaran</span>
                                <span class="font-bold text-white text-right">{{ $activePeriod->academic_year ?? '2026/2027' }}</span>
                            </div>

                            <div class="flex items-start justify-between gap-2">
                                <span class="text-white/60">Jalur Pendaftaran</span>
                                <span class="font-bold text-white text-right">Unggulan &bull; Reguler</span>
                            </div>

                            <div class="flex items-start justify-between gap-2">
                                <span class="text-white/60">Tanggal Acuan Usia</span>
                                <span class="font-bold text-accent-300 text-right">
                                    {{ $activePeriod?->age_reference_date?->format('d F Y') ?? '01 Juli 2026' }}
                                </span>
                            </div>

                            <div class="flex items-start justify-between gap-2">
                                <span class="text-white/60">Pelayanan Loket PTSP</span>
                                <span class="font-bold text-white text-right">Senin – Jumat, 08.00 – 14.00 WIB</span>
                            </div>
                        </div>

                        {{-- Quick Steps Teaser --}}
                        <div class="p-4 rounded-xl bg-black/20 border border-white/10 text-xs text-primary-100/90 leading-relaxed">
                            <p class="font-semibold text-white mb-1.5 flex items-center gap-1.5">
                                <svg class="w-4 h-4 text-accent-300" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                                </svg>
                                Alur Mandiri 4 Tahap
                            </p>
                            Daftarkan akun dengan NIK calon siswa, isi formulir profil, unggah dokumen persyaratan, dan unduh bukti pendaftaran untuk diserahkan ke madrasah.
                        </div>

                        {{-- Direct Entry Link --}}
                        <a
                            href="#alur"
                            class="block w-full text-center py-2.5 rounded-xl text-xs font-bold text-white bg-white/10 hover:bg-white/20 border border-white/20 transition"
                        >
                            Pelajari Alur Lengkap &darr;
                        </a>
                    </div>
                </div>
            </div>
        </div>
    </section>

    {{-- =========================================================================
         SECTION 2: PROGRAM PENDIDIKAN (Unggulan & Reguler)
         ========================================================================= --}}
    <section id="program" class="py-20 sm:py-24 bg-white border-b border-neutral-200/80">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            {{-- Section Title --}}
            <div class="text-center max-w-3xl mx-auto mb-16">
                <span class="text-xs font-bold uppercase tracking-wider text-secondary-700 bg-secondary-50 border border-secondary-200 px-3 py-1 rounded-full">
                    Pilihan Jalur Masuk
                </span>
                <h2 class="text-3xl sm:text-4xl font-extrabold text-neutral-900 tracking-tight mt-3">
                    Program Pendidikan MIN 1 Kota Madiun
                </h2>
                <p class="text-sm sm:text-base text-neutral-600 mt-3 leading-relaxed">
                    Dua program unggulan yang dirancang komprehensif untuk mengembangkan potensi spiritual, intelektual, dan akhlak mulia calon peserta didik baru.
                </p>
            </div>

            {{-- 2 Program Cards --}}
            <div class="grid grid-cols-1 md:grid-cols-2 gap-8 lg:gap-10">
                {{-- 1. Program Unggulan --}}
                <div class="group relative rounded-2xl border-2 border-primary-500/80 bg-white p-8 sm:p-10 shadow-sm hover:shadow-xl transition-all duration-300 hover:-translate-y-1 flex flex-col justify-between">
                    <div>
                        <div class="flex items-center justify-between gap-3 mb-6">
                            <span class="inline-flex items-center gap-1.5 px-3 py-1 rounded-full text-xs font-extrabold bg-primary-100 text-primary-800 border border-primary-200">
                                <span>★</span> Program Unggulan
                            </span>
                            <span class="text-xs font-semibold text-neutral-400">Kelas 1 MI</span>
                        </div>

                        <h3 class="text-2xl font-extrabold text-neutral-900 mb-3 group-hover:text-primary-700 transition-colors">
                            Kelas Unggulan Tahfidz & Sains
                        </h3>

                        <p class="text-sm text-neutral-600 leading-relaxed mb-6">
                            Program akselerasi bakat minat intensif dengan fokus pada pembiasaan tilawah dan hafalan Al-Qur'an terpadu, literasi sains madrasah, serta pembelajaran digital modern.
                        </p>

                        <div class="pt-6 border-t border-neutral-100 space-y-3 text-xs text-neutral-700">
                            <div class="flex items-start gap-3">
                                <span class="w-5 h-5 rounded-full bg-secondary-100 text-secondary-700 font-bold flex items-center justify-center shrink-0 mt-0.5">✓</span>
                                <span><strong>Target Hafalan Juz 30:</strong> Pembimbingan tilawah dan tahsin tartil intensif.</span>
                            </div>
                            <div class="flex items-start gap-3">
                                <span class="w-5 h-5 rounded-full bg-secondary-100 text-secondary-700 font-bold flex items-center justify-center shrink-0 mt-0.5">✓</span>
                                <span><strong>Digital Classroom & Sains:</strong> Pengenalan eksperimen dasar sains dan integrasi teknologi pembelajaran.</span>
                            </div>
                            <div class="flex items-start gap-3">
                                <span class="w-5 h-5 rounded-full bg-secondary-100 text-secondary-700 font-bold flex items-center justify-center shrink-0 mt-0.5">✓</span>
                                <span><strong>Pembinaan Prestasi & Olimpiade:</strong> Pendampingan kompetisi akademik dan keagamaan sejak dini.</span>
                            </div>
                        </div>
                    </div>

                    <div class="mt-8 pt-6 border-t border-neutral-100">
                        <a
                            href="{{ route('register') }}"
                            class="inline-flex items-center justify-center w-full py-3 rounded-xl text-xs font-bold text-white bg-primary-600 hover:bg-primary-700 shadow-sm transition-all"
                        >
                            <span>Daftar Jalur Unggulan</span>
                            <span class="ml-1.5">&rarr;</span>
                        </a>
                    </div>
                </div>

                {{-- 2. Program Reguler --}}
                <div class="group relative rounded-2xl border-2 border-secondary-500/80 bg-white p-8 sm:p-10 shadow-sm hover:shadow-xl transition-all duration-300 hover:-translate-y-1 flex flex-col justify-between">
                    <div>
                        <div class="flex items-center justify-between gap-3 mb-6">
                            <span class="inline-flex items-center gap-1.5 px-3 py-1 rounded-full text-xs font-extrabold bg-secondary-100 text-secondary-800 border border-secondary-200">
                                <span>✦</span> Program Reguler
                            </span>
                            <span class="text-xs font-semibold text-neutral-400">Kelas 1 MI</span>
                        </div>

                        <h3 class="text-2xl font-extrabold text-neutral-900 mb-3 group-hover:text-secondary-700 transition-colors">
                            Kelas Reguler Berkarakter
                        </h3>

                        <p class="text-sm text-neutral-600 leading-relaxed mb-6">
                            Pendidikan dasar Islam komprehensif mengacu pada Kurikulum Merdeka nasional yang dipadukan dengan penguatan nilai-nilai keislaman dan pembiasaan akhlak terpuji.
                        </p>

                        <div class="pt-6 border-t border-neutral-100 space-y-3 text-xs text-neutral-700">
                            <div class="flex items-start gap-3">
                                <span class="w-5 h-5 rounded-full bg-secondary-100 text-secondary-700 font-bold flex items-center justify-center shrink-0 mt-0.5">✓</span>
                                <span><strong>Kurikulum Merdeka Kemenag:</strong> Pembelajaran tematik terpadu yang interaktif dan ramah anak.</span>
                            </div>
                            <div class="flex items-start gap-3">
                                <span class="w-5 h-5 rounded-full bg-secondary-100 text-secondary-700 font-bold flex items-center justify-center shrink-0 mt-0.5">✓</span>
                                <span><strong>Pembiasaan Ibadah Harian:</strong> Sholat Dhuha dan Sholat Dzuhur berjamaah di masjid madrasah.</span>
                            </div>
                            <div class="flex items-start gap-3">
                                <span class="w-5 h-5 rounded-full bg-secondary-100 text-secondary-700 font-bold flex items-center justify-center shrink-0 mt-0.5">✓</span>
                                <span><strong>Pengembangan Karakter & Pramuka:</strong> Ekstrakurikuler minat, bakat seni Islam, dan olahraga.</span>
                            </div>
                        </div>
                    </div>

                    <div class="mt-8 pt-6 border-t border-neutral-100">
                        <a
                            href="{{ route('register') }}"
                            class="inline-flex items-center justify-center w-full py-3 rounded-xl text-xs font-bold text-white bg-secondary-600 hover:bg-secondary-700 shadow-sm transition-all"
                        >
                            <span>Daftar Jalur Reguler</span>
                            <span class="ml-1.5">&rarr;</span>
                        </a>
                    </div>
                </div>
            </div>
        </div>
    </section>

    {{-- =========================================================================
         SECTION 3: ALUR PENDAFTARAN RESMI (4 Langkah Terpadu)
         ========================================================================= --}}
    <section id="alur" class="py-20 sm:py-24 bg-neutral-50/70 border-b border-neutral-200/80">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="text-center max-w-3xl mx-auto mb-16">
                <span class="text-xs font-bold uppercase tracking-wider text-primary-700 bg-primary-50 border border-primary-200 px-3 py-1 rounded-full">
                    Panduan Orang Tua / Wali
                </span>
                <h2 class="text-3xl sm:text-4xl font-extrabold text-neutral-900 tracking-tight mt-3">
                    4 Langkah Alur Pendaftaran Terpadu
                </h2>
                <p class="text-sm sm:text-base text-neutral-600 mt-3 leading-relaxed">
                    Sistem dirancang mandiri, transparan, dan dapat disimpan sementara sebagai draf sebelum dilakukan penyerahan berkas fisik ke madrasah.
                </p>
            </div>

            {{-- 4 Steps Grid --}}
            <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-6">
                {{-- Step 1 --}}
                <div class="rounded-2xl bg-white p-7 border border-neutral-200/80 shadow-xs hover:shadow-md transition-all duration-200 hover:-translate-y-1 flex flex-col justify-between">
                    <div>
                        <div class="w-12 h-12 rounded-xl bg-primary-50 text-primary-700 flex items-center justify-center font-extrabold text-lg mb-5 border border-primary-200/60">
                            1
                        </div>
                        <h4 class="text-base font-bold text-neutral-900 mb-2">Registrasi Akun NIK</h4>
                        <p class="text-xs text-neutral-600 leading-relaxed">
                            Daftarkan NIK 16 digit calon siswa, tentukan kata sandi, dan aktifkan akun melalui kode OTP WhatsApp 6 digit.
                        </p>
                    </div>
                    <div class="mt-6 pt-4 border-t border-neutral-100 text-[11px] font-semibold text-primary-700">
                        1 Akun = 1 NIK Siswa
                    </div>
                </div>

                {{-- Step 2 --}}
                <div class="rounded-2xl bg-white p-7 border border-neutral-200/80 shadow-xs hover:shadow-md transition-all duration-200 hover:-translate-y-1 flex flex-col justify-between">
                    <div>
                        <div class="w-12 h-12 rounded-xl bg-primary-50 text-primary-700 flex items-center justify-center font-extrabold text-lg mb-5 border border-primary-200/60">
                            2
                        </div>
                        <h4 class="text-base font-bold text-neutral-900 mb-2">Lengkapi Biodata</h4>
                        <p class="text-xs text-neutral-600 leading-relaxed">
                            Isi formulir online: biodata diri siswa, alamat domisili, data Ayah & Ibu kandung, serta riwayat sekolah TK/RA asal.
                        </p>
                    </div>
                    <div class="mt-6 pt-4 border-t border-neutral-100 text-[11px] font-semibold text-primary-700">
                        Pilihan Jalur Unggulan/Reguler
                    </div>
                </div>

                {{-- Step 3 --}}
                <div class="rounded-2xl bg-white p-7 border border-neutral-200/80 shadow-xs hover:shadow-md transition-all duration-200 hover:-translate-y-1 flex flex-col justify-between">
                    <div>
                        <div class="w-12 h-12 rounded-xl bg-primary-50 text-primary-700 flex items-center justify-center font-extrabold text-lg mb-5 border border-primary-200/60">
                            3
                        </div>
                        <h4 class="text-base font-bold text-neutral-900 mb-2">Unggah Dokumen</h4>
                        <p class="text-xs text-neutral-600 leading-relaxed">
                            Unggah berkas digital Kartu Keluarga, Akta Kelahiran, dan Pas Foto terbaru dalam format PDF atau Gambar (maksimal 2MB).
                        </p>
                    </div>
                    <div class="mt-6 pt-4 border-t border-neutral-100 text-[11px] font-semibold text-primary-700">
                        Penyimpanan Terenkripsi & Privat
                    </div>
                </div>

                {{-- Step 4 --}}
                <div class="rounded-2xl bg-white p-7 border border-secondary-300 bg-secondary-50/20 shadow-xs hover:shadow-md transition-all duration-200 hover:-translate-y-1 flex flex-col justify-between">
                    <div>
                        <div class="w-12 h-12 rounded-xl bg-secondary-600 text-white flex items-center justify-center font-extrabold text-lg mb-5 shadow-xs">
                            4
                        </div>
                        <h4 class="text-base font-bold text-neutral-900 mb-2">Cetak Bukti & PTSP</h4>
                        <p class="text-xs text-neutral-600 leading-relaxed">
                            Kirim formulir resmi, unduh lembar Bukti Pendaftaran PDF ber-barcode resmi, dan bawa berkas fisik ke Loket PTSP MIN 1 Kota Madiun.
                        </p>
                    </div>
                    <div class="mt-6 pt-4 border-t border-secondary-200 text-[11px] font-semibold text-secondary-800">
                        Verifikasi Fisik di Madrasah
                    </div>
                </div>
            </div>
        </div>
    </section>

    {{-- =========================================================================
         SECTION 4: LAYANAN LOKET PTSP & JADWAL RESMI
         ========================================================================= --}}
    <section id="ptsp" class="py-20 sm:py-24 bg-white">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="rounded-3xl bg-gradient-to-br from-neutral-900 via-neutral-800 to-neutral-950 text-white p-8 sm:p-12 lg:p-16 shadow-2xl relative overflow-hidden">
                <div class="absolute -right-20 -bottom-20 w-80 h-80 bg-primary-600/10 rounded-full blur-3xl pointer-events-none"></div>

                <div class="grid grid-cols-1 lg:grid-cols-12 gap-10 items-center relative z-10">
                    <div class="lg:col-span-8 space-y-4">
                        <span class="inline-flex items-center gap-1.5 text-xs font-bold uppercase tracking-wider text-accent-300">
                            Loket Pelayanan Terpadu Satu Pintu (PTSP)
                        </span>
                        <h3 class="text-2xl sm:text-3xl lg:text-4xl font-extrabold tracking-tight text-white leading-tight">
                            Verifikasi Fisik & Konsultasi Langsung di Madrasah
                        </h3>
                        <p class="text-sm sm:text-base text-neutral-300 leading-relaxed max-w-2xl">
                            Panitia PPDB MIN 1 Kota Madiun melayani penyerahan bukti cetak pendaftaran dan verifikasi keaslian dokumen persyaratan fisik di gedung madrasah sesuai jam operasional resmi.
                        </p>

                        <div class="pt-4 grid grid-cols-1 sm:grid-cols-2 gap-4 text-xs text-neutral-200">
                            <div class="p-4 rounded-xl bg-white/5 border border-white/10">
                                <span class="text-neutral-400 block mb-1">Jam Operasional Loket:</span>
                                <strong class="text-white text-sm block">Senin – Jumat: 08.00 – 14.00 WIB</strong>
                                <span class="text-[11px] text-neutral-400">(Sabtu, Minggu & Hari Libur Nasional Tutup)</span>
                            </div>

                            <div class="p-4 rounded-xl bg-white/5 border border-white/10">
                                <span class="text-neutral-400 block mb-1">Lokasi Kampus Madrasah:</span>
                                <strong class="text-white text-sm block">MIN 1 Kota Madiun</strong>
                                <span class="text-[11px] text-neutral-400">Kota Madiun, Jawa Timur</span>
                            </div>
                        </div>
                    </div>

                    <div class="lg:col-span-4 flex flex-col gap-3.5">
                        <a
                            href="{{ route('register') }}"
                            class="inline-flex items-center justify-center w-full py-4 rounded-xl font-bold text-sm text-neutral-900 bg-accent-400 hover:bg-accent-300 shadow-lg hover:shadow-xl transition-all duration-150 active:scale-[0.98]"
                        >
                            <span>Daftar Akun Siswa Sekarang</span>
                            <span class="ml-2">&rarr;</span>
                        </a>

                        <a
                            href="{{ route('login') }}"
                            class="inline-flex items-center justify-center w-full py-3.5 rounded-xl font-semibold text-xs text-white bg-white/10 hover:bg-white/20 border border-white/20 transition-all duration-150"
                        >
                            <span>Sudah Punya Akun? Masuk NIK</span>
                        </a>
                    </div>
                </div>
            </div>
        </div>
    </section>
</x-layouts.app>
