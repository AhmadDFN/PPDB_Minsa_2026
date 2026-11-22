# DAFTAR FITUR DAN ANALISIS CELAH (FEATURE GAP ANALYSIS)
## Sistem Informasi PPDB Online MIN 1 Kota Madiun (PPDB Minsatama)
### Tahun Ajaran 2026/2027

Dokumen ini menyajikan analisis komparatif yang transparan antara kebutuhan bisnis ideal dengan implementasi aktual kode sumber pada aplikasi PPDB Minsatama saat ini. Analisis ini memisahkan secara tegas antara fitur yang telah siap pakai (*implemented*), fitur yang berfungsi dengan batasan (*partial/limited*), dan fitur yang belum memiliki antarmuka pengguna (*feature gaps*).

> **Rekonsiliasi 20 November 2026:** gunakan matriks aktual di `docs/blueprints/ADMIN-SUPERADMIN-MODULE-MATRIX.md`; permission seeder tanpa route/controller/view dicatat sebagai GAP.

---

## 1. REKAPITULASI STATUS FITUR KESELURUHAN

```
+-----------------------------------------------------------------------------------+
|                        STATUS FITUR SISTEM PPDB MINSATAMA                         |
+-----------------------------------------------------------------------------------+
| 1. Fitur Pendaftar (Calon Siswa / Orang Tua)    : 100% IMPLEMENTED & TESTED       |
| 2. Fitur Keamanan, Kriptografi & Backend Logic  : 100% IMPLEMENTED & TESTED       |
| 3. Fitur Mesin PDF & QR Verifikasi              : 100% IMPLEMENTED & TESTED       |
| 4. Fitur Admin Reopen Registration              : 100% IMPLEMENTED & TESTED       |
| 5. Antarmuka Manajemen Admin Lainnya (UI Gaps)  : BELUM TERSEDIA (FEATURE GAPS)   |
| 6. Integrasi Eksternal Gateway WhatsApp Live    : PENDING OWNER (DRIVER LOG AKTIF)|
| 7. Aset Visual Resmi (Logo & Alamat SK)         : PENDING OWNER (TEKS RESMI AKTIF)|
+-----------------------------------------------------------------------------------+
```

---

## 2. TABEL ANALISIS FITUR TERSEDIA VS CELAH APLIKASI (GAP MATRIX)

