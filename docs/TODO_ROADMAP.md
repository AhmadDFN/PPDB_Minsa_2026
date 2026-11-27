# TODO ROADMAP â€” PPDB MINSATAMA
## Peta Jalan Pengembangan dan Rencana Tugas Berkelanjutan

## PHASE 11 — LAUNCH READINESS (27 NOVEMBER 2026)

Keputusan pemilik: nomor resmi `YYNNNN` (contoh `270001`), sequence global per tahun penerimaan, diterbitkan saat pengesahan panitia; modul wawancara resmi menjadi bagian PPDB. Seleksi akhir, perankingan, pengumuman, dan daftar ulang tetap di luar scope.

Checklist lengkap dan stop conditions berada di [`docs/phases/PHASE-11-LAUNCH-READINESS-AND-DECISIONS.md`](phases/PHASE-11-LAUNCH-READINESS-AND-DECISIONS.md).

### P0 — Contract and data safety

- [ ] Hapus generator, validator, query, UI, PDF, CSV, seeder, dan test legacy `YYNNN`/`PPDB/...`; gunakan satu kontrak `YYNNNN`.
- [ ] Tambahkan regression test sequence `270001`, `270002`, batas 9999, lintas program/periode, retry, duplicate approval, concurrency, dan rollback.
- [ ] Audit dan pulihkan MariaDB pascainsiden `administrative_regions` tanpa `migrate:fresh`; periksa backup, tabel parsial, FK, dan migration status.
- [ ] Jalankan migration additive pada target setelah bukti backup dan persetujuan pemilik.

### P0 — Production configuration

- [ ] Verifikasi `APP_DEBUG=false`, HTTPS/`APP_URL`, storage privat, permission, cache, queue/cron, dan APP environment.
- [ ] Verifikasi `gd`, upload 10M, post 16M, memory 256M, execution 60 detik, dan batas web server.
- [ ] Verifikasi Fonnte device/token/kuota, timeout, graceful degradation, serta pemisahan pesan OTP dan password sementara.
- [ ] Verifikasi live OTP lupa password menggunakan kanal Fonnte aktif setelah deployment yang disetujui; jangan mencatat token atau OTP.

### P1 — Applicant acceptance

- [ ] Uji registrasi, OTP, resend/recovery, sibling support, mandatory password change, dan session isolation.
- [ ] Uji enam formulir pada 360/390/768 px tanpa overflow/console error.
- [ ] Uji cascading wilayah, salin alamat, peta offline, GPS opt-in, Haversine, fallback manual, dan no-coordinate-storage.
- [ ] Uji dokumen 5 MB, MIME, replacement, revision history, private storage, dan IDOR.
- [ ] Uji foto asli/latar biru sesuai keputusan operasional.
- [ ] Uji draft/final PDF, watermark, snapshot, tanggal, masking, fingerprint, dan scan QR fisik.

### P1 — Staff and interview acceptance

