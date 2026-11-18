# KNOWN ISSUES & LIMITATIONS — PPDB MINSATAMA
## Catatan Kendala, Batasan Lingkungan, dan Rencana Mitigasi

**Terakhir Ditinjau**: 18 November 2026

---

## 1. DAFTAR ISU & BATASAN SAAT INI

### 1.1 Enam Temuan NPM High pada Rantai `picomatch`
- **Status**: TERBUKA (MENUNGGU PATCH UPSTREAM)
- **Tingkat Keparahan**: High pada advisory; paket yang terpasang merupakan dependency build/development.
- **Deskripsi**:
  `npm audit --json` melaporkan `GHSA-fc5q-39xc-vjpf` / `CVE-2026-112366` melalui `picomatch`, `fdir`, `tinyglobby`, `vite-plugin-full-reload`, `laravel-vite-plugin`, dan `vite`. GitHub Advisory saat diperiksa belum menetapkan versi patched. NPM dry-run menyarankan downgrade lintas major Vite/plugin, sehingga tidak diterapkan.
- **Mitigasi**:
  Jangan gunakan `npm audit fix --force`; periksa advisory dan kompatibilitas setelah patch tersedia. Hindari build dari konfigurasi/glob yang dikendalikan pengguna tak tepercaya.

### 1.2 Admin Reopen Belum Diimplementasikan dan Kebijakannya Belum Lengkap
- **Status**: MENUNGGU KEPUTUSAN PEMILIK
- **Deskripsi**:
  Seeder mendefinisikan permission `reopen_registration` dan hanya role `superadmin` yang menerimanya. Tidak ditemukan route, policy, controller, atau action untuk reopen. Dokumen Business Requirements tidak menetapkan apakah reopen berlaku untuk satu pendaftaran atau seluruh periode, durasinya, proses penutupan kembali, maupun alasan dan audit yang diwajibkan.
- **Mitigasi**:
  Jangan menganggap permission sebagai implementasi atau memberi akses melalui jalur lain. Pemilik perlu menetapkan cakupan, masa berlaku, role, alasan wajib, audit, dan cara akses ditutup kembali sebelum fitur dibuat.

### 1.3 Layanan Daemon MySQL Lokal Belum Aktif di Port 3306
- **Status**: BLOCKED — MYSQL TIDAK TERSEDIA
- **Tingkat Keparahan**: Rendah (Lingkungan Pengembangan Lokal)
- **Deskripsi**:
  Ekstensi `pdo_mysql` aktif, tetapi service MySQL pada `127.0.0.1:3306` tidak dapat dihubungi. Akibatnya, kedua pengujian migrasi fisik live pada database uji disposable `ppdb_minsatama_test` diskip (`MySqlSchemaCompatibilityTest`, 2 tests skipped).
- **Dampak**:
  Kompatibilitas migrasi dan concurrency InnoDB belum diverifikasi. SQLite tidak membuktikan semantik lock/constraint MySQL. Jangan menyatakan gate ini PASS berdasarkan tes SQLite.
- **Rencana Mitigasi**:
  Saat MySQL tersedia, pastikan koneksi `mysql_test` menunjuk hanya ke database uji disposable dan bukan database produksi sebelum menjalankan migrasi. Aplikasi tidak mengalami gangguan karena pengujian otomatis default berjalan di SQLite in-memory yang terisolasi.

### 1.4 Batas Unggah PHP Lokal di Bawah Kebijakan Aplikasi
- **Status**: MENUNGGU KONFIGURASI SERVER
- **Tingkat Keparahan**: Operasional; unggahan lebih dari 2 MB akan ditolak oleh PHP sebelum mencapai Laravel.
- **Deskripsi**:
  Kebijakan aplikasi sudah 5120 KB. Nilai PHP lokal yang terukur adalah `upload_max_filesize=2M` dan `post_max_size=8M`. Feature test menguji batas 5120 KB melalui file sintetis, bukan request melalui PHP/web server produksi.
- **Mitigasi**:
  Atur `upload_max_filesize=5M`, `post_max_size` lebih besar dari 5M, dan batas request web server yang mencakup overhead multipart. Setelah perubahan, uji unggah aktual 5 MB.

### 1.5 Migration Default Ukuran Persyaratan Lokal
- **Status**: SELESAI PADA SQLITE LOKAL
- **Deskripsi**:
  Migration tambahan `2026_11_18_120000_update_document_requirement_default_size` telah diterapkan setelah migration awal terverifikasi berjalan. Migration hanya mengubah default kolom `max_size_kb` menjadi 5120; tidak mengubah atau menghapus nilai baris yang telah ada.

