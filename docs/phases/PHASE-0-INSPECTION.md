# PHASE 0 — INSPEKSI LINGKUNGAN & PERENCANAAN FONDASI
## Laporan Hasil Inspeksi Awal Repositori & Alat Pengembangan

**Status**: SELESAI & DISETUJUI  
**Tanggal**: 15 November 2026

---

## 1. HASIL PEMERIKSAAN PERANGKAT LUNAK

| Komponen | Versi Terdeteksi | Status Kelayakan |
|---|---|---|
| **PHP** | 8.3.31 (ZTS x64) | ✅ Memenuhi syarat minimal Laravel 13 (>= 8.3) |
| **Composer** | 2.7.1 | ✅ Siap pakai |
| **Node.js** | v24.18.0 (LTS) | ✅ Siap pakai |
| **NPM** | 9.3.0 | ✅ Siap pakai |
| **Sistem Operasi** | Windows 10/11 x64 | ✅ Lingkungan kerja terkonfirmasi |
| **Sistem Kontrol Versi** | Git 2.45+ | ✅ Branch `main` aktif |

---

## 2. STATUS EKSTENSI PHP

- `pdo_sqlite`: Aktif (digunakan untuk continuous automated test suite).
- `pdo_mysql`: Aktif di `php.ini` (digunakan untuk deployment MySQL produksi).
- `openssl`, `mbstring`, `intl`, `gd`, `curl`, `fileinfo`, `bcmath`, `zip`: Seluruhnya aktif.

---

## 3. KEPUTUSAN AWAL FONDASI

1. Scaffold proyek menggunakan Laravel 13 framework terbaru (`v13.42.0`).
2. Menetapkan arsitektur CSS menggunakan Tailwind CSS v4 dengan plugin `@tailwindcss/vite`.
3. Memasang paket otorisasi standar industri `spatie/laravel-permission` versi 8.5+.
4. Mengadopsi palet warna identitas madrasah: Minsatama Green (`#0B6E3F`), Primary Cyan-Blue (`#0F7FA3`), Gold Accent (`#F2C94C`).
