# TECHNICAL DECISIONS — ARCHITECTURE DECISION RECORDS (ADR)
## Log Keputusan Arsitektur dan Pilihan Desain PPDB Minsatama

---

### ADR-001: Pemilihan Single Guard & Single Users Table
- **Tanggal**: 15 November 2026
- **Status**: DISETUJUI (APPROVED)
- **Konteks**:
  Aplikasi memiliki dua jenis aktor: Calon Siswa (masuk via NIK) dan Administrator (masuk via Email). Laravel mendukung multi-guard autentikasi atau single-guard dengan pembedaan tipe akun.
- **Opsi yang Dipertimbangkan**:
  1. *Multi-guard*: Membuat guard `applicant` dan guard `admin` dengan dua model/tabel terpisah (`applicants` dan `admins`).
  2. *Single-guard*: Satu tabel `users`, satu model `User`, satu guard `web`, dibedakan oleh kolom `account_type` (`applicant`, `admin`).
- **Solusi yang Dipilih**: Opsi 2 (Single-guard `web` dengan kolom `account_type`).
- **Alasan**:
  Menghindari kompleksitas sesi multi-guard, masalah state session cookie ganda, dan kompatibilitas native penuh dengan Spatie Laravel Permission tanpa modifikasi guard rumit.
- **Konsekuensi**:
  Kolom `email` pada tabel `users` dijadikan `nullable` (karena siswa login dengan NIK), dan logika autentikasi serta middleware wajib memeriksa `account_type`.

---

### ADR-002: Arsitektur Kriptografi NIK (Enkripsi Reversibel + Hash Deterministik)
- **Tanggal**: 15 November 2026
- **Status**: DISETUJUI (APPROVED)
- **Konteks**:
  NIK adalah data kependudukan pribadi sensitif (UU PDP). NIK harus dapat dicetak kembali pada lembar bukti pendaftaran PDF (membutuhkan enkripsi reversibel), namun sistem juga memerlukan verifikasi keunikan akun cepat tanpa mendekripsi seluruh baris database.
- **Opsi yang Dipertimbangkan**:
  1. *Plaintext NIK*: Menyimpan NIK secara terbuka.
  2. *Blind Indexing Tradisional*: Menggunakan salt dinamis pada setiap record.
  3. *Enkripsi AES-256 + HMAC-SHA256 Deterministik*: Menyimpan `nik_encrypted` via AES-256-CBC dan `nik_lookup_hash` via HMAC-SHA256 ber-kunci rahasia mandiri dengan unique index.
- **Solusi yang Dipilih**: Opsi 3 (`nik_encrypted` + `nik_lookup_hash`).
- **Alasan**:
  Menghadirkan performa lookup $O(1)$, perlindungan kebocoran basis data, dan kemampuan mendekripsi NIK hanya saat pembuatan dokumen PDF resmi.
- **Konsekuensi**:
  Diperlukan pengelolaan dua kunci rahasia: `APP_KEY` untuk enkripsi dan `NIK_HMAC_KEY` untuk lookup hash.

---

### ADR-003: Keamanan Kriptografi OTP (HMAC-SHA256 Context Binding)
- **Tanggal**: 15 November 2026
- **Status**: DISETUJUI (APPROVED)
- **Konteks**:
  Kode OTP numerik 6 digit hanya memiliki 1.000.000 kemungkinan nilai. Menggunakan plain SHA-256 membuka risiko serangan *rainbow table* jika basis data bocor.
- **Opsi yang Dipertimbangkan**:
  1. *Plain SHA-256 Hash*.
  2. *Bcrypt Hash*: Menggunakan `Hash::make()` (lambat dan membutuhkan CPU tinggi untuk token berumur pendek).
  3. *HMAC-SHA256 dengan Kunci Mandiri & Context Binding*: Mengikat `user_id`, `purpose`, `plain_otp`, dan nomor telepon menggunakan `OTP_HMAC_KEY`.
