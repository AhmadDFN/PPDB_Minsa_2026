# KNOWN ISSUES & LIMITATIONS — PPDB MINSATAMA
## Catatan Kendala, Batasan Lingkungan, dan Rencana Mitigasi
### Terakhir Ditinjau: 19 November 2026 (Fase 9)

---

## 1. DAFTAR ISU & BATASAN SAAT INI

### 1.1 Status Dependensi NPM (picomatch advisory)
- **Status**: TERSELESAIKAN (RESOLVED)
- **Tingkat Keparahan**: Tidak ada (Clean)
- **Verifikasi**:
  Eksekusi `npm.cmd audit` dan `npm.cmd audit --omit=dev` pada 19 November 2026 menghasilkan **0 vulnerabilities**. Seluruh rantai dependensi build dan runtime dinyatakan bersih dari kerentanan.

### 1.2 Batasan Verifikasi Fisik Database Produksi (MySQL 8.x)
- **Status**: **READY FOR STAGING IMPORT (Skema DDL schema_clean.sql tersedia)**
- **Tingkat Keparahan**: Kepatuhan Operasional Produksi
- **Deskripsi**:
  Pengujian fisik basis data pada Fase 6–8 dijalankan menggunakan server lokal MariaDB 10.4.32/InnoDB (`ppdb_phase6_test` pada `127.0.0.1:3306`), dengan 10 tes skema, migrasi Fase 1–7, indeks unik, foreign keys, ukuran default 5120 KB, dan submit konkuren multi-proses seluruhnya **LULUS (10 Passed, 122 Assertions)**.
  Pada Fase 9, berkas skema bersih `database/schema_clean.sql` telah disiapkan dan siap diimpor ke database Hostinger / VPS MySQL 8.
- **Rencana Mitigasi**:
  Prosedur deployment dan verifikasi migrasi telah disiapkan pada `docs/deployment/HOSTINGER-STAGING-GUIDE.md` dan `docs/deployment/AAPANEL-PRODUCTION-GUIDE.md`.

### 1.3 Nomor Sequence Lintas Periode
- **Status**: SELESAI & TERVERIFIKASI
- **Deskripsi**:
  Counter dibagikan per tahun ajaran dan kode program (`REGULER` / `UNGGULAN`) melalui baris unik pada `registration_number_sequences`. Penguncian transaksional `lockForUpdate()` mencegah tabrakan nomor antarperiode maupun konkurensi multi-pendaftar.
- **Verifikasi**:
  Uji konkurensi multi-proses MariaDB dan E2E Scenario 2 membuktikan nomor berurutan dan terisolasi sempurna.

### 1.4 Batas Unggah PHP Hosting di Bawah Kebijakan Aplikasi
- **Status**: PRASYARAT KONFIGURASI SERVER PRODUKSI
- **Tingkat Keparahan**: Operasional Server
- **Deskripsi**:
  Kebijakan aplikasi mengizinkan berkas hingga 5120 KB (5 MB). Konfigurasi bawaan PHP default biasanya `upload_max_filesize=2M` dan `post_max_size=8M`. Jika nilai ini tidak dinaikkan di server hosting, PHP akan menolak berkas sebelum mencapai lapisan validasi FormRequest Laravel.
- **Rencana Mitigasi**:
  Administrator server wajib mengatur `upload_max_filesize = 16M` dan `post_max_size = 20M` pada `php.ini` atau panel cPanel/Hostinger/aaPanel sebagaimana dirinci dalam `docs/deployment/HOSTINGER-STAGING-GUIDE.md`.

### 1.5 Integrasi Pengiriman OTP WhatsApp Fonnte
- **Status**: IMPLEMENTED + LIVE TESTED + OWNER CONFIRMED
- **Tingkat Keparahan**: Info / Operasional
- **Deskripsi**:
  Driver Fonnte tersedia dan pemilik proyek mengonfirmasi pengiriman OTP berhasil pada pengujian langsung. Driver `log`/`array` tetap digunakan untuk mode lokal dan automated test.
- **Rencana Mitigasi**:
  Pertahankan timeout, validasi respons, redaksi log, dan uji regresi. Jangan mencatat token gateway atau OTP plaintext pada laporan.

