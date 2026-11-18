# LAPORAN FASE 3 — AUTENTIKASI, AKTIVASI AKUN & OTORISASI
## Sistem Informasi Penerimaan Peserta Didik Baru (PPDB) MIN 1 Kota Madiun (Minsatama)

**Tanggal Penyelesaian**: 15 November 2026  
**Status**: SELESAI & TERVERIFIKASI PENUH  
**Commit Git Terkait**: `f36f057`  
**Framework & Lingkungan**: Laravel 13.42.0, PHP 8.3.31, Spatie Laravel Permission 8.5.0, Tailwind CSS v4, SQLite in-memory (Testing Suite), Target Produksi MySQL (InnoDB).

---

## 1. RINGKASAN EKSEKUTIF

Fase 3 berfokus pada pembangunan arsitektur keamanan, alur autentikasi ganda (calon siswa berbasis NIK dan panitia/admin berbasis Email), verifikasi nomor WhatsApp menggunakan One-Time Password (OTP) 6 digit yang diamankan dengan HMAC-SHA256, pembuatan kata sandi bertingkat, pemulihan akun tanpa pembocoran identitas (anti-enumerasi), serta proteksi otorisasi dan kontrol sesi.

Seluruh 91 pengujian otomatis (*unit & feature tests*) dengan 302 asersi dinyatakan **LULUS (100% PASSED)**, dengan kepatuhan format kode 100% via **Laravel Pint**.

---

## 2. ARSITEKTUR AUTENTIKASI & OTORISASI

```
+----------------------------------------------------------------------------------------------------+
|                                    SATU TABEL USERS & GUARD WEB                                     |
+----------------------------------------------------------------------------------------------------+
                                      |
         +----------------------------+----------------------------+
         |                                                         |
         v                                                         v
 [AKUN CALON SISWA]                                       [AKUN ADMINISTRATOR]
 - Login: NIK (16 digit) + Password                       - Login: Email + Password
 - account_type: 'applicant'                               - account_type: 'admin'
 - account_status: PENDING_ACTIVATION -> ACTIVE           - account_status: ACTIVE
 - NIK dienkripsi (AES-256-CBC) via Laravel Crypt         - Terikat Spatie Permission (superadmin, admin_ppdb)
 - Lookup deterministik HMAC-SHA256 (nik_lookup_hash)     - Dilarang login via /login calon siswa
 - Verifikasi kepemilikan nomor telepon via OTP           - Dilarang mengakses dasbor pendaftar
 - Dilarang mengakses rute /admin/*
```

### Karakteristik Desain Inti:
1. **Satu Tabel `users` & Satu Guard `web`**: Sesuai instruksi dan panduan resmi Laravel, sistem tidak menggunakan multi-guard yang rentan terhadap kebocoran sesi, melainkan membedakan peran melalui kolom `account_type` dan `account_status`.
2. **Isolasi Peran Ketat**:
   - Calon siswa (`account_type = applicant`) hanya dapat login melalui `/login` dan hanya diizinkan mengakses `/pendaftar/*`.
   - Administrator (`account_type = admin`) hanya dapat login melalui `/admin/login` dan harus memiliki peran Spatie (`superadmin` atau `admin_ppdb`).
3. **Status Akun (`AccountStatus`)**:
   - `PENDING_ACTIVATION`: Akun telah didaftarkan namun belum menyelesaikan verifikasi OTP & pembuatan kata sandi. Ditolak login normal dan diarahkan ke alur aktivasi.
   - `ACTIVE`: Akun yang sah dan diizinkan mengakses sistem.
   - `SUSPENDED`: Akun dinonaktifkan oleh panitia. Upaya login langsung ditolak dengan pesan informatif, dan sesi aktif yang disuspensi langsung ditendang (*force logout*).

---

## 3. ALUR PENDAFTARAN & AKTIVASI CALON SISWA

Implementasi alur pendaftaran mengikuti 5 langkah berurutan (*atomic multi-step workflow*):

