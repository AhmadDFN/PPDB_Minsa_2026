# PRODUCTION READINESS CHECKLIST
## Sistem Informasi PPDB Online MIN 1 Kota Madiun (PPDB Minsatama)
### Tahun Ajaran 2026/2027

**Versi Dokumen**: 1.0.0
**Tanggal Evaluasi**: 19 November 2026
**Status Kesiapan Teknis**: **READY FOR STAGING / BLOCKED PRODUCTION GATES DOCUMENTED**
**Sasaran**: Tim DevOps / Deployment Engineer / Tim IT Madrasah

---

## 1. MATRIKS EVALUASI KESIAPAN PRODUKSI

| Komponen | Spesifikasi / Syarat Minimal | Status Kesiapan | Catatan & Rencana Mitigasi |
|---|---|:---:|---|
| **Versi PHP** | PHP 8.3.x (CLI & FPM) | **PASS** | Lingkungan pengujian terverifikasi menggunakan PHP 8.3.31 (Visual C++ 2019 x64). |
| **Ekstensi PHP Wajib** | `pdo_mysql`, `openssl`, `mbstring`, `fileinfo`, `dom`, `xml`, `xmlwriter`, `simplexml`, `curl`, `zip`, `bcmath`, `ctype`, `filter`, `hash`, `session`, `tokenizer` | **PASS** | Seluruh ekstensi inti Laravel dan sistem kriptografi aktif. |
| **Ekstensi PHP GD / Image** | `ext-gd` untuk rendering pas foto PNG pada Dompdf | **PRASYARAT OPERASIONAL** | Pada hosting produksi (Hostinger), `ext-gd` wajib diaktifkan pada menu PHP Extensions. Pada lingkungan tanpa GD, pas foto JPG tetap dapat dirender dengan sempurna, sedangkan PNG ditolak dengan pesan yang informatif. |
| **Batas Runtime PHP: `upload_max_filesize`** | Minimal 10M (memperhitungkan batas berkas 5120 KB + overhead multipart) | **MENUNGGU KONFIGURASI SERVER** | Konfigurasi bawaan PHP default biasanya 2M. Administrator hosting **wajib mengubah** nilai ini menjadi minimal `10M` pada `php.ini`. |
| **Batas Runtime PHP: `post_max_size`** | Minimal 16M (harus lebih besar dari `upload_max_filesize`) | **MENUNGGU KONFIGURASI SERVER** | Wajib diatur menjadi minimal `16M` pada konfigurasi hosting. |
| **Batas Runtime PHP: `memory_limit`** | Minimal 256M (rekomendasi 512M untuk kompilasi PDF Dompdf) | **MENUNGGU KONFIGURASI SERVER** | Dompdf membutuhkan alokasi memori yang cukup saat memproses font DejaVu Sans dan gambar A4. |
| **Batas Runtime PHP: `max_execution_time`** | Minimal 60 detik | **MENUNGGU KONFIGURASI SERVER** | Mencegah timeout saat pembuatan PDF bersamaan. |
| **Konfigurasi Lingkungan (`.env`)** | `APP_ENV=production`<br>`APP_DEBUG=false`<br>`APP_URL=https://ppdb.min1kotamadiun.sch.id` | **READY** | Generator PDF memvalidasi skema HTTPS publik saat `APP_ENV=production`. Nilai localhost atau IP privat akan ditolak secara ketat. |
| **Kunci Rahasia Aplikasi (`APP_KEY`)** | 32-byte base64 encoded AES key | **READY** | Dikelola secara mandiri di `.env` produksi. **Dilarang keras mengubah APP_KEY** setelah data pendaftar tersimpan karena akan menyebabkan kegagalan dekripsi NIK. |
| **Kunci Hash Deterministik (`NIK_HMAC_KEY`)** | 64-karakter string rahasia independen dari `APP_KEY` | **READY** | Digunakan untuk lookup hash HMAC-SHA256 NIK calon siswa. **Wajib konsisten dan tidak boleh diubah** sepanjang operasional sistem. |
| **Kunci Token OTP (`OTP_HMAC_KEY`)** | 64-karakter string rahasia independen | **READY** | Mengikat konteks OTP aktivasi dan pemulihan kata sandi. |
| **Basis Data Produksi** | MySQL 8.x / MariaDB 10.4+ (InnoDB Engine, UTF8MB4) | **VERIFIKASI FISIK MARIADB PASS; MYSQL 8 BLOCKED** | Skema tabel, indeks, foreign keys, counter submit multi-proses terverifikasi lulus pada MariaDB 10.4.32/InnoDB. Gerbang uji fisik MySQL 8 mandiri ditandai **BLOCKED** menunggu penyediaan server staging/hosting MySQL 8. |
| **Penyimpanan Berkas Privat (`ppdb_private`)** | Storage path `storage/app/private/` dengan `serve = false`, `throw = true` | **PASS** | Seluruh dokumen pendaftaran dan PDF tersimpan di luar `public_html`. Akses publik langsung melalui URL web dicegah 100%. |
| **Hak Akses Direktori Server (Linux Permissions)** | Direktori `storage/` dan `bootstrap/cache/` (chmod 775 atau 755 tergantung user web server) | **MENUNGGU DEPLOYMENT** | Web server (`www-data` atau user Hostinger) harus memiliki izin tulis penuh pada direktori tersebut. |
| **Aset Frontend Terkompilasi** | Asset Vite terkompilasi di `public/build/` | **PASS** | Build Vite sukses (3 modules transformed, CSS 95.99 kB, JS 1.72 kB). |
| **Audit Dependensi Composer** | 0 advisories | **PASS** | `composer audit` bersih tanpa kerentanan. |
| **Audit Dependensi NPM Produksi** | 0 vulnerabilities | **PASS** | `npm.cmd audit --omit=dev` bersih (0 vulnerabilities). |
| **Aset Identitas Resmi Madrasah** | Logo resmi Kemenag/MIN 1 Madiun dan alamat lengkap terverifikasi | **PENDING OWNER ACTION** | Saat ini kop PDF menggunakan teks institusi resmi Kementerian Agama dan MIN 1 Kota Madiun tanpa memasukkan logo rekayasa. Menunggu penyerahan aset resmi dari pemilik proyek. |

