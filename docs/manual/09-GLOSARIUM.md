# GLOSARIUM ISTILAH SISTEM
## Sistem Informasi PPDB Online MIN 1 Kota Madiun (PPDB Minsatama)
### Tahun Ajaran 2026/2027

Dokumen glosarium ini menyajikan penjelasan sederhana mengenai istilah teknis, aturan bisnis, dan terminologi kepanitiaan madrasah yang digunakan di seluruh aplikasi PPDB Minsatama.

---

### A
- **Account Type**: Pembeda jenis akun di tabel `users`. Terdiri dari `applicant` (akun calon siswa/orang tua) dan `admin` (akun panitia madrasah).
- **Admin Reopen**: Fitur otorisasi khusus yang memungkinkan panitia membuka kembali akses edit formulir bagi pendaftar yang telah melakukan submit final pada saat periode pendaftaran telah ditutup.
- **Age Reference Date (Tanggal Acuan Umur)**: Tanggal standar resmi (1 Juli tahun ajaran berjalan) yang digunakan sebagai patokan untuk menghitung usia calon peserta didik saat mendaftar.
- **Applicant Policy**: Lapisan keamanan di level backend yang memastikan seorang pendaftar hanya memiliki wewenang untuk melihat dan mengubah data pendaftarannya sendiri.
- **Audit Trail**: Catatan riwayat aktivitas operasional penting di basis data (seperti siapa admin yang membuka akses edit, kapan dibuka, dan alasan pembukaan).

### B
- **Bacon QR Code**: Pustaka perangkat lunak yang digunakan oleh sistem untuk menghasilkan kode QR dalam format grafis vektor SVG tajam.
- **Batas Efektif Unggah (5120 KB)**: Ukuran berkas digital maksimal yang dapat diterima oleh sistem untuk satu dokumen (5 Megabyte).

### C
- **Consent v1.0**: Klausul pernyataan keabsahan data resmi yang wajib disetujui dan ditandatangani secara digital oleh orang tua sebelum pendaftaran dapat dikirimkan.
- **CSPRNG (Cryptographically Secure Pseudo-Random Number Generator)**: Generator bilangan acak berstandar keamanan tinggi yang digunakan untuk memproduksi token dan kunci rahasia aplikasi.

### D
- **Data Revision**: Angka pencatat versi perubahan data pada formulir pendaftaran. Angka ini bertambah otomatis setiap kali orang tua memperbarui data setelah pendaftaran terkirim.
- **Dompdf**: Mesin (*rendering engine*) internal server yang mengubah susunan kode HTML dan gaya visual CSS menjadi berkas dokumen cetak PDF standar A4.
- **DRAFT**: Status awal formulir pendaftaran di mana orang tua masih bebas melengkapi atau mengubah isian data. Pada status ini, pendaftar belum memiliki nomor registrasi resmi.

### E
- **Early Close**: Tindakan penutupan jendela akses edit ulang oleh panitia lebih cepat dari waktu kedaluwarsa yang telah dijadwalkan.
- **Ekstensi PHP `gd`**: Modul pemroses grafis pada server PHP yang diwajibkan aktif agar mesin PDF dapat menanam pas foto berformat PNG ke dalam lembar dokumen.

### F
- **Feature Gap**: Celah perbedaan antara fitur yang direncanakan atau yang ada di izin database dengan ketersediaan antarmuka web aktual pada sistem.
- **Finfo (File Information)**: Fitur keamanan server PHP yang memeriksa isi biner asli suatu berkas (*magic bytes*) untuk memastikan berkas yang diunggah benar-benar PDF atau gambar asli, bukan berkas berbahaya yang sekadar diganti nama ekstensinya.

### I
- **IDOR (Insecure Direct Object Reference)**: Celah keamanan aplikasi web di mana pengguna jahat mencoba mengakses data milik pengguna lain dengan mengganti nomor ID pada alamat URL. Sistem Minsatama kebal terhadap celah ini.

### M
- **MIME Type**: Standar internet penanda tipe berkas (misal: `application/pdf`, `image/jpeg`, `image/png`).

