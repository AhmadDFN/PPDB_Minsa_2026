<header class="bg-white/95 backdrop-blur-md border-b border-neutral-200/90 sticky top-0 z-50 transition-all duration-200">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        <div class="flex items-center justify-between h-20 gap-3">
            {{-- Institutional Identity / Logo Mark --}}
            <a href="{{ url('/') }}" class="flex items-center gap-3 group select-none min-w-0">
                <div class="relative w-10 h-10 sm:w-11 sm:h-11 shrink-0 transition-transform duration-200 group-hover:scale-[1.04] flex items-center justify-center">
                    <img src="{{ asset('images/logo-minsatama.png') }}" alt="Logo MIN 1 Kota Madiun" class="w-full h-full object-contain drop-shadow-xs" loading="eager">
                </div>
                <div class="flex flex-col min-w-0">
                    <span class="text-[10px] sm:text-[11px] uppercase tracking-wider font-bold text-secondary-700 leading-tight truncate">
                        Kementerian Agama RI
                    </span>
                    <span class="text-sm sm:text-base md:text-lg font-extrabold text-neutral-900 tracking-tight group-hover:text-primary-700 transition-colors truncate">
                        MIN 1 Kota Madiun
                    </span>
                    <span class="text-[10px] sm:text-[11px] text-neutral-500 font-medium hidden sm:block leading-tight truncate">
                        Sistem Informasi Pendaftaran Peserta Didik Baru (PPDB)
                    </span>
                </div>
            </a>

            {{-- Desktop Navigation Links (>= md / 768px) --}}
            <nav class="hidden md:flex items-center gap-6 lg:gap-8 text-sm font-semibold text-neutral-700 shrink-0" aria-label="Navigasi Utama">
                <a href="{{ url('/') }}" class="transition-colors hover:text-primary-600 {{ request()->is('/') ? 'text-primary-700 font-bold' : '' }}">
                    Beranda
                </a>
                <a href="{{ url('/#program') }}" class="transition-colors hover:text-primary-600">
                    Program Pendidikan
                </a>
                <a href="{{ url('/#alur') }}" class="transition-colors hover:text-primary-600">
                    Alur Pendaftaran
                </a>
                <a href="{{ url('/#ptsp') }}" class="transition-colors hover:text-primary-600">
                    Jadwal & Loket PTSP
                </a>
            </nav>

            {{-- Right CTA / Auth Buttons (>= md / 768px) --}}
            <div class="hidden md:flex items-center gap-3 shrink-0">
                @auth
                    @if (auth()->user()->isAdmin())
                        <a
                            href="{{ route('admin.dashboard') }}"
                            class="inline-flex items-center gap-2 px-3.5 py-2 rounded-xl text-xs font-bold uppercase tracking-wider bg-primary-50 text-primary-700 hover:bg-primary-100 transition border border-primary-200 shadow-xs"
                        >
                            <svg class="w-4 h-4 text-primary-600" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10.325 4.317c.426-1.756 2.924-1.756 3.35 0a1.724 1.724 0 002.573 1.066c1.543-.94 3.31.826 2.37 2.37a1.724 1.724 0 001.065 2.572c1.756.426 1.756 2.924 0 3.35a1.724 1.724 0 00-1.066 2.573c.94 1.543-.826 3.31-2.37 2.37a1.724 1.724 0 00-2.572 1.065c-.426 1.756-2.924 1.756-3.35 0a1.724 1.724 0 00-2.573-1.066c-1.543.94-3.31-.826-2.37-2.37a1.724 1.724 0 00-1.065-2.572c-1.756-.426-1.756-2.924 0-3.35a1.724 1.724 0 001.066-2.573c-.94-1.543.826-3.31 2.37-2.37.996.608 2.296.07 2.572-1.065z" />
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z" />
                            </svg>
                            <span>Panel Admin</span>
                        </a>
                    @else
                        <a
                            href="{{ route('applicant.dashboard') }}"
                            class="inline-flex items-center gap-2 px-4 py-2 rounded-xl text-xs font-bold uppercase tracking-wider bg-emerald-600 hover:bg-emerald-700 text-white transition shadow-sm"
                        >
                            <span class="w-2 h-2 rounded-full bg-emerald-300 animate-pulse"></span>
                            <span>Buka Portal Pendaftar</span>
                            <svg class="w-3.5 h-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M14 5l7 7m0 0l-7 7m7-7H3" />
                            </svg>
                        </a>
                    @endif
                    <form method="POST" action="{{ auth()->user()->isAdmin() ? route('admin.logout') : route('logout') }}" class="inline">
                        @csrf
                        <button
                            type="submit"
                            class="px-2.5 py-2 text-xs font-semibold text-neutral-500 hover:text-red-600 transition cursor-pointer"
                            title="Keluar dari sistem"
                        >
                            Keluar
                        </button>
                    </form>
                @else
                    <a
                        href="{{ route('login') }}"
                        class="inline-flex items-center gap-1.5 px-3 py-2 rounded-xl text-xs font-bold text-neutral-700 hover:text-primary-700 hover:bg-neutral-100/80 transition"
                    >
                        <svg class="w-4 h-4 text-neutral-500" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 16l-4-4m0 0l4-4m-4 4h14m-5 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h7a3 3 0 013 3v1" />
                        </svg>
                        <span>Masuk NIK</span>
                    </a>

                    <a
                        href="{{ route('register') }}"
                        class="inline-flex items-center gap-1.5 px-4 py-2.5 rounded-xl text-xs font-bold text-white bg-primary-600 hover:bg-primary-700 shadow-md hover:shadow-lg transition-all duration-150 active:scale-[0.98]"
                    >
                        <span>Daftar Akun Baru</span>
                        <svg class="w-3.5 h-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M14 5l7 7m0 0l-7 7m7-7H3" />
                        </svg>
                    </a>
                @endauth
            </div>

            {{-- Mobile Menu Hamburger Button (< md / 768px) --}}
            <div class="flex md:hidden items-center shrink-0">
                <button
                    type="button"
                    id="mobile-menu-button"
                    class="p-2.5 rounded-xl text-neutral-800 bg-neutral-100 hover:bg-neutral-200 border border-neutral-300 focus:outline-none focus:ring-2 focus:ring-primary-500 transition cursor-pointer select-none"
                    aria-label="Buka menu navigasi"
                    aria-expanded="false"
                    aria-controls="mobile-menu-panel"
                >
                    {{-- Icon when menu is closed: hamburger 3 bars --}}
                    <svg id="menu-open-icon" class="w-6 h-6 stroke-neutral-800" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.2" d="M4 6h16M4 12h16M4 18h16" />
                    </svg>
                    {{-- Icon when menu is open: X cross --}}
                    <svg id="menu-close-icon" class="w-6 h-6 stroke-neutral-800 hidden" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.2" d="M6 18L18 6M6 6l12 12" />
                    </svg>
                </button>
            </div>
        </div>
    </div>

    {{-- Mobile Menu Dropdown Panel (< md / 768px) --}}
    <div
        id="mobile-menu-panel"
        class="hidden md:hidden border-t border-neutral-200/90 bg-white/98 backdrop-blur-md px-4 pt-3 pb-6 space-y-2 shadow-xl"
    >
        <a href="{{ url('/') }}" class="block px-3.5 py-2.5 rounded-xl text-sm font-semibold text-neutral-800 hover:bg-primary-50 hover:text-primary-700 transition">
            Beranda PPDB
        </a>
        <a href="{{ url('/#program') }}" class="block px-3.5 py-2.5 rounded-xl text-sm font-semibold text-neutral-800 hover:bg-primary-50 hover:text-primary-700 transition">
            Program Pendidikan (Unggulan & Reguler)
        </a>
        <a href="{{ url('/#alur') }}" class="block px-3.5 py-2.5 rounded-xl text-sm font-semibold text-neutral-800 hover:bg-primary-50 hover:text-primary-700 transition">
            Alur & Syarat Pendaftaran
        </a>
        <a href="{{ url('/#ptsp') }}" class="block px-3.5 py-2.5 rounded-xl text-sm font-semibold text-neutral-800 hover:bg-primary-50 hover:text-primary-700 transition">
            Jadwal & Loket PTSP
        </a>

        <div class="pt-4 border-t border-neutral-100 flex flex-col gap-2.5">
            @auth
                @if (auth()->user()->isAdmin())
                    <a href="{{ route('admin.dashboard') }}" class="btn btn-primary w-full justify-center">
                        Buka Panel Admin
                    </a>
                @else
                    <a href="{{ route('applicant.dashboard') }}" class="btn btn-primary w-full justify-center bg-emerald-600 hover:bg-emerald-700">
                        Buka Portal Pendaftar
                    </a>
                @endif
                <form method="POST" action="{{ auth()->user()->isAdmin() ? route('admin.logout') : route('logout') }}">
                    @csrf
                    <button type="submit" class="btn btn-outline w-full justify-center text-red-600 border-red-200 hover:bg-red-50">
                        Keluar
                    </button>
                </form>
            @else
                <a href="{{ route('login') }}" class="btn btn-outline w-full justify-center font-semibold">
                    Masuk Akun (NIK & Password)
                </a>
                <a href="{{ route('register') }}" class="btn btn-primary w-full justify-center font-semibold shadow-md">
                    Daftar Akun Calon Siswa
                </a>
            @endauth
        </div>
    </div>
</header>
