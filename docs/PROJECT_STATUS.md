# PROJECT STATUS — PPDB MINSATAMA
## Sumber Kebenaran Tunggal Progres Pengembangan Sistem

**Terakhir Diperbarui**: 18 November 2026
**Dokumentasi Versi**: 1.1.0  
**Penanggung Jawab Teknis**: Senior Full-Stack Architect & Technical Project Manager

---

## 1. STATUS FASE SAAT INI

| Parameter | Nilai Faktual |
|---|---|
| **Fase Berjalan Saat Ini** | **Fase 5 Selesai — Menunggu Otorisasi Pemilik Proyek untuk Fase 6** |
| **Fase Terakhir Diselesaikan** | **Fase 5 — Manajemen Dokumen Persyaratan** |
| **Status Persetujuan Fase Terakhir** | **IMPLEMENTASI SELESAI; 141 PASSED, 0 FAILED, 2 SKIPPED; MENUNGGU REVIEW PEMILIK** |
| **Commit Git Terakhir yang Diverifikasi** | `e2a8bb3` (`first commit`, `main` = `origin/main`) |
| **Fase Berikutnya yang Diusulkan** | **Fase 6 — Alur Submit Pendaftaran** |
| **Status Izin Fase 6** | **BELUM DIOTORISASI; Fase 5 dihentikan sesuai stop condition** |

---

## 2. HASIL EKSEKUSI PENGUJIAN OTOMATIS TERAKHIR

- **Perintah Pengujian**: `php artisan test`
- **Waktu Eksekusi**: 18 November 2026
- **Total Pengujian**: 143 pengujian
- **Lulus (Passed)**: **141 pengujian**
- **Gagal (Failed)**: **0 pengujian**
- **Dilewati (Skipped)**: **2 pengujian** (Pengujian skema langsung pada MySQL fisik menunggu daemon MySQL lokal aktif)
- **Total Asersi**: **494 asersi**
- **Durasi Eksekusi**: 7.64 detik
- **Kepatuhan Format Kode (Pint)**: **100% PSR-12 / Laravel Code Style PASSED** (`vendor/bin/pint --test`)
- **Audit Dependensi Composer**: **0 kerentanan** (`composer audit`)
- **Audit Dependensi NPM**: **6 temuan high** pada rantai `picomatch` (development/build); `npm audit --omit=dev` **0 temuan**. Advisory belum menetapkan versi patched yang kompatibel.
- **Asset Build Vite**: **3 modules transformed, 85.02 kB CSS, 1.72 kB JS PASSED** (`npm run build`)

### Pemeriksaan Kesiapan Teknis (18 November 2026)

- Branch `main` dan remote `origin` mengarah ke `AhmadDFN/PPDB_Minsa_2026`; commit pada branch adalah root commit `e2a8bb3`. Referensi `4c64724` yang tercantum di catatan lama tidak ditemukan pada branch/remote saat ini.
- Sumber maksud perubahan riwayat tidak dapat dipastikan dari Git yang tersedia: riwayat aktual bisa merupakan repository baru atau history yang disquash/dipindahkan. Tidak ada perubahan history yang dilakukan.
- `package-lock.json` memiliki perubahan lokal pada nama package root (`PPDB_2026` ke `PPDB_Minsa_2026`), selaras dengan nama pada `package.json`; perubahan dipertahankan.
- Setelah pemeriksaan agregat SQLite menunjukkan tidak ada applicant/NIK tersimpan, dua key HMAC lokal dibuat secara independen dan hanya disimpan di `.env` yang di-ignore; APP_KEY tidak diubah. Runtime memverifikasi keberadaan dan perbedaan key tanpa menampilkan nilai.
- Kebijakan ukuran resmi adalah 5120 KB global/default; batas lebih rendah per dokumen diperbolehkan dan batas di atas 5120 KB ditolak. Migration tambahan telah diterapkan pada SQLite lokal.
- Fase 5 mengimplementasikan penyimpanan privat, retrieval persyaratan, service kelengkapan, upload/replacement, revisi historis, preview/unduh terotorisasi, UI, dan integrasi dashboard.
- Nilai PHP lokal saat diuji: `upload_max_filesize=2M`, `post_max_size=8M`. Pengaturan `upload_max_filesize` harus dinaikkan ke 5M untuk unggahan live ukuran maksimum; test 5120 KB memakai file sintetis di runner Laravel.
- Temuan NPM development/build tetap terbuka; verifikasi produksi (`npm audit --omit=dev`) bersih. MySQL fisik masih pending.
- Referensi commit lama `4c64724` belum dapat direkonsiliasi dengan riwayat aktual yang dimulai dari `e2a8bb3`; tidak ada rewrite history.

---

## 3. MATRIX PENYELESAIAN FASE PENGEMBANGAN

