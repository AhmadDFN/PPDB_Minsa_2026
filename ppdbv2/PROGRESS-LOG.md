# LOG PROGRES & FITUR SELESAI (PROGRESS-LOG.md)
**Proyek:** PPDB Online MIN 1 Kota Madiun v2 (`PPDB_Minsa_2026`)

Dokumen ini mencatat seluruh pekerjaan, fitur yang telah diselesaikan, hasil pengujian singkat, serta status push ke branch `main` (produksi). AI Agent wajib menambahkan baris/entri baru setiap kali menyelesaikan sebuah task.

---

## Ringkasan Status Fase

| Fase | Nama Fase | Status | Progres |
|---|---|---|:-:|
| Fase 0 | Pre-Flight & Kesehatan Fondasi | Selesai | 100% |
| Fase 1 | Skema Database, Model & Seeder Role/Akun | Selesai | 100% |
| Fase 2 | Rombak Penomoran Urut (Opsi B) & WA Fonnte | Selesai | 100% |
| Fase 3 | Panel Master Pengaturan Wawancara (Superadmin) | Selesai | 100% |
| Fase 4 | Portal Pewawancara (Scan QR & Sequential Gate) | Selesai | 100% |
| Fase 5 | Rekap Nilai Admin, Koreksi Ber-Audit & Ekspor CSV | Selesai | 100% |

---

## Catatan Riwayat Pekerjaan (Terbaru di Atas)

### [2026-11-26] Revisi Form Data Calon Siswa — Implementasi, Test, Commit & Push
- **Status:** Commit `6814d47` telah dipush ke `origin/main` (26 November 2026). Tidak ada deployment; verifikasi staging/production dan acceptance manual tetap pending.
- **Ringkasan Perubahan:**
  - Menambahkan field biodata, alamat, orang tua/wali, pendidikan, NISN kondisional, dan dokumen TK; nilai KK, nomor bantuan, serta NIK orang tua dienkripsi. Migrasi data lama mengosongkan kolom NIK plaintext setelah enkripsi/HMAC.
  - Kesiapan pendaftaran mencakup field baru; umur tetap dihitung runtime. PDF final memuat data keluarga yang ditentukan, NIK orang tua/wali tersamar, dan status setiap berkas yang telah diunggah.
  - Menambahkan No. Handphone Calon Siswa terpisah dari WA akun/OTP orang tua; nilai tervalidasi, readiness, review, admin detail, dan PDF privat diperbarui. NISN yang ditandai belum tersedia kini dibersihkan dari nilai lama.
  - Menambahkan snapshot lokal wilayah administrasi 2025 beserta atribusi/lisensi upstream, migration, seeder, API bertingkat dan cascading select; kode wilayah divalidasi terhadap parent dan label disimpan dari katalog. Seeder lokal mengimpor 91.599 baris (38 provinsi, 83.762 desa/kelurahan).
  - Widget peta offline dibuat dari potongan OpenStreetMap sekitar Kampus 1 (2.805 fitur; 138 jalan dan 2.667 bangunan), beserta skrip konversi, atribusi, dan catatan ODbL. Pin mendukung tap/klik, drag, keyboard; geolocation hanya diminta lewat tombol eksplisit. Preview dan backend menghitung Haversine; koordinat tidak disimpan. Fallback jarak manual aktif. Harness browser komponen memverifikasi tap/drag/keyboard, titik luar cakupan, jarak manual, 360×800 dan 390×844 tanpa overflow; bukan UAT form pendaftar penuh. Cakupan visual masih sekitar kampus.
  - Recheck browser mobile pada biodata/alamat/orang tua/pendidikan/dokumen/tinjau di viewport 360×800: tidak ada overflow horizontal. Cascade Jatim → Kota Madiun → Taman → Demangan dan tombol salin alamat orang tua berhasil. Harness widget map di 360×800/390×844 juga tanpa overflow dan console errors.