- [ ] Uji role/permission applicant, admin, superadmin, interviewer stage 1/2/3 dan seluruh 403 boundary.
- [ ] Uji dashboard, applicant detail, filter/search `YYNNNN`, NIK HMAC, pagination, reports, CSV, dan IDOR.
- [ ] Uji document verification, reopen, expiry, audit, and replacement workflow.
- [ ] Uji master period/program/requirements, system settings, admin lifecycle, Communication Center, template, dan audit viewer.
- [ ] Uji batch/stage/criteria, sequential gate, score lock, correction audit, recap, CSV export, dan role isolation wawancara.
- [ ] Tutup checklist live admin/superadmin; angka lama 2 PASS, 13 PARTIAL, 1 BLOCKED, 2 belum diuji adalah historis sebelum fresh seed. Gunakan hasil putaran baru pada `adminlivetest/03-CHECKLIST-DAN-HASIL.md`.
- [ ] Lanjutkan UAT superadmin untuk mutasi aman akun staf, permission, master, audit, dan UI responsif. Login, baca menu, simpan ulang template, branding, serta unduhan CSV sudah diuji; pengiriman WA menunggu jalur kirim uji terkontrol (AL-010).
- [ ] Cocokkan akun superadmin live yang pernah berhasil login dengan inventaris seeder; gunakan kredensial aktif dari penyimpanan privat. Seeder `firstOrCreate` tidak memulihkan sandi akun yang ada. Jangan menjalankan `LiveTestSeeder` pada produksi untuk memperoleh akses.
- [ ] Tindak lanjuti AL-004: pastikan kredensial yang pernah ada dalam dokumen Git tidak digunakan di server live; rotasi yang terpengaruh dan periksa log akses. Dokumen baru telah menghapus nilai terbuka, tetapi riwayat Git tetap perlu penanganan terpisah.
- [ ] **P0 sebelum panel resmi MIN diluncurkan:** siapkan sandi baru untuk seluruh akun staf, pastikan default seeder statis tidak dipakai, simpan rahasia di luar Git, dan verifikasi akses/log. Pemilik menerima akun uji sementara pada `ahmaddanyfn.biz.id` yang dapat direset (AL-004).
- [ ] **P1 UAT:** siapkan dokumen dummy pada fixture SUBMITTED setelah fresh seed, lalu uji verifikasi, pengesahan/nomor `YYNNNN`, PDF/QR, audit, dan WhatsApp hanya ke penerima uji terkendali (AL-007).
- [ ] **P1 komunikasi:** contoh isi dan placeholder editor telah terlihat di live; lanjutkan uji Fonnte/OTP pemulihan dengan penerima uji (AL-006).
- [ ] **P1 audit superadmin (AL-009):** tampilkan audit komunikasi pada `/admin/audit-logs`, termasuk filter/ringkasan, tanpa token atau isi pesan; uji regresi dan verifikasi live.
- [x] **P1 kirim uji superadmin (AL-010):** form live tersedia; satu pesan uji dikirim dan pemilik mengonfirmasi masuk ke WhatsApp. Respons panel tidak konsisten (AL-011).
- [ ] **P1 respons gateway (AL-011):** parser ID array sesuai dokumentasi Fonnte dan teks status aman sudah diperbaiki, diuji, serta terlihat pada berkas Hostinger; verifikasi respons sukses live pada pengiriman terpisah yang disetujui. Jangan mengirim ulang pesan uji hari ini.
- [x] **UAT superadmin 20.26 WIB:** login/logout, branding dan petunjuk default, simpan ulang template, serta unduhan CSV berhasil. Audit komunikasi dan kirim uji tercatat sebagai gap; mutasi akun/role/master masih menunggu uji terpisah.
- [ ] **P2 branding:** nilai aktif live dan login pendaftar sudah menampilkan `PPDB`; petunjuk default statis yang masih `PPDBM` diperbaiki lokal dan menunggu uji ulang sesudah rilis (AL-005).
- [x] **P3 UX staf:** setelah logout, akses rute admin tanpa sesi menuju `/admin/login`; sudah diverifikasi live (AL-008).

### P1 — Operational readiness

- [ ] Uji backup database/storage dan restore pada environment terisolasi.
- [ ] Audit log privacy, rate limiting, CSRF, XSS, SQLi, upload abuse, session, authorization, dan QR privacy.
- [ ] Sinkronkan landing/manual dengan scope resmi dan jam PTSP 07.00 – 15.00 WIB.

### Final gates

- [ ] `php artisan test`
- [ ] `vendor/bin/pint --test`
- [ ] `npm.cmd run build`
- [ ] `composer audit`
- [ ] `npm.cmd audit`
- [ ] `git diff --check`
- [ ] Browser smoke/UAT semua role
- [ ] Approval owner atas launch checklist

## Rekonsiliasi Admin/Superadmin (20 November 2026)

- [ ] Periksa semua variasi status dashboard dan lakukan audit aksesibilitas lengkap setelah perbaikan palet/kontras; preview sintetis keadaan draft telah dilakukan.

- [ ] Next Atelier: uji dashboard pendaftar terautentikasi dan semua variasi status, aksesibilitas dan alur E2E; selesaikan rekonsiliasi konten landing sebelum menganggap desain tervalidasi penuh.

