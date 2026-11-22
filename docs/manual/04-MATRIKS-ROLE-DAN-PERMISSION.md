# MATRIKS ROLE & PERMISSION APLIKASI
## Sistem Informasi PPDB Online MIN 1 Kota Madiun (PPDB Minsatama)
### Tahun Ajaran 2026/2027

Dokumen ini memuat pemetaan komprehensif seluruh hak akses pengguna terhadap setiap modul dan fitur aplikasi berdasarkan kode sumber backend, controller, middleware, policy, serta tabel izin Spatie Laravel Permission aktual.

> **Catatan rekonsiliasi 20 November 2026:** Matriks historis di bawah dipertahankan sebagai snapshot. Untuk status terkini yang membedakan endpoint nyata dari permission yang baru didefinisikan, gunakan [`docs/blueprints/ADMIN-SUPERADMIN-MODULE-MATRIX.md`](../blueprints/ADMIN-SUPERADMIN-MODULE-MATRIX.md).

---

## 1. PENGERTIAN PRINSIP HAK AKSES

Untuk memahami matriks di bawah ini, berikut definisi istilah arsitektur keamanan yang diterapkan:

1. **Autentikasi (*Authentication*)**:
   Proses pembuktian identitas pengguna saat masuk sistem.
   - Pendaftar (Calon Siswa) membuktikan diri menggunakan **NIK (16 digit) dan Kata Sandi** (`account_type = applicant`).
   - Administrator membuktikan diri menggunakan **Alamat Email dan Kata Sandi** (`account_type = admin`).
2. **Otorisasi (*Authorization*)**:
   Proses penentuan apakah pengguna yang telah terotentikasi berhak menjalankan aksi atau mengakses URL tertentu.
3. **Peran (*Role*)**:
   Kelompok penamaan hak akses pada pustaka Spatie Permission. Di sistem Minsatama terdapat 2 role resmi pada guard `web`: `superadmin` dan `admin_ppdb`.
4. **Izin (*Permission*)**:
   Hak akses granular spesifik (misal: `reopen_registration`, `view_dashboard`).
5. **Kebijakan Pendaftar (*Applicant Policy / Ownership Isolation*)**:
   Mekanisme proteksi internal di mana akun pendaftar hanya diizinkan memanipulasi baris data miliknya sendiri (`$request->user()->applicant`). Upaya mengakses ID siswa lain otomatis menghasilkan respons `HTTP 403 Forbidden` atau `404 Not Found`.

---

## 2. MATRIKS HAK AKSES MODUL DAN FITUR

Keterangan Status:
- **`ALLOWED`**: Diizinkan penuh sesuai peran.
- **`DENIED`**: Ditolak keras oleh sistem (menghasilkan respons 403/404 atau redirect).
- **`CONDITIONAL`**: Diizinkan dengan syarat operasional tertentu (misal: hanya selama periode buka, atau hanya untuk berkas miliknya sendiri).
- **`NOT IMPLEMENTED`**: Fitur/antarmuka belum dibangun pada sistem saat ini (*Feature Gap*).
- **`NOT APPLICABLE`**: Tidak relevan untuk peran tersebut.

