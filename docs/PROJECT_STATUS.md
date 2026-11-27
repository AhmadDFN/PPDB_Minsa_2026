# PROJECT STATUS — PPDB MINSATAMA
## Sumber Kebenaran Tunggal Progres Pengembangan Sistem
### Sistem Informasi PPDB MIN 1 Kota Madiun (Tahun Ajaran 2026/2027)

**Terakhir Diperbarui**: 26 November 2026
**Dokumentasi Versi**: 1.6.0
**Penanggung Jawab Teknis**: Senior Software Engineer, Laravel Architect, QA Lead, Technical Writer & IT Operations Consultant

> **Insiden migrasi wilayah — 27 November 2026:** Log yang diberikan menunjukkan `migrate:fresh --seed` menghapus tabel sebelum migrasi `administrative_regions` gagal. Urutan indeks unik dan foreign key diperbaiki secara lokal; validasi MariaDB, pemeriksaan tabel parsial, backup/pemulihan data, dan deployment masih pending. Audit Composer juga menemukan lima advisory pada tiga paket Symfony. Lihat `ppdbv2/KNOWN-ISSUES.md` ISS-011 dan `ppdbv2/PROGRESS-LOG.md`.

> **Rekonsiliasi Admin/Superadmin — 20 November 2026:** Blueprint dan governance dokumentasi tersedia di `docs/blueprints/`. Audit kode aktual menemukan dashboard, applicant management, reopen, dan branding settings telah tersedia; verifikasi dokumen, ekspor, master periode/program/persyaratan, lifecycle akun admin, serta audit viewer belum memiliki UI/controller lengkap. Permission di seeder tidak dihitung sebagai implementasi. Fase blueprint ini tidak mengubah kode, database, `.env`, akun, deployment, commit, atau push.

> **Phase 5A — 23 November 2026:** Verifikasi dokumen telah committed dan pushed ke `origin/main` pada `6e7f4d9` dan `3b5731a` (305 passed, 11 skipped, 1.376 assertions). Deployment, migration production, dan live verification tetap **PENDING**.

> **Phase 5B / 5B.1 — 23 November 2026:** Laporan admin dan redacted CSV export diimplementasikan pada branch `feature/ppdb-admin-reports-export`. Hardening commit `1465c58` menghapus risiko N+1 pada streaming dan memastikan rate limiter berjalan sebelum query berat. Branch final telah pushed dan diverifikasi; merge, deployment, dan live verification masih **PENDING**.

> **Phase 5C / 5C.1 — 23 November 2026:** Audit Viewer read-only `/admin/audit-logs` dan review fixes telah dipush pada stacked branch `feature/ppdb-audit-viewer`. Action filter lintas sumber telah diverifikasi; actor Applicant dianonimkan sebagai `Pendaftar`. Focused suite 5 passed/45 assertions dan full suite 317 passed/11 skipped/1.451 assertions. PR #2 masih OPEN; merge, deployment, dan live verification **PENDING**.

> **Hotfix Superadmin Settings & Admin Provisioning — 23 November 2026:** Perbaikan lokal atomik untuk `system_settings` + `system_setting_logs`, cache clear setelah commit, logging diagnosis provisioning tanpa credential, dan regression coverage telah dibuat pada branch `fix/superadmin-settings-admin-provisioning`. Ketersediaan migration production dan live verification belum terbukti.

> **Fase 5 Modul Wawancara — 25 November 2026:** Rekap nilai admin, koreksi nilai ber-audit, dan ekspor CSV streamed telah diaudit dan diperkuat. Sanitasi anti-formula injection kini berlaku untuk seluruh sel data, termasuk placeholder `-`. Focused suite lulus 4 test/44 assertion; full suite dan quality gates lulus. Commit dan push ke `main` disetujui pemilik; deployment dan smoke test produksi tidak dilakukan.

