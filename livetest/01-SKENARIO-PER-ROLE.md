# SKENARIO PENGUJIAN PER PERAN (SKENARIO-PER-ROLE.md)
### Rincian Skenario, Langkah Kerja, dan Ekspektasi Hasil Uji untuk Setiap Role

Dokumen ini menguraikan skenario langkah demi langkah (*step-by-step test journey*) saat Anda atau AI menjalankan live test sebagai masing-masing peran.

> **Skema default:** mulai dari login akun dummy. `/daftar`, `/aktivasi`, OTP, dan WhatsApp bukan bagian dari prasyarat skenario ini; alur tersebut diuji manual terpisah oleh pemilik proyek.

---

## SKENARIO 1: Pengujian Sebagai Pendaftar Baru (Status DRAFT)
- **Tujuan**: Memastikan alur pendaftar dari pengisian modul formulir hingga siap diserahkan berjalan mulus tanpa error.
- **Aktor**: Wali Murid Calon Siswa (Budi Santoso).
- **Halaman Masuk**: `/login`
- **Kredensial**:
  - NIK: `[NIK-DUMMY-DIHAPUS]`
  - Password: `[KREDENSIAL-DIHAPUS]`

### Langkah Pengujian:
1. **Login Calon Siswa**:
   - Buka `/login`.
   - Masukkan NIK `[NIK-DUMMY-DIHAPUS]` dan Password `[KREDENSIAL-DIHAPUS]`.
   - Klik **"Masuk ke Akun Calon Siswa"**.
   - **Ekspektasi**: Pengguna **langsung diarahkan ke `/pendaftar/dashboard`**. Tidak ada paksaan ganti password di awal. Terdapat tombol *"Ganti Kata Sandi (Opsional)"* di navbar dan banner hero.
2. **Periksa Dasbor Pendaftar**:
   - Kartu NIK menampilkan `3577********0001` (masking aktif).
   - WhatsApp terdaftar: `081234560001`.
   - Status: `DRAFT`.
3. **Pengisian Formulir 5 Langkah**:
   - **Langkah 1 (Biodata)**: Buka `/pendaftar/biodata`. Cek tanggal lahir, jenis kelamin, dan usia anak otomatis (dihitung per 1 Juli 2026). Klik Simpan.
   - **Langkah 2 (Alamat)**: Buka `/pendaftar/alamat`. Lengkapi jalan, RT, RW, kelurahan, kecamatan, kode pos. Cek estimasi jarak otomatis ke madrasah. Klik Simpan.
   - **Langkah 3 (Orang Tua)**: Buka `/pendaftar/orang-tua`. Isi identitas Ayah dan Ibu kandung (Nama, NIK, Pendidikan, Pekerjaan, Penghasilan, No HP). Klik Simpan.
   - **Langkah 4 (Pendidikan)**: Buka `/pendaftar/pendidikan`. Pilih jenis asal sekolah (TK/RA), isi nama sekolah dan NPSN. Klik Simpan.
   - **Langkah 5 (Pilihan Program)**: Buka `/pendaftar/program`. Pilih program pendidikan (**Program Reguler** atau **Program Unggulan**). Klik Simpan.
4. **Unggah Dokumen Berkas**:
   - Buka `/pendaftar/dokumen`.
   - Unggah berkas contoh: Kartu Keluarga (JPG/PDF), Akta Kelahiran, dan Pas Foto Siswa (maksimal 5 MB).
   - Pastikan berkas tersimpan dan tombol pratinjau (*preview*) dokumen privat berfungsi.
5. **Cetak PDF Draf**:
   - Di dasbor, klik **"Buat Lembar Draf PDF"**.
   - **Ekspektasi**: File PDF terunduh dengan tanda air (*watermark*) **`DRAFT`** di latar belakang.
6. **Final Submit (Persetujuan Legalitas)**:
   - Buka `/pendaftar/pendaftaran/tinjau`.
   - Cek indikator kelengkapan. Jika seluruhnya hijau, centang kotak persetujuan legalitas data (*Consent v1.0*).
   - Klik tombol **"Kirim Pendaftaran"**.
   - **Ekspektasi**: Status berubah menjadi **`SUBMITTED`**. Formulir terkunci. Muncul banner *"Menunggu Pengesahan Berkas oleh Panitia"*.

---

## SKENARIO 2: Pengujian Sebagai Panitia PPDB (Verifikator / `admin_ppdb`)
- **Tujuan**: Memastikan panitia dapat memantau data pendaftar, memfilter data, memeriksa berkas, dan mengesahkan berkas untuk menerbitkan nomor urut pendaftaran.
- **Aktor**: Panitia Verifikator 1.
- **Halaman Masuk**: `/admin/login`
- **Kredensial**:
  - Email: `admin1@minsatama.sch.id`
  - Password: `[KREDENSIAL-DIHAPUS]`

### Langkah Pengujian:
1. **Login Panitia**:
   - Buka `/admin/login`.
   - Masukkan email `admin1@minsatama.sch.id` dan password `[KREDENSIAL-DIHAPUS]`.
   - Klik Masuk.
   - **Ekspektasi**: Berhasil masuk ke `/admin/dashboard`.
2. **Pantau Dasbor Dinamis**:
   - Periksa kartu statistik: Total Calon Siswa, Total DRAFT, Total SUBMITTED, Grafik Program Reguler & Unggulan, serta Periode Aktif.
   - Pastikan seluruh angka berasal dari query database (bukan angka statis).
