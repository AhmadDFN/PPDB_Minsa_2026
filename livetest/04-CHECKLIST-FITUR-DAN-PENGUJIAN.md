# LEMBAR CHECKLIST FITUR & PENGUJIAN (CHECKLIST-FITUR-DAN-PENGUJIAN.md)
### Matriks Verifikasi Modul Fungsional & Kualitas Desain Antarmuka

Gunakan checklist ini saat menjalankan sesi live testing. Beri tanda centang `[x]` untuk setiap fitur yang berhasil diverifikasi.

---

## 1. Modul Autentikasi & Akun Siswa (`/login`, `/daftar`, `/aktivasi`)

- [ ] **[AUT-01] Validasi Form Registrasi (`/daftar`)**: Form hanya menampilkan 4 kolom (NIK, Nama Lengkap, Nomor HP, Checkbox Persetujuan).
- [ ] **[AUT-02] Dukungan Sibling (Nomor HP Ganda)**: Nomor HP orang tua yang sama dapat didaftarkan kembali untuk NIK anak yang berbeda.
- [ ] **[AUT-03] Input NIK 16 Digit Numeric**: Kolom NIK membatasi panjang 16 digit dan menampilkan keyboard angka pada perangkat ponsel (`inputmode="numeric"`).
- [ ] **[AUT-04] Masking NIK di Layar**: NIK tidak pernah ditampilkan secara telanjang di layar publik/dasbor (tampil dalam format `3577********0001`).
- [ ] **[AUT-05] Login Calon Siswa (`/login`)**: Berhasil masuk menggunakan NIK (16 digit) dan Password akun.
- [ ] **[AUT-06] Toggle Password Visibility**: Tombol ikon mata berfungsi menampilkan/menyembunyikan teks kata sandi.
- [ ] **[AUT-07] Alur Masuk Langsung ke Dasbor**: Begitu login berhasil, pendaftar langsung dialihkan ke `/pendaftar/dashboard` tanpa paksaan ganti kata sandi.
- [ ] **[AUT-08] Ganti Kata Sandi Opsional**: Tombol *"Ganti Kata Sandi (Opsional)"* tersedia di dasbor dan bilah menu (navbar).

---

## 2. Modul Dasbor Pendaftar (`/pendaftar/dashboard`)

- [ ] **[DSH-01] Futuristic Command Banner**: Header hero menampilkan identitas calon siswa, status akun aktif, dan periode pendaftaran PPDB.
- [ ] **[DSH-02] 4-Card Telemetry Row**: Menampilkan 4 kartu ringkasan:
  1. NIK Calon Siswa (masking + badge Enkripsi AES-256).
  2. Nomor WhatsApp Terdaftar (badge Terverifikasi OTP).
  3. Usia Calon Siswa (dihitung otomatis per tanggal acuan PPDB aktif).
  4. Status Formulir (DRAFT / SUBMITTED / VERIFIED).
- [ ] **[DSH-03] Status Card Progress Bar**: Menampilkan persentase kelengkapan data pendaftaran secara real-time.
- [ ] **[DSH-04] Kartu Aksi Cepat 6 Modul**: Tautan navigasi ke 5 langkah formulir dan modul dokumen berfungsi presisi.

---

## 3. Modul Formulir Pendaftaran 5 Langkah

- [ ] **[FRM-01] Stepper Indikator Langkah**: Indikator langkah 1 s.d. 5 aktif sesuai rute yang dibuka.
- [ ] **[FRM-02] Langkah 1 - Biodata Siswa (`/pendaftar/biodata`)**:
  - NIK calon siswa terkunci (*readonly/disabled*).
  - Tempat lahir, tanggal lahir, jenis kelamin, anak ke-, dan jumlah saudara dapat disimpan.
  - Usia terhitung dinamis di banner informasi tanpa menyimpan kolom usia di database.
- [ ] **[FRM-03] Langkah 2 - Alamat Domisili (`/pendaftar/alamat`)**:
  - Kolom jalan, RT, RW, kelurahan, kecamatan, kota, provinsi, dan kode pos tersimpan rapi.
  - Nilai leading zero pada RT/RW (misal `03`, `02`) tidak hilang.
  - Perhitungan estimasi jarak otomatis ke madrasah berfungsi.
- [ ] **[FRM-04] Langkah 3 - Data Orang Tua / Wali (`/pendaftar/orang-tua`)**:
  - Data Ayah Kandung dan Ibu Kandung wajib diisi lengkap (Nama, NIK, Status Hidup, Pendidikan, Pekerjaan, Penghasilan, No HP).
  - Data Wali bersifat opsional jika kedua orang tua kandung masih ada.
- [ ] **[FRM-05] Langkah 4 - Riwayat Pendidikan Asal (`/pendaftar/pendidikan`)**:
  - Pilihan jenis sekolah asal (TK / RA / PAUD / Lainnya).
  - Kolom nama sekolah, NPSN, dan kota asal sekolah tersimpan.
- [ ] **[FRM-06] Langkah 5 - Pilihan Program (`/pendaftar/program`)**:
  - Memilih antara **Program Reguler** atau **Program Unggulan**.
  - Deskripsi dan fasilitas masing-masing program tampil jelas.

---

