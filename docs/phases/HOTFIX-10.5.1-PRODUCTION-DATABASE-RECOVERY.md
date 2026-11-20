# HOTFIX 10.5.1 — PRODUCTION DATABASE MIGRATION & RUNTIME RECOVERY
## Sistem Informasi PPDB Online MIN 1 Kota Madiun (PPDB Minsatama)

**Tanggal Eksekusi**: 20 November 2026  
**Status**: SELESAI SECARA TEKNIS (KODE LOKAL & PENGUJIAN 100% PASSED)  
**Tipe Perbaikan**: Hotfix Keberlanjutan Basis Data Produksi & Degradasi Anggun (*Graceful Runtime Recovery*)  

---

## 1. TEMUAN MASALAH & ANALISIS AKAR PENYEBAB (ROOT CAUSE)

### A. Gejala di Lingkungan Produksi / Staging Hostinger:
Halaman pendaftaran (`/daftar`), halaman masuk (`/login`), dan proses autentikasi menampilkan pesan kesalahan HTTP 500:
```text
Illuminate\Database\QueryException
SQLSTATE[42S02]: Base table or view not found: 1146
Table '...system_settings' doesn't exist.

SQL:
select * from system_settings where key = institution_display_name limit 1
at app/Models/SystemSetting.php:29
```

### B. Hasil Audit & Pembuktian Faktual:
1. **Penyebab Utama (Primary Cause)**:
   - Pada commit `8665755` (Fase 10.4), fitur *dynamic system branding* diperkenalkan melalui model `App\Models\SystemSetting` yang dipanggil pada layout publik (`resources/views/auth/applicant-login.blade.php`, `applicant-register.blade.php`, dll.) guna menampilkan nama institusi secara seragam.
   - Repositori telah memiliki berkas migrasi `database/migrations/2026_11_20_080004_create_system_settings_tables.php`.
   - Ketika kode di-deploy melalui GitHub Auto Deploy ke Hostinger, *webhook* hanya menjalankan sinkronisasi Git (`git pull`) namun **TIDAK menjalankan `php artisan migrate --force`** secara otomatis pada basis data MySQL/MariaDB Hostinger demi mencegah eksekusi DDL tanpa pengawasan manusia.
   - Akibatnya, tabel `system_settings` dan `system_setting_logs` belum terbentuk di basis data produksi Hostinger saat kode baru mulai melayani permintaan pengguna.

2. **Ketiadaan Degradasi Anggun (*Missing Fallback*)**:
   - Metode `SystemSetting::get()` sebelumnya langsung memanggil query Eloquent di dalam `Cache::rememberForever()`.
   - Ketika tabel belum tersedia, Eloquent melempar `QueryException` (1146 / 42S02) tanpa penanganan khusus sehingga mengakibatkan HTTP 500 fatal.

3. **Paparan Informasi Debug di Lingkungan Produksi**:
   - Screenshot menunjukkan jejak stack trace, query SQL, dan struktur tabel secara terbuka.
   - Hal ini membuktikan bahwa konfigurasi environment Hostinger masih menggunakan `APP_DEBUG=true`.

---

## 2. DAFTAR MIGRASI FASE 10.4 & 10.5 YANG TERTUNDA DI HOSTINGER

Berdasarkan audit repositori, terdapat **5 migrasi baru** dari Fase 10.4 yang berstatus *Pending* di basis data Hostinger. Seluruh migrasi ini bersifat **aditif, non-destruktif**, dan aman bagi data yang sudah ada:

| No | Nama File Migrasi | Efek Skema Basis Data | Dampak Data Eksisting |
|---|---|---|---|
| 1 | `2026_11_20_080001_make_applicant_biodata_fields_nullable.php` | Mengubah kolom `gender`, `birth_place`, `birth_date` pada tabel `applicants` menjadi `nullable`. | **Aman**. Baris data yang sudah ada tetap utuh. |
| 2 | `2026_11_20_080002_add_must_change_password_to_users_table.php` | Menambahkan kolom `must_change_password` (boolean, default false) dan `password_changed_at` (timestamp, nullable) pada tabel `users`. | **Aman**. Akun eksisting otomatis bernilai `false`. |
| 3 | `2026_11_20_080003_create_yearly_registration_sequences_table.php` | Membuat tabel baru `yearly_registration_sequences` untuk sequence nomor pendaftaran 5 digit (`YYNNN`). | **Aman**. Tabel baru terisolasi. |
| 4 | `2026_11_20_080004_create_system_settings_tables.php` | Membuat tabel baru `system_settings` dan `system_setting_logs` untuk pengaturan institusi dan jejak audit. | **Aman**. Menyelesaikan tabel yang hilang. |
| 5 | `2026_11_20_080005_make_admission_period_program_id_nullable_in_registrations.php` | Mengubah kolom foreign key `admission_period_program_id` pada tabel `registrations` menjadi `nullable`. | **Aman**. Baris registrasi yang sudah ada tetap utuh. |

