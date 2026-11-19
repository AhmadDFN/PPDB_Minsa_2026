# USER ACCEPTANCE TESTING (UAT) TEST PLAN
## Sistem Informasi PPDB Online MIN 1 Kota Madiun (PPDB Minsatama)
### Tahun Ajaran 2026/2027

**Versi Dokumen**: 1.0.0
**Tanggal Penerbitan**: 19 November 2026
**Penanggung Jawab**: QA Lead & Tim Pengembang PPDB Minsatama
**Sasaran Pengguna Dokumen**: Panitia PPDB, Tim IT Madrasah, Pimpinan MIN 1 Kota Madiun, dan Penguji Perwakilan Wali Murid.

---

> [!IMPORTANT]
> **Petunjuk Pelaksanaan UAT bagi Penguji Sekolah**:
> 1. Dokumen ini dirancang agar dapat diuji langsung oleh panitia madrasah dan perwakilan wali murid tanpa perlu memahami kode pemrograman.
> 2. Kolom **Hasil Aktual**, **Status (PASS/FAIL)**, **Nama Penguji**, **Tanggal Uji**, dan **Catatan** sengaja dikosongkan untuk diisi secara fisik/digital saat sesi pengujian bersama pihak sekolah berlangsung.
> 3. Jangan menandai pengujian sebagai **PASS** sebelum langkah tersebut benar-benar diverifikasi secara faktual di layar antarmuka pengguna.
> 4. Lingkup sistem PPDB Minsatama **BERAKHIR** pada saat pendaftar berhasil mencetak dokumen Bukti Pendaftaran (PDF) dan membawanya ke loket PTSP madrasah. Sistem **tidak mengelola** seleksi, pemeringkatan, pengumuman, atau daftar ulang.

---

## 1. DAFTAR PENGUJIAN KELOMPOK PENDAFTAR / WALI MURID (APPLICANT)

