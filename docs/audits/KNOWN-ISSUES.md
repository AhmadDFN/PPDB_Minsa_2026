# KNOWN ISSUES & LIMITATIONS — PPDB MINSATAMA
## Catatan Kendala, Batasan Lingkungan, dan Rencana Mitigasi

**Terakhir Ditinjau**: 15 November 2026

---

## 1. DAFTAR ISU & BATASAN SAAT INI

### 1.1 Layanan Daemon MySQL Lokal Belum Aktif di Port 3306
- **Status**: TERTUNDA (PENDING)
- **Tingkat Keparahan**: Rendah (Lingkungan Pengembangan Lokal)
- **Deskripsi**:
  Service daemon MySQL lokal pada port 3306 pada mesin host Windows belum diaktifkan saat sesi pengujian berjalan. Akibatnya, pengujian migrasi fisik live pada database `ppdb_minsatama_test` diskip (`MySqlSchemaCompatibilityTest`, 2 tests skipped).
- **Dampak**:
  Pengujian live pada InnoDB fisik belum berjalan secara otomatis pada continuous test runner lokal, meskipun seluruh sintaks migrasi, tipe kolom, dan constraint telah sesuai standar MySQL 8.x dan lulus di SQLite.
- **Rencana Mitigasi**:
  Administrator lingkungan lokal cukup menyalakan layanan MySQL (misal: via Laragon, XAMPP, atau Windows Service `MySQL80`) saat siap menguji koneksi live. Aplikasi tidak mengalami gangguan karena pengujian otomatis default berjalan di SQLite in-memory yang terisolasi.

### 1.2 Transportasi Pengiriman OTP WhatsApp Masih Menggunakan Driver Log/Simulasi
- **Status**: SESUAI DESAIN FASE SAAT INI (BY-DESIGN)
- **Tingkat Keparahan**: Info / Operasional
- **Deskripsi**:
  Sistem saat ini menggunakan driver `log` (mencatat kode OTP pada berkas log lokal untuk memudahkan pengujian pengembang) dan driver `array` (untuk automated tests). Belum ada gateway WhatsApp resmi (seperti Fonnte, Twilio, atau Zenziva) yang dihubungkan karena belum tersedianya kredensial API resmi madrasah.
- **Rencana Mitigasi**:
  Abstraksi `OtpDeliveryInterface` telah disiapkan. Ketika pihak madrasah telah menyediakan provider WhatsApp dan API key resmi, pengembang hanya perlu menambahkan satu kelas implementasi (misal: `FonnteOtpDelivery`) dan mengikatnya pada `OtpDeliveryService` tanpa mengubah logika bisnis pendaftaran atau basis data.

### 1.3 Pembatasan Ukuran Form Data Sibling
- **Status**: TERMITIGASI
- **Deskripsi**:
  Orang tua dengan anak kembar harus membuat dua akun terpisah dengan NIK masing-masing, namun nomor WhatsApp orang tua dapat digunakan kembali.
- **Rencana Mitigasi**:
  Telah diverifikasi penuh dengan test case `test_allows_sibling_registration_with_same_phone_number` yang lulus tanpa konflik database.

---

## 2. PROSEDUR PELAPORAN ISU BARU

Jika ditemukan bug atau deviasi perilaku sistem selama pengujian manual:
1. Catat rute URI, metode HTTP, dan payload data yang dikirimkan.
2. Lampirkan log error dari `storage/logs/laravel.log`.
3. Tambahkan skenario kegagalan tersebut ke berkas pengujian otomatis terkait (*reproduce via test*).
4. Lakukan perbaikan kode hingga pengujian otomatis lulus.
5. Perbarui berkas `KNOWN-ISSUES.md` ini.
