# TODO ROADMAP â€” PPDB MINSATAMA
## Peta Jalan Pengembangan dan Rencana Tugas Berkelanjutan

**Status Notasi**:
- `[x]` Selesai (*Completed*)
- `[-]` Sedang Berjalan (*In Progress*)
- `[ ]` Menunggu (*Pending*)
- `[!]` Terhambat (*Blocked*)

---

## 1. TUGAS SELESAI (COMPLETED TASKS)

- [x] **[P0 - Kritis] Inisialisasi Struktur Dokumentasi Master**: Membuat berkas master `AGENTS.md`, `PROJECT_BLUEPRINT.md`, `PROJECT_STATUS.md`, `TODO_ROADMAP.md`, `BUSINESS_REQUIREMENTS.md`, `SYSTEM_ARCHITECTURE.md`, `DATABASE_DESIGN.md`, `SECURITY_AND_TESTING.md`, `TECHNICAL_DECISIONS.md`, `DEVELOPMENT_GUIDE.md`.
- [x] **[P0 - Kritis] Audit Repositori & Test Suite Fase 7**: Regresi Fase 7: 192 lulus, 924 asersi, 0 gagal/skip; 13 tes PDF terarah lulus. Pint, build, dan Composer audit lulus; NPM audit tetap 6 high development/build dan 0 temuan production-only. Verifikasi 9 tes MariaDB/InnoDB merupakan hasil Fase 6, bukan uji migration PDF.
- [x] **[P1 - Tinggi] Service & Action Layer Biodata Siswa (Fase 4)**:
  - `app/Actions/Applicant/UpdateApplicantBiodataAction.php` untuk melengkapi data nama, tempat/tanggal lahir, jenis kelamin, NISN, anak ke-, jumlah saudara. NIK terkunci dan read-only.
- [x] **[P1 - Tinggi] Form & Action Data Alamat Domisili (Fase 4)**:
  - `app/Actions/Applicant/SaveApplicantAddressAction.php` untuk menyimpan alamat jalan, RT/RW (preserves leading zeroes), kelurahan, kecamatan, kota, provinsi, kode pos, jarak ke sekolah.
- [x] **[P1 - Tinggi] Form & Action Data Orang Tua & Wali (Fase 4)**:
  - `app/Actions/Applicant/SaveApplicantParentsAction.php` mewajibkan data Ayah Kandung dan Ibu Kandung (Nama, NIK, Pendidikan, Pekerjaan, Penghasilan, No. HP), serta Wali opsional. Menangani toggle aktif/nonaktif wali secara atomik via DB transaction.
- [x] **[P1 - Tinggi] Form & Action Riwayat Pendidikan TK/RA Asal (Fase 4)**:
  - `app/Actions/Applicant/SaveApplicantEducationAction.php` mencatat jenjang RA, TK, PAUD, Lainnya, atau Tidak Sekolah (NONE).
- [x] **[P1 - Tinggi] Pemilihan Program Pendidikan (Fase 4)**:
  - `app/Actions/Applicant/SelectAdmissionProgramAction.php` menghubungkan pendaftaran dengan program periode aktif (Unggulan / Reguler) sebagai status `DRAFT`.
- [x] **[P1 - Tinggi] Kalkulasi Umur Dinamis (Runtime Calculation) (Fase 4)**:
  - Menggunakan metode `Applicant::calculateAgeAt($admissionPeriod->age_reference_date)` untuk menampilkan usia siswa (Tahun, Bulan, Hari) pada UI tanpa kolom usia permanen di database.
- [x] **[P1 - Tinggi] Dasbor Pendaftar dengan Progress Bar Dinamis (Fase 4)**:
  - `ApplicantProfileService::getCompletionSummary()` menghitung persentase kelengkapan 0-100%, status per modul (Lengkap / Belum Lengkap / Belum Diisi), dan banner periode aktif.
- [x] **[P1 - Tinggi] Manajemen Dokumen Privat (Fase 5)**:
  - Persyaratan per periode/program, kelengkapan, validasi 5120 KB/per-dokumen, SHA-256, upload/replacement, revisi historis, dan preview/download terotorisasi.

