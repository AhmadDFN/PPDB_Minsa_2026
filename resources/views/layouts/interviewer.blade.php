<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}" class="h-full">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1, maximum-scale=1, user-scalable=no">
    <meta name="csrf-token" content="{{ csrf_token() }}">

    <title>{{ isset($title) ? $title . ' — ' : '' }}Portal Pewawancara — PPDB MIN 1 Kota Madiun</title>

    <link rel="icon" type="image/png" href="{{ asset('images/logo-minsatama.png') }}">
    <link rel="icon" type="image/svg+xml" href="{{ asset('favicon.svg') }}">

    @fonts
    @vite(['resources/css/app.css', 'resources/js/app.js'])
    @stack('styles')
</head>
<body class="bg-gray-50 text-gray-800 antialiased min-h-screen flex flex-col font-sans">
    {{-- Top App Header (Mobile-First) --}}
    <header class="bg-white border-b border-emerald-100 shadow-xs sticky top-0 z-30">
        <div class="max-w-2xl mx-auto px-4 py-3 flex items-center justify-between">
            <div class="flex items-center gap-3">
                <a href="{{ route('interview.index') }}" class="flex items-center gap-2.5">
                    <img src="{{ asset('images/logo-minsatama.png') }}" alt="Logo MIN 1 Kota Madiun" class="w-9 h-9 object-contain shrink-0">
                    <div>
                        <div class="text-xs font-semibold text-emerald-800 uppercase tracking-wider">Portal Pewawancara</div>
                        <div class="text-sm font-bold text-gray-900 leading-tight">MIN 1 Kota Madiun</div>
                    </div>
                </a>
            </div>

            <div class="flex items-center gap-2">
                @auth
                    <div class="hidden sm:block text-right">
                        <div class="text-xs font-semibold text-gray-800">{{ auth()->user()->name }}</div>
                        <div class="text-[10px] text-emerald-600 font-medium">
                            @if(auth()->user()->hasRole('interviewer_stage_1'))
                                Tahap 1 (BTQ)
                            @elseif(auth()->user()->hasRole('interviewer_stage_2'))
                                Tahap 2 (Calistung)
                            @elseif(auth()->user()->hasRole('interviewer_stage_3'))
                                Tahap 3 (Unggulan)
                            @endif
                        </div>
                    </div>

                    <form method="POST" action="{{ route('logout') }}" class="inline">
                        @csrf
                        <button
                            type="submit"
                            title="Keluar dari Portal"
                            class="inline-flex items-center justify-center p-2 rounded-xl text-gray-500 hover:text-red-600 hover:bg-red-50 focus:outline-hidden focus:ring-2 focus:ring-red-500 transition min-h-11 min-w-11"
                            aria-label="Logout"
                        >
                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 16l4-4m0 0l-4-4m4 4H7m6 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h4a3 3 0 013 3v1" />
                            </svg>
                        </button>
                    </form>
                @endauth
            </div>
        </div>
    </header>

    {{-- Main Mobile Container --}}
    <main class="flex-1 max-w-2xl w-full mx-auto px-4 py-5">
        {{-- Flash Messages --}}
        @if (session('success'))
            <div class="mb-4 p-4 rounded-xl bg-emerald-50 border border-emerald-200 text-emerald-900 flex items-start gap-3 shadow-xs">
                <svg class="w-5 h-5 text-emerald-600 shrink-0 mt-0.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z" />
                </svg>
                <div class="text-sm font-medium flex-1">{{ session('success') }}</div>
            </div>
        @endif

        @if (session('error'))
            <div class="mb-4 p-4 rounded-xl bg-red-50 border border-red-200 text-red-900 flex items-start gap-3 shadow-xs">
                <svg class="w-5 h-5 text-red-600 shrink-0 mt-0.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                </svg>
                <div class="text-sm font-medium flex-1">{{ session('error') }}</div>
            </div>
        @endif

        @if (session('warning'))
            <div class="mb-4 p-4 rounded-xl bg-amber-50 border border-amber-200 text-amber-900 flex items-start gap-3 shadow-xs">
                <svg class="w-5 h-5 text-amber-600 shrink-0 mt-0.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z" />
                </svg>
                <div class="text-sm font-medium flex-1">{{ session('warning') }}</div>
            </div>
        @endif

        @if (session('info'))
            <div class="mb-4 p-4 rounded-xl bg-blue-50 border border-blue-200 text-blue-900 flex items-start gap-3 shadow-xs">
                <svg class="w-5 h-5 text-blue-600 shrink-0 mt-0.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                </svg>
                <div class="text-sm font-medium flex-1">{{ session('info') }}</div>
            </div>
        @endif

        @yield('content')
    </main>

    {{-- Footer --}}
    <footer class="mt-auto py-4 border-t border-gray-200 bg-white text-center text-xs text-gray-500">
        <p>&copy; {{ date('Y') }} PPDB MIN 1 Kota Madiun. Sistem Seleksi Wawancara.</p>
    </footer>

    @stack('scripts')
</body>
</html>