| Test ID | Skenario Pengujian | Langkah Pengujian | Hasil yang Diharapkan (*Expected Result*) | Hasil Aktual | Status (PASS / FAIL) | Nama Penguji | Tanggal Uji | Catatan / Temuan |
|:---:|---|---|---|---|:---:|---|---|---|
| **UAT-APP-01** | Pendaftaran Akun Baru Calon Siswa | 1. Buka halaman utama PPDB.<br>2. Klik tombol "Daftar Sekarang".<br>3. Masukkan 16 digit NIK calon siswa, nama lengkap, jenis kelamin, tempat lahir, tanggal lahir, nomor WhatsApp aktif orang tua, dan centang persetujuan.<br>4. Klik tombol "Daftar Akun". | Sistem menerima data pendaftaran, mengalihkan pengguna ke halaman Verifikasi OTP WhatsApp, dan mengirimkan kode OTP 6 digit ke nomor WhatsApp orang tua. Pesan sukses berbahasa Indonesia ditampilkan. | | *MENUNGGU SESI SEKOLAH* | | | |
| **UAT-APP-02** | Verifikasi OTP WhatsApp (Aktivasi) | 1. Periksa pesan masuk WhatsApp pada nomor yang didaftarkan.<br>2. Masukkan 6 digit kode OTP pada formulir verifikasi.<br>3. Klik "Verifikasi OTP". | Sistem memvalidasi kode OTP dalam kurun waktu 5 menit. Jika benar, sistem mengalihkan pengguna ke halaman Pembuatan Kata Sandi Baru. | | *MENUNGGU SESI SEKOLAH* | | | |
| **UAT-APP-03** | Pembuatan Kata Sandi Akun | 1. Masukkan kata sandi minimal 8 karakter.<br>2. Masukkan konfirmasi kata sandi yang sama persis.<br>3. Klik "Simpan Kata Sandi & Selesaikan Aktivasi". | Akun calon siswa dinyatakan AKTIF. Sistem mengarahkan ke halaman Masuk (Login) disertai pesan notifikasi aktivasi berhasil. | | *MENUNGGU SESI SEKOLAH* | | | |
| **UAT-APP-04** | Masuk (Login) Akun Calon Siswa | 1. Pada halaman login pendaftar, masukkan NIK anak (16 digit) sebagai username.<br>2. Masukkan kata sandi yang telah dibuat.<br>3. Klik "Masuk ke Akun". | Pengguna berhasil masuk dan langsung diarahkan ke Dasbor Utama Pendaftar. Nama siswa dan NIK termasker (contoh: `3573********0001`) tampil dengan benar. | | *MENUNGGU SESI SEKOLAH* | | | |
| **UAT-APP-05** | Pengisian Formulir Langkah 1: Biodata Siswa | 1. Masuk menu Biodata.<br>2. Periksa NIK (terkunci/tidak dapat diedit).<br>3. Lengkapi data: NISN (opsional), anak ke-, dan jumlah saudara kandung.<br>4. Klik "Simpan & Lanjutkan". | Data biodata tersimpan rapi, indikator kelengkapan modul Biodata pada dasbor berubah menjadi "Lengkap", dan pengguna diarahkan ke pengisian Alamat. | | *MENUNGGU SESI SEKOLAH* | | | |
| **UAT-APP-06** | Pengisian Formulir Langkah 2: Alamat Domisili | 1. Masuk menu Alamat.<br>2. Isi jalan, RT, RW (mempertahankan angka nol di depan seperti 002/005), kelurahan, kecamatan, kota, provinsi, kode pos, dan estimasi jarak.<br>3. Klik "Simpan & Lanjutkan". | Alamat tersimpan, persentase kelengkapan bertambah, dan sistem mengalihkan ke formulir Data Orang Tua. | | *MENUNGGU SESI SEKOLAH* | | | |
| **UAT-APP-07** | Pengisian Formulir Langkah 3: Data Orang Tua & Wali | 1. Lengkapi seluruh identitas Ayah Kandung (Nama, NIK, Status Hidup, Pendidikan, Pekerjaan, Penghasilan, No HP).<br>2. Lengkapi seluruh identitas Ibu Kandung.<br>3. Pilih apakah menggunakan Wali (opsional).<br>4. Klik "Simpan & Lanjutkan". | Data orang tua tersimpan. Status kelengkapan Ayah dan Ibu pada dasbor menjadi "Lengkap". | | *MENUNGGU SESI SEKOLAH* | | | |
| **UAT-APP-08** | Pengisian Formulir Langkah 4: Riwayat Pendidikan Asal | 1. Pilih jenjang sekolah asal (RA / TK / PAUD / Lainnya / Tidak Sekolah).<br>2. Masukkan nama sekolah TK/RA asal dan NPSN (jika ada).<br>3. Klik "Simpan & Lanjutkan". | Riwayat pendidikan tersimpan, modul pendidikan dinyatakan "Lengkap". | | *MENUNGGU SESI SEKOLAH* | | | |
| **UAT-APP-09** | Pemilihan Program Pendidikan | 1. Masuk menu Pilihan Program.<br>2. Pilih salah satu jalur yang dibuka (Program Reguler atau Program Unggulan).<br>3. Klik "Simpan Pilihan Program". | Pilihan program tersimpan sebagai status `DRAFT`. Nama program terpilih tampil pada ringkasan dasbor. | | *MENUNGGU SESI SEKOLAH* | | | |
| **UAT-APP-10** | Unggah Dokumen Persyaratan (Upload Berkas) | 1. Masuk menu Unggah Berkas Persyaratan.<br>2. Unggah berkas Kartu Keluarga (PDF atau JPG/PNG maks 5 MB).<br>3. Unggah berkas Pas Foto Calon Siswa.<br>4. Klik "Unggah Berkas". | Berkas tersimpan ke penyimpanan privat yang aman. Muncul status "Sudah Diunggah" lengkap dengan tombol "Lihat Berkas" dan "Ganti Berkas". | | *MENUNGGU SESI SEKOLAH* | | | |
| **UAT-APP-11** | Penerbitan dan Pengecekan PDF Draf | 1. Sebelum mengirim pendaftaran final, klik tombol "Cetak PDF Draf" pada dasbor.<br>2. Buka berkas PDF yang terunduh. | Berkas PDF berukuran standar A4 terbuka dengan sempurna. Pada latar belakang halaman terdapat watermark diagonal transparan bertuliskan **"DRAFT - BUKAN BUKTI RESMI"**, dan NIK calon siswa ditampilkan dalam format masker. | | *MENUNGGU SESI SEKOLAH* | | | |
| **UAT-APP-12** | Peninjauan Ringkasan & Persetujuan (Submit Final) | 1. Buka menu "Tinjau & Kirim Pendaftaran".<br>2. Periksa ringkasan seluruh isian data.<br>3. Baca pernyataan persetujuan (Consent v1.0).<br>4. Centang kotak persetujuan dan ketikkan nama wali murid penanggung jawab.<br>5. Klik tombol "Kirim Pendaftaran Final". | Sistem memvalidasi kelengkapan seluruh modul. Pendaftaran berhasil dikirim, status berubah menjadi `SUBMITTED`, dan sistem menerbitkan Nomor Registrasi Resmi permanen (contoh: `PPDB/2026-2027/REGULER/00001`). | | *MENUNGGU SESI SEKOLAH* | | | |
| **UAT-APP-13** | Pengunduhan Bukti Pendaftaran Resmi (PDF Final) | 1. Pada dasbor setelah status `SUBMITTED`, klik "Cetak Bukti Pendaftaran Resmi (PDF Final)".<br>2. Unduh dan buka dokumen PDF tersebut. | Dokumen PDF resmi terbuka tanpa watermark draf. Memuat kop madrasah resmi, Nomor Registrasi resmi, NIK calon siswa lengkap, pas foto, klausul persetujuan historis, petunjuk penyerahan berkas ke PTSP, dan kode QR verifikasi resmi. | | *MENUNGGU SESI SEKOLAH* | | | |
| **UAT-APP-14** | Pemindaian Kode QR Verifikasi Dokumen | 1. Gunakan aplikasi pemindai QR atau kamera smartphone untuk memindai kode QR pada cetakan PDF.<br>2. Buka tautan HTTPS yang dihasilkan pemindai. | Halaman verifikasi publik madrasah terbuka dengan aman. Menampilkan: Nomor Registrasi, Tanggal Terbit, Fingerprint Dokumen, dan Status Dokumen ("current"). **Tidak membocorkan data pribadi (PII)** seperti NIK lengkap, nomor telepon, alamat, atau nama orang tua. | | *MENUNGGU SESI SEKOLAH* | | | |
| **UAT-APP-15** | Perubahan Data Pasca-Submit (Periode Terbuka) | 1. Saat periode pendaftaran masih aktif dibuka, ubah data alamat domisili.<br>2. Klik "Simpan Perubahan".<br>3. Kembali ke dasbor pendaftar. | Perubahan tersimpan. Nilai revisi data bertambah (`data_revision`). Dokumen PDF final versi lama ditandai dengan status **"Perlu Diterbitkan Ulang (Outdated)"** tanpa mengubah Nomor Registrasi atau tanggal submit awal. Pendaftar dapat menekan tombol cetak ulang untuk menghasilkan PDF Final versi terbaru. | | *MENUNGGU SESI SEKOLAH* | | | |