```
[Form Pendaftaran /daftar]
       |
       v (Validasi NIK 16 digit, Nama, JK, Tempat/Tgl Lahir, No. WA, Consent)
[DB Transaction: User(PENDING) + Applicant + Issue OTP]
       |
       v (Redirect dengan session 'activation_user_id')
[Verifikasi OTP /aktivasi]
       |
       v (Validasi 6 digit, HMAC Hash check, DB Lock, attempts <= 5, expire <= 5m)
[Stempel phone_verified_at & verified_at pada OTP]
       |
       v (Advance session 'activation_verified_user_id')
[Buat Kata Sandi /aktivasi/buat-password]
       |
       v (Validasi min 8 char, confirmed, Hash::make)
[Update User: Password baru & Status ACTIVE] -> Redirect /login (Siap Masuk)
```

---

## 4. KEAMANAN KRIPTOGRAFI OTP (HMAC-SHA256 BINDING)

Sesuai instruksi penting pada Bagian 4 *Master Prompt*, sistem **TIDAK MENGGUNAKAN** plain SHA-256 (yang memiliki ruang pencarian sempit hanya 1.000.000 kombinasi) untuk menyimpan kode OTP.

### Implementasi:
- **Kunci Rahasia Terpisah**: `OTP_HMAC_KEY` dikonfigurasi secara mandiri pada `.env`, terpisah dan wajib berbeda dari `APP_KEY` dan `NIK_HMAC_KEY`.
- **Fail Closed**: Jika `OTP_HMAC_KEY` tidak diatur, bernilai kosong, atau sama dengan `APP_KEY`/`NIK_HMAC_KEY`, `OtpService::computeHash()` langsung melemparkan `RuntimeException` dan menolak memproses.
- **Kontekstual Binding**: Hash OTP mengikat empat dimensi parameter:
  $$\text{Data} = \text{user\_id} \parallel \text{purpose} \parallel \text{plain\_otp} \parallel \text{normalized\_phone}$$
  $$\text{OTP\_Hash} = \text{HMAC-SHA256}(\text{Data}, \text{OTP\_HMAC\_KEY})$$
- **Verifikasi Constant-Time**: Evaluasi kode OTP menggunakan `hash_equals()` untuk mencegah eksploitasi *timing-attack*.
- **Database Row Lock**: Verifikasi OTP dibungkus dalam transaksi database dengan `lockForUpdate()` pada model `UserOtpToken` untuk mencegah *race condition* dan penggunaan berulang secara bersamaan (*concurrent replay attack*).
- **Pembatasan Percobaan & Kedaluwarsa**: Maksimal 5 kali percobaan gagal sebelum token dinonaktifkan permanen; masa kedaluwarsa tepat 5 menit.
- **Penerbitan Ulang (*Resend*)**: Meminta kirim ulang OTP secara otomatis mengedaluwarsa seluruh token aktivasi sebelumnya yang belum dikonsumsi.

### Abstraksi Pengiriman OTP (`OtpDeliveryInterface`):
- `LogOtpDelivery`: Transportasi pengembangan lokal. Pada lingkungan non-produksi, log mencatat kode verifikasi untuk memudahkan pengujian manual. Pada lingkungan produksi (`production`), kode OTP **TIDAK PERNAH DITULISKAN KE LOG** (hanya ID dan nomor telepon termasker).
- `ArrayOtpDelivery`: Transportasi *in-memory* berkecepatan tinggi khusus pengujian otomatis (`tests`), menyediakan inspeksi instan kode OTP tanpa ketergantungan pihak ketiga.
- `NullOtpDelivery`: Transportasi pasif tanpa efek samping.

---

## 5. STRUKTUR BERKAS YANG DIKEMBANGKAN PADA FASE 3

