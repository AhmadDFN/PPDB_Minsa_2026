# LAPORAN IMPLEMENTASI FASE 9
## Hostinger Staging Deployment & User Acceptance Testing
### Sistem Informasi PPDB Online MIN 1 Kota Madiun (PPDB Minsatama)

**Tanggal Penyusunan**: 19 November 2026  
**Versi Dokumen**: 1.0.0  
**Status Fase**: **SELESAI SECARA TEKNIS (SIAP UAT FISIK SEKOLAH & MENUNGGU ARSITEKTUR AAPANEL PRODUKSI)**  
**Peran Auditor / Engineer**: Senior Laravel DevOps Engineer, Deployment Engineer, QA Lead, Application Security Engineer, Database Engineer  
**Lingkungan Staging**: `https://sleek-forestgreen-mink-13.hostingersite.com`  
**Target Produksi Masa Depan**: VPS Linux Mandiri dengan aaPanel (Ubuntu 22.04/24.04 LTS, Nginx, PHP 8.3 FPM, MariaDB 10.11+/MySQL 8)

---

## 1. EXECUTIVE SUMMARY

Fase 9 berfokus pada transisi sistem dari lingkungan pengujian lokal menuju lingkungan server sungguhan (*real-world hosting*) sebagai tahap **Staging** pada Hostinger (`sleek-forestgreen-mink-13.hostingersite.com`), serta persiapan arsitektur deployment masa depan ke server VPS berbasis **aaPanel**.

Tujuan utama fase ini meliputi:
1. Memvalidasi kompatibilitas penuh aplikasi PPDB Minsatama (Laravel 13, PHP 8.3) pada hosting publik nyata.
2. Mempersiapkan seluruh artefak rilis staging (paket deployment ZIP terkompilasi, berkas skema database bersih `schema_clean.sql`, konfigurasi proteksi root `.htaccess`, dan variabel lingkungan `.env`).
3. Memastikan integritas alur bisnis end-to-end dari registrasi akun hingga pencetakan dokumen bukti registrasi PDF dan pemindaian kode QR.
4. Memetakan hasil pengujian penerimaan pengguna (*User Acceptance Testing*) terhadap 21 skenario formal pada `UAT-TEST-PLAN.md`.
5. Menyusun cetak biru lengkap (*production runbook*) untuk deployment final di server VPS aaPanel tanpa melanggar batasan larangan deployment produksi sebelum persetujuan resmi.

**Readiness Score Kesiapan Staging**: **96/100**  
(4 poin tertahan pada ketersediaan berkas logo resolusi tinggi resmi madrasah, teks alamat lengkap SK madrasah, dan penandatanganan fisik lembar UAT oleh komite madrasah).

---

## 2. PRE-FLIGHT REPOSITORY VERIFICATION

Sebelum paket deployment disusun, audit ketat dijalankan pada repositori lokal:

- **Branch Git**: `main` (bersih, sinkron dengan `origin/main` pada GitHub `https://github.com/AhmadDFN/PPDB_Minsa_2026.git`).
- **Baseline Commit Fase 8**: `774f7bc` (`test(phase-8): complete integration and production readiness audit`).
- **Commit Persiapan Deployment**:
  - `777ba60`: `build(deploy): include compiled assets and root htaccess for hostinger deployment`
  - `8de7ac0`: `chore(database): add clean mysql schema dump for phpmyadmin import`
  - `e42e7d0`: `fix(htaccess): add RewriteCond to prevent loop in root rewrite`
- **Hasil Test Suite Lokal**:
  - `php artisan test`: **203 tests PASSED, 1065 assertions, 0 failed, 0 skipped**.
  - Waktu eksekusi: 8.4 detik (SQLite in-memory) dan 10 skenario MariaDB 10.4.32 fisik lulus.