> **Revisi Form Calon Siswa — 26 November 2026 (STATUS: PARTIAL, COMMITTED & PUSHED):** Implementasi form additive, field No. Handphone calon siswa, dan perlindungan enkripsi data sensitif tersedia pada working tree. Dataset wilayah lokal 2025 (91.599 baris), API cascade tervalidasi, salin alamat orang tua, serta koordinat Kampus 1 dari direktori Kemendikdasmen telah ditambahkan. Widget peta memakai data OSM statis lokal dengan pin tap/drag/keyboard dan Haversine; koordinat tidak disimpan. Harness browser widget lulus pada 360×800/390×844, tetapi UAT map di form pendaftar terautentikasi masih pending dan cakupan visual hanya sekitar Kampus 1. PDF privat mencakup data keluarga, NIK tersamar, dan status dokumen yang diunggah. Full suite lulus (388 test, 373 passed, 15 skipped, 1.781 assertions); Pint, Vite build, npm/Composer audit, route/view, map JS syntax dan diff-check lulus. UAT submit/verifikasi admin/PDF final pernah berhasil; recheck IDOR PDF serta dokumen lintas akun menghasilkan 404. Viewport 360 px untuk enam langkah formulir telah diuji sebelum widget map ditambah. Masih pending: form UAT GPS/map, foto asli/latar biru, scan QR fisik, validasi MySQL/MariaDB, staging dan production. Commit `6814d47` berhasil dipush ke `origin/main`; deployment tidak dilakukan. Rincian: `ppdbv2/PROGRESS-LOG.md`, `ppdbv2/KNOWN-ISSUES.md`, `livetest/06-HASIL-UAT-FORM-2026-11-26.md`.

---

> **Live Smoke Test Publik — 26 November 2026:** Beranda dan halaman login/registrasi merespons; rute pendaftar tanpa autentikasi dialihkan ke login. Pada 360/390 px tidak ditemukan overflow di login/registrasi, tetapi pada 768 px keduanya melebar sampai 825 px (ISS-010). Akun dummy belum diverifikasi tersedia di produksi, sehingga tidak ada login, submit, upload, atau perubahan record. Detail: `livetest/07-HASIL-LIVE-SMOKE-PRODUKSI-2026-11-26.md`.

> **27 November 2026:** Lima akun dummy draft kosong telah dibuat dan diperiksa pada database lokal menggunakan `BlankApplicantLiveTestSeeder`; kredensial acak disimpan privat dan tidak tersedia otomatis di server publik. Panduan: `livetest/08-LOGIN-LIMA-DUMMY.md`.

> **UAT live 27 November 2026:** Akun dummy berhasil submit dan mengunduh PDF final dua halaman di server live; QR fisik dan IDOR live masih pending. Perbaikan lokal jumlah saudara dan salin wilayah orang tua sedang diverifikasi; lihat `livetest/09-HASIL-UAT-LIVE-2026-11-27.md`. Lima advisory Composer ditangani melalui pembaruan dependensi; audit lokal terbaru bersih (ISS-012), verifikasi versi server masih pending.

> **Rekonsiliasi UAT 27 November 2026:** Perbaikan jumlah saudara, salin wilayah, navbar tablet, pesan PDF, dan tanggal PDF telah dikomit. Pesan tinjau terbaru terlihat di server; verifikasi live terhadap perbaikan lain masih pending. Composer audit bersih setelah pembaruan delapan paket; full suite 375 passed, 15 skipped, 1.793 assertions. Rincian: `ppdbv2/PROGRESS-LOG.md` dan `livetest/09-HASIL-UAT-LIVE-2026-11-27.md`.

## 1. STATUS FASE SAAT INI

| Parameter | Nilai Faktual |
|---|---|
| **Fase Berjalan Saat Ini** | **REKONSILIASI STATUS & LIVE ACCEPTANCE HOSTINGER** |
| **Status Teknis Hotfix 10.5.1** | **SELESAI (100% TERUJI OTOMATIS: 286 TESTS PASSED, 1386 ASSERTIONS, PINT & VITE COMPLIANT)** |
| **Status UAT Sekolah** | **APPLICANT LIVE TEST PASS WITH BLOCKER; ADMIN/SUPERADMIN LIVE TEST PENDING** |
| **Status Lingkungan Staging** | **ONLINE/TESTED DI HOSTINGER: https://ahmaddanyfn.biz.id** |
| **Status Gerbang Produksi (aaPanel VPS)** | **HOLD (DILARANG DEPLOY SEBELUM PERSETUJUAN EKSPLISIT PEMILIK)** |
| **Status Logo Resmi Madrasah** | **INTEGRATED (`public/images/logo-minsatama.png` seragam di seluruh portal & PDF)** |
| **Commit Baseline Sebelum Hotfix** | `550d0e2` |
| **Revisi Form Calon Siswa** | **PARTIAL — pushed; live smoke test menemukan tablet overflow, end-to-end masih pending** |

