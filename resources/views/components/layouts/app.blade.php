<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}" class="scroll-smooth">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <meta name="description" content="{{ $metaDescription ?? 'Sistem Informasi Pendaftaran Peserta Didik Baru (PPDB) MIN 1 Kota Madiun Tahun Ajaran 2026/2027. Pendaftaran mandiri, transparan, dan terpercaya.' }}">

    <title>{{ isset($title) ? $title . ' — ' : '' }}{{ config('app.name') }} — MIN 1 Kota Madiun</title>

    <link rel="icon" type="image/svg+xml" href="{{ asset('favicon.svg') }}">

    @fonts
    @vite(['resources/css/app.css', 'resources/js/app.js'])
    @stack('styles')
</head>
<body class="bg-neutral-50/60 text-neutral-800 antialiased min-h-screen flex flex-col font-sans selection:bg-primary-500 selection:text-white">
    {{-- Skip to main content for accessibility --}}
    <a href="#main-content" class="sr-only focus:not-sr-only focus:absolute focus:top-2 focus:left-2 focus:z-50 btn btn-primary">
        Langsung ke konten utama
    </a>

    {{-- Public Navigation Header --}}
    <x-navigation.public-navbar />

    {{-- Global Flash Messages --}}
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 w-full mt-4">
        @if (session('success'))
            <x-ui.alert type="success" dismissible>
                {{ session('success') }}
            </x-ui.alert>
        @endif

        @if (session('error'))
            <x-ui.alert type="danger" dismissible>
                {{ session('error') }}
            </x-ui.alert>
        @endif

        @if (session('warning'))
            <x-ui.alert type="warning" dismissible>
                {{ session('warning') }}
            </x-ui.alert>
        @endif

        @if (session('info'))
            <x-ui.alert type="info" dismissible>
                {{ session('info') }}
            </x-ui.alert>
        @endif
    </div>

    {{-- Main Content --}}
    <main id="main-content" class="flex-1">
        {{ $slot ?? $__env->yieldContent('content') }}
    </main>

    {{-- Professional Academic Footer --}}
    <footer class="bg-neutral-900 text-neutral-300 mt-auto border-t-4 border-primary-600">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 pt-14 pb-10">
            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-12 gap-10">
                {{-- Col 1: Identity & Accreditation (5 cols) --}}
                <div class="lg:col-span-5 space-y-4">
                    <div class="flex items-center gap-3.5">
                        <div class="w-11 h-11 rounded-xl bg-gradient-to-br from-secondary-600 to-primary-600 text-white flex items-center justify-center font-extrabold text-base shadow-sm">
                            <span>M1</span>
                        </div>
                        <div>
                            <span class="block text-[11px] uppercase tracking-wider text-secondary-400 font-bold leading-tight">
                                Kementerian Agama RI
                            </span>
                            <span class="block text-lg font-extrabold text-white tracking-tight leading-tight">
                                MIN 1 Kota Madiun
                            </span>
                        </div>
                    </div>

                    <p class="text-neutral-400 text-xs sm:text-sm leading-relaxed max-w-md">
                        Sistem Informasi Pendaftaran Peserta Didik Baru (PPDB) Madrasah Ibtidaiyah Negeri 1 Kota Madiun. Mewujudkan generasi beriman, berakhlak mulia, cerdas, mandiri, dan berwawasan lingkungan.
                    </p>

                    <div class="flex flex-wrap items-center gap-2 pt-2">
                        <span class="inline-flex items-center gap-1.5 px-2.5 py-1 rounded-md text-[11px] font-bold bg-neutral-800 text-neutral-300 border border-neutral-700">
                            <span class="w-2 h-2 rounded-full bg-secondary-400"></span>
                            Terakreditasi A BAN-S/M
                        </span>
                        <span class="inline-flex items-center gap-1.5 px-2.5 py-1 rounded-md text-[11px] font-bold bg-neutral-800 text-neutral-300 border border-neutral-700">
                            Madrasah Adiwiyata
                        </span>
                    </div>
                </div>

                {{-- Col 2: Navigation Links (3 cols) --}}
                <div class="lg:col-span-3 space-y-3">
                    <h4 class="text-xs font-bold uppercase tracking-wider text-white">Navigasi Pendaftaran</h4>
                    <ul class="space-y-2.5 text-xs text-neutral-400">
                        <li>
                            <a href="{{ url('/') }}" class="hover:text-white transition-colors">Beranda PPDB</a>
                        </li>
                        <li>
                            <a href="{{ url('/#program') }}" class="hover:text-white transition-colors">Program Unggulan & Reguler</a>
                        </li>
                        <li>
                            <a href="{{ url('/#alur') }}" class="hover:text-white transition-colors">Alur & Syarat Pendaftaran</a>
                        </li>
                        <li>
                            <a href="{{ route('register') }}" class="hover:text-white transition-colors">Registrasi Akun Calon Siswa</a>
                        </li>
                        <li>
                            <a href="{{ route('login') }}" class="hover:text-white transition-colors">Masuk Akun (NIK & Password)</a>
                        </li>
                    </ul>
                </div>

                {{-- Col 3: PTSP Counter Info (4 cols) --}}
                <div class="lg:col-span-4 space-y-3">
                    <h4 class="text-xs font-bold uppercase tracking-wider text-white">Loket Layanan PTSP</h4>
                    <p class="text-neutral-400 text-xs leading-relaxed">
                        Loket Pelayanan Terpadu Satu Pintu (PTSP)<br>
                        Gedung Utama MIN 1 Kota Madiun<br>
                        Kota Madiun, Jawa Timur
                    </p>

                    <div class="p-3.5 rounded-xl bg-neutral-800/80 border border-neutral-700/80 text-xs space-y-1">
                        <div class="flex items-center justify-between text-neutral-300">
                            <span>Senin – Kamis</span>
                            <span class="font-bold text-white">08.00 – 14.00 WIB</span>
                        </div>
                        <div class="flex items-center justify-between text-neutral-300">
                            <span>Jumat</span>
                            <span class="font-bold text-white">08.00 – 11.00 WIB</span>
                        </div>
                        <div class="pt-1.5 border-t border-neutral-700 text-[11px] text-neutral-400">
                            Sabtu, Minggu & Hari Libur Nasional Tutup
                        </div>
                    </div>
                </div>
            </div>

            {{-- Bottom Legal Bar --}}
            <div class="border-t border-neutral-800 mt-12 pt-6 flex flex-col sm:flex-row items-center justify-between gap-4 text-xs text-neutral-400">
                <p>&copy; 2026 MIN 1 Kota Madiun. Hak Cipta Dilindungi Undang-Undang.</p>
                <div class="flex items-center gap-4 text-[11px] text-neutral-400">
                    <span>Sistem Informasi PPDB Minsatama</span>
                    <span>&bull;</span>
                    <span>Keamanan Data Terenkripsi</span>
                </div>
            </div>
        </div>
    </footer>

    @stack('scripts')
</body>
</html>