- **File Utama:** migrations, models, Form Requests, Actions, readiness/profile services, Blade formulir/PDF, factories, feature tests, dan roadmap terkait.
- **Hasil Test Faktual:**
  - `php artisan test` → PASSED (388 tests; 373 passed; 15 skipped; 1,781 assertions).
  - `vendor/bin/pint --test` → PASSED.
  - `npm.cmd run build` → PASSED.
  - `npm.cmd audit` → PASSED (0 vulnerabilities).
  - `composer audit` → PASSED saat retry (Packagist pulih; tidak ditemukan security vulnerability advisories; percobaan pertama mendapat HTTP/2 502).
  - `php artisan route:list --path=pendaftar`, `view:cache`, `view:clear`, inline map JavaScript syntax check, local map dataset integrity, `git diff --check` → PASSED.
- **UAT Browser Lokal:** Final submit, pengesahan admin dummy, dan PDF final berhasil (sebelum tambahan wilayah). Uji ulang cascade dan salin alamat lulus; akun dummy kedua mengakses PDF akun pertama dan menerima 404. JPG dummy menyelesaikan hambatan PNG/GD. Automated QR verification: 4 tests/13 assertions lulus; pemindaian QR aktual dari kamera/PDF belum dilakukan. OTP fisik/WhatsApp, staging, dan produksi tidak diuji.
- **Bukti & Gap:** Lihat `docs/phases/FORM-REVISION-IMPLEMENTATION-REPORT-2026-11-26.md`, `livetest/06-HASIL-UAT-FORM-2026-11-26.md`, `KNOWN-ISSUES.md` ISS-004/005/006/008, serta resolved ISS-009.
- **Commit/Push/Deploy:** `6814d47` berhasil dipush ke `origin/main`; deployment tidak dilakukan. UAT map pada form terautentikasi/GPS permission, foto asli, QR scan fisik, MySQL/MariaDB dan staging tercatat sebagai pekerjaan manual lanjutan.

### [2026-11-25] Hardening Fase 5 Wawancara — Sanitasi Sel Placeholder CSV (Task 5.3)
- **Commit / Branch:** `main` (commit dan push disetujui pemilik)
- **Ringkasan Perubahan:**
  - Memastikan seluruh sel data CSV, termasuk placeholder `-`, melewati sanitasi anti-formula injection.
  - Menambahkan regression assertion untuk placeholder CSV yang diawali tanda minus.
- **File Utama yang Diubah:**
  - `app/Actions/Interview/ExportInterviewRecapCsvAction.php`
  - `tests/Feature/Interview/InterviewRecapAndOverrideTest.php`
- **Hasil Test Sederhana:**
  - `php artisan test --filter=InterviewRecapAndOverrideTest` → PASSED (4 tests, 44 assertions)
- **Catatan Tambahan:** Tidak melakukan deployment atau smoke test produksi pada sesi ini.

### [2026-11-25] Eksekusi Fase 5: Rekap Nilai Admin, Koreksi Ber-Audit & Ekspor CSV (Task 5.1 - 5.4)
- **Commit / Branch:** `main`
- **Ringkasan Perubahan:**
  - **Task 5.1**: Halaman Rekap Nilai Wawancara (`/admin/interview-recaps`) khusus `admin_ppdb` & `superadmin`:
    - Menampilkan rincian skor per poin, **Jumlah (`total_score`)**, dan **Rata-rata (`average_score`)** secara terpisah untuk Tahap 1, Tahap 2, dan Tahap 3 dengan format `tabular-nums`.
    - Filter berdasarkan Jadwal/Batch, Program (Reguler/Unggulan), Status Kelengkapan (`all`, `completed`, `incomplete`), dan pencarian Nama / Nomor Urut.
    - Menambahkan menu navigasi **"Rekap Nilai Wawancara"** di sidebar admin (`admin-sidebar.blade.php`) untuk `admin_ppdb` dan `superadmin`.
  - **Task 5.2**: Koreksi Nilai Ber-Audit (`AdminOverrideInterviewScoreAction` & Modal Koreksi):
    - Form Request `OverrideInterviewScoreRequest` mewajibkan alasan koreksi minimal 10 karakter dan skor integer `0–100`.
    - Menghitung ulang `total_score` dan `average_score` di server serta mencatat `last_edited_by` dan `last_edit_reason`.
    - Mencatat JSON *before/after diff*, alasan, dan aktor ke `admin_configuration_audit_logs` dengan aksi `score_overridden`.
  - **Task 5.3**: Ekspor CSV Dinamis & Tersanitasi (`ExportInterviewRecapCsvAction`):
    - Ekspor *streamed CSV* (UTF-8 BOM) dengan kolom dinamis mengikuti kriteria aktif per tahap dan filter aktif.
    - Sanitasi *anti-formula injection* (`'` prefix untuk sel berawalan `=`, `+`, `-`, `@`, tab, CR).
  - **Task 5.4**: Pengujian Keamanan & Fungsionalitas (`InterviewRecapAndOverrideTest`):
    - Seluruh role pewawancara (`interviewer_stage_1/2/3`) dan `applicant` mendapat `403 Forbidden` pada rute rekap, koreksi nilai, dan ekspor CSV.
    - Validasi alasan koreksi, hitung ulang skor, audit log *before/after*, dan sanitasi CSV lulus 100% (4 tests, 43 assertions).