---

## 2. HASIL EKSEKUSI PENGUJIAN OTOMATIS TERAKHIR (HOTFIX 10.5.1)

> Angka pada bagian hotfix ini adalah snapshot historis laporan hotfix. Hasil checkout aktual 20 November 2026 dicatat pada Bagian 7 dan laporan `docs/audits/STATUS-RECONCILIATION-2026-11-20.md`.

- **Perintah Pengujian**: `php artisan test`
- **Waktu Eksekusi**: 20 November 2026
- **Total Pengujian**: **286 pengujian**
- **Lulus (Passed)**: **286 pengujian (100%)**
- **Gagal (Failed)**: **0 pengujian**
- **Dilewati (Skipped)**: **0 pengujian**
- **Total Asersi**: **1.386 asersi**
- **Durasi Eksekusi**: ~14.4 detik
- **Kepatuhan Format Kode (Pint)**: **PASSED (0 issues)** (`vendor/bin/pint --test`)
- **Audit Dependensi Composer**: **0 advisories** (`composer audit`)
- **Audit Dependensi NPM Produksi**: **0 vulnerabilities** (`npm.cmd audit --omit=dev`)
- **Asset Build Vite**: **PASSED** (3 modules transformed, CSS 117.44 kB, JS 1.72 kB)

### Rincian Suite Pengujian Tambahan Hotfix 10.5.1:
1. **System Setting Recovery Suite** (`tests/Feature/SystemSettingRecoveryTest.php`):
   - 7 pengujian komprehensif mencakup pembacaan default fallback nama institusi, pembacaan nilai kustom hasil konfigurasi Superadmin, simulasi ketahanan runtime saat tabel `system_settings` belum terbentuk (menolak HTTP 500 dan mencegah pencemaran cache permanen), penegakan agar error database non-tabel tidak disembunyikan, perenderan bersih halaman `/login` dan `/daftar`, serta verifikasi nol regresi autentikasi pendaftar maupun admin.

---

## 3. MATRIX PENYELESAIAN FASE PENGEMBANGAN

