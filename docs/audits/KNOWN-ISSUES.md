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

### 1.2 Batasan Verifikasi Database Produksi
- **Status**: TERBUKA — MySQL 8.x BELUM DIUJI TERPISAH
- **Deskripsi**:
  Sembilan tes migrasi/backfill counter, skema, default 5120 KB, submit konkuren, rollback, dan retry lulus pada MariaDB 10.4.32/InnoDB menggunakan database disposable `ppdb_phase6_test`. Hasil ini tidak diklaim sebagai verifikasi fisik MySQL 8.x.
- **Mitigasi**:
  Jalankan suite yang sama terhadap versi MySQL produksi sebelum rilis. Jangan arahkan tes ke database produksi atau data pendaftar.

### 1.3 Nomor Sequence Lintas Periode
- **Status**: SELESAI
- **Deskripsi**:
  Karena format resmi tidak memuat ID periode, counter dibagikan per tahun ajaran dan program melalui row unik pada `registration_number_sequences`. Migration mem-backfill nilai tertinggi dari tabel counter per-periode lama; submit mengunci row counter global yang sama.
- **Verifikasi**:
  Tes SQLite dan MariaDB multi-proses membuktikan nomor berurutan dan berbeda pada skenario lintas periode.

### 1.4 Batas Unggah PHP Lokal di Bawah Kebijakan Aplikasi
- **Status**: MENUNGGU KONFIGURASI SERVER
- **Tingkat Keparahan**: Operasional; unggahan lebih dari 2 MB akan ditolak oleh PHP sebelum mencapai Laravel.
- **Deskripsi**:
  Kebijakan aplikasi sudah 5120 KB. Nilai PHP lokal yang terukur adalah `upload_max_filesize=2M` dan `post_max_size=8M`. Feature test menguji batas 5120 KB melalui file sintetis, bukan request melalui PHP/web server produksi.
- **Mitigasi**:
  Atur `upload_max_filesize=5M`, `post_max_size` lebih besar dari 5M, dan batas request web server yang mencakup overhead multipart. Setelah perubahan, uji unggah aktual 5 MB.

### 1.5 Migration Default Ukuran Persyaratan
- **Status**: SELESAI
- **Deskripsi**:
  Migration tambahan menetapkan default kolom `max_size_kb` menjadi 5120 KB tanpa mengubah nilai baris yang telah ada. Default terverifikasi pada SQLite dan MariaDB.

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

### 1.8 Format Nomor dan Consent
- **Status**: SELESAI — DISETUJUI PEMILIK
- **Deskripsi**:
  Format `PPDB/{tahun-ajaran}/{kode-program}/{urutan-5-digit}`, kode `REGULER`/`UNGGULAN`, dan redaksi consent versi `v1.0` telah disahkan dan diterapkan. Submit tetap gagal tertutup jika konfigurasi production kosong.

### 1.9 Admin Reopen per Pendaftaran
- **Status**: SELESAI — DISETUJUI DAN DIUJI
- **Deskripsi**:
  Admin berizin dapat membuka akses edit untuk satu pendaftaran submitted dengan alasan dan expiry wajib, menutup lebih awal dengan alasan, dan seluruh tindakan dicatat. Expiry ditegakkan server-side tanpa cron.
- **Verifikasi**:
  Feature tests mencakup permission, lifecycle, audit, validasi, serta akses pendaftar setelah reopen dan setelah expiry.

### 1.10 Penutupan Fase 6
- **Status**: SELESAI
- **Deskripsi**:
  Seluruh gate Fase 6 lulus dengan 179 tes, 0 gagal/skip, dan verifikasi MariaDB fisik. Fase 7 belum dimulai dan masih memerlukan otorisasi tertulis pemilik.

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
