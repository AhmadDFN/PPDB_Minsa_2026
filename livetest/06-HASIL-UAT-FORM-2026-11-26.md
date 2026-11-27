# Hasil UAT Revisi Form Calon Siswa — 26 November 2026

**Lingkungan:** lokal (`127.0.0.1:8000`, SQLite, akun dummy)
**Tujuan:** memeriksa revisi form, submit, verifikasi staf, PDF final, akses dokumen lintas akun, dan tampilan mobile/tablet.

## Hasil per skenario

| Area | Hasil | Bukti / batasan |
|---|---|---|
| Login pendaftar dummy | PASS | Akun uji lokal berhasil masuk. |
| Simpan form biodata, alamat, orang tua, pendidikan, dan program | PASS | Form lengkap disimpan dan dipakai pada alur UAT. Jarak menggunakan nilai manual 2,3 km. |
| Dokumen wajib dan submit final | PASS | Lima dokumen dummy terpenuhi; registration terkirim dan dashboard menampilkan status resmi. Berkas KK/akta/surat/NISN memakai logo dummy, sehingga validasi isi dokumen tidak diuji. |
| Verifikasi dokumen oleh admin dummy | PASS | Semua dokumen wajib disahkan dan nomor pendaftaran diterbitkan. |
| Generate dan unduh PDF final | PASS | PDF final versi 1 berhasil dibuat setelah foto PNG diganti JPG. PNG awal memunculkan pesan bahwa ekstensi GD belum tersedia. |
| Foto pas foto | PARTIAL | JPG biru berlabel “DUMMY UAT PHOTO” hanya menguji rendering PDF; bukan foto siswa dan bukan validasi latar biru. |
| QR | PARTIAL | `RegistrationPdfVerificationTest`: 4 test/13 assertion lulus. PDF mempunyai QR final; pemindaian QR aktual dengan kamera/decoder belum dilakukan. |
| IDOR dokumen lintas akun | PASS | Sesi akun pendaftar kedua meminta `/pendaftar/dokumen/3/download` milik akun pertama; hasil browser 404 Not Found. |
| Responsive 390 × 844 | PASS setelah perbaikan | Halaman biodata, alamat, orang tua, pendidikan, dokumen, dan tinjau: `documentElement.scrollWidth` 375 px, viewport 390 px. |
| Responsive 768 × 1024 | PASS setelah perbaikan | Halaman biodata: `documentElement.scrollWidth` 753 px, viewport 768 px. Sebelumnya indikator langkah desktop menyebabkan overflow; kini indikator dapat digulir di dalam komponennya. |
| Cascade wilayah lokal | PASS | Browser memilih Jawa Timur → Kota Madiun → Taman → Demangan. API menyajikan turunan dari dataset bundled; kode tersimpan dan tervalidasi berjenjang. |
| Salin alamat siswa ke ayah | PASS | Tombol mengisi Provinsi/Kota/Kecamatan/Desa yang sama dengan alamat siswa. Tidak men-submit perubahan data pada recheck. |
| Responsive 360 × 800 | PASS | Biodata, alamat, orang tua, pendidikan, dokumen, dan tinjau: lebar dokumen 345 px, viewport 360 px; tanpa horizontal overflow. |
| No. Handphone Calon Siswa | PASS | Field terlihat di biodata mobile; penyimpanan/validasi diuji di feature suite. Tetap terpisah dari WhatsApp akun penerima OTP. |
| Checkbox NISN belum tersedia | PASS | Browser membuktikan checkbox menonaktifkan input NISN dan menghilangkan required pada viewport 360 px; automated test membuktikan NISN lama dihapus saat dicentang. |
| Pin offline dan kalkulasi jarak | PASS di component harness; alur penuh PARTIAL | Harness browser memakai markup dan skrip widget yang sama: tap menetapkan pin, drag dan panah keyboard menggesernya, titik Kampus 1 = 0,00 km, koordinat di luar cakupan tetap dihitung, fallback manual berfungsi. Viewport 360×800 dan 390×844 tanpa overflow, console error kosong. Feature tests memastikan pin tanpa longitude ditolak dan koordinat tidak disimpan. Harness bukan form pendaftar terautentikasi; GPS permission prompt tidak diuji. |
| IDOR PDF lintas akun | PASS | Akun dummy kedua meminta PDF versi milik akun pertama (`/pendaftar/pendaftaran/pdf/2/unduh`); halaman merespons 404. |
| OTP/WhatsApp fisik | SKIPPED | Skenario memakai akun dummy; tidak ada WhatsApp atau OTP fisik dikirim. |
| Staging/production | SKIPPED | Tidak ada deployment atau tes lingkungan live. |