### 1.6 Transportasi Pengiriman OTP WhatsApp Masih Menggunakan Driver Log/Simulasi
- **Status**: SESUAI DESAIN FASE SAAT INI (BY-DESIGN)
- **Tingkat Keparahan**: Info / Operasional
- **Deskripsi**:
  Sistem saat ini menggunakan driver `log` (mencatat kode OTP pada berkas log lokal untuk memudahkan pengujian pengembang) dan driver `array` (untuk automated tests). Belum ada gateway WhatsApp resmi (seperti Fonnte, Twilio, atau Zenziva) yang dihubungkan karena belum tersedianya kredensial API resmi madrasah.
- **Rencana Mitigasi**:
  Abstraksi `OtpDeliveryInterface` telah disiapkan. Ketika pihak madrasah telah menyediakan provider WhatsApp dan API key resmi, pengembang hanya perlu menambahkan satu kelas implementasi (misal: `FonnteOtpDelivery`) dan mengikatnya pada `OtpDeliveryService` tanpa mengubah logika bisnis pendaftaran atau basis data.

### 1.7 Pembatasan Ukuran Form Data Sibling
- **Status**: TERMITIGASI
- **Deskripsi**:
  Orang tua dengan anak kembar harus membuat dua akun terpisah dengan NIK masing-masing, namun nomor WhatsApp orang tua dapat digunakan kembali.
- **Rencana Mitigasi**:
  Telah diverifikasi penuh dengan test case `test_allows_sibling_registration_with_same_phone_number` yang lulus tanpa konflik database.

### 1.8 Persetujuan Format Nomor dan Redaksi Consent
- **Status**: MENUNGGU PENGESAHAN SEKOLAH SEBELUM PRODUKSI
- **Deskripsi**:
  Implementasi Fase 6 memakai format sementara `PPDB/{tahun-ajaran}/{kode-program}/{urutan-5-digit}` dengan sequence per periode-program, serta redaksi dari fixture tes versi `v1.0`. Keduanya belum disahkan sebagai kebijakan resmi sekolah.
- **Mitigasi**:
  Konfigurasi teks/versi consent kosong membuat submit gagal tertutup; production tidak memakai fallback sementara. Pemilik sekolah harus mengesahkan format, lingkup sequence, redaksi, dan versi sebelum rilis atau nomor produksi diterbitkan.

### 1.9 Verifikasi Konkurensi Submit pada MySQL
- **Status**: BLOCKED — MYSQL TIDAK TERSEDIA
- **Deskripsi**:
  Submit mengunci row periode, pendaftaran, program, dan sequence dalam transaksi untuk idempotensi/penomoran. Pengujian SQLite tidak membuktikan semantik lock atau beban konkurensi InnoDB. Dua tes MySQL tetap skipped karena server tidak tersedia.
- **Mitigasi**:
  Jalankan pengujian skema dan konkurensi terkontrol pada MySQL/InnoDB sebelum produksi; jangan menyatakan concurrency fisik telah tervalidasi.

### 1.10 Nomor Registrasi dan Consent Menunggu Persetujuan
- **Status**: PENDING OWNER APPROVAL
- **Deskripsi**:
  Format nomor `PPDB/{tahun-ajaran}/{kode-program}/{urutan-5-digit}` dan consent versi `v1.0` merupakan konfigurasi/fixture provisional. Tidak ditemukan persetujuan tertulis pemilik untuk format, cakupan/reset sequence, redaksi, dan versi consent.
- **Mitigasi**:
  Pertahankan submit production fail-closed bila consent belum dikonfigurasi. Jangan menerbitkan nomor produksi sebelum keputusan tersebut disahkan.

### 1.11 PDF Bukti Pendaftaran Belum Diimplementasikan
- **Status**: MENUNGGU FASE 7
- **Deskripsi**:
  Fase 6 mencatat status `SUBMITTED` dan nomor registrasi, tetapi belum menghasilkan PDF final atau draft.
- **Mitigasi**:
  Fitur ini berada di luar lingkup Fase 6 dan tidak boleh dianggap tersedia sebelum Fase 7 diotorisasi dan diselesaikan.

---

## 2. PROSEDUR PELAPORAN ISU BARU

Jika ditemukan bug atau deviasi perilaku sistem selama pengujian manual:
1. Catat rute URI, metode HTTP, dan payload data yang dikirimkan.
2. Lampirkan log error dari `storage/logs/laravel.log`.
3. Tambahkan skenario kegagalan tersebut ke berkas pengujian otomatis terkait (*reproduce via test*).
4. Lakukan perbaikan kode hingga pengujian otomatis lulus.
5. Perbarui berkas `KNOWN-ISSUES.md` ini.
