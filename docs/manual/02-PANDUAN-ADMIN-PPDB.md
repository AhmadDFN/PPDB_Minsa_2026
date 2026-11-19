# PANDUAN OPERASIONAL — ADMINISTRATOR PPDB (PANITIA)
## Sistem Informasi PPDB Online MIN 1 Kota Madiun (PPDB Minsatama)
### Tahun Ajaran 2026/2027

Buku panduan ini disusun khusus untuk staf dan panitia madrasah yang memiliki peran **Admin PPDB** (`admin_ppdb`) dalam sistem PPDB Minsatama. Seluruh isi panduan ini merujuk secara ketat pada fitur yang **benar-benar terpasang dan tersedia pada sistem saat ini**.

---

## 1. PENGENALAN PERAN & PRINSIP ADMINISTRATOR

### 1.1 Peran Admin PPDB (`admin_ppdb`)
Peran `admin_ppdb` adalah peran operasional kepanitiaan madrasah yang terdaftar pada tabel pengguna (`users`) dengan `account_type = admin` dan memiliki izin (*permissions*) Spatie:
1. `view_dashboard` (Akses dasbor administrator)
2. `view_applicants` (Melihat daftar pendaftaran terkirim)
3. `reopen_registration` (Membuka dan menutup jendela izin edit ulang pendaftar)
4. `verify_documents` (Izin verifikasi dokumen — *catatan: antarmuka web khusus verifikasi dokumen belum tersedia*)
5. `export_reports` (Izin ekspor rekapitulasi — *catatan: antarmuka web ekspor laporan belum tersedia*)

### 1.2 Batasan Hak Akses & Integritas
- Administrator masuk menggunakan **Alamat Email dan Kata Sandi**, bukan NIK.
- Administrator dilarang keras mencoba masuk melalui formulir login calon siswa.
- Administrator tidak memiliki hak akses mengubah struktur database atau mengubah hak akses Spatie.
- Tindakan pembukaan akses edit (*reopen*) dicatat secara permanen pada log audit (*audit trail*).

---

## 2. AKSES MASUK & DASBOR ADMINISTRATOR

### 2.1 Alur Masuk (Login Admin)
1. Buka peramban (*browser*) dan akses rute khusus login admin:
   `https://<domain-aplikasi>/admin/login`
2. Masukkan kredensial akun panitia:
   - **Email**: Alamat email kedinasan panitia yang telah didaftarkan.
   - **Kata Sandi**: Kata sandi rahasia akun admin Anda.
3. Klik tombol **"Masuk sebagai Administrator"**.
4. Sistem memverifikasi kecocokan kredensial dan memastikan akun bertipe `admin` dengan status `ACTIVE`. Jika valid, Anda akan dialihkan ke halaman dasbor admin.

### 2.2 Dasbor Administrator (`/admin/dashboard` & `/admin/preview`)
- Saat ini antarmuka dasbor admin menampilkan ringkasan kartu statistik fondasi (*preview card*):
  - Total Pendaftar
  - Berkas Terkirim
  - Draf Pendaftaran
  - Status Periode PPDB
- Pada bilah navigasi atas/samping, terdapat menu utama:
  - **Dasbor**: Halaman ringkasan.
  - **Edit Ulang Pendaftaran**: Menu pengelolaan akses pembukaan formulir siswa.
  - **Keluar (Logout)**: Tombol untuk mengakhiri sesi kerja administrator.

---

## 3. TATA KELOLA PEMBUKAAN AKSES EDIT ULANG (*ADMIN REOPEN WORKFLOW*)

Fitur utama yang saat ini telah aktif secara penuh pada antarmuka administrator adalah **Pengelolaan Akses Edit Ulang Pendaftaran (Reopen Registration)**.

```
[Admin Buka /admin/registrations] ──> [Pilih Siswa Tertentu] ──> [Isi Alasan & Waktu Expired] 
                                                                           │
                                                                           v
[Siswa Mengedit di Dasbornya]    <── [Jendela Akses Terbuka] <── [Klik Buka Akses]
```

### 3.1 Kapan Admin Boleh Membuka Akses Edit Ulang?
Akses edit ulang hanya boleh dibuka apabila memenuhi kondisi operasional berikut:
1. Pendaftaran calon siswa bersangkutan telah berstatus **`SUBMITTED`** (telah dikirim).
2. Periode pendaftaran resmi telah ditutup (*closed*), atau pendaftar membutuhkan perbaikan data formal atas rekomendasi verifikator loket PTSP madrasah.
3. Orang tua/wali siswa mengajukan permohonan resmi perbaikan data (misal: kesalahan ketik ejaan nama, pembaruan nomor Kartu Keluarga, atau pergantian pas foto).

> [!IMPORTANT]
> **SIFAT AKSES EDIT ULANG**:
> - Akses edit ulang dibuka **hanya untuk 1 (satu) pendaftaran spesifik**, bukan membuka sistem untuk seluruh pendaftar.
> - Pembukaan akses **TIDAK MENGUBAH** nomor registrasi resmi yang telah terbit.
> - Pembukaan akses **TIDAK MENGUBAH** tanggal submit awal (*submitted_at*).
> - Selama akses terbuka, siswa dapat memperbarui biodata, alamat, orang tua, riwayat TK, dan dokumen di dasbornya sendiri.

### 3.2 Langkah-Langkah Membuka Akses Edit Ulang
1. Masuk ke menu **"Akses Edit Ulang Pendaftaran"** (`/admin/registrations`).
2. Halaman menampilkan tabel daftar seluruh calon siswa yang telah melakukan submit final, memuat:
   - Nomor Pendaftaran (format `PPDB/2026-2027/...`).
   - Nama Lengkap Calon Siswa.
   - Periode & Program Terpilih.
   - Status Akses Edit Ulang Saat Ini.
   - Kolom Tindakan.