| No | Fase Pengembangan | Target Deliverables | Status | Catatan Teknis |
|:---:|---|---|:---:|---|
| 0 | **Inspeksi Lingkungan** | Validasi PHP 8.3, Node 24, Composer, Git, direktori proyek | **SELESAI** | Lulus verifikasi awal proyek |
| 1 | **Fondasi Proyek** | Laravel 13, Tailwind v4, Desain Token, Blade Layouts & Components | **SELESAI** | Lulus build Vite & token MINSATAMA |
| 2 | **Database & Model** | 16 Migrasi tabel, Model Eloquent, Relasi, PHP Enums, Seeders | **SELESAI** | Relasi normal & integritas foreign keys |
| 2.1 | **Audit Keamanan & DB** | Hash NIK HMAC-SHA256, enkripsi NIK, isolasi koneksi MySQL | **SELESAI** | Kriptografi AES-256-CBC & HMAC-SHA256 |
| 3 | **Autentikasi & OTP** | Login NIK/Email, OTP HMAC kontekstual, aktivasi bertahap, rate limiting | **SELESAI** | 91 tes lulus, 302 asersi |
| 4 | **Formulir & Profil Pendaftar** | Biodata siswa, ayah, ibu, wali, riwayat TK, pilih program, hitung umur | **SELESAI** | Kalkulasi umur dinamis runtime, 4 modul formulir |
| 5 | **Manajemen Dokumen** | Upload privat berkas pendaftaran, validasi MIME/ukuran, revisi berkas | **SELESAI** | Global/default 5120 KB; validasi MIME finfo aktual |
| 6 | **Alur Pendaftaran PPDB** | Validasi kesiapan, consent, submit idempoten, nomor, audit revisi, admin reopen | **SELESAI** | Submit transaksi atomik, nomor unik per tahun ajaran-program |
| 7 | **Mesin PDF Pendaftaran** | Cetak draft ber-watermark DRAFT, bukti registrasi resmi, versioning PDF, QR verifikasi, akses privat | **SELESAI** | Dompdf sandboxed, QR SVG minimal, integritas SHA-256 |
| 8 | **Integrasi Sistem, UAT & Kesiapan Produksi** | End-to-end testing (7 skenario), audit keamanan menyeluruh, UAT test plan, performa/benchmark, checklist produksi, panduan backup/restore | **SELESAI** | 203 tes lulus (100%); dokumen UAT, checklist produksi, panduan deployment & backup selesai |
| 9 | **Hostinger Staging Deployment & UAT** | Pemeriksaan Hostinger, paket deploy staging, skema MySQL bersih, panduan Hostinger & aaPanel, lembar hasil UAT | **SELESAI** | Paket ZIP 23.9MB steril, skema `schema_clean.sql`, panduan `HOSTINGER-STAGING-GUIDE.md` & `AAPANEL-PRODUCTION-GUIDE.md` selesai; siap uji fisik |
| 10 | **Operational Docs, User Manual & SOP** | 12 buku panduan operasional (Orang Tua, Admin, Superadmin, Matriks Hak Akses, SOP Panitia, FAQ, Pedoman Privasi, Glosarium, Analisis Gap, Briefing, Quick Guide) | **SELESAI** | 12 dokumen operasional di `docs/manual/`, 100% tervalidasi terhadap kode aktual repositori, nol perubahan kode aplikasi |
| 10.1 | **Admin Dashboard Real Data & Resilient Navigation** | Controller dashboard data riil, UI dashboard informatif (5 metrik, hero periode, tabel pendaftar terbaru, quick actions), sidebar aman bebas 404 | **SELESAI** | `AdminDashboardController` + Blade view riil, 12 feature tests baru, 215 tests passed, 0 failed, Pint & Vite compliant |
| 10.2 | **Integrasi Gateway WhatsApp Fonnte API** | Driver `FonnteOtpDelivery`, normalisasi nomor Indonesia, validasi status Fonnte, penanganan error/timeout, template aktivasi & pemulihan, 20 test cases otomatis | **SELESAI** | 235 tests passed (100%), 1150 assertions, panduan operasional `FONNTE-WHATSAPP-INTEGRATION-GUIDE.md`, siap uji manual pemilik proyek |
| 10.3 | **Pending Registration Recovery & NIK Reuse** | Mekanisme recovery pendaftaran tertunda, verifikasi identitas, update nomor WA, invalidasi OTP lama, zero-duplicate guarantee, 20 test cases otomatis | **SELESAI** | 256 tests passed (100%), 1217 assertions, laporan komprehensif `PHASE-10.3-IMPLEMENTATION-REPORT.md`, siap deploy |
| 10.4 | **Simplifikasi Registrasi, Auto-Password, No. 5-Digit & Dynamic Branding** | Formulir 4 input tanpa password/biodata, temporary password otomatis via WA, wajib ganti password di awal login, no reg 5-digit `YYNNN` lintas program maks 999, settings branding Superadmin | **SELESAI** | 265 tests passed (100%), 1302 assertions, laporan komprehensif `PHASE-10.4-IMPLEMENTATION-REPORT.md`, siap deploy |
| 10.5 | **Admin Dashboard Dinamis & Manajemen Data Pendaftar** | Dashboard metrik riil (global & periode), daftar pendaftar paginasi 15/hal, multi-search (nama, reg YYNNN, NIK 16 digit via HMAC hash), filter (periode, program, status), detail pendaftar 11 seksi kartu info, hitung umur dinamis runtime, pratinjau & unduh berkas privat dengan proteksi IDOR lintas pendaftar, audit logs aktivitas terkini | **SELESAI** | 279 tests passed (100%), 1365 assertions, 14 feature tests baru di `ApplicantManagementTest`, laporan komprehensif `PHASE-10.5-IMPLEMENTATION-REPORT.md`, siap deploy |
| 10.5.1 | **Hotfix Pemulihan Basis Data & Runtime Recovery** | Menangani kegagalan HTTP 500 akibat tabel `system_settings` belum termigrasi di Hostinger, degradasi anggun aman tanpa mencemari cache, identifikasi 5 migrasi pending non-destruktif, audit keamanan `APP_DEBUG=false` | **SELESAI** | 286 tests passed (100%), 1386 assertions, 7 feature tests baru di `SystemSettingRecoveryTest`, panduan migrasi komprehensif `HOTFIX-10.5.1-PRODUCTION-DATABASE-RECOVERY.md` |