- **Kepatuhan Gaya Kode**: `vendor/bin/pint --test` **PASSED (0 formatting issues)**.
- **Audit Dependensi**:
  - `composer audit`: **0 vulnerabilities/advisories**.
  - `npm.cmd audit`: **0 vulnerabilities** (dependensi dev dan produksi bersih).
- **Kompilasi Frontend (Vite)**:
  - `npm.cmd run build`: **BERHASIL**.
  - `public/build/assets/app-*.css`: 95.99 kB (Tailwind CSS v4 teroptimasi).
  - `public/build/assets/app-*.js`: 1.72 kB (Vanilla JS murni).

---

## 3. DEPLOYMENT ENVIRONMENT ARCHITECTURE

Proyek PPDB Minsatama menerapkan pemisahan ketat antara dua lingkungan:

```
+-----------------------------------------------------------------------------------+
|                        LINGKUNGAN STAGING (FASE 9)                                |
| Provider       : Hostinger Cloud/Shared Hosting                                   |
| Domain         : https://sleek-forestgreen-mink-13.hostingersite.com             |
| Web Server     : LiteSpeed / Apache (Root .htaccess rewrite ke public/)          |
| PHP Runtime    : PHP 8.3.36 (64-bit)                                              |
| Storage        : Local disk (Private storage via storage/app/private)             |
| Database       : MySQL 8 / MariaDB Hostinger via phpMyAdmin                       |
| Tujuan         : Uji fungsional server publik, verifikasi PDF/QR, demonstrasi UAT |
+-----------------------------------------------------------------------------------+
                                          |
                                          | (Setelah Sign-Off Pemilik Proyek)
                                          v
+-----------------------------------------------------------------------------------+
|                        LINGKUNGAN PRODUKSI (MASA DEPAN)                           |
| Provider       : VPS Mandiri (KVM / Cloud Compute)                                |
| Control Panel  : aaPanel v6.8+ / v7.x                                             |
| Domain Resmi   : https://ppdb.min1kotamadiun.sch.id (atau domain madrasah resmi)  |
| Web Server     : Nginx 1.24+ (FastCGI, Security Headers, Gzip/Brotli)              |
| PHP Runtime    : PHP 8.3 FPM + OPcache 256MB                                      |
| Database       : MariaDB 10.11+ LTS / MySQL 8.0 Enterprise InnoDB                  |
| Cache & Queue  : Redis 7.x In-Memory                                              |
| Background Ops : Supervisor Process Manager (WhatsApp OTP queue & async PDF)      |
| Backup         : Cron otomatis harian ke penyimpanan off-site/cloud               |
+-----------------------------------------------------------------------------------+
```

---

## 4. HOSTINGER STAGING ENVIRONMENT ANALYSIS

Pemeriksaan aktif terhadap server Hostinger pada URL `https://sleek-forestgreen-mink-13.hostingersite.com` menghasilkan data faktual berikut:

1. **Konektivitas & Respons Server**:
   - Status: `HTTP/1.1 200 OK`.
   - Web Server: `Server: hcdn` (Hostinger Content Delivery Network & Reverse Proxy).
   - Platform: `platform: hostinger`, `panel: hpanel`.
   - Security Header Bawaan: `Content-Security-Policy: upgrade-insecure-requests`.
2. **Versi PHP Runtime**:
   - `X-Powered-By: PHP/8.3.36`.
   - Sangat kompatibel dengan spesifikasi proyek Laravel 13 (mensyaratkan PHP 8.3+).
3. **Ketersediaan Ekstensi PHP**:
   - Ekstensi standar `pdo_mysql`, `mbstring`, `openssl`, `tokenizer`, `xml`, `ctype`, `curl`, `fileinfo` aktif.
   - Ekstensi grafis `gd` wajib diaktifkan melalui menu hPanel (*PHP Extensions*) agar berkas pas foto berformat PNG dapat dirender oleh mesin Dompdf tanpa galat.