### A. Services & Contracts (`app/Services/` & `app/Contracts/`)
| Berkas | Deskripsi & Tanggung Jawab |
|---|---|
| `app/Contracts/OtpDeliveryInterface.php` | Antarmuka kontrak pengiriman OTP |
| `app/Services/Authentication/OtpDeliveryService.php` | Manajer resolver transport pengiriman OTP |
| `app/Services/Authentication/Delivery/LogOtpDelivery.php` | Driver log aman (rahasia di production) |
| `app/Services/Authentication/Delivery/ArrayOtpDelivery.php` | Driver in-memory untuk automated testing |
| `app/Services/Authentication/Delivery/NullOtpDelivery.php` | Driver no-op |
| `app/Services/Authentication/OtpService.php` | Layanan inti komputasi hash HMAC, penerbitan, penguncian, dan validasi OTP |
| `app/Services/Authentication/NikAuthService.php` | Layanan autentikasi NIK, pencegahan enumerasi, throttling rate limiter |
| `app/Services/Authentication/AdminAuthenticationService.php` | Layanan autentikasi administrator & verifikasi peran Spatie |

### B. Actions (`app/Actions/Authentication/`)
| Berkas | Deskripsi & Tanggung Jawab |
|---|---|
| `RegisterApplicantAction.php` | Transaksi atomik pembuatan `User`, profil `Applicant`, dan penerbitan OTP |
| `VerifyOtpAction.php` | Validasi OTP kontekstual dan penyematan stempel `phone_verified_at` |
| `CompleteApplicantActivationAction.php` | Penyimpanan hash kata sandi dan transisi status akun ke `ACTIVE` |
| `ResetPasswordAction.php` | Pembaruan kata sandi pemulihan dan invalidasi `remember_token` |

### C. Form Requests (`app/Http/Requests/Auth/`)
| Berkas | Validasi & Pesan Bahasa Indonesia |
|---|---|
| `ApplicantRegisterRequest.php` | NIK 16 digit numerik, nama lengkap, JK (L/P), TTL, format WhatsApp Indonesia, consent |
| `ApplicantLoginRequest.php` | NIK 16 digit numerik dan kata sandi |
| `AdminLoginRequest.php` | Alamat email valid dan kata sandi |
| `VerifyOtpRequest.php` | 6 digit numerik kode OTP |
| `SendOtpRequest.php` | Permintaan kirim ulang OTP |
| `CompleteActivationRequest.php` | Kata sandi minimal 8 karakter dan konfirmasi |
| `ForgotPasswordRequest.php` | NIK 16 digit calon siswa |
| `ResetPasswordRequest.php` | Kata sandi baru minimal 8 karakter dan konfirmasi |

### D. Middleware Otorisasi (`app/Http/Middleware/`)
| Berkas & Alias | Fungsi Proteksi |
|---|---|
| `EnsureAccountIsActive.php` (`active`) | Memastikan pengguna terautentikasi berstatus `ACTIVE`. Jika `SUSPENDED` atau `PENDING_ACTIVATION`, sesi langsung diterminasi dan diarahkan ke login dengan notifikasi. |
| `EnsureApplicant.php` (`applicant`) | Membatasi akses khusus pengguna dengan `account_type = applicant`. Mencegah akses ke area admin. |
| `EnsureAdmin.php` (`admin`) | Membatasi akses khusus administrator (`account_type = admin`) dengan peran Spatie `superadmin` atau `admin_ppdb`. Mencegah calon siswa masuk. |

### E. Controllers (`app/Http/Controllers/`)
| Controller | Rute Utama yang Ditangani |
|---|---|
| `Auth\ApplicantRegisterController.php` | `GET /daftar`, `POST /daftar` |
| `Auth\ApplicantActivationController.php` | `GET /aktivasi`, `POST /aktivasi/verifikasi`, `POST /aktivasi/kirim-ulang`, `GET /aktivasi/buat-password`, `POST /aktivasi/buat-password` |
| `Auth\ApplicantLoginController.php` | `GET /login`, `POST /login` |
| `Auth\ApplicantPasswordResetController.php` | `GET /lupa-password`, `POST /lupa-password`, `GET /lupa-password/verifikasi`, `POST /lupa-password/verifikasi`, `POST /lupa-password/kirim-ulang`, `GET /lupa-password/reset`, `POST /lupa-password/reset` |
| `Auth\AdminLoginController.php` | `GET /admin/login`, `POST /admin/login` |
| `Auth\LogoutController.php` | `POST /logout` (Calon Siswa), `POST /admin/logout` (Administrator) |
| `Applicant\DashboardController.php` | `GET /pendaftar/dashboard` (Dasbor placeholder pendaftar terverifikasi) |

