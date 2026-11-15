# AGENTS.md — PANDUAN KERJA DAN INSTRUKSI TETAP CODING AGENT
## Sistem Informasi PPDB MIN 1 Kota Madiun (PPDB Minsatama)

Dokumen ini adalah **pedoman wajib dan instruksi permanen** bagi setiap agen AI/developer yang bekerja pada repositori ini. Setiap agen yang menangani proyek ini **HARUS MEMBACA DAN MEMATUHI** seluruh ketentuan di bawah ini tanpa pengecualian.

---

## 1. ATURAN WAJIB SEBELUM MEMULAI SESI KERJA

Sebelum melakukan modifikasi kode apapun pada repositori ini, agen wajib menjalankan urutan langkah berikut:

1. **Periksa Status Git**:
   - Jalankan `git status` dan periksa commit terakhir (`git log -1`).
   - Pastikan tidak ada perubahan liar yang belum terverifikasi.
2. **Baca Dokumentasi Utama Proyek**:
   - `AGENTS.md` (berkas ini).
   - `docs/PROJECT_STATUS.md` (**sumber kebenaran tunggal** status kemajuan proyek).
   - `docs/PROJECT_BLUEPRINT.md` (cetak biru arsitektur, batasan cakupan, dan identitas madrasah).
   - `docs/TODO_ROADMAP.md` (daftar tugas terperinci dan dependensi antar fase).
   - `docs/BUSINESS_REQUIREMENTS.md` (aturan bisnis resmi yang disetujui pemilik proyek).
   - Berkas laporan fase terkait di `docs/phases/`.
3. **Konfirmasi Fase Aktif**:
   - Identifikasi fase yang sedang berjalan dari `docs/PROJECT_STATUS.md`.
   - **JANGAN PERNAH** melompati fase atau memulai fase berikutnya tanpa izin eksplisit tertulis dari Pemilik Proyek.

---

## 2. PRINSIP ARSITEKTUR & TEKNOLOGI

- **Framework**: Laravel 13.x (PHP 8.3+).
- **Target Basis Data Produksi**: MySQL (InnoDB Engine, UTF8MB4). Pengujian terisolasi menggunakan SQLite in-memory.
- **Frontend & UI**:
  - Blade Templates + Tailwind CSS v4 + Vanilla JS murni.
  - **DILARANG KERAS** menggunakan Bootstrap, Inertia.js, Vue, React, Svelte, Angular, atau framework SPA lainnya.
- **Pola Desain**:
  - Model-View-Controller (MVC) yang bersih.
  - *Service / Action Pattern* untuk logika bisnis inti (kontroler harus tetap ramping / *thin controllers*).
  - *Form Request Validation* untuk seluruh validasi masukan formulir dengan pesan berbahasa Indonesia.
  - Spatie Laravel Permission 8.5+ terpasang pada guard `web`.

---

## 3. ATURAN BISNIS INTI YANG TIDAK BOLEH DIUBAH SECARA SEPIHAK

1. **Batasan Cakupan Aplikasi**:
   - Sistem PPDB Minsatama **BERAKHIR** pada saat pendaftar mengunduh/mencetak dokumen bukti pendaftaran (PDF) dan menyerahkannya ke loket PTSP madrasah.
   - Sistem **TIDAK MENGELOLA** seleksi siswa, perankingan, pengumuman kelulusan/penolakan, maupun daftar ulang.
   - **TIDAK ADA** pembatasan kuota pendaftaran.
2. **Satu Akun Satu Siswa**:
   - Satu NIK hanya dapat memiliki satu akun di seluruh sistem.
   - NIK calon siswa adalah *username* akun pendaftar.
   - Nomor WhatsApp orang tua dapat digunakan kembali untuk mendaftarkan saudara kandung (*siblings*).
3. **Privasi & Keamanan Kriptografi NIK**:
   - NIK **WAJIB DIENKRIPSI** saat disimpan di database (`nik_encrypted` via AES-256-CBC Laravel Crypt).
   - Pencarian NIK menggunakan hash deterministik HMAC-SHA256 (`nik_lookup_hash`) dengan kunci rahasia mandiri `NIK_HMAC_KEY`.
   - **DILARANG KERAS** mencatat NIK dalam bentuk plaintext pada log aplikasi.
   - NIK harus selalu ditampilkan dalam format masker pada antarmuka biasa (contoh: `3573********0001`).
4. **Keamanan One-Time Password (OTP)**:
   - OTP 6 digit di-hash menggunakan HMAC-SHA256 dengan kunci rahasia mandiri `OTP_HMAC_KEY`.
   - Data hash OTP mengikat `user_id`, `purpose`, `plain_otp`, dan `phone`.
   - Masa berlaku OTP tepat 5 menit dengan batas maksimal 5 kali percobaan.
   - Verifikasi menggunakan `hash_equals()` dan row lock `lockForUpdate()`.
   - Plaintext OTP **DILARANG KERAS** dicatat pada log produksi maupun dikembalikan dalam respons API/web.
5. **Autentikasi & Otorisasi**:
   - Satu tabel `users`, satu guard `web`.
   - Calon siswa masuk menggunakan NIK + kata sandi (`account_type = applicant`).
   - Administrator masuk menggunakan Email + kata sandi (`account_type = admin`).
   - Calon siswa **DILARANG KERAS** diberikan peran atau izin administratif Spatie.
   - Pengguna dengan status `PENDING_ACTIVATION` atau `SUSPENDED` tidak boleh masuk ke sistem.

---

## 4. STANDAR PENGEMBANGAN & PENGUJIAN KUALITAS

Setiap penambahan atau perubahan kode harus mematuhi protokol berikut:

1. **Pengujian Nyata**:
   - **DILARANG MENGKLAIM** bahwa tes telah lulus tanpa benar-benar menjalankannya via terminal (`php artisan test`).
   - Sertakan jumlah pengujian (*passed, skipped, failed*) dan asersi secara faktual.
2. **Format Kode & Linting**:
   - Selalu jalankan `vendor/bin/pint` sebelum membuat commit atau menyelesaikan fase untuk memastikan 100% kepatuhan gaya kode.
3. **Keamanan Tanpa Kompromi**:
   - Periksa kerentanan dependensi via `composer audit` dan `npm audit`.
   - Lindungi dari eksploitasi IDOR, CSRF, XSS, Session Fixation, dan Race Condition.
4. **Pembaruan Dokumentasi**:
   - Setelah menyelesaikan pekerjaan pada sebuah fase, perbarui segera `docs/PROJECT_STATUS.md`, `docs/TODO_ROADMAP.md`, dan laporan fase terkait di `docs/phases/`.

---

## 5. PROTOKOL KONDISI BERHENTI (STOP CONDITION)

Setelah menyelesaikan implementasi dan pengujian fase yang ditugaskan:
1. Pastikan seluruh berkas teruji dan terformat rapi.
2. Buat dokumentasi laporan fase dan perbarui status proyek.
3. Lakukan commit Git yang rapi dengan pesan konvensional.
4. **BERHENTI (STOP)**.
5. Laporkan capaian secara objektif kepada Pemilik Proyek dan tunggu persetujuan eksplisit sebelum melanjutkan ke fase berikutnya.