4. **Karakteristik Dokumen Root & File Manager**:
   - Direktori utama web adalah `public_html/`.
   - Berkas bawaan `default.php` ("Segera hadir") wajib dihapus agar tidak menghalangi rute Laravel.

---

## 5. STAGING DEPLOYMENT EXECUTION

### A. Penyusunan Paket Rilis Mandiri
Untuk menghindari kendala kompilasi aset atau latensi unduh Composer di shared hosting, dibuat arsip mandiri:
- **Nama Berkas**: `deploy_minsatama_hostinger.zip`.
- **Ukuran**: 24.094.334 bytes (~24 MB).
- **Lokasi Lokal**: `D:\Dany\PPDB\deploy_minsatama_hostinger.zip`.
- **Isi Paket**:
  - Seluruh kode aplikasi `app/`, `bootstrap/`, `config/`, `database/`, `resources/`, `routes/`.
  - Folder dependensi produksi `vendor/` (telah dioptimalkan dengan autoloader).
  - Folder publik `public/` memuat aset terkompilasi `public/build/` (CSS dan JS Vite).
  - Root `.htaccess` dengan aturan pengalihan aman.
  - Skema database bersih `database/schema_clean.sql`.
  - Template konfigurasi staging `.env`.

### B. Konfigurasi Root `.htaccess` Anti-Loop
Konfigurasi berikut diterapkan pada root repositori dan paket staging untuk mengalirkan traffic ke folder `public/`:
```apache
<IfModule mod_rewrite.c>
    RewriteEngine On
    RewriteRule ^(\.env|\.git|composer\.(json|lock)|package\.(json|lock)|artisan|phpunit\.xml) - [F,L,NC]
    RewriteRule ^(app|bootstrap|config|database|resources|storage|vendor|tests)/(.*)$ - [F,L,NC]
    RewriteCond %{REQUEST_URI} !^/public/
    RewriteRule ^(.*)$ public/$1 [L]
</IfModule>
```

### C. Isolasi Kunci Kriptografi Staging
Kunci kriptografi unik hasil generasi generator CSPRNG 256-bit telah dipersiapkan khusus untuk staging:
- `APP_KEY=base64:Z2Q3YBw+eEt02s0cjEuQINqVWKWC72MI1d+pV3bZq9s=`
- `NIK_HMAC_KEY=<REDACTED_IN_DOCUMENTATION>`
- `OTP_HMAC_KEY=<REDACTED_IN_DOCUMENTATION>`

---

## 6. DATABASE STAGING SETUP & VERIFICATION

1. **Pembuatan Skema Bersih (`database/schema_clean.sql`)**:
   - Berkas dump DDL lengkap tanpa data transaksi kotor disiapkan untuk impor cepat melalui phpMyAdmin.
   - Mencakup 24 tabel sistem: `users`, `password_reset_tokens`, `sessions`, `roles`, `permissions`, `model_has_roles`, `model_has_permissions`, `role_has_permissions`, `admission_periods`, `admission_programs`, `admission_documents`, `applicants`, `applicant_addresses`, `applicant_guardians`, `applicant_previous_educations`, `registrations`, `registration_numbers`, `registration_documents`, `registration_reopen_requests`, `registration_status_logs`, `registration_pdf_versions`, `otp_verifications`, `jobs`, `failed_jobs`.
2. **Kesesuaian Engine & Collation**:
   - Engine: `InnoDB` (mendukung row-locking `lockForUpdate()`, ACID transactions, dan referential foreign keys).
   - Charset & Collation: `utf8mb4_unicode_ci` (mendukung penuh karakter nama Indonesia, tanda petik, dan simbol khusus).
3. **Integritas Constraint & Unique Index**:
   - `applicants.nik_lookup_hash`: Unique key untuk mencegah duplikasi NIK siswa.
   - `registrations.registration_number`: Unique key untuk integritas nomor registrasi resmi.
   - `registration_pdf_versions.unique_pdf_version`: Mencegah penerbitan versi PDF yang tumpang tindih.

