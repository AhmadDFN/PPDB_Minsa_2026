# SECURITY AUDIT REPORT — PPDB MINSATAMA
## Laporan Hasil Audit Keamanan Sistem Informasi PPDB MIN 1 Kota Madiun
### Tahun Ajaran 2026/2027

**Tanggal Audit**: 19 November 2026
**Auditor**: Application Security Auditor & Senior Laravel Architect
**Status Audit**: **FASE 9 — STAGING CLEARANCE & PRODUCTION ARCHITECTURE VERIFIED**

---

## 1. EXECUTIVE SUMMARY & AUDIT SCORECARD

Audit keamanan menyeluruh dilakukan terhadap seluruh komponen aplikasi PPDB Minsatama (Fase 1 hingga Fase 8). Pengujian mencakup pemeriksaan kode statis (*static code analysis*), audit kriptografi, audit dependensi pihak ketiga, serta pengujian penetrasi terarah (*dynamic security tests*) terhadap 18 vektor serangan web modern.

| Vektor Keamanan | Status Audit | Keterangan & Mekanisme Pengamanan |
|---|:---:|---|
| **1. Kriptografi NIK Siswa** | **PASS** | AES-256-CBC via Laravel Crypt + HMAC-SHA256 deterministik lookup hash. Tidak ada plaintext NIK di database atau log. |
| **2. Keamanan Token OTP** | **PASS** | HMAC-SHA256 berikat konteks (`user_id`, `purpose`, `plain_otp`, `phone`). Kedaluwarsa 5 menit, batas 5 percobaan, `hash_equals()`, `lockForUpdate()`. |
| **3. Autentikasi & Pemulihan Sandi** | **PASS** | Pemisahan akun pendaftar (NIK) dan admin (Email). Bcrypt hashing, token pemulihan bersandi kriptografis, pencegahan enumerasi akun. |
| **4. Otorisasi & Spatie Permission** | **PASS** | Strict RBAC pada guard `web`. Siswa dilarang keras memiliki izin admin. Middleware `active`, `applicant`, dan `admin` memutus sesi ilegal. |
| **5. Proteksi IDOR (Multi-Tenant)** | **PASS** | Seluruh data profil, berkas, dan PDF di-scope secara ketat ke `applicant_id` milik user login (`whereHas('registration', ...)->firstOrFail()`). Menghasilkan 404/403. |
| **6. Proteksi CSRF** | **PASS** | Seluruh form POST/PUT/DELETE dilindungi token CSRF Laravel (`@csrf` / `VerifyCsrfToken`). |
| **7. Proteksi XSS (Cross-Site Scripting)**| **PASS** | Sanitasi otomatis Blade template `{{ }}`, pelarangan `{!! !!}` pada masukan pengguna, header `X-Content-Type-Options: nosniff`. |
| **8. Proteksi SQL Injection** | **PASS** | 100% kueri database menggunakan Eloquent ORM dan PDO parameter binding. Tidak ada kueri mentah (*raw SQL concatenation*). |
| **9. Proteksi Mass Assignment** | **PASS** | Properti `$fillable` didefinisikan eksplisit pada seluruh model Eloquent. Data input difilter ketat via FormRequest. Parameter manipulatif (`status`, `registration_number`) diabaikan. |
| **10. Keamanan Unggah Berkas** | **PASS** | Batas ukuran global 5120 KB, whitelist ekstensi ketat (`pdf`, `jpg`, `jpeg`, `png`), validasi tipe MIME aktual via PHP `finfo` (mencegah MIME spoofing). |
| **11. Proteksi Path Traversal** | **PASS** | Nama berkas di-generate acak via `Str::uuid()`. Nama asli disanitasi via `safeOriginalName()`. Simbol `../` dan `\` ditolak keras. |
| **12. Penyimpanan Privat (`ppdb_private`)**| **PASS** | Berkas tersimpan di luar webroot (`storage/app/private/`) dengan flag `serve => false`. Tidak ada URL publik langsung ke berkas. |
| **13. Pengunduhan Terotorisasi** | **PASS** | Pengunduhan berkas dan PDF melalui controller terproteksi yang memverifikasi kepemilikan dan integritas file SHA-256 sebelum *streaming*. |
| **14. Keamanan Mesin PDF (Dompdf)** | **PASS** | `isRemoteEnabled = false`, `isPhpEnabled = false`, `chroot` dibatasi ke storage privat. Menghilangkan risiko SSRF atau RCE melalui PDF renderer. |
| **15. Keamanan Verifikasi Publik QR** | **PASS** | Token acak 256-bit (CSPRNG), hanya hash SHA-256 token yang disimpan di DB. Endpoint publik hanya memuat nomor, tanggal, status, dan fingerprint dokumen (ZERO PII leaked). |
| **16. Pembatasan Laju (*Rate Limiting*)** | **PASS** | Throttle aktif: Login/Aktivasi, Pembuatan PDF (5 req/menit), Verifikasi QR publik (10 req/menit). |
| **17. Proteksi Log Sensitif** | **PASS** | NIK dan OTP dilarang dicatat pada log produksi. Pesan kegagalan menggunakan kode error generik tanpa menampilkan stack trace sensitif. |
| **18. Isolasi Kunci Rahasia (.env)** | **PASS** | `APP_KEY`, `NIK_HMAC_KEY`, dan `OTP_HMAC_KEY` wajib independen dan unik. Runtime melempar `RuntimeException` jika kunci kosong atau identik. |

---

## 2. HASIL AUDIT DEPENDENSI PERANGKAT LUNAK

### 2.1 Paket Dependensi PHP (Composer)
- **Perintah Eksekusi**: `composer audit`
- **Hasil**: **0 advisories (No security vulnerability advisories found)**
- Seluruh pustaka PHP (Laravel Framework 13.x, Spatie Permission 8.5+, Dompdf 3.1+, BaconQrCode 3.0+) bersih dari kerentanan keamanan yang diketahui.

### 2.2 Paket Dependensi Frontend (NPM)
- **Perintah Eksekusi Runtime Produksi**: `npm.cmd audit --omit=dev`
  - **Hasil**: **found 0 vulnerabilities**
- **Perintah Eksekusi Penuh**: `npm.cmd audit`
  - **Hasil**: **found 0 vulnerabilities**
- **Catatan**: Seluruh temuan terdahulu pada paket build telah bersih (0 vulnerabilities).

---

## 3. VERIFIKASI KRIPTOGRAFI DATA PRIBADI (NIK & OTP)

### A. Pengujian Enkripsi NIK (`NikManager.php`)
- Algoritma Enkripsi: AES-256-CBC dengan IV dinamis per baris data via `Illuminate\Support\Facades\Crypt`.
- Algoritma Pencarian: HMAC-SHA256 deterministik dengan kunci rahasia `NIK_HMAC_KEY`.
- Uji Kegagalan Tertutup (*Fail-Closed*): Sistem secara otomatis menolak eksekusi dan melempar `RuntimeException` jika:
  1. `NIK_HMAC_KEY` bernilai null atau string kosong.
  2. Nilai `NIK_HMAC_KEY` sama persis dengan `APP_KEY` (mencegah kompromi silang).
- Integritas Masker: Representasi visual NIK selalu ditampilkan dalam format masker aman (`3573********0001`), kecuali pada dokumen fisik resmi Bukti Pendaftaran final setelah pendaftar menyelesaikan submit.

### B. Pengujian Token OTP (`OtpService.php`)
- Algoritma: HMAC-SHA256 mengikat konteks: `hash_hmac('sha256', "{$userId}|{$purpose}|{$plainOtp}|{$phone}", $otpKey)`.
- Ketahanan terhadap Serangan:
  1. *Rainbow Table Attack*: Dieliminasi karena hash mengikat data kontekstual pengguna.
  2. *Timing Attack*: Dieliminasi dengan verifikasi berbasis waktu konstan `hash_equals()`.
  3. *Race Condition / Concurrent Replay*: Dieliminasi dengan transaksi baris terkunci `lockForUpdate()`.
  4. *Brute Force Attack*: Dibatasi maksimal 5 kali percobaan gagal sebelum token terkunci permanen.
  5. *Token Expiration*: Dibatasi tepat 5 menit sejak penerbitan.

---

## 4. ISOLASI KEPEMILIKAN DATA (IDOR & MULTI-TENANT TEST)

Pengujian penetrasi terarah pada E2E Scenario 6 membuktikan:
1. **Pengunduhan Dokumen Persyaratan**:
   - Jika Applicant A mencoba mengunduh dokumen milik Applicant B (`/pendaftar/dokumen/{idB}/download`), sistem mengembalikan respons **404 Not Found** (kueri di-scope ke `applicant_id` milik akun yang login). Tidak ada informasi keberadaan dokumen yang dibocorkan kepada penyerang.
2. **Pengunduhan PDF Bukti Pendaftaran**:
   - Jika Applicant A mencoba mengunduh PDF pendaftaran milik Applicant B (`/pendaftar/pendaftaran/pdf/{pdfB}/unduh`), sistem mengembalikan respons **404 Not Found**.
3. **Pencegahan Manipulasi Parameter Permintaan (Parameter Tampering)**:
   - Pengiriman payload berisi `'registration_number' => 'PPDB/FORGED/99999'` atau `'status' => 'ACCEPTED'` pada saat submit pendaftaran final diabaikan secara total oleh backend. Nomor registrasi resmi tetap diterbitkan melalui sequence generator transaksional `PPDB/{tahun-ajaran}/{kode-program}/{urutan}`.

---

## 5. KEAMANAN DOKUMEN PDF & KODE QR VERIFIKASI

1. **Konfigurasi Sandbox Dompdf**:
   - `isRemoteEnabled`: `false` (mencegah penyerang menyisipkan gambar atau aset dari server eksternal/SSRF).
   - `isPhpEnabled`: `false` (mencegah eksekusi kode PHP di dalam dokumen PDF).
   - `chroot`: Dibatasi secara ketat ke direktori `storage/app/private/`.
2. **Validasi Pas Foto untuk Mencegah Decompression Bomb / Memory Exhaustion**:
   - Dimensi gambar pas foto dibatasi maksimal 20.000.000 piksel (`width * height <= 20,000,000`). Pas foto raksasa yang dapat menghabiskan memori server ditolak sebelum proses rendering dimulai.
3. **Privasi Endpoint Publik Verifikasi QR**:
   - Token verifikasi dihasilkan melalui 32 byte secure random bytes (`bin2hex(random_bytes(32))`).
   - Basis data hanya menyimpan hash SHA-256 dari token (`verification_token_hash`).
   - Halaman publik `/verifikasi/bukti-pendaftaran/{token}?registration_number=...` hanya menampilkan:
     - Nomor Registrasi
     - Tanggal Penerbitan Dokumen
     - Fingerprint SHA-256 Dokumen (16 karakter pertama)
     - Status Dokumen (`current` / `outdated` / `superseded`)
   - **TIDAK ADA DATA PRIBADI (PII) YANG DIUNGKAP**: NIK calon siswa, alamat, nomor telepon, nama orang tua, dan tautan berkas fisik PDF tidak pernah dikembalikan ke respons publik.

---

## 6. KESIMPULAN AUDIT KEAMANAN

Sistem Informasi PPDB MIN 1 Kota Madiun telah memenuhi seluruh standar keamanan aplikasi web pemerintahan dan madrasah:
- **Tingkat Risiko Kerentanan**: **LOW / MINIMAL**
- **Kesiapan Keamanan untuk Produksi**: **CONDITIONAL — PENDING LIVE ACCEPTANCE AND OWNER GO-LIVE APPROVAL**

## 7. REKONSILIASI KEAMANAN 20 NOVEMBER 2026

### Endpoint Migrasi Produksi

Audit menemukan endpoint GET migrasi production dengan token statis tertanam pada `routes/web.php`. Endpoint tersebut dapat menjalankan migrasi, seeding, dan pembersihan cache. Endpoint telah dihapus dari source lokal, dan regression test memastikan URL maintenance tersebut mengembalikan 404. Endpoint tidak diakses pada Hostinger. Migrasi operasional wajib melalui CLI SSH setelah backup.

### Privasi Uji Live

Credential QA, OTP, token gateway, NIK lengkap, nomor WhatsApp lengkap, dan dokumen pribadi tidak dicatat dalam laporan live. Credential yang pernah dibagikan dalam percakapan harus dianggap terekspos dan diganti sebelum dipakai untuk operasi resmi.
