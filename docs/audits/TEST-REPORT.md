# AUTOMATED TEST EXECUTION REPORT — PPDB MINSATAMA
## Laporan Hasil Pengujian Otomatis Sistem PPDB MIN 1 Kota Madiun

**Eksekusi Regresi Terbaru**: 18 November 2026
**Framework Pengujian**: PHPUnit 12.5.44 / Laravel Test Runner
**Lingkungan**: SQLite in-memory (`:memory:`)  
**Total Pengujian Terbaru**: 161 tes
**Status Terbaru**: **159 LULUS, 2 DILEWATI, 0 GAGAL**
**Total Asersi Terbaru**: 618
**Durasi Terbaru**: 8.72 detik

## 1. HASIL TERBARU — FASE 6

- `php artisan test --compact`: 161 tests, 159 passed, 2 skipped, 0 failed; 618 assertions. Kedua skip adalah tes kompatibilitas MySQL karena MySQL pada `127.0.0.1:3306` tidak dapat dihubungi.
- `php artisan test --compact tests/Feature/Applicant/RegistrationWorkflowTest.php`: 18 tests passed; 114 assertions.
- Cakupan Fase 6: kelengkapan, consent, fail-closed konfigurasi consent kosong, submit atomik/idempoten, nomor dan sequence per periode-program, periode/program tidak aktif, ownership, masking, audit revisi, edit setelah submit/penutupan periode, dan preservasi revisi dokumen.
- Form ayah/ibu dan pemeriksaan pra-submit mewajibkan field sesuai Business Requirements: NIK, status, pendidikan, pekerjaan, penghasilan, dan telepon.
- Batas pengujian: tes isolasi berjalan di SQLite. Ekstensi `pdo_mysql` tersedia, tetapi layanan MySQL fisik tidak tersedia. Tidak ada migration atau uji concurrency InnoDB yang dijalankan.
- `vendor/bin/pint --dirty --format agent`: PASS.
- `npm.cmd run build`: PASS (Vite 8.3.8; 3 modules transformed; CSS 85.88 kB, JS 1.72 kB).
- `composer audit`: 0 advisories. `npm.cmd audit --json`: 6 high temuan pada rantai `picomatch`; `npm.cmd audit --omit=dev`: 0 temuan.
- `php artisan test` dijalankan setelah Pint; tidak ada migration pada database MySQL/persisten. Gate MySQL dan concurrency masih BLOCKED.

## 2. HASIL FASE 5 — MANAJEMEN DOKUMEN

- Regresi pada akhir Fase 5: 143 tests, 141 passed, 2 skipped, 0 failed; 494 assertions.
- `php artisan test --filter=DocumentManagementTest`: 12 tests passed, 54 assertions.
- Cakupan dokumen: UI persyaratan, file di bawah/tepat/di atas 5120 KB, limit per dokumen, penolakan konfigurasi >5120 KB, ekstensi/MIME spoofing, ownership/IDOR, revisi dan persistensi berkas lama, escaping konten riwayat, cleanup setelah kegagalan DB, dan progress dashboard.
- Batas pengujian: file 5120 KB diuji menggunakan upload sintetis Laravel. PHP lokal masih `upload_max_filesize=2M`, jadi live upload 5 MB memerlukan konfigurasi server sesuai kebijakan.

---

## 3. RINCIAN HISTORIS PENGUJIAN — BASELINE 15 NOVEMBER 2026

Rincian berikut mempertahankan laporan sebelum Fase 5 (93 tes, 91 lulus, 2 dilewati); gunakan hasil terbaru di atas sebagai status regresi saat ini.