- **File Utama yang Ditambah/Diubah:**
  - `app/Actions/Interview/AdminOverrideInterviewScoreAction.php` (baru)
  - `app/Actions/Interview/InterviewRecapQueryBuilder.php` (baru)
  - `app/Actions/Interview/ExportInterviewRecapCsvAction.php` (baru)
  - `app/Http/Requests/Admin/InterviewRecapFilterRequest.php` (baru)
  - `app/Http/Requests/Admin/OverrideInterviewScoreRequest.php` (baru)
  - `app/Http/Controllers/Admin/InterviewRecapController.php` (baru)
  - `resources/views/admin/interview-recaps/index.blade.php` (baru)
  - `resources/views/components/navigation/admin-sidebar.blade.php`
  - `routes/web.php`
  - `tests/Feature/Interview/InterviewRecapAndOverrideTest.php` (baru)
  - `ppdbv2/todo-roadmap.md`
  - `ppdbv2/PROGRESS-LOG.md`

### [2026-11-24] Eksekusi Fase 4: Portal Pewawancara (Mobile-First, Scan QR & Sequential Gate) (Task 4.1 - 4.5)
- **Commit / Branch:** `main`
- **Ringkasan Perubahan:**
  - **Task 4.1**: Rute & Middleware Khusus Portal Pewawancara:
    - Rute `/wawancara/*` diproteksi middleware `auth`, `active`, dan role `interviewer_stage_1|interviewer_stage_2|interviewer_stage_3`.
    - Redirect otomatis pada login staff (`AdminLoginController`) dan rute generic `/dashboard` ke `/wawancara`.
    - Pewawancara diblokir mutlak dengan `403 Forbidden` dari seluruh rute `/admin/*` via `EnsureAdmin`.
  - **Task 4.2**: Logika Bisnis & Sequential Gate (`LookupApplicantForInterviewAction` & `StartInterviewSessionAction`):
    - Lookup cerdas dari QR URL (`route('public.registration-pdfs.verify')`), token verifikasi 64 hex, maupun nomor urut `270001`.
    - Validasi berkas: wajib telah disahkan panitia verifikator (`verified_at !== null`).
    - Validasi Sequential Gate: Tahap 2 wajib Tahap 1 COMPLETED; Tahap 3 wajib Program Unggulan dan Tahap 1 & 2 COMPLETED.
    - Soft Lock Mechanism: mencegah penilaian ganda bersamaan (< 30 menit). Mengizinkan pengambilalihan (*takeover*) jika sesi ditinggalkan terlantar >= 30 menit.
  - **Task 4.3**: UI Halaman Scan & Ergonomi Mobile (`/wawancara`):
    - Layout mandiri tanpa sidebar `layouts.interviewer` khusus perangkat bergerak/tablet dengan tema warna MINSATAMA.
    - Scanner QR kamera interaktif (`html5-qrcode`) dengan fallback mulus ke input angka manual berukuran besar (`min-h-12`).
    - Counter siswa selesai hari ini (query dinamis berdasarkan tanggal hari ini dan pewawancara).
    - Isolasi Mutlak Nilai: pewawancara tidak dapat melihat angka nilai siswa yang telah dinilai sebelumnya.
    - Banner sesi aktif yang sedang berlangsung dengan aksi Lanjutkan atau Batalkan Sesi.
  - **Task 4.4**: Form Penilaian, Score Locking & Stream Pas Foto (`/wawancara/sesi/{session}`):
    - Menampilkan pas foto 3x4 (via route streaming privat `photo` dengan proteksi IDOR dan fallback avatar SVG), nama lengkap siswa, nomor urut, program, dan jadwal batch.
    - Input skor 0-100 per kriteria aktif dengan tombol touch step (-5, -1, +1, +5).
    - Modal konfirmasi tegas "Kunci & Simpan Nilai" sebelum submit permanen.
    - `SubmitAndLockInterviewAction`: menghitung `total_score` dan `average_score` di server, mengunci status ke `COMPLETED`, mencatat riwayat ke `admin_configuration_audit_logs`.
    - Setelah sesi dikunci, pewawancara dilarang mengakses form nilai kembali (isolasi mutlak nilai).
  - **Task 4.5**: Pengujian Rangkaian Lengkap:
    - Feature test `tests/Feature/Interview/InterviewerPortalTest.php` lulus 100% (7 tests, 52 assertions).
    - Seluruh test suite sistem lulus 100% (361 passed, 15 skipped).
    - Gaya kode Laravel Pint 100% bersih.