---

## 2. HASIL FASE 5 â€” MANAJEMEN BERKAS & DOKUMEN PENDAFTARAN

Persetujuan Fase 5 telah diberikan. Hasil dan pengujian dicatat di `docs/phases/PHASE-5-IMPLEMENTATION-REPORT.md`.

- [x] Disk privat tanpa URL publik dan route pemilik untuk preview/unduh.
- [x] Retrieval persyaratan per periode/program dan service kelengkapan dokumen.
- [x] Upload/replacement dengan validasi MIME aktual, ekstensi, batas efektif, dan SHA-256.
- [x] Riwayat revisi tanpa menghapus berkas lama; cleanup berkas baru saat persistensi database gagal.
- [x] Antarmuka dokumen pendaftar dan integrasi progress dashboard.
- [x] Maksimum global/default 5120 KB; batas lebih rendah per dokumen; migration tambahan untuk default kolom.
- [x] Uji batas unggah, format/MIME, IDOR, replacement, cleanup, dan dashboard.

**Di luar cakupan Fase 5**: submit final, nomor registrasi, PDF, seleksi, persetujuan dokumen, dan pelaporan administrator.

**Di luar cakupan Fase 6**: PDF draft/final, seleksi, persetujuan dokumen, dan pelaporan administrator.

---

## 3. TUGAS FASE LANJUTAN (FUTURE PHASE TASKS)

### Fase 6 â€” Alur Pendaftaran (Draft, Validasi Kelengkapan & Submit Final)
- [x] **[P1 - Tinggi] Sistem Status Formulir (DRAFT & SUBMITTED)**:
  - *Deskripsi*: Menegakkan aturan bahwa formulir dapat disimpan sebagai DRAFT berkali-kali.
- [x] **[P1 - Tinggi] Engine Validasi Kelengkapan Pra-Submit**:
  - *Deskripsi*: Pengecekan seluruh modul (Biodata, Ortu, Alamat, TK, Program, Berkas Wajib) sebelum pendaftar diizinkan menekan tombol "Kirim Pendaftaran Final".
- [x] **[P1 - Tinggi] Submit Final, Persetujuan, dan Audit Revisi**:
  - *Deskripsi*: Submit atomik/idempoten menyimpan persetujuan, waktu submit, status, nomor registrasi unik, dan log perubahan. Perubahan pasca-submit diizinkan hanya saat periode masih terbuka.
- [x] **[P1 - Tinggi] Penerbitan Nomor Registrasi Unik**:
  - *Deskripsi*: Format resmi `PPDB/{tahun-ajaran}/{kode-program}/{urutan-5-digit}` dengan kode `REGULER`/`UNGGULAN`. Sequence dikunci dan dibagikan per tahun ajaran-program agar periode ganda pada tahun yang sama tidak menghasilkan nomor bentrok.
- [x] **[P1 - Tinggi] Halaman Tinjauan dan Integrasi Dasbor**:
  - *Deskripsi*: Menampilkan ringkasan aman, NIK termasker, kesiapan pendaftaran, nomor dan status setelah submit.
- [x] **[P1 - Tinggi] Persetujuan Submit Resmi**:
  - *Deskripsi*: Redaksi consent versi `v1.0` disimpan bersama pendaftaran saat submit.
- [x] **[P1 - Tinggi] Admin Reopen per Pendaftaran**:
  - *Deskripsi*: Admin berizin dapat membuka akses edit untuk pendaftaran yang telah dikirim, dengan alasan dan kedaluwarsa wajib; akses dapat ditutup lebih awal dan semua tindakan diaudit.
- [x] **[P1 - Tinggi] Verifikasi Migrasi dan Submit Konkuren MariaDB/InnoDB**:
  - *Deskripsi*: Sembilan tes fisik mencakup migrasi/backfill counter, skema, default 5120 KB, submit multi-proses pada pendaftaran sama dan periode berbeda, serta rollback/retry.