### 1.1 Tests\Unit\OtpServiceSecurityTest (10 Tes - 28 Asersi) — LULUS
- `✓ compute hash produces deterministic hmac sha256`: Menjamin hash HMAC deterministik 64 karakter.
- `✓ compute hash differs when context changes`: Memastikan perubahan user_id, purpose, otp, atau nomor telepon menghasilkan hash berbeda.
- `✓ compute hash fails closed when key is missing`: Melempar `RuntimeException` jika kunci belum dikonfigurasi.
- `✓ compute hash fails closed when key equals app key`: Menolak penggunaan kunci yang identik dengan `APP_KEY`.
- `✓ compute hash fails closed when key equals nik hmac key`: Menolak penggunaan kunci yang identik dengan `NIK_HMAC_KEY`.
- `✓ generate and send dispatches otp and invalidates previous tokens`: Memvalidasi pengiriman OTP dan kedaluwarsa token lama saat penerbitan baru.
- `✓ verify otp rejects wrong code and tracks attempts`: Menolak kode salah dan menaikkan counter percobaan.
- `✓ verify otp rejects when attempts exhausted`: Mengunci token setelah 5 kali percobaan gagal.
- `✓ verify otp rejects expired token`: Menolak token setelah 5 menit.
- `✓ verify otp rejects replay of consumed token`: Menolak penggunaan kembali token yang sudah terverifikasi.

### 1.2 Tests\Feature\Auth\ApplicantRegistrationTest (7 Tes - 44 Asersi) — LULUS
- `✓ can view registration page`: Tampilan halaman pendaftaran awal memuat form dan petunjuk KK/Akta.
- `✓ registration validates required fields`: Validasi seluruh kolom wajib (NIK, nama, JK, TTL, no. WA, consent).
- `✓ registration rejects invalid nik format`: Menolak format NIK selain 16 digit angka numerik.
- `✓ successful registration atomically creates user applicant and dispatches otp`: Transaksi DB atomik membuat User, Applicant, dan mengirim OTP.
- `✓ rejects duplicate nik registration`: Menolak pendaftaran dengan NIK yang sama.
- `✓ allows sibling registration with same phone number`: Mengizinkan nomor telepon orang tua yang sama untuk pendaftaran anak berbeda.
- `✓ authenticated applicant is redirected away from registration`: Mengalihkan pendaftar yang sudah login ke dasbor.

### 1.3 Tests\Feature\Auth\OtpActivationTest (8 Tes - 32 Asersi) — LULUS
- `✓ cannot view otp screen without session`: Mengalihkan ke form daftar jika sesi aktivasi tidak ada.
- `✓ can view otp screen with valid session`: Menampilkan nomor telepon termasker pada sesi valid.
- `✓ valid otp verifies phone and advances to password creation`: Memverifikasi kode benar, membubuhkan stempel `phone_verified_at`, dan melanjutkan ke form kata sandi.
- `✓ invalid otp is rejected`: Menolak kode OTP salah.
- `✓ cannot view password creation without verified session`: Menolak akses form kata sandi sebelum nomor telepon terverifikasi.
- `✓ setting password activates account and sets active status`: Memperbarui kata sandi ter-hash dan mengaktifkan status akun ke `ACTIVE`.
- `✓ setting password requires minimum 8 characters and confirmation`: Menolak kata sandi kurang dari 8 karakter atau konfirmasi tidak cocok.
- `✓ resending otp dispatches new code and invalidates old code`: Mengirim kode baru dan mengedaluwarsa token lama.

### 1.4 Tests\Feature\Auth\ApplicantLoginTest (8 Tes - 28 Asersi) — LULUS
- `✓ can view applicant login page`: Form masuk calon siswa memuat input NIK dan kata sandi.
- `✓ active applicant can login with valid nik and password`: Calon siswa aktif dapat masuk ke dasbor.
- `✓ login rejects pending activation account and redirects to activation`: Akun belum aktif diarahkan ke halaman verifikasi.
- `✓ login rejects suspended account`: Akun tersuspensi ditolak masuk.
- `✓ login rejects wrong password with generic error`: Menolak kata sandi salah tanpa membocorkan status akun.
- `✓ login rejects non existent nik with generic error`: Menolak NIK tidak terdaftar dengan pesan generik yang sama.
- `✓ rate limiter throttles excessive failed applicant logins`: Mengunci akses setelah 5 kali gagal login.
- `✓ applicant logout terminates session`: Memutus sesi dan token CSRF saat logout.