- [ ] Calm Academic: selesaikan UAT visual dashboard pendaftar, pemeriksaan aksesibilitas/responsif lengkap, dan rekonsiliasi konten landing dengan pemilik; lihat Phase 11.

Roadmap target dan dependency disentralisasi di [`docs/blueprints/ADMIN-SUPERADMIN-IMPLEMENTATION-ROADMAP.md`](blueprints/ADMIN-SUPERADMIN-IMPLEMENTATION-ROADMAP.md). Gap prioritas: verifikasi dokumen, master data, ekspor redacted, lifecycle akun admin, dan audit viewer. Jangan menandai permission seeder sebagai fitur selesai.

## Phase 10.6 — Role-Based Dashboard

- [x] Dashboard Admin dan Superadmin dipisahkan berdasarkan role aktual dengan query data riil.
- [x] Sidebar/layout menampilkan identitas role dan hanya mengaktifkan route yang tersedia.
- [x] Tren submission tujuh hari dan metrik global Superadmin ditambahkan tanpa migration baru.
- [ ] UAT live Admin/Superadmin dan review owner.

**Status Notasi**:
- `[x]` Selesai (*Completed*)
- `[-]` Sedang Berjalan (*In Progress*)
- `[ ]` Menunggu (*Pending*)
- `[!]` Terhambat (*Blocked*)

## Hotfix — Superadmin Settings & Admin Provisioning

- [x] Update setting dan audit log dibuat atomik; cache dibersihkan setelah commit.
- [x] Provisioning admin mempertahankan transaksi/role/audit dan menambahkan logging diagnosis tanpa credential.
- [x] Regression test untuk rollback audit, cache, sanitasi HTML, identity, role, dan password hash.
- [ ] Verifikasi schema production melalui `migrate:status`/phpMyAdmin, migration additive oleh pemilik, deployment, dan live acceptance.

## Revisi Form Calon Siswa (26 November 2026) — Local Only

- [ ] Insiden migrasi MariaDB: periksa backup dan tabel wilayah parsial, tentukan pemulihan dengan pemilik, validasi perbaikan pada MariaDB aman, dan tangani lima advisory Composer; lihat ISS-011.

- [x] Schema additive, enkripsi KK/nomor bantuan/NIK orang tua, No. Handphone calon siswa terpisah, Form Request, Action, readiness, dokumen, review/admin, dan PDF final berisi status berkas unggahan serta NIK termasking.
- [x] Snapshot wilayah lokal 2025 (91.599 entri) dengan sumber/lisensi terdokumentasi, API cascading dengan validasi parent-child, serta shortcut salin wilayah alamat siswa.
- [x] Automated regression: 388 tests, 373 passed, 15 skipped, 1.781 assertions; Pint, Vite build, Composer/NPM audit, route/view, JavaScript syntax, map data integrity, dan diff-check lulus.
- [x] Browser lokal: cascade wilayah/copy alamat; IDOR dokumen dan PDF lintas akun 404; enam langkah pada 360 px tanpa overflow.
- [x] Peta offline OSM lokal, pin tap/drag/keyboard, geolocation opt-in, dan Haversine server-side; feature test memastikan koordinat tidak disimpan. Harness komponen browser pada 360×800/390×844 lulus tanpa overflow; cakupan visual terbatas sekitar Kampus 1. Fallback jarak manual tersedia tanpa mengirim koordinat ke provider.
- [ ] UAT map di form pendaftar penuh, foto asli/latar biru, scan QR aktual, skema MySQL/MariaDB dan staging.
- [ ] Review akhir, commit/push/deploy setelah seluruh acceptance gaps tertutup.

## Phase 5A — Verifikasi Dokumen & Review Pendaftar

- [x] Queue admin hanya menampilkan pendaftaran `SUBMITTED` dengan filter pencarian/status.
- [x] Preview berkas menggunakan private storage dan pemeriksaan ownership/IDOR di server.
- [x] Status `PENDING`, `APPROVED`, `REJECTED`, dan `REVISION_REQUIRED` beserta alasan wajib untuk penolakan/revisi.
- [x] Riwayat perubahan status, audit actor, transaksi dan row locking.
- [x] Replacement dokumen mengembalikan status verifikasi ke `PENDING`.
- [x] Automated regression lengkap, commit/push ke `origin/main`.
- [ ] Deployment Hostinger, migration additive, dan live acceptance test oleh pemilik proyek.

