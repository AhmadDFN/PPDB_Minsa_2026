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
                <span class="text-xs font-bold tracking-tight text-gray-900 uppercase">Panel Admin PPDB</span>
                <span class="text-[11px] text-gray-500 font-medium">Operasional Pendaftaran</span>
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
            href="{{ route('admin.dashboard') }}"
            class="flex items-center gap-3 px-3 py-2.5 rounded-lg text-sm font-medium transition-colors {{ request()->routeIs('admin.dashboard') || request()->is('admin') ? 'bg-primary-50 text-primary-700 font-semibold' : 'text-gray-700 hover:bg-gray-50 hover:text-gray-900' }}"
        >
            <svg class="w-5 h-5 {{ request()->routeIs('admin.dashboard') || request()->is('admin') ? 'text-primary-600' : 'text-gray-400' }}" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 12l2-2m0 0l7-7 7 7M5 10v10a1 1 0 001 1h3m10-11l2 2m-2-2v10a1 1 0 01-1 1h-3m-6 0a1 1 0 001-1v-4a1 1 0 011-1h2a1 1 0 011 1v4a1 1 0 001 1m-6 0h6" />
            </svg>
            <span>Dashboard</span>
        </a>

        {{-- Data Pendaftar --}}
        @can('view_applicants')
            <a
                href="{{ route('admin.applicants.index') }}"
                class="flex items-center gap-3 px-3 py-2.5 rounded-lg text-sm font-medium transition-colors {{ request()->routeIs('admin.applicants*') ? 'bg-primary-50 text-primary-700 font-semibold' : 'text-gray-700 hover:bg-gray-50 hover:text-gray-900' }}"
            >
                <svg class="w-5 h-5 {{ request()->routeIs('admin.applicants*') ? 'text-primary-600' : 'text-gray-400' }}" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4.354a4 4 0 110 5.292M15 21H3v-1a6 6 0 0112 0v1zm0 0h6v-1a6 6 0 00-9-5.197M13 7a4 4 0 11-8 0 4 4 0 018 0z" />
                </svg>
                <span>Data Pendaftar</span>
            </a>
        @else
            <div
                class="flex items-center justify-between px-3 py-2.5 rounded-lg text-sm font-medium text-gray-400 cursor-not-allowed select-none"
                title="Modul Data Pendaftar dibatasi untuk akun Anda"
            >
                <div class="flex items-center gap-3">
                    <svg class="w-5 h-5 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4.354a4 4 0 110 5.292M15 21H3v-1a6 6 0 0112 0v1zm0 0h6v-1a6 6 0 00-9-5.197M13 7a4 4 0 11-8 0 4 4 0 018 0z" />
                    </svg>
                    <span>Data Pendaftar</span>
                </div>
                <span class="text-[10px] font-semibold text-gray-400 bg-gray-100 px-1.5 py-0.5 rounded">Terkunci</span>
            </div>
        @endcan

        @can('export_reports')
            <a href="{{ route('admin.reports.index') }}"
                class="flex items-center gap-3 px-3 py-2.5 rounded-lg text-sm font-medium transition-colors {{ request()->routeIs('admin.reports.*') ? 'bg-primary-50 text-primary-700 font-semibold' : 'text-gray-700 hover:bg-gray-50 hover:text-gray-900' }}">
                <span class="w-5 text-center">▤</span><span>Laporan & Ekspor</span>
            </a>
        @endcan

        @can('reopen_registration')
            <a
                href="{{ route('admin.registrations.index') }}"
                class="flex items-center gap-3 px-3 py-2.5 rounded-lg text-sm font-medium transition-colors {{ request()->routeIs('admin.registrations*') ? 'bg-primary-50 text-primary-700 font-semibold' : 'text-gray-700 hover:bg-gray-50 hover:text-gray-900' }}"
            >
                <svg class="w-5 h-5 {{ request()->routeIs('admin.registrations*') ? 'text-primary-600' : 'text-gray-400' }}" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v6h6M20 20v-6h-6M5 9a7 7 0 0112-3l3 3M19 15a7 7 0 01-12 3l-3-3" />
                </svg>
                <span>Buka Ulang Pendaftaran</span>
            </a>
        @endcan

        @can('verify_documents')
        <a href="{{ route('admin.document-verification.index') }}"
            class="flex items-center gap-3 px-3 py-2.5 rounded-lg text-sm font-medium transition-colors {{ request()->routeIs('admin.document-verification.*') ? 'bg-primary-50 text-primary-700 font-semibold' : 'text-gray-700 hover:bg-gray-50 hover:text-gray-900' }}">
            <span class="w-5 text-center">✓</span><span>Verifikasi Berkas</span>
        </a>
        @else
        <div
            class="flex items-center justify-between px-3 py-2.5 rounded-lg text-sm font-medium text-gray-400 cursor-not-allowed select-none"
            title="Modul Verifikasi Berkas dijadwalkan pada fase berikutnya"
        >
            <div class="flex items-center gap-3">
                <svg class="w-5 h-5 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" />
                </svg>
                <span>Verifikasi Berkas</span>
            </div>
            <span class="text-[10px] font-semibold text-gray-400 bg-gray-100 px-1.5 py-0.5 rounded">Segera</span>
        </div>
        @endcan

        <div class="pt-5 px-3 pb-2 text-[11px] font-bold text-gray-400 uppercase tracking-wider">
            Konfigurasi PPDB
        </div>

        @can('manage_admission_periods')
        <a href="{{ route('admin.admission-periods.index') }}"
            class="flex items-center gap-3 px-3 py-2.5 rounded-lg text-sm font-medium transition-colors {{ request()->routeIs('admin.admission-periods.*') ? 'bg-primary-50 text-primary-700 font-semibold' : 'text-gray-700 hover:bg-gray-50 hover:text-gray-900' }}">
            <span class="w-5 text-center">◷</span><span>Periode Pendaftaran</span>
        </a>
        @endcan

        @can('manage_programs')
        <a href="{{ route('admin.programs.index') }}"
            class="flex items-center gap-3 px-3 py-2.5 rounded-lg text-sm font-medium transition-colors {{ request()->routeIs('admin.programs.*') ? 'bg-primary-50 text-primary-700 font-semibold' : 'text-gray-700 hover:bg-gray-50 hover:text-gray-900' }}">
            <span class="w-5 text-center">▦</span><span>Program Pendaftaran</span>
        </a>
        @endcan

        @can('manage_document_requirements')
        <a href="{{ route('admin.document-requirements.index') }}"
            class="flex items-center gap-3 px-3 py-2.5 rounded-lg text-sm font-medium transition-colors {{ request()->routeIs('admin.document-requirements.*') ? 'bg-primary-50 text-primary-700 font-semibold' : 'text-gray-700 hover:bg-gray-50 hover:text-gray-900' }}">
            <span class="w-5 text-center">▤</span><span>Persyaratan Dokumen</span>
        </a>
        @endcan

        @can('interview.manage_master')
        <div class="pt-5 px-3 pb-2 text-[11px] font-bold text-gray-400 uppercase tracking-wider">
            Modul Wawancara
        </div>
        <a href="{{ route('admin.interview-batches.index') }}"
            class="flex items-center gap-3 px-3 py-2.5 rounded-lg text-sm font-medium transition-colors {{ request()->routeIs('admin.interview-batches.*') ? 'bg-primary-50 text-primary-700 font-semibold' : 'text-gray-700 hover:bg-gray-50 hover:text-gray-900' }}">
            <svg class="w-5 h-5 {{ request()->routeIs('admin.interview-batches.*') ? 'text-primary-600' : 'text-gray-400' }}" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z" />
            </svg>
            <span>Jadwal Wawancara</span>
        </a>
        <a href="{{ route('admin.interview-stages.index') }}"
            class="flex items-center gap-3 px-3 py-2.5 rounded-lg text-sm font-medium transition-colors {{ request()->routeIs('admin.interview-stages.*') ? 'bg-primary-50 text-primary-700 font-semibold' : 'text-gray-700 hover:bg-gray-50 hover:text-gray-900' }}">
            <svg class="w-5 h-5 {{ request()->routeIs('admin.interview-stages.*') ? 'text-primary-600' : 'text-gray-400' }}" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2m-6 9l2 2 4-4" />
            </svg>
            <span>Tahap & Poin Wawancara</span>
        </a>
        @endcan

        {{-- Legacy placeholders retained only when permission is absent --}}
        @cannot('manage_admission_periods')
        <div
            class="flex items-center justify-between px-3 py-2.5 rounded-lg text-sm font-medium text-gray-400 cursor-not-allowed select-none"
            title="Modul Konfigurasi Periode Pendaftaran dijadwalkan pada fase berikutnya"
        >
            <div class="flex items-center gap-3">
                <svg class="w-5 h-5 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z" />
                </svg>
                <span>Periode Pendaftaran</span>
            </div>
            <span class="text-[10px] font-semibold text-gray-400 bg-gray-100 px-1.5 py-0.5 rounded">Segera</span>
        </div>
        @endcannot

        @cannot('manage_programs')
        {{-- Program Pendidikan (Upcoming) --}}
        <div
            class="flex items-center justify-between px-3 py-2.5 rounded-lg text-sm font-medium text-gray-400 cursor-not-allowed select-none"
            title="Modul Program MI dijadwalkan pada fase berikutnya"
        >
            <div class="flex items-center gap-3">
                <svg class="w-5 h-5 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 11H5m14 0a2 2 0 012 2v6a2 2 0 01-2 2H5a2 2 0 01-2-2v-6a2 2 0 012-2m14 0V9a2 2 0 00-2-2M5 11V9a2 2 0 012-2m0 0V5a2 2 0 012-2h6a2 2 0 012 2v2M7 7h10" />
                </svg>
                <span>Program MI</span>
            </div>
            <span class="text-[10px] font-semibold text-gray-400 bg-gray-100 px-1.5 py-0.5 rounded">Segera</span>
        </div>
        @endcannot

        <div class="pt-5 px-3 pb-2 text-[11px] font-bold text-gray-400 uppercase tracking-wider">
            Laporan & Sistem
        </div>

        @can('manage_admin_users')
            <a href="{{ route('admin.admin-users.index') }}" class="flex items-center gap-3 px-3 py-2.5 rounded-lg text-sm font-medium {{ request()->routeIs('admin.admin-users.*') ? 'bg-primary-50 text-primary-700 font-semibold' : 'text-gray-700 hover:bg-gray-50' }}"><span class="w-5 text-center">♙</span><span>Akun Administrator</span></a>
            <a href="{{ route('admin.roles.index') }}" class="flex items-center gap-3 px-3 py-2.5 rounded-lg text-sm font-medium {{ request()->routeIs('admin.roles.*') ? 'bg-primary-50 text-primary-700 font-semibold' : 'text-gray-700 hover:bg-gray-50' }}"><span class="w-5 text-center">⚿</span><span>Roles & Permissions</span></a>
        @endcan

        @can('manage_system_settings')
            {{-- Pengaturan Sistem (Superadmin) --}}
            <a
                href="{{ route('admin.settings.index') }}"
                class="flex items-center gap-3 px-3 py-2.5 rounded-lg text-sm font-medium transition-colors {{ request()->routeIs('admin.settings.*') ? 'bg-primary-50 text-primary-700 font-semibold' : 'text-gray-700 hover:bg-gray-50 hover:text-gray-900' }}"
            >
                <svg class="w-5 h-5 {{ request()->routeIs('admin.settings.*') ? 'text-primary-600' : 'text-gray-400' }}" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6V4m0 2a2 2 0 100 4m0-4a2 2 0 110 4m-6 8a2 2 0 100-4m0 4a2 2 0 110-4m0 4v2m0-6V4m6 6v10m6-2a2 2 0 100-4m0 4a2 2 0 110-4m0 4v2m0-6V4" />
                </svg>
                <span>Pengaturan Sistem</span>
            </a>
        @endcan

        @can('manage_communication_settings')
            <a
                href="{{ route('admin.communication.index') }}"
                class="flex items-center gap-3 px-3 py-2.5 rounded-lg text-sm font-medium transition-colors {{ request()->routeIs('admin.communication.*') ? 'bg-primary-50 text-primary-700 font-semibold' : 'text-gray-700 hover:bg-gray-50 hover:text-gray-900' }}"
            >
                <svg class="w-5 h-5 {{ request()->routeIs('admin.communication.*') ? 'text-primary-600' : 'text-gray-400' }}" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 10h.01M12 10h.01M16 10h.01M9 16h6m-8 4h10a3 3 0 003-3V7a3 3 0 00-3-3H7a3 3 0 00-3 3v10a3 3 0 003 3z" />
                </svg>
                <span>Communication Center</span>
            </a>
        @endcan

        @can('view_activity_logs')
            <a href="{{ route('admin.audit-logs.index') }}"
                class="flex items-center gap-3 px-3 py-2.5 rounded-lg text-sm font-medium transition-colors {{ request()->routeIs('admin.audit-logs.*') ? 'bg-primary-50 text-primary-700 font-semibold' : 'text-gray-700 hover:bg-gray-50 hover:text-gray-900' }}">
                <span class="w-5 text-center">◌</span><span>Audit Aktivitas</span>
            </a>
        @endcan

        @cannot('export_reports')
        {{-- Laporan Rekapitulasi (Upcoming) --}}
        <div
            class="flex items-center justify-between px-3 py-2.5 rounded-lg text-sm font-medium text-gray-400 cursor-not-allowed select-none"
            title="Modul Laporan & Ekspor dijadwalkan pada fase berikutnya"
        >
            <div class="flex items-center gap-3">
                <svg class="w-5 h-5 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 17v-2m3 2v-4m3 4v-6m2 10H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" />
                </svg>
                <span>Laporan & Ekspor</span>
            </div>
            <span class="text-[10px] font-semibold text-gray-400 bg-gray-100 px-1.5 py-0.5 rounded">Segera</span>
        </div>
        @endcannot

        @cannot('manage_admin_users')
        {{-- Pengguna & Hak Akses (Upcoming) --}}
        <div
            class="flex items-center justify-between px-3 py-2.5 rounded-lg text-sm font-medium text-gray-400 cursor-not-allowed select-none"
            title="Modul Manajemen Pengguna dijadwalkan pada fase berikutnya"
        >
            <div class="flex items-center gap-3">
                <svg class="w-5 h-5 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10.325 4.317c.426-1.756 2.924-1.756 3.35 0a1.724 1.724 0 002.573 1.066c1.543-.94 3.31.826 2.37 2.37a1.724 1.724 0 001.065 2.572c1.756.426 1.756 2.924 0 3.35a1.724 1.724 0 00-1.066 2.573c.94 1.543-.826 3.31-2.37 2.37a1.724 1.724 0 00-2.572 1.065c-.426 1.756-2.924 1.756-3.35 0a1.724 1.724 0 00-2.573-1.066c-1.543.94-3.31-.826-2.37-2.37a1.724 1.724 0 00-1.065-2.572c-1.756-.426-1.756-2.924 0-3.35a1.724 1.724 0 001.066-2.573c-.94-1.543.826-3.31 2.37-2.37.996.608 2.296.07 2.572-1.065z" />
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z" />
                </svg>
                <span>Manajemen Pengguna</span>
            </div>
            <span class="text-[10px] font-semibold text-gray-400 bg-gray-100 px-1.5 py-0.5 rounded">Segera</span>
        </div>
        @endcannot
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
                    <span class="text-[11px] text-gray-500 truncate">{{ auth()->user()?->hasRole('superadmin') ? 'Superadmin' : 'Admin PPDB' }}</span>
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