### 1.6 Aset Identitas Resmi Madrasah (Logo & Alamat Lengkap)
- **Status**: PENDING OWNER ACTION
- **Tingkat Keparahan**: Tampilan / Branding PDF
- **Deskripsi**:
  Logo resmi Kementerian Agama dan MIN 1 Kota Madiun serta alamat jalan lengkap madrasah belum tersedia sebagai berkas sumber terverifikasi di repositori. Saat ini kop PDF Bukti Pendaftaran menggunakan teks nama institusi resmi yang terdokumentasi tanpa merekayasa logo fiktif.
- **Rencana Mitigasi**:
  Menunggu penyerahan berkas logo resmi dalam format PNG/SVG transparan dan teks alamat resmi dari pemilik proyek.

### 1.7 Rendering Pas Foto PNG Memerlukan Ekstensi PHP GD
- **Status**: PRASYARAT OPERASIONAL HOSTING
- **Deskripsi**:
  Dompdf memerlukan ekstensi PHP `gd` untuk menanam gambar PNG ke dalam dokumen PDF. Pas foto format JPG dapat dirender tanpa ekstensi GD (menggunakan DCT decode bawaan PDF). Pada lingkungan tanpa GD, pas foto PNG ditolak secara elegan dengan pesan informatif: *"Server belum mendukung pas foto PNG. Aktifkan ekstensi PHP GD atau unggah pas foto dalam format JPG."*
- **Rencana Mitigasi**:
  Pastikan ekstensi PHP `gd` diaktifkan pada panel PHP Extensions di hosting staging Hostinger dan aaPanel VPS.

### 1.8 Berkas default.php Hostinger Menghalangi Routing Laravel
- **Status**: TERSELESAIKAN (RESOLVED IN DOCUMENTATION)
- **Deskripsi**:
  Domain baru di Hostinger memuat berkas `public_html/default.php` bertuliskan "Segera hadir". Jika berkas ini tidak dihapus, request HTTP GET akan disajikan langsung oleh web server tanpa melalui routing Laravel.
- **Rencana Mitigasi**:
  Panduan deployment mewajibkan penghapusan berkas `default.php` sebelum mengekstrak paket rilis.

### 1.9 Potensi Loop Rewrite pada Root .htaccess Apache/LiteSpeed
- **Status**: TERSELESAIKAN (RESOLVED IN COMMIT e42e7d0)
- **Deskripsi**:
  Aturan rewrite root tanpa kondisi pengecualian direktori dapat menyebabkan infinite rewrite loop internal pada server LiteSpeed/Apache.
- **Rencana Mitigasi**:
  Ditambahkan kondisi `RewriteCond %{REQUEST_URI} !^/public/` sebelum aturan rewrite ke `public/$1`.

### 1.8 Persyaratan APP_URL Publik HTTPS untuk QR Final
- **Status**: PRASYARAT OPERASIONAL PRODUKSI
- **Deskripsi**:
  Pembuatan PDF final pada lingkungan `APP_ENV=production` menolak konfigurasi `APP_URL` non-HTTPS, host `localhost`, atau IP privat agar QR verifikasi pada cetakan fisik selalu mengarah ke domain publik yang valid.
- **Rencana Mitigasi**:
  Pastikan `APP_URL` di `.env` produksi diisi URL domain HTTPS resmi madrasah sebelum mencetak PDF final.

---

## 2. PROSEDUR PELAPORAN ISU BARU

Jika ditemukan anomali baru:
1. Dokumentasikan langkah reproduksi secara detail.
2. Lampirkan log error dari `storage/logs/laravel.log`.
3. Buat skenario automated test yang mereplikasi anomali tersebut.
4. Lakukan perbaikan kode hingga seluruh test suite (203+ tests) lulus.
5. Perbarui berkas `KNOWN-ISSUES.md`.

### 1.10 Status Rekonsiliasi Hostinger dan UAT
- **Status**: APPLICANT PASS WITH BLOCKER; ADMIN/SUPERADMIN PENDING VERIFICATION
- Hostinger online/testing telah dikonfirmasi pada `https://ahmaddanyfn.biz.id` dengan Auto Deploy dan database aktif.
- Applicant live test berhasil sampai upload dokumen, PDF draft, dan halaman tinjau.
- Consent, Final Submit, PDF final, QR, serta uji operasional Admin/Superadmin belum dikonfirmasi pada laporan live terakhir.

### 1.11 Endpoint Migrasi HTTP
- **Status**: DIHAPUS DARI SOURCE LOKAL
- Endpoint GET migrasi produksi dengan token statis telah dihapus. Migrasi hanya boleh dijalankan melalui CLI setelah backup dan review.