*Catatan: Pada Fase 10.5 tidak ada penambahan migrasi baru karena tabel yang digunakan (`registrations`, `applicants`, `users`, `registration_logs`, `registration_documents`, dll.) telah tersedia secara lengkap.*

---

## 3. PERBAIKAN KODE APLIKASI (RUNTIME RECOVERY)

### Pembaruan `app/Models/SystemSetting.php`:
Untuk memberikan ketahanan maksimal selama jendela migrasi deployment:
1. `SystemSetting::get()` kini menangkap `QueryException` khusus kondisi tabel hilang (`SQLSTATE 42S02`, MySQL code `1146`, SQLite `no such table`).
2. Jika tabel belum terbentuk, sistem langsung mengembalikan nilai bawaan default (`PPDBM MIN 1 KOTA MADIUN`) tanpa menimbulkan HTTP 500.
3. Nilai fallback saat tabel hilang **TIDAK disimpan ke dalam cache secara permanen**, sehingga segera setelah migrasi selesai dieksekusi di server, nilai aktual di basis data langsung terbaca secara instan.
4. Kesalahan basis data non-tabel (seperti koneksi terputus, autentikasi gagal, atau deadlock) **tetap dilemparkan (re-thrown)** dan tidak disembunyikan.

---

## 4. PANDUAN EKSEKUSI PEMILIK PROYEK DI SERVER HOSTINGER

> [!CAUTION]
> **DILARANG KERAS MENJALANKAN:**
> `php artisan migrate:fresh`, `php artisan migrate:refresh`, `php artisan migrate:reset`, `php artisan db:wipe`, perintah `DROP TABLE`, atau `TRUNCATE`. Perintah-perintah tersebut akan menghapus akun dan data pendaftar!

Ikuti urutan langkah aman berikut melalui SSH atau menu Terminal hPanel Hostinger:

### Langkah 1: Cadangkan Basis Data Terlebih Dahulu (Backup)
1. Masuk ke **hPanel Hostinger** &rarr; **Databases** &rarr; **phpMyAdmin**.
2. Pilih basis data PPDB Minsatama.
3. Klik menu **Export** &rarr; Pilih metode **Quick** &rarr; Format **SQL** &rarr; Klik **Export** untuk mengunduh berkas cadangan ke komputer lokal.

### Langkah 2: Periksa Status Migrasi yang Tertunda
Buka SSH atau Terminal Hostinger pada direktori root proyek:
```bash
php artisan migrate:status
```
Pastikan 5 migrasi berikut berstatus `Pending`:
- `2026_11_20_080001_make_applicant_biodata_fields_nullable`
- `2026_11_20_080002_add_must_change_password_to_users_table`
- `2026_11_20_080003_create_yearly_registration_sequences_table`
- `2026_11_20_080004_create_system_settings_tables`
- `2026_11_20_080005_make_admission_period_program_id_nullable_in_registrations`

### Langkah 3: Terapkan Migrasi Non-Destruktif
Jalankan migrasi resmi dengan flag `--force`:
```bash
php artisan migrate --force
```

### Langkah 4: Sesuaikan Konfigurasi Keamanan `.env` Produksi
Periksa berkas `.env` di Hostinger (melalui File Manager atau `nano .env`):
Pastikan dua baris berikut telah diatur secara benar:
```env
APP_ENV=production
APP_DEBUG=false
```
*(Jangan pernah membiarkan `APP_DEBUG=true` di server yang dapat diakses publik).*

### Langkah 5: Bersihkan dan Optimalkan Cache Laravel
Jalankan rangkaian perintah berikut di terminal:
```bash
php artisan optimize:clear
php artisan config:cache
php artisan route:cache
php artisan view:cache
```

---

## 5. HASIL PENGUJIAN OTOMATIS (AUTOMATED REGRESSION TESTS)

