# LAPORAN IMPLEMENTASI FASE 6 — ALUR PENDAFTARAN

**Tanggal**: 18 November 2026
**Status**: Fase 6 CLOSED
**Basis pengujian**: SQLite in-memory dan MariaDB 10.4.32/InnoDB pada database khusus
**Cakupan**: Validasi kesiapan, tinjauan, persetujuan, submit final, nomor registrasi, audit perubahan, dan admin reopen.

## 1. Ringkasan

Fase 6 mengimplementasikan transisi pendaftaran `DRAFT` menjadi `SUBMITTED`. Pendaftar dapat meninjau ringkasan data dan kelengkapan, menyetujui pernyataan, lalu mengirim pendaftaran. Server memvalidasi ulang seluruh syarat dalam transaksi, menerbitkan nomor unik, mencatat persetujuan dan audit, serta menjaga operasi submit ulang tetap idempoten.

Perubahan biodata, alamat, orang tua/wali, pendidikan, pilihan program, dan dokumen setelah submit hanya diterima ketika periode masih dibuka. Perubahan yang benar-benar mengubah data menaikkan `data_revision` dan menyimpan nama field yang berubah tanpa mencatat nilai pribadi.

## 2. Implementasi

- Menambahkan `RegistrationReadinessService` untuk memeriksa biodata/NIK, alamat, ayah, ibu, pendidikan, periode/program, dokumen wajib, dan persetujuan.
- Melengkapi validasi formulir serta readiness ayah/ibu agar NIK, status, pendidikan, pekerjaan, penghasilan, dan telepon sesuai field wajib pada Business Requirements.
- Menambahkan halaman tinjauan pendaftar dengan NIK siswa/orang tua termasker, ringkasan aman dokumen, daftar bagian yang belum lengkap, dan kontrol submit.
- Menambahkan `SubmitRegistrationRequest`, `RegistrationSubmissionController`, dan `SubmitRegistrationAction`. Submit melakukan validasi server-side, penguncian row, pencatatan consent, perubahan status, nomor, timestamp, dan `registration_logs` dalam transaksi.
- Menambahkan `RegistrationNumberGeneratorService` dengan satu row counter terkunci per tahun ajaran-program; nomor hanya diterbitkan ketika submit berhasil. Unique constraint pendaftaran tetap menjadi perlindungan tambahan.
- Menjamin sequence nomor unik lintas periode untuk pasangan tahun ajaran-program yang sama melalui unique key dan `lockForUpdate()` pada row counter global.
- Menambahkan `RegistrationEditService` untuk menegakkan periode terbuka, transaksi perubahan, revision counter, dan audit field-level tanpa nilai pribadi. Penggantian dokumen tetap mempertahankan file historis dan membersihkan file baru jika transaksi luar gagal.
- Menambahkan integrasi status/tautan tinjauan di dashboard dan navigasi formulir.
- Menambahkan migration additive untuk counter global `registration_number_sequences` dengan backfill nilai tertinggi dari `registration_sequences` lama, metadata consent, window reopen, dan default `document_requirements.max_size_kb=5120`. Tabel counter lama dipertahankan. Migration indeks dokumen memakai nama pendek yang kompatibel dengan MariaDB dan dapat melanjutkan pemulihan skema parsial tanpa menghapus tabel/data.
- Memperbaiki kelengkapan dokumen untuk konfigurasi yang hanya memiliki persyaratan opsional: kondisi ini tidak lagi dianggap sebagai konfigurasi persyaratan kosong.
- Menambahkan window reopen per pendaftaran dengan aktor, alasan, waktu buka/kedaluwarsa, penutupan dini, validasi server-side pada setiap operasi, dan pencatatan audit.

## 3. Keputusan Bisnis yang Diterapkan

- Format nomor resmi `PPDB/{tahun-ajaran}/{kode-program}/{urutan-5-digit}` dengan kode `REGULER`/`UNGGULAN`.
- Sequence global per tahun ajaran-program, termasuk di antara beberapa periode dalam tahun yang sama, agar format resmi tidak menghasilkan nomor duplikat.
- Consent resmi versi `v1.0`: “Saya selaku orang tua/wali menyatakan bahwa seluruh data yang diisikan adalah benar dan dapat dipertanggungjawabkan.”
- Admin PPDB atau superadmin dengan permission dapat reopen satu pendaftaran `SUBMITTED` pada periode tertutup dengan alasan dan expiry wajib, lalu menutupnya lebih awal dengan alasan.
- Konfigurasi consent production kosong tetap gagal tertutup.

## 4. Batas Cakupan

Fase ini tidak membuat PDF draft/final, tidak melakukan seleksi atau keputusan penerimaan, tidak memberi persetujuan dokumen, dan tidak menambahkan pelaporan administrator. Pembuatan PDF tetap menunggu Fase 7 serta otorisasi pemilik.

## 5. Hasil Verifikasi

- `php artisan test --compact`: **179 passed; 0 failed, 0 skipped; 823 assertions**.
- `php artisan test --compact tests/Feature/Applicant/RegistrationWorkflowTest.php`: **21 passed; 128 assertions**.
- Sembilan tes fisik MariaDB/InnoDB lulus, mencakup migration/backfill dan skema, default `max_size_kb=5120`, submit konkuren pada registration sama dan lintas periode, rollback, dan retry. Database yang digunakan adalah `ppdb_phase6_test`, bukan database aplikasi.
- `vendor/bin/pint --dirty --format agent`: lulus.
- `npm.cmd run build`: lulus; Vite 8.3.8, 3 modul ditransformasi; CSS 86.56 kB, JS 1.72 kB.
- `composer audit`: tidak ada advisories.
- `npm.cmd audit --omit=dev`: 0 vulnerabilities. `npm.cmd audit --json`: 6 temuan high pada dependency build/development melalui advisory `GHSA-fc5q-39xc-vjpf` / `CVE-2026-112366`; tidak ada dependency yang diubah dan temuan belum dinyatakan diperbaiki.
- Migration MySQL dijalankan hanya pada database uji khusus. MariaDB lulus; MySQL 8.x target produksi belum diuji terpisah.

## 6. Isu Operasional dan Batas Cakupan

1. Enam temuan NPM high pada dependency build/development tetap menunggu patch kompatibel; tidak diklaim telah diperbaiki.
2. PHP lokal masih `upload_max_filesize=2M`, `post_max_size=8M`; upload live 5 MB memerlukan konfigurasi PHP dan request limit web server.
3. Uji fisik terverifikasi pada MariaDB 10.4.32, bukan MySQL 8.x target produksi.
4. Fase 6 selesai. Jangan memulai PDF, seleksi, approval dokumen, pelaporan admin, atau pekerjaan Fase 7 tanpa otorisasi tertulis pemilik.
