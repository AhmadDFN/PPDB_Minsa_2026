# SECURITY & TESTING STRATEGY — PPDB MINSATAMA
## Kerangka Kerja Keamanan Aplikasi, Model Ancaman, dan Strategi Pengujian

---

## 1. MODEL ANCAMAN & STRATEGI MITIGASI (THREAT MODEL)

| Potensi Ancaman | Vektor Serangan | Dampak | Mitigasi Teknis PPDB Minsatama |
|---|---|---|---|
| **Enumerasi NIK Siswa** | Mengirim NIK acak pada halaman login atau lupa password untuk memeriksa keberadaan akun. | Kebocoran data apakah anak terdaftar di MIN 1 Kota Madiun. | **Pesan Respons Generik**: Login dan formulir lupa kata sandi memberikan respons identik baik NIK ditemukan maupun tidak. |
| **Brute-Force Login & OTP** | Percobaan otomatis kombinasi kata sandi atau kode OTP 6-digit. | Pengambilalihan akun pendaftar. | **Rate Limiter**: Batas 5 percobaan login per menit; Batas 5 percobaan gagal per token OTP sebelum terkunci permanen. |
| **Rainbow Table Attack pada OTP** | Peretasan basis data dan komputasi offline atas 1.000.000 kemungkinan kode OTP 6-digit. | Pembocoran kode verifikasi. | **HMAC Context Binding**: Hash mengikat `user_id`, `purpose`, dan nomor telepon menggunakan secret key terpisah `OTP_HMAC_KEY`. |
| **Race Condition / OTP Replay** | Dua request verifikasi OTP dikirimkan secara serentak (*concurrent*). | Bypass validasi verifikasi nomor. | **Database Row Locking**: Eksekusi verifikasi menggunakan `lockForUpdate()` dalam transaksi DB atomik. |
| **Eskalasi Hak Akses (Privilege Escalation)** | Calon siswa mengakses URL `/admin/*` atau memanipulasi parameter role. | Modifikasi data atau penentuan status ilegal. | **Middleware `EnsureAdmin`**: Pengecekan ketat `account_type === 'admin'` dan Spatie Permission guard `web`. Siswa tidak pernah diberi peran Spatie. |
| **Session Fixation & Hijacking** | Penggunaan session ID lama setelah berhasil login. | Pembajakan sesi pendaftar/admin. | **Session Regeneration**: Panggilan `$request->session()->regenerate()` seketika saat autentikasi berhasil. |
| **Kebocoran Dokumen Sensitif** | Mengakses berkas Akta/KK langsung melalui URL publik browser. | Pelanggaran UU Perlindungan Data Pribadi (UU PDP). | **Private Disk Storage**: Berkas disimpan di luar direktori publik web dan hanya diunduh melalui kontroler terotorisasi. |

---

## 2. ARSITEKTUR KEAMANAN NIK & PRIVASI SISWA

```
[Plaintext NIK: 3573010101190001]
               |
      +--------+--------+
      |                 |
      v                 v
[Laravel Crypt]   [HMAC-SHA256]
(AES-256-CBC)     (Kunci: NIK_HMAC_KEY)
      |                 |
      v                 v
[nik_encrypted]   [nik_lookup_hash]
(Tersimpan aman)  (Indeks Unik O(1))
```

### Prinsip Fail-Closed:
Pada `app/Support/NikManager.php`:
```php
$key = config('ppdb.nik_hmac_key');
if (empty($key)) {
    throw new RuntimeException('NIK_HMAC_KEY belum dikonfigurasi.');
}
if ($key === config('app.key')) {
    throw new RuntimeException('NIK_HMAC_KEY tidak boleh sama dengan APP_KEY.');
}
```
Aplikasi menolak memproses query pendaftaran jika kunci keamanan belum diatur atau salah konfigurasi.

---

## 3. STRATEGI PENGUJIAN OTOMATIS (AUTOMATED TEST STRATEGY)

Sistem menggunakan piramida pengujian berlapis:

```
          / \
         /   \     E2E / Browser Acceptance (Fase 9)
        /-----\
       /       \    Feature Tests (API & Controllers) - 78 Tests
      /---------\
     /           \   Unit Tests (Security & Kriptografi) - 21 Tests
    /-------------\
```

### Pembagian Kelompok Test Suite:
1. **Unit Tests (`tests/Unit/`)**:
   - `NikManagerTest`: Normalisasi format, validasi panjang 16 digit, determinisme hash, dan enkripsi round-trip.
   - `OtpServiceSecurityTest`: Validasi HMAC, kegagalan kunci hilang (*fail closed*), kedaluwarsa waktu, penguncian percobaan habis, dan pencegahan replay.
2. **Feature Tests Database (`tests/Feature/Database/`)**:
   - `DatabaseSchemaAndRulesTest`: Verifikasi integritas tabel, relasi Eloquent, unique constraint, dan cascade delete.
   - `SpatiePermissionSecurityTest`: Isolasi peran `superadmin` dan `admin_ppdb`, penolakan peran pada akun pendaftar.
   - `MySqlSchemaCompatibilityTest`: Pengujian skema live pada mesin InnoDB fisik.
3. **Feature Tests Autentikasi (`tests/Feature/Auth/`)**:
   - `ApplicantRegistrationTest`: Form pendaftaran, transaksi atomik, penolakan NIK ganda, pendaftaran saudara kandung (*sibling*).
   - `OtpActivationTest`: Alur aktivasi 5-langkah, verifikasi nomor, stempel `phone_verified_at`, pembuatan kata sandi.
   - `ApplicantLoginTest`: Login NIK aktif, penolakan akun pending/tersuspensi, proteksi rate limiting.
   - `AdminLoginTest`: Login admin email + peran Spatie, penolakan akun pendaftar di portal admin.
   - `PasswordRecoveryTest`: Pemulihan kata sandi berbasis OTP, proteksi anti-enumerasi NIK.
   - `AuthorizationAndMiddlewareTest`: Penegakan otorisasi middleware `active`, `applicant`, dan `admin`.

---

## 4. SKENARIO PENGUJIAN MANUAL (MANUAL TESTING SCENARIOS)

| No | Kasus Uji Manual | Langkah Pengujian | Hasil yang Diharapkan |
|---|---|---|---|
| 1 | Pendaftaran NIK tidak 16 digit | Masukkan NIK '12345' pada formulir pendaftaran | Muncul validasi "NIK harus berupa 16 digit angka", form tidak terkirim. |
| 2 | Pendaftaran NIK duplikat | Daftarkan NIK yang sudah berstatus aktif | Sistem menolak dengan pesan "NIK sudah terdaftar dalam sistem PPDB". |
| 3 | Penggunaan OTP salah berulang | Masukkan 5 kali kode OTP sembarang di `/aktivasi` | Pada percobaan ke-5, token terkunci. Percobaan ke-6 ditolak: "Batas maksimal percobaan OTP telah habis". |
| 4 | Percobaan login akun pending | Buat akun pendaftaran, jangan verifikasi OTP, coba login di `/login` | Ditolak login dan langsung dialihkan kembali ke `/aktivasi` untuk verifikasi. |
| 5 | Percobaan login akun tersuspensi | Ubah status akun menjadi `suspended`, coba login | Muncul pesan "Akun Anda sedang dinonaktifkan oleh panitia PPDB". |
| 6 | Siswa mencoba akses panel admin | Login sebagai siswa, lalu akses langsung URL `/admin/preview` | Menampilkan pesan HTTP 403 Forbidden ("Akses ditolak"). |
| 7 | Admin mencoba login di form siswa | Gunakan email dan password admin pada form `/login` | Ditolak dengan pesan generik "NIK atau kata sandi yang Anda masukkan salah". |
| 8 | Lupa password NIK tidak terdaftar | Masukkan NIK sembarang pada `/lupa-password` | Muncul pesan sukses generik; tidak ada OTP terkirim, tidak ada kebocoran status NIK. |
