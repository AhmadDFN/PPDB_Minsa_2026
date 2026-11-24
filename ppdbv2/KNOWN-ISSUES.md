# CATATAN ERROR, BLOCKER & UTANG TEKNIS (KNOWN-ISSUES.md)
**Proyek:** PPDB Online MIN 1 Kota Madiun v2 (`PPDB_Minsa_2026`)

Dokumen ini berfungsi sebagai **penampung error dan hal yang belum bisa dikerjakan** agar pengembangan tidak macet (*stuck*) di satu titik. Jika ada kendala yang belum selesai dalam 2–3 percobaan, catat di sini lalu lanjutkan ke task lain di `todo-roadmap.md`.

---

## Daftar Isu Aktif (Open / Pending)

### [ISS-002] Ketergantungan Ekstensi PHP `gd` untuk Render Pas Foto PNG di Dompdf
- **Tanggal Dicatat:** 2026-11-24
- **Fase / Sumber:** Infrastruktur Hostinger (Temuan Review Sistem v1)
- **Status:** `OPEN` — Prioritas Sedang
- **File Terkait:** Modul Generator PDF Bukti Pendaftaran (`GenerateRegistrationPdfAction`)
- **Deskripsi Masalah:** Dompdf membutuhkan ekstensi PHP `gd` untuk merender gambar PNG. Jika server tidak mengaktifkan `gd`, pembuatan PDF untuk siswa dengan pas foto PNG dapat gagal.
- **Rencana Solusi:** Pastikan ekstensi `gd` aktif pada PHP 8.3 di hPanel Hostinger, dan tambahkan instruksi rekomendasi format JPG/JPEG pada UI upload pas foto sebagai pertahanan tambahan.

---

### [ISS-003] Validasi Batas PHP Upload di hPanel Hostinger
- **Tanggal Dicatat:** 2026-11-24
- **Fase / Sumber:** Infrastruktur Hostinger (Temuan Review Sistem v1)
- **Status:** `OPEN` — Prioritas Sedang
- **Deskripsi Masalah:** Aplikasi mengizinkan upload dokumen hingga 5 MB per berkas. Perlu dipastikan konfigurasi hPanel Hostinger sudah menggunakan `upload_max_filesize = 10M`, `post_max_size = 16M`, dan `memory_limit = 256M`.
- **Rencana Solusi:** Verifikasi langsung melalui menu PHP Configuration di hPanel Hostinger sebelum pendaftaran gelombang aktif dibuka.

---

## Daftar Isu Selesai (Resolved)

### [ISS-001] Kompatibilitas `storedAs` Migration pada SQLite Lokal
- **Tanggal Dicatat:** 2026-11-24
- **Tanggal Diselesaikan:** 2026-11-24
- **Fase / Sumber:** Fase 0.1
- **Status:** `RESOLVED`
- **File Terkait:** `database/migrations/2026_11_24_120000_enforce_single_active_admission_period.php`
- **Solusi Diterapkan:** Menambahkan percabangan `if (DB::getDriverName() === 'sqlite')` dengan partial unique index `WHERE is_active = 1` pada SQLite dan `storedAs` pada MySQL. Seluruh 354 test lulus tanpa error.

---

<!-- TEMPLATE ENTRI ERROR BARU
### [ISS-XXX] Judul Singkat Error / Blocker
- **Tanggal Dicatat:** YYYY-MM-DD
- **Fase / Task Terkait:** Fase X / Task X.Y
- **Status:** `OPEN` | `IN_PROGRESS` | `RESOLVED`
- **File / Baris Terkait:** `path/to/file.php:L123`
- **Pesan Error / Gejala:**
  ```text
  Tempel pesan error atau stack trace ringkas di sini
  ```
- **Langkah Reproduksi:**
  1. ...
- **Hipotesis & Rencana Solusi:**
  - ...
-->