### F. Blade Views (`resources/views/`)
Semua antarmuka dibuat menggunakan Tailwind CSS v4 murni, responsif (*mobile-first*), tanpa Bootstrap, dan tanpa dependensi SPA framework:
1. `resources/views/auth/applicant-register.blade.php`: Formulir pendaftaran awal ramah wali murid dengan panduan dokumen KK/Akta.
2. `resources/views/auth/applicant-verify-otp.blade.php`: Input 6 digit OTP berjarak renggang (*spaced mono*), nomor WhatsApp termasker, dan tombol kirim ulang.
3. `resources/views/auth/applicant-set-password.blade.php`: Formulir pembuatan kata sandi akun dengan kontrol lihat/sembunyikan (*eye icon toggle*).
4. `resources/views/auth/applicant-login.blade.php`: Halaman masuk calon siswa berbasis NIK dan kata sandi, opsi *ingat saya*, dan tautan lupa kata sandi.
5. `resources/views/auth/admin-login.blade.php`: Portal masuk khusus panitia PPDB & admin bernuansa gelap-elegan dengan aksen emas Minsatama.
6. `resources/views/auth/forgot-password.blade.php`: Formulir lupa kata sandi berbasis NIK dengan kotak petunjuk bagi wali murid yang kehilangan akses nomor telepon.
7. `resources/views/auth/verify-reset-otp.blade.php`: Verifikasi OTP pemulihan akun.
8. `resources/views/auth/reset-password.blade.php`: Formulir penyetelan kata sandi baru.
9. `resources/views/applicant/dashboard.blade.php`: Dasbor ringkasan profil terverifikasi, NIK termasker (`3573********0001`), nomor telepon, status aktif, dan tombol keluar.

---

## 6. TABEL RUTE APLIKASI (FASE 3)

| Verb HTTP | URI Path | Nama Rute | Middleware | Pengendali |
|---|---|---|---|---|
| `GET` | `/` | `home` | `web` | Closure (Landing Page) |
| `GET` | `/daftar` | `register` | `web`, `guest` | `ApplicantRegisterController@create` |
| `POST` | `/daftar` | `register.store` | `web`, `guest` | `ApplicantRegisterController@store` |
| `GET` | `/aktivasi` | `activation.form` | `web`, `guest` | `ApplicantActivationController@showOtpForm` |
| `POST` | `/aktivasi/verifikasi` | `activation.verify` | `web`, `guest` | `ApplicantActivationController@verifyOtp` |
| `POST` | `/aktivasi/kirim-ulang` | `activation.resend` | `web`, `guest` | `ApplicantActivationController@resendOtp` |
| `GET` | `/aktivasi/buat-password` | `activation.password.form` | `web`, `guest` | `ApplicantActivationController@showPasswordForm` |
| `POST` | `/aktivasi/buat-password` | `activation.password.store` | `web`, `guest` | `ApplicantActivationController@savePassword` |
| `GET` | `/login` | `login` | `web`, `guest` | `ApplicantLoginController@create` |
| `POST` | `/login` | `login.store` | `web`, `guest` | `ApplicantLoginController@store` |
| `POST` | `/logout` | `logout` | `web`, `auth` | `LogoutController@logout` |
| `GET` | `/lupa-password` | `password.request` | `web`, `guest` | `ApplicantPasswordResetController@showForgotPasswordForm` |
| `POST` | `/lupa-password` | `password.send` | `web`, `guest` | `ApplicantPasswordResetController@sendResetOtp` |
| `GET` | `/lupa-password/verifikasi` | `password.reset.verify.form` | `web`, `guest` | `ApplicantPasswordResetController@showVerifyOtpForm` |
| `POST` | `/lupa-password/verifikasi` | `password.reset.verify` | `web`, `guest` | `ApplicantPasswordResetController@verifyResetOtp` |
| `POST` | `/lupa-password/kirim-ulang` | `password.reset.resend` | `web`, `guest` | `ApplicantPasswordResetController@resendResetOtp` |
| `GET` | `/lupa-password/reset` | `password.reset.form` | `web`, `guest` | `ApplicantPasswordResetController@showResetPasswordForm` |
| `POST` | `/lupa-password/reset` | `password.reset.store` | `web`, `guest` | `ApplicantPasswordResetController@resetPassword` |
| `GET` | `/admin/login` | `admin.login` | `web`, `guest` | `AdminLoginController@create` |
| `POST` | `/admin/login` | `admin.login.store` | `web`, `guest` | `AdminLoginController@store` |
| `POST` | `/admin/logout` | `admin.logout` | `web`, `auth` | `LogoutController@adminLogout` |
| `GET` | `/dashboard` | `dashboard` | `web`, `auth`, `active` | Closure (Redirector berbasis tipe akun) |
| `GET` | `/pendaftar/dashboard` | `applicant.dashboard` | `web`, `auth`, `active`, `applicant` | `DashboardController@index` |
| `GET` | `/admin/preview` | `admin.preview` | `web`, `auth`, `active`, `admin` | Closure (Admin Foundation View) |
| `GET` | `/admin/dashboard` | `admin.dashboard` | `web`, `auth`, `active`, `admin` | Closure (Admin Foundation View) |

