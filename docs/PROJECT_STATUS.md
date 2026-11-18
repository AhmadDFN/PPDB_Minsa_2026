# PROJECT STATUS — PPDB MINSATAMA
## Sumber Kebenaran Tunggal Progres Pengembangan Sistem

**Terakhir Diperbarui**: 18 November 2026
**Dokumentasi Versi**: 1.1.0  
**Penanggung Jawab Teknis**: Senior Full-Stack Architect & Technical Project Manager

---

## 1. STATUS FASE SAAT INI

| Parameter | Nilai Faktual |
|---|---|
| **Fase Berjalan Saat Ini** | **Penutupan Fase 6 TERHAMBAT — menunggu keputusan pemilik dan verifikasi MySQL** |
| **Implementasi Fase Terakhir** | **Fase 6 — Alur Pendaftaran dan Submit Final (closure belum lulus)** |
| **Status Persetujuan Fase Terakhir** | **IMPLEMENTASI DIAUDIT; FASE 6 BELUM CLOSED; 159 PASSED, 0 FAILED, 2 SKIPPED** |
| **Commit Implementasi Fase 6** | `311e099` (`feat(registration): implement final submission workflow`) |
| **Fase Berikutnya yang Diusulkan** | **Fase 7 — Pembuatan Dokumen Bukti Pendaftaran PDF** |
| **Status Fase 7** | **BLOCKED sampai seluruh gate Fase 6 lulus dan pemilik memberi otorisasi** |

---

## 2. HASIL EKSEKUSI PENGUJIAN OTOMATIS TERAKHIR

- **Perintah Pengujian**: `php artisan test --compact`
- **Waktu Eksekusi**: 18 November 2026
- **Total Pengujian**: 161 pengujian
- **Lulus (Passed)**: **159 pengujian**
- **Gagal (Failed)**: **0 pengujian**
- **Dilewati (Skipped)**: **2 pengujian** (Pengujian skema langsung pada MySQL fisik menunggu daemon MySQL lokal aktif)
- **Total Asersi**: **618 asersi**
- **Durasi Eksekusi**: 8.72 detik
- **Kepatuhan Format Kode (Pint)**: **PASSED** (`vendor/bin/pint --dirty --format agent`)
- **Audit Dependensi Composer**: **0 kerentanan** (`composer audit`)
- **Audit Dependensi NPM**: **6 temuan high** pada rantai `picomatch` (development/build); `npm audit --omit=dev` **0 temuan**. Advisory belum menetapkan versi patched yang kompatibel.
- **Asset Build Vite**: **3 modules transformed, 85.88 kB CSS, 1.72 kB JS PASSED** (`npm.cmd run build`)

### Pemeriksaan Teknis Terbaru (18 November 2026)

- Pada awal audit penutupan, branch `main` dan `origin/main` sama-sama menunjuk `311e099`; worktree bersih. Remote terkonfigurasi sebagai `AhmadDFN/PPDB_Minsa_2026`.
- Commit `311e099` adalah commit implementasi Fase 6 yang diaudit. Tidak ada perubahan sejarah Git.
- Worktree bersih pada awal audit; `package-lock.json` tidak diubah selama audit ini.
- Setelah pemeriksaan agregat SQLite menunjukkan tidak ada applicant/NIK tersimpan, dua key HMAC lokal dibuat secara independen dan hanya disimpan di `.env` yang di-ignore; APP_KEY tidak diubah. Runtime memverifikasi keberadaan dan perbedaan key tanpa menampilkan nilai.
- Kebijakan ukuran resmi adalah 5120 KB global/default; batas lebih rendah per dokumen diperbolehkan dan batas di atas 5120 KB ditolak. Migration tambahan telah diterapkan pada SQLite lokal.
- Fase 5 mengimplementasikan penyimpanan privat, retrieval persyaratan, service kelengkapan, upload/replacement, revisi historis, preview/unduh terotorisasi, UI, dan integrasi dashboard.
- Fase 6 mengimplementasikan tinjauan dan validasi kesiapan, submit transaksional/idempoten, nomor registrasi dengan sequence per periode-program, persetujuan tersimpan, serta audit perubahan pasca-submit. Laporan implementasi tersedia di `docs/phases/PHASE-6-IMPLEMENTATION-REPORT.md`.
- Format nomor `PPDB/{tahun-ajaran}/{kode-program}/{urutan-5-digit}` dan teks/versi persetujuan yang digunakan adalah asumsi implementasi sementara; belum menjadi keputusan resmi sekolah. Konfigurasi persetujuan kosong menyebabkan submit gagal tertutup, dan konfigurasi resmi wajib ditetapkan sebelum produksi.
- Tes regresi audit final: 161 total, 159 lulus, 2 dilewati (MySQL), 0 gagal, 618 asersi. Dua tes MySQL diskip karena server pada `127.0.0.1:3306` tidak dapat dihubungi; Perilaku row-lock dan konkurensi fisik InnoDB belum diverifikasi.
- Review aturan reopen: hanya permission `reopen_registration` yang tersedia dan hanya diberikan ke `superadmin`; belum ada route/action/policy atau alur audit untuk reopen. Business Requirements tidak menetapkan target dan masa berlaku akses reopen. Perlu keputusan pemilik.
- Tidak ditemukan persetujuan tertulis untuk format nomor maupun redaksi/versi consent. Keduanya masih provisional.
- Laporan audit penutupan tersedia di `docs/phases/PHASE-6-FINAL-CLOSURE-REPORT.md`; status fase tetap BLOCKED dan Fase 7 tidak dimulai.
- Nilai PHP lokal saat diuji: `upload_max_filesize=2M`, `post_max_size=8M`. Pengaturan `upload_max_filesize` harus dinaikkan ke 5M untuk unggahan live ukuran maksimum; test 5120 KB memakai file sintetis di runner Laravel.
- Temuan NPM development/build tetap terbuka; verifikasi produksi (`npm audit --omit=dev`) bersih. MySQL fisik masih pending.
- Riwayat yang tersedia berisi `e2a8bb3` → `4e70b5d` → `d970b33` → `311e099`; `main` dan `origin/main` sama-sama menunjuk `311e099` pada awal audit. Referensi historis `4c64724` tidak ditemukan; penyebab perbedaan tidak dapat dipastikan dari bukti Git yang tersedia. Tidak ada rewrite history.

