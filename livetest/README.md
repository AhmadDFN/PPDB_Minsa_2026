# PANDUAN PERSIAPAN LIVE TEST PPDB MIN 1 KOTA MADIUN
### Direktori Resmi Persiapan & Prosedur Pengujian Lapangan (Live Testing)

Dokumen ini disusun sebagai panduan menyeluruh pelaksanaan **Live Test** pada Sistem Informasi PPDB Online MIN 1 Kota Madiun (`PPDB Minsatama`), baik pada lingkungan lokal (`http://localhost:8000`) maupun server produksi (`https://ahmaddanyfn.biz.id/`).

---

## 1. Peta Dokumen Persiapan Live Test

| Dokumen | Isi & Fokus Utama |
|---|---|
| **[README.md](./README.md)** | Ikhtisar sistem, strategi pengujian, penanganan kendala OTP Fonnte, dan peta rute live test. |
| **[01-SKENARIO-PER-ROLE.md](./01-SKENARIO-PER-ROLE.md)** | Panduan skenario bertindak sebagai: Superadmin, Admin PPDB (Verifikator), Calon Siswa (DRAFT), Calon Siswa (SUBMITTED), dan Calon Siswa (VERIFIED). |
| **[02-KREDENSIAL-DAN-AKUN-UJI.md](./02-KREDENSIAL-DAN-AKUN-UJI.md)** | Daftar lengkap akun uji deterministik (NIK, Email, Password, Peran), URL akses login, dan perintah seeder database. |
| **[03-PANDUAN-SOLUSI-FONNTE-OTP.md](./03-PANDUAN-SOLUSI-FONNTE-OTP.md)** | Penjelasan teknis kendala Fonnte WhatsApp (AI tidak menerima WA) beserta 3 strategi solusi: Akun Pre-Seeded, Kolaborasi Human-in-the-Loop, dan Inspeksi Database. |
| **[04-CHECKLIST-FITUR-DAN-PENGUJIAN.md](./04-CHECKLIST-FITUR-DAN-PENGUJIAN.md)** | Lembar checklist pengujian step-by-step untuk seluruh fitur (16 modul fungsional & verifikasi UI responsif). |

---

Panduan lima akun pendaftar baru dengan draft kosong: [08-LOGIN-LIMA-DUMMY.md](./08-LOGIN-LIMA-DUMMY.md). Akun dan kredensialnya hanya tersedia pada lingkungan tempat seeder dijalankan.

## 2. Memahami Kendala OTP WhatsApp Fonnte & Solusinya

### Skema Live Test Default: Login Akun Dummy

Untuk pengujian fitur aplikasi, alur default **tidak melewati pendaftaran baru dan OTP WhatsApp**. Gunakan akun dummy yang dibuat oleh `LiveTestSeeder`, lalu langsung login sesuai peran.

Jalankan hanya pada database lokal/test atau environment yang memang disiapkan untuk live test:

```bash
php artisan migrate
php artisan db:seed --class=LiveTestSeeder
php artisan optimize:clear
```

Urutan pengujian utama:

1. Login pendaftar dummy berstatus `DRAFT` untuk menguji formulir, dokumen, PDF draft, dan Final Submit.
2. Login `admin_ppdb` dummy untuk menguji verifikasi dokumen dan penerbitan nomor urut.
3. Login pendaftar dummy berstatus `VERIFIED` untuk menguji PDF final dan QR publik.
4. Login superadmin dan pewawancara dummy untuk menguji master wawancara, sequential gate, penguncian nilai, rekap, audit, dan CSV.

Pengujian pendaftaran baru, OTP, dan pengiriman password sementara melalui WhatsApp tetap tersedia sebagai **skenario manual terpisah** dan tidak menjadi prasyarat live test fitur lainnya.

> [!IMPORTANT]
> **Mengapa AI / Browser Otomatis Tidak Bisa Mendaftar Akun Baru Secara Mandiri di `/daftar`?**  
> Pada pendaftaran akun baru, sistem PPDB Minsatama mengirimkan **kode OTP 6-digit** ke nomor WhatsApp orang tua melalui **Fonnte API**. Kemudian, setelah OTP diverifikasi, sistem mengirimkan **kata sandi sementara** via WhatsApp.  
> Karena AI / agen otomatis tidak memiliki perangkat fisik ponsel/kartu SIM untuk membaca notifikasi WhatsApp secara langsung, **proses pendaftaran baru tidak dapat dilakukan secara mandiri oleh AI tanpa bantuan manusia**.

