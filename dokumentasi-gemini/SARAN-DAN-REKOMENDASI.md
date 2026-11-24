# SARAN & REKOMENDASI PENGEMBANGAN SISTEM PPDB MINSATAMA
### Rekomendasi Teknis, Arsitektural, Kinerja, UX/UI, dan Tata Kelola Operasional

Berdasarkan hasil audit menyeluruh, peninjauan kode sumber (*codebase*), dan pengujian langsung (*live testing*) di server produksi Hostinger, berikut adalah saran dan rekomendasi strategis untuk memastikan sistem beroperasi dengan performa optimal, aman, dan memuaskan seluruh pengguna.

---

## 1. Rekomendasi Teknis & Kompatibilitas Basis Data

### 1.1 Penyesuaian Multi-Database pada Migration `active_slot`
- **Kondisi Temuan**:
  Migration `2026_11_24_120000_enforce_single_active_admission_period.php` menggunakan sintaks:
  ```php
  $table->string('active_slot', 10)->nullable()->storedAs("CASE WHEN is_active = 1 THEN 'ACTIVE' ELSE NULL END");
  ```
  Sintaks ini berjalan sempurna di MySQL 8.x / MariaDB produksi, tetapi menimbulkan galat pada SQLite lokal (`cannot add a STORED column`) karena SQLite tidak mengizinkan penambahan generated column `STORED` via `ALTER TABLE`.
- **Rekomendasi Solusi**:
  Gunakan pengecekan driver basis data agar migrasi berjalan mulus baik di SQLite in-memory (saat unit test otomatis) maupun di MySQL produksi:
  ```php
  if (DB::getDriverName() === 'sqlite') {
      Schema::table('admission_periods', function (Blueprint $table): void {
          $table->string('active_slot', 10)->nullable();
      });
  } else {
      Schema::table('admission_periods', function (Blueprint $table): void {
          $table->string('active_slot', 10)
              ->nullable()
              ->storedAs("CASE WHEN is_active = 1 THEN 'ACTIVE' ELSE NULL END");
      });
  }
  ```
  Dengan penyesuaian ini, perintah `php artisan migrate` dan suite pengujian lokal dapat berjalan 100% tanpa hambatan.

---

## 2. Rekomendasi Konfigurasi Infrastruktur & Hosting (Hostinger)

### 2.1 Konfigurasi Batas PHP Runtime di Server Produksi
Pengunggahan dokumen legalitas (KK, Akta, Pas Foto) memiliki batas aplikasi **5120 KB (5 MB)** per file. Agar pengunggahan berkas berukuran maksimum tidak terpotong oleh web server/PHP, pastikan setelan PHP pada hPanel Hostinger diatur sebagai berikut:

| Parameter PHP | Nilai Rekomendasi | Alasan Teknis |
|---|---|---|
| `upload_max_filesize` | **10M** | Memberikan kelonggaran aman di atas batas berkas 5 MB aplikasi. |
| `post_max_size` | **16M** | Menampung multipart form data beserta token CSRF dan metadata. |
| `memory_limit` | **256M** | Memberikan alokasi memori yang cukup saat merender berkas PDF via Dompdf. |
| `max_execution_time`| **60 detik** | Mencegah timeout saat pembuatan PDF pendaftaran beresolusi tinggi. |
| `max_input_vars` | **3000** | Menjaga stabilitas saat pengisian form formulir pendaftar. |

### 2.2 Optimasi Caching Laravel Pasca-Deploy
Setiap kali melakukan pembaruan kode via Git Auto Deploy di Hostinger, pastikan perintah optimasi berikut selalu dieksekusi:
```bash
php artisan config:cache
php artisan route:cache
php artisan view:cache
```
Langkah ini mempercepat waktu booting framework Laravel hingga 3x lipat dan menghemat penggunaan CPU pada paket hosting bersama (*shared hosting*).

---

## 3. Rekomendasi Pengalaman Pengguna (UX / UI)