- **File Utama yang Ditambah/Diubah:**
  - `app/Actions/Interview/LookupApplicantForInterviewAction.php` (baru)
  - `app/Actions/Interview/StartInterviewSessionAction.php` (baru)
  - `app/Actions/Interview/SubmitAndLockInterviewAction.php` (baru)
  - `app/Http/Requests/Interview/LookupApplicantRequest.php` (baru)
  - `app/Http/Requests/Interview/SubmitInterviewScoreRequest.php` (baru)
  - `app/Http/Controllers/Interview/InterviewerPortalController.php` (baru)
  - `resources/views/layouts/interviewer.blade.php` (baru)
  - `resources/views/interview/index.blade.php` (baru)
  - `resources/views/interview/session.blade.php` (baru)
  - `app/Services/Authentication/AdminAuthenticationService.php`
  - `app/Http/Controllers/Auth/AdminLoginController.php`
  - `routes/web.php`
  - `tests/Feature/Interview/InterviewerPortalTest.php` (baru)
  - `ppdbv2/todo-roadmap.md`
  - `ppdbv2/PROGRESS-LOG.md`

### [2026-11-24] Eksekusi Fase 3: Panel Master Pengaturan Wawancara (Superadmin) (Task 3.1 - 3.4)
- **Commit / Branch:** `main`
- **Ringkasan Perubahan:**
  - **Task 3.1**: Membuat CRUD Master Jadwal / Batch Wawancara (`/admin/interview-batches`):
    - Form tambah & edit batch dengan kalkulasi saran nomor urut otomatis dari batch terakhir (default 70 kuota/hari).
    - Validasi rentang nomor urut dan proteksi anti-overlap antar batch pada periode yang sama.
    - Proteksi hapus: batch yang sudah memiliki relasi `Registration` ditolak dihapus.
    - Action `SaveInterviewBatchAction` dengan pencatatan audit log via `AdminConfigurationAuditService`.
  - **Task 3.2**: Membuat CRUD Master Tahap Wawancara (`/admin/interview-stages`):
    - Tampilan komprehensif 3 tahap wawancara (`STAGE_1`, `STAGE_2`, `STAGE_3`) pada periode aktif.
    - Superadmin dapat mengubah judul, deskripsi panduan penguji, dan status aktif.
    - Kode tahap, nomor urutan, cakupan program, dan role pewawancara dikunci sistem untuk menjaga integritas Sequential Gate.
  - **Task 3.3**: Pengelolaan Poin Penilaian Wawancara (`interview_criteria`) & Proteksi Penghapusan:
    - Tambah, edit, dan atur urutan poin penilaian per tahap.
    - Proteksi Task 3.3: poin penilaian yang sudah memiliki riwayat penilaian (`InterviewScore`) DILARANG di-hard-delete (hanya bisa dinonaktifkan).
    - Proteksi minimal 1 poin penilaian wajib aktif pada tahap yang berstatus aktif.
  - **Task 3.4**: Otorisasi & Feature Test:
    - Proteksi seluruh rute master wawancara menggunakan permission `interview.manage_master` dan `Gate::before` superadmin.
    - Role lain (`admin_ppdb`, `interviewer_stage_1`, `applicant`, guest) mendapatkan `403 Forbidden` / redirect.
    - Menambahkan menu navigasi sidebar admin "Jadwal Wawancara" dan "Tahap & Poin Wawancara" khusus superadmin.
    - Feature test `tests/Feature/Interview/InterviewMasterManagementTest.php` lulus 100% (6 tests, 46 assertions).