### 3 Strategi Solusi yang Disediakan:

1. **Strategi A: Pengujian Menggunakan Akun Dummy (Rekomendasi Utama)**
   Sistem telah menyediakan seeder khusus `LiveTestSeeder` (`php artisan db:seed --class=LiveTestSeeder`) yang langsung menyediakan akun-akun pendaftar dan admin siap uji dengan NIK dan Password yang sudah diketahui secara pasti. **Tidak memerlukan pengiriman OTP WhatsApp sama sekali.**

2. **Strategi B: Kolaborasi Human-in-the-Loop (Pengujian Pendaftaran Riil)**  
   Jika Pemilik Proyek ingin menguji formulir `/daftar` secara live hingga pesan WhatsApp masuk:
   - Masukkan nomor WhatsApp riil milik Anda.
   - Pesan OTP Fonnte masuk ke ponsel fisik Anda dalam 2–5 detik.
   - Anda memberikan 6 digit kode OTP tersebut di obrolan chat.
   - AI memasukkan kode di `/aktivasi`.
   - Kata sandi sementara masuk ke WhatsApp Anda, lalu diberikan ke AI untuk masuk ke `/login`.

3. **Strategi C: Verifikasi Log Internal Server**  
   Pada lingkungan pengembangan, pengiriman pesan Fonnte dicatat ke log sistem/database sehingga status pesan tetap dapat diaudit.

---

## 3. Matriks Peran & Rute Live Test

| Peran Live Test | Login Melalui | Kredensial Utama | Halaman Utama Setelah Masuk | Fokus Pengujian |
|---|---|---|---|---|
| **Super Administrator** | `/admin/login` | Email: `admin@minsatama.sch.id`<br>Pass: `SuperAdminM1Madiun2026!` | `/admin/dashboard` | Master Jadwal Batch Wawancara, Master Tahap & Poin Penilaian, Audit Log, Pengaturan Madrasah |
| **Admin PPDB (Verifikator)** | `/admin/login` | Email: `admin1@minsatama.sch.id`<br>Pass: `Minsatama2026!` | `/admin/dashboard` | Metrik Dasbor Dinamis, Filter Pendaftar, Verifikasi Dokumen, Sahkan Berkas & Terbitkan Nomor |
| **Calon Siswa (DRAFT)** | `/login` | NIK: `3577022003990001`<br>Pass: `Pendaftar2026!` | `/pendaftar/dashboard` | Pengisian Formulir 5 Langkah (Biodata, Alamat, Orang Tua, Pendidikan, Program), Unggah Berkas, Draf PDF |
| **Calon Siswa (SUBMITTED)** | `/login` | NIK: `3577022003990002`<br>Pass: `Pendaftar2026!` | `/pendaftar/dashboard` | Tinjauan Status Terkirim Resmi, Formulir Terkunci, Banner Status Menunggu Pengesahan Berkas |
| **Calon Siswa (VERIFIED)** | `/login` | NIK: `3577022003990003`<br>Pass: `Pendaftar2026!` | `/pendaftar/dashboard` | Nomor Pendaftaran Resmi `270001`, Jadwal Wawancara, Unduh PDF Bukti Final, Verifikasi QR Publik |
| **Pewawancara (Pool Penguji)** | `/admin/login` | Email: `penguji1a@minsatama.sch.id`<br>Pass: `Minsatama2026!` | `/admin` / `/wawancara` | Isolasi hak akses (blind grading), verifikasi sequential gate antar-tahap wawancara |

---

## 4. Cara Menyiapkan Database untuk Live Test

Jalankan perintah berikut di terminal:

```bash
# Terapkan seluruh migrasi tabel terbaru
php artisan migrate

# Jalankan seeder akun uji dan master wawancara
php artisan db:seed --class=LiveTestSeeder

# Bersihkan cache aplikasi agar data langsung mutakhir
php artisan optimize:clear
```