### 3.1 Peningkatan Visual Antarmuka Upload Dokumen
- **Temuan Lapangan**: Pada halaman `/pendaftar/dokumen`, masing-masing dokumen (KK, Akta, Pas Foto) memiliki form dan tombol "Unggah Dokumen" tersendiri. Sebagian wali murid pemula cenderung memilih ketiga file terlebih dahulu sebelum mengklik salah satu tombol unggah.
- **Saran Perbaikan**:
  1. Tambahkan banner instruksi singkat di bagian atas halaman dokumen: *"Unggah berkas satu per satu. Klik tombol Unggah Dokumen di bawah masing-masing kotak berkas."*
  2. Tambahkan indikator visual centang hijau (✓ Berkas Tersimpan) yang mencolok begitu dokumen berhasil terunggah.

### 3.2 Floating Button Bantuan WhatsApp Resmi PTSP
- Tambahkan tombol melayang (*floating action button*) ikon WhatsApp di pojok kanan bawah portal pendaftar yang langsung mengarahkan ke nomor Helpdesk Resmi Panitia PPDB MIN 1 Kota Madiun.
- Hal ini memudahkan wali murid yang mengalami kendala teknis (seperti salah memasukkan data NIK atau kuota habis) untuk segera berkonsultasi dengan petugas madrasah pada jam kerja (07.00 – 15.00 WIB).

---

## 4. Rekomendasi Keandalan Komunikasi (WhatsApp Gateway)

### 4.1 Pemantauan Saluran WhatsApp (Fonnte API)
- **Tantangan**: Pengiriman kode OTP dan kata sandi sementara bergantung penuh pada saldo kuota paket Fonnte dan status koneksi perangkat WhatsApp.
- **Saran Solusi**:
  1. Pada halaman **Communication Center** Superadmin, tampilkan status koneksi (*Device Status: CONNECTED*) dan sisa kuota Fonnte secara realtime.
  2. Berikan notifikasi email otomatis kepada Superadmin jika kuota Fonnte tersisa di bawah 100 pesan.
  3. Sediakan tombol manual *"Reset Password ke Akun Pendaftar"* di panel admin jika nomor WhatsApp wali murid tidak aktif atau terblokir.

---

## 5. Rekomendasi Mesin PDF & Kompatibilitas Gambar

### 5.1 Dukungan Ekstensi PHP GD untuk Pas Foto PNG
- **Temuan Teknis**: Dompdf membutuhkan ekstensi PHP `gd` untuk merender foto bertipe PNG ke dalam dokumen PDF. Pada lingkungan tanpa ekstensi GD, Dompdf hanya mendukung format JPG/JPEG.
- **Saran**:
  - Pastikan ekstensi `gd` aktif pada versi PHP 8.3 di hPanel Hostinger.
  - Alternatif pertahanan (*graceful degradation*): Pada modul upload pas foto, berikan prioritas rekomendasi format JPG/JPEG agar dokumen PDF dapat dirender secara sempurna dalam kondisi server apapun.

### 5.2 Standarisasi Logo Resmi Madrasah
- Saat ini kop surat PDF menggunakan teks nama kementerian dan madrasah resmi.
- Disarankan menyematkan logo resmi Kementerian Agama RI dan logo MIN 1 Kota Madiun berformat vektor SVG atau PNG transparan beresolusi terukur (300 DPI) agar lembar Bukti Pendaftaran PDF Final terlihat semakin representatif dan formal saat diserahkan ke loket PTSP.

---

## 6. Rekomendasi Tata Kelola Operasional Loket PTSP

1. **Penggunaan Scanner QR Code di Loket**:
   Panitia loket PTSP disarankan menyiapkan smartphone atau barcode scanner 2D yang terhubung ke browser. Saat wali murid menyerahkan lembar Bukti Pendaftaran, panitia cukup memindai QR Code untuk langsung membuka halaman verifikasi resmi sistem (`/verifikasi/bukti-pendaftaran/{token}`) dalam waktu kurang dari 2 detik.
2. **SOP Penerbitan Tanda Terima Fisik**:
   Setelah dokumen fisik asli diverifikasi dan berkas digital disetujui (*APPROVED*) pada panel admin, panitia memberikan lembar tanda terima fisik bertandatangan loket kepada wali murid sebagai bukti penyerahan berkas final.