---

## 4. STATUS LINGKUNGAN BASIS DATA (MYSQL & MARIADB)

1. **Target Produksi**: MariaDB 10.11+ LTS / MySQL 8.0+ (InnoDB Engine, UTF8MB4).
2. **Lingkungan Pengujian Terisolasi**: SQLite in-memory (`:memory:`) digunakan untuk continuous test suite agar pengujian berjalan atomik dan cepat.
3. **Status Verifikasi Fisik MariaDB**:
   - Pengujian fisik menggunakan MariaDB 10.4.32/InnoDB melalui koneksi `mysql_test` ke database `ppdb_phase6_test`.
   - Sepuluh tes migrasi Fase 1–7, skema tabel, default 5120 KB, kolom & unique index PDF, submit multi-proses, rollback, dan retry **100% LULUS**.
4. **Status Skema MySQL 8 Staging**:
   - Skema DDL bersih `database/schema_clean.sql` siap diimpor ke phpMyAdmin MySQL/MariaDB Hostinger.

---

## 5. DOKUMEN OPERASIONAL & KESIAPAN PRODUKSI TERSEDIA

1. `docs/phases/HOTFIX-10.5.1-PRODUCTION-DATABASE-RECOVERY.md`: Laporan dan panduan pemulihan basis data produksi dan runtime recovery Hostinger.
2. `docs/phases/PHASE-10.5-IMPLEMENTATION-REPORT.md`: Laporan komprehensif implementasi Fase 10.5 (Admin Dashboard Dinamis & Manajemen Data Pendaftar).
2. `docs/phases/PHASE-10.4-IMPLEMENTATION-REPORT.md`: Laporan komprehensif implementasi Fase 10.4 (Simplifikasi Registrasi, Auto-Generated Password, Nomor 5-Digit & Dynamic Branding).
3. `docs/phases/PHASE-10.3-IMPLEMENTATION-REPORT.md`: Laporan komprehensif implementasi Fase 10.3 (Pending Registration Recovery & NIK Reuse).
4. `docs/phases/PHASE-10.2-IMPLEMENTATION-REPORT.md` / `docs/manual/FONNTE-WHATSAPP-INTEGRATION-GUIDE.md`: Panduan & laporan integrasi WhatsApp Fonnte API.
3. `docs/manual/`: Direktori 12 dokumen operasional lengkap:
   - `README.md` (Indeks master panduan)
   - `01-PANDUAN-ORANG-TUA-WALI.md` (Buku panduan lengkap 14 bab untuk orang tua)
   - `02-PANDUAN-ADMIN-PPDB.md` (Buku panduan operasional panitia admin)
   - `03-PANDUAN-SUPERADMIN.md` (Buku panduan pengelola sistem superadmin)
   - `04-MATRIKS-ROLE-DAN-PERMISSION.md` (Matriks hak akses komprehensif)
   - `05-SOP-OPERASIONAL-PANITIA.md` (Standar operasional prosedur kepanitiaan)
   - `06-FAQ-DAN-TROUBLESHOOTING.md` (Tanya jawab & pemecahan masalah)
   - `07-PEDOMAN-KEAMANAN-PENGGUNA.md` (Pedoman keamanan & privasi UU PDP)
   - `08-ALUR-KERJA-SISTEM.md` (Diagram alur kerja Mermaid)
   - `09-GLOSARIUM.md` (Kamus istilah sistem & madrasah)
   - `10-DAFTAR-FITUR-DAN-GAP.md` (Analisis celah fitur & workaround)
   - `BRIEFING-PANITIA-PPDB.md` (Ringkasan materi briefing panitia)
   - `QUICK-GUIDE-ORANG-TUA.md` (Panduan kilat pendaftaran 3 menit)
