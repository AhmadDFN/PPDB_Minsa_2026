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
- [x] **[P0 - Kritis] Audit Repositori & Test Suite Terkini**: Regresi terbaru 141 lulus, 494 asersi, 2 skip MySQL; Pint lulus, Composer audit 0 advisory, NPM audit 6 high development-only dan 0 temuan production-only.
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

## 2. HASIL FASE 5 — MANAJEMEN BERKAS & DOKUMEN PENDAFTARAN

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

### Fase 6 — Alur Pendaftaran (Draft, Validasi Kelengkapan & Submit Final)
- [x] **[P1 - Tinggi] Sistem Status Formulir (DRAFT & SUBMITTED)**:
  - *Deskripsi*: Menegakkan aturan bahwa formulir dapat disimpan sebagai DRAFT berkali-kali.
- [x] **[P1 - Tinggi] Engine Validasi Kelengkapan Pra-Submit**:
  - *Deskripsi*: Pengecekan seluruh modul (Biodata, Ortu, Alamat, TK, Program, Berkas Wajib) sebelum pendaftar diizinkan menekan tombol "Kirim Pendaftaran Final".
- [x] **[P1 - Tinggi] Submit Final, Persetujuan, dan Audit Revisi**:
  - *Deskripsi*: Submit atomik/idempoten menyimpan persetujuan, waktu submit, status, nomor registrasi unik, dan log perubahan. Perubahan pasca-submit diizinkan hanya saat periode masih terbuka.
- [x] **[P1 - Tinggi] Penerbitan Nomor Registrasi Unik**:
  - *Deskripsi*: Sequence unik dikunci per kombinasi periode-program. Format sementara `PPDB/{tahun-ajaran}/{kode-program}/{urutan-5-digit}`; belum disahkan sebagai format resmi sekolah.
- [x] **[P1 - Tinggi] Halaman Tinjauan dan Integrasi Dasbor**:
  - *Deskripsi*: Menampilkan ringkasan aman, NIK termasker, kesiapan pendaftaran, nomor dan status setelah submit.

**Status Fase 6**: Implementasi dan regresi tersedia, tetapi fase **belum CLOSED**. Persetujuan format nomor/consent dan kebijakan reopen menunggu pemilik; migrasi/concurrency MySQL terhambat oleh server yang tidak tersedia. Lihat `docs/phases/PHASE-6-FINAL-CLOSURE-REPORT.md`.

### Fase 7 — Pembuatan Dokumen Bukti Pendaftaran (PDF Generator)
- [ ] **[BLOCKED — P1] Desain Layout PDF Bukti Pendaftaran**:
  - *Deskripsi*: Desain lembar bukti resmi ukuran F4/A4 dengan kop madrasah resmi, logo Kemenag, pas foto pendaftar, tabel biodata, dan tanda tangan wali murid.
- [ ] **[BLOCKED — P1] Watermark DRAFT Otomatis**:
  - *Deskripsi*: Jika status masih DRAFT, PDF memuat watermark besar transparan bertuliskan "DRAFT - BUKAN BUKTI RESMI".
- [ ] **[BLOCKED — P1] PDF Final & Versioning**:
  - *Deskripsi*: Jika status SUBMITTED, PDF diterbitkan sebagai bukti sah registrasi dan dicatat pada tabel `registration_pdf_versions`.

**Gate Fase 7: BLOCKED.** Tidak ada source, migration, atau dependency Fase 7 yang dibuat. Jangan mulai sebelum status closure Fase 6 menjadi CLOSED dan pemilik memberi otorisasi tertulis.

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
- [x] **[P1 - Selesai] Otorisasi Dokumen Pendaftar**: Preview, unduh, dan riwayat revisi dibatasi ke dokumen milik pendaftar yang sedang masuk.
- [ ] **[P0 - Berkala] Pantau advisory `picomatch`**: Enam temuan high tetap terbuka pada dependency build/development; advisory belum menyatakan patched version. Hindari downgrade lintas major tanpa kajian kompatibilitas.
- [ ] **[P1 - Operasional] Atur batas unggah server**: PHP produksi wajib `upload_max_filesize=5M`, `post_max_size` > 5M, dan request limit web server mencakup overhead multipart. Lingkungan lokal saat ini masih `2M`.
- [ ] **[P1 - Operasional] Verifikasi MySQL fisik**: Dua tes kompatibilitas MySQL tetap pending sampai server tersedia.
- [ ] **[P1 - Kebijakan] Penetapan kebijakan admin reopen**: Permission `reopen_registration` tersedia bagi superadmin, tetapi implementasi dan ruang lingkup reopen menunggu keputusan pemilik.
- [x] **[P2 - Dokumentasi] Verifikasi referensi riwayat Git**: Riwayat saat audit diverifikasi (`e2a8bb3` → `4e70b5d` → `d970b33` → `311e099`); referensi historis `4c64724` tidak ditemukan dan penyebabnya belum dapat dipastikan. Tidak ada rewrite history.
- [ ] **[P2 - Berkala] Rotasi Kunci HMAC & Manajemen Secret**: Prosedur pembaruan kunci rahasia jika terjadi insiden keamanan.
- [ ] **[P2 - Berkala] Audit Log Pembersihan Token OTP Kedaluwarsa**: Scheduled task pembersihan otomatis record OTP yang telah kedaluwarsa lebih dari 30 hari.