- **File Utama yang Ditambah/Diubah:**
  - `app/Actions/Interview/SaveInterviewBatchAction.php` (baru)
  - `app/Http/Controllers/Admin/InterviewBatchController.php` (baru)
  - `app/Http/Controllers/Admin/InterviewStageController.php` (baru)
  - `app/Http/Controllers/Admin/InterviewCriterionController.php` (baru)
  - `app/Http/Requests/Admin/StoreInterviewBatchRequest.php` (baru)
  - `app/Http/Requests/Admin/UpdateInterviewBatchRequest.php` (baru)
  - `app/Http/Requests/Admin/UpdateInterviewStageRequest.php` (baru)
  - `app/Http/Requests/Admin/StoreInterviewCriterionRequest.php` (baru)
  - `app/Http/Requests/Admin/UpdateInterviewCriterionRequest.php` (baru)
  - `resources/views/admin/interview-batches/index.blade.php` (baru)
  - `resources/views/admin/interview-stages/index.blade.php` (baru)
  - `resources/views/components/navigation/admin-sidebar.blade.php`
  - `routes/web.php`
  - `app/Providers/AppServiceProvider.php`
  - `database/seeders/RoleAndPermissionSeeder.php`
  - `tests/Feature/Interview/InterviewMasterManagementTest.php` (baru)
- **Hasil Test Sederhana:**
  - `php artisan test --filter=InterviewMasterManagementTest` → PASSED (6 tests, 46 assertions)
  - `php artisan test` → PASSED (369 tests, 354 passed, 0 failed, 1657 assertions, 15 skipped)
  - `vendor/bin/pint --test` → PASSED
  - `npm run build` → PASSED
- **Catatan Tambahan:** Fase 3 100% selesai dan siap dilanjutkan ke Fase 4 (Portal Pewawancara).

### [2026-11-24] Eksekusi Fase 2: Rombak Penomoran Urut (Opsi B) & Notifikasi WhatsApp (Task 2.1 - 2.6)
- **Commit / Branch:** `main`
- **Ringkasan Perubahan:**
  - **Task 2.1**: Menunda penomoran saat formulir disubmit pendaftar (`SubmitRegistrationAction`). Status menjadi `SUBMITTED`, nomor registrasi & urutan bernilai `null` (ditandai "Menunggu Pengesahan Berkas" di dashboard & review).
  - **Task 2.2**: Membuat `ApproveAndAssignQueueNumberAction` dan memperbarui `RegistrationNumberGeneratorService::generateQueueNumber()`. Validasi seluruh berkas wajib harus `APPROVED`, penomoran format 4-digit `{tahun}{4digit}` (contoh `270001`), pemetaan otomatis `interview_batch_id` berdasarkan rentang urutan (70/batch), pencatatan audit log `verified_and_queued`, dan fallback notifikasi WhatsApp via Fonnte API.
  - **Task 2.3**: Menambahkan endpoint `POST /admin/document-verification/{registration}/sahkan` dan tombol antarmuka "Sahkan Berkas & Terbitkan Nomor" di daftar verifikasi berkas (`admin.document-verification.index`) dan detail pendaftar (`admin.applicants.show`).
  - **Task 2.4**: Memperbarui template Bukti Pendaftaran PDF (`final.blade.php` & `RegistrationPdfRenderer`) untuk mencantumkan Nomor Urut Pendaftaran, rincian Jadwal Wawancara (gelombang, tanggal, waktu, ruangan), dan QR code verifikasi. Menambahkan pencegahan pembuatan PDF final sebelum berkas disahkan panitia.
  - **Task 2.5**: Menambahkan template WhatsApp `DOCUMENT_APPROVED_SCHEDULE` di `CommunicationMessageTemplateService` dan migrasi tabel `communication_settings` dengan placeholder dinamis.
  - **Task 2.6**: Menambahkan feature test komprehensif `tests/Feature/Interview/QueueNumberAndVerificationTest.php` dan menyesuaikan suite tes eksisting.
