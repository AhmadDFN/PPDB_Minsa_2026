<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}" class="scroll-smooth dark">
<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0" />
  <meta name="csrf-token" content="{{ csrf_token() }}" />
  <meta name="description" content="Portal Resmi PPDB MIN 1 Kota Madiun (MINSATAMA) Tahun Ajaran {{ $activePeriod->academic_year ?? '2026/2027' }}. Sistem pendaftaran mandiri digital paperless Kelas Unggulan & Reguler." />

  <title>PPDB {{ $activePeriod->academic_year ?? '2026/2027' }} | MIN 1 Kota Madiun (MINSATAMA) — Sistem Aplikasi Digital</title>

  <link rel="icon" type="image/png" href="{{ asset('images/logo-minsatama.png') }}" />
  <link rel="icon" type="image/svg+xml" href="{{ asset('favicon.svg') }}" />

  <!-- Google Fonts: Plus Jakarta Sans & Space Grotesk -->
  <link rel="preconnect" href="https://fonts.googleapis.com" />
  <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin />
  <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;500;600;700;800&family=Space+Grotesk:wght@500;600;700&display=swap" rel="stylesheet" />

  <!-- Tailwind CSS & Vite Assets -->
  @vite(['resources/css/app.css', 'resources/js/app.js'])

  <!-- Custom Futuristik Glassmorphism & Cyber Grid Styles -->
  <style>
    :root {
      color-scheme: dark;
    }

    body {
      background-color: #04090E;
      color: #F1F5F9;
      font-family: 'Plus Jakarta Sans', sans-serif;
      overflow-x: hidden;
    }

    /* Cyber Grid Background */
    .cyber-grid {
      background-size: 44px 44px;
      background-image:
        linear-gradient(to right, rgba(0, 245, 160, 0.04) 1px, transparent 1px),
        linear-gradient(to bottom, rgba(0, 217, 245, 0.04) 1px, transparent 1px);
    }

    /* Glassmorphism Panel */
    .glass-panel {
      background: rgba(10, 22, 34, 0.68);
      backdrop-filter: blur(18px);
      -webkit-backdrop-filter: blur(18px);
      border: 1px solid rgba(255, 255, 255, 0.09);
      box-shadow: 0 20px 50px rgba(0, 0, 0, 0.45);
    }

    .glass-panel-hover {
      transition: transform 0.35s cubic-bezier(0.22, 1, 0.36, 1), border-color 0.35s ease, box-shadow 0.35s ease;
    }

    .glass-panel-hover:hover {
      transform: translateY(-4px);
      border-color: rgba(0, 245, 160, 0.35);
      box-shadow: 0 24px 60px rgba(0, 245, 160, 0.09);
    }

    /* Neon Text Gradient */
    .text-gradient-neon {
      background: linear-gradient(135deg, #00F5A0 0%, #00D9F5 55%, #FBBF24 100%);
      -webkit-background-clip: text;
      -webkit-text-fill-color: transparent;
      background-clip: text;
    }

    /* 3D Student ID Card Perspective */
    .card-3d-wrapper {
      perspective: 1100px;
    }

    .card-3d {
      transform-style: preserve-3d;
      transition: transform 0.18s ease-out, box-shadow 0.3s ease;
    }

    .card-shimmer {
      background: linear-gradient(
        115deg,
        transparent 20%,
        rgba(0, 245, 160, 0.14) 45%,
        rgba(0, 217, 245, 0.18) 55%,
        transparent 80%
      );
      background-size: 200% 200%;
      animation: shimmerMove 6s infinite linear;
    }

    @keyframes shimmerMove {
      0% { background-position: 200% 0%; }
      100% { background-position: -200% 0%; }
    }

    @keyframes floatSlow {
      0%, 100% { transform: translateY(0px); }
      50% { transform: translateY(-10px); }
    }

    .animate-float {
      animation: floatSlow 5s ease-in-out infinite;
    }

    /* Custom Scrollbar */
    ::-webkit-scrollbar {
      width: 8px;
    }
    ::-webkit-scrollbar-track {
      background: #04090E;
    }
    ::-webkit-scrollbar-thumb {
      background: #162B3D;
      border-radius: 9999px;
    }
    ::-webkit-scrollbar-thumb:hover {
      background: #00F5A0;
    }
  </style>
</head>
<body class="cyber-grid selection:bg-emerald-400 selection:text-slate-950 antialiased">

  <!-- Ambient Neon Glow Orbs -->
  <div class="fixed inset-0 pointer-events-none overflow-hidden -z-10">
    <div class="absolute -top-36 -left-28 w-[28rem] h-[28rem] rounded-full bg-emerald-500/15 blur-[130px]"></div>
    <div class="absolute top-1/3 -right-32 w-[30rem] h-[30rem] rounded-full bg-cyan-500/15 blur-[140px]"></div>
    <div class="absolute bottom-0 left-1/3 w-[24rem] h-[24rem] rounded-full bg-amber-400/10 blur-[130px]"></div>
  </div>

  <!-- ===================================================================== -->
  <!-- 1. NAVBAR GLASSMORPHISM                                               -->
  <!-- ===================================================================== -->
  <header class="fixed top-0 inset-x-0 z-40 border-b border-white/10 bg-[#050C13]/85 backdrop-blur-xl">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 h-20 flex items-center justify-between gap-4">

      <!-- Brand Logo & Identitas Madrasah -->
      <a href="#beranda" class="flex items-center gap-3.5 group select-none">
        <div class="w-11 h-11 rounded-2xl bg-gradient-to-br from-[#00F5A0] via-[#00D9F5] to-amber-400 p-[1.5px] shadow-lg shadow-emerald-500/20 shrink-0">
          <div class="w-full h-full rounded-[14px] bg-[#061019] flex items-center justify-center p-1 overflow-hidden group-hover:scale-105 transition-transform">
            <img src="{{ asset('images/logo-minsatama.png') }}" alt="Logo MIN 1 Kota Madiun" class="w-full h-full object-contain" />
          </div>
        </div>
        <div>
          <div class="flex items-center gap-2">
            <span class="font-display font-bold text-base sm:text-lg text-white tracking-tight">MIN 1 KOTA MADIUN</span>
            <span class="hidden sm:inline-block px-2 py-0.5 text-[10px] font-bold uppercase tracking-wider rounded-md bg-emerald-500/15 text-emerald-300 border border-emerald-400/30">
              MINSATAMA
            </span>
          </div>
          <p class="text-[11px] sm:text-xs text-slate-400">Portal PPDB Digital &bull; TA {{ $activePeriod->academic_year ?? '2026/2027' }}</p>
        </div>
      </a>

      <!-- Desktop Navigation -->
      <nav class="hidden lg:flex items-center gap-7 text-sm font-medium text-slate-300" aria-label="Navigasi Utama">
        <a href="#beranda" class="hover:text-emerald-400 transition-colors">Beranda</a>
        <a href="#program" class="hover:text-emerald-400 transition-colors">Program Kelas</a>
        <a href="#alur-jadwal" class="hover:text-emerald-400 transition-colors">Alur &amp; Jadwal</a>
        <a href="#lokasi" class="hover:text-emerald-400 transition-colors">Lokasi &amp; PTSP</a>
        <a href="#faq" class="hover:text-emerald-400 transition-colors">FAQ</a>
      </nav>

      <!-- Desktop CTA Buttons -->
      <div class="hidden sm:flex items-center gap-3">
        <button type="button" onclick="openDocModal()" class="px-4 py-2.5 rounded-xl text-xs sm:text-sm font-semibold text-cyan-300 bg-cyan-500/10 hover:bg-cyan-500/20 border border-cyan-500/30 transition-all flex items-center gap-2 cursor-pointer">
          <svg class="w-4 h-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
            <path stroke-linecap="round" stroke-linejoin="round" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" />
          </svg>
          <span>Syarat Digital</span>
        </button>

        @auth
          @if (auth()->user()->isAdmin())
            <a href="{{ route('admin.dashboard') }}" class="px-5 py-2.5 rounded-xl text-xs sm:text-sm font-bold text-slate-950 bg-gradient-to-r from-[#00F5A0] to-[#00D9F5] hover:opacity-95 shadow-lg shadow-emerald-500/25 transition-all flex items-center gap-2">
              <span>Panel Admin</span>
              <svg class="w-4 h-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2">
                <path stroke-linecap="round" stroke-linejoin="round" d="M13.5 6H5.25A2.25 2.25 0 003 8.25v10.5A2.25 2.25 0 005.25 21h10.5A2.25 2.25 0 0018 18.75V10.5m-10.5 6L21 3m0 0h-5.25M21 3v5.25" />
              </svg>
            </a>
          @else
            <a href="{{ route('applicant.dashboard') }}" class="px-5 py-2.5 rounded-xl text-xs sm:text-sm font-bold text-slate-950 bg-gradient-to-r from-[#00F5A0] to-[#00D9F5] hover:opacity-95 shadow-lg shadow-emerald-500/25 transition-all flex items-center gap-2">
              <span class="w-2 h-2 rounded-full bg-slate-950 animate-pulse"></span>
              <span>Portal Pendaftar</span>
              <svg class="w-4 h-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2">
                <path stroke-linecap="round" stroke-linejoin="round" d="M13.5 6H5.25A2.25 2.25 0 003 8.25v10.5A2.25 2.25 0 005.25 21h10.5A2.25 2.25 0 0018 18.75V10.5m-10.5 6L21 3m0 0h-5.25M21 3v5.25" />
              </svg>
            </a>
          @endif

          <form method="POST" action="{{ auth()->user()->isAdmin() ? route('admin.logout') : route('logout') }}" class="inline">
            @csrf
            <button type="submit" class="px-2 py-1 text-xs text-slate-400 hover:text-rose-400 transition" title="Keluar">
              Keluar
            </button>
          </form>
        @else
          <button type="button" onclick="openPortalModal('Jalur Unggulan / Reguler')" class="px-5 py-2.5 rounded-xl text-xs sm:text-sm font-bold text-slate-950 bg-gradient-to-r from-[#00F5A0] to-[#00D9F5] hover:opacity-95 shadow-lg shadow-emerald-500/25 transition-all flex items-center gap-2 cursor-pointer">
            <span>Aplikasi PPDB</span>
            <svg class="w-4 h-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2">
              <path stroke-linecap="round" stroke-linejoin="round" d="M13.5 6H5.25A2.25 2.25 0 003 8.25v10.5A2.25 2.25 0 005.25 21h10.5A2.25 2.25 0 0018 18.75V10.5m-10.5 6L21 3m0 0h-5.25M21 3v5.25" />
            </svg>
          </button>
        @endauth
      </div>

      <!-- Mobile Hamburger Button -->
      <button type="button" id="mobileMenuBtn" onclick="toggleMobileMenu()" aria-label="Buka Menu Navigasi" class="lg:hidden p-2.5 rounded-xl glass-panel text-slate-200 hover:text-emerald-400 cursor-pointer">
        <svg class="w-6 h-6" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
          <path stroke-linecap="round" stroke-linejoin="round" d="M4 6h16M4 12h16M4 18h16" />
        </svg>
      </button>
    </div>

    <!-- Mobile Dropdown Navigation -->
    <div id="mobileMenu" class="hidden lg:hidden border-t border-white/10 bg-[#060E16]/95 backdrop-blur-2xl px-4 pt-3 pb-6 space-y-2">
      <a href="#beranda" onclick="toggleMobileMenu()" class="block px-4 py-2.5 rounded-xl text-sm font-medium text-slate-200 hover:bg-white/5 hover:text-emerald-400">Beranda</a>
      <a href="#program" onclick="toggleMobileMenu()" class="block px-4 py-2.5 rounded-xl text-sm font-medium text-slate-200 hover:bg-white/5 hover:text-emerald-400">Program Kelas</a>
      <a href="#alur-jadwal" onclick="toggleMobileMenu()" class="block px-4 py-2.5 rounded-xl text-sm font-medium text-slate-200 hover:bg-white/5 hover:text-emerald-400">Alur &amp; Jadwal</a>
      <a href="#lokasi" onclick="toggleMobileMenu()" class="block px-4 py-2.5 rounded-xl text-sm font-medium text-slate-200 hover:bg-white/5 hover:text-emerald-400">Lokasi &amp; PTSP</a>
      <a href="#faq" onclick="toggleMobileMenu()" class="block px-4 py-2.5 rounded-xl text-sm font-medium text-slate-200 hover:bg-white/5 hover:text-emerald-400">FAQ</a>
      <div class="pt-3 grid grid-cols-2 gap-3 border-t border-white/10">
        <button type="button" onclick="toggleMobileMenu(); openDocModal()" class="py-3 px-4 rounded-xl text-xs font-semibold text-cyan-300 bg-cyan-500/10 border border-cyan-500/30 text-center">
          Syarat Digital
        </button>
        @auth
          <a href="{{ route('applicant.dashboard') }}" class="py-3 px-4 rounded-xl text-xs font-bold text-slate-950 bg-gradient-to-r from-[#00F5A0] to-[#00D9F5] text-center">
            Portal Pendaftar
          </a>
        @else
          <button type="button" onclick="toggleMobileMenu(); openPortalModal('Jalur Unggulan / Reguler')" class="py-3 px-4 rounded-xl text-xs font-bold text-slate-950 bg-gradient-to-r from-[#00F5A0] to-[#00D9F5] text-center">
            Aplikasi PPDB
          </button>
        @endauth
      </div>
    </div>
  </header>

  <!-- ===================================================================== -->
  <!-- 2. HERO SECTION + KARTU DIGITAL 3D + COUNTDOWN                        -->
  <!-- ===================================================================== -->
  <section id="beranda" class="relative pt-32 pb-20 lg:pt-40 lg:pb-28">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
      <div class="grid lg:grid-cols-12 gap-12 lg:gap-10 items-center">

        <!-- Kolom Kiri: Headline, Deskripsi, Countdown & Tombol -->
        <div class="lg:col-span-7 space-y-7 text-center lg:text-left">
          <!-- Status Badge -->
          <div class="inline-flex items-center gap-2.5 px-4 py-2 rounded-full glass-panel border border-emerald-400/30">
            <span class="relative flex h-2.5 w-2.5">
              <span class="animate-ping absolute inline-flex h-full w-full rounded-full bg-emerald-400 opacity-75"></span>
              <span class="relative inline-flex rounded-full h-2.5 w-2.5 bg-emerald-400"></span>
            </span>
            <span class="text-[11px] sm:text-xs font-bold tracking-wider uppercase text-emerald-300">
              PPDB {{ $activePeriod->academic_year ?? '2026/2027' }} &bull; 100% PAPERLESS APP &bull; PTSP 07.00 – 15.00 WIB
            </span>
          </div>

          <!-- Headline -->
          <h1 class="font-display text-4xl sm:text-5xl lg:text-[3.5rem] font-bold tracking-tight leading-[1.13] text-white">
            Mencetak Generasi <br class="hidden sm:block" />
            <span class="text-gradient-neon">Qur'ani, Cerdas &amp; Digital</span> <br class="hidden sm:block" />
            di Era Masa Depan.
          </h1>

          <!-- Subjudul -->
          <p class="text-base sm:text-lg text-slate-300/90 max-w-2xl mx-auto lg:mx-0 leading-relaxed">
            Portal Informasi Resmi Penerimaan Peserta Didik Baru (PPDB) <strong class="text-white font-semibold">MIN 1 Kota Madiun (MINSATAMA)</strong>. Seluruh pendaftaran dan unggah dokumen kini berjalan <strong class="text-emerald-300 font-semibold">100% digital tanpa map fisik</strong> melalui Sistem Aplikasi PPDB untuk pilihan <strong class="text-emerald-300 font-semibold">Program Unggulan</strong> maupun <strong class="text-cyan-300 font-semibold">Program Reguler</strong>.
          </p>

          <!-- Live Countdown Timer -->
          <div class="max-w-lg mx-auto lg:mx-0">
            <div class="flex items-center justify-between text-xs text-slate-400 mb-2.5 px-1">
              <span class="uppercase tracking-wider font-semibold text-amber-400">Target Pelayanan &amp; Seleksi PPDB</span>
              <span class="font-mono text-slate-300">{{ $activePeriod?->end_date?->translatedFormat('d M Y') ?? '08 Feb 2027' }}</span>
            </div>
            <div class="grid grid-cols-4 gap-2.5 sm:gap-3.5">
              <div class="glass-panel rounded-2xl p-3 sm:p-4 text-center border border-emerald-500/20">
                <div id="cd-days" class="font-display text-2xl sm:text-3xl font-bold text-white">00</div>
                <div class="text-[11px] uppercase tracking-wider text-emerald-400 mt-0.5">Hari</div>
              </div>
              <div class="glass-panel rounded-2xl p-3 sm:p-4 text-center border border-cyan-500/20">
                <div id="cd-hours" class="font-display text-2xl sm:text-3xl font-bold text-white">00</div>
                <div class="text-[11px] uppercase tracking-wider text-cyan-400 mt-0.5">Jam</div>
              </div>
              <div class="glass-panel rounded-2xl p-3 sm:p-4 text-center border border-emerald-500/20">
                <div id="cd-minutes" class="font-display text-2xl sm:text-3xl font-bold text-white">00</div>
                <div class="text-[11px] uppercase tracking-wider text-emerald-400 mt-0.5">Menit</div>
              </div>
              <div class="glass-panel rounded-2xl p-3 sm:p-4 text-center border border-amber-500/20">
                <div id="cd-seconds" class="font-display text-2xl sm:text-3xl font-bold text-amber-400">00</div>
                <div class="text-[11px] uppercase tracking-wider text-amber-300 mt-0.5">Detik</div>
              </div>
            </div>
          </div>

          <!-- Tombol CTA Utama -->
          <div class="flex flex-col sm:flex-row items-center justify-center lg:justify-start gap-4 pt-1">
            @auth
              <a href="{{ route('applicant.dashboard') }}" class="w-full sm:w-auto px-7 py-4 rounded-2xl font-bold text-slate-950 bg-gradient-to-r from-[#00F5A0] via-[#00D9F5] to-[#00F5A0] bg-[length:200%_auto] hover:bg-right transition-all duration-500 shadow-xl shadow-emerald-500/25 flex items-center justify-center gap-3 text-sm sm:text-base">
                <span>Buka Dasbor Pendaftar Saya</span>
                <svg class="w-5 h-5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2">
                  <path stroke-linecap="round" stroke-linejoin="round" d="M14 5l7 7m0 0l-7 7m7-7H3" />
                </svg>
              </a>
            @else
              <button type="button" onclick="openPortalModal('Jalur Unggulan / Reguler')" class="w-full sm:w-auto px-7 py-4 rounded-2xl font-bold text-slate-950 bg-gradient-to-r from-[#00F5A0] via-[#00D9F5] to-[#00F5A0] bg-[length:200%_auto] hover:bg-right transition-all duration-500 shadow-xl shadow-emerald-500/25 flex items-center justify-center gap-3 text-sm sm:text-base cursor-pointer">
                <svg class="w-5 h-5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2">
                  <path stroke-linecap="round" stroke-linejoin="round" d="M15.59 14.37a6 6 0 01-5.84 7.38v-4.8m5.84-2.58a14.98 14.98 0 006.16-12.12A14.98 14.98 0 009.631 8.41m5.96 5.96a14.926 14.926 0 01-5.841 2.58m-.119-8.54a6 6 0 00-7.381 5.84h4.8m2.581-5.84a14.927 14.927 0 00-2.58 5.84m2.699 2.7c-.103.021-.207.041-.311.06a15.09 15.09 0 01-2.448-2.448 14.9 14.9 0 01.06-.312m-2.24 2.39a4.493 4.493 0 00-1.757 4.306 4.493 4.493 0 004.306-1.758M16.5 9a1.5 1.5 0 11-3 0 1.5 1.5 0 013 0z" />
                </svg>
                <span>Akses Aplikasi PPDB Online</span>
              </button>
            @endauth

            <button type="button" onclick="openDocModal()" class="w-full sm:w-auto px-7 py-4 rounded-2xl font-semibold text-white glass-panel hover:bg-white/10 border border-white/15 transition-all flex items-center justify-center gap-2.5 text-sm sm:text-base cursor-pointer">
              <svg class="w-5 h-5 text-cyan-400" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                <path stroke-linecap="round" stroke-linejoin="round" d="M9 12.75L11.25 15 15 9.75m-3-7.036A11.959 11.959 0 013.598 6 11.99 11.99 0 003 9.749c0 5.592 3.824 10.29 9 11.623 5.176-1.332 9-6.03 9-11.622 0-1.31-.21-2.571-.598-3.751h-.152c-3.196 0-6.1-1.248-8.25-3.285z" />
              </svg>
              <span>Checklist Dokumen Digital</span>
            </button>
          </div>
        </div>

        <!-- Kolom Kanan: Kartu Peserta Digital 3D Interaktif -->
        <div class="lg:col-span-5 flex justify-center">
          <div class="card-3d-wrapper w-full max-w-md animate-float">
            <div id="studentIdCard" class="card-3d relative rounded-3xl p-6 sm:p-7 glass-panel border border-emerald-400/35 overflow-hidden shadow-2xl shadow-emerald-500/15">
              <!-- Shimmer Overlay -->
              <div class="card-shimmer absolute inset-0 pointer-events-none"></div>

              <!-- Header Kartu -->
              <div class="flex items-center justify-between pb-5 border-b border-white/10 relative z-10">
                <div class="flex items-center gap-3">
                  <div class="w-10 h-10 rounded-xl bg-gradient-to-br from-emerald-400 to-cyan-400 p-0.5 flex items-center justify-center shrink-0 overflow-hidden shadow-sm">
                    <img src="{{ asset('images/logo-minsatama.png') }}" alt="Logo MIN 1 Kota Madiun" class="w-full h-full object-contain bg-[#061019] rounded-[10px] p-0.5" />
                  </div>
                  <div>
                    <div class="font-display font-bold text-sm text-white tracking-wide">KARTU PESERTA DIGITAL</div>
                    <div class="text-[11px] text-emerald-400">PPDB MIN 1 KOTA MADIUN</div>
                  </div>
                </div>
                <span class="px-2.5 py-1 rounded-lg bg-amber-400/15 border border-amber-400/30 text-amber-300 font-mono text-[11px] font-bold">
                  TA {{ $activePeriod->academic_year ?? '2026/2027' }}
                </span>
              </div>

              <!-- Isi Profil Kartu -->
              <div class="py-6 flex items-center gap-4 relative z-10">
                <div class="w-16 h-16 sm:w-20 sm:h-20 rounded-2xl bg-gradient-to-br from-emerald-500/20 to-cyan-500/20 border border-emerald-400/40 flex flex-col items-center justify-center text-emerald-300 shrink-0">
                  <svg class="w-8 h-8" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M15.75 6a3.75 3.75 0 11-7.5 0 3.75 3.75 0 017.5 0zM4.501 20.118a7.5 7.5 0 0114.998 0A17.933 17.933 0 0112 21.75c-2.676 0-5.216-.584-7.499-1.632z" />
                  </svg>
                  <span class="text-[9px] font-mono uppercase mt-1 text-cyan-300">E-PASS</span>
                </div>

                <div class="space-y-1.5 flex-1 min-w-0">
                  <div class="text-[11px] uppercase tracking-wider text-slate-400">Nomor Registrasi Aplikasi</div>
                  <div class="font-mono text-sm sm:text-base font-bold text-amber-400 tracking-wider truncate">MINSATAMA-UNG-{{ date('Y') }}-001</div>
                  <div class="text-base sm:text-lg font-bold text-white truncate">Aisyah Humaira Putri</div>
                  <div class="inline-flex items-center gap-1.5 text-xs text-emerald-400 bg-emerald-500/10 px-2.5 py-0.5 rounded-md border border-emerald-500/25">
                    <span class="w-1.5 h-1.5 rounded-full bg-emerald-400"></span>
                    <span>Terverifikasi Digital &bull; Paperless</span>
                  </div>
                </div>
              </div>

              <!-- Footer Metadata Kartu & QR Simulasi -->
              <div class="pt-4 border-t border-white/10 flex items-center justify-between gap-4 relative z-10">
                <div class="space-y-1 text-xs">
                  <div class="text-slate-400">Pilihan Program: <strong class="text-emerald-300">Program Unggulan</strong></div>
                  <div class="text-slate-400">Berkas Fisik: <strong class="text-cyan-300">Tidak Diperlukan (0 Map)</strong></div>
                </div>
                <!-- Mini SVG QR Code -->
                <div class="w-12 h-12 rounded-xl bg-white p-1.5 shrink-0 flex items-center justify-center">
                  <svg class="w-full h-full text-slate-950" viewBox="0 0 24 24" fill="currentColor">
                    <path d="M3 3h7v7H3V3zm2 2v3h3V5H5zm9-2h7v7h-7V3zm2 2v3h3V5h-3zM3 14h7v7H3v-7zm2 2v3h3v-3H5zm11-2h2v2h-2v-2zm-2 2h2v2h-2v-2zm4 0h3v2h-3v-2zm-4 3h2v2h-2v-2zm3 0h4v2h-4v-2z" />
                  </svg>
                </div>
              </div>
            </div>
          </div>
        </div>

      </div>

      <!-- Highlight Keunggulan Singkat -->
      <div class="grid grid-cols-2 lg:grid-cols-4 gap-4 mt-16">
        <div class="glass-panel rounded-2xl p-5 border border-white/10">
          <div class="text-2xl sm:text-3xl font-display font-bold text-emerald-400">100%</div>
          <div class="text-xs sm:text-sm font-semibold text-white mt-1">Pendaftaran Digital</div>
          <p class="text-xs text-slate-400 mt-1">Tanpa map fisik, unggah scan/foto dokumen langsung di aplikasi.</p>
        </div>
        <div class="glass-panel rounded-2xl p-5 border border-white/10">
          <div class="text-2xl sm:text-3xl font-display font-bold text-cyan-400">2 Jalur</div>
          <div class="text-xs sm:text-sm font-semibold text-white mt-1">Program Unggulan &amp; Program Reguler</div>
          <p class="text-xs text-slate-400 mt-1">Pilihan program pendidikan sesuai potensi &amp; minat putra-putri Anda.</p>
        </div>
        <div class="glass-panel rounded-2xl p-5 border border-white/10">
          <div class="text-2xl sm:text-3xl font-display font-bold text-amber-400">Akreditasi A</div>
          <div class="text-xs sm:text-sm font-semibold text-white mt-1">Madrasah Unggulan</div>
          <p class="text-xs text-slate-400 mt-1">Integrasi kurikulum nasional, Kemenag RI, Tahfidz &amp; sains digital.</p>
        </div>
        <div class="glass-panel rounded-2xl p-5 border border-white/10">
          <div class="text-2xl sm:text-3xl font-display font-bold text-emerald-400">07.00–15.00</div>
          <div class="text-xs sm:text-sm font-semibold text-white mt-1">Layanan Helpdesk PTSP</div>
          <p class="text-xs text-slate-400 mt-1">Verifikasi berkas online &amp; konsultasi langsung setiap hari kerja.</p>
        </div>
      </div>
    </div>
  </section>

  <!-- ===================================================================== -->
  <!-- 3. DUA PROGRAM KELAS RESMI (UNGGULAN & REGULER)                       -->
  <!-- ===================================================================== -->
  <section id="program" class="py-20 border-t border-white/5 bg-[#050C13]/70">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">

      <div class="text-center max-w-3xl mx-auto mb-14 space-y-3">
        <span class="text-xs font-bold uppercase tracking-widest text-cyan-400 px-3.5 py-1.5 rounded-full bg-cyan-500/10 border border-cyan-500/20">
          Pilihan Program Pendidikan MINSATAMA
        </span>
        <h2 class="font-display text-3xl sm:text-4xl font-bold text-white">
          2 Jalur Penerimaan: <span class="text-gradient-neon">Program Unggulan &amp; Program Reguler</span>
        </h2>
        <p class="text-slate-400 text-sm sm:text-base">
          MIN 1 Kota Madiun membuka dua pilihan program kelas dengan ekosistem pembelajaran modern. Keduanya menggunakan pendaftaran berbasis aplikasi tanpa pengumpulan map fisik.
        </p>
      </div>

      <div class="grid lg:grid-cols-2 gap-8">

        <!-- KARTU 1: PROGRAM KELAS UNGGULAN -->
        <div class="glass-panel glass-panel-hover rounded-3xl p-7 sm:p-9 flex flex-col justify-between border-t-4 border-t-emerald-400 relative overflow-hidden">
          <div class="absolute -right-12 -top-12 w-40 h-40 bg-emerald-500/10 rounded-full blur-2xl pointer-events-none"></div>
          <div>
            <div class="flex flex-wrap items-center justify-between gap-2 mb-5">
              <div class="flex flex-wrap items-center gap-2">
                <span class="px-3.5 py-1 rounded-full text-xs font-bold bg-emerald-500/15 text-emerald-300 border border-emerald-400/40">
                  PROGRAM KELAS UNGGULAN
                </span>
                <span class="px-3 py-1 rounded-full text-xs font-bold bg-emerald-950 text-emerald-400 border border-emerald-500/40">
                  100% Paperless
                </span>
              </div>
              <svg class="w-7 h-7 text-amber-400" viewBox="0 0 24 24" fill="currentColor">
                <path d="M5 16L3 5l5.5 5L12 4l3.5 6L21 5l-2 11H5zm14 3c0 .6-.4 1-1 1H6c-.6 0-1-.4-1-1v-1h14v1z" />
              </svg>
            </div>

            <h3 class="font-display text-2xl sm:text-3xl font-bold text-white mb-3">
              Kelas Unggulan <span class="text-emerald-400">(Tahfidz &amp; Sains Digital)</span>
            </h3>
            <p class="text-sm text-slate-300 mb-6 leading-relaxed">
              Memadukan Kurikulum Nasional &amp; Kementerian Agama RI dengan <strong>Kurikulum PLUS</strong> berbasis <em>Self Activities Learning (SAL)</em>, Ruang Kelas Ber-AC &amp; Smart Classroom, Homeroom Teacher &amp; Guru Pendamping, serta Outing Class tematik.
            </p>

            <!-- Detail Kurikulum Plus -->
            <div class="bg-[#060D14]/85 rounded-2xl p-4 sm:p-5 border border-emerald-500/20 mb-6">
              <p class="text-xs font-bold uppercase tracking-wider text-emerald-400 mb-3">Muatan Intensif Kurikulum Plus:</p>
              <div class="grid sm:grid-cols-2 gap-3 text-xs text-slate-200">
                <div class="flex items-center gap-2">
                  <span class="w-2 h-2 rounded-full bg-emerald-400 shrink-0"></span>
                  <span>Tartil &amp; Turjuman Al-Qur'an (8 JP/pekan)</span>
                </div>
                <div class="flex items-center gap-2">
                  <span class="w-2 h-2 rounded-full bg-emerald-400 shrink-0"></span>
                  <span>Tahfidzul Qur'an Intensif (3 JP/pekan)</span>
                </div>
                <div class="flex items-center gap-2">
                  <span class="w-2 h-2 rounded-full bg-cyan-400 shrink-0"></span>
                  <span>Bahasa Inggris Komunikatif (4 JP/pekan)</span>
                </div>
                <div class="flex items-center gap-2">
                  <span class="w-2 h-2 rounded-full bg-amber-400 shrink-0"></span>
                  <span>Olimpiade IPA &amp; Matematika (4 JP/pekan)</span>
                </div>
                <div class="flex items-center gap-2 sm:col-span-2">
                  <span class="w-2 h-2 rounded-full bg-cyan-400 shrink-0"></span>
                  <span>Musical Class, E-Learning Saintifik &amp; Smart Classroom</span>
                </div>
              </div>
            </div>

            <!-- Ketentuan Seleksi Digital -->
            <div class="space-y-2.5 text-xs text-slate-300 mb-8">
              <p class="font-bold text-white uppercase tracking-wider">Ketentuan &amp; Tahapan Seleksi Digital:</p>
              <div class="flex items-start gap-2.5">
                <span class="text-emerald-400 font-bold mt-0.5">&#10003;</span>
                <span>Membuat akun &amp; melengkapi biodata pada <strong>Aplikasi PPDB Online MINSATAMA</strong> tanpa pengumpulan map fisik.</span>
              </div>
              <div class="flex items-start gap-2.5">
                <span class="text-emerald-400 font-bold mt-0.5">&#10003;</span>
                <span>Mengunggah file scan/foto dokumen digital (Akta Kelahiran, KK, Surat Keterangan Aktif TK/RA, Pas Foto, serta Piagam Prestasi jika ada).</span>
              </div>
              <div class="flex items-start gap-2.5">
                <span class="text-emerald-400 font-bold mt-0.5">&#10003;</span>
                <span>Mengikuti <strong>Tes Pemetaan Akademik, BTQ/Tahfidz, Wawancara Orang Tua/Wali, serta Wawancara Psikologi</strong> khusus kelas unggulan.</span>
              </div>
            </div>
          </div>

          <button type="button" onclick="openPortalModal('Program Kelas Unggulan')" class="w-full py-4 px-5 rounded-2xl font-bold text-sm sm:text-base bg-gradient-to-r from-[#00F5A0] to-[#00D9F5] text-slate-950 hover:opacity-95 shadow-lg shadow-emerald-500/20 transition-all flex items-center justify-center gap-2 cursor-pointer">
            <span>Info &amp; Daftar Jalur Unggulan</span>
            <svg class="w-4 h-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2">
              <path stroke-linecap="round" stroke-linejoin="round" d="M13.5 6H5.25A2.25 2.25 0 003 8.25v10.5A2.25 2.25 0 005.25 21h10.5A2.25 2.25 0 0018 18.75V10.5m-10.5 6L21 3m0 0h-5.25M21 3v5.25" />
            </svg>
          </button>
        </div>

        <!-- KARTU 2: PROGRAM KELAS REGULER -->
        <div class="glass-panel glass-panel-hover rounded-3xl p-7 sm:p-9 flex flex-col justify-between border-t-4 border-t-cyan-400 relative overflow-hidden">
          <div class="absolute -right-12 -top-12 w-40 h-40 bg-cyan-500/10 rounded-full blur-2xl pointer-events-none"></div>
          <div>
            <div class="flex flex-wrap items-center justify-between gap-2 mb-5">
              <div class="flex flex-wrap items-center gap-2">
                <span class="px-3.5 py-1 rounded-full text-xs font-bold bg-cyan-500/15 text-cyan-300 border border-cyan-400/40">
                  PROGRAM KELAS REGULER
                </span>
                <span class="px-3 py-1 rounded-full text-xs font-bold bg-cyan-950/90 text-cyan-300 border border-cyan-500/40">
                  100% Paperless
                </span>
              </div>
              <svg class="w-7 h-7 text-cyan-400" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                <path stroke-linecap="round" stroke-linejoin="round" d="M4.26 10.147a60.436 60.436 0 00-.491 6.347A48.627 48.627 0 0112 20.904a48.627 48.627 0 018.232-4.41 60.46 60.46 0 00-.491-6.347m-15.482 0a50.57 50.57 0 00-2.658-.813A59.905 59.905 0 0112 3.493a59.902 59.902 0 0110.399 5.84c-.896.248-1.783.52-2.658.814m-15.482 0A50.697 50.697 0 0112 13.489a50.702 50.702 0 017.74-3.342" />
              </svg>
            </div>

            <h3 class="font-display text-2xl sm:text-3xl font-bold text-white mb-3">
              Kelas Reguler <span class="text-cyan-400">(Kurikulum Merdeka &amp; Kemenag)</span>
            </h3>
            <p class="text-sm text-slate-300 mb-6 leading-relaxed">
              Program pendidikan dasar berstandar nasional yang mengintegrasikan kurikulum umum Kemendikdasmen dengan muatan pendidikan agama Islam Kementerian Agama RI, pembiasaan ibadah harian, tahfidz Juz 30, serta ekstrakurikuler bakat minat.
            </p>

            <!-- Keunggulan Kelas Reguler -->
            <div class="bg-[#060D14]/85 rounded-2xl p-4 sm:p-5 border border-cyan-500/20 mb-6">
              <p class="text-xs font-bold uppercase tracking-wider text-cyan-400 mb-3">Keunggulan Ekosistem Kelas Reguler:</p>
              <div class="grid sm:grid-cols-2 gap-3 text-xs text-slate-200">
                <div class="flex items-center gap-2">
                  <span class="w-2 h-2 rounded-full bg-cyan-400 shrink-0"></span>
                  <span>Pembiasaan Shalat Dhuha &amp; Dhuhur Berjamaah</span>
                </div>
                <div class="flex items-center gap-2">
                  <span class="w-2 h-2 rounded-full bg-cyan-400 shrink-0"></span>
                  <span>Tahfidz Juz 30 &amp; Baca Tulis Al-Qur'an</span>
                </div>
                <div class="flex items-center gap-2">
                  <span class="w-2 h-2 rounded-full bg-emerald-400 shrink-0"></span>
                  <span>Akses Lab Komputer &amp; E-Learning Madrasah</span>
                </div>
                <div class="flex items-center gap-2">
                  <span class="w-2 h-2 rounded-full bg-amber-400 shrink-0"></span>
                  <span>Ekstrakurikuler Pramuka, Olahraga &amp; Seni</span>
                </div>
              </div>
            </div>

            <!-- Ketentuan Seleksi Digital -->
            <div class="space-y-2.5 text-xs text-slate-300 mb-8">
              <p class="font-bold text-white uppercase tracking-wider">Ketentuan &amp; Tahapan Seleksi Digital:</p>
              <div class="flex items-start gap-2.5">
                <span class="text-cyan-400 font-bold mt-0.5">&#10003;</span>
                <span>Memenuhi persyaratan usia masuk MI (prioritas usia 7 tahun atau minimal 6 tahun pada tanggal 1 Juli {{ date('Y') }}).</span>
              </div>
              <div class="flex items-start gap-2.5">
                <span class="text-cyan-400 font-bold mt-0.5">&#10003;</span>
                <span>Mengisi formulir dan mengunggah scan/foto dokumen persyaratan langsung di <strong>Aplikasi PPDB Online</strong> tanpa perlu map fisik.</span>
              </div>
              <div class="flex items-start gap-2.5">
                <span class="text-cyan-400 font-bold mt-0.5">&#10003;</span>
                <span>Mengikuti <strong>Observasi Kesiapan Belajar Siswa</strong> &amp; <strong>Wawancara Orang Tua/Wali Murid</strong> sesuai sesi yang tertera di aplikasi.</span>
              </div>
            </div>
          </div>

          <button type="button" onclick="openPortalModal('Program Kelas Reguler')" class="w-full py-4 px-5 rounded-2xl font-bold text-sm sm:text-base bg-cyan-500/15 hover:bg-cyan-400 hover:text-slate-950 text-cyan-300 border border-cyan-400/50 transition-all flex items-center justify-center gap-2 cursor-pointer">
            <span>Info &amp; Daftar Jalur Reguler</span>
            <svg class="w-4 h-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2">
              <path stroke-linecap="round" stroke-linejoin="round" d="M13.5 6H5.25A2.25 2.25 0 003 8.25v10.5A2.25 2.25 0 005.25 21h10.5A2.25 2.25 0 0018 18.75V10.5m-10.5 6L21 3m0 0h-5.25M21 3v5.25" />
            </svg>
          </button>
        </div>

      </div>
    </div>
  </section>

  <!-- ===================================================================== -->
  <!-- 4. ALUR PENDAFTARAN DIGITAL (2 SWITCHER) & JADWAL SELEKSI             -->
  <!-- ===================================================================== -->
  <section id="alur-jadwal" class="py-20">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">

      <!-- Bagian Alur Pendaftaran Digital -->
      <div class="mb-24">
        <div class="text-center max-w-3xl mx-auto mb-10 space-y-3">
          <span class="text-xs font-bold uppercase tracking-widest text-emerald-400 px-3.5 py-1.5 rounded-full bg-emerald-500/10 border border-emerald-500/20">
            Alur Resmi Sistem Aplikasi PPDB (Paperless)
          </span>
          <h2 class="font-display text-3xl sm:text-4xl font-bold text-white">
            Alur Pendaftaran <span class="text-gradient-neon">Program Unggulan &amp; Program Reguler</span>
          </h2>
          <p class="text-slate-400 text-sm sm:text-base">
            Seluruh pemberkasan dilakukan secara digital melalui aplikasi tanpa pengumpulan map fisik. Pilih tab di bawah untuk melihat tahapan masing-masing jalur.
          </p>
        </div>

        <!-- Toggle Switcher 2 Alur -->
        <div class="flex justify-center mb-10">
          <div class="inline-flex flex-wrap justify-center p-1.5 rounded-2xl glass-panel border border-white/10 gap-2">
            <button type="button" id="tabBtnUnggulan" onclick="switchAlurTab('unggulan')" class="px-5 sm:px-7 py-3 rounded-xl font-display font-bold text-xs sm:text-sm bg-gradient-to-r from-[#00F5A0] to-[#00D9F5] text-slate-950 shadow-lg transition-all cursor-pointer">
              Alur Program Unggulan (100% Digital)
            </button>
            <button type="button" id="tabBtnReguler" onclick="switchAlurTab('reguler')" class="px-5 sm:px-7 py-3 rounded-xl font-display font-bold text-xs sm:text-sm text-slate-300 hover:text-white transition-all cursor-pointer">
              Alur Program Reguler (100% Digital)
            </button>
          </div>
        </div>

        <!-- PANEL ALUR 1: JALUR UNGGULAN -->
        <div id="alurPanelUnggulan" class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-5 gap-4">
          <div class="glass-panel rounded-2xl p-5 border border-emerald-400/40 bg-emerald-500/[0.04]">
            <div class="flex items-center justify-between mb-4">
              <span class="w-9 h-9 rounded-xl bg-gradient-to-br from-emerald-400 to-cyan-400 text-slate-950 font-display font-bold flex items-center justify-center text-sm">01</span>
              <span class="text-[11px] font-mono text-emerald-300">ONLINE</span>
            </div>
            <h4 class="font-display font-bold text-white text-base mb-1.5">Buat Akun di Aplikasi</h4>
            <p class="text-xs text-slate-400 leading-relaxed">
              Registrasi akun orang tua pada Aplikasi PPDB MINSATAMA, pilih <strong>Program Unggulan</strong>, dan isi formulir biodata siswa.
            </p>
          </div>

          <div class="glass-panel rounded-2xl p-5 border border-white/10 hover:border-emerald-400/40 transition-all">
            <div class="flex items-center justify-between mb-4">
              <span class="w-9 h-9 rounded-xl bg-emerald-500/20 text-emerald-300 font-display font-bold flex items-center justify-center text-sm">02</span>
              <span class="text-[11px] font-mono text-amber-300">UPLOAD</span>
            </div>
            <h4 class="font-display font-bold text-white text-base mb-1.5">Unggah Dokumen Digital</h4>
            <p class="text-xs text-slate-400 leading-relaxed">
              Upload file scan/foto (PDF/JPG) Akta Kelahiran, KK, Surat Ket. Aktif TK/RA, Pas Foto, &amp; Piagam Prestasi (tanpa map fisik).
            </p>
          </div>

          <div class="glass-panel rounded-2xl p-5 border border-white/10 hover:border-emerald-400/40 transition-all">
            <div class="flex items-center justify-between mb-4">
              <span class="w-9 h-9 rounded-xl bg-emerald-500/20 text-emerald-300 font-display font-bold flex items-center justify-center text-sm">03</span>
              <span class="text-[11px] font-mono text-cyan-300">VERIFIKASI</span>
            </div>
            <h4 class="font-display font-bold text-white text-base mb-1.5">Validasi Online &amp; Kartu Tes</h4>
            <p class="text-xs text-slate-400 leading-relaxed">
              Tim verifikator PTSP memeriksa berkas digital (<strong>07.00 – 15.00 WIB</strong>). Setelah valid, <strong>Kartu Peserta Digital</strong> terbit otomatis di akun.
            </p>
          </div>

          <div class="glass-panel rounded-2xl p-5 border border-white/10 hover:border-emerald-400/40 transition-all">
            <div class="flex items-center justify-between mb-4">
              <span class="w-9 h-9 rounded-xl bg-emerald-500/20 text-emerald-300 font-display font-bold flex items-center justify-center text-sm">04</span>
              <span class="text-[11px] font-mono text-amber-300">SELEKSI</span>
            </div>
            <h4 class="font-display font-bold text-white text-base mb-1.5">Pemetaan &amp; Tes Psikologi</h4>
            <p class="text-xs text-slate-400 leading-relaxed">
              Hadir ke madrasah sesuai sesi di aplikasi untuk mengikuti pemetaan akademik, BTQ/Tahfidz, serta <strong>Wawancara Psikologi</strong> &amp; orang tua.
            </p>
          </div>

          <div class="glass-panel rounded-2xl p-5 border border-white/10 hover:border-emerald-400/40 transition-all sm:col-span-2 lg:col-span-1">
            <div class="flex items-center justify-between mb-4">
              <span class="w-9 h-9 rounded-xl bg-emerald-500/20 text-emerald-300 font-display font-bold flex items-center justify-center text-sm">05</span>
              <span class="text-[11px] font-mono text-emerald-300">HASIL</span>
            </div>
            <h4 class="font-display font-bold text-white text-base mb-1.5">Pengumuman &amp; Cetak Bukti</h4>
            <p class="text-xs text-slate-400 leading-relaxed">
              Hasil seleksi diumumkan real-time pada dashboard akun aplikasi PPDB, dilanjutkan unduh bukti PDF dan konfirmasi pendaftaran.
            </p>
          </div>
        </div>

        <!-- PANEL ALUR 2: JALUR REGULER -->
        <div id="alurPanelReguler" class="hidden grid-cols-1 sm:grid-cols-2 lg:grid-cols-5 gap-4">
          <div class="glass-panel rounded-2xl p-5 border border-cyan-400/40 bg-cyan-500/[0.04]">
            <div class="flex items-center justify-between mb-4">
              <span class="w-9 h-9 rounded-xl bg-gradient-to-br from-cyan-400 to-blue-400 text-slate-950 font-display font-bold flex items-center justify-center text-sm">01</span>
              <span class="text-[11px] font-mono text-cyan-300">ONLINE</span>
            </div>
            <h4 class="font-display font-bold text-white text-base mb-1.5">Buat Akun &amp; Pilih Reguler</h4>
            <p class="text-xs text-slate-400 leading-relaxed">
              Registrasi akun pada Aplikasi PPDB MINSATAMA, pilih <strong>Program Reguler</strong>, dan lengkapi formulir data siswa &amp; wali.
            </p>
          </div>

          <div class="glass-panel rounded-2xl p-5 border border-white/10 hover:border-cyan-400/40 transition-all">
            <div class="flex items-center justify-between mb-4">
              <span class="w-9 h-9 rounded-xl bg-cyan-500/20 text-cyan-300 font-display font-bold flex items-center justify-center text-sm">02</span>
              <span class="text-[11px] font-mono text-emerald-300">UPLOAD</span>
            </div>
            <h4 class="font-display font-bold text-white text-base mb-1.5">Unggah Dokumen Wajib</h4>
            <p class="text-xs text-slate-400 leading-relaxed">
              Upload scan/foto (PDF/JPG) Akta Kelahiran, Kartu Keluarga, Surat Ket. Aktif TK/RA, dan Pas Foto terbaru langsung ke sistem (tanpa map).
            </p>
          </div>

          <div class="glass-panel rounded-2xl p-5 border border-white/10 hover:border-cyan-400/40 transition-all">
            <div class="flex items-center justify-between mb-4">
              <span class="w-9 h-9 rounded-xl bg-cyan-500/20 text-cyan-300 font-display font-bold flex items-center justify-center text-sm">03</span>
              <span class="text-[11px] font-mono text-cyan-300">VERIFIKASI</span>
            </div>
            <h4 class="font-display font-bold text-white text-base mb-1.5">Validasi Usia &amp; Dokumen</h4>
            <p class="text-xs text-slate-400 leading-relaxed">
              Panitia memvalidasi persyaratan usia dan dokumen digital pukul <strong>07.00 – 15.00 WIB</strong> hingga terbit jadwal observasi di aplikasi.
            </p>
          </div>

          <div class="glass-panel rounded-2xl p-5 border border-white/10 hover:border-cyan-400/40 transition-all">
            <div class="flex items-center justify-between mb-4">
              <span class="w-9 h-9 rounded-xl bg-cyan-500/20 text-cyan-300 font-display font-bold flex items-center justify-center text-sm">04</span>
              <span class="text-[11px] font-mono text-amber-300">OBSERVASI</span>
            </div>
            <h4 class="font-display font-bold text-white text-base mb-1.5">Observasi Kesiapan Belajar</h4>
            <p class="text-xs text-slate-400 leading-relaxed">
              Mengikuti observasi kesiapan belajar anak serta wawancara singkat orang tua/wali sesuai sesi pada kartu peserta digital.
            </p>
          </div>

          <div class="glass-panel rounded-2xl p-5 border border-white/10 hover:border-cyan-400/40 transition-all sm:col-span-2 lg:col-span-1">
            <div class="flex items-center justify-between mb-4">
              <span class="w-9 h-9 rounded-xl bg-cyan-500/20 text-cyan-300 font-display font-bold flex items-center justify-center text-sm">05</span>
              <span class="text-[11px] font-mono text-cyan-300">HASIL</span>
            </div>
            <h4 class="font-display font-bold text-white text-base mb-1.5">Pengumuman &amp; Cetak Bukti</h4>
            <p class="text-xs text-slate-400 leading-relaxed">
              Pantau pengumuman kelulusan melalui Aplikasi PPDB MINSATAMA dan unduh dokumen bukti pendaftaran resmi ber-barcode.
            </p>
          </div>
        </div>
      </div>

      <!-- Tabel Jadwal Seleksi PPDB -->
      <div class="glass-panel rounded-3xl p-6 sm:p-10 border border-white/10">
        <div class="flex flex-col md:flex-row md:items-center justify-between gap-4 mb-8">
          <div>
            <span class="text-xs font-bold uppercase tracking-widest text-amber-400">Jadwal Resmi &amp; Helpdesk PTSP</span>
            <h3 class="font-display text-2xl sm:text-3xl font-bold text-white mt-1">
              Jadwal Seleksi PPDB <span class="text-cyan-400">T.A. {{ $activePeriod->academic_year ?? '2026/2027' }}</span>
            </h3>
          </div>
          <div class="inline-flex items-center gap-2.5 text-xs font-semibold text-emerald-300 bg-emerald-500/10 px-4 py-2.5 rounded-xl border border-emerald-500/30">
            <span class="w-2 h-2 rounded-full bg-emerald-400"></span>
            <span>Verifikasi Online &amp; Helpdesk PTSP: 07.00 – 15.00 WIB</span>
          </div>
        </div>

        <div class="overflow-x-auto">
          <table class="w-full text-left border-collapse min-w-[640px]">
            <thead>
              <tr class="border-b border-white/10 text-xs uppercase tracking-wider text-slate-400">
                <th class="py-4 px-4">Tahapan Kegiatan</th>
                <th class="py-4 px-4">Program Unggulan</th>
                <th class="py-4 px-4">Program Reguler</th>
                <th class="py-4 px-4 text-right">Metode / Keterangan</th>
              </tr>
            </thead>
            <tbody class="divide-y divide-white/5 text-sm">
              <tr class="hover:bg-white/[0.02] transition-colors">
                <td class="py-4 px-4 font-semibold text-white">Pendaftaran Akun &amp; Unggah Dokumen Digital</td>
                <td class="py-4 px-4 text-emerald-300 font-medium">Periode Aktif</td>
                <td class="py-4 px-4 text-cyan-300 font-medium">Periode Aktif</td>
                <td class="py-4 px-4 text-right">
                  <span class="inline-block px-3 py-1 rounded-full text-xs font-bold bg-emerald-500/15 text-emerald-400 border border-emerald-500/30">
                    100% Online di Aplikasi
                  </span>
                </td>
              </tr>
              <tr class="hover:bg-white/[0.02] transition-colors">
                <td class="py-4 px-4 font-semibold text-white">Verifikasi Dokumen oleh Tim Panitia</td>
                <td class="py-4 px-4 text-slate-200">Sesuai Jadwal Periode</td>
                <td class="py-4 px-4 text-slate-200">Sesuai Jadwal Periode</td>
                <td class="py-4 px-4 text-right">
                  <span class="inline-block px-3 py-1 rounded-full text-xs font-semibold bg-cyan-500/15 text-cyan-300 border border-cyan-500/30">
                    Digital (07.00–15.00 WIB)
                  </span>
                </td>
              </tr>
              <tr class="hover:bg-white/[0.02] transition-colors">
                <td class="py-4 px-4 font-semibold text-white">Pemetaan Kesiapan Belajar &amp; Wawancara Orang Tua</td>
                <td class="py-4 px-4 text-slate-200">Sesuai Jadwal Kartu Peserta</td>
                <td class="py-4 px-4 text-slate-200">Sesuai Jadwal Kartu Peserta</td>
                <td class="py-4 px-4 text-right">
                  <span class="inline-block px-3 py-1 rounded-full text-xs font-semibold bg-white/10 text-slate-200">
                    Hadir di Madrasah
                  </span>
                </td>
              </tr>
              <tr class="hover:bg-white/[0.02] transition-colors">
                <td class="py-4 px-4 font-semibold text-white">Wawancara Psikologi Khusus Program Unggulan</td>
                <td class="py-4 px-4 text-amber-300 font-medium">Jadwal Khusus Unggulan</td>
                <td class="py-4 px-4 text-slate-500 italic">Tidak Diwajibkan</td>
                <td class="py-4 px-4 text-right">
                  <span class="inline-block px-3 py-1 rounded-full text-xs font-semibold bg-amber-500/15 text-amber-300 border border-amber-500/30">
                    Khusus Unggulan
                  </span>
                </td>
              </tr>
              <tr class="hover:bg-white/[0.02] transition-colors">
                <td class="py-4 px-4 font-semibold text-white">Pengumuman Hasil &amp; Penyerahan Bukti Cetak</td>
                <td class="py-4 px-4 text-emerald-300 font-medium">Batas Akhir Periode</td>
                <td class="py-4 px-4 text-cyan-300 font-medium">Batas Akhir Periode</td>
                <td class="py-4 px-4 text-right">
                  <span class="inline-block px-3 py-1 rounded-full text-xs font-semibold bg-emerald-500/15 text-emerald-400 border border-emerald-500/30">
                    Loket PTSP &amp; Aplikasi
                  </span>
                </td>
              </tr>
            </tbody>
          </table>
        </div>
      </div>

    </div>
  </section>

  <!-- ===================================================================== -->
  <!-- 5. LOKASI KAMPUS & LAYANAN HELPDESK PTSP                              -->
  <!-- ===================================================================== -->
  <section id="lokasi" class="py-20 border-t border-white/5 bg-[#050C13]/70">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
      <div class="text-center max-w-3xl mx-auto mb-12 space-y-3">
        <span class="text-xs font-bold uppercase tracking-widest text-emerald-400 px-3.5 py-1.5 rounded-full bg-emerald-500/10 border border-emerald-500/20">
          Alamat Resmi &amp; Pelayanan Terpadu Satu Pintu
        </span>
        <h2 class="font-display text-3xl sm:text-4xl font-bold text-white">
          Lokasi Kampus &amp; <span class="text-gradient-neon">Helpdesk PTSP</span>
        </h2>
        <p class="text-slate-400 text-sm sm:text-base">
          Pelayanan konsultasi aplikasi PPDB, verifikasi digital, dan informasi madrasah dilayani setiap hari kerja pukul 07.00 – 15.00 WIB.
        </p>
      </div>

      <div class="grid md:grid-cols-3 gap-6">
        <!-- Card Kampus Utama -->
        <div class="glass-panel rounded-3xl p-6 sm:p-7 border border-emerald-500/25 space-y-3">
          <div class="inline-flex px-3 py-1 rounded-full text-xs font-bold bg-emerald-500/15 text-emerald-300 border border-emerald-400/30">
            KAMPUS UTAMA &amp; PTSP
          </div>
          <h3 class="font-display text-xl font-bold text-white">Gedung Induk MIN 1 Kota Madiun</h3>
          <p class="text-sm text-slate-300 leading-relaxed">
            Jl. Sitinggil No. 3, Kelurahan Demangan, Kecamatan Taman, Kota Madiun, Jawa Timur 63136.
          </p>
          <p class="text-xs text-slate-400 pt-1">
            Pusat layanan PTSP, Helpdesk PPDB, serta lokasi pelaksanaan tes pemetaan &amp; wawancara seleksi.
          </p>
        </div>

        <!-- Card Kampus 2 -->
        <div class="glass-panel rounded-3xl p-6 sm:p-7 border border-cyan-500/25 space-y-3">
          <div class="inline-flex px-3 py-1 rounded-full text-xs font-bold bg-cyan-500/15 text-cyan-300 border border-cyan-400/30">
            KAMPUS 2 MINSATAMA
          </div>
          <h3 class="font-display text-xl font-bold text-white">Gedung Kampus 2 Demangan</h3>
          <p class="text-sm text-slate-300 leading-relaxed">
            Jl. Kapten Tendean, Kelurahan Demangan, Kecamatan Taman, Kota Madiun, Jawa Timur.
          </p>
          <p class="text-xs text-slate-400 pt-1">
            Area gedung pembelajaran terpadu yang mendukung aktivitas akademik dan pengembangan bakat siswa.
          </p>
        </div>

        <!-- Card Jam Layanan & Kontak -->
        <div class="glass-panel rounded-3xl p-6 sm:p-7 border border-amber-500/25 space-y-3">
          <div class="inline-flex px-3 py-1 rounded-full text-xs font-bold bg-amber-500/15 text-amber-300 border border-amber-400/30">
            JAM OPERASIONAL &amp; KONTAK
          </div>
          <h3 class="font-display text-xl font-bold text-white font-mono">07.00 – 15.00 WIB</h3>
          <ul class="space-y-2 text-sm text-slate-300">
            <li><strong>Telepon Kantor:</strong> (0351) 462046</li>
            <li><strong>Helpdesk WhatsApp:</strong> 0856-4567-4020</li>
            <li><strong>Email Resmi:</strong> min1kotamadiun@gmail.com</li>
          </ul>
          <p class="text-xs text-slate-400 pt-1">
            Hari Senin – Jumat (hari kerja). Pendaftaran online di aplikasi dapat diakses 24 jam.
          </p>
        </div>
      </div>
    </div>
  </section>

  <!-- ===================================================================== -->
  <!-- 6. FAQ ACCORDION                                                      -->
  <!-- ===================================================================== -->
  <section id="faq" class="py-20 border-t border-white/5">
    <div class="max-w-4xl mx-auto px-4 sm:px-6 lg:px-8">

      <div class="text-center mb-12 space-y-3">
        <span class="text-xs font-bold uppercase tracking-widest text-cyan-400 px-3.5 py-1.5 rounded-full bg-cyan-500/10 border border-cyan-500/20">
          Pusat Informasi Orang Tua
        </span>
        <h2 class="font-display text-3xl sm:text-4xl font-bold text-white">
          Pertanyaan yang Sering <span class="text-gradient-neon">Diajukan (FAQ)</span>
        </h2>
        <p class="text-slate-400 text-sm sm:text-base">
          Jawaban atas pertanyaan umum seputar sistem aplikasi PPDB 100% digital tanpa map fisik serta perbedaan program kelas.
        </p>
      </div>

      <div class="space-y-4">
        <!-- FAQ 1 -->
        <div class="glass-panel rounded-2xl overflow-hidden border border-white/10">
          <button type="button" onclick="toggleFaq(1)" class="w-full px-6 py-5 text-left flex items-center justify-between gap-4 focus:outline-none cursor-pointer">
            <span class="font-display font-bold text-white text-base sm:text-lg">Apakah orang tua masih perlu mengumpulkan map fisik ke sekolah saat mendaftar?</span>
            <span id="faq-icon-1" class="text-emerald-400 font-bold text-lg transition-transform duration-300 rotate-180">&#9662;</span>
          </button>
          <div id="faq-body-1" class="px-6 pb-5 text-sm text-slate-300 leading-relaxed border-t border-white/5 pt-3">
            <strong>Tidak perlu.</strong> PPDB MIN 1 Kota Madiun menggunakan sistem aplikasi digital yang <strong>100% paperless</strong>. Orang tua tidak perlu lagi membeli map fisik atau mencetak formulir rangkap dua; cukup mengunggah hasil scan/foto dokumen persyaratan langsung di dalam aplikasi dan menyerahkan bukti cetak saat verifikasi fisik ke loket PTSP.
          </div>
        </div>

        <!-- FAQ 2 -->
        <div class="glass-panel rounded-2xl overflow-hidden border border-white/10">
          <button type="button" onclick="toggleFaq(2)" class="w-full px-6 py-5 text-left flex items-center justify-between gap-4 focus:outline-none cursor-pointer">
            <span class="font-display font-bold text-white text-base sm:text-lg">Apa saja dokumen digital yang wajib disiapkan untuk diunggah ke aplikasi?</span>
            <span id="faq-icon-2" class="text-emerald-400 font-bold text-lg transition-transform duration-300">&#9662;</span>
          </button>
          <div id="faq-body-2" class="hidden px-6 pb-5 text-sm text-slate-300 leading-relaxed border-t border-white/5 pt-3">
            Dokumen yang perlu dipindai/difoto dengan jelas (format PDF atau JPG/PNG, maks. 5 MB per file) meliputi: (1) Akta Kelahiran calon siswa, (2) Kartu Keluarga (KK), (3) Surat Keterangan Aktif Siswa dari TK/RA/BA asal, (4) Pas foto berwarna terbaru calon siswa, serta (5) Piagam/Sertifikat kejuaraan atau tahfidz (opsional bagi pendaftar jalur unggulan).
          </div>
        </div>

        <!-- FAQ 3 -->
        <div class="glass-panel rounded-2xl overflow-hidden border border-white/10">
          <button type="button" onclick="toggleFaq(3)" class="w-full px-6 py-5 text-left flex items-center justify-between gap-4 focus:outline-none cursor-pointer">
            <span class="font-display font-bold text-white text-base sm:text-lg">Apa perbedaan utama antara Program Unggulan dan Program Reguler?</span>
            <span id="faq-icon-3" class="text-emerald-400 font-bold text-lg transition-transform duration-300">&#9662;</span>
          </button>
          <div id="faq-body-3" class="hidden px-6 pb-5 text-sm text-slate-300 leading-relaxed border-t border-white/5 pt-3">
            <strong>Program Unggulan</strong> menerapkan Kurikulum Plus (tambahan jam intensif Tartil &amp; Turjuman Al-Qur'an, Tahfidzul Qur'an, Bahasa Inggris, Olimpiade IPA &amp; Matematika, Musical Class, ruang ber-AC &amp; Smart Classroom, serta guru pendamping kelas) dan mewajibkan <em>Wawancara Psikologi</em>. Sementara <strong>Program Reguler</strong> menjalankan kurikulum nasional &amp; Kemenag secara menyeluruh dengan pembiasaan ibadah harian, tahfidz Juz 30, dan ekstrakurikuler beragam tanpa tes psikologi khusus.
          </div>
        </div>

        <!-- FAQ 4 -->
        <div class="glass-panel rounded-2xl overflow-hidden border border-white/10">
          <button type="button" onclick="toggleFaq(4)" class="w-full px-6 py-5 text-left flex items-center justify-between gap-4 focus:outline-none cursor-pointer">
            <span class="font-display font-bold text-white text-base sm:text-lg">Kapan jam operasional Helpdesk PTSP jika mengalami kendala pendaftaran di aplikasi?</span>
            <span id="faq-icon-4" class="text-emerald-400 font-bold text-lg transition-transform duration-300">&#9662;</span>
          </button>
          <div id="faq-body-4" class="hidden px-6 pb-5 text-sm text-slate-300 leading-relaxed border-t border-white/5 pt-3">
            Layanan konsultasi dan Helpdesk PTSP MIN 1 Kota Madiun di <strong>Jl. Sitinggil No. 3, Demangan, Kec. Taman, Kota Madiun</strong> maupun melalui WhatsApp resmi madrasah (0856-4567-4020) beroperasi setiap hari kerja pukul <strong>07.00 – 15.00 WIB</strong>.
          </div>
        </div>
      </div>

    </div>
  </section>

  <!-- ===================================================================== -->
  <!-- FOOTER                                                                -->
  <!-- ===================================================================== -->
  <footer class="border-t border-white/10 bg-[#03070B] pt-14 pb-10">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
      <div class="grid md:grid-cols-2 lg:grid-cols-4 gap-10 pb-10 border-b border-white/10">

        <div class="space-y-4 lg:col-span-2">
          <div class="flex items-center gap-3.5">
            <div class="w-11 h-11 rounded-2xl bg-slate-900 border border-emerald-500/30 p-1 flex items-center justify-center shrink-0">
              <img src="{{ asset('images/logo-minsatama.png') }}" alt="Logo MIN 1 Kota Madiun" class="w-full h-full object-contain" />
            </div>
            <div>
              <div class="font-display font-bold text-white text-lg">MIN 1 Kota Madiun (MINSATAMA)</div>
              <p class="text-xs text-emerald-400 font-mono">NPSN: 60720864 &bull; Kementerian Agama Kota Madiun</p>
            </div>
          </div>
          <p class="text-sm text-slate-400 max-w-md leading-relaxed">
            Madrasah Ibtidaiyah Negeri unggulan berakreditasi A yang memadukan kurikulum nasional, nilai-nilai Qur'ani, program kelas unggulan &amp; reguler, serta ekosistem pembelajaran digital modern.
          </p>
        </div>

        <div>
          <h5 class="font-display font-bold text-white text-xs uppercase tracking-wider mb-4">Navigasi Portal</h5>
          <ul class="space-y-2.5 text-sm text-slate-400">
            <li><a href="#beranda" class="hover:text-emerald-400 transition-colors">Beranda Utama</a></li>
            <li><a href="#program" class="hover:text-emerald-400 transition-colors">Program Unggulan &amp; Reguler</a></li>
            <li><a href="#alur-jadwal" class="hover:text-emerald-400 transition-colors">Alur Digital &amp; Jadwal</a></li>
            <li><button type="button" onclick="openDocModal()" class="hover:text-cyan-400 transition-colors cursor-pointer">Checklist Dokumen Digital</button></li>
            <li><button type="button" onclick="openPortalModal('Jalur Unggulan / Reguler')" class="hover:text-emerald-400 transition-colors cursor-pointer">Gerbang Aplikasi PPDB</button></li>
          </ul>
        </div>

        <div>
          <h5 class="font-display font-bold text-white text-xs uppercase tracking-wider mb-4">PTSP &amp; Alamat</h5>
          <ul class="space-y-2.5 text-xs sm:text-sm text-slate-400">
            <li><strong>Kampus Utama:</strong> Jl. Sitinggil No. 3, Demangan, Kec. Taman, Kota Madiun 63136</li>
            <li><strong>Kampus 2:</strong> Jl. Kapten Tendean, Demangan, Kota Madiun</li>
            <li><strong>Telepon / WA:</strong> (0351) 462046 / 0856-4567-4020</li>
            <li class="text-emerald-300 font-semibold font-mono">Layanan PTSP: 07.00 – 15.00 WIB</li>
          </ul>
        </div>

      </div>

      <div class="pt-7 flex flex-col sm:flex-row items-center justify-between gap-4 text-xs text-slate-500">
        <p>&copy; {{ date('Y') }} Panitia PPDB MIN 1 Kota Madiun (MINSATAMA). Hak Cipta Dilindungi.</p>
        <p class="font-mono text-slate-400">PORTAL INFORMASI PPDB DIGITAL &bull; KEMENAG KOTA MADIUN</p>
      </div>
    </div>
  </footer>

  <!-- ===================================================================== -->
  <!-- MODAL 1: GERBANG PORTAL APLIKASI PPDB MINSATAMA                       -->
  <!-- ===================================================================== -->
  <div id="portalModal" class="fixed inset-0 z-50 hidden items-center justify-center p-4 bg-black/80 backdrop-blur-md">
    <div class="glass-panel w-full max-w-lg rounded-3xl border border-emerald-400/30 shadow-2xl overflow-hidden">
      <div class="px-6 py-5 border-b border-white/10 flex items-center justify-between bg-gradient-to-r from-emerald-500/10 to-cyan-500/10">
        <div class="flex items-center gap-3">
          <div class="w-9 h-9 rounded-xl bg-slate-900 border border-emerald-500/30 p-1 flex items-center justify-center shrink-0">
            <img src="{{ asset('images/logo-minsatama.png') }}" alt="Logo" class="w-full h-full object-contain" />
          </div>
          <div>
            <h3 class="font-display font-bold text-white text-base sm:text-lg">Gerbang Aplikasi PPDB MINSATAMA</h3>
            <p class="text-xs text-slate-400">Sistem Pendaftaran 100% Digital &bull; TA {{ $activePeriod->academic_year ?? '2026/2027' }}</p>
          </div>
        </div>
        <button type="button" onclick="closePortalModal()" aria-label="Tutup Modal" class="w-8 h-8 rounded-lg bg-white/5 hover:bg-white/10 text-slate-300 flex items-center justify-center font-bold cursor-pointer">
          &#10005;
        </button>
      </div>

      <div class="p-6 space-y-5">
        <div class="rounded-2xl p-4 bg-emerald-500/10 border border-emerald-400/30 space-y-2">
          <div class="flex items-center justify-between gap-2">
            <span class="text-xs font-bold uppercase tracking-wider text-emerald-300">Pilihan Program</span>
            <span id="modalSelectedProgramBadge" class="px-3 py-0.5 rounded-full text-xs font-bold bg-emerald-400 text-slate-950 font-mono">Program Unggulan / Reguler</span>
          </div>
          <p class="text-xs text-slate-300 leading-relaxed">
            Seluruh pengisian formulir, unggah dokumen persyaratan (PDF/JPG), pemantauan status verifikasi, hingga pengunduhan Kartu Peserta Tes dilakukan langsung di dalam <strong>Sistem Aplikasi PPDB MIN 1 Kota Madiun</strong> tanpa pengumpulan map fisik.
          </p>
        </div>

        <div class="space-y-2.5 text-xs text-slate-300">
          <p class="font-bold uppercase tracking-wider text-white">Persiapan Sebelum Masuk ke Aplikasi:</p>
          <div class="bg-[#060D14]/85 p-3.5 rounded-xl border border-white/10">
            <strong class="text-emerald-400">1. Akun &amp; Identitas:</strong> Siapkan NIK Calon Siswa (16 digit) dan nomor WhatsApp aktif orang tua/wali untuk verifikasi OTP.
          </div>
          <div class="bg-[#060D14]/85 p-3.5 rounded-xl border border-white/10">
            <strong class="text-cyan-400">2. Dokumen Digital:</strong> Scan/foto Akta Kelahiran, KK, Surat Keterangan Aktif TK/RA, dan Pas Foto terbaru.
          </div>
          <div class="bg-[#060D14]/85 p-3.5 rounded-xl border border-white/10">
            <strong class="text-amber-400">3. Verifikasi &amp; Helpdesk PTSP:</strong> Tim panitia memvalidasi data setiap hari kerja pukul <strong>07.00 – 15.00 WIB</strong>.
          </div>
        </div>

        <div class="pt-2 flex flex-col sm:flex-row items-center justify-end gap-3">
          <a href="{{ route('login') }}" class="w-full sm:w-auto px-5 py-3 rounded-xl text-xs sm:text-sm font-semibold text-slate-300 hover:text-white hover:bg-white/5 border border-slate-700 text-center transition">
            Sudah Punya Akun? Masuk NIK
          </a>
          <a href="{{ route('register') }}" class="w-full sm:w-auto px-6 py-3 rounded-xl text-xs sm:text-sm font-bold text-slate-950 bg-gradient-to-r from-[#00F5A0] to-[#00D9F5] hover:opacity-95 shadow-lg shadow-emerald-500/20 text-center transition">
            Daftar Akun Calon Siswa
          </a>
        </div>
      </div>
    </div>
  </div>

  <!-- ===================================================================== -->
  <!-- MODAL 2: CHECKLIST PERSYARATAN DOKUMEN DIGITAL                        -->
  <!-- ===================================================================== -->
  <div id="docModal" class="fixed inset-0 z-50 hidden items-center justify-center p-4 bg-black/80 backdrop-blur-md">
    <div class="glass-panel w-full max-w-lg rounded-3xl border border-cyan-400/30 shadow-2xl overflow-hidden">
      <div class="px-6 py-5 border-b border-white/10 flex items-center justify-between bg-gradient-to-r from-cyan-500/10 to-emerald-500/10">
        <div class="flex items-center gap-3">
          <div class="w-9 h-9 rounded-xl bg-slate-900 border border-cyan-500/30 p-1 flex items-center justify-center shrink-0">
            <img src="{{ asset('images/logo-minsatama.png') }}" alt="Logo" class="w-full h-full object-contain" />
          </div>
          <div>
            <h3 class="font-display font-bold text-white text-base sm:text-lg">Checklist Dokumen Digital</h3>
            <p class="text-xs text-slate-400">Tanpa Map Fisik &bull; Unggah Langsung di Aplikasi</p>
          </div>
        </div>
        <button type="button" onclick="closeDocModal()" aria-label="Tutup Modal" class="w-8 h-8 rounded-lg bg-white/5 hover:bg-white/10 text-slate-300 flex items-center justify-center font-bold cursor-pointer">
          &#10005;
        </button>
      </div>

      <div class="p-6 space-y-4">
        <div class="rounded-2xl bg-[#060D14]/90 border border-white/10 p-4 space-y-3 text-xs">
          <div class="flex items-center justify-between border-b border-white/10 pb-2.5">
            <span class="font-bold text-white uppercase tracking-wider">Dokumen Wajib (Unggulan &amp; Reguler)</span>
            <span class="px-2.5 py-0.5 rounded bg-cyan-500/15 text-cyan-300 font-mono">PDF / JPG / PNG</span>
          </div>
          <div class="flex items-center justify-between text-slate-200">
            <span>&#10003; Scan / Foto Akta Kelahiran Asli</span>
            <span class="text-emerald-400 font-semibold">Wajib</span>
          </div>
          <div class="flex items-center justify-between text-slate-200">
            <span>&#10003; Scan / Foto Kartu Keluarga (KK)</span>
            <span class="text-emerald-400 font-semibold">Wajib</span>
          </div>
          <div class="flex items-center justify-between text-slate-200">
            <span>&#10003; Surat Keterangan Aktif Siswa TK / RA / BA</span>
            <span class="text-emerald-400 font-semibold">Wajib</span>
          </div>
          <div class="flex items-center justify-between text-slate-200">
            <span>&#10003; File Pas Foto Berwarna Terbaru (3x4)</span>
            <span class="text-emerald-400 font-semibold">Wajib</span>
          </div>
          <div class="flex items-center justify-between text-amber-200 pt-2 border-t border-white/10">
            <span>&#9733; Scan Piagam Kejuaraan / Tahfidz</span>
            <span class="text-amber-400 font-semibold">Khusus Unggulan (Opsional)</span>
          </div>
        </div>

        <div class="rounded-2xl bg-emerald-500/10 border border-emerald-500/25 p-4 text-xs text-slate-300 leading-relaxed">
          <strong class="text-emerald-300 block mb-1">Informasi Pelayanan PTSP:</strong>
          Orang tua tidak perlu membawa map fisik ke madrasah saat mendaftar online. Kehadiran ke <strong>Kampus Utama MIN 1 Kota Madiun (Jl. Sitinggil No. 3)</strong> dilakukan saat jadwal observasi kesiapan belajar dan penyerahan bukti pendaftaran sesuai jam kerja loket PTSP (<strong>07.00 – 15.00 WIB</strong>).
        </div>

        <div class="flex items-center justify-end gap-3 pt-1">
          <button type="button" onclick="closeDocModal()" class="px-5 py-2.5 rounded-xl text-xs font-semibold text-slate-300 hover:bg-white/5 cursor-pointer">Tutup</button>
          <button type="button" onclick="closeDocModal(); openPortalModal('Jalur Unggulan / Reguler')" class="px-5 py-2.5 rounded-xl text-xs font-bold text-slate-950 bg-gradient-to-r from-[#00F5A0] to-[#00D9F5] cursor-pointer">
            Buka Gerbang Aplikasi
          </button>
        </div>
      </div>
    </div>
  </div>

  <!-- ===================================================================== -->
  <!-- TOAST NOTIFICATION                                                    -->
  <!-- ===================================================================== -->
  <div id="toastNotification" class="fixed bottom-5 right-5 z-50 hidden items-center gap-3 px-5 py-3.5 rounded-2xl glass-panel border border-emerald-400/40 shadow-2xl text-sm text-white">
    <span class="w-2.5 h-2.5 rounded-full bg-emerald-400 animate-pulse"></span>
    <span id="toastMessage">Mengarahkan ke Sistem Aplikasi PPDB Online MIN 1 Kota Madiun...</span>
  </div>

  <!-- ===================================================================== -->
  <!-- JAVASCRIPT INTERAKTIVITAS                                             -->
  <!-- ===================================================================== -->
  <script>
    // 1. Mobile Navigation Toggle
    function toggleMobileMenu() {
      const menu = document.getElementById('mobileMenu');
      if (menu) {
        menu.classList.toggle('hidden');
      }
    }

    // 2. Live Countdown Timer
    function updateCountdown() {
      const targetDate = new Date('{{ $activePeriod?->end_date?->format("Y-m-d\TH:i:sP") ?? "2027-02-08T07:00:00+07:00" }}').getTime();
      const now = new Date().getTime();
      const distance = Math.max(0, targetDate - now);

      const days = Math.floor(distance / (1000 * 60 * 60 * 24));
      const hours = Math.floor((distance % (1000 * 60 * 60 * 24)) / (1000 * 60 * 60));
      const minutes = Math.floor((distance % (1000 * 60 * 60)) / (1000 * 60));
      const seconds = Math.floor((distance % (1000 * 60)) / 1000);

      const elDays = document.getElementById('cd-days');
      const elHours = document.getElementById('cd-hours');
      const elMinutes = document.getElementById('cd-minutes');
      const elSeconds = document.getElementById('cd-seconds');

      if (elDays) elDays.textContent = String(days).padStart(2, '0');
      if (elHours) elHours.textContent = String(hours).padStart(2, '0');
      if (elMinutes) elMinutes.textContent = String(minutes).padStart(2, '0');
      if (elSeconds) elSeconds.textContent = String(seconds).padStart(2, '0');
    }
    updateCountdown();
    setInterval(updateCountdown, 1000);

    // 3. Efek Tilt 3D Interaktif pada Kartu Peserta Digital
    const studentCard = document.getElementById('studentIdCard');
    if (studentCard) {
      studentCard.addEventListener('mousemove', (e) => {
        const rect = studentCard.getBoundingClientRect();
        const x = e.clientX - rect.left;
        const y = e.clientY - rect.top;
        const centerX = rect.width / 2;
        const centerY = rect.height / 2;
        const rotateX = ((y - centerY) / centerY) * -8;
        const rotateY = ((x - centerX) / centerX) * 8;
        studentCard.style.transform = `rotateX(${rotateX}deg) rotateY(${rotateY}deg)`;
      });

      studentCard.addEventListener('mouseleave', () => {
        studentCard.style.transform = 'rotateX(0deg) rotateY(0deg)';
      });
    }

    // 4. Switcher 2 Alur Pendaftaran (Unggulan vs Reguler)
    function switchAlurTab(jalur) {
      const btnUnggulan = document.getElementById('tabBtnUnggulan');
      const btnReguler = document.getElementById('tabBtnReguler');
      const panelUnggulan = document.getElementById('alurPanelUnggulan');
      const panelReguler = document.getElementById('alurPanelReguler');

      if (!btnUnggulan || !btnReguler || !panelUnggulan || !panelReguler) return;

      if (jalur === 'unggulan') {
        btnUnggulan.className = 'px-5 sm:px-7 py-3 rounded-xl font-display font-bold text-xs sm:text-sm bg-gradient-to-r from-[#00F5A0] to-[#00D9F5] text-slate-950 shadow-lg transition-all cursor-pointer';
        btnReguler.className = 'px-5 sm:px-7 py-3 rounded-xl font-display font-bold text-xs sm:text-sm text-slate-300 hover:text-white transition-all cursor-pointer';
        panelUnggulan.classList.remove('hidden');
        panelUnggulan.classList.add('grid');
        panelReguler.classList.add('hidden');
        panelReguler.classList.remove('grid');
      } else {
        btnReguler.className = 'px-5 sm:px-7 py-3 rounded-xl font-display font-bold text-xs sm:text-sm bg-gradient-to-r from-[#00D9F5] to-blue-400 text-slate-950 shadow-lg transition-all cursor-pointer';
        btnUnggulan.className = 'px-5 sm:px-7 py-3 rounded-xl font-display font-bold text-xs sm:text-sm text-slate-300 hover:text-white transition-all cursor-pointer';
        panelReguler.classList.remove('hidden');
        panelReguler.classList.add('grid');
        panelUnggulan.classList.add('hidden');
        panelUnggulan.classList.remove('grid');
      }
    }

    // 5. FAQ Accordion Toggle
    function toggleFaq(index) {
      const body = document.getElementById(`faq-body-${index}`);
      const icon = document.getElementById(`faq-icon-${index}`);
      if (!body || !icon) return;

      const isHidden = body.classList.contains('hidden');
      if (isHidden) {
        body.classList.remove('hidden');
        icon.classList.add('rotate-180');
      } else {
        body.classList.add('hidden');
        icon.classList.remove('rotate-180');
      }
    }

    // 6. Modal Gerbang Portal Aplikasi PPDB
    const portalModal = document.getElementById('portalModal');
    function openPortalModal(programLabel) {
      const badge = document.getElementById('modalSelectedProgramBadge');
      if (badge && programLabel) {
        badge.textContent = programLabel;
      }
      if (portalModal) {
        portalModal.classList.remove('hidden');
        portalModal.classList.add('flex');
      }
    }

    function closePortalModal() {
      if (portalModal) {
        portalModal.classList.add('hidden');
        portalModal.classList.remove('flex');
      }
    }

    // 7. Modal Checklist Dokumen Digital
    const docModal = document.getElementById('docModal');
    function openDocModal() {
      if (docModal) {
        docModal.classList.remove('hidden');
        docModal.classList.add('flex');
      }
    }

    function closeDocModal() {
      if (docModal) {
        docModal.classList.add('hidden');
        docModal.classList.remove('flex');
      }
    }

    // Tutup modal jika klik backdrop luar
    window.addEventListener('click', (e) => {
      if (e.target === portalModal) closePortalModal();
      if (e.target === docModal) closeDocModal();
    });
  </script>
</body>
</html>