Seluruh gate kualitas dan pengujian regresi telah dijalankan secara lokal:

```text
1. Automated Test Suite (php artisan test):
   - Total Tests: 286 tests (bertambah 7 tests baru di SystemSettingRecoveryTest)
   - Status: PASSED (100%)
   - Assertions: 1.386 assertions
   - Failures: 0
   - Errors: 0
   - Execution Time: ~14.4 detik

2. Code Style Compliance (vendor/bin/pint --test):
   - Status: PASSED (0 style issues found)

3. Frontend Build (npm.cmd run build):
   - Status: PASSED (3 modules transformed, CSS 117.44 kB, JS 1.72 kB)

4. Security Dependency Audits:
   - Composer: PASSED (0 security advisories found)
   - NPM Production: PASSED (0 vulnerabilities found)

5. Git Diff Quality Check:
   - Status: CLEAN (0 whitespace or merge conflicts)
```

### Rincian Skenario Uji Baru (`tests/Feature/SystemSettingRecoveryTest.php`):
1. `test_system_setting_reads_default_when_record_does_not_exist`: Memastikan default fallback `'PPDBM MIN 1 KOTA MADIUN'` terbaca saat tabel ada tetapi baris data kosong.
2. `test_system_setting_reads_custom_value_when_configured`: Memastikan nilai yang diubah oleh Superadmin dapat dibaca secara akurat.
3. `test_system_setting_gracefully_falls_back_when_table_is_missing`: Mensimulasikan kondisi tabel `system_settings` hilang/belum dimigrasi, memastikan sistem TIDAK melempar HTTP 500 dan cache TIDAK tercemar secara permanen.
4. `test_system_setting_does_not_swallow_non_table_database_errors`: Memastikan kegagalan basis data umum di luar masalah tabel tidak disembunyikan.
5. `test_login_and_registration_pages_render_cleanly_when_table_exists`: Halaman `/login` dan `/daftar` merender nama institusi dengan respons 200 OK saat tabel tersedia.
6. `test_login_and_registration_pages_render_without_500_when_table_is_missing`: Halaman `/login` dan `/daftar` tetap merender dengan 200 OK saat tabel belum terbentuk.
7. `test_admin_and_applicant_authentication_has_no_regression`: Memastikan alur login dan dashboard Applicant serta Admin tetap berfungsi 100% tanpa regresi.

---

## 6. CHECKLIST VERIFIKASI SITUS SETELAH MIGRASI

Setelah pemilik proyek menerapkan langkah migrasi dan optimasi di Hostinger, lakukan pengujian manual pada tautan produksi:

- [ ] **Halaman Publik**:
  - Buka `https://ahmadanyfn.biz.id/` (Landing page tampil futuristik, 07.00 - 15.00 WIB, logo resmi terpasang).
  - Buka `https://ahmadanyfn.biz.id/login` (Tampil tanpa HTTP 500, judul memuat nama madrasah).
  - Buka `https://ahmadanyfn.biz.id/daftar` (Tampil formulir ringkas 4 input).
- [ ] **Alur Calon Siswa (Applicant)**:
  - Uji pendaftaran akun baru dengan NIK dan No. WA valid.
  - Verifikasi penerimaan kode OTP WhatsApp via Fonnte.
  - Verifikasi kode OTP pada form `/aktivasi`.
  - Verifikasi penerimaan password sementara via WhatsApp Fonnte.
  - Masuk pertama kali & selesaikan wajib ganti kata sandi (`/pendaftar/ganti-password`).
  - Masuk ke Dashboard Pendaftar (`/pendaftar/dashboard`).
- [ ] **Panel Admin**:
  - Masuk ke `https://ahmadanyfn.biz.id/admin/login` menggunakan kredensial Admin/Superadmin.
  - Buka `/admin/dashboard` (Data metrik pendaftar riil tampil tanpa error).
  - Buka `/admin/pendaftar` (Tabel pendaftar, pencarian, dan filter berfungsi).
  - Buka `/admin/pengaturan` (Khusus Superadmin: periksa dan ubah nama institusi jika diperlukan, pastikan log audit tercatat).
- [ ] **Integritas Basis Data**:
  - Tabel `system_settings` dan `system_setting_logs` terkonfirmasi ada di phpMyAdmin.
  - Data pendaftar, riwayat unggahan dokumen, berkas PDF bukti pendaftaran, dan nomor registrasi tetap utuh tanpa ada yang terhapus.