4. `docs/phases/PHASE-10-IMPLEMENTATION-REPORT.md`: Laporan komprehensif 16 seksi implementasi Fase 10.
5. `docs/phases/PHASE-9-IMPLEMENTATION-REPORT.md`: Laporan komprehensif 18 seksi implementasi Fase 9.
6. `docs/deployment/HOSTINGER-STAGING-GUIDE.md`: Panduan langkah demi langkah staging Hostinger.
7. `docs/deployment/AAPANEL-PRODUCTION-GUIDE.md`: Cetak biru operasional server produksi VPS dengan aaPanel.
8. `docs/uat/UAT-TEST-PLAN.md`: Rencana uji penerimaan pengguna (UAT) untuk kelompok Pendaftar dan Panitia/Admin.
9. `docs/uat/UAT-RESULTS.md`: Hasil evaluasi 21 skenario UAT.
10. `docs/deployment/PRODUCTION-READINESS-CHECKLIST.md`: Matriks evaluasi runtime PHP, batas upload, memori, ekstensi, dan konfigurasi environment.
11. `docs/deployment/DEPLOYMENT-GUIDE.md`: Panduan langkah demi langkah deployment dan pembaruan aplikasi ke Hostinger/VPS.
12. `docs/deployment/BACKUP-AND-RESTORE.md`: Prosedur pencadangan basis data, berkas privat, dan pemulihan bencana.
13. `docs/audits/SECURITY-AUDIT.md`: Laporan audit keamanan 18 dimensi web application security.
14. `docs/audits/TEST-REPORT.md`: Laporan eksekusi automated test cases.
15. `docs/audits/KNOWN-ISSUES.md`: Catatan isu terbuka, prasyarat operasional hosting, dan mitigasi.
16. `docs/phases/PHASE-8-IMPLEMENTATION-REPORT.md`: Laporan komprehensif implementasi Fase 8.

---

## 6. PROTOKOL KONDISI BERHENTI (STOP CONDITION)

Hotfix 10.5.1 selesai secara penuh. Akar penyebab HTTP 500 berhasil dibuktikan (tabel `system_settings` belum termigrasi pada Hostinger), pemulihan runtime tanpa pencemaran cache terimplementasi dan teruji, 5 migrasi tertunda teridentifikasi aman/non-destruktif, dan panduan eksekusi pemilik proyek tersusun lengkap (286 pengujian, 1.386 asersi, 0 gagal). Agen BERHENTI dan menunggu pemilik proyek menerapkan migrasi di Hostinger. DILARANG melakukan `git push` atau deploy ke server produksi secara mandiri.

## 7. REKONSILIASI AKTUAL 20 NOVEMBER 2026

- Hostinger online/testing pada `https://ahmaddanyfn.biz.id`; GitHub Auto Deploy dan penggunaan database/upload telah dikonfirmasi pemilik proyek.
- Integrasi Fonnte diklasifikasikan **IMPLEMENTED + LIVE TESTED + OWNER CONFIRMED** berdasarkan pengiriman OTP yang berhasil pada uji langsung.
- Applicant E2E live berjalan sampai pengisian data, upload tiga dokumen, PDF draft, dan halaman tinjau.
- Consent, Final Submit, status `SUBMITTED`, PDF final, dan QR masih **PENDING VERIFICATION** pada laporan live terakhir.
- Admin dan Superadmin telah memiliki implementasi kode, tetapi pengujian operasional live belum memadai.
- Endpoint HTTP migrasi produksi ber-token statis telah dihapus dari source lokal dan diuji agar tidak tersedia melalui HTTP. Migrasi berikutnya wajib melalui CLI SSH setelah backup.
- Automated test checkout saat ini: 289 tests, 278 passed, 11 skipped, 1.273 assertions. Skipped berasal dari integrasi MySQL/pdo_mysql dan satu skenario GD/PNG sesuai kondisi test.
- Laporan live applicant: `docs/uat/LIVE-APPLICANT-E2E-REPORT-2026-11-20.md`.
- Catatan environment Hostinger: `docs/deployment/CURRENT-HOSTINGER-ENVIRONMENT.md`.