- **File Utama yang Ditambah/Diubah:**
  - `app/Actions/Registration/ApproveAndAssignQueueNumberAction.php` (baru)
  - `app/Actions/Registration/SubmitRegistrationAction.php`
  - `app/Actions/Registration/GenerateRegistrationPdfAction.php`
  - `app/Http/Controllers/Admin/DocumentVerificationController.php`
  - `app/Services/Registration/RegistrationNumberGeneratorService.php`
  - `app/Services/Communication/CommunicationMessageTemplateService.php`
  - `database/migrations/2026_11_24_140002_add_document_approved_schedule_template.php` (baru)
  - `resources/views/admin/document-verification/index.blade.php`
  - `resources/views/admin/applicants/show.blade.php`
  - `resources/views/applicant/dashboard.blade.php`
  - `resources/views/applicant/registration/review.blade.php`
  - `resources/views/pdf/registration/final.blade.php`
  - `routes/web.php`
  - `tests/Feature/Interview/QueueNumberAndVerificationTest.php` (baru)
- **Hasil Test Sederhana:**
  - `php artisan test --filter=QueueNumberAndVerificationTest` → PASSED (6 tests, 30 assertions)
  - `php artisan test` → PASSED (363 tests, 348 passed, 0 failed, 1610 assertions, 15 skipped)
  - `vendor/bin/pint --test` → PASSED
  - `npm run build` → PASSED
- **Catatan Tambahan:** Fase 2 100% selesai dan siap dilanjutkan ke Fase 3 (Panel Master Pengaturan Wawancara Superadmin).

### [2026-11-24] Eksekusi Fase 1: Skema Database, Model & Seeder Role/Akun (Task 1.1 - 1.6)
- **Commit / Branch:** `main`
- **Ringkasan Perubahan:**
  - Membuat migrasi `2026_11_24_140000_create_interview_modules_tables.php` (tabel: `interview_batches`, `interview_stages`, `interview_criteria`, `interview_sessions`, `interview_scores`).
  - Membuat migrasi `2026_11_24_140001_add_queue_and_interview_fields_to_registrations.php` (kolom: `queue_sequence`, `verified_at`, `verified_by`, `interview_batch_id`).
  - Membuat Model Eloquent: `InterviewBatch`, `InterviewStage`, `InterviewCriterion`, `InterviewSession`, `InterviewScore` serta update relasi pada `Registration` dan `AdmissionPeriod`.
  - Memperbarui `RoleAndPermissionSeeder` dengan permissions modul wawancara dan 3 role pewawancara (`interviewer_stage_1`, `interviewer_stage_2`, `interviewer_stage_3`).
  - Membuat `InterviewAccountSeeder` (seed 3 admin_ppdb, 3 penguji tahap 1, 3 penguji tahap 2, 3 penguji tahap 3).
  - Membuat `InterviewMasterSeeder` (seed 3 tahap wawancara dinamis, 9 kriteria nilai default, dan 5 batch kuota 70 anak/hari).
  - Membuat `InterviewSchemaAndSeederTest` untuk memverifikasi integritas skema dan seeder.