**Status Fase 6: CLOSED.** Seluruh keputusan bisnis telah diterapkan; 179 tes lulus, termasuk 9 tes MariaDB/InnoDB. Lihat `docs/phases/PHASE-6-FINAL-CLOSURE-REPORT.md`.

### Fase 7 â€” Pembuatan Dokumen Bukti Pendaftaran (PDF Generator)
- [x] **[P1 - Tinggi] Desain Layout PDF Bukti Pendaftaran**:
  - *Deskripsi*: PDF A4 dengan kop berbasis nama institusi terverifikasi, ringkasan biodata, foto bila tersedia, dan ruang tanda tangan. Logo/alamat resmi belum tersedia di repositori dan tidak direkayasa.
- [x] **[P1 - Tinggi] Watermark DRAFT Otomatis**:
  - *Deskripsi*: Jika status masih DRAFT, PDF memuat watermark besar transparan bertuliskan "DRAFT - BUKAN BUKTI RESMI".
- [x] **[P1 - Tinggi] PDF Final & Versioning**:
  - *Deskripsi*: Jika status SUBMITTED, PDF diterbitkan sebagai bukti pengiriman (bukan keputusan seleksi), dicatat pada `registration_pdf_versions`, dan versi lama dipertahankan.
- [x] **[P1 - Tinggi] Penyimpanan Privat dan Akses PDF**:
  - *Deskripsi*: File PDF disimpan tanpa URL publik; unduhan pendaftar memeriksa ownership, MIME, ukuran, dan SHA-256.
- [x] **[P1 - Tinggi] QR Verifikasi Minimal**:
  - *Deskripsi*: PDF final memuat token acak, hash token disimpan di basis data, dan halaman verifikasi publik hanya menampilkan nomor, tanggal, fingerprint, serta status.
- [x] **[P1 - Tinggi] Dashboard dan Revisi PDF**:
  - *Deskripsi*: Pendaftar dapat membuat PDF draft/final, melihat riwayat versi, dan mengetahui jika versi final tertinggal dari revisi data.
- [x] **[P1 - Tinggi] Uji Regresi Fase 7**:
  - *Deskripsi*: 13 tes PDF terarah dan regresi lengkap 192 tes lulus pada SQLite; MySQL 8 fisik belum diverifikasi.

**Status Fase 7: CLOSED.** Implementasi dan tes selesai setelah otorisasi pemilik. Operasional produksi memerlukan `APP_URL` HTTPS publik untuk QR; PNG memerlukan ekstensi GD, sementara JPG telah dirender pada lingkungan lokal tanpa GD/Imagick. Logo/alamat resmi menunggu sumber resmi dari pemilik. Laporan: `docs/phases/PHASE-7-IMPLEMENTATION-REPORT.md`.

### Fase 8 — Integrasi Sistem, UAT & Kesiapan Produksi (System Integration & Readiness)
- [x] **[P0 - Kritis] Uji Integrasi Menyeluruh End-to-End (E2E Testing)**:
  - *Deskripsi*: 7 skenario pengujian komprehensif (`EndToEndRegistrationIntegrationTest.php`): pendaftaran normal, isolasi sequence jalur unggulan, validasi kelengkapan pra-submit, revisi data dan PDF stale, alur admin reopen & kedaluwarsa server-side, proteksi IDOR & isolasi multi-tenant, serta failure & recovery. (7 Passed, 121 Assertions).
- [x] **[P0 - Kritis] Audit Keamanan Menyeluruh (OWASP & Security Review)**:
  - *Deskripsi*: Evaluasi 18 vektor keamanan web (kriptografi NIK AES-256-CBC & HMAC lookup, OTP binding, CSRF, XSS, SQLi, Mass Assignment, proteksi upload & MIME finfo aktual, sandboxing Dompdf, privasi QR publik). Laporan: `docs/audits/SECURITY-AUDIT.md`.
- [x] **[P1 - Tinggi] Benchmark Kinerja & Reliabilitas**:
  - *Deskripsi*: Uji query N+1 pada dasbor (bounded <= 30 kueri), profil memori dan ukuran PDF Dompdf (DejaVu font subsetting), dan uji beban request sintetis (error rate 0.0%). Berkas: `PerformanceAndBenchmarkTest.php`.