---

## 2. DAFTAR PENGUJIAN KELOMPOK ADMINISTRATOR / PANITIA PPDB

| Test ID | Skenario Pengujian | Langkah Pengujian | Hasil yang Diharapkan (*Expected Result*) | Hasil Aktual | Status (PASS / FAIL) | Nama Penguji | Tanggal Uji | Catatan / Temuan |
|:---:|---|---|---|---|:---:|---|---|---|
| **UAT-ADM-01** | Masuk (Login) Akun Administrator | 1. Buka halaman login admin madrasah (`/admin/login`).<br>2. Masukkan alamat email panitia dan kata sandi.<br>3. Klik "Masuk sebagai Administrator". | Panitia berhasil masuk dan diarahkan ke panel administrator PPDB. | | *MENUNGGU SESI SEKOLAH* | | | |
| **UAT-ADM-02** | Pemeriksaan Hak Akses (*Permission Check*) | 1. Masuk menggunakan akun staf biasa yang tidak memiliki izin `reopen_registration`.<br>2. Coba akses menu pembukaan pendaftaran (`/admin/registrations`). | Sistem menolak akses dengan respons 403 Forbidden sesuai prinsip hak akses berjenjang Spatie Permission. | | *MENUNGGU SESI SEKOLAH* | | | |
| **UAT-ADM-03** | Pembukaan Akses Edit Pendaftar (*Reopen Registration*) | 1. Masuk sebagai Admin dengan izin `reopen_registration`.<br>2. Pilih pendaftar yang membutuhkan perbaikan data.<br>3. Klik tombol "Buka Akses Edit".<br>4. Masukkan alasan pembukaan (wajib diisi, misal: "Permintaan perbaikan kartu keluarga dari orang tua") dan tentukan waktu kedaluwarsa (misal: 2 jam ke depan).<br>5. Klik "Konfirmasi Buka Akses". | Akses edit untuk siswa tersebut berhasil dibuka. Sistem mencatat log pembukaan ke tabel audit. Status jendela akses aktif (*open*) tampil di daftar panitia. Siswa lain tetap terkunci. | | *MENUNGGU SESI SEKOLAH* | | | |
| **UAT-ADM-04** | Penutupan Akses Edit Lebih Awal (*Early Close*) | 1. Pada pendaftaran yang sedang berstatus dibuka (*reopened*), klik tombol "Tutup Akses Sekarang".<br>2. Masukkan alasan penutupan lebih awal.<br>3. Klik "Konfirmasi Penutupan". | Jendela akses edit segera ditutup seketika. Formulir pendaftar langsung terkunci kembali tanpa menunggu waktu kedaluwarsa habis. | | *MENUNGGU SESI SEKOLAH* | | | |
| **UAT-ADM-05** | Penguncian Otomatis Pasca-Kedaluwarsa (*Server-Side Expiry*) | 1. Biarkan jendela akses edit melewati batas waktu kedaluwarsa (*expires_at*).<br>2. Pendaftar mencoba menyimpan perubahan data. | Sistem secara otomatis menolak penyimpanan dengan pesan bahwa waktu edit ulang telah berakhir. Nomor registrasi dan status submit awal tetap aman tidak berubah. | | *MENUNGGU SESI SEKOLAH* | | | |
| **UAT-ADM-06** | Pemeriksaan Jejak Audit (*Audit Trail Integrity*) | 1. Buka catatan riwayat/log pada pendaftaran terkait di database/panel. | Seluruh riwayat terekam lengkap: aksi pembukaan oleh admin, alasan pembukaan, waktu kedaluwarsa, perubahan revisi data oleh siswa, dan penutupan jendela. Tidak ada celah manipulasi log. | | *MENUNGGU SESI SEKOLAH* | | | |

---

## 3. LEMBAR PENGESAHAN HASIL UJI PENERIMAAN (UAT SIGN-OFF)

Pengesahan ini ditandatangani setelah seluruh skenario di atas diuji secara langsung pada lingkungan staging/uji bersama pihak madrasah:

| Peran Jabatan | Nama Terang | Tanda Tangan | Tanggal Pengesahan |
|---|---|:---:|:---:|
| **Ketua Panitia PPDB 2026/2027** | *(Menunggu Pelaksanaan)* | `[                   ]` | `___ / ___ / 2026` |
| **Koordinator IT Madrasah** | *(Menunggu Pelaksanaan)* | `[                   ]` | `___ / ___ / 2026` |
| **Perwakilan PTSP Madrasah** | *(Menunggu Pelaksanaan)* | `[                   ]` | `___ / ___ / 2026` |
| **Lead QA & Pengembang Sistem** | Tim Teknis PPDB Minsatama | `[                   ]` | `___ / ___ / 2026` |