| No | Modul / Kebutuhan Fungsional | Status Implementasi Aktual | Kondisi Saat Ini di Repositori | Rekomendasi Pengembangan Lanjutan (Masa Depan) |
|:---:|---|:---:|---|---|
| 1 | **Registrasi Akun Siswa (NIK)** | **IMPLEMENTED & TESTED** | NIK unik, terenkripsi AES-256-CBC, pencarian via HMAC-SHA256, NIK termasker di UI. | Siap pakai tanpa kendala. |
| 2 | **Dukungan Saudara Kandung** | **IMPLEMENTED & TESTED** | 1 nomor WhatsApp dapat dipakai mendaftar beberapa anak (anak kembar/saudara). | Siap pakai tanpa kendala. |
| 3 | **Pengiriman Kode OTP WhatsApp** | **IMPLEMENTED + LIVE TESTED + OWNER CONFIRMED** | Driver Fonnte, normalisasi nomor, timeout, validasi respons, dan redaksi log tersedia; pengiriman langsung telah dikonfirmasi pemilik. Driver log/array tetap untuk lokal/test. | Pertahankan rotasi token, monitoring quota, dan regression test. |
| 4 | **Formulir 4 Langkah Pendaftar** | **IMPLEMENTED & TESTED** | Lengkap: Biodata (NIK read-only), Alamat (leading zero RT/RW), Ortu (Ayah & Ibu wajib, Wali opsional), Riwayat TK. | Siap pakai tanpa kendala. |
| 5 | **Perhitungan Usia Siswa** | **IMPLEMENTED & TESTED** | Dihitung dinamis saat runtime (`calculateAgeAt`) terhadap patokan 1 Juli, tidak disimpan statis di DB. | Siap pakai tanpa kendala. |
| 6 | **Unggah Berkas Privat** | **IMPLEMENTED & TESTED** | Berkas disimpan di `storage/app/private/`, batas 5120 KB, validasi MIME biner via PHP `finfo`, riwayat revisi. | Siap pakai tanpa kendala. |
| 7 | **Submit Final & Nomor Registrasi**| **IMPLEMENTED & TESTED** | Validasi kelengkapan pra-submit, persetujuan consent v1.0, nomor resmi unik per tahun-program berurutan. | Siap pakai tanpa kendala. |
| 8 | **PDF Bukti Pendaftaran (Draft/Final)**| **IMPLEMENTED & TESTED** | PDF Draft ber-watermark miring transparan DRAFT; PDF Final resmi tanpa watermark, ada QR dan foto. | Menambahkan file logo resmi dan teks alamat SK madrasah jika sudah diserahkan pemilik. |
| 9 | **Verifikasi Publik Kode QR** | **IMPLEMENTED & TESTED** | Halaman publik menampilkan keabsahan dokumen tanpa membocorkan data pribadi (Zero PII). | Siap pakai tanpa kendala. |
| 10 | **Admin Reopen Registration** | **IMPLEMENTED & TESTED** | Halaman `/admin/registrations` dan formulir reopen per-siswa dengan alasan wajib dan waktu kedaluwarsa. | Siap pakai tanpa kendala. |
| 11 | **Dasbor Statistik Admin Riil** | **IMPLEMENTED + AUTOMATED TESTED** | `/admin/dashboard` memakai `AdminDashboardController` dengan metrik periode aktif/global, status draft/submitted, breakdown program, lima submission terbaru, aktivitas, dan empty state. | Belum menjadi pusat analitik/supervisi lengkap; health monitoring dan tren historis masih roadmap. |
| 12 | **Pencarian & Filter Pendaftar Admin**| **IMPLEMENTED + AUTOMATED TESTED** | `/admin/pendaftar` mendukung pencarian nama, nomor registrasi, NIK melalui HMAC, filter periode/program/status, pagination, dan query string. | Tambahkan verifikasi live dan UX lanjutan bila diperlukan. |
| 13 | **Pratinjau Berkas Dokumen oleh Admin**| **IMPLEMENTED + AUTOMATED TESTED** | Detail pendaftar menyediakan preview/download privat; controller memeriksa kepemilikan dokumen terhadap registration dan memakai no-store/nosniff. | Verifikasi live lintas role/IDOR tetap pending. |
| 14 | **Ekspor Rekapitulasi Data (Excel/CSV)**| **FEATURE GAP** | Izin `export_reports` sudah terdaftar di seeder Spatie, namun belum ada tombol atau generator ekspor di web. | Membangun export engine menggunakan Laravel Excel / Maatwebsite atau CSV streaming. |
| 15 | **Manajemen Periode & Program di Web** | **FEATURE GAP** | Izin `manage_admission_periods` ada di Spatie, namun pengaturan periode baru masih dilakukan lewat database/seeder. | Membangun antarmuka CRUD Periode Pendaftaran dan Program Pendidikan untuk Superadmin. |
| 16 | **Manajemen Akun Admin di Web** | **FEATURE GAP** | Izin `manage_admin_users` ada di Spatie, namun penambahan staf admin baru masih dilakukan via database/Tinker. | Membangun antarmuka pengelolaan akun petugas loket bagi Superadmin. |
| 17 | **Pengaturan Branding Superadmin** | **IMPLEMENTED + AUTOMATED TESTED** | `/admin/pengaturan` dibatasi `manage_system_settings`, menyimpan nama institusi dan mencatat `system_setting_logs`. | Uji live dan perluasan pengaturan branding masih pending keputusan owner. |

---

## 3. PROSEDUR SEMENTARA UNTUK MENANGANI CELAH APLIKASI (WORKAROUNDS)

Selama fitur-fitur yang berstatus *Feature Gap* di atas belum dibangun antarmuka visualnya:

1. **Untuk Pencarian Pendaftar oleh Admin**:
   Gunakan halaman `/admin/pendaftar` dan filter server-side. Pencarian NIK diproses melalui HMAC dan tidak menampilkan NIK plaintext.
2. **Untuk Verifikasi Berkas Unggahan**:
   Verifikasi keabsahan dokumen dilakukan secara fisik langsung di loket PTSP madrasah saat orang tua menyerahkan lembar cetak bukti registrasi beserta dokumen asli (KK dan Akta Kelahiran).
3. **Untuk Ekspor Rekapitulasi Data Pendaftar**:
   Tim IT madrasah / Developer dapat mengekspor rekapitulasi data pendaftar langsung dari database MySQL menggunakan phpMyAdmin atau perintah SQL terstruktur dengan tetap menjaga enkripsi NIK.
4. **Untuk Penambahan Akun Admin Baru**:
   Superadmin meminta bantuan Tim IT madrasah untuk menjalankan perintah pembuatan akun via `php artisan tinker` sesuai prosedur resmi pada [03-PANDUAN-SUPERADMIN.md](03-PANDUAN-SUPERADMIN.md).

> [!CAUTION]
> **PERINGATAN PROTOKOL KERJA**:
> Sesuai instruksi baku `AGENTS.md`, seluruh rekomendasi penambahan fitur di atas **DILARANG DIIMPLEMENTASIKAN PADA FASE INI**. Fase ini murni berfokus pada pendokumentasian, analisis celah, dan pembuatan panduan operasional.