## Test otomatis dan quality gates

- `php artisan test`: **PASS**, 388 tests; 373 passed, 15 skipped, 1.781 assertions.
- Peta OSM tersedia lokal dengan atribusi ODbL; map JavaScript lolos `node --check`, data memuat 2.805 fitur, dan widget lulus browser harness desktop/mobile 360/390 px.
- Test PDF final memastikan status berkas yang diunggah tampil dan NIK orang tua/wali tetap tersamar tanpa nilai utuh pada payload.
- `php artisan test --filter=RegistrationPdfVerificationTest`: **PASS**, 4 tests, 13 assertions.
- `vendor/bin/pint --test`: **PASS**.
- `npm.cmd run build`: **PASS**.
- `npm.cmd audit`: **PASS**, 0 vulnerabilities.
- `composer audit`: **PASS** saat retry (tidak ada advisory; percobaan pertama sementara gagal dengan Packagist HTTP/2 502).
- `php artisan route:list --path=pendaftar`, `php artisan view:cache`, `php artisan view:clear`, dan `git diff --check`: **PASS**.
- Migrasi additive dan migration backfill NIK lama berhasil dijalankan pada database SQLite lokal. Query agregat setelah backfill: 0 baris guardian menyimpan NIK plaintext; 6 mempunyai ciphertext dan lookup hash. Dataset wilayah lokal berhasil di-seed: 91.599 baris. MySQL/MariaDB belum diuji.

## Kegagalan yang ditemukan dan tindak lanjut

1. PNG sebagai foto menyebabkan PDF final gagal saat PHP lokal tidak memiliki GD. UAT dilanjutkan dengan JPG. Tindak lanjutnya tercatat di Phase 11.
2. Test reopen gagal karena fixture belum menyediakan field baru dan profil historis lengkap. Fixture diperbarui, lalu suite tersebut lulus.
3. Full suite sempat menemukan ekspektasi test lama yang meminta NIK utuh pada PDF final dan snapshot benchmark yang belum mempunyai field baru. Assertion kini memverifikasi masking, PDF menangani nilai historis yang kosong; full suite lulus.
4. Browser menemukan overflow horizontal 316 px pada indikator langkah viewport tablet. Komponen diperbaiki dan pengukuran 390/768 px kembali tanpa overflow halaman.

## Saran / pekerjaan pemilik

1. Uji map langsung di form pendaftar dengan akun dummy, termasuk izin lokasi ditolak, serta tampilan titik luar cakupan. Harness komponen telah diuji pada 360/390 px; koordinat rumah tidak dikirim ke provider (ISS-004/008).
2. Scan PDF final menggunakan kamera ponsel, pastikan status/nomor yang ditampilkan sesuai, dan pastikan halaman verifikasi publik tidak membocorkan PII (ISS-006).
3. Uji unggah pas foto calon siswa yang benar dengan latar biru dan dokumen asli yang dapat diterima loket (ISS-005).
4. Jalankan pemeriksaan migrasi pada MySQL/MariaDB dan UAT staging. Audit Composer sekarang lulus tanpa advisory (ISS-007 resolved).
5. Review kode selesai; commit `6814d47` berhasil dipush ke `origin/main`. Deployment tidak dilakukan. Sebelum staging/production, selesaikan UAT map/GPS pada form terautentikasi, scan QR fisik, uji foto asli berlatar biru, serta migrasi dan UAT MySQL/MariaDB.
