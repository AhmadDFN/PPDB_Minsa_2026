# CATATAN ERROR, BLOCKER & UTANG TEKNIS (KNOWN-ISSUES.md)
**Proyek:** PPDB Online MIN 1 Kota Madiun v2 (`PPDB_Minsa_2026`)

Dokumen ini berfungsi sebagai **penampung error dan hal yang belum bisa dikerjakan** agar pengembangan tidak macet (*stuck*) di satu titik. Jika ada kendala yang belum selesai dalam 2–3 percobaan, catat di sini lalu lanjutkan ke task lain di `todo-roadmap.md`.

---

## Daftar Isu Aktif (Open / Pending)

### [ISS-011] Migrasi Wilayah Gagal dan Pemulihan Database Tertunda
- **Tanggal:** 2026-11-27
- **Status:** `OPEN` — perbaikan lokal tersedia; server dan pemulihan data belum diverifikasi.
- **Temuan:** Log menunjukkan `migrate:fresh --seed` menghapus tabel kemudian gagal pada foreign key `administrative_regions`. Migrasi dapat meninggalkan tabel parsial dan seeder belum dijalankan.
- **Tindak lanjut:** Verifikasi backup, status migrasi, struktur dan isi tabel parsial; evaluasi pemulihan data dengan pemilik. Jangan ulangi `migrate:fresh` atau menghapus tabel. Uji perbaikan pada MariaDB yang aman sebelum deployment.
- **Keamanan dependensi:** Audit Composer terkini melaporkan lima advisory pada tiga paket Symfony; penanganan terpisah masih diperlukan.

### [ISS-004] Cakupan Pin Manual Peta Offline Terbatas di Area Kampus
- **Tanggal Dicatat:** 2026-11-26
- **Fase / Task Terkait:** Revisi form calon siswa, workflow update form
- **Status:** `IN_PROGRESS` — Pin dan Haversine tersedia; cakupan map dan UAT visual masih perlu ditutup
- **File Terkait:** `resources/views/applicant/form/address.blade.php`, `public/maps/min1-madiun-campus.json`, `scripts/build-offline-campus-map.php`, `app/Http/Requests/Applicant/UpdateAddressRequest.php`, `app/Actions/Applicant/SaveApplicantAddressAction.php`, `config/ppdb.php`
- **Gejala / implementasi:** Widget peta offline menggunakan potongan lokal OpenStreetMap (2.805 bentuk: 138 jalan dan 2.667 bangunan). Pin dapat diletakkan dengan klik/ketuk, diseret, atau digerakkan lewat keyboard; lokasi perangkat hanya diminta setelah tombol ditekan. Haversine berjalan lokal dan dihitung ulang server-side. Feature test memastikan koordinat tidak tersimpan.
- **Batas:** Dataset visual hanya mencakup area sekitar Kampus 1 (111.510–111.519 BT, 7.665–7.653 LS). Jika lokasi berada di luar area, koordinat manual/perangkat tetap dapat dihitung tetapi pin tidak tampak pada peta. Widget diuji pada harness browser 360×800 dan 390×844; alur form pendaftar terautentikasi belum diuji ulang dengan widget map.
- **Privasi & sumber:** Berkas map disajikan dari origin aplikasi; koordinat calon siswa tidak dikirim ke OpenStreetMap/vendor lain. Sumber OSM dan lisensi ODbL dicantumkan di UI dan README. Data administrasi 2025 (38 provinsi, 91.599 entri) tetap terpisah.
- **Rencana:** Perluas cakupan peta offline yang ringan dengan sumber berlisensi, lakukan UAT drag/tap/keyboard pada mobile, dan pastikan tampilan untuk lokasi di luar cakupan jelas. Fallback jarak manual tetap tersedia.

### [ISS-008] UAT Browser Pin Offline dan Jarak Haversine
- **Tanggal Dicatat:** 2026-11-26
- **Fase / Task Terkait:** Revisi form calon siswa
- **Status:** `IN_PROGRESS` — Automated Haversine dan widget browser pass; alur form penuh/GPS masih pending
- **Gejala:** Widget diuji pada harness lokal untuk meletakkan pin, drag, keyboard, kalkulasi di luar area dan fallback manual; pengujian belum melalui form pendaftar terautentikasi. Dialog izin GPS tidak diuji.
- **Yang telah dibuktikan:** Feature test alamat: titik sama dengan Kampus 1 menghasilkan 0,00 km; pin tanpa longitude ditolak; latitude/longitude tetap null pada record. Harness browser 360×800 dan 390×844 tanpa overflow, console error kosong. Asset lokal memiliki 2.805 fitur dan Kampus 1 berada di dalam batas peta.
- **Rencana:** Uji form pendaftar penuh dengan akun dummy dan izin GPS ditolak/diizinkan setelah izin perangkat yang sesuai; jangan gunakan lokasi perangkat aktual tanpa tindakan/izin eksplisit.