### 1.5 Tests\Feature\Auth\AdminLoginTest (7 Tes - 23 Asersi) — LULUS
- `✓ can view admin login page`: Portal admin bernuansa profesional panitia.
- `✓ admin with valid credentials and role can login`: Admin dengan peran `superadmin`/`admin_ppdb` berhasil login.
- `✓ applicant cannot login through admin login`: Calon siswa ditolak login di portal admin.
- `✓ admin without required role cannot login`: Akun admin tanpa peran Spatie ditolak masuk.
- `✓ inactive admin cannot login`: Admin tersuspensi ditolak masuk.
- `✓ admin with wrong password is rejected`: Menolak kata sandi salah.
- `✓ admin logout terminates session`: Logout admin memutus sesi.

### 1.6 Tests\Feature\Auth\PasswordRecoveryTest (6 Tes - 27 Asersi) — LULUS
- `✓ can view forgot password page`: Halaman lupa kata sandi memuat form NIK.
- `✓ submitting registered nik with verified phone issues otp`: Mengirimkan OTP pemulihan pada akun terverifikasi.
- `✓ submitting non existent nik returns generic message without enumerating`: Menampilkan pesan generik tanpa memberitahu bahwa NIK tidak ada.
- `✓ submitting account with unverified phone returns warning`: Menampilkan petunjuk verifikasi ke loket PTSP untuk nomor belum terverifikasi.
- `✓ verifying recovery otp advances to reset password form`: Verifikasi OTP pemulihan mengizinkan form ubah kata sandi.
- `✓ setting new password updates user password and enables login`: Kata sandi baru berhasil disimpan dan dapat digunakan login.

### 1.7 Tests\Feature\Auth\AuthorizationAndMiddlewareTest (6 Tes - 14 Asersi) — LULUS
- `✓ unauthenticated user cannot access applicant dashboard`: Tamu ditolak mengakses dasbor pendaftar.
- `✓ unauthenticated user cannot access admin dashboard`: Tamu ditolak mengakses dasbor admin.
- `✓ applicant cannot access admin dashboard`: Calon siswa ditolak mengakses dasbor admin (403 Forbidden).
- `✓ admin cannot access applicant dashboard`: Admin ditolak mengakses dasbor calon siswa (403 Forbidden).
- `✓ suspended user is logged out by active middleware`: Sesi pendaftar tersuspensi diputus paksa.
- `✓ pending activation user is logged out by active middleware`: Sesi pendaftar belum aktif diputus paksa.

### 1.8 Tests\Feature\Database\DatabaseSchemaAndRulesTest (18 Tes - 58 Asersi) — LULUS
- Pengujian skema 16 tabel, foreign key constraints, unique indexes, enum casts, dan kalkulasi dinamis umur siswa.

### 1.9 Tests\Feature\Database\SpatiePermissionSecurityTest (7 Tes - 19 Asersi) — LULUS
- Pengujian isolasi peran Spatie, izin guard `web`, dan pencegahan pemberian peran ke akun siswa.

### 1.10 Tests\Feature\FoundationTest (3 Tes - 7 Asersi) — LULUS
- Pengujian landing page beranda, layout admin, konfigurasi zona waktu `Asia/Jakarta`, dan locale `id`.

### 1.11 Tests\Unit\SecurityAndOtpTest (11 Tes - 22 Asersi) — LULUS
- Pengujian unit normalisasi NIK, enkripsi round-trip, determinisme hash, dan kompatibilitas model OTP.

### 1.12 Tests\Feature\Database\MySqlSchemaCompatibilityTest (2 Tes) — SKIPPED
- `S mysql service is not running on 127.0.0.1:3306`: Pengujian live InnoDB dilewati karena daemon MySQL lokal belum aktif.

---

## 3. KEPATUHAN GAYA KODE (LINTING & CODE STYLE)

- **Alat**: Laravel Pint v1.27
- **Perintah**: `vendor/bin/pint --dirty --format agent`
- **Hasil**: `PASS` (tidak ada file PHP yang perlu diformat pada audit dokumentasi ini).