| Modul / Fitur Sistem | Tamu (Publik) | Applicant (Siswa/Wali) | Admin PPDB | Superadmin | Landasan Kode / Middleware / Policy |
|---|:---:|:---:|:---:|:---:|---|
| **Halaman Beranda (Home)** | `ALLOWED` | `ALLOWED` | `ALLOWED` | `ALLOWED` | Route `/` (`routes/web.php`) |
| **Registrasi Akun Baru** | `ALLOWED` | `DENIED` | `DENIED` | `DENIED` | Route `/daftar` (`guest` middleware) |
| **Verifikasi OTP & Buat Sandi** | `CONDITIONAL` | `DENIED` | `DENIED` | `DENIED` | Route `/aktivasi/*` (status `PENDING_ACTIVATION`) |
| **Masuk (Login) Siswa via NIK** | `ALLOWED` | `DENIED` | `DENIED` | `DENIED` | Route `/login` (`guest` middleware) |
| **Masuk (Login) Admin via Email**| `ALLOWED` | `DENIED` | `DENIED` | `DENIED` | Route `/admin/login` (`guest` middleware) |
| **Dasbor Utama Pendaftar** | `DENIED` | `ALLOWED` | `DENIED` | `DENIED` | Route `/pendaftar/dashboard` (`auth, active, applicant`) |
| **Dasbor Utama Admin** | `DENIED` | `DENIED` | `ALLOWED` | `ALLOWED` | Route `/admin/dashboard` (`auth, active, admin`) |
| **Isi / Edit Biodata Siswa** | `DENIED` | `CONDITIONAL` | `DENIED` | `DENIED` | Route `/pendaftar/biodata` (Hanya data miliknya; NIK read-only) |
| **Isi / Edit Alamat Domisili** | `DENIED` | `CONDITIONAL` | `DENIED` | `DENIED` | Route `/pendaftar/alamat` (Hanya data miliknya) |
| **Isi / Edit Data Orang Tua** | `DENIED` | `CONDITIONAL` | `DENIED` | `DENIED` | Route `/pendaftar/orang-tua` (Hanya data miliknya) |
| **Isi / Edit Riwayat TK/RA** | `DENIED` | `CONDITIONAL` | `DENIED` | `DENIED` | Route `/pendaftar/pendidikan` (Hanya data miliknya) |
| **Pilih Jalur Program** | `DENIED` | `CONDITIONAL` | `DENIED` | `DENIED` | Route `/pendaftar/program` (Status `DRAFT`) |
| **Unggah Berkas (KK/Foto)** | `DENIED` | `CONDITIONAL` | `DENIED` | `DENIED` | Route `/pendaftar/dokumen/*` (Maks 5MB, format PDF/JPG/PNG) |
| **Pratinjau / Unduh Berkas Siswa**| `DENIED` | `CONDITIONAL` | `NOT IMPLEMENTED` | `NOT IMPLEMENTED` | `DocumentController` (Pemilik berkas; admin belum ada UI) |
| **Tinjau Pendaftaran & Consent** | `DENIED` | `ALLOWED` | `DENIED` | `DENIED` | Route `/pendaftar/pendaftaran/tinjau` |
| **Submit Pendaftaran Final** | `DENIED` | `CONDITIONAL` | `DENIED` | `DENIED` | Route `/pendaftar/pendaftaran/kirim` (Validasi kelengkapan) |
| **Terbitkan PDF Draf** | `DENIED` | `CONDITIONAL` | `DENIED` | `DENIED` | Route `/pendaftar/pendaftaran/pdf/draft` (Status `DRAFT`) |
| **Terbitkan PDF Final Resmi** | `DENIED` | `CONDITIONAL` | `DENIED` | `DENIED` | Route `/pendaftar/pendaftaran/pdf/final` (Status `SUBMITTED`) |
| **Unduh Berkas PDF Registrasi**| `DENIED` | `CONDITIONAL` | `NOT IMPLEMENTED` | `NOT IMPLEMENTED` | `RegistrationPdfController` (Hanya pemilik akun terdaftar) |
| **Verifikasi Publik Kode QR** | `ALLOWED` | `ALLOWED` | `ALLOWED` | `ALLOWED` | Route `/verifikasi/bukti-pendaftaran/{token}` (Throttle 10/min) |
| **Lihat Daftar Pendaftar Terkirim**| `DENIED` | `DENIED` | `ALLOWED` | `ALLOWED` | Route `/admin/registrations` (`can:reopen_registration`) |
| **Buka Akses Edit Ulang (Reopen)**| `DENIED` | `DENIED` | `ALLOWED` | `ALLOWED` | Route `/admin/registrations/{id}/reopen` (POST) |
| **Tutup Akses Reopen Lebih Awal** | `DENIED` | `DENIED` | `ALLOWED` | `ALLOWED` | Route `/admin/registration-reopen-windows/{id}/close` (POST) |
| **Pencarian & Filter Pendaftar** | `DENIED` | `DENIED` | `NOT IMPLEMENTED` | `NOT IMPLEMENTED` | Belum ada controller/view pencarian di admin |
| **Verifikasi Berkas Digital** | `DENIED` | `NOT APPLICABLE`| `NOT IMPLEMENTED` | `NOT IMPLEMENTED` | Permission `verify_documents` ada di seeder, UI belum ada |
| **Ekspor Laporan (Excel/PDF)** | `DENIED` | `NOT APPLICABLE`| `NOT IMPLEMENTED` | `NOT IMPLEMENTED` | Permission `export_reports` ada di seeder, UI belum ada |
| **Kelola Periode & Program Baru** | `DENIED` | `NOT APPLICABLE`| `DENIED` | `ALLOWED` | Phase 4A route/controller/UI dengan `manage_admission_periods` dan `manage_programs` |
| **Kelola Persyaratan Dokumen** | `DENIED` | `NOT APPLICABLE`| `DENIED` | `ALLOWED` | Phase 4A route/controller/UI dengan `manage_document_requirements` |
| **Kelola Akun Admin Lain** | `DENIED` | `NOT APPLICABLE`| `DENIED` | `IMPLEMENTED` | Superadmin-only `/admin/admin-users`, lifecycle dan audit |
| **Lihat Log Audit Sistem** | `DENIED` | `NOT APPLICABLE`| `NOT IMPLEMENTED` | `NOT IMPLEMENTED` | Permission `view_activity_logs` ada, UI belum ada |

---

## 3. PENEGAKAN KEAMANAN TINGKAT BACKEND

1. **Isolasi Guard Web**:
   Seluruh hak akses Spatie terikat ketat pada guard `web`. Pendaftar (`account_type = applicant`) dilarang keras memiliki peran atau izin Spatie apapun. Jika ada upaya menyematkan role admin ke pendaftar, middleware `applicant` akan otomatis menolak akses.
2. **Pemberantasan IDOR (Insecure Direct Object References)**:
   Pada controller pendaftar, pencarian data profil tidak menggunakan ID dari URL masukan, melainkan selalu dievaluasi dari model relasi otentikasi:
   `$applicant = $request->user()->applicant;`
   Hal ini menjamin secara matematis bahwa pendaftar A tidak akan pernah bisa mengakses atau menimpa data pendaftar B.