---

## 7. HASIL PENGUJIAN OTOMATIS (TEST SUITE)

Eksekusi pengujian suite lengkap menggunakan `php artisan test`:

```
   PASS  Tests\Unit\OtpServiceSecurityTest
  ✓ compute hash produces deterministic hmac sha256                               0.04s  
  ✓ compute hash differs when context changes                                      0.01s  
  ✓ compute hash fails closed when key is missing                                  0.01s  
  ✓ compute hash fails closed when key equals app key                              0.01s  
  ✓ compute hash fails closed when key equals nik hmac key                         0.01s  
  ✓ generate and send dispatches otp and invalidates previous tokens               0.02s  
  ✓ verify otp rejects wrong code and tracks attempts                              0.02s  
  ✓ verify otp rejects when attempts exhausted                                     0.02s  
  ✓ verify otp rejects expired token                                               0.01s  
  ✓ verify otp rejects replay of consumed token                                    0.02s  

   PASS  Tests\Feature\Auth\ApplicantRegistrationTest
  ✓ can view registration page                                                     0.04s  
  ✓ registration validates required fields                                         0.02s  
  ✓ registration rejects invalid nik format                                        0.01s  
  ✓ successful registration atomically creates user applicant and dispatches otp   0.03s  
  ✓ rejects duplicate nik registration                                             0.02s  
  ✓ allows sibling registration with same phone number                             0.03s  
  ✓ authenticated applicant is redirected away from registration                   0.02s  

   PASS  Tests\Feature\Auth\OtpActivationTest
  ✓ cannot view otp screen without session                                         0.01s  
  ✓ can view otp screen with valid session                                         0.02s  
  ✓ valid otp verifies phone and advances to password creation                     0.03s  
  ✓ invalid otp is rejected                                                        0.02s  
  ✓ cannot view password creation without verified session                         0.01s  
  ✓ setting password activates account and sets active status                      0.02s  
  ✓ setting password requires minimum 8 characters and confirmation                0.02s  
  ✓ resending otp dispatches new code and invalidates old code                     0.03s  

   PASS  Tests\Feature\Auth\ApplicantLoginTest
  ✓ can view applicant login page                                                  0.02s  
  ✓ active applicant can login with valid nik and password                         0.03s  
  ✓ login rejects pending activation account and redirects to activation           0.02s  
  ✓ login rejects suspended account                                                0.02s  
  ✓ login rejects wrong password with generic error                                0.02s  
  ✓ login rejects non existent nik with generic error                              0.02s  
  ✓ rate limiter throttles excessive failed applicant logins                       0.05s  
  ✓ applicant logout terminates session                                            0.02s  

   PASS  Tests\Feature\Auth\AdminLoginTest
  ✓ can view admin login page                                                      0.03s  
  ✓ admin with valid credentials and role can login                                0.04s  
  ✓ applicant cannot login through admin login                                     0.03s  
  ✓ admin without required role cannot login                                       0.03s  
  ✓ inactive admin cannot login                                                    0.03s  
  ✓ admin with wrong password is rejected                                          0.03s  
  ✓ admin logout terminates session                                                0.03s  

   PASS  Tests\Feature\Auth\PasswordRecoveryTest
  ✓ can view forgot password page                                                  0.02s  
  ✓ submitting registered nik with verified phone issues otp                       0.03s  
  ✓ submitting non existent nik returns generic message without enumerating        0.02s  
  ✓ submitting account with unverified phone returns warning                       0.02s  
  ✓ verifying recovery otp advances to reset password form                         0.03s  
  ✓ setting new password updates user password and enables login                   0.04s  

   PASS  Tests\Feature\Auth\AuthorizationAndMiddlewareTest
  ✓ unauthenticated user cannot access applicant dashboard                         0.01s  
  ✓ unauthenticated user cannot access admin dashboard                             0.02s  
  ✓ applicant cannot access admin dashboard                                        0.03s  
  ✓ admin cannot access applicant dashboard                                        0.03s  
  ✓ suspended user is logged out by active middleware                              0.02s  
  ✓ pending activation user is logged out by active middleware                     0.02s  

   PASS  Tests\Feature\Database\DatabaseSchemaAndRulesTest (18 tests)
   PASS  Tests\Feature\Database\SpatiePermissionSecurityTest (7 tests)
   PASS  Tests\Feature\FoundationTest (3 tests)
   PASS  Tests\Unit\SecurityAndOtpTest (11 tests)
   SKIP  Tests\Feature\Database\MySqlSchemaCompatibilityTest (2 skipped - waiting for live MySQL daemon)

  Tests:    91 passed, 2 skipped, 93 total
  Assertions: 302 assertions
  Duration: 6.59 seconds
```