## Phase 5B — Laporan Admin & Redacted CSV Export

- [x] Reporting UI `/admin/reports` dengan ringkasan submitted, program, periode, dan status dokumen.
- [x] Filter periode, program, rentang tanggal submit, dan status verifikasi dengan Form Request.
- [x] CSV streaming UTF-8 BOM, kolom minimum, WIB, chunk/cursor, join-based export tanpa N+1, dan tanpa file permanen.
- [x] Data minimization, CSV formula-injection protection, RBAC `export_reports`, rate limit per user, dan audit metadata.
- [x] Automated regression final dan commit hardening `1465c58`.
- [x] Push hardening ke `origin/feature/ppdb-admin-reports-export` dan verifikasi remote.
- [ ] Merge PR #1, deployment, migration (tidak diperlukan), dan live acceptance test.

## Phase 5C — Audit Viewer & Activity Monitoring

- [x] Viewer read-only `/admin/audit-logs` dengan middleware `view_activity_logs`.
- [x] Timeline lintas admin configuration, system settings, document verification, dan registration activity.
- [x] Deduplikasi event verifikasi dokumen dan redaction metadata sensitif.
- [x] Filter GET tervalidasi, pagination global 25 baris, source health, dan summary cards berbasis query aktual.
- [x] Automated authorization, integration, privacy, source health, pagination, dan bounded-query tests.
- [x] Review fixes: normalized action filter dan anonimisasi actor Applicant diverifikasi dengan regression tests.
- [x] Commit dan push branch `feature/ppdb-audit-viewer`; PR #2 tetap stacked terhadap Phase 5B.
- [ ] PR review/merge, deployment, dan live verification.

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
  - *Deskripsi*: Penyusunan dokumen [docs/deployment/HOSTINGER-STAGING-GUIDE.md](deployment/HOSTINGER-STAGING-GUIDE.md) mencakup metode ekstraksi ZIP, pembuatan database MySQL, seeding referensi, pengujian alur 1–7, dan pemecahan masalah.
- [x] **[P1 - Tinggi] Cetak Biru Produksi Mandiri aaPanel VPS**:
  - *Deskripsi*: Penyusunan panduan operasional produksi resmi [docs/deployment/AAPANEL-PRODUCTION-GUIDE.md](deployment/AAPANEL-PRODUCTION-GUIDE.md) meliputi Nginx FastCGI, PHP 8.3 FPM, MariaDB 10.11/MySQL 8, Redis, Supervisor, Let's Encrypt SSL, zero-downtime deployment script, cron backup, dan rollback darurat.
- [x] **[P1 - Tinggi] Pelaporan Hasil Uji Penerimaan Pengguna (UAT Results)**:
  - *Deskripsi*: Pencatatan hasil evaluasi 21 skenario UAT pada [docs/uat/UAT-RESULTS.md](uat/UAT-RESULTS.md) (21 skenario lulus verifikasi teknis otomatis; lembar pengesahan menunggu pelaksanaan fisik panitia madrasah).
- [x] **[P0 - Kritis] Laporan Komprehensif Fase 9 (18 Seksi)**:
  - *Deskripsi*: Penyusunan dokumen master [docs/phases/PHASE-9-IMPLEMENTATION-REPORT.md](phases/PHASE-9-IMPLEMENTATION-REPORT.md).

**Status Fase 9: SELESAI SECARA TEKNIS (SIAP UAT FISIK SEKOLAH & MENUNGGU ARSITEKTUR AAPANEL PRODUKSI).** Paket rilis staging siap diuji di server Hostinger, dokumentasi produksi aaPanel lengkap. Dilarang melakukan deployment produksi ke aaPanel sebelum persetujuan resmi.

### Fase 10 — Dokumentasi Operasional, User Manual & SOP Kepanitiaan (Operational Docs)
- [x] **[P0 - Kritis] Audit Repositori & Hak Akses Aktual**:
  - *Deskripsi*: Verifikasi peran Spatie (`superadmin`, `admin_ppdb`) dan akun siswa (`applicant`), pemetaan rute, middleware, dan controller aktual tanpa asumsi peran fiktif.