### [ISS-005] Validasi Latar Biru Pas Foto Belum Diimplementasikan
- **Tanggal Dicatat:** 2026-11-26
- **Fase / Task Terkait:** Revisi form calon siswa; dokumen persyaratan
- **Status:** `OPEN` — Validasi visual manual pemilik diperlukan
- **File Terkait:** validasi unggah dokumen dan `GenerateRegistrationPdfAction`
- **Gejala:** UAT memakai JPG biru dummy berlabel UAT hanya untuk membuktikan jalur render PDF. Sistem memvalidasi tipe/ukuran file, tetapi belum memastikan latar foto berwarna biru atau foto merupakan pas foto siswa yang benar.
- **Rencana:** Pemilik menguji unggah pas foto asli sesuai ketentuan; tentukan apakah validasi visual harus manual oleh petugas atau perlu aturan teknis tambahan.

### [ISS-006] Pemindaian QR PDF Final Belum Dibuktikan Secara Fisik
- **Tanggal Dicatat:** 2026-11-26
- **Fase / Task Terkait:** Verifikasi PDF/QR
- **Status:** `OPEN` — Automated verification lulus; scan manual pending
- **File Terkait:** PDF final UAT dan endpoint `/verifikasi/bukti-pendaftaran/{token}`
- **Gejala:** PDF final berhasil dibuat dan suite `RegistrationPdfVerificationTest` lulus (4 test/13 assertion), tetapi alat decoder/pemindai QR lokal tidak tersedia untuk membaca QR dari PDF final UAT.
- **Rencana:** Pemilik scan PDF final memakai kamera/ponsel dan memastikan nomor pendaftaran/status cocok serta halaman publik tidak menampilkan PII.

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

### [ISS-007] Audit Composer Sempat Tidak Terjangkau
- **Tanggal Dicatat/Diselesaikan:** 2026-11-26
- **Status:** `RESOLVED`
- **Pesan awal:** Packagist HTTP/2 502 saat percobaan pertama.
- **Solusi:** Percobaan ulang `composer audit` berhasil dan melaporkan tidak ada security vulnerability advisories.

### [ISS-009] Dataset Wilayah Bertingkat Belum Ada di Aplikasi
- **Tanggal Dicatat/Diselesaikan:** 2026-11-26
- **Status:** `RESOLVED` untuk dataset dan cascading select lokal
- **Solusi:** Menambahkan snapshot dataset wilayah 2025 beserta atribusi/lisensi upstream, migration, seeder, endpoint terotorisasi, validasi parent-child, dan cascading select Blade. Seeder mengimpor 91.599 baris; uji browser lokal memverifikasi Jawa Timur → Kota Madiun → Taman → Demangan dan tombol salin alamat orang tua. Perubahan dataset di masa depan perlu mengikuti sumber dan lisensi yang disertakan.

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

### [ISS-010] Header Login dan Registrasi Overflow pada Tablet 768 px
- **Tanggal Dicatat:** 2026-11-26
- **Fase / Task Terkait:** Live smoke test revisi form calon siswa
- **Status:** `OPEN` — Terlihat pada produksi saat pengukuran browser; belum diperbaiki
- **Rute Terdampak:** `/login`, `/daftar`
- **Gejala:** Pada viewport 768×1024, `document.documentElement.scrollWidth` dan `document.body.scrollWidth` bernilai 825 px. Elemen navigasi header kanan mencapai x=825 px dan halaman menampilkan horizontal scrollbar. Pada viewport 360×800 (scroll width 345 px) dan 390×844 (375 px), overflow tidak ditemukan.
- **Reproduksi:** Buka salah satu rute di atas, set viewport 768×1024, lalu ukur `document.documentElement.scrollWidth`.
- **Rencana:** Audit breakpoint navigasi publik (`hidden md:flex`) dan ukuran minimum grup tombol/link. Perbaiki agar grup navigasi berpindah ke menu mobile atau dapat menyesuaikan lebar pada tablet; verifikasi ulang 360/390/768 px.
- **Bukti:** `livetest/07-HASIL-LIVE-SMOKE-PRODUKSI-2026-11-26.md`.

### [ISS-011] Composer Audit Menemukan Advisory Symfony
- **Tanggal:** 2026-11-27
- **Status:** OPEN — perlu audit dampak dan pembaruan dependensi.
- **Hasil:** `composer audit` gagal: lima advisory pada `symfony/http-kernel` (PKSA-k1tg-4k6j-w4xf), `symfony/mailer` (PKSA-vqxj-j8fm-1q3k, PKSA-9r8k-3vgs-8f3v), dan `symfony/routing` (PKSA-pbtx-4qgh-mmkh, PKSA-2vy6-qchn-f13r). Tingkat severity tidak tercantum pada hasil JSON.
- **Tindak lanjut:** periksa versi perbaikan dan kompatibilitas Laravel, perbarui dependensi, lalu jalankan ulang audit dan test penuh sebelum push.