---

## 8. STATUS VERIFIKASI MYSQL

- **Koneksi Uji Coba**: Berkas `config/database.php` telah memiliki koneksi `mysql_test` yang mengarah ke basis data terisolasi `ppdb_minsatama_test`.
- **Status Daemon MySQL Lokal**: Layanan daemon MySQL lokal pada port 3306 pada mesin host Windows saat ini **belum diaktifkan**.
- **Hasil Uji**: Sesuai instruksi Bagian 14 *Master Prompt*, verifikasi MySQL secara fisik ditandai sebagai **TERTUNDA (PENDING)** hingga server database MySQL lokal diaktifkan. Pengujian fungsional berjalan penuh di atas SQLite memory tanpa deviasi skema.

---

## 9. PETUNJUK SETUP MANUAL PENGGUNA (ENVIRONMENT)

Sebelum menjalankan aplikasi di server lokal/produksi, pastikan parameter rahasia berikut telah terkonfigurasi pada berkas `.env`:

```env
# Kunci Rahasia Deterministic Lookup Hash NIK (64 hex characters)
NIK_HMAC_KEY=

# Kunci Rahasia One-Time Password (OTP) HMAC Binding (Wajib terpisah dari APP_KEY dan NIK_HMAC_KEY)
OTP_HMAC_KEY=

# Parameter OTP
OTP_LENGTH=6
OTP_EXPIRY_MINUTES=5
OTP_MAX_ATTEMPTS=5
OTP_DELIVERY_DRIVER=log
```

---

## 10. KESIMPULAN & STATUS STOP CONDITION

Fase 3 telah selesai secara menyeluruh. Tidak ada kode atau alur kerja di luar cakupan Fase 3 (seperti pengisian biodata orang tua lengkap, riwayat pendidikan, unggah dokumen berkas, penyerahan formulir akhir, cetak PDF, atau rekapitulasi admin) yang diimplementasikan.

Pengembangan saat ini **BERHENTI** pada tahap ini (*Stop Condition*) untuk menunggu evaluasi dan persetujuan resmi dari Pemilik Proyek sebelum melanjutkan ke **Fase 4: Alur Pengisian Data Calon Siswa, Orang Tua & Program Pendidikan**.