- [x] **[P1 - Tinggi] User Manual Orang Tua / Wali**:
  - *Deskripsi*: Buku panduan lengkap 14 bab [docs/manual/01-PANDUAN-ORANG-TUA-WALI.md](manual/01-PANDUAN-ORANG-TUA-WALI.md) dan [docs/manual/QUICK-GUIDE-ORANG-TUA.md](manual/QUICK-GUIDE-ORANG-TUA.md) (infografis teks 3 menit).
- [x] **[P1 - Tinggi] Panduan Operasional Admin PPDB**:
  - *Deskripsi*: Buku panduan operasional panitia [docs/manual/02-PANDUAN-ADMIN-PPDB.md](manual/02-PANDUAN-ADMIN-PPDB.md) berbasis fitur nyata (login, dasbor, reopen registration, penutupan dini, dan penegakan kedaluwarsa).
- [x] **[P1 - Tinggi] Panduan Pengelola Superadmin**:
  - *Deskripsi*: Buku panduan teknis [docs/manual/03-PANDUAN-SUPERADMIN.md](manual/03-PANDUAN-SUPERADMIN.md) untuk isolasi kunci kriptografi, manajemen database, dan prosedur pembuatan akun via CLI/Tinker.
- [x] **[P1 - Tinggi] Matriks Hak Akses Role & Permission**:
  - *Deskripsi*: Matriks lengkap modul vs peran aktual [docs/manual/04-MATRIKS-ROLE-DAN-PERMISSION.md](manual/04-MATRIKS-ROLE-DAN-PERMISSION.md).
- [x] **[P1 - Tinggi] Standar Operasional Prosedur (SOP) Panitia**:
  - *Deskripsi*: SOP kepanitiaan 3 fase (Sebelum Dibuka, Saat Pelaksanaan, dan Pasca-Penutupan) [docs/manual/05-SOP-OPERASIONAL-PANITIA.md](manual/05-SOP-OPERASIONAL-PANITIA.md) serta [docs/manual/BRIEFING-PANITIA-PPDB.md](manual/BRIEFING-PANITIA-PPDB.md).
- [x] **[P1 - Tinggi] FAQ & Troubleshooting Terpisah**:
  - *Deskripsi*: Tanya jawab penanganan kendala orang tua dan admin [docs/manual/06-FAQ-DAN-TROUBLESHOOTING.md](manual/06-FAQ-DAN-TROUBLESHOOTING.md).
- [x] **[P1 - Tinggi] Pedoman Privasi & Keamanan Data (UU PDP)**:
  - *Deskripsi*: Pedoman kepatuhan perlindungan data pribadi anak [docs/manual/07-PEDOMAN-KEAMANAN-PENGGUNA.md](manual/07-PEDOMAN-KEAMANAN-PENGGUNA.md).
- [x] **[P1 - Tinggi] Diagram Alur Kerja Sistem (Mermaid)**:
  - *Deskripsi*: Visualisasi alur end-to-end, OTP sequence, dan reopen workflow [docs/manual/08-ALUR-KERJA-SISTEM.md](manual/08-ALUR-KERJA-SISTEM.md).
- [x] **[P1 - Tinggi] Glosarium Istilah Sistem**:
  - *Deskripsi*: Kamus istilah teknis dan madrasah [docs/manual/09-GLOSARIUM.md](manual/09-GLOSARIUM.md).
- [x] **[P0 - Kritis] Analisis Celah Fitur (Feature Gap Analysis)**:
  - *Deskripsi*: Evaluasi jujur fitur tersedia vs fitur gap [docs/manual/10-DAFTAR-FITUR-DAN-GAP.md](manual/10-DAFTAR-FITUR-DAN-GAP.md).
- [x] **[P0 - Kritis] Laporan Komprehensif Fase 10 (16 Seksi)**:
  - *Deskripsi*: Laporan master [docs/phases/PHASE-10-IMPLEMENTATION-REPORT.md](phases/PHASE-10-IMPLEMENTATION-REPORT.md).

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