## 4. Modul Dokumen Persyaratan & Pratinjau Privat (`/pendaftar/dokumen`)

- [ ] **[DOC-01] Validasi Ekstensi & Ukuran Berkas**: Menerima file JPG/PNG/PDF dengan ukuran maksimal 5 MB (5120 KB). Menolak file di atas 5 MB dengan pesan berbahasa Indonesia.
- [ ] **[DOC-02] Penyimpanan Privat**: Berkas tersimpan di disk privat (`storage/app/private/ppdb/`), tidak bisa diakses langsung via URL browser publik.
- [ ] **[DOC-03] Tombol Pratinjau Dokumen**: Pendaftar dapat melihat pratinjau dokumen yang telah diunggah di browser secara aman.
- [ ] **[DOC-04] Proteksi IDOR**: Pendaftar A ditolak saat mencoba membuka URL dokumen pendaftar B (`403 Forbidden`).

---

## 5. Modul Final Submit & Dokumen PDF (`/pendaftar/pendaftaran/tinjau`)

- [ ] **[SUB-01] Lembar Draf PDF (Watermark DRAFT)**: Tombol cetak draf menghasilkan berkas PDF pratinjau ber-watermark diagonal `DRAFT`.
- [ ] **[SUB-02] Readiness Check (Cek Kesiapan Submit)**: Kotak submit hanya aktif jika seluruh data identitas dan dokumen wajib telah lengkap.
- [ ] **[SUB-03] Persetujuan Legalitas (Consent v1.0)**: Pendaftar mencentang pernyataan resmi orang tua sebelum tombol submit aktif.
- [ ] **[SUB-04] Atomisitas Final Submit**: Saat dikirim, status berubah dari `DRAFT` ke `SUBMITTED`, formulir terkunci dari perubahan, dan stempel waktu submit tercatat.
- [ ] **[SUB-05] Bukti Pendaftaran PDF Final**: Menghasilkan PDF resmi tanpa watermark DRAFT, memuat QR Code verifikasi unik, dan tata letak A4 rapi.
- [ ] **[SUB-06] Verifikasi QR Code Publik**: Memindai QR Code mengarah ke halaman publik yang membuktikan dokumen sah tanpa membocorkan NIK/telepon pribadi.

---

## 6. Modul Administrasi Panitia PPDB (`/admin/*`)

- [ ] **[ADM-01] Login Admin PPDB (`/admin/login`)**: Panitia masuk via email dan kata sandi admin. Calon siswa ditolak login di portal admin.
- [ ] **[ADM-02] Dasbor Dinamis Database**: Statistik total pendaftar, status draft, submitted, dan grafik program tampil berdasarkan data riil basis data.
- [ ] **[ADM-03] Pencarian & Filter Pendaftar (`/admin/pendaftar`)**:
  - Pencarian nama pendaftar dan NIK (via HMAC lookup hash).
  - Filter berdasarkan periode PPDB, program studi, dan status formulir.
  - Reset filter berfungsi mengembalikan tampilan semula.
- [ ] **[ADM-04] Detail Pendaftar 11 Section (`/admin/pendaftar/{id}`)**: Menampilkan 11 kartu komprehensif data calon siswa secara terstruktur.
- [ ] **[ADM-05] Tombol Pengesahan Berkas & Nomor Urut**:
  - Panitia mengklik *"Sahkan Berkas & Terbitkan Nomor"*.
  - Terbit nomor urut resmi format `{tahun}{4digit}` (misal: `270002`).
  - Terpeta otomatis ke batch jadwal wawancara harian (kuota 70/hari).
- [ ] **[ADM-06] Reopen Registration (Buka Ulang Akses)**: Panitia dapat membuka akses perbaikan data pendaftar dengan batas waktu kedaluwarsa dan alasan wajib.
- [ ] **[ADM-07] Master Jadwal Wawancara (`/admin/interview-batches`)**: Superadmin dapat mengelola gelombang tanggal, jam, kuota, dan rentang urutan nomor tanpa risiko overlap.
- [ ] **[ADM-08] Master Tahap & Poin Wawancara (`/admin/interview-stages`)**: Superadmin dapat mengelola 3 tahap wawancara dan daftar poin penilaian.
- [ ] **[ADM-09] Audit Trail Log (`/admin/audit-logs`)**: Setiap perubahan data penting oleh admin tercatat rapi beserta nama aktor dan stempel waktu.

---

## 7. Verifikasi Responsivitas Desain (Mobile, Tablet, Desktop)

- [ ] **[RSP-01] Mobile (< 640px)**:
  - Navbar dan sidebar otomatis menjadi mobile drawer toggle (hamburger) dengan backdrop gelap.
  - Form input mengalir 1 kolom vertikal dengan touch target minimal 44px.
  - Tabel dibungkus `overflow-x-auto` sehingga tidak ada layout yang pecah.
- [ ] **[RSP-02] Tablet (640px - 1024px)**:
  - Grid formulir dan kartu ringkasan otomatis menjadi 2 kolom (`sm:grid-cols-2`).
- [ ] **[RSP-03] Desktop (> 1024px)**:
  - Metrik statistik tampil dalam 4 kolom (`lg:grid-cols-4`).
  - Sidebar admin menetap di sisi kiri dengan area konten yang dapat di-scroll mandiri.