| No | Fase Pengembangan | Target Deliverables | Status | Catatan Teknis |
|:---:|---|---|:---:|---|
| 0 | **Inspeksi Lingkungan** | Validasi PHP 8.3, Node 24, Composer, Git, direktori proyek | **SELESAI** | Disetujui pada awal proyek |
| 1 | **Fondasi Proyek** | Laravel 13, Tailwind v4, Desain Token, Blade Layouts & Components | **SELESAI** | Lulus build Vite & uji fondasi |
| 2 | **Database & Model** | 16 Migrasi tabel, Model Eloquent, Relasi, PHP Enums, Seeders | **SELESAI** | Relasi normal & integritas foreign keys |
| 2.1 | **Audit Keamanan & DB** | Hash NIK HMAC-SHA256, enkripsi NIK, isolasi koneksi MySQL | **SELESAI BERSYARAT** | Audit kriptografi selesai; verifikasi fisik MySQL masih pending |
| 3 | **Autentikasi & OTP** | Login NIK/Email, OTP HMAC kontekstual, aktivasi bertahap, rate limiting | **SELESAI** | 91 tes lulus, 302 asersi |
| 4 | **Formulir & Profil Pendaftar** | Biodata siswa, ayah, ibu, wali, riwayat TK, pilih program, hitung umur | **SELESAI** | 129 tes lulus, 440 asersi, 2 skip (MySQL), Pint lulus |
| 5 | **Manajemen Dokumen** | Upload privat berkas pendaftaran, validasi MIME/ukuran, revisi berkas | **SELESAI** | 141 tes lulus, 2 skip MySQL; laporan implementasi Fase 5 tersedia |
| 6 | **Alur Pendaftaran PPDB** | Validasi kelengkapan, transisi status DRAFT ke SUBMITTED, lock data | *Terjadwal* | Menunggu Fase 5 selesai |
| 7 | **Mesin PDF Pendaftaran** | Cetak draft ber-watermark DRAFT, bukti registrasi resmi, versioning PDF | *Terjadwal* | Menunggu Fase 6 selesai |
| 8 | **Panel Panitia & PTSP** | Verifikasi fisik di loket, rekapitulasi jalur pendaftaran, ekspor laporan | *Terjadwal* | Menunggu Fase 7 selesai |
| 9 | **Quality Assurance & Rilis** | End-to-end testing, audit keamanan menyeluruh, panduan pengguna | *Terjadwal* | Final stage sebelum go-live |

---

## 4. STATUS LINGKUNGAN BASIS DATA (MYSQL & SQLITE)

1. **Target Produksi (Production Target)**: MySQL 8.x / MariaDB (InnoDB Engine, UTF8MB4).
2. **Lingkungan Pengujian Terisolasi**: SQLite in-memory (`:memory:`) digunakan untuk continuous test suite agar pengujian berjalan cepat, atomik, dan tidak mengotori database fisik.
3. **Status Verifikasi Fisik MySQL**:
   - Konfigurasi koneksi `mysql_test` ke database `ppdb_minsatama_test` telah siap pada `config/database.php`.
   - Daemon service MySQL pada port 3306 di mesin lokal Windows saat ini belum dijalankan (*not listening*).
   - Pengujian `MySqlSchemaCompatibilityTest` (2 tes) dilewati secara terencana (*gracefully skipped*) sampai server MySQL fisik lokal dijalankan oleh administrator server.
4. **SQLite persisten lokal**:
   - Migration additive untuk default `document_requirements.max_size_kb` menjadi 5120 telah dijalankan setelah status migration dan kesiapan database diperiksa; tidak ada migration destruktif.

---

## 5. CAPAIAN FITUR FASE 4–5

1. **Service Profil Terpusat (`ApplicantProfileService`)**:
   - Menghitung kelengkapan profil pendaftar secara realtime (`biodata`, `address`, `father`, `mother`, `guardian`, `education`, `program`).
   - Kalkulasi persentase kelengkapan 0-100% dan status terintegrasi.
   - Mengambil data periode pendaftaran yang berstatus aktif.
2. **Kalkulasi Usia Dinamis (`Applicant::calculateAgeAt`)**:
   - Usia dihitung secara matematis pada saat runtime terhadap tanggal acuan periode pendaftaran (`age_reference_date`).
   - Tidak pernah disimpan sebagai kolom permanen pada basis data, menjaga normalisasi 3NF.
3. **Pemisahan Logika Bisnis Bersih (Action Pattern)**:
   - `UpdateApplicantBiodataAction`: Pembaruan data siswa dan perlindungan NIK permanen.
   - `SaveApplicantAddressAction`: Pembaruan alamat dan preservasi leading zeros pada RT/RW.
   - `SaveApplicantParentsAction`: Penyimpanan atomik ayah, ibu (wajib), dan wali (opsional) menggunakan database transaction.
   - `SaveApplicantEducationAction`: Riwayat TK/RA/PAUD/NONE.
   - `SelectAdmissionProgramAction`: Pemilihan program pendidikan (Unggulan / Reguler) yang tersimpan sebagai `RegistrationStatus::DRAFT`.
4. **Keamanan & Validasi Masukan**:
   - 5 Form Requests independen dengan validasi berbahasa Indonesia yang bersahabat.
   - Proteksi IDOR ketat: Semua operasi mengikat context `$request->user()->applicant`.
   - Enkripsi NIK tetap aman, input NIK tidak dapat dimanipulasi melalui request form.
5. **Manajemen Dokumen (Fase 5)**:
   - Persyaratan hanya diambil untuk periode/program pendaftaran pendaftar; `DocumentCompletenessService` menghitung status dokumen wajib.
   - Upload memvalidasi ekstensi, MIME Fileinfo, SHA-256, maksimum global/default 5120 KB, dan batas per dokumen yang lebih kecil.
   - Replacement mencatat metadata revisi dan mempertahankan file historis. File baru dibersihkan jika persistensi database gagal.
   - Preview, download, dan revisi historis dilayani oleh route terotorisasi dengan pembatasan ownership; tidak ada public URL.
   - Halaman pengelolaan dokumen dan progress dashboard tersedia. Tidak ada persetujuan dokumen atau pengiriman final.

---

## 6. PROTOKOL STOP CONDITION

Fase 5 telah diimplementasikan dan diuji. Agen **BERHENTI** pada batas Fase 5 dan menunggu review serta persetujuan eksplisit Pemilik Proyek sebelum memulai Fase 6. Fase 5 tidak mencakup submit final, nomor registrasi, PDF, seleksi, persetujuan dokumen, atau pelaporan administrator.