- **Solusi yang Dipilih**: Opsi 3 (HMAC-SHA256 Context Binding).
- **Alasan**:
  Komputasi cepat, mencegah *rainbow table* karena terikat kunci berentropi tinggi dan konteks pengguna, serta verifikasi aman dengan `hash_equals()`.
- **Konsekuensi**:
  Diperlukan kunci `OTP_HMAC_KEY` mandiri di `.env` yang wajib berbeda dari `APP_KEY` dan `NIK_HMAC_KEY`.

---

### ADR-004: Penanganan Perhitungan Umur Dinamis (Runtime Calculation)
- **Tanggal**: 15 November 2026
- **Status**: DISETUJUI (APPROVED)
- **Konteks**:
  Pendaftaran madrasah memerlukan informasi usia siswa per tanggal acuan tahun ajaran baru (misal: per 1 Juli).
- **Opsi yang Dipertimbangkan**:
  1. *Menyimpan Usia di Kolom Basis Data*: Kolom `age_years`, `age_months`, `age_days`.
  2. *Runtime Calculation*: Menghitung umur secara dinamis saat data dibaca berdasarkan tanggal lahir siswa dan `age_reference_date` pada tabel `admission_periods`.
- **Solusi yang Dipilih**: Opsi 2 (Runtime Calculation via `Applicant::calculateAgeAt($refDate)`).
- **Alasan**:
  Usia berubah seiring waktu. Menyimpan umur statis di basis data menyebabkan redundansi dan inkonsistensi data historis.
- **Konsekuensi**:
  Tidak ada kolom umur di database. Umur dihitung saat tampilan/PDF diproses.

---

### ADR-005: Larangan Penggunaan Frontend SPA & Pembatasan Framework
- **Tanggal**: 15 November 2026
- **Status**: DISETUJUI (APPROVED)
- **Konteks**:
  Menentukan arsitektur antarmuka pengguna yang sesuai dengan profil wali murid dan kebutuhan perawatan jangka panjang madrasah.
- **Opsi yang Dipertimbangkan**:
  1. *Single Page Application (SPA)*: Inertia.js, Vue, React, atau Svelte.
  2. *Server-Side Rendered (SSR) Blade*: Blade Templates + Tailwind CSS v4 + Vanilla JS murni.
- **Solusi yang Dipilih**: Opsi 2 (Blade + Tailwind CSS v4 + Vanilla JS).
- **Alasan**:
  Memastikan aksesibilitas ringan bagi seluruh perangkat ponsel orang tua, tidak memerlukan runtime JavaScript berat, SEO bersahabat, pemuatan halaman cepat, dan kemudahan pemeliharaan oleh tim internal madrasah tanpa kompilasi bundler yang rumit.
- **Konsekuensi**:
  Interaktivitas UI (modal, drawer menu, show/hide password) ditulis menggunakan Vanilla JS murni yang modular dan teruji.

---

### ADR-006: Batas Ukuran Dokumen dan Penyimpanan Privat
- **Tanggal**: 18 November 2026
- **Status**: DISETUJUI PEMILIK (APPROVED)
- **Konteks**:
  Dokumen rancangan awal memuat 2048 KB, sedangkan konfigurasi global telah menetapkan 5120 KB. Pemilik proyek menetapkan maksimum resmi 5 MB per berkas.
- **Solusi yang Dipilih**:
  `config/ppdb.php` menetapkan maksimum global 5120 KB dan default per dokumen 5120 KB. Batas khusus dokumen dapat lebih kecil, dengan limit efektif sebagai nilai terendah global/per-dokumen; konfigurasi di atas 5120 KB ditolak.
- **Penyimpanan**:
  Berkas berada di disk `ppdb_private` (`storage/app/private/ppdb/documents`), diakses melalui route terotorisasi, tidak memakai URL publik. SHA-256 disimpan bersama metadata. Penggantian mempertahankan berkas lama dan merekam revisinya.
- **Migrasi**:
  Karena migration pembuatan persyaratan sudah diterapkan pada SQLite persisten, default kolom diperbarui melalui migration tambahan, bukan mengubah riwayat migration.
