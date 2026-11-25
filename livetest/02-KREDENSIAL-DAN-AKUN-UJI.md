# KREDENSIAL & AKUN UJI LIVE TEST (KREDENSIAL-DAN-AKUN-UJI.md)
### Referensi Kredensial Resmi untuk Pengujian Lokal & Produksi

Gunakan daftar akun di bawah ini untuk melakukan live testing tanpa perlu mendaftar dari awal dan tanpa perlu verifikasi WhatsApp OTP.

---

## 1. Akun Calon Siswa / Pendaftar (Login via `/login`)

Login pendaftar menggunakan **NIK Calon Siswa (16 Digit)** sebagai Username dan **Password Akun**:

| No | Nama Calon Siswa | NIK (Username) | Password | No. WhatsApp Terdaftar | Status Registrasi | Peran / Fokus Pengujian |
|:--:|---|---|---|---|:---:|---|
| **1** | **Budi Santoso** | `3577022003990001` | `Pendaftar2026!` | `081234560001` | **`DRAFT`** | Uji pengisian formulir 5 langkah, ganti program, unggah berkas, cetak PDF Draf, dan submit final. |
| **2** | **Siti Rahmawati** | `3577022003990002` | `Pendaftar2026!` | `081234560002` | **`SUBMITTED`** | Uji pendaftar terkirim lengkap yang siap disahkan oleh Admin PPDB di panel admin. |
| **3** | **Ahmad Dany Pratama** | `3577022003990003` | `Pendaftar2026!` | `081234560003` | **`VERIFIED`**<br>(No: `270001`) | Uji cetak PDF Bukti Pendaftaran Final ber-QR, lihat jadwal wawancara, dan verifikasi QR publik. |

> [!NOTE]
> Semua akun calon siswa di atas diatur dengan status `must_change_password = false`. Saat login pertama kali, pendaftar **langsung masuk ke dasbor**, tanpa dipaksa mengubah kata sandi.

---

## 2. Akun Panitia & Penguji (Login via `/admin/login`)

Login panitia madrasah menggunakan **Alamat Email Resmi** dan **Password Akun**:

| No | Nama Akun | Alamat Email | Password | Role Spatie | Hak Akses Utama |
|:--:|---|---|---|---|---|
| **1** | **Super Administrator PPDB** | `admin@minsatama.sch.id` | `SuperAdminM1Madiun2026!` | `superadmin` | Akses mutlak: Master Jadwal, Master Tahap/Poin, Akun Panitia, Audit Log, Komunikasi Fonnte. |
| **2** | **Panitia Verifikator 1** | `admin1@minsatama.sch.id` | `Minsatama2026!` | `admin_ppdb` | Dasbor Dinamis, Filter Pendaftar, Verifikasi Dokumen, Sahkan Berkas & Terbitkan Nomor. |
| **3** | **Panitia Verifikator 2** | `admin2@minsatama.sch.id` | `Minsatama2026!` | `admin_ppdb` | Backup verifikator berkas & ekspor laporan pendaftar. |
| **4** | **Panitia Verifikator 3** | `admin3@minsatama.sch.id` | `Minsatama2026!` | `admin_ppdb` | Backup verifikator berkas & buka ulang akses pendaftar (*Reopen*). |
| **5** | **Pewawancara BTQ 1** | `penguji1a@minsatama.sch.id` | `Minsatama2026!` | `interviewer_stage_1` | Khusus penilaian **Tahap 1 (Baca Tulis Al-Qur'an / Iqro')**. Dilarang buka rekap nilai. |
| **6** | **Pewawancara Calistung 1** | `penguji2a@minsatama.sch.id` | `Minsatama2026!` | `interviewer_stage_2` | Khusus penilaian **Tahap 2 (Calistung Sederhana)**. Wajib tunggu Tahap 1 selesai. |
| **7** | **Pewawancara Khusus 1** | `penguji3a@minsatama.sch.id` | `Minsatama2026!` | `interviewer_stage_3` | Khusus penilaian **Tahap 3 (Program Unggulan)**. Wajib tunggu Tahap 1 & 2 selesai. |

---

## 3. URL Endpoint Penting untuk Pengujian

### A. Portal Publik & Calon Siswa
- Halaman Utama Madrasah: `/`
- Pendaftaran Akun Baru (Memerlukan OTP WA): `/daftar`
- Verifikasi OTP WhatsApp: `/aktivasi`
- Masuk Akun Calon Siswa: `/login`
- Lupa Kata Sandi Siswa: `/lupa-password`
- Dasbor Pendaftar: `/pendaftar/dashboard`
- Langkah 1 (Biodata): `/pendaftar/biodata`
- Langkah 2 (Alamat): `/pendaftar/alamat`
- Langkah 3 (Orang Tua): `/pendaftar/orang-tua`
- Langkah 4 (Pendidikan Asal): `/pendaftar/pendidikan`
- Langkah 5 (Pilihan Program): `/pendaftar/program`
- Unggah Dokumen Berkas: `/pendaftar/dokumen`
- Tinjau & Final Submit: `/pendaftar/pendaftaran/tinjau`
- Ganti Kata Sandi (Opsional): `/pendaftar/ganti-password`
- Halaman Verifikasi QR Publik: `/verifikasi/bukti-pendaftaran/{token}`

### B. Portal Administrasi Panitia
- Masuk Akun Panitia/Admin: `/admin/login`
- Dasbor Utama Admin PPDB: `/admin/dashboard`
- Data Pendaftar (Pencarian & Filter): `/admin/pendaftar`
- Detail Pendaftar & Sahkan Berkas: `/admin/pendaftar/{id}`
- Panel Verifikasi Dokumen: `/admin/document-verification`
- Kelola Master Jadwal Wawancara: `/admin/interview-batches` *(khusus superadmin)*
- Kelola Master Tahap & Poin: `/admin/interview-stages` *(khusus superadmin)*
- Audit Log Aktivitas: `/admin/audit-logs` *(khusus superadmin)*

---

## 4. Reset Kredensial Uji ke Kondisi Semula

Jika data pendaftar uji telah diubah-ubah selama sesi live test dan ingin dikembalikan ke kondisi awal, jalankan perintah satu baris berikut di terminal:

```bash
php artisan db:seed --class=LiveTestSeeder
```
Perintah ini akan secara aman mengembalikan akun `3577022003990001`, `3577022003990002`, dan `3577022003990003` ke status semula beserta password-nya masing-masing.
