# TODO ROADMAP — PPDB MINSATAMA
## Peta Jalan Pengembangan dan Rencana Tugas Berkelanjutan

**Status Notasi**:
- `[x]` Selesai (*Completed*)
- `[-]` Sedang Berjalan (*In Progress*)
- `[ ]` Menunggu (*Pending*)
- `[!]` Terhambat (*Blocked*)

---

## 1. TUGAS SELESAI (COMPLETED TASKS)

- [x] **[P0 - Kritis] Inisialisasi Struktur Dokumentasi Master**: Membuat berkas master `AGENTS.md`, `PROJECT_BLUEPRINT.md`, `PROJECT_STATUS.md`, `TODO_ROADMAP.md`, `BUSINESS_REQUIREMENTS.md`, `SYSTEM_ARCHITECTURE.md`, `DATABASE_DESIGN.md`, `SECURITY_AND_TESTING.md`, `TECHNICAL_DECISIONS.md`, `DEVELOPMENT_GUIDE.md`.
- [x] **[P0 - Kritis] Audit Repositori & Test Suite Terkini**: Menjalankan pengujian otomatis independen (`php artisan test`, `composer audit`, `npm audit`, `vendor/bin/pint --test`). 128 tes lulus, 0 error Pint, 0 celah keamanan dependensi.
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

---

## 2. TUGAS FASE BERIKUTNYA (FASE 5: MANAJEMEN BERKAS & DOKUMEN PENDAFTARAN)

*Catatan: Tugas di bawah ini HANYA boleh dieksekusi setelah mendapatkan persetujuan tertulis dari Pemilik Proyek.*

- [ ] **[P1 - Tinggi] Konfigurasi Penyimpanan Privat**:
  - *Deskripsi*: Memastikan disk `private/ppdb/documents` aman dari akses publik langsung melalui web server.
- [ ] **[P1 - Tinggi] Service Unggah Dokumen Mandiri**:
  - *Deskripsi*: `app/Services/Document/DocumentUploadService.php` dengan validasi ekstensi (PDF, JPG, PNG), deteksi MIME type asli (bukan ekstensi palsu), batas ukuran maks. 2MB.
- [ ] **[P1 - Tinggi] Penggantian Dokumen & Riwayat Revisi**:
  - *Deskripsi*: Menyimpan dokumen pengganti ke tabel `registration_document_revisions` sebelum menimpa berkas aktif pada `registration_documents`.
- [ ] **[P1 - Tinggi] Antarmuka Unggah Berkas Pendaftar**:
  - *Deskripsi*: Halaman unggah berkas berdasarkan persyaratan dokumen aktif pada `document_requirements`.

---

## 3. TUGAS FASE LANJUTAN (FUTURE PHASE TASKS)

### Fase 6 — Alur Pendaftaran (Draft, Validasi Kelengkapan & Submit Final)
- [ ] **[P1 - Tinggi] Sistem Status Formulir (DRAFT & SUBMITTED)**:
  - *Deskripsi*: Menegakkan aturan bahwa formulir dapat disimpan sebagai DRAFT berkali-kali.
- [ ] **[P1 - Tinggi] Engine Validasi Kelengkapan Pra-Submit**:
  - *Deskripsi*: Pengecekan seluruh modul (Biodata, Ortu, Alamat, TK, Program, Berkas Wajib) sebelum pendaftar diizinkan menekan tombol "Kirim Pendaftaran Final".
- [ ] **[P1 - Tinggi] Penerbitan Nomor Registrasi Unik**:
  - *Deskripsi*: Nomor registrasi unik diterbitkan HANYA saat pendaftar melakukan submit final (contoh format: `REG-2026-XXXX`).

### Fase 7 — Pembuatan Dokumen Bukti Pendaftaran (PDF Generator)
- [ ] **[P1 - Tinggi] Desain Layout PDF Bukti Pendaftaran**:
  - *Deskripsi*: Desain lembar bukti resmi ukuran F4/A4 dengan kop madrasah resmi, logo Kemenag, pas foto pendaftar, tabel biodata, dan tanda tangan wali murid.
- [ ] **[P1 - Tinggi] Watermark DRAFT Otomatis**:
  - *Deskripsi*: Jika status masih DRAFT, PDF memuat watermark besar transparan bertuliskan "DRAFT - BUKAN BUKTI RESMI".
- [ ] **[P1 - Tinggi] PDF Final & Versioning**:
  - *Deskripsi*: Jika status SUBMITTED, PDF diterbitkan sebagai bukti sah registrasi dan dicatat pada tabel `registration_pdf_versions`.

### Fase 8 — Panel Panitia PPDB & Loket PTSP
- [ ] **[P1 - Tinggi] Antarmuka Verifikasi Berkas Fisik**:
  - *Deskripsi*: Memungkinkan panitia di loket PTSP memeriksa kecocokan berkas fisik asli dengan berkas digital yang diunggah.
- [ ] **[P2 - Menengah] Modul Revisi Berkas oleh Panitia**:
  - *Deskripsi*: Panitia dapat menandai dokumen buram/salah dan memberikan catatan revisi yang tampil di dasbor wali murid.
- [ ] **[P2 - Menengah] Rekapitulasi Data & Ekspor Laporan**:
  - *Deskripsi*: Filter data berdasarkan jalur (Reguler/Unggulan), jenis kelamin, asal sekolah TK, dan ekspor ke Excel/CSV.

### Fase 9 — Quality Assurance & Deployment
- [ ] **[P1 - Tinggi] Uji Integrasi Menyeluruh (End-to-End Testing)**.
- [ ] **[P1 - Tinggi] Audit Keamanan Lanjutan (Penetration Test & OWASP Top 10)**.
- [ ] **[P2 - Menengah] Panduan Operasional Pengguna (User Manual Panitia & Wali Murid)**.

---

## 4. TUGAS KEAMANAN & PEMELIHARAAN (SECURITY & MAINTENANCE)

- [x] **[P0 - Selesai] Enkripsi NIK & Deterministic HMAC Lookup**: Terimplementasi via `NikManager` dengan pengujian unit 100% lulus.
- [x] **[P0 - Selesai] Pengamanan Kriptografi OTP Binding**: Terimplementasi via `OtpService` dengan kunci rahasia terpisah dan context binding.
- [x] **[P0 - Selesai] Proteksi Brute-Force Rate Limiting**: Batas 5 percobaan login per menit dan batas permintaan OTP.
- [x] **[P0 - Selesai] IDOR Prevention pada Form Profil**: Seluruh aksi pembaruan profil diikat ketat pada `$request->user()->applicant`, mengabaikan ID yang dikirim melalui request.
- [ ] **[P2 - Berkala] Rotasi Kunci HMAC & Manajemen Secret**: Prosedur pembaruan kunci rahasia jika terjadi insiden keamanan.
- [ ] **[P2 - Berkala] Audit Log Pembersihan Token OTP Kedaluwarsa**: Scheduled task pembersihan otomatis record OTP yang telah kedaluwarsa lebih dari 30 hari.