---

## 3. MATRIX PENYELESAIAN FASE PENGEMBANGAN

| No | Fase Pengembangan | Target Deliverables | Status | Catatan Teknis |
|:---:|---|---|:---:|---|
| 0 | **Inspeksi Lingkungan** | Validasi PHP 8.3, Node 24, Composer, Git, direktori proyek | **SELESAI** | Disetujui pada awal proyek |
| 1 | **Fondasi Proyek** | Laravel 13, Tailwind v4, Desain Token, Blade Layouts & Components | **SELESAI** | Lulus build Vite & uji fondasi |
| 2 | **Database & Model** | 16 Migrasi tabel, Model Eloquent, Relasi, PHP Enums, Seeders | **SELESAI** | Relasi normal & integritas foreign keys |
| 2.1 | **Audit Keamanan & DB** | Hash NIK HMAC-SHA256, enkripsi NIK, isolasi koneksi MySQL | **SELESAI BERSYARAT** | Audit kriptografi selesai; verifikasi fisik MySQL masih pending |
| 3 | **Autentikasi & OTP** | Login NIK/Email, OTP HMAC kontekstual, aktivasi bertahap, rate limiting | **SELESAI** | 91 tes lulus, 302 asersi |
| 4 | **Formulir & Profil Pendaftar** | Biodata siswa, ayah, ibu, wali, riwayat TK, pilih program, hitung umur | **SELESAI** | 128 tes lulus, 438 asersi, 2 skip (MySQL), Pint lulus |
| 5 | **Manajemen Dokumen** | Upload privat berkas pendaftaran, validasi MIME/ukuran, revisi berkas | **SELESAI** | 141 tes lulus, 2 skip MySQL; laporan implementasi Fase 5 tersedia |
| 6 | **Alur Pendaftaran PPDB** | Validasi kesiapan, consent, submit idempoten, nomor, audit revisi | **IMPLEMENTASI DIAUDIT; CLOSURE BLOCKED** | 159 passed; keputusan nomor/consent dan MySQL/reopen belum tuntas |
| 7 | **Mesin PDF Pendaftaran** | Cetak draft ber-watermark DRAFT, bukti registrasi resmi, versioning PDF | **BLOCKED** | Tidak dimulai sebelum seluruh gate Fase 6 lulus dan ada otorisasi pemilik |
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

## 5. CAPAIAN FITUR FASE 4–6

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
6. **Tinjauan dan Submit Pendaftaran (Fase 6)**:
   - Service kesiapan menggabungkan biodata, alamat, orang tua, pendidikan, periode/program, dokumen wajib, dan persetujuan.
   - Submit ulang idempoten; transaksi mengunci periode, pendaftaran, program, dan sequence nomor, lalu menyimpan consent, nomor, status `SUBMITTED`, waktu submit, serta log.
   - Setelah submit, perubahan saat periode masih terbuka menaikkan `data_revision` hanya jika ada perubahan dan menyimpan nama field tanpa nilai pribadi. Perubahan setelah periode ditutup ditolak.
   - Halaman tinjauan memasker NIK, tidak menampilkan path file privat, dan tidak menghasilkan PDF. Pembuatan PDF final berada di Fase 7.

---

## 6. PROTOKOL STOP CONDITION

Implementasi Fase 6 telah diaudit, tetapi fase **BELUM CLOSED**. Gate persetujuan format nomor/consent dan kebijakan reopen menunggu pemilik; migrasi dan concurrency MySQL/InnoDB terhambat karena MySQL tidak tersedia. Agen **BERHENTI** di sini dan tidak memulai Fase 7. Lihat `docs/phases/PHASE-6-FINAL-CLOSURE-REPORT.md`.