### Fase 10.4 — Simplifikasi Registrasi, Auto-Generated Password, Nomor Legacy (SUPERSEDED) & Dynamic Branding

> Bagian nomor `YYNNN` di bawah adalah catatan historis. Keputusan pemilik 27 November 2026 menggantinya dengan `YYNNNN` yang diterbitkan saat pengesahan panitia. Tugas koreksi aktif berada di Phase 11.
- [x] **[P0 - Kritis] Simplifikasi Formulir Registrasi Akun Baru**:
  - *Deskripsi*: Mengubah `/daftar` hanya menerima 4 field (`nik`, `full_name`, `phone`, `consent`). Menghapus input kata sandi, konfirmasi kata sandi, dan input biodata siswa. Biodata dialihkan ke Langkah 1 dashboard.
- [x] **[P0 - Kritis] Penghapusan Pembuatan Kata Sandi Manual Pasca-OTP**:
  - *Deskripsi*: Menghapus form `/aktivasi/buat-password` dan langsung mengarahkan pengguna ke `/login` setelah aktivasi OTP berhasil.
- [x] **[P0 - Kritis] Penerbitan Kata Sandi Sementara Kriptografis Otomatis**:
  - *Deskripsi*: Menghasilkan 10 karakter alfanumerik acak aman, menyimpan hash `Hash::make()` dengan `must_change_password = true`, dan mengirimkan kata sandi sementara dalam pesan WhatsApp Fonnte terpisah dari OTP.
- [x] **[P0 - Kritis] Penegakan Wajib Ganti Kata Sandi (Mandatory Password Change)**:
  - *Deskripsi*: Middleware `EnsurePasswordNotTemporary` mengunci dashboard dan form pendaftaran bagi akun dengan `must_change_password = true`, mengarahkan ke form `/pendaftar/ganti-password` hingga kata sandi permanen tersimpan.
- [x] **[P0 - Kritis] Format Nomor Pendaftaran 5 Digit (YYNNN)**:
  - *Deskripsi*: Format `YYNNN` (misal `27001` - `27999`), urutan global lintas program (Reguler & Unggulan berbagi sequence), batas kuota maks 999, dialokasikan sejak registrasi awal dan dipertahankan saat pending recovery.
- [x] **[P0 - Kritis] Pengaturan Branding Sistem Dinamis (Superadmin)**:
  - *Deskripsi*: Hak akses khusus `manage_system_settings` bagi Superadmin untuk mengatur `institution_display_name` melalui `/admin/pengaturan`, dengan pencatatan audit log `system_setting_logs` dan proteksi 403 Forbidden bagi Admin PPDB.
- [x] **[P0 - Kritis] Suite Pengujian Otomatis 9 Skenario Spesifik**:
  - *Deskripsi*: `tests/Feature/Auth/RegistrationSimplificationAndGeneratedPasswordTest.php` menguji seluruh dimensi Fase 10.4 dengan 9 tes lulus dan 75 asersi.
- [x] **[P1 - Tinggi] Laporan Implementasi Fase 10.4**:
  - *Deskripsi*: Pembuatan dokumen komprehensif `docs/phases/PHASE-10.4-IMPLEMENTATION-REPORT.md`.

**Status Fase 10.4: SELESAI (100% TERUJI OTOMATIS: 265 TESTS PASSED, 1302 ASSERTIONS).** Pint & Vite compliant, composer/npm audit clean.

### Fase 10.5 — Admin Dashboard Dinamis & Manajemen Data Pendaftar
- [x] **[P0 - Kritis] Admin Dashboard Dinamis Berbasis Data Riil**:
  - *Deskripsi*: Menampilkan metrik statistik global pendaftar (total akun, total pendaftaran, total draft, total submitted), metrik periode aktif, tabel 5 pendaftar terkini dengan tautan detail, serta log aktivitas audit registrasi terkini.
- [x] **[P0 - Kritis] Manajemen Daftar Pendaftar (/admin/pendaftar)**:
  - *Deskripsi*: Tampilan tabular pendaftar dengan paginasi 15 data per halaman, multi-search (nama siswa, nomor pendaftaran 5-digit `YYNNN`, NIK 16 digit via deterministik HMAC lookup hash tanpa kebocoran plaintext NIK), filter periode, program, dan status.