- [x] **[P1 - Tinggi] Verifikasi Fisik MySQL/MariaDB**:
  - *Deskripsi*: 10 tes skema, foreign keys, indeks, ukuran default 5120 KB, dan uji konkurensi multi-proses transaksi InnoDB `SELECT ... FOR UPDATE` lulus pada MariaDB 10.4.32. Gerbang fisik MySQL 8.x ditandai BLOCKED menunggu server staging MySQL 8.
- [x] **[P1 - Tinggi] Rencana Uji Penerimaan Pengguna (UAT Test Plan)**:
  - *Deskripsi*: Berkas panduan UAT non-teknis untuk panitia madrasah dan wali murid (`docs/uat/UAT-TEST-PLAN.md`) dengan checklist 15 skenario pendaftar dan 6 skenario admin.
- [x] **[P1 - Tinggi] Checklist & Panduan Kesiapan Produksi**:
  - *Deskripsi*: Penyusunan checklist environment hosting (`docs/deployment/PRODUCTION-READINESS-CHECKLIST.md`), panduan deployment langkah demi langkah (`docs/deployment/DEPLOYMENT-GUIDE.md`), dan panduan backup-restore (`docs/deployment/BACKUP-AND-RESTORE.md`).

**Status Fase 8: CLOSED.** 203 pengujian lulus (100%), 1065 asersi, 0 failed, 0 skipped. Pint, Vite build, Composer audit, dan NPM audit seluruhnya lulus tanpa celah keamanan.

### Fase 9 — Hostinger Staging Deployment & User Acceptance Testing (Staging & UAT)
- [x] **[P0 - Kritis] Audit Kesiapan Staging Hostinger**:
  - *Deskripsi*: Evaluasi kapabilitas server `sleek-forestgreen-mink-13.hostingersite.com` (PHP 8.3.36, LiteSpeed/Apache, memory limit 512M, SSL HTTPS aktif, prasyarat ekstensi `ext-gd`).
- [x] **[P0 - Kritis] Paket Deployment Mandiri & Skema Bersih MySQL**:
  - *Deskripsi*: Penyusunan arsip deploy `deploy_minsatama_hostinger.zip` (24 MB) memuat dependensi teroptimasi, aset Vite terkompilasi, konfigurasi `.env` staging, isolasi kunci kriptografi mandiri, serta skema database bersih `database/schema_clean.sql`.
- [x] **[P0 - Kritis] Proteksi Direktori & Konfigurasi Root .htaccess**:
  - *Deskripsi*: Penerapan aturan rewrite anti-loop `RewriteCond %{REQUEST_URI} !^/public/` dan pemblokiran akses langsung ke berkas sensitif (`.env`, `storage/`, `vendor/`, `artisan`). Commit: `e42e7d0`.