- **Konsekuensi Operasional**:
  Server perlu `upload_max_filesize=5M`, `post_max_size` lebih besar dari 5M, dan batas request web server yang mencakup overhead multipart. Pada lingkungan verifikasi ini nilai PHP saat ini `upload_max_filesize=2M`; pengaturan tersebut belum diubah.

---

### ADR-007: Format Nomor Registrasi dan Persetujuan Submit
- **Tanggal**: 18 November 2026
- **Status**: DISETUJUI PEMILIK (APPROVED)
- **Konteks**:
  Fase 6 memerlukan nomor registrasi unik dan pencatatan persetujuan yang disahkan sekolah.
- **Keputusan**:
  - Format nomor resmi `PPDB/{tahun-ajaran}/{kode-program}/{urutan-5-digit}`, misalnya `PPDB/2026-2027/REGULER/00001`.
  - Kode program yang sah adalah `REGULER` dan `UNGGULAN`.
  - Consent resmi versi `v1.0`: “Saya selaku orang tua/wali menyatakan bahwa seluruh data yang diisikan adalah benar dan dapat dipertanggungjawabkan.”
- **Batasan dan Pengamanan**:
  Konfigurasi teks atau versi consent yang kosong tetap menyebabkan submit gagal tertutup. Lingkungan production tidak diberi fallback redaksi.
- **Konsekuensi**:
  Nomor tidak memuat ID periode, sehingga sequence harus unik untuk setiap pasangan tahun ajaran-program di semua periode. Tidak ada nomor produksi yang diasumsikan telah terbit sebelum implementasi ini.

### ADR-008: Admin Reopen per Pendaftaran
- **Tanggal**: 18 November 2026
- **Status**: DISETUJUI PEMILIK DAN DIIMPLEMENTASIKAN
- **Keputusan**:
  Admin PPDB atau superadmin dengan permission `reopen_registration` dapat membuka kembali satu pendaftaran `SUBMITTED` pada periode tertutup dengan alasan serta expiry wajib. Akses dapat ditutup lebih awal dengan alasan wajib. Expiry ditegakkan server-side tanpa cron dan semua tindakan diaudit.
- **Konsekuensi**:
  Reopen tidak mengubah status, nomor, maupun waktu submit; hanya operasi edit milik pendaftar yang diizinkan selama window masih berlaku.

### ADR-009: Sequence Nomor Unik Lintas Periode
- **Tanggal**: 18 November 2026
- **Status**: DISETUJUI SEBAGAI INVARIAN TEKNIS
- **Konteks**:
  Format nomor resmi tidak memuat identitas periode. Counter terpisah per periode dapat menerbitkan nomor sama jika terdapat dua periode dalam tahun ajaran yang sama untuk program yang sama.
- **Keputusan**:
  Migration additive membuat tabel counter global dengan unique key tahun ajaran-program dan mem-backfill nilai tertinggi dari counter per-periode yang lama. Submit mengunci satu row counter tersebut di dalam transaksi sebelum menaikkan sequence, sehingga semua periode pada scope yang sama menggunakan counter atomik yang sama.
- **Konsekuensi**:
  Sequence tetap monoton untuk tahun ajaran-program yang sama meskipun pendaftaran dibuka melalui lebih dari satu periode. Tabel `registration_sequences` lama dipertahankan untuk kompatibilitas data; generator aktif menggunakan `registration_number_sequences`.

### ADR-010: Kompatibilitas Indeks Migrasi pada MariaDB
- **Tanggal**: 18 November 2026
- **Status**: DIIMPLEMENTASIKAN
- **Konteks**:
  Nama indeks komposit default pada migration dokumen melebihi batas identifier MariaDB 10.4. Migration berhenti setelah tabel terbentuk, sebelum migration tercatat.
- **Keputusan**:
  Gunakan nama indeks eksplisit yang pendek dan pulihkan tabel parsial secara idempotent dengan menambahkan unique index yang belum ada. Tidak ada tabel/data yang dihapus.
- **Konsekuensi**:
  Uji migrasi terhadap database khusus `ppdb_phase6_test` dapat dilanjutkan dan lulus pada MariaDB 10.4.32/InnoDB.