---

## 2. DETAIL REKOMENDASI KONFIGURASI HOSTING (HOSTINGER / CLOUD)

### A. Pengaturan File `.htaccess` Utama
Pastikan dokumen root mengarah ke direktori `public/`:
```apache
<IfModule mod_rewrite.c>
    RewriteEngine On
    RewriteRule ^(.*)$ public/$1 [L]
</IfModule>
```
Atau lebih disarankan mengatur *Document Root* pada panel hosting langsung ke `/home/uXXXX/domains/namadomain.sch.id/public_html/public`.

### B. Proteksi Direktori Privat
Pastikan direktori `storage/app/private` tidak dapat dibaca dari luar melalui HTTP request. Konfigurasi disk `ppdb_private` yang menggunakan `serve => false` memastikan Laravel tidak menyediakan route otomatis ke direktori ini. Seluruh pengunduhan wajib melalui controller terproteksi yang memverifikasi ownership dan tanda tangan kriptografis SHA-256.

### C. Pengaturan Nilai PHP (`php.ini` atau .user.ini)
```ini
upload_max_filesize = 10M
post_max_size = 16M
memory_limit = 512M
max_execution_time = 120
max_input_vars = 2000
date.timezone = Asia/Jakarta
```

---

## 3. CHECKLIST PRA-PELUNCURAN (PRE-FLIGHT LAUNCH GATES)

- [ ] Konfigurasi DNS domain resmi (`ppdb.min1kotamadiun.sch.id`) mengarah ke server hosting dengan SSL/TLS Let's Encrypt aktif (HTTPS).
- [ ] Pengaturan `APP_ENV=production` dan `APP_DEBUG=false` terverifikasi.
- [ ] Kunci `APP_KEY`, `NIK_HMAC_KEY`, dan `OTP_HMAC_KEY` dibuat dengan entropi tinggi dan diamankan dalam file `.env` server.
- [ ] Skema database dimigrasikan menggunakan perintah aman: `php artisan migrate --force` (DILARANG `migrate:fresh`!).
- [ ] Role dan permission di-seed menggunakan: `php artisan db:seed --class=RoleAndPermissionSeeder --force`.
- [ ] Akun Super Admin madrasah pertama dibuat melalui seeder atau CLI terisolasi.
- [ ] Periode pendaftaran aktif dikonfigurasi melalui database/seeder dengan tanggal buka/tutup dan kuota program.
- [ ] Ekstensi PHP `gd` dipastikan aktif pada PHP 8.3 hosting.
- [ ] Dilakukan uji coba pendaftaran 1 akun uji oleh tim madrasah pada domain langsung sebelum pengumuman pendaftaran resmi dibuka.
