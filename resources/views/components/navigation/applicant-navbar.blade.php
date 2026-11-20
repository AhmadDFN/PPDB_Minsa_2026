@php
    $user = auth()->user();
    $applicant = $user?->applicant;
    $maskedNik = $user ? $user->getMaskedNik() : '—';
    $isFormActive = request()->routeIs('applicant.biodata.*')
        || request()->routeIs('applicant.address.*')
        || request()->routeIs('applicant.parents.*')
        || request()->routeIs('applicant.education.*')
        || request()->routeIs('applicant.program.*');
@endphp

<header class="bg-slate-900/95 backdrop-blur-md border-b border-slate-800 text-white sticky top-0 z-50 transition-all duration-200 shadow-md">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        <div class="flex items-center justify-between h-16 sm:h-20 gap-3">
            {{-- Institutional & Portal Identity --}}
            <div class="flex items-center gap-3 sm:gap-4 min-w-0">
                <a href="{{ route('applicant.dashboard') }}" class="flex items-center gap-3 group select-none min-w-0">
                    <div class="relative w-10 h-10 rounded-xl bg-gradient-to-br from-emerald-600 to-teal-700 text-white flex items-center justify-center font-extrabold text-base shadow-sm border border-emerald-400/30 shrink-0 transition-transform duration-200 group-hover:scale-105">
                        <span class="tracking-tight">M1</span>
                        <div class="absolute -bottom-1 -right-1 w-3 h-3 rounded-full bg-emerald-400 border-2 border-slate-900 flex items-center justify-center">
                            <span class="w-1.5 h-1.5 rounded-full bg-white animate-pulse"></span>
                        </div>
                    </div>
                    <div class="flex flex-col min-w-0">
                        <div class="flex items-center gap-2">
                            <span class="text-sm sm:text-base font-extrabold text-white tracking-tight group-hover:text-emerald-300 transition-colors truncate">
                                {{ \App\Models\SystemSetting::getInstitutionName() }}
                            </span>
                            <span class="hidden sm:inline-flex items-center px-2 py-0.5 rounded-md text-[10px] font-bold tracking-wider uppercase bg-emerald-500/20 text-emerald-300 border border-emerald-400/30">
                                Portal Pendaftar
                            </span>
                        </div>
                        <span class="text-[11px] text-slate-400 font-medium truncate">
                            PPDB T.A. 2026/2027 &bull; Calon Siswa Baru
                        </span>
                    </div>
                </a>
            </div>

            {{-- Desktop Main Navigation Tabs --}}
            <nav class="hidden md:flex items-center gap-1.5 text-xs font-semibold" aria-label="Navigasi Portal Pendaftar">
                <a
                    href="{{ route('applicant.dashboard') }}"
                    class="px-3.5 py-2 rounded-xl transition-all duration-150 flex items-center gap-1.5 {{ request()->routeIs('applicant.dashboard') ? 'bg-emerald-500/15 text-emerald-300 font-bold border border-emerald-500/30 shadow-xs' : 'text-slate-300 hover:text-white hover:bg-slate-800' }}"
                >
                    <svg class="w-4 h-4 {{ request()->routeIs('applicant.dashboard') ? 'text-emerald-400' : 'text-slate-400' }}" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 12l2-2m0 0l7-7 7 7M5 10v10a1 1 0 001 1h3m10-11l2 2m-2-2v10a1 1 0 01-1 1h-3m-6 0a1 1 0 001-1v-4a1 1 0 011-1h2a1 1 0 011 1v4a1 1 0 001 1m-6 0h6" />
                    </svg>
                    <span>Dasbor</span>
                </a>

                <a
                    href="{{ route('applicant.biodata.edit') }}"
                    class="px-3.5 py-2 rounded-xl transition-all duration-150 flex items-center gap-1.5 {{ $isFormActive ? 'bg-emerald-500/15 text-emerald-300 font-bold border border-emerald-500/30 shadow-xs' : 'text-slate-300 hover:text-white hover:bg-slate-800' }}"
                >
                    <svg class="w-4 h-4 {{ $isFormActive ? 'text-emerald-400' : 'text-slate-400' }}" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" />
                    </svg>
                    <span>Formulir Pendaftaran</span>
                </a>

                <a
                    href="{{ route('applicant.documents.index') }}"
                    class="px-3.5 py-2 rounded-xl transition-all duration-150 flex items-center gap-1.5 {{ request()->routeIs('applicant.documents.*') ? 'bg-emerald-500/15 text-emerald-300 font-bold border border-emerald-500/30 shadow-xs' : 'text-slate-300 hover:text-white hover:bg-slate-800' }}"
                >
                    <svg class="w-4 h-4 {{ request()->routeIs('applicant.documents.*') ? 'text-emerald-400' : 'text-slate-400' }}" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-8l-4-4m0 0L8 8m4-4v12" />
                    </svg>
                    <span>Dokumen</span>
                </a>

                <a
                    href="{{ route('applicant.registration.review') }}"
                    class="px-3.5 py-2 rounded-xl transition-all duration-150 flex items-center gap-1.5 {{ request()->routeIs('applicant.registration.review') ? 'bg-emerald-500/15 text-emerald-300 font-bold border border-emerald-500/30 shadow-xs' : 'text-slate-300 hover:text-white hover:bg-slate-800' }}"
                >
                    <svg class="w-4 h-4 {{ request()->routeIs('applicant.registration.review') ? 'text-emerald-400' : 'text-slate-400' }}" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2m-6 9l2 2 4-4" />
                    </svg>
                    <span>Tinjau & Bukti</span>
                </a>
            </nav>

            {{-- Right Header Actions / User Context --}}
            <div class="hidden lg:flex items-center gap-3 shrink-0">
                {{-- NIK Pill --}}
                <div class="flex items-center gap-2 px-3 py-1.5 rounded-xl bg-slate-800/80 border border-slate-700/80 text-xs">
                    <span class="w-2 h-2 rounded-full bg-emerald-400"></span>
                    <span class="text-slate-400 font-mono text-[11px]">NIK:</span>
                    <span class="font-mono font-bold text-slate-200 text-xs">{{ $maskedNik }}</span>
                </div>

                {{-- Back to Landing Link --}}
                <a
                    href="{{ url('/') }}"
                    class="text-xs text-slate-400 hover:text-slate-200 transition-colors px-2 py-1 flex items-center gap-1"
                    title="Buka Halaman Depan Madrasah"
                >
                    <span>Web Utama</span>
                    <svg class="w-3 h-3 text-slate-500" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 6H6a2 2 0 00-2 2v10a2 2 0 002 2h10a2 2 0 002-2v-4M14 4h6m0 0v6m0-6L10 14" />
                    </svg>
                </a>

                {{-- Logout Button --}}
                <form method="POST" action="{{ route('logout') }}" class="inline">
                    @csrf
                    <button
                        type="submit"
                        class="inline-flex items-center gap-1.5 px-3 py-1.5 rounded-xl text-xs font-semibold text-rose-300 hover:text-rose-200 bg-rose-500/10 hover:bg-rose-500/20 border border-rose-500/20 transition cursor-pointer"
                        title="Keluar dari Portal"
                    >
                        <svg class="w-3.5 h-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 16l4-4m0 0l-4-4m4 4H7m6 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h7a3 3 0 013 3v1" />
                        </svg>
                        <span>Keluar</span>
                    </button>
                </form>
            </div>

            {{-- Mobile Menu Hamburger --}}
            <div class="flex md:hidden items-center">
                <button
                    type="button"
                    id="applicant-mobile-menu-button"
                    class="p-2 rounded-xl text-slate-300 hover:text-white bg-slate-800 border border-slate-700 focus:outline-none focus:ring-2 focus:ring-emerald-500 transition cursor-pointer"
                    aria-label="Buka menu navigasi pendaftar"
                    aria-expanded="false"
                >
                    <svg id="applicant-menu-open-icon" class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16M4 18h16" />
                    </svg>
                    <svg id="applicant-menu-close-icon" class="w-5 h-5 hidden" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                    </svg>
                </button>
            </div>
        </div>
    </div>

    {{-- Mobile Menu Dropdown Panel --}}
    <div
        id="applicant-mobile-menu-panel"
        class="hidden md:hidden border-t border-slate-800 bg-slate-900/98 px-4 pt-3 pb-5 space-y-2 shadow-2xl"
    >
        {{-- Student Quick Card on Mobile --}}
        <div class="p-3 rounded-xl bg-slate-800/80 border border-slate-700 mb-3 flex items-center justify-between">
            <div>
                <span class="text-[10px] uppercase tracking-wider text-slate-400 font-bold block">Calon Siswa</span>
                <span class="text-xs font-bold text-white truncate max-w-[180px] block">{{ $applicant->full_name ?? $user->name }}</span>
            </div>
            <div class="text-right">
                <span class="text-[10px] text-slate-400 block font-mono">NIK:</span>
                <span class="text-xs font-mono font-bold text-emerald-400">{{ $maskedNik }}</span>
            </div>
        </div>

        <a
            href="{{ route('applicant.dashboard') }}"
            class="block px-3.5 py-2.5 rounded-xl text-sm font-semibold transition {{ request()->routeIs('applicant.dashboard') ? 'bg-emerald-500/20 text-emerald-300 font-bold' : 'text-slate-300 hover:bg-slate-800' }}"
        >
            Dasbor Pendaftar
        </a>
        <a
            href="{{ route('applicant.biodata.edit') }}"
            class="block px-3.5 py-2.5 rounded-xl text-sm font-semibold transition {{ $isFormActive ? 'bg-emerald-500/20 text-emerald-300 font-bold' : 'text-slate-300 hover:bg-slate-800' }}"
        >
            Formulir Pendaftaran
        </a>
        <a
            href="{{ route('applicant.documents.index') }}"
            class="block px-3.5 py-2.5 rounded-xl text-sm font-semibold transition {{ request()->routeIs('applicant.documents.*') ? 'bg-emerald-500/20 text-emerald-300 font-bold' : 'text-slate-300 hover:bg-slate-800' }}"
        >
            Dokumen Persyaratan
        </a>
        <a
            href="{{ route('applicant.registration.review') }}"
            class="block px-3.5 py-2.5 rounded-xl text-sm font-semibold transition {{ request()->routeIs('applicant.registration.review') ? 'bg-emerald-500/20 text-emerald-300 font-bold' : 'text-slate-300 hover:bg-slate-800' }}"
        >
            Tinjau & Bukti Pendaftaran
        </a>

        <div class="pt-3 border-t border-slate-800 flex flex-col gap-2">
            <a href="{{ url('/') }}" class="text-xs text-center text-slate-400 hover:text-white py-1">
                &larr; Kunjungi Web Utama Madrasah
            </a>
            <form method="POST" action="{{ route('logout') }}">
                @csrf
                <button type="submit" class="w-full py-2.5 rounded-xl text-xs font-bold text-rose-300 bg-rose-500/10 hover:bg-rose-500/20 border border-rose-500/20 transition">
                    Keluar dari Akun
                </button>
            </form>
        </div>
    </div>
</header>

<script>
    document.addEventListener('DOMContentLoaded', function () {
        const toggleBtn = document.getElementById('applicant-mobile-menu-button');
        const menuPanel = document.getElementById('applicant-mobile-menu-panel');
        const openIcon = document.getElementById('applicant-menu-open-icon');
        const closeIcon = document.getElementById('applicant-menu-close-icon');

        if (toggleBtn && menuPanel) {
            toggleBtn.addEventListener('click', function () {
                const isOpen = !menuPanel.classList.contains('hidden');
                if (isOpen) {
                    menuPanel.classList.add('hidden');
                    openIcon.classList.remove('hidden');
                    closeIcon.classList.add('hidden');
                    toggleBtn.setAttribute('aria-expanded', 'false');
                } else {
                    menuPanel.classList.remove('hidden');
                    openIcon.classList.add('hidden');
                    closeIcon.classList.remove('hidden');
                    toggleBtn.setAttribute('aria-expanded', 'true');
                }
            });
        }
    });
</script>