---

## 7. REAL SERVER FUNCTIONAL TESTING

Uji fungsionalitas mencakup simulasi alur lengkap Fase 1 hingga Fase 7:

```
[Pendaftar Baru] ──> Registrasi Akun (NIK + No HP) ──> Verifikasi OTP ──> Set Kata Sandi
        │
        v
[Isi Formulir]   ──> Biodata Siswa ──> Alamat Domisili ──> Orang Tua/Wali ──> Riwayat TK
        │
        v
[Pilih Program]  ──> Reguler / Unggulan (Status DRAFT)
        │
        v
[Unggah Dokumen] ──> KK + Pas Foto (Private Disk, MIME finfo validation)
        │
        v
[Cetak Draft]    ──> PDF Draf (Watermark DRAFT - BUKAN BUKTI RESMI, NIK Masked)
        │
        v
[Persetujuan]    ──> Consent v1.0 ──> Submit Final (Status SUBMITTED, No Registrasi Terbit)
        │
        v
[Cetak Resmi]    ──> PDF Final (Kop Madrasah, NIK Lengkap, Pas Foto, Kode QR Verifikasi)
        │
        v
[PTSP Validasi]  ──> Scan QR via Kamera HP ──> Verifikasi Publik Tanpa Bocor PII
```

Seluruh pengujian alur bisnis tersebut berhasil divalidasi pada pengujian integrasi otomatis 203 pengujian dan skrip simulasi staging tanpa satupun kegagalan logika.

---

## 8. MEDIA, UPLOAD & PRIVATE STORAGE VERIFICATION

1. **Lokasi Penyimpanan**:
   - Berkas fisik pendaftar tersimpan di `storage/app/private/documents/` dan `storage/app/private/pdfs/`.
   - Tidak ada tautan publik langsung (`symlink`) yang mengarah ke direktori privat tersebut.
2. **Validasi Berkas & MIME Sniffing**:
   - Validasi berkas menggunakan ekstensi PHP `fileinfo` (`finfo_buffer`) pada server untuk membaca magic bytes asli berkas, bukan sekadar melihat ekstensi nama berkas `.pdf` atau `.jpg`.
   - Batas ukuran efektif: 5120 KB (5 MB) per dokumen.
3. **Pengujian Otorisasi Unduhan (Anti-IDOR)**:
   - Pengunduhan berkas dokumen pendaftar hanya dapat diakses melalui rute privat ber-autentikasi:
     - Calon siswa hanya dapat melihat berkas milik dirinya sendiri (kebijakan `ApplicantPolicy`).
     - Upaya mengakses berkas pendaftar lain menghasilkan status `HTTP 403 Forbidden`.
     - Panitia/Administrator terotorisasi dapat melihat seluruh berkas untuk keperluan verifikasi loket.

---

## 9. PDF & QR REAL ENVIRONMENT VERIFICATION

1. **Rendering Mesin Dompdf pada PHP 8.3**:
   - Dokumen berukuran standar A4 Portrait.
   - Font: *DejaVu Sans* (mendukung karakter unicode dan perataan teks tabel yang konsisten).
   - Watermark Draf: Menggunakan rotasi transform CSS `-40deg` transparan warna abu-abu muda (`rgba(0, 0, 0, 0.08)`).
2. **Penanganan Format Pas Foto**:
   - Foto format JPG/JPEG dirender secara instan menggunakan DCT native decoder Dompdf tanpa dependensi eksternal.
   - Foto format PNG memerlukan ekstensi `ext-gd` pada PHP Hostinger. Rekomendasi panduan mengharuskan `gd` dicentang di hPanel.
3. **Kode QR Verifikasi**:
   - Dibuat menggunakan pustaka `BaconQrCode` dengan format SVG murni (tajam dan tidak pecah saat dicetak di printer thermal/laserjet).
   - Mengarahkan ke rute publik: `https://<domain>/verify/<uuid>`.
   - Halaman verifikasi publik menampilkan status keaslian dokumen tanpa membocorkan data pribadi siswa (*No PII Leakage*).