- **File Utama yang Ditambah/Diubah:**
  - `database/migrations/2026_11_24_140000_create_interview_modules_tables.php`
  - `database/migrations/2026_11_24_140001_add_queue_and_interview_fields_to_registrations.php`
  - `app/Models/InterviewBatch.php`
  - `app/Models/InterviewStage.php`
  - `app/Models/InterviewCriterion.php`
  - `app/Models/InterviewSession.php`
  - `app/Models/InterviewScore.php`
  - `app/Models/Registration.php`
  - `app/Models/AdmissionPeriod.php`
  - `database/seeders/RoleAndPermissionSeeder.php`
  - `database/seeders/InterviewAccountSeeder.php`
  - `database/seeders/InterviewMasterSeeder.php`
  - `database/seeders/DatabaseSeeder.php`
  - `tests/Feature/Interview/InterviewSchemaAndSeederTest.php`
- **Hasil Test Sederhana:**
  - `php artisan test --filter=InterviewSchemaAndSeederTest` → PASSED (3 tests, 27 assertions)
  - `php artisan db:seed` → PASSED (seluruh seeder dieksekusi sukses)
- **Catatan Tambahan:** Siap melangkah ke Fase 2 (Rombak Penomoran Urut Opsi B, Aksi Approve & WhatsApp Fonnte).

### [2026-11-24] Eksekusi Fase 0: Pre-Flight & Kesehatan Fondasi (Task 0.1, 0.2, 0.3)
- **Commit / Branch:** `main`
- **Ringkasan Perubahan:**
  - Memperbaiki kompatibilitas driver SQLite pada migrasi `2026_11_24_120000_enforce_single_active_admission_period.php` dengan partial unique index `WHERE is_active = 1` saat SQLite dan `storedAs` pada MySQL.
  - Menjalankan migrasi lokal yang tertunda hingga seluruh 37 migrasi sukses `[Ran]`.
  - Menjalankan full automated test suite: 354 tests, 339 passed, 0 failed, 15 skipped (physical MySQL tests).
  - Memverifikasi batasan runtime PHP & .env (PHP 8.3, limit upload, memori).
  - Menyelesaikan issue `[ISS-001]` di `ppdbv2/KNOWN-ISSUES.md`.
- **File Utama yang Ditambah/Diubah:**
  - `database/migrations/2026_11_24_120000_enforce_single_active_admission_period.php`
  - `ppdbv2/KNOWN-ISSUES.md`
  - `ppdbv2/todo-roadmap.md`
  - `ppdbv2/PROGRESS-LOG.md`
- **Hasil Test Sederhana:**
  - `php artisan test` → PASSED (354 tests, 339 passed, 1542 assertions, 0 failures, 15 skipped)
- **Catatan Tambahan:** Fondasi pengujian lokal 100% sehat dan siap untuk Fase 1 (Skema Database Wawancara).

### [2026-11-24] Inisialisasi Dokumentasi & Perencanaan PPDB v2
- **Task Terkait:** Persiapan Blueprint, Roadmap, dan Aturan Kerja AI.
- **Ringkasan Pekerjaan:**
  - Menyusun `ppdbv2/blueprint-wawancara.md` (desain alur Opsi B penomoran `270001`, jadwal 70/hari, 3 tahap wawancara customizable, isolasi role pewawancara, kunci mutlak & rekap terpisah per tahap).
  - Menyusun `ppdbv2/todo-roadmap.md` (5 fase implementasi terstruktur + Fase 0 pre-flight).
  - Menyusun `ppdbv2/AI-RULES.md` (aturan kerja: kerjakan → test sederhana → push `main`, serta protokol anti-stuck).
  - Menyiapkan `ppdbv2/PROGRESS-LOG.md` dan `ppdbv2/KNOWN-ISSUES.md`.
- **Hasil Test:** N/A (tahap dokumentasi).
- **Status Push `main`:** Siap dimasukkan ke repositori.

---

<!-- TEMPLATE ENTRI BARU (Salin ke bagian atas daftar riwayat setiap selesai task)
### [YYYY-MM-DD] Nama Fitur / Sub-Task (Kode Task misal: 1.1)
- **Commit / Branch:** `main` (`<short-hash>`)
- **Ringkasan Perubahan:**
  - ...
- **File Utama yang Ditambah/Diubah:**
  - `...`
- **Hasil Test Sederhana:**
  - `php artisan test --filter=...` → PASSED (X tests, Y assertions)
- **Catatan Tambahan:** (Opsional)
-->
