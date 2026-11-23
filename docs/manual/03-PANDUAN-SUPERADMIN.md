# PANDUAN PENGELOLA — SUPER ADMINISTRATOR (SUPERADMIN)
## Sistem Informasi PPDB Online MIN 1 Kota Madiun (PPDB Minsatama)
### Tahun Ajaran 2026/2027

Dokumen panduan ini ditujukan bagi **Super Administrator** (`superadmin`), yaitu penanggung jawab teknis tertinggi, koordinator IT madrasah, atau kepala sistem informasi PPDB Minsatama.

> **Status Phase 4B:** Manajemen akun admin dan permission role tersedia pada branch pengembangan; commit, push, deployment, dan live verification tetap mengikuti checkpoint fase.

---

## 1. DEFINISI & KEDUDUKAN PERAN SUPERADMIN

Peran `superadmin` adalah peran tingkat tertinggi (*root/highest privilege*) pada tabel `roles` Spatie Laravel Permission yang memiliki **seluruh 10 izin (*permissions*) administratif**:

| No | Nama Izin (*Permission Name*) | Lingkup Otoritas | Ketersediaan Antarmuka Web |
|:---:|---|---|:---:|
| 1 | `view_dashboard` | Mengakses dasbor administrator | **TERSEDIA** (`/admin/dashboard`) |
| 2 | `view_applicants` | Melihat daftar pendaftaran terkirim | **TERSEDIA** (`/admin/registrations`) |
| 3 | `reopen_registration` | Membuka & menutup akses edit ulang pendaftaran | **TERSEDIA** (`/admin/registrations/.../reopen`) |
| 4 | `verify_documents` | Memvalidasi berkas dokumen pendaftar | *Belum ada UI (Manual Loket)* |
| 5 | `export_reports` | Mengekspor rekapitulasi redacted CSV dari pendaftaran `SUBMITTED` | `/admin/reports` dan `/admin/reports/export` |
| 6 | `manage_admission_periods` | Menambah/mengubah periode tahun ajaran PPDB | **TERSEDIA** (`/admin/admission-periods`) |
| 7 | `manage_programs` | Menambah/mengubah jalur program (Reguler/Unggulan) | **TERSEDIA** (`/admin/programs`) |
| 8 | `manage_document_requirements` | Mengatur daftar berkas persyaratan wajib | **TERSEDIA** (`/admin/document-requirements`) |
| 9 | `manage_admin_users` | Membuat dan mengelola akun Admin PPDB | **TERSEDIA** (`/admin/admin-users`) |
| 10 | `view_activity_logs` | Memantau seluruh rekaman audit sistem | *Belum ada UI (Via DB Log)* |

---

## 2. PERBEDAAN HAK AKSES: SUPERADMIN VS ADMIN PPDB

| Aspek Operasional | Admin PPDB (`admin_ppdb`) | Super Administrator (`superadmin`) |
|---|---|---|
| **Fokus Tugas** | Layanan harian loket pendaftaran & permohonan edit data siswa. | Pengawasan teknis sistem, keamanan kunci, dan konfigurasi master data. |
| **Akses Dasbor & Reopen** | Ya, memiliki izin operasional penuh. | Ya, memiliki hak akses penuh. |
| **Kewenangan Manajemen Pengguna** | Tidak memiliki izin mengelola akun admin lain. | Memiliki izin penuh `manage_admin_users`. |
| **Kewenangan Konfigurasi Periode**| Tidak memiliki izin membuka/menutup periode pendaftaran. | Memiliki izin penuh `manage_admission_periods`. |
| **Akses Konfigurasi Server (`.env`)**| Dilarang keras mengakses berkas konfigurasi server. | Bertanggung jawab atas pengelolaan berkas `.env` dan kunci kriptografi. |

---

## 3. PANDUAN PENGELOLAAN MASTER DATA TANPA ANTARMUKA WEB (SAAT INI)

Karena antarmuka manajemen pengguna dan periode saat ini berstatus *Feature Gap* (belum memiliki tampilan tombol web), Superadmin menjalankan pengelolaan master data melalui mekanisme basis data atau perintah CLI yang aman:

### 3.1 Penambahan Akun Admin PPDB Baru
Untuk menambahkan staf baru sebagai panitia Admin PPDB:
Gunakan perintah aman Laravel Tinker atau Seeder termonitor:
```bash
php artisan tinker
```
Lalu jalankan skrip pembuatan akun:
```php
$user = App\Models\User::create([
    'name' => 'Nama Panitia PPDB',
    'email' => 'panitia1@minsatama.sch.id',
    'password' => Hash::make('KataSandiKuatAcak2026!'),
    'account_type' => App\Enums\AccountType::ADMIN,
    'account_status' => App\Enums\AccountStatus::ACTIVE,
    'email_verified_at' => now(),
    'phone' => '081234567890',
]);

$user->assignRole('admin_ppdb');
```

### 3.2 Konfigurasi Periode Pendaftaran Baru
Periode pendaftaran dikelola pada tabel `admission_periods`:
```sql
INSERT INTO `admission_periods` (
    `academic_year`, `name`, `start_date`, `end_date`, `age_reference_date`, `is_active`, `created_at`, `updated_at`
) VALUES (
    '2026/2027', 'PPDB MINSATAMA 2026/2027', '2026-11-01 00:00:00', '2026-12-31 23:59:59', '2026-07-01', 1, NOW(), NOW()
);
```
*Catatan Kritis*: Kolom `age_reference_date` adalah tanggal acuan resmi untuk perhitungan umur siswa saat runtime (standar madrasah: 1 Juli tahun ajaran berjalan).

---

## 4. TANGGUNG JAWAB KEAMANAN & ISOLASI KUNCI RAHASIA

Superadmin adalah pemegang tanggung jawab mutlak atas keamanan infrastruktur:

1. **Penggantian Kredensial Bawaan Seeder**:
   Akun bawaan seeder `admin@minsatama.sch.id` wajib segera diganti kata sandinya sebelum sistem dipublikasikan ke orang tua siswa.
2. **Perlindungan Kunci Kriptografi Mandiri**:
   - `APP_KEY`: Kunci enkripsi sesi dan token.
   - `NIK_HMAC_KEY`: Kunci hash satu arah untuk pencarian NIK siswa.
   - `OTP_HMAC_KEY`: Kunci pengaman token verifikasi OTP WhatsApp.
   > [!CAUTION]
   > Dilarang keras mengubah `APP_KEY` atau `NIK_HMAC_KEY` setelah data pendaftaran mulai masuk! Perubahan kunci akan menyebabkan seluruh data NIK terenkripsi dan hash pencarian tidak dapat diakses kembali.
3. **Pencadangan Basis Data Rutin**:
   Superadmin wajib memastikan pencadangan harian basis data berjalan secara otomatis dan berkas dump SQL tersimpan di lokasi penyimpanan aman terpisah.