3. Cari nama atau nomor registrasi siswa yang mengajukan perbaikan, lalu klik tautan **"Buka akses"** di kolom Tindakan.
4. Anda akan diarahkan ke halaman detail pendaftaran (`/admin/registrations/{id}/reopen`):
   - Periksa kebenaran identitas siswa pada kartu *Rincian Pendaftaran*.
   - Pada kartu *Status Akses Saat Ini*, lengkapi formulir pembukaan:
     - **Alasan Pembukaan Akses (Wajib)**: Tuliskan alasan operasional secara jelas (maksimal 1000 karakter). Contoh: *"Permintaan orang tua untuk memperbaiki kesalahan ketik NIK Ibu Kandung sesuai berkas KK fisik asli."*
     - **Berlaku Sampai (Wajib)**: Tentukan batas waktu kedaluwarsa akses (tanggal dan jam di masa depan). Rekomendasi standar: berikan jendela waktu 2 jam s.d. 24 jam ke depan.
5. Klik tombol biru **"Buka akses edit ulang"**.
6. Sistem menyimpan jendela akses secara atomik, mencatat data pembuka ke tabel audit, dan mengaktifkan izin edit bagi pendaftar tersebut.

### 3.3 Penutupan Akses Edit Lebih Awal (*Early Close*)
Jika orang tua siswa telah selesai melakukan revisi sebelum waktu kedaluwarsa habis, atau jika terjadi kekeliruan pembukaan akses:
1. Buka kembali halaman detail pendaftaran siswa terkait di menu admin.
2. Pada kartu status, akan terlihat label: *"Aktif sampai [Tanggal Jam]"*.
3. Pada formulir penutupan di bawahnya:
   - Tuliskan **Alasan Penutupan Lebih Awal (Wajib)**. Contoh: *"Orang tua telah selesai mengunggah pas foto baru dan mengonfirmasi via WhatsApp."*
4. Klik tombol merah **"Tutup akses sekarang"**.
5. Akses edit bagi siswa tersebut langsung terkunci seketika tanpa menunggu waktu kedaluwarsa habis.

### 3.4 Penegakan Kedaluwarsa Otomatis di Sisi Server (*Server-Side Expiry*)
- Sistem menerapkan penegakan kedaluwarsa secara langsung di sisi server (*real-time evaluation*).
- Jika waktu telah melewati jam kedaluwarsa (*expires_at*), tombol simpan di dasbor siswa secara otomatis menolak penyimpanan data revisi meskipun siswa masih membuka halaman formulir.
- Tidak ada risiko siswa dapat mengubah data di luar batas waktu yang telah disetujui admin.

---

## 4. DAFTAR FITUR YANG BELUM TERSEDIA DI ANTARMUKA ADMIN (FEATURE GAPS)

Demi kejujuran operasional, seluruh panitia wajib mengetahui bahwa modul-modul berikut **BELUM MEMILIKI ANTARMUKA WEB PADA PANEL ADMIN**:

1. **Pencarian & Pemfilteran Pendaftar**:
   - Belum ada kotak pencarian instan (*search box*) berdasarkan nama atau NIK di halaman admin.
   - Belum ada filter penyaring berdasarkan jalur program (Reguler/Unggulan).
2. **Pratinjau Dokumen Berkas dari Sisi Admin**:
   - Admin belum memiliki tombol langsung untuk membuka file foto KK atau Pas Foto pendaftar di panel admin. Verifikasi berkas saat ini dilakukan secara manual fisik di loket PTSP madrasah.
3. **Pengunduhan PDF Bukti Pendaftaran oleh Admin**:
   - Pengunduhan PDF bukti pendaftaran saat ini hanya dapat diakses melalui akun siswa yang bersangkutan.
4. **Pengaturan Periode & Program Baru**:
   - Pembuatan tahun ajaran baru atau penambahan kuota program belum memiliki form input admin dan harus dikonfigurasi melalui database oleh tim IT madrasah / developer.
5. **Ekspor Data ke Excel**:
   - Belum tersedia tombol *Export to Excel/CSV* pada halaman admin.

*Rekomendasi penambahan antarmuka ini telah dicatat secara resmi pada dokumen [10-DAFTAR-FITUR-DAN-GAP.md](file:///D:/Dany/PPDB/PPDB_Minsa_2026/docs/manual/10-DAFTAR-FITUR-DAN-GAP.md).*

---

## 5. TANGGUNG JAWAB & INTEGRITAS KEAMANAN ADMIN

Setiap panitia pemegang akun admin wajib mematuhi kode etik berikut:

1. **Kerahasiaan Akun**: Dilarang membagikan username dan password admin kepada pihak manapun, termasuk sesama panitia yang tidak bertugas.
2. **Perlindungan Data Pribadi Anak**: NIK, alamat rumah, nomor telepon orang tua, dan dokumen kependudukan yang ada di sistem adalah data pribadi yang dilindungi undang-undang. Dilarang menyebarkan atau mengunggah data siswa ke grup obrolan umum (WhatsApp/Telegram).
3. **Ketertiban Akses Reopen**: Setiap pembukaan akses edit ulang wajib memiliki dasar permohonan yang valid dan alasan yang terdokumentasi. Dilarang membuka akses edit tanpa alasan kedinasan yang sah.
4. **Disiplin Keluar Sistem (Logout)**: Selalu klik tombol **"Keluar / Logout"** setiap kali meninggalkan komputer loket, terutama jika menggunakan komputer bersama di madrasah.
