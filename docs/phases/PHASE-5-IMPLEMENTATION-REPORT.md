# LAPORAN IMPLEMENTASI FASE 5 — MANAJEMEN DOKUMEN

**Status**: Selesai; menunggu review dan persetujuan pemilik sebelum Fase 6
**Tanggal verifikasi**: 18 November 2026

## 1. Lingkup yang Diselesaikan

- Disk privat `ppdb_private` di `storage/app/private` dengan base path `ppdb/documents`; tidak menyediakan URL publik untuk dokumen.
- Retrieval persyaratan hanya untuk periode aktif dan program pendaftar, serta `DocumentCompletenessService` untuk dokumen wajib.
- Upload dan replacement melalui action terpisah; validasi server-side untuk ukuran, ekstensi, MIME Fileinfo, dan SHA-256.
- Kebijakan ukuran: maksimum global/default 5120 KB; persyaratan dapat menetapkan nilai lebih kecil. Limit efektif adalah nilai terendah antara global dan persyaratan. Model menolak konfigurasi di atas 5120 KB.
- Replacement menyimpan metadata berkas lama sebagai revisi tanpa menghapus file historis. Jika persistensi database gagal, file baru dibersihkan; kegagalan cleanup dilaporkan eksplisit.
- Preview, download, dan akses revisi dibatasi ke applicant pemilik. Antarmuka pengelolaan dokumen tersedia dan progress dokumen terintegrasi pada dashboard.
- Tidak ada submit final, nomor registrasi, PDF, seleksi, persetujuan dokumen, atau pelaporan administrator.

## 2. Migration dan Data Lokal

- Migration awal `document_requirements` telah diterapkan pada SQLite persisten; default baru dibuat melalui migration tambahan `2026_11_18_120000_update_document_requirement_default_size.php`.
- Migration additive telah dijalankan pada SQLite lokal. Verifikasi `PRAGMA table_info` menunjukkan default `max_size_kb` adalah `5120`.
- Migration hanya mengubah default kolom dan tidak memperbarui nilai baris yang sudah ada, sehingga batas per dokumen yang mungkin memang sengaja lebih kecil tetap dipertahankan.
- Pemeriksaan agregat lokal menunjukkan 0 applicant, 0 data NIK terenkripsi, 0 registration, dan 0 dokumen pendaftaran. Tidak ada data applicant yang diubah.

## 3. Konfigurasi Secret

- `APP_KEY` tersedia dan tidak diubah. Setelah pemeriksaan data lokal, `NIK_HMAC_KEY` dan `OTP_HMAC_KEY` independen dibuat dengan CSPRNG dan disimpan hanya pada `.env` lokal yang di-ignore Git.
- Runtime mendeteksi kedua HMAC key dan memastikan keduanya berbeda satu sama lain serta dari `APP_KEY`. Nilai key tidak ditampilkan atau dicatat.
- `.env.example` tetap hanya memuat placeholder kosong dan instruksi konfigurasi. Operasi NIK/OTP tetap gagal tertutup bila key hilang atau tidak valid.

## 4. Hasil Pengujian dan Audit

- `php artisan test`: **143 tes**, **141 passed**, **0 failed**, **2 skipped**, **494 assertions**. Dua skip adalah pengujian kompatibilitas MySQL karena server fisik belum tersedia.
- `php artisan test --filter=DocumentManagementTest`: **12 passed**, **54 assertions**.
- Cakupan dokumen menguji file di bawah, tepat pada, dan di atas 5120 KB; batas lebih rendah per dokumen; konfigurasi di atas maksimum; ekstensi/MIME spoofing; guest dan cross-applicant IDOR; history replacement; file lama tetap tersedia; escaping konten riwayat; cleanup setelah kegagalan database; tampilan persyaratan dan progress dashboard.
- `vendor/bin/pint --test`: **PASS**.
- `npm run build`: **PASS**, 3 modules transformed; CSS 85.02 kB dan JS 1.72 kB.
- `composer audit`: **0 advisories**.
- `npm audit --json`: **6 high** temuan pada dependency build/development terkait `GHSA-fc5q-39xc-vjpf` / `CVE-2026-112366`; advisory belum menetapkan versi patched yang kompatibel. `npm audit --omit=dev`: **0 temuan**. Dependency dan lockfile tidak diubah untuk menekan temuan; `npm audit fix --force` tidak dijalankan.

## 5. Batas Operasional yang Belum Terverifikasi

- PHP lokal saat verifikasi memiliki `upload_max_filesize=2M` dan `post_max_size=8M`. Sebelum unggahan maksimum digunakan melalui HTTP, tetapkan `upload_max_filesize=5M`, `post_max_size` lebih besar dari 5M, dan request limit web server yang mencakup overhead multipart. Pengujian file tepat 5120 KB adalah feature test sintetis Laravel dan tidak menggantikan uji upload live.
- MySQL fisik belum tersedia; hasil kompatibilitas MySQL belum dapat diklaim lulus.
- Advisory dependency NPM build tetap terbuka; hindari glob yang dikendalikan pengguna dan evaluasi ulang setelah ada patch upstream.

## 6. Keputusan Penghentian

Fase 5 selesai sesuai batas yang disetujui. Perubahan project status, roadmap, strategi keamanan, audit, dan test report telah diperbarui. Pekerjaan berhenti di Fase 5 sambil menunggu persetujuan pemilik sebelum Fase 6.
