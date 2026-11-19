# PANDUAN DEPLOYMENT STAGING HOSTINGER
## Sistem Informasi PPDB MIN 1 Kota Madiun (PPDB Minsatama)
### Tahun Ajaran 2026/2027

**Target Domain Staging**: `https://sleek-forestgreen-mink-13.hostingersite.com`  
**Provider**: Hostinger (Cloud / Shared Hosting)  
**Versi PHP**: PHP 8.3.36 (LiteSpeed / Apache)  
**Tujuan**: Pengujian fungsionalitas, integrasi storage, PDF & QR, serta User Acceptance Testing (UAT) sebelum migrasi ke server VPS produksi.

---

> [!IMPORTANT]
> **LINGKUNGAN STAGING SAJA**:
> Hostinger digunakan secara eksklusif sebagai lingkungan staging/uji coba. Server produksi resmi nantinya menggunakan VPS mandiri dengan panel **aaPanel**. Jangan menggunakan data siswa nyata pada server staging Hostinger ini.

---

## 1. PERSYARATAN & AUDIT SPESIFIKASI HOSTINGER

Sebelum memulai deployment, pastikan konfigurasi pada **hPanel** (Hostinger Control Panel) telah memenuhi spesifikasi minimum berikut:

| Parameter | Kebutuhan Minimum | Nilai Aktual Hostinger | Status |
|---|---|---|:---:|
| **Versi PHP** | PHP 8.3+ | PHP 8.3.36 | **TERPENUHI** |
| **Ekstensi PHP Wajib** | `pdo_mysql`, `mbstring`, `openssl`, `tokenizer`, `xml`, `ctype`, `curl`, `fileinfo` | Aktif secara default | **TERPENUHI** |
| **Ekstensi PHP Grafis** | `ext-gd` (Wajib untuk pas foto format PNG pada PDF) | Periksa di hPanel > Advanced > PHP Extensions | **WAJIB AKTIF** |
| **Memory Limit** | Minimal `256M` (direkomendasikan `512M` untuk render PDF) | `memory_limit = 512M` | **TERPENUHI** |
| **Upload Max Filesize** | Minimal `10M` | `upload_max_filesize = 16M` | **TERPENUHI** |
| **Post Max Size** | Minimal `16M` | `post_max_size = 20M` | **TERPENUHI** |
| **Max Execution Time** | Minimal `60s` | `max_execution_time = 120` | **TERPENUHI** |
| **Sertifikat SSL** | HTTPS Aktif (Let's Encrypt Hostinger) | Aktif (`Server: hcdn`, HTTPS 200 OK) | **TERPENUHI** |

---

## 2. STRUKTUR DIREKTORI & PENANGANAN DOKUMEN ROOT

Aplikasi Laravel memiliki berkas publik di subdirektori `/public`. Pada hosting Hostinger, terdapat dua arsitektur penempatan:

### Pilihan A: Mengubah Document Root ke `/public_html/public` (Paling Bersih)
Jika paket hosting Anda mendukung kustomisasi Document Root di hPanel:
1. Masuk ke **hPanel** > **Websites** > Kelola `sleek-forestgreen-mink-13.hostingersite.com`.
2. Buka menu **Advanced** > **Change Document Root**.
3. Arahkan direktori root ke: `public_html/public`.
4. Seluruh isi proyek Laravel diletakkan di dalam `public_html/`.

### Pilihan B: Menggunakan Root `.htaccess` (Jika Document Root Terkunci di `public_html`)
Repositori ini telah dilengkapi dengan berkas root `.htaccess` yang aman dan teruji untuk meneruskan seluruh request ke subdirektori `public/` tanpa loop internal:

```apache
<IfModule mod_rewrite.c>
    RewriteEngine On

    # Keamanan: Blokir akses langsung ke berkas sensitif di root
    RewriteRule ^(\.env|\.git|composer\.(json|lock)|package\.(json|lock)|artisan|phpunit\.xml) - [F,L,NC]
    RewriteRule ^(app|bootstrap|config|database|resources|storage|vendor|tests)/(.*)$ - [F,L,NC]

    # Teruskan request publik tanpa infinite loop
    RewriteCond %{REQUEST_URI} !^/public/
    RewriteRule ^(.*)$ public/$1 [L]
</IfModule>
```

---

## 3. PERSIAPAN BASIS DATA PADA HOSTINGER

1. Masuk ke **hPanel** > **Databases** > **MySQL Databases**.
2. Buat database baru khusus staging:
   - **Nama Database**: `uXXXXXXXXX_ppdb_staging` (sesuaikan dengan prefix akun Hostinger Anda).
   - **Username**: `uXXXXXXXXX_ppdb_user`.
   - **Password**: Gunakan kata sandi kuat acak (simpan di password manager).
3. Buka **phpMyAdmin** melalui hPanel.
4. Pilih database yang baru dibuat, klik tab **Import**.
5. Pilih berkas skema bersih yang telah disediakan:
   - Lokasi lokal: `database/schema_clean.sql`.
   - Klik **Go / Kirim** untuk mengeksekusi pembuatan 24 tabel, relasi foreign key, dan index.

---

## 4. KONFIGURASI ENVIRONMENT STAGING (`.env`)

Buat berkas `.env` pada direktori root Laravel di server Hostinger dengan parameter berikut:

```dotenv
APP_NAME="PPDB MIN 1 Kota Madiun"
APP_ENV=staging
APP_KEY=base64:Z2Q3YBw+eEt02s0cjEuQINqVWKWC72MI1d+pV3bZq9s=
APP_DEBUG=false
APP_URL=https://sleek-forestgreen-mink-13.hostingersite.com

APP_LOCALE=id
APP_FALLBACK_LOCALE=id
APP_FAKER_LOCALE=id_ID
APP_TIMEZONE=Asia/Jakarta

LOG_CHANNEL=daily
LOG_DEPRECATIONS_CHANNEL=null
LOG_LEVEL=info

DB_CONNECTION=mysql
DB_HOST=127.0.0.1
DB_PORT=3306
DB_DATABASE=uXXXXXXXXX_ppdb_staging
DB_USERNAME=uXXXXXXXXX_ppdb_user
DB_PASSWORD="MASUKKAN_PASSWORD_DB_HOSTINGER_ANDA"

BROADCAST_CONNECTION=log
FILESYSTEM_DISK=private
QUEUE_CONNECTION=database

CACHE_STORE=file
CACHE_PREFIX=ppdb_staging_cache_

SESSION_DRIVER=file
SESSION_LIFETIME=120
SESSION_ENCRYPT=false
SESSION_PATH=/
SESSION_DOMAIN=null
SESSION_SECURE_COOKIE=true
SESSION_HTTP_ONLY=true
SESSION_SAME_SITE=lax

# KUNCI KRIPTOGRAFI MANDIRI MINSATAMA (CSPRNG 256-bit)
NIK_HMAC_KEY=2c655b9f752c6ac39c03e102a774f935d53ed2b3b4ad8f6abf891b473701084f
OTP_HMAC_KEY=3d2d2011d778bacc913a1f283d4d3827715e8fe01519442f174a5823898364f1

# Kredensial Pengujian WhatsApp OTP (Staging Sandbox)
WHATSAPP_GATEWAY_PROVIDER=log
```

> [!CAUTION]
> Jangan pernah mengubah `NIK_HMAC_KEY` atau `APP_KEY` setelah data pendaftaran mulai masuk, karena perubahan kunci akan menyebabkan seluruh NIK terenkripsi dan hash pencarian tidak dapat didekripsi kembali.

---

## 5. METODE DEPLOYMENT KE HOSTINGER

### Metode 1: Menggunakan Paket ZIP Siap Deploy (Direkomendasikan & Tercepat)
Paket ZIP lengkap telah dipersiapkan di:
`D:\Dany\PPDB\deploy_minsatama_hostinger.zip` (Ukuran ~24 MB, memuat seluruh kode, vendor produksi, aset terkompilasi, dan konfigurasi).

Langkah instalasi:
1. Buka **hPanel** > **Files** > **File Manager**.
2. Masuk ke direktori `public_html/`.
3. **PENTING**: Hapus berkas `default.php` bawaan Hostinger (yang menampilkan halaman "Segera hadir").
4. Klik tombol **Upload** > Pilih berkas `deploy_minsatama_hostinger.zip`.
5. Setelah selesai, klik kanan berkas ZIP tersebut dan pilih **Extract**. Ekstrak langsung ke dalam `public_html/`.
6. Pastikan berkas `.env` telah disesuaikan dengan kredensial database Hostinger.
7. Hapus berkas `deploy_minsatama_hostinger.zip` dari server setelah ekstraksi berhasil.

### Metode 2: Menggunakan Fitur Git Deployment Hostinger
1. Buka **hPanel** > **Files** > **File Manager**.
2. Kosongkan direktori `public_html/` secara menyeluruh (hapus `default.php`).
3. Buka menu **Advanced** > **Git** pada hPanel.
4. Masukkan URL repositori GitHub:
   `https://github.com/AhmadDFN/PPDB_Minsa_2026.git`
5. Pilih branch: `main`.
6. Tentukan direktori instalasi: `public_html`.
7. Klik **Create / Deploy**.
8. Setelah clone selesai, unggah folder `vendor/` dan berkas `.env` secara manual via File Manager atau FTP karena folder `vendor` diabaikan oleh `.gitignore`.

---

## 6. PENGATURAN PERMISSION DAN PENYIMPANAN PRIVAT

Pada Shared Hosting Linux Hostinger, permission file dan folder harus diatur dengan benar agar Laravel dapat menulis log, sesi, dan berkas privat:

1. Buka **File Manager** atau terminal SSH:
   - Folder `storage/` dan seluruh subfoldernya: Permission **775** (atau **755** tergantung user group Apache Hostinger).
   - Folder `bootstrap/cache/`: Permission **775**.
2. Pastikan subdirektori penyimpanan privat telah terbentuk:
   - `storage/app/private/`
   - `storage/app/private/documents/`
   - `storage/app/private/pdfs/`
3. Jalankan symlink storage jika diperlukan untuk aset publik (meskipun dokumen pendaftar disimpan secara privat via controller):
   - Jika memiliki akses SSH: `php artisan storage:link`.
   - Jika tanpa SSH: Buat berkas PHP sementara `symlink.php` di dalam `public_html/` yang berisi:
     ```php
     <?php
     symlink(__DIR__.'/storage/app/public', __DIR__.'/public/storage');
     echo "Symlink created successfully!";
     ```
     Buka di browser sekali, lalu segera hapus berkas `symlink.php` tersebut.

---

## 7. SEEDING DATA PENGUJIAN STAGING

Untuk memulai pengujian UAT di staging, database memerlukan data referensi (Program Reguler & Unggulan, Akun Admin, Hak Akses Spatie):

Jika memiliki akses SSH pada Hostinger:
```bash
php artisan db:seed --class=DatabaseSeeder --force
```

Jika tanpa akses SSH:
Gunakan SQL Insert berikut pada phpMyAdmin untuk mengaktifkan akun Admin Staging dan Periode Pendaftaran UAT:

```sql
-- Role dan Permission dasar Spatie
INSERT INTO `roles` (`name`, `guard_name`, `created_at`, `updated_at`) VALUES
('super_admin', 'web', NOW(), NOW()),
('committee', 'web', NOW(), NOW()),
('verifier', 'web', NOW(), NOW());

-- Periode PPDB 2026/2027 Aktif
INSERT INTO `admission_periods` (`academic_year`, `name`, `start_date`, `end_date`, `age_reference_date`, `is_active`, `created_at`, `updated_at`) VALUES
('2026/2027', 'PPDB MINSATAMA 2026/2027', '2026-11-01', '2026-12-31', '2026-07-01', 1, NOW(), NOW());

-- Program Pendidikan
INSERT INTO `admission_programs` (`code`, `name`, `is_active`, `created_at`, `updated_at`) VALUES
('REGULER', 'Program Reguler', 1, NOW(), NOW()),
('UNGGULAN', 'Program Unggulan', 1, NOW(), NOW());
```

---

## 8. CHECKLIST VERIFIKASI PASCA-DEPLOYMENT STAGING

Lakukan verifikasi bertahap berikut pada browser:

- [ ] **Akses URL Utama**: Buka `https://sleek-forestgreen-mink-13.hostingersite.com/`. Halaman beranda PPDB Minsatama harus tampil sempurna dengan warna institusi (#0F7FA3).
- [ ] **Akses Asset CSS/JS**: Periksa tab Network browser (DevTools F12). Berkas CSS (`build/assets/app-*.css`) dan JS harus berstatus `200 OK`.
- [ ] **Keamanan Berkas Sensitif**: Buka `https://sleek-forestgreen-mink-13.hostingersite.com/.env`. Harus menghasilkan **403 Forbidden** atau **404 Not Found**, BUKAN menampilkan teks konfigurasi.
- [ ] **Alur Pendaftaran Calon Siswa**:
  1. Registrasi akun baru dengan NIK dummy.
  2. Verifikasi OTP (cek kode pada log staging `storage/logs/laravel-*.log` jika provider adalah log).
  3. Lengkapi biodata, alamat, orang tua, sekolah asal, dan program.
  4. Unggah kartu keluarga dan pas foto (coba JPG dan PNG).
  5. Unduh PDF Draf (periksa watermark transparan DRAFT).
  6. Kirim pendaftaran final (status `SUBMITTED`).
  7. Unduh Bukti Pendaftaran Resmi (PDF Final).
  8. Pindai kode QR verifikasi menggunakan kamera HP.

---

## 9. PEMECAHAN MASALAH UMUM (TROUBLESHOOTING)

### 1. Muncul Tulisan "Segera hadir" Hostinger
- **Penyebab**: Berkas `default.php` bawaan Hostinger belum dihapus dari direktori `public_html/`.
- **Solusi**: Masuk ke File Manager hPanel, hapus `default.php`.

### 2. Terjadi Loop Pengalihan (Too Many Redirects / 500 Error)
- **Penyebab**: Aturan rewrite pada root `.htaccess` memicu redirect loop.
- **Solusi**: Pastikan baris `RewriteCond %{REQUEST_URI} !^/public/` ada tepat sebelum `RewriteRule ^(.*)$ public/$1 [L]`.

### 3. Halaman Putih / 500 Internal Server Error
- **Penyebab**: Permission folder storage tidak dapat ditulis oleh web server atau ekstensi PHP belum aktif.
- **Solusi**:
  1. Periksa log kesalahan di `storage/logs/laravel-*.log`.
  2. Ubah permission folder `storage/` dan `bootstrap/cache/` menjadi 775 atau 777 sementara.
  3. Pastikan ekstensi `pdo_mysql` dan `fileinfo` aktif di hPanel.

### 4. Gagal Render Pas Foto Format PNG pada Dokumen PDF
- **Penyebab**: Ekstensi PHP `gd` belum diaktifkan di Hostinger.
- **Solusi**: Buka hPanel > **PHP Configurations** > **PHP Extensions** > Centang `gd` > Klik **Save**.
