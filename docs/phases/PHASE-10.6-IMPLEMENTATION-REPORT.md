# PHASE 10.6 — SINGLE ADMIN WORKSPACE

**Status:** IMPLEMENTED LOCALLY — PENDING OWNER REVIEW
**Tanggal:** 22 November 2026
**Scope:** dashboard, shell, navigasi, query agregasi, dan regression tests. Tidak mencakup modul administrasi gap.

## Implementasi

- `AdminDashboardController` menyediakan satu dashboard `/admin/dashboard` untuk role `superadmin` dan `admin_ppdb`.
- Superadmin dan Admin PPDB menggunakan layout, header, sidebar, dan dashboard operasional yang sama; perbedaan akses ditegakkan oleh permission server-side.
- Dashboard tetap period-scoped dan memperoleh tren submission tujuh hari dari `registrations` aktual.
- Login Superadmin dan Admin PPDB sama-sama diarahkan ke `admin.dashboard`; tidak ada workspace switcher atau route dashboard Superadmin terpisah.
- `manage_system_settings` dan permission lain tetap ditegakkan di server.
- Placeholder Verifikasi Berkas, Periode, Program, Laporan & Ekspor, dan Manajemen Pengguna tetap disabled/unavailable.

## Keamanan dan batasan

Tidak ada guard baru, role baru, migration, perubahan status pendaftaran, data dummy produksi, atau infrastructure health palsu. NIK tidak dipakai dalam widget dashboard. Log registrasi dan log pengaturan ditampilkan sebagai sumber audit yang berbeda.

## Pengujian terarah

`tests/Feature/Admin/AdminDashboardTest.php` mencakup akses kedua role ke dashboard yang sama, ketiadaan route Superadmin terpisah, tren nyata, empty state, permission denial, applicant isolation, dan NIK non-leakage. Suite dashboard/login/authorization terarah lulus **42 passed, 155 assertions**.

## Quality gates

- `php artisan test`: **281 passed, 11 skipped, 0 failed, 1,284 assertions**.
- `vendor/bin/pint --test`: lulus.
- `npm run build`: lulus (`vite v8.3.8`).
- `composer audit --no-interaction`: tidak ada advisory.
- `npm audit --omit=dev`: 0 vulnerabilities.
- `git diff --check`: lulus.

## Belum dilakukan

Live acceptance pada Hostinger masih menunggu persetujuan/akun QA terpisah. Modul Communication Center, CRUD konfigurasi, user/permission management, reports, dan health monitoring tetap berada pada tahap roadmap berikutnya. Tidak ada commit, push, deployment, migrasi database, atau akun QA pada laporan ini.