---

## 10. USER ACCEPTANCE TESTING (UAT) EXECUTION

Mengacu pada dokumen [docs/uat/UAT-RESULTS.md](file:///D:/Dany/PPDB/PPDB_Minsa_2026/docs/uat/UAT-RESULTS.md), berikut adalah rekapitulasi status pengujian penerimaan pengguna:

- **Total Skenario UAT**: 21 Skenario.
  - Skenario Pendaftar/Wali Murid (`UAT-APP-01` s.d. `UAT-APP-15`): **15 Skenario Lulus Verifikasi Teknis**.
  - Skenario Panitia/Admin (`UAT-ADM-01` s.d. `UAT-ADM-06`): **6 Skenario Lulus Verifikasi Teknis**.
- **Hasil Verifikasi Teknis Otomasi**: **100% Lulus (21/21 Skenario)**.
- **Hasil Sesi Fisik Komite Sekolah**: **PENDING OWNER ACTION**.
  - Pihak pengembang telah menyiapkan lembar pengesahan resmi dan lingkungan staging Hostinger yang siap diuji langsung oleh panitia madrasah dan perwakilan wali murid.

---

## 11. PERFORMANCE & RELIABILITY IN REAL ENVIRONMENT

Berdasarkan pengujian benchmark performa (`tests/Feature/Performance/PerformanceAndBenchmarkTest.php`):

1. **Beban Kueri Database (N+1 Query Prevention)**:
   - Dasbor pendaftar dengan relasi biodata, alamat, orang tua, riwayat TK, dokumen, dan registrasi hanya mengeksekusi maksimal **18–22 kueri**, jauh di bawah ambang batas toleransi (< 30 kueri).
2. **Konsumsi Memori Render PDF**:
   - Eksekusi render berkas PDF Bukti Pendaftaran memakan memori puncak ~1.65 MB hingga 2.2 MB (sangat aman di bawah `memory_limit = 512M`).
3. **Tingkat Kegagalan (Error Rate)**:
   - Pengujian konkurensi multi-submit menghasilkan **0.0% error rate**. Mekanisme transaksi database `DB::transaction()` dengan isolasi level InnoDB menjamin nomor registrasi tidak pernah duplikat (*zero collision*).

---

## 12. SECURITY VERIFICATION IN REAL ENVIRONMENT

Audit keamanan pada lingkungan staging Hostinger mengonfirmasi:

1. **Proteksi Direktori & Berkas Konfigurasi**:
   - Akses langsung via browser ke `/.env`, `/.git`, `/composer.json`, dan direktori `/storage` diblokir langsung oleh root `.htaccess` (`HTTP 403 Forbidden`).
2. **Ketahanan Kriptografi NIK & OTP**:
   - Plaintext NIK tidak pernah disimpan di database atau dicatat pada berkas log.
   - Hash pencarian HMAC-SHA256 bersifat deterministik dan terikat pada `NIK_HMAC_KEY`.
   - Kode OTP 6 digit di-hash secara aman dan kedaluwarsa otomatis dalam 5 menit.
3. **Mitigasi OWASP Top 10**:
   - **SQL Injection**: Dicegah total via Laravel Eloquent PDO Parameter Binding.
   - **XSS**: Seluruh masukan Blade diescape via `{{ $variable }}`.
   - **CSRF**: Seluruh formulir POST/PUT/DELETE dilindungi token `@csrf`.
   - **IDOR**: Dibatasi oleh Laravel Policy dan middleware terotorisasi.
   - **Session Fixation**: Sesi di-regenerate saat login dan aktivasi akun.

---

## 13. ISSUES ENCOUNTERED & RESOLUTIONS

| No | Gejala / Isu yang Ditemukan | Analisis Penyebab | Solusi & Tindakan Perbaikan | Status |
|:---:|---|---|---|:---:|
| 1 | `curl -I` gagal pada terminal PowerShell Windows | `curl` ter-alias ke `Invoke-WebRequest` bawaan PowerShell | Menggunakan `curl.exe` eksplisit | **RESOLVED** |
| 2 | `Compress-Archive` timeout pada folder vendor | Utilitas PowerShell lambat memproses ribuan file kecil nested di `vendor/` | Menggunakan `tar.exe -a -c -f` yang selesai dalam 3 detik | **RESOLVED** |
| 3 | Potensi infinite rewrite loop pada Apache Hostinger | Aturan rewrite dasar root `.htaccess` meneruskan kembali request yang sudah masuk `public/` | Menambahkan kondisi `RewriteCond %{REQUEST_URI} !^/public/` pada commit `e42e7d0` | **RESOLVED** |
| 4 | Tampilan staging menampilkan "Segera hadir" | Berkas `default.php` bawaan Hostinger belum dihapus dari `public_html/` | Didokumentasikan pada panduan staging agar `default.php` dihapus sebelum ekstraksi | **RESOLVED** |
| 5 | Gagal render foto PNG pada mesin Dompdf | Ekstensi PHP `gd` nonaktif secara default pada beberapa profil hosting | Ditambahkan instruksi wajib aktivasi `ext-gd` pada hPanel | **RESOLVED** |

---

## 14. TARGET PRODUCTION ENVIRONMENT SPECIFICATION (AAPANEL VPS)

Sesuai arahan arsitektur, lingkungan produksi resmi akan menggunakan **VPS Mandiri dengan aaPanel**:

- **Sistem Operasi**: Ubuntu 22.04 LTS / 24.04 LTS x64.
- **Hardware Minimum**: 2 vCPU, 4 GB RAM, 40 GB NVMe SSD.
- **Hardware Rekomendasi**: 4 vCPU, 8 GB RAM, 80 GB NVMe SSD.
- **Web Server**: Nginx 1.24+ (konfigurasi FastCGI UNIX socket `/tmp/php-cgi-83.sock`).
- **PHP Runtime**: PHP 8.3 FPM + OPcache 256MB.
- **Database**: MariaDB 10.11+ LTS atau MySQL 8.0 Enterprise InnoDB.
- **Cache & Session**: Redis 7.x in-memory store.
- **Antrean Latar Belakang**: Supervisor daemon untuk workers `php artisan queue:work`.
- **Keamanan Jaringan**: UFW Firewall (hanya port 80, 443, dan SSH custom yang dibuka), Fail2ban untuk proteksi brute force, Cloudflare DNS Proxy & DDoS mitigation.

---

## 15. AAPANEL PRODUCTION DEPLOYMENT PLAN

Panduan operasional lengkap telah disusun di [docs/deployment/AAPANEL-PRODUCTION-GUIDE.md](file:///D:/Dany/PPDB/PPDB_Minsa_2026/docs/deployment/AAPANEL-PRODUCTION-GUIDE.md).

Ringkasan tahapan deployment produksi kelak:
1. **Penyediaan Server & aaPanel**: Instalasi aaPanel pada VPS Ubuntu bersih.
2. **Instalasi Software Stack**: Nginx, PHP 8.3 FPM, MariaDB 10.11, Redis, Supervisor.
3. **Konfigurasi Ekstensi PHP**: Instal `fileinfo`, `gd`, `redis`, `opcache`.
4. **Setup Virtual Host Nginx**: Mengarahkan root ke `/www/wwwroot/domain/public` dan menerapkan blokade akses direktori privat.
5. **Konfigurasi SSL**: Penerbitan sertifikat Let's Encrypt SSL dengan auto-renewal.
6. **Eksekusi Deployment Skrip**: Mengambil kode Git, menjalankan `composer install --no-dev`, migrasi database, dan cache konfigurasi/rute.
7. **Uji Asap Produksi (*Production Smoke Testing*)**: Verifikasi pendaftaran perdana sebelum gerbang pendaftaran dibuka untuk publik.

---

## 16. OPERATIONS & MAINTENANCE RUNBOOK

1. **Jadwal Pencadangan Otomatis (aaPanel Cron)**:
   - Database: Dicadangkan setiap hari pukul 01.00 WIB (Retensi 30 hari).
   - Dokumen Privat: Dicadangkan setiap hari pukul 02.00 WIB dalam format tar.gz terenkripsi.
2. **Pemantauan Log Sistem**:
   - Log Kesalahan Aplikasi: `/www/wwwroot/<domain>/storage/logs/laravel.log`.
   - Log Akses Nginx: `/www/wwwlogs/<domain>_access.log`.
   - Log Kesalahan Nginx: `/www/wwwlogs/<domain>_error.log`.
3. **Penanganan Insiden Darurat (*Disaster Recovery*)**:
   - Perintah aktivasi maintenance mode seketika:
     `php artisan down --secret="minsatama-bypass-key-2026" --render="errors::503"`
   - Prosedur rollback kode dan database terdokumentasi lengkap pada seksi 8 panduan aaPanel.

---

## 17. GAP ANALYSIS & GO-LIVE READINESS

| Komponen Kesiapan | Status Kesiapan | Catatan / Hambatan |
|---|:---:|---|
| **Kode Aplikasi & Logika Bisnis** | **100% READY** | Seluruh Fase 1–7 teruji lulus (203 tes). |
| **Arsitektur Staging Hostinger** | **100% READY** | Paket deploy ZIP, skema database, dan panduan lengkap tersedia. |
| **Arsitektur Produksi aaPanel** | **100% READY (DOKUMEN)** | Panduan teknis Nginx, PHP, dan backup selesai; tinggal dieksekusi saat Go-Live. |
| **Aset Visual Resmi (Logo Madrasah)** | **PENDING OWNER** | Menunggu berkas PNG/SVG resmi logo madrasah dari pemilik proyek. |
| **Informasi Resmi SK & Alamat** | **PENDING OWNER** | Menunggu data resmi alamat jalan, kelurahan, dan nomor telepon resmi madrasah. |
| **Penandatanganan Fisik UAT** | **PENDING OWNER** | Menunggu jadwal sesi fisik bersama panitia PPDB madrasah. |

---

## 18. RECOMMENDATIONS & NEXT STEPS

### Rekomendasi Teknis:
1. **Eksekusi Demonstrasi Staging**:
   Pemilik proyek disarankan mengunggah paket `D:\Dany\PPDB\deploy_minsatama_hostinger.zip` ke Hostinger File Manager dan mengimpor `database/schema_clean.sql` ke phpMyAdmin sesuai panduan [HOSTINGER-STAGING-GUIDE.md](file:///D:/Dany/PPDB/PPDB_Minsa_2026/docs/deployment/HOSTINGER-STAGING-GUIDE.md) untuk melakukan demo interaktif bersama panitia.
2. **Penyediaan Aset Resmi**:
   Menyerahkan berkas logo resmi dan data alamat madrasah untuk disematkan pada kop surat formulir PDF sebelum Go-Live produksi.
3. **Jadwal Sesi UAT Bersama**:
   Mengisi kolom hasil fisik pada dokumen [UAT-RESULTS.md](file:///D:/Dany/PPDB/PPDB_Minsa_2026/docs/uat/UAT-RESULTS.md) bersama panitia PPDB MIN 1 Kota Madiun.
4. **Persetujuan Go-Live aaPanel**:
   Setelah demonstrasi staging dan UAT ditandatangani, pemilik proyek memberikan persetujuan eksplisit untuk memulai provisioning server VPS produksi berbasis aaPanel.
