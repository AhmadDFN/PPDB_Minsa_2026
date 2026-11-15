{{-- Mobile Sidebar Backdrop --}}
<div id="admin-sidebar-backdrop" class="fixed inset-0 bg-gray-900/40 z-30 hidden lg:hidden transition-opacity"></div>

{{-- Sidebar Container --}}
<aside
    id="admin-sidebar"
    class="fixed inset-y-0 left-0 z-40 w-64 bg-white border-r border-gray-200 transform -translate-x-full lg:translate-x-0 lg:static lg:inset-0 transition-transform duration-200 ease-in-out flex flex-col shadow-sm"
>
    {{-- Sidebar Header --}}
    <div class="h-16 px-6 flex items-center justify-between border-b border-gray-100 bg-white">
        <div class="flex items-center gap-3">
            <div class="w-9 h-9 rounded-lg bg-primary-600 text-white flex items-center justify-center font-bold text-sm shadow-xs">
                <span>M1</span>
            </div>
            <div class="flex flex-col">
                <span class="text-xs font-bold tracking-tight text-gray-900 uppercase">Panel Admin</span>
                <span class="text-[11px] text-gray-500 font-medium">PPDB Minsatama</span>
            </div>
        </div>

        {{-- Mobile Close Button --}}
        <button
            type="button"
            id="admin-sidebar-close"
            class="p-1.5 rounded-md text-gray-400 hover:text-gray-600 hover:bg-gray-100 lg:hidden focus:outline-none"
            aria-label="Tutup sidebar"
        >
            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
            </svg>
        </button>
    </div>

    {{-- Navigation Menu --}}
    <nav class="flex-1 px-4 py-6 space-y-1 overflow-y-auto">
        <div class="px-3 pb-2 text-[11px] font-bold text-gray-400 uppercase tracking-wider">
            Menu Utama
        </div>

        {{-- Dashboard --}}
        <a
            href="{{ url('/admin/dashboard') }}"
            class="flex items-center gap-3 px-3 py-2.5 rounded-lg text-sm font-medium transition-colors {{ request()->is('admin/dashboard') || request()->is('admin') ? 'bg-primary-50 text-primary-700 font-semibold' : 'text-gray-700 hover:bg-gray-50 hover:text-gray-900' }}"
        >
            <svg class="w-5 h-5 {{ request()->is('admin/dashboard') || request()->is('admin') ? 'text-primary-600' : 'text-gray-400' }}" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 12l2-2m0 0l7-7 7 7M5 10v10a1 1 0 001 1h3m10-11l2 2m-2-2v10a1 1 0 01-1 1h-3m-6 0a1 1 0 001-1v-4a1 1 0 011-1h2a1 1 0 011 1v4a1 1 0 001 1m-6 0h6" />
            </svg>
            <span>Dashboard</span>
        </a>

        {{-- Pendaftar PPDB --}}
        <a
            href="{{ url('/admin/pendaftar') }}"
            class="flex items-center gap-3 px-3 py-2.5 rounded-lg text-sm font-medium transition-colors {{ request()->is('admin/pendaftar*') ? 'bg-primary-50 text-primary-700 font-semibold' : 'text-gray-700 hover:bg-gray-50 hover:text-gray-900' }}"
        >
            <svg class="w-5 h-5 {{ request()->is('admin/pendaftar*') ? 'text-primary-600' : 'text-gray-400' }}" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4.354a4 4 0 110 5.292M15 21H3v-1a6 6 0 0112 0v1zm0 0h6v-1a6 6 0 00-9-5.197M13 7a4 4 0 11-8 0 4 4 0 018 0z" />
            </svg>
            <span>Data Pendaftar</span>
        </a>

        {{-- Dokumen Persyaratan --}}
        <a
            href="{{ url('/admin/dokumen') }}"
            class="flex items-center gap-3 px-3 py-2.5 rounded-lg text-sm font-medium transition-colors {{ request()->is('admin/dokumen*') ? 'bg-primary-50 text-primary-700 font-semibold' : 'text-gray-700 hover:bg-gray-50 hover:text-gray-900' }}"
        >
            <svg class="w-5 h-5 {{ request()->is('admin/dokumen*') ? 'text-primary-600' : 'text-gray-400' }}" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" />
            </svg>
            <span>Verifikasi Berkas</span>
        </a>

        <div class="pt-5 px-3 pb-2 text-[11px] font-bold text-gray-400 uppercase tracking-wider">
            Konfigurasi PPDB
        </div>

        {{-- Periode Penerimaan --}}
        <a
            href="{{ url('/admin/periode') }}"
            class="flex items-center gap-3 px-3 py-2.5 rounded-lg text-sm font-medium transition-colors {{ request()->is('admin/periode*') ? 'bg-primary-50 text-primary-700 font-semibold' : 'text-gray-700 hover:bg-gray-50 hover:text-gray-900' }}"
        >
            <svg class="w-5 h-5 {{ request()->is('admin/periode*') ? 'text-primary-600' : 'text-gray-400' }}" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z" />
            </svg>
            <span>Periode Pendaftaran</span>
        </a>

        {{-- Program Pendidikan --}}
        <a
            href="{{ url('/admin/program') }}"
            class="flex items-center gap-3 px-3 py-2.5 rounded-lg text-sm font-medium transition-colors {{ request()->is('admin/program*') ? 'bg-primary-50 text-primary-700 font-semibold' : 'text-gray-700 hover:bg-gray-50 hover:text-gray-900' }}"
        >
            <svg class="w-5 h-5 {{ request()->is('admin/program*') ? 'text-primary-600' : 'text-gray-400' }}" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 11H5m14 0a2 2 0 012 2v6a2 2 0 01-2 2H5a2 2 0 01-2-2v-6a2 2 0 012-2m14 0V9a2 2 0 00-2-2M5 11V9a2 2 0 012-2m0 0V5a2 2 0 012-2h6a2 2 0 012 2v2M7 7h10" />
            </svg>
            <span>Program MI</span>
        </a>

        <div class="pt-5 px-3 pb-2 text-[11px] font-bold text-gray-400 uppercase tracking-wider">
            Laporan & Sistem
        </div>

        {{-- Laporan Rekapitulasi --}}
        <a
            href="{{ url('/admin/laporan') }}"
            class="flex items-center gap-3 px-3 py-2.5 rounded-lg text-sm font-medium transition-colors {{ request()->is('admin/laporan*') ? 'bg-primary-50 text-primary-700 font-semibold' : 'text-gray-700 hover:bg-gray-50 hover:text-gray-900' }}"
        >
            <svg class="w-5 h-5 {{ request()->is('admin/laporan*') ? 'text-primary-600' : 'text-gray-400' }}" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 17v-2m3 2v-4m3 4v-6m2 10H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" />
            </svg>
            <span>Laporan & Ekspor</span>
        </a>

        {{-- Pengguna & Hak Akses --}}
        <a
            href="{{ url('/admin/pengguna') }}"
            class="flex items-center gap-3 px-3 py-2.5 rounded-lg text-sm font-medium transition-colors {{ request()->is('admin/pengguna*') ? 'bg-primary-50 text-primary-700 font-semibold' : 'text-gray-700 hover:bg-gray-50 hover:text-gray-900' }}"
        >
            <svg class="w-5 h-5 {{ request()->is('admin/pengguna*') ? 'text-primary-600' : 'text-gray-400' }}" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10.325 4.317c.426-1.756 2.924-1.756 3.35 0a1.724 1.724 0 002.573 1.066c1.543-.94 3.31.826 2.37 2.37a1.724 1.724 0 001.065 2.572c1.756.426 1.756 2.924 0 3.35a1.724 1.724 0 00-1.066 2.573c.94 1.543-.826 3.31-2.37 2.37a1.724 1.724 0 00-2.572 1.065c-.426 1.756-2.924 1.756-3.35 0a1.724 1.724 0 00-2.573-1.066c-1.543.94-3.31-.826-2.37-2.37a1.724 1.724 0 00-1.065-2.572c-1.756-.426-1.756-2.924 0-3.35a1.724 1.724 0 001.066-2.573c-.94-1.543.826-3.31 2.37-2.37.996.608 2.296.07 2.572-1.065z" />
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z" />
            </svg>
            <span>Manajemen Pengguna</span>
        </a>
    </nav>

    {{-- Sidebar Footer: Admin User Info & Logout --}}
    <div class="p-4 border-t border-gray-100 bg-gray-50/70">
        <div class="flex items-center justify-between gap-3">
            <div class="flex items-center gap-3 min-w-0">
                <div class="w-9 h-9 rounded-full bg-secondary-600 text-white flex items-center justify-center font-bold text-xs uppercase shrink-0">
                    {{ substr(auth()->user()?->name ?? 'AD', 0, 2) }}
                </div>
                <div class="flex flex-col min-w-0">
                    <span class="text-xs font-semibold text-gray-900 truncate">{{ auth()->user()?->name ?? 'Administrator PPDB' }}</span>
                    <span class="text-[11px] text-gray-500 truncate">{{ auth()->user()?->email ?? 'admin@minsatama.sch.id' }}</span>
                </div>
            </div>
            <form method="POST" action="{{ route('admin.logout') }}" class="shrink-0">
                @csrf
                <button type="submit" class="p-1.5 text-gray-400 hover:text-red-600 rounded-lg hover:bg-gray-100 transition" title="Keluar">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 16l4-4m0 0l-4-4m4 4H7m6 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h7a3 3 0 013 3v1" />
                    </svg>
                </button>
            </form>
        </div>
    </div>
</aside>