### N
- **NIK (Nomor Induk Kependudukan)**: 16 digit nomor identitas kependudukan unik yang diterbitkan oleh Dinas Kependudukan dan Pencatatan Sipil (Dukcapil). Menjadi *username* akun calon siswa.
- **NIK Encrypted (`nik_encrypted`)**: Format penyimpanan NIK di database yang telah disandi menggunakan algoritma enkripsi simetris AES-256-CBC sehingga tidak dapat dibaca oleh pihak luar tanpa kunci rahasia.
- **NIK Lookup Hash (`nik_lookup_hash`)**: Nilai hash satu arah deterministik berbasis HMAC-SHA256 yang digunakan oleh sistem untuk mencari data siswa di database tanpa perlu mendekripsi seluruh data.
- **NIK Masking**: Format tampilan NIK yang disensor pada antarmuka layar biasa (contoh: `3573********0001`) demi melindungi privasi anak dari pandangan orang lain di sekitarnya.
- **Nomor Registrasi Resmi**: Nomor identitas resmi pendaftaran permanen dengan format baku `PPDB/{tahun-ajaran}/{kode-program}/{urutan-5-digit}` (contoh: `PPDB/2026-2027/REGULER/00001`).

### O
- **OTP (One-Time Password)**: Kode rahasia acak 6 digit yang diterbitkan sementara (5 menit) untuk memvalidasi kepemilikan nomor telepon orang tua saat aktivasi akun atau pemulihan kata sandi.
- **Outdated PDF**: Status penanda pada dokumen PDF Bukti Pendaftaran versi lama yang menandakan bahwa isian data formulir telah mengalami revisi sehingga pendaftar harus mencetak ulang PDF versi terbaru.

### P
- **PDF Draf**: Lembaran pratinjau formulir pendaftaran berukuran A4 yang memuat tanda air (*watermark*) transparan bertuliskan *"DRAFT - BUKAN BUKTI RESMI"* dan NIK termasker.
- **PDF Final (Bukti Pendaftaran Resmi)**: Dokumen resmi tanda bukti pengiriman pendaftaran yang memuat kop madrasah, nomor registrasi resmi, NIK lengkap, pas foto, dan kode QR verifikasi.
- **Private Storage**: Folder penyimpanan berkas privat di server (`storage/app/private/`) yang berada di luar jangkauan akses URL web langsung. Dokumen hanya dapat diunduh melalui controller terotorisasi.
- **PTSP (Pelayanan Terpadu Satu Pintu)**: Loket layanan terpadu di kantor MIN 1 Kota Madiun tempat orang tua menyerahkan berkas fisik cetakan bukti registrasi dan mencocokkan dokumen asli.

### Q
- **QR Code Verifikasi**: Kode matriks dua dimensi pada lembar bukti pendaftaran yang mengarahkan ke halaman verifikasi resmi madrasah untuk membuktikan keaslian dokumen fisik yang dibawa ke loket.

### R
- **Runtime Age Calculation**: Metode kalkulasi usia otomatis (Tahun, Bulan, Hari) yang dihitung secara dinamis saat sistem berjalan, bukan disimpan sebagai kolom statis di database agar usia tidak menjadi keliru di masa depan.

### S
- **Server-Side Expiry**: Mekanisme penegakan batas waktu izin edit ulang di tingkat server secara langsung tanpa bergantung pada cron job terjadwal.
- **Sibling Support**: Kemampuan sistem yang mengizinkan satu nomor WhatsApp orang tua digunakan untuk mendaftarkan beberapa anak kandung/kembar yang berbeda NIK.
- **Spatie Laravel Permission**: Pustaka manajemen peran dan izin berstandar industri yang mengatur wewenang administrator pada guard web.
- **SUBMITTED**: Status resmi pendaftaran yang telah dikirim secara permanen oleh orang tua dan telah mengunci nomor registrasi resmi.

### Z
- **Zero PII Leakage**: Standar privasi pada halaman verifikasi kode QR publik yang tidak menampilkan data identitas pribadi sensitif siswa (seperti nama orang tua, alamat rumah, nomor telepon, dan NIK lengkap) kepada publik pemindai kode.
