# LAPORAN IMPLEMENTASI FASE 6 — ALUR PENDAFTARAN

**Tanggal**: 18 November 2026
**Status**: Implementasi diaudit; penutupan Fase 6 BLOCKED
**Basis pengujian**: SQLite in-memory
**Cakupan**: Validasi kesiapan, tinjauan, persetujuan, submit final, nomor registrasi, serta audit perubahan pasca-submit.

## 1. Ringkasan

Fase 6 mengimplementasikan transisi pendaftaran `DRAFT` menjadi `SUBMITTED`. Pendaftar dapat meninjau ringkasan data dan kelengkapan, menyetujui pernyataan, lalu mengirim pendaftaran. Server memvalidasi ulang seluruh syarat dalam transaksi, menerbitkan nomor unik, mencatat persetujuan dan audit, serta menjaga operasi submit ulang tetap idempoten.

Perubahan biodata, alamat, orang tua/wali, pendidikan, pilihan program, dan dokumen setelah submit hanya diterima ketika periode masih dibuka. Perubahan yang benar-benar mengubah data menaikkan `data_revision` dan menyimpan nama field yang berubah tanpa mencatat nilai pribadi.

## 2. Implementasi

- Menambahkan `RegistrationReadinessService` untuk memeriksa biodata/NIK, alamat, ayah, ibu, pendidikan, periode/program, dokumen wajib, dan persetujuan.
- Melengkapi validasi formulir serta readiness ayah/ibu agar NIK, status, pendidikan, pekerjaan, penghasilan, dan telepon sesuai field wajib pada Business Requirements.
- Menambahkan halaman tinjauan pendaftar dengan NIK siswa/orang tua termasker, ringkasan aman dokumen, daftar bagian yang belum lengkap, dan kontrol submit.
- Menambahkan `SubmitRegistrationRequest`, `RegistrationSubmissionController`, dan `SubmitRegistrationAction`. Submit melakukan validasi server-side, penguncian row, pencatatan consent, perubahan status, nomor, timestamp, dan `registration_logs` dalam transaksi.
- Menambahkan `RegistrationNumberGeneratorService` dengan row sequence terkunci per `admission_period_program_id`; nomor hanya diterbitkan ketika submit berhasil. Unique constraint pendaftaran tetap menjadi perlindungan tambahan.
- Menambahkan `RegistrationEditService` untuk menegakkan periode terbuka, transaksi perubahan, revision counter, dan audit field-level tanpa nilai pribadi. Penggantian dokumen tetap mempertahankan file historis dan membersihkan file baru jika transaksi luar gagal.
- Menambahkan integrasi status/tautan tinjauan di dashboard dan navigasi formulir.
- Menambahkan dua migration additive: tabel `registration_sequences`, serta metadata versi dan nama pemberi persetujuan pada `registration_consents`. Tidak ada migration destruktif.
- Memperbaiki kelengkapan dokumen untuk konfigurasi yang hanya memiliki persyaratan opsional: kondisi ini tidak lagi dianggap sebagai konfigurasi persyaratan kosong.

## 3. Asumsi yang Belum Disahkan Sekolah

Dokumen yang tersedia belum menetapkan satu format nomor dan redaksi consent final. Implementasi sementara menggunakan:

- Format nomor `PPDB/{tahun-ajaran}/{kode-program}/{urutan-5-digit}`, misalnya `PPDB/2026-2027/REGULER/00001`.
- Sequence terpisah untuk setiap pasangan periode-program.
- Redaksi yang sudah ada pada fixture tes, dengan versi sementara `v1.0`.

Pilihan ini bukan keputusan bisnis resmi. `.env.example` menyediakan placeholder kosong. Pada `APP_ENV=production`, konfigurasi consent yang tidak diisi menyebabkan submit gagal tertutup. Format, lingkup sequence, redaksi, dan versi consent harus disahkan pemilik sekolah sebelum penerbitan nomor produksi.

## 4. Batas Cakupan

Fase ini tidak membuat PDF draft/final, tidak melakukan seleksi atau keputusan penerimaan, tidak memberi persetujuan dokumen, dan tidak menambahkan pelaporan administrator. Pembuatan PDF tetap menunggu Fase 7 serta otorisasi pemilik.

## 5. Hasil Verifikasi

- `php artisan test --compact`: **161 total; 159 passed, 0 failed, 2 skipped; 618 assertions**.
- `php artisan test --compact tests/Feature/Applicant/RegistrationWorkflowTest.php`: **18 passed; 114 assertions**.
- Dua tes yang dilewati adalah tes kompatibilitas MySQL karena server lokal tidak tersedia. Perilaku row-lock/konkurensi InnoDB belum terverifikasi secara fisik.
- `vendor/bin/pint --dirty --format agent`: lulus.
- `npm.cmd run build`: lulus; Vite 8.3.8, 3 modul ditransformasi.
- `composer audit`: tidak ada advisories.
- `npm.cmd audit --omit=dev`: 0 vulnerabilities. `npm.cmd audit --json`: 6 temuan high pada dependency build/development melalui advisory `GHSA-fc5q-39xc-vjpf` / `CVE-2026-112366`; tidak ada dependency yang diubah dan temuan belum dinyatakan diperbaiki.
- Tidak ada migration yang dijalankan pada MySQL atau database persisten.

## 6. Isu dan Langkah Berikutnya

1. Pemilik sekolah mengesahkan format/lingkup nomor dan teks/versi consent sebelum produksi.
2. Tentukan kebijakan admin reopen; permission yang ada belum terhubung ke workflow dan cakupan/durasi belum ditetapkan.
3. Jalankan verifikasi migration dan konkurensi pada MySQL/InnoDB ketika server tersedia; hasil SQLite tidak menggantikannya.
4. Pertahankan pemantauan enam temuan NPM high dan evaluasi ulang setelah patch kompatibel tersedia.
5. Konfigurasi server unggahan 5 MB yang dicatat pada laporan Fase 5 tetap harus diterapkan sebelum menerima file maksimum.
6. Jangan memulai Fase 7 sebelum semua gerbang ini lulus serta pemilik memberi otorisasi tertulis.
