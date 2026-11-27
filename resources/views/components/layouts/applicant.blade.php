<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}" class="scroll-smooth">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <meta name="description" content="{{ $metaDescription ?? 'Portal Pendaftaran Peserta Didik Baru (PPDB) Mandiri MIN 1 Kota Madiun Tahun Ajaran 2026/2027.' }}">

    <title>{{ isset($title) ? $title . ' — ' : '' }}Portal Pendaftar — MIN 1 Kota Madiun</title>

    <link rel="icon" type="image/svg+xml" href="{{ asset('favicon.svg') }}">

    @fonts
    @vite(['resources/css/app.css', 'resources/js/app.js'])
    @stack('styles')
</head>
<body class="bg-slate-950 text-slate-100 antialiased min-h-screen flex flex-col font-sans selection:bg-emerald-500 selection:text-white {{ request()->routeIs('applicant.dashboard') ? 'calm-academic calm-dashboard' : '' }}">
    {{-- Skip to main content for accessibility --}}
    <a href="#main-content" class="sr-only focus:not-sr-only focus:absolute focus:top-2 focus:left-2 focus:z-50 btn btn-primary">
        Langsung ke konten utama
    </a>

    {{-- Dedicated Applicant Portal Header --}}
    <x-navigation.applicant-navbar />

    {{-- Global Flash Messages --}}
    <div class="max-w-6xl mx-auto px-4 sm:px-6 lg:px-8 w-full mt-6">
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

    {{-- Main Portal Content --}}
    <main id="main-content" class="flex-1 py-4 sm:py-6">
        {{ $slot ?? $__env->yieldContent('content') }}
    </main>

    {{-- Dedicated Unobtrusive Applicant Footer --}}
    <footer class="bg-slate-900 border-t border-slate-800/80 text-slate-400 mt-auto text-xs">
        <div class="max-w-6xl mx-auto px-4 sm:px-6 lg:px-8 py-6">
            <div class="flex flex-col sm:flex-row items-center justify-between gap-4">
                <div class="flex items-center gap-3">
                    <div class="w-7 h-7 rounded-lg bg-emerald-600/30 text-emerald-400 border border-emerald-500/30 flex items-center justify-center font-bold text-xs">
                        M1
                    </div>
                    <div>
                        <span class="font-bold text-slate-200">PPDB MIN 1 Kota Madiun</span>
                        <span class="text-slate-500 text-[11px] block sm:inline sm:ml-2">Portal Calon Siswa Baru T.A. 2026/2027</span>
                    </div>
                </div>

                <div class="text-center sm:text-right text-[11px] text-slate-400 space-y-1">
                    <p>
                        Pelayanan Loket PTSP: <span class="font-semibold text-slate-200">Senin – Jumat, 07.00 – 15.00 WIB</span>
                    </p>
                    <p class="text-slate-500">
                        Keamanan Kriptografis NIK &bull; &copy; 2026 MIN 1 Kota Madiun
                    </p>
                </div>
            </div>
        </div>
    </footer>

    @stack('scripts')
</body>
</html>