- [x] **[P0 - Kritis] Halaman Detail Pendaftar Komprehensif (/admin/pendaftar/{id})**:
  - *Deskripsi*: 11 kartu seksi informasi (Identitas Siswa dengan perhitungan usia dinamis runtime, Alamat Domisili, Data Ayah, Data Ibu, Data Wali, Riwayat Pendidikan TK/RA, Pilihan Program & Periode, Kelengkapan Dokumen & Persyaratan, Status & Persetujuan, Riwayat Log Pendaftaran, Status Akses Buka Ulang).
- [x] **[P0 - Kritis] Pratinjau & Unduh Dokumen Terotorisasi Anti-IDOR**:
  - *Deskripsi*: Akses pratinjau inline dan unduh berkas privat melalui `/admin/pendaftar/{registration}/dokumen/{document}/preview` dan `/download` dengan verifikasi ketat kepemilikan berkas `abort_unless($document->registration_id === $registration->id, 404)`.
- [x] **[P0 - Kritis] Navigasi Admin Aktif & Proteksi Spatie Role/Permission**:
  - *Deskripsi*: Sidebar mengaktifkan rute `admin.applicants.index` berpelindung `@can('view_applicants')`, penanda status menu aktif, pencegahan 404 dengan rute fallback terproteksi.
- [x] **[P0 - Kritis] Suite Pengujian Otomatis 14 Skenario**:
  - *Deskripsi*: `tests/Feature/Admin/ApplicantManagementTest.php` menguji seluruh dimensi otorisasi, paginasi, pencarian aman, filter, rendering 11 kartu detail, dan proteksi IDOR berkas. 14 tes lulus dengan 63 asersi.
- [x] **[P1 - Tinggi] Laporan Implementasi Fase 10.5**:
  - *Deskripsi*: Pembuatan dokumen komprehensif `docs/phases/PHASE-10.5-IMPLEMENTATION-REPORT.md`.

**Status Fase 10.5: SELESAI (100% TERUJI OTOMATIS: 279 TESTS PASSED, 1365 ASSERTIONS).** Pint & Vite compliant, composer/npm audit clean.

### Hotfix 10.5.1 — Production Database Migration & Runtime Recovery
- [x] **[P0 - Kritis] Investigasi & Pembuktian Root Cause HTTP 500 di Hostinger**:
  - *Deskripsi*: Terbukti bahwa tabel `system_settings` belum ada di basis data Hostinger karena webhook GitHub Auto Deploy tidak menjalankan `php artisan migrate --force`.
- [x] **[P0 - Kritis] Degradasi Anggun Model SystemSetting (Graceful Runtime Fallback)**:
  - *Deskripsi*: Menangkap `QueryException` khusus tabel hilang (`SQLSTATE 42S02` / 1146 / `no such table`) untuk mengembalikan default `'PPDBM MIN 1 KOTA MADIUN'` tanpa mencemari cache secara permanen dan tanpa menyembunyikan kesalahan database lainnya.
- [x] **[P0 - Kritis] Identifikasi 5 Migrasi Tertunda Non-Destruktif**:
  - *Deskripsi*: Pemetaan 5 migrasi Fase 10.4 yang berstatus pending di Hostinger, memastikan 100% aman dan tidak merusak/menghapus data pendaftar existing.
- [x] **[P0 - Kritis] Penguatan Keamanan Lingkungan Produksi (APP_DEBUG=false)**:
  - *Deskripsi*: Dokumentasi dan instruksi pengamanan `.env` Hostinger untuk menonaktifkan debug trace yang mengekspos struktur basis data dan query SQL.
- [x] **[P0 - Kritis] Suite Pengujian Otomatis 7 Skenario**:
  - *Deskripsi*: `tests/Feature/SystemSettingRecoveryTest.php` menguji seluruh dimensi fallback, non-swallowing database error, rendering login/register, dan verifikasi nol regresi autentikasi. 7 tes lulus dengan 21 asersi.
