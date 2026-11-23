<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}" class="scroll-smooth">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">

    <title>{{ isset($title) ? $title . ' — ' : '' }}Admin PPDB — {{ config('app.name') }}</title>

    <link rel="icon" type="image/png" href="{{ asset('images/logo-minsatama.png') }}">
    <link rel="icon" type="image/svg+xml" href="{{ asset('favicon.svg') }}">

    @fonts
    @vite(['resources/css/app.css', 'resources/js/app.js'])
    @stack('styles')
</head>
<body class="bg-gray-100 text-gray-800 antialiased min-h-screen flex flex-col">

    {{-- Top Navigation Bar --}}
    <header class="bg-white border-b border-gray-200 sticky top-0 z-30 shadow-xs">
        <div class="flex items-center justify-between h-16 px-4 sm:px-6">
            <div class="flex items-center gap-4">
                {{-- Mobile menu button --}}
                <button
                    type="button"
                    id="admin-sidebar-toggle"
                    class="lg:hidden p-2 rounded-lg text-gray-500 hover:text-gray-700 hover:bg-gray-100 focus:outline-none focus:ring-2 focus:ring-primary-500"
                    aria-label="Buka navigasi sidebar"
                >
                    <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16M4 18h16" />
                    </svg>
                </button>

                {{-- Brand Header / Title --}}
                <a href="{{ url('/admin') }}" class="flex items-center gap-3">
                    <div class="w-8 h-8 shrink-0 flex items-center justify-center">
                        <img src="{{ asset('images/logo-minsatama.png') }}" alt="Logo MIN 1 Kota Madiun" class="w-full h-full object-contain">
                    </div>
                    <div class="hidden sm:flex flex-col">
                        <span class="text-sm font-bold text-gray-900 leading-tight">PPDB Minsatama</span>
                        <span class="text-[11px] text-gray-500 font-medium">Panel Administrasi</span>
                    </div>
                </a>
            </div>

            {{-- Right Header Actions --}}
            <div class="flex items-center gap-4">
                {{-- Quick Link to Public Site --}}
                <a
                    href="{{ url('/') }}"
                    target="_blank"
                    class="hidden md:inline-flex items-center gap-1.5 text-xs text-gray-500 hover:text-primary-600 transition"
                    title="Buka portal publik"
                >
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 6H6a2 2 0 00-2 2v10a2 2 0 002 2h10a2 2 0 002-2v-4M14 4h6m0 0v6m0-6L10 14" />
                    </svg>
                    <span>Portal Publik</span>
                </a>

                <div class="h-5 w-px bg-gray-200 hidden md:block"></div>

                {{-- User Profile & Logout Placeholder --}}
                <div class="flex items-center gap-3">
                    <div class="hidden sm:flex flex-col text-right">
                        <span class="text-xs font-semibold text-gray-800">{{ auth()->user()?->name ?? 'Administrator' }}</span>
                        <span class="text-[11px] text-gray-500">{{ auth()->user()?->hasRole('superadmin') ? 'Superadmin' : 'Admin PPDB' }}</span>
                    </div>
                    <div class="w-8 h-8 rounded-full bg-primary-100 text-primary-800 flex items-center justify-center font-semibold text-xs border border-primary-200">
                        {{ substr(auth()->user()?->name ?? 'A', 0, 1) }}
                    </div>
                    <form method="POST" action="{{ url('/logout') }}" class="inline">
                        @csrf
                        <button
                            type="submit"
                            class="p-1.5 rounded-lg text-gray-400 hover:text-red-600 hover:bg-red-50 transition focus:outline-none"
                            title="Keluar dari sesi"
                        >
                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 16l4-4m0 0l-4-4m4 4H7m6 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h4a3 3 0 013 3v1" />
                            </svg>
                        </button>
                    </form>
                </div>
            </div>
        </div>
    </header>

    <div class="flex flex-1 overflow-hidden">
        {{-- Sidebar --}}
        <x-navigation.admin-sidebar />

        {{-- Main Content Area --}}
        <main class="flex-1 p-6 lg:p-8 overflow-y-auto">
            {{-- Breadcrumb Placeholder --}}
            @isset($breadcrumbs)
                <nav class="mb-4 flex items-center text-xs text-gray-500 gap-2" aria-label="Breadcrumb">
                    <a href="{{ url('/admin') }}" class="hover:text-primary-600 transition">Admin</a>
                    @foreach($breadcrumbs as $label => $url)
                        <span>/</span>
                        @if($loop->last)
                            <span class="font-semibold text-gray-800">{{ $label }}</span>
                        @else
                            <a href="{{ $url }}" class="hover:text-primary-600 transition">{{ $label }}</a>
                        @endif
                    @endforeach
                </nav>
            @endisset

            {{-- Page Header --}}
            @isset($header)
                <div class="mb-6">
                    {{ $header }}
                </div>
            @endisset

            {{-- Flash Messages --}}
            @if (session('success'))
                <div class="mb-6">
                    <x-ui.alert type="success" dismissible>{{ session('success') }}</x-ui.alert>
                </div>
            @endif

            @if (session('error'))
                <div class="mb-6">
                    <x-ui.alert type="danger" dismissible>{{ session('error') }}</x-ui.alert>
                </div>
            @endif

            @if (session('warning'))
                <div class="mb-6">
                    <x-ui.alert type="warning" dismissible>{{ session('warning') }}</x-ui.alert>
                </div>
            @endif

            {{ $slot ?? $__env->yieldContent('content') }}
        </main>
    </div>

    @stack('scripts')
</body>
</html>
