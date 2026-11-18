# Laporan Audit Penutupan Fase 6 — Alur Pendaftaran

**Tanggal audit**: 18 November 2026
**Commit pada awal penutupan**: `fb5924c`
**Branch**: `main` (pada awal audit sama dengan `origin/main`)
**Status akhir**: **FASE 6 CLOSED**
**Keputusan fase berikutnya**: **Fase 7 belum dimulai; menunggu otorisasi tertulis pemilik**

## 1. Ringkasan Eksekutif

Keputusan resmi tentang format nomor, consent, dan admin reopen telah diterapkan. Migrasi, backfill counter, dan submit konkuren diverifikasi pada MariaDB 10.4.32/InnoDB melalui database uji khusus. Full regression lulus dengan 179 tes dan 823 asersi. Fase 6 ditutup; Fase 7 tidak dimulai dan tetap memerlukan otorisasi tertulis pemilik.

MySQL 8.x target produksi belum diuji terpisah, dan konfigurasi PHP lokal untuk upload live 5 MB masih perlu diperbarui.

## 2. Status Gate Penutupan

| Gate | Status | Bukti / temuan |
|---|---|---|
| Format nomor registrasi | **PASS** | Format `PPDB/{tahun-ajaran}/{kode-program}/{urutan-5-digit}` dan kode `REGULER`/`UNGGULAN` disahkan dan diterapkan. |
| Teks dan versi consent | **PASS** | Redaksi resmi versi `v1.0` disimpan bersama pendaftaran; konfigurasi production kosong tetap fail-closed. |
| Migrasi pada MariaDB/InnoDB | **PASS** | Migrasi lengkap dan skema terverifikasi pada database khusus `ppdb_phase6_test`. Default `max_size_kb=5120` ikut diperiksa. |
| Submit bersamaan / sequence concurrency | **PASS** | Multi-process tests lulus untuk pendaftaran sama, pendaftar berbeda, lintas periode tahun-program sama, serta rollback/retry. |
| Kebijakan dan fitur admin reopen | **PASS** | Akses per pendaftaran dengan alasan/expiry, penutupan dini, penegakan tanpa cron, permission dan audit diuji. |
| Konsistensi laporan dan angka tes | **PASS** | Laporan diperbarui berdasarkan hasil final: 179 tes lulus, 0 gagal/skip, 823 asersi. |
| Kesiapan Fase 7 | **MENUNGGU OTORISASI** | Fase 6 closed, tetapi implementasi PDF Fase 7 tidak dimulai tanpa izin tertulis pemilik. |

## 3. Tinjauan Teknis Implementasi

- Submit mengunci periode, pendaftaran, program, dan row counter global dalam transaksi, mencatat consent dan audit, mengubah status serta menerbitkan nomor.
- Sequence dibagikan per tahun ajaran-program melalui unique key. Migration additive mem-backfill nilai tertinggi dari counter per-periode lama; locking pada row counter mencegah duplikasi saat periode berbeda diproses bersamaan.
- Reopen berlaku untuk satu pendaftaran submitted dengan reason dan expiry wajib, dapat ditutup dini, ditegakkan server-side, dan seluruh perubahan dicatat dalam `registration_logs`.
- Uji migrasi menemukan nama indeks komposit yang melampaui batas MariaDB. Nama indeks diperpendek dan migration dapat melanjutkan skema parsial secara idempotent tanpa menghapus tabel atau data.
- Belum ada PDF draft/final pada Fase 6; dokumen PDF tetap di luar cakupan audit dan tidak diimplementasikan.

### Keputusan pemilik yang diterapkan

1. Format resmi `PPDB/{tahun-ajaran}/{kode-program}/{urutan-5-digit}`, kode `REGULER` dan `UNGGULAN`.
2. Consent resmi versi `v1.0`: “Saya selaku orang tua/wali menyatakan bahwa seluruh data yang diisikan adalah benar dan dapat dipertanggungjawabkan.”
3. Admin PPDB atau superadmin dengan permission dapat reopen satu registration submitted dalam window wajib expiry dan reason; dapat menutup dini dengan reason.
4. Sequence berlaku global per tahun ajaran-program untuk menjaga keunikan format yang tidak memuat ID periode.

## 4. Hasil Verifikasi Aktual

### Automated tests

- Full regression `php artisan test --compact`: **179 tests; 179 passed, 0 failed, 0 skipped; 823 assertions**.
- Workflow `php artisan test --compact tests/Feature/Applicant/RegistrationWorkflowTest.php`: **21 passed; 128 assertions**.
- Tes integrasi fisik MySQL/MariaDB: **9 passed**, dijalankan pada MariaDB 10.4.32/InnoDB dan database khusus `ppdb_phase6_test`. Cakupan termasuk migrasi/backfill, indeks/FK, default 5120 KB, submit multi-process, lintas periode, rollback, dan retry.
- Database uji aplikasi tidak digunakan; tidak ada migration destruktif.

### Kualitas dan dependency

- `vendor/bin/pint --dirty --format agent`: **PASS**.
- `npm.cmd run build`: **PASS**; Vite 8.3.8, 3 modules, CSS 86.56 kB, JS 1.72 kB.
- `composer audit`: **0 advisories**.
- `npm.cmd audit --omit=dev`: **0 vulnerabilities**.
- `npm.cmd audit --json`: **6 temuan high** pada dependency build/development terkait `GHSA-fc5q-39xc-vjpf` / `CVE-2026-112366`. Advisory belum menetapkan versi patch; tidak ada dependency/lockfile yang diubah. Jangan mengklaim temuan telah diperbaiki.

## 5. Status Git dan Riwayat

- Pada awal penyelesaian, worktree bersih dan branch aktif `main`; commit terakhir `fb5924c`. Remote `origin` mengarah ke `AhmadDFN/PPDB_Minsa_2026`.
- Riwayat lokal yang tersedia memuat `e2a8bb3` → `4e70b5d` → `d970b33` → `311e099` → `fb5924c`.
- Referensi historis `4c64724` yang tercatat pada dokumentasi lama tidak ditemukan pada riwayat branch saat audit. Bukti yang tersedia tidak cukup untuk menyimpulkan apakah penyebabnya squash, migrasi repository, atau hal lain.
- Tidak ada rewrite history, force push, atau penghapusan perubahan.

## 6. Risiko yang Tetap Terbuka

1. Enam temuan NPM high pada dependency build/development masih terbuka menunggu patch yang kompatibel. Hindari memproses pola glob tak tepercaya dalam build.
2. Konfigurasi PHP lokal masih `upload_max_filesize=2M`, `post_max_size=8M`; upload live 5 MB memerlukan `upload_max_filesize=5M`, `post_max_size` lebih besar dari 5M, dan request limit web server yang mencakup multipart overhead.
3. MariaDB 10.4.32/InnoDB lulus; MySQL 8.x target produksi belum diuji terpisah.
4. Fase 7 belum dimulai dan tetap memerlukan otorisasi tertulis pemilik.

## 7. Rekomendasi dan Stop Condition

**Fase 6 CLOSED.** Seluruh gate yang disepakati telah lulus dan laporan diperbarui. Tetap jangan memulai Fase 7 tanpa otorisasi tertulis pemilik.

Pada batas fase, berhenti setelah penyelesaian Fase 6. Jangan membuat fitur PDF, migration, dependency, atau implementasi Fase 7 sampai pemilik memberi persetujuan eksplisit.