3. **Cari & Filter Pendaftar (`/admin/pendaftar`)**:
   - Buka menu **"Data Pendaftar"**.
   - Coba lakukan pencarian nama *"Siti Rahmawati"* atau ketik NIK.
   - Coba filter berdasarkan status *"SUBMITTED"*.
   - **Ekspektasi**: Tabel pendaftar menampilkan data yang sesuai secara responsif (`overflow-x-auto`).
4. **Buka Detail Pendaftar & Pengesahan Berkas**:
   - Klik tombol **"Detail"** pada calon siswa Siti Rahmawati (`/admin/pendaftar/{id}`).
   - Periksa 11 kartu identitas (Biodata, Alamat, Orang Tua, Sekolah, Program, Dokumen, Audit Log).
   - Klik tombol **"Sahkan Berkas & Terbitkan Nomor"** pada bagian atas header.
   - Konfirmasi pada modal konfirmasi browser.
   - **Ekspektasi**:
     - Sistem menerbitkan Nomor Urut Pendaftaran format resmi `{tahun}{4digit}` (contoh: `270002`).
     - Mengalokasikan jadwal gelombang wawancara otomatis sesuai kuota harian.
     - Status berubah menjadi **Terverifikasi (VERIFIED)**.
     - Riwayat tindakan tercatat di Audit Log.

---

## SKENARIO 3: Pengujian Sebagai Pendaftar yang Telah Disahkan (`VERIFIED`)
- **Tujuan**: Memastikan pendaftar yang telah disahkan menerima nomor urut resmi, melihat rincian jadwal wawancara, dan dapat mengunduh Bukti Pendaftaran PDF Final ber-QR Code.
- **Aktor**: Calon Siswa Terverifikasi (Ahmad Dany Pratama).
- **Halaman Masuk**: `/login`
- **Kredensial**:
  - NIK: `[NIK-DUMMY-DIHAPUS]`
  - Password: `[KREDENSIAL-DIHAPUS]`

### Langkah Pengujian:
1. **Login Calon Siswa**:
   - Masuk di `/login` dengan NIK `[NIK-DUMMY-DIHAPUS]` dan password `[KREDENSIAL-DIHAPUS]`.
2. **Cek Tampilan Dasbor**:
   - Badge nomor pendaftaran resmi menampilkan **`270001`**.
   - Status menampilkan **`Sudah Terkirim & Terverifikasi`**.
   - Tampil rincian jadwal wawancara: Gelombang, Tanggal, Jam, dan Lokasi Ruangan.
3. **Unduh Bukti Pendaftaran PDF Final**:
   - Buka menu **"Tinjau & Bukti"** (`/pendaftar/pendaftaran/tinjau`) atau tombol unduh di dasbor.
   - Klik **"Unduh Bukti Pendaftaran Resmi (PDF)"**.
   - **Ekspektasi**: Berkas PDF bersih (tanpa watermark DRAFT), memuat Nomor Urut `270001`, jadwal wawancara, dan QR Code verifikasi.
4. **Uji Verifikasi QR Code Publik**:
   - Klik atau buka tautan verifikasi QR publik: `/verifikasi/bukti-pendaftaran/{token}`.
   - **Ekspektasi**: Halaman publik menampilkan validitas berkas sah atas nama siswa MIN 1 Kota Madiun tanpa membocorkan data pribadi (PII terlindungi).

---

## SKENARIO 4: Pengujian Sebagai Super Administrator (`superadmin`)
- **Tujuan**: Memverifikasi kendali penuh pengaturan master wawancara, audit log, dan isolasi hak akses tingkat tinggi.
- **Aktor**: Super Administrator PPDB.
- **Halaman Masuk**: `/admin/login`
- **Kredensial**:
  - Email: `admin@minsatama.sch.id`
  - Password: `[KREDENSIAL-DIHAPUS]`

### Langkah Pengujian:
1. **Login Superadmin**:
   - Masuk di `/admin/login` dengan kredensial superadmin.
2. **Kelola Master Jadwal Wawancara (`/admin/interview-batches`)**:
   - Buka menu **"Jadwal Wawancara"**.
   - Lihat daftar gelombang (Batch 1, 2, dst.).
   - Klik **"Tambah Jadwal Gelombang"**:
     - Sistem otomatis menyarankan nomor urut lanjutan dari batch sebelumnya.
     - Coba simpan rentang yang tumpang tindih (overlap): sistem wajib menolak.
     - Tambah batch baru yang valid: berhasil tersimpan dan tercatat di audit log.
3. **Kelola Master Tahap & Poin Wawancara (`/admin/interview-stages`)**:
   - Buka menu **"Tahap & Poin Wawancara"**.
   - Lihat 3 Tahap: Tahap 1 (BTQ), Tahap 2 (Calistung), Tahap 3 (Khusus Unggulan).
   - Buka rincian poin penilaian pada Tahap 1.
   - Coba tambah poin baru atau nonaktifkan poin.
   - Pastikan poin yang telah memiliki riwayat nilai tidak dapat dihapus permanen (*soft toggle only*).
4. **Periksa Audit Log Sistem (`/admin/audit-logs`)**:
   - Buka menu Audit Log.
   - Pastikan seluruh aktivitas admin (pengesahan berkas, perubahan batch) tercatat dengan nama aktor, waktu, dan rincian perubahan.