- [x] **[P1 - Tinggi] Panduan Operasional Staging Hostinger**:
  - *Deskripsi*: Penyusunan dokumen [docs/deployment/HOSTINGER-STAGING-GUIDE.md](file:///D:/Dany/PPDB/PPDB_Minsa_2026/docs/deployment/HOSTINGER-STAGING-GUIDE.md) mencakup metode ekstraksi ZIP, pembuatan database MySQL, seeding referensi, pengujian alur 1–7, dan pemecahan masalah.
- [x] **[P1 - Tinggi] Cetak Biru Produksi Mandiri aaPanel VPS**:
  - *Deskripsi*: Penyusunan panduan operasional produksi resmi [docs/deployment/AAPANEL-PRODUCTION-GUIDE.md](file:///D:/Dany/PPDB/PPDB_Minsa_2026/docs/deployment/AAPANEL-PRODUCTION-GUIDE.md) meliputi Nginx FastCGI, PHP 8.3 FPM, MariaDB 10.11/MySQL 8, Redis, Supervisor, Let's Encrypt SSL, zero-downtime deployment script, cron backup, dan rollback darurat.
- [x] **[P1 - Tinggi] Pelaporan Hasil Uji Penerimaan Pengguna (UAT Results)**:
  - *Deskripsi*: Pencatatan hasil evaluasi 21 skenario UAT pada [docs/uat/UAT-RESULTS.md](file:///D:/Dany/PPDB/PPDB_Minsa_2026/docs/uat/UAT-RESULTS.md) (21 skenario lulus verifikasi teknis otomatis; lembar pengesahan menunggu pelaksanaan fisik panitia madrasah).
- [x] **[P0 - Kritis] Laporan Komprehensif Fase 9 (18 Seksi)**:
  - *Deskripsi*: Penyusunan dokumen master [docs/phases/PHASE-9-IMPLEMENTATION-REPORT.md](file:///D:/Dany/PPDB/PPDB_Minsa_2026/docs/phases/PHASE-9-IMPLEMENTATION-REPORT.md).

**Status Fase 9: SELESAI SECARA TEKNIS (SIAP UAT FISIK SEKOLAH & MENUNGGU ARSITEKTUR AAPANEL PRODUKSI).** Paket rilis staging siap diuji di server Hostinger, dokumentasi produksi aaPanel lengkap. Dilarang melakukan deployment produksi ke aaPanel sebelum persetujuan resmi.

### Fase 10 — Dokumentasi Operasional, User Manual & SOP Kepanitiaan (Operational Docs)
- [x] **[P0 - Kritis] Audit Repositori & Hak Akses Aktual**:
  - *Deskripsi*: Verifikasi peran Spatie (`superadmin`, `admin_ppdb`) dan akun siswa (`applicant`), pemetaan rute, middleware, dan controller aktual tanpa asumsi peran fiktif.
- [x] **[P1 - Tinggi] User Manual Orang Tua / Wali**:
  - *Deskripsi*: Buku panduan lengkap 14 bab [docs/manual/01-PANDUAN-ORANG-TUA-WALI.md](file:///D:/Dany/PPDB/PPDB_Minsa_2026/docs/manual/01-PANDUAN-ORANG-TUA-WALI.md) dan [docs/manual/QUICK-GUIDE-ORANG-TUA.md](file:///D:/Dany/PPDB/PPDB_Minsa_2026/docs/manual/QUICK-GUIDE-ORANG-TUA.md) (infografis teks 3 menit).
- [x] **[P1 - Tinggi] Panduan Operasional Admin PPDB**:
  - *Deskripsi*: Buku panduan operasional panitia [docs/manual/02-PANDUAN-ADMIN-PPDB.md](file:///D:/Dany/PPDB/PPDB_Minsa_2026/docs/manual/02-PANDUAN-ADMIN-PPDB.md) berbasis fitur nyata (login, dasbor, reopen registration, penutupan dini, dan penegakan kedaluwarsa).
- [x] **[P1 - Tinggi] Panduan Pengelola Superadmin**:
  - *Deskripsi*: Buku panduan teknis [docs/manual/03-PANDUAN-SUPERADMIN.md](file:///D:/Dany/PPDB/PPDB_Minsa_2026/docs/manual/03-PANDUAN-SUPERADMIN.md) untuk isolasi kunci kriptografi, manajemen database, dan prosedur pembuatan akun via CLI/Tinker.
- [x] **[P1 - Tinggi] Matriks Hak Akses Role & Permission**:
  - *Deskripsi*: Matriks lengkap modul vs peran aktual [docs/manual/04-MATRIKS-ROLE-DAN-PERMISSION.md](file:///D:/Dany/PPDB/PPDB_Minsa_2026/docs/manual/04-MATRIKS-ROLE-DAN-PERMISSION.md).
- [x] **[P1 - Tinggi] Standar Operasional Prosedur (SOP) Panitia**:
  - *Deskripsi*: SOP kepanitiaan 3 fase (Sebelum Dibuka, Saat Pelaksanaan, dan Pasca-Penutupan) [docs/manual/05-SOP-OPERASIONAL-PANITIA.md](file:///D:/Dany/PPDB/PPDB_Minsa_2026/docs/manual/05-SOP-OPERASIONAL-PANITIA.md) serta [docs/manual/BRIEFING-PANITIA-PPDB.md](file:///D:/Dany/PPDB/PPDB_Minsa_2026/docs/manual/BRIEFING-PANITIA-PPDB.md).
- [x] **[P1 - Tinggi] FAQ & Troubleshooting Terpisah**:
  - *Deskripsi*: Tanya jawab penanganan kendala orang tua dan admin [docs/manual/06-FAQ-DAN-TROUBLESHOOTING.md](file:///D:/Dany/PPDB/PPDB_Minsa_2026/docs/manual/06-FAQ-DAN-TROUBLESHOOTING.md).
- [x] **[P1 - Tinggi] Pedoman Privasi & Keamanan Data (UU PDP)**:
  - *Deskripsi*: Pedoman kepatuhan perlindungan data pribadi anak [docs/manual/07-PEDOMAN-KEAMANAN-PENGGUNA.md](file:///D:/Dany/PPDB/PPDB_Minsa_2026/docs/manual/07-PEDOMAN-KEAMANAN-PENGGUNA.md).
- [x] **[P1 - Tinggi] Diagram Alur Kerja Sistem (Mermaid)**:
  - *Deskripsi*: Visualisasi alur end-to-end, OTP sequence, dan reopen workflow [docs/manual/08-ALUR-KERJA-SISTEM.md](file:///D:/Dany/PPDB/PPDB_Minsa_2026/docs/manual/08-ALUR-KERJA-SISTEM.md).
- [x] **[P1 - Tinggi] Glosarium Istilah Sistem**:
  - *Deskripsi*: Kamus istilah teknis dan madrasah [docs/manual/09-GLOSARIUM.md](file:///D:/Dany/PPDB/PPDB_Minsa_2026/docs/manual/09-GLOSARIUM.md).
- [x] **[P0 - Kritis] Analisis Celah Fitur (Feature Gap Analysis)**:
  - *Deskripsi*: Evaluasi jujur fitur tersedia vs fitur gap [docs/manual/10-DAFTAR-FITUR-DAN-GAP.md](file:///D:/Dany/PPDB/PPDB_Minsa_2026/docs/manual/10-DAFTAR-FITUR-DAN-GAP.md).
- [x] **[P0 - Kritis] Laporan Komprehensif Fase 10 (16 Seksi)**:
  - *Deskripsi*: Laporan master [docs/phases/PHASE-10-IMPLEMENTATION-REPORT.md](file:///D:/Dany/PPDB/PPDB_Minsa_2026/docs/phases/PHASE-10-IMPLEMENTATION-REPORT.md).

**Status Fase 10: SELESAI (100% ARTEFAK DOKUMENTASI TERSEDIA & TERVALIDASI).** Nol modifikasi pada kode sumber aplikasi. Dokumen operasional siap dipakai untuk pelatihan panitia dan sosialisasi orang tua.

### Fase 10.1 — Admin Dashboard Real Data & Navigasi Bebas 404
- [x] **[P0 - Kritis] Controller Admin Dashboard Data Riil**:
  - *Deskripsi*: Membuat `app/Http/Controllers/Admin/AdminDashboardController.php` dengan kueri agregasi optimal (2 kueri ringkas), pemisahan data periode aktif vs lampau, 5 pendaftar terbaru terkirim dengan eager loading, dan penanganan status periode kosong (*graceful empty state*).
- [x] **[P1 - Tinggi] Antarmuka Blade Admin Dashboard Profesional**:
  - *Deskripsi*: Membangun `resources/views/admin/dashboard.blade.php` beridentitas visual Minsatama (#0F7FA3, #0B6E3F, #F2C94C), hero banner periode aktif dengan countdown waktu, 5 kartu metrik riil (Total, Terkirim, Draf, Reguler, Unggulan), tabel 5 pendaftar terbaru tanpa paparan NIK/PII, kartu aksi cepat (*Buka Ulang Pendaftaran* & *Portal Publik*), serta status sistem/petugas login.
- [x] **[P1 - Tinggi] Penanganan Navigasi Sidebar Bebas Error 404**:
  - *Deskripsi*: Memperbarui `resources/views/components/navigation/admin-sidebar.blade.php` agar menu yang rutenya belum diimplementasikan (`Data Pendaftar`, `Verifikasi Berkas`, `Periode`, `Program`, `Laporan`, `Pengguna`) memiliki badge "Segera" dan tidak melempar error `404 | Not Found`. Menambahkan route fallback di `routes/web.php` yang mengarahkan URL modul mendatang kembali ke dashboard dengan notifikasi informatif.
- [x] **[P0 - Kritis] Suite Pengujian Fitur Dasbor Admin**:
  - *Deskripsi*: `tests/Feature/Admin/AdminDashboardTest.php` memverifikasi 12 skenario pengujian (otorisasi role/permission, kalkulasi agregat riil, isolasi periode lampau, 5 submisi teranyar, empty state, proteksi kerahasiaan NIK, dan redirect navigasi aman). 12 tes lulus dengan 44 asersi.

**Status Fase 10.1: SELESAI (100% TERUJI, BEBAS 404 & TERHUBUNG DATA AKTUAL).** 215 tests passed, 1109 assertions. Code style Pint 100% compliant. Vite asset compilation sukses.

### Fase 10.2 — Integrasi Gateway WhatsApp OTP Menggunakan Fonnte API
- [x] **[P0 - Kritis] Driver Pengiriman Fonnte (`FonnteOtpDelivery`)**:
  - *Deskripsi*: Membuat `app/Services/Authentication/Delivery/FonnteOtpDelivery.php` dengan normalisasi nomor telepon Indonesia (`08...`, `628...`, `+628...`), header Authorization langsung tanpa Bearer, payload `asForm()`, dan validasi status boolean/string.
- [x] **[P0 - Kritis] Pemisahan Template Pesan Profesional**:
  - *Deskripsi*: Template pesan terpisah untuk Aktivasi Akun dan Pemulihan Kata Sandi, steril dari NIK/informasi pribadi sensitif.
- [x] **[P0 - Kritis] Penanganan Kegagalan & Timeout Ambigu**:
  - *Deskripsi*: Penanganan terisolasi untuk token invalid, device disconnected, insufficient quota, invalid target, timeout koneksi, dan respons non-JSON. Log teknis steril tanpa plaintext OTP maupun token API.
- [x] **[P0 - Kritis] Integrasi Service Bisnis & Pengendali**:
  - *Deskripsi*: `OtpDeliveryService` memetakan driver `'fonnte'`, `OtpService` melempar `RuntimeException` ramah saat provider menolak pengiriman, dan kontroler auth menangkap exception tanpa fatal error 500.
- [x] **[P0 - Kritis] Suite Pengujian Otomatis 20 Skenario**:
  - *Deskripsi*: `tests/Feature/Auth/FonnteOtpDeliveryTest.php` menguji 20 skenario menyeluruh menggunakan `Http::fake()`. 20 tes lulus dengan 41 asersi.
- [x] **[P1 - Tinggi] Panduan Konfigurasi & Operasional**:
  - *Deskripsi*: Pembuatan dokumen `docs/manual/FONNTE-WHATSAPP-INTEGRATION-GUIDE.md` (cara perolehan token, scan QR, konfigurasi `.env`, rotasi token, dan troubleshooting).

**Status Fase 10.2: SELESAI SECARA TEKNIS (SIAP UJI MANUAL NOMOR KENDALI PEMILIK PROYEK).** 235 tests passed, 1150 assertions. Nol pengiriman WhatsApp sungguhan pada automated test.

### Fase 10.3 — Pending Registration Recovery & NIK Reuse After OTP Failure
- [x] **[P0 - Kritis] Mekanisme Pending Registration Recovery**:
  - *Deskripsi*: Memperbarui `app/Actions/Authentication/RegisterApplicantAction.php` untuk membedakan NIK dengan status `PENDING_ACTIVATION` vs `ACTIVE` / `SUSPENDED`. Akun `PENDING_ACTIVATION` dapat melanjutkan pendaftaran dengan NIK yang sama.
- [x] **[P0 - Kritis] Validasi & Pencocokan Identitas**:
  - *Deskripsi*: Verifikasi kesesuaian 4 atribut identitas (`full_name`, `gender`, `birth_place`, `birth_date`) dengan normalisasi kapital & whitespace sebelum mengizinkan pemulihan pendaftaran tertunda.
- [x] **[P0 - Kritis] Pembaruan Nomor Telepon & Zero-Duplicate Guarantee**:
  - *Deskripsi*: Memperbarui `users.phone` pada akun yang sudah ada, mereset `phone_verified_at = null`, menginvalider seluruh token OTP aktivasi lama, dan menerbitkan OTP baru ke nomor baru tanpa membuat duplikat `User` atau `Applicant`.
- [x] **[P0 - Kritis] Penanganan Kegagalan Pengiriman Tanpa Mengunci Akun**:
  - *Deskripsi*: Jika Fonnte gagal mengirim OTP, akun tetap berstatus `PENDING_ACTIVATION`, diarahkan ke `/aktivasi` dengan pesan peringatan ramah, dan disediakan tombol "Coba Kirim Ulang OTP" serta tautan "Kembali ke Formulir Pendaftaran".
- [x] **[P0 - Kritis] Suite Pengujian Otomatis 20 Skenario**:
  - *Deskripsi*: `tests/Feature/Auth/PendingRegistrationRecoveryTest.php` menguji 20 skenario menyeluruh menggunakan `Http::fake()`. 20 tes lulus dengan 59 asersi.
- [x] **[P1 - Tinggi] Laporan Implementasi Fase 10.3**:
  - *Deskripsi*: Pembuatan dokumen komprehensif `docs/phases/PHASE-10.3-IMPLEMENTATION-REPORT.md`.

**Status Fase 10.3: SELESAI (100% TERUJI OTOMATIS: 256 TESTS PASSED, 1217 ASSERTIONS).** Nol duplikasi user, nol plaintext OTP/NIK bocor. Pint & Vite compliant.

---

## 4. TUGAS KEAMANAN & PEMELIHARAAN (SECURITY & MAINTENANCE)

- [x] **[P0 - Selesai] Enkripsi NIK & Deterministic HMAC Lookup**: Terimplementasi via `NikManager` dengan pengujian unit 100% lulus.
- [x] **[P0 - Selesai] Pengamanan Kriptografi OTP Binding**: Terimplementasi via `OtpService` dengan kunci rahasia terpisah dan context binding.
- [x] **[P0 - Selesai] Proteksi Brute-Force Rate Limiting**: Batas 5 percobaan login per menit dan batas permintaan OTP.
- [x] **[P0 - Selesai] IDOR Prevention pada Form Profil**: Seluruh aksi pembaruan profil diikat ketat pada `$request->user()->applicant`, mengabaikan ID yang dikirim melalui request.
- [x] **[P1 - Selesai] Otorisasi Dokumen Pendaftar**: Preview, unduh, dan riwayat revisi dibatasi ke dokumen milik pendaftar yang sedang masuk.
- [x] **[P0 - Selesai] Audit Dependensi NPM**: Eksekusi `npm.cmd audit` menghasilkan 0 vulnerabilities.
- [x] **[P1 - Operasional] Dokumentasi batas unggah server**: Rekomendasi konfigurasi server produksi (`upload_max_filesize=10M`, `post_max_size=16M`) didokumentasikan di `PRODUCTION-READINESS-CHECKLIST.md`.
- [x] **[P1 - Operasional] Verifikasi MariaDB fisik**: Sepuluh tes lulus pada MariaDB 10.4.32/InnoDB dan database uji khusus `ppdb_phase6_test`. Gerbang MySQL 8 mandiri dicatat BLOCKED.
- [x] **[P1 - Kebijakan] Implementasi kebijakan admin reopen**: Hak akses per pendaftaran dengan alasan, waktu kedaluwarsa, penutupan dini, penegakan server-side, dan audit telah diterapkan dan diuji E2E.