## 8. FASE 10.6 — SINGLE ADMIN WORKSPACE

**Status:** IMPLEMENTED LOCALLY — PENDING OWNER REVIEW / COMMIT

- `/admin/dashboard` menjadi satu-satunya dashboard administrasi untuk `admin_ppdb` dan `superadmin`.
- Kedua role menggunakan layout, header, sidebar, dan dashboard operasional yang sama; perbedaan akses ditentukan oleh Spatie Permission/Gate di server.
- Superadmin mempertahankan full access melalui role yang sudah ada tanpa akun kedua, impersonation, atau role tambahan.
- Dashboard menampilkan metrik periode aktif, tren submission tujuh hari, pendaftar terbaru, aktivitas pendaftaran, dan quick actions yang benar-benar tersedia.
- Route/view/workspace Superadmin terpisah dihapus. Fitur verifikasi dokumen, CRUD periode/program, ekspor, manajemen pengguna, audit viewer, dan infrastructure monitoring tetap GAP sesuai roadmap; Communication Center sudah memiliki provider foundation dan editor template chat.

## 9. PHASE 3 — COMMUNICATION CENTER PROVIDER FOUNDATION

**Status:** IMPLEMENTED LOCALLY — PENDING OWNER REVIEW / COMMIT

- Provider abstraction dan registry provider-neutral tersedia; Fonnte menjadi adapter pertama.
- Device Token Fonnte divalidasi melalui device profile, credential disimpan terenkripsi, dan metadata sender disimpan tanpa token.
- Channel provider, credential, global active channel, dan audit log memiliki tabel terpisah melalui migration non-destructive.
- Hanya satu channel aktif secara global dan pergantian provider dilakukan manual/atomic.
- Alur OTP dan temporary password mempertahankan legacy `FONNTE_TOKEN` sampai database provider mode diaktifkan secara eksplisit.
- Setelah database mode aktif, tidak ada silent fallback atau automatic failover.
- Communication Center dan empat template chat WhatsApp hanya tersedia untuk Superadmin melalui `manage_communication_settings`.
- Template, notification rules, message logs, additional providers, arbitrary test-send, dan Account API device management belum diimplementasikan.
## PHASE 4A — PPDB CONFIGURATION MANAGEMENT

- Phase 4A.2 hardening adds transactional audit consistency, a database-enforced single-active-period marker, normalized historical-date protection, and validation of program/period scope for document requirements. SQLite tests cover the invariant; MySQL/MariaDB runtime concurrency validation remains pending.

**Status:** IMPLEMENTED LOCALLY — PENDING OWNER REVIEW

- Admission Period, Program, dan Document Requirements management tersedia dalam satu Admin Workspace.
- Perubahan master data dilindungi permission server-side dan dicatat pada audit konfigurasi.
- Tidak ada physical delete route untuk menjaga historical registration dan dokumen existing.
- Verifikasi live, deployment, dan migration production masih pending.

## PHASE 4B — ADMIN USER & ROLE/PERMISSION MANAGEMENT

**Status:** IMPLEMENTED LOCALLY — AUTOMATED TESTED — COMMIT/PUSH/DEPLOY/LIVE VERIFIED PENDING

- Admin account lifecycle tersedia untuk Superadmin melalui server-side permission `manage_admin_users`.
- Applicant accounts tidak muncul pada listing administrator.
- Role `superadmin` dilindungi; permission `admin_ppdb` dibatasi allowlist dan diaudit.
- Password reset menggunakan password broker existing dan notification fake pada test.
- Admin invitation/password setup menggunakan dedicated named routes, broker token expiry/throttle, one-time completion, dan transisi pending-to-active; delivery email production belum diverifikasi.
- Password reset throttling menggunakan API `sendResetLink()` broker dan dispatch failure dicatat secara aman tanpa token atau URL.