- [x] **[P1 - Tinggi] Dokumen Panduan Pemulihan Hostinger**:
  - *Deskripsi*: Pembuatan dokumen komprehensif `docs/phases/HOTFIX-10.5.1-PRODUCTION-DATABASE-RECOVERY.md`.

**Status Hotfix 10.5.1: SELESAI (100% TERUJI OTOMATIS: 286 TESTS PASSED, 1386 ASSERTIONS).** Pint & Vite compliant, composer/npm audit clean.

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

## 5. PRIORITAS REKONSILIASI 20 NOVEMBER 2026

- [x] **[P0] Hapus endpoint HTTP migrasi ber-token statis** dari `routes/web.php` dan tambahkan regression test route tidak tersedia.
- [x] **[P1] Catat hasil live applicant Hostinger** sampai PDF draft dan halaman tinjau.
- [ ] **[P1] Verifikasi consent dan Final Submit** pada Hostinger dengan akun QA.
- [ ] **[P1] Verifikasi PDF final dan QR** tanpa membocorkan PII.
- [ ] **[P2] Uji live Admin/Superadmin**: login, dashboard, daftar/detail pendaftar, dokumen, reopen, settings, dan permission boundary.
- [ ] **[P2] Verifikasi database engine/version dan runtime Hostinger** tanpa menyimpan credential.
- [ ] **[P2] Lengkapi UAT sekolah** dan validasi branding, alamat resmi, backup/restore, serta prosedur operasional.
- [ ] **[P3] Reconcile dokumentasi lama** yang masih memuat angka test atau status fitur yang tidak sesuai checkout aktual.

Go-Live produksi tetap menunggu persetujuan eksplisit pemilik proyek.

### Refactor arsitektur — Single Admin Workspace

- [x] Konsolidasikan Superadmin dan Admin PPDB ke `/admin/dashboard` dengan satu layout, header, dan sidebar.
- [x] Hapus route, view, dan workspace switcher Superadmin terpisah.
- [x] Pertahankan full access Superadmin melalui permission server-side tanpa akun atau role tambahan.
- [ ] Implementasikan modul roadmap berikutnya hanya setelah backend nyata tersedia: Communication Center, CRUD konfigurasi PPDB, user/permission management, reports, audit center, dan system health.

### Phase 3 — Communication Center Provider Foundation

- [x] Provider-neutral contract, registry, resolver, Fonnte adapter, dan fixed endpoint safety.
- [x] Encrypted Device Token storage terpisah dari metadata channel.
- [x] Device profile validation dan single active channel manual switching.
- [x] Legacy `FONNTE_TOKEN` transition tanpa silent fallback setelah database mode diaktifkan.
- [x] Superadmin-only Provider & Gateway UI dan safe communication audit events.
- [x] Superadmin-editable OTP and temporary-password WhatsApp templates with placeholder validation.
- [ ] Additional providers, template/versioning, notification rules, message logs, retention policy, dan controlled test recipients.
### Phase 4A — PPDB Configuration Management

- [x] Phase 4A.2 integrity hardening: atomic configuration/audit transactions, generated-marker uniqueness for one active period, normalized historical date checks, and program-period scope validation.
- [ ] Run disposable MySQL/MariaDB runtime concurrency validation for the active-period invariant before production deployment.
- [x] Admission Period Management dengan validasi rentang tanggal, singleton active period, dan audit.
- [x] Program Management dengan relasi periode dan perlindungan historical registration.
- [x] Document Requirements Management dengan scope periode/program, status wajib, urutan, dan batas 5120 KB.
- [ ] Live acceptance, production migration, dan deployment.

### Phase 4B — Admin User & Role/Permission Management

- [x] Admin account listing, create, edit, activate/deactivate, protected self/last-superadmin rules, and password reset request.
- [x] Protected `superadmin` role view and allowlisted `admin_ppdb` permission management with cache refresh and audit.
- [x] Server-side authorization and applicant exclusion.
- [x] Dedicated invitation/password setup/reset flow with broker tokens, resend throttling, and serialized last-Superadmin protection.
- [x] Password reset broker throttle enforcement and safe invitation/reset dispatch-failure audit events.
- [ ] Commit, push, production deployment, and live acceptance.
