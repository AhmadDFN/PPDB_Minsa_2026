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
| **Kebocoran Dokumen Sensitif / IDOR** | Mengakses berkas Akta/KK dengan URL publik atau ID dokumen milik pendaftar lain. | Pelanggaran UU Perlindungan Data Pribadi (UU PDP). | **Private Disk + ownership query**: Berkas berada di `storage/app/private/ppdb/documents`; preview, unduh, dan revisi hanya dilayani setelah pemeriksaan pemilik. Tidak ada URL publik. |
| **Upload Berkas Berbahaya / Besar** | Ekstensi palsu, MIME spoofing, atau unggahan melebihi kebijakan. | Eksekusi/penyimpanan konten berbahaya atau konsumsi resource. | **Validasi server-side**: allowlist ekstensi + MIME Fileinfo, SHA-256, maksimum global/default 5120 KB, serta limit efektif per dokumen. |

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
       /       \    Feature Tests (API & Controllers)
      /---------\
     /           \   Unit Tests (Security & Kriptografi)
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
4. **Feature Tests Dokumen (`tests/Feature/Applicant/DocumentManagementTest.php`)**:
   - Menguji batas di bawah, tepat, dan di atas 5120 KB; limit per persyaratan; MIME/ekstensi; akses guest dan IDOR; history replacement; cleanup file saat persistensi gagal; dan progress dashboard.
5. **Feature Tests Submit Pendaftaran (`tests/Feature/Applicant/RegistrationWorkflowTest.php`)**:
   - Menguji kelengkapan pra-submit termasuk field wajib ayah/ibu, konfigurasi dokumen wajib/opsional, consent, status dan nomor, idempotensi, sequence per periode-program, periode/program tidak aktif, ownership, masking NIK, audit revisi tanpa nilai pribadi, kunci edit setelah periode ditutup, dan preservasi revisi dokumen.
   - Uji SQLite tidak membuktikan konkurensi row-lock InnoDB; verifikasi fisik MySQL masih pending.

### Konfigurasi Consent Submit

- Teks dan versi persetujuan harus disahkan sekolah sebelum produksi. Redaksi sementara hanya untuk lingkungan pengembangan/testing.
- Konfigurasi consent kosong menyebabkan submit gagal tertutup; `config/ppdb.php` tidak menyediakan fallback redaksi sementara untuk `APP_ENV=production`.
- `.env.example` berisi placeholder kosong untuk `PPDB_CONSENT_VERSION` dan `PPDB_CONSENT_STATEMENT`; jangan mengisi nilai produksi sebelum mendapat redaksi resmi.

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

---

## 5. KESIAPAN KUNCI HMAC DAN AUDIT NPM

### Kunci NIK dan OTP

- `NIK_HMAC_KEY` dan `OTP_HMAC_KEY` wajib memiliki nilai acak, independen, dan berbeda satu sama lain serta dari `APP_KEY`.
- `.env.example` hanya berisi placeholder kosong. Pada konfigurasi lokal sesi ini, kedua key telah dibuat dengan CSPRNG setelah pemeriksaan database SQLite memastikan tidak ada data applicant atau NIK tersimpan yang bergantung pada key sebelumnya. Nilai tidak ditampilkan atau dicatat.
- Konfigurasi runtime telah diverifikasi memuat kedua key dan mendeteksi perbedaan terhadap satu sama lain dan `APP_KEY`; APP_KEY tidak diubah. Test memakai key khusus testing dari `phpunit.xml`.
- Operasi NIK/OTP menolak kunci yang kosong dan menolak kunci yang sama. Jangan mencatat atau membagikan nilai kunci.
- Untuk setup lokal baru, buat dua nilai independen menggunakan CSPRNG dan simpan hanya di `.env` yang di-ignore Git. Jangan mengganti `APP_KEY` atau `NIK_HMAC_KEY` pada sistem yang mungkin sudah memiliki data terenkripsi/lookup tanpa prosedur migrasi.

### Audit Dependency Frontend

- `npm audit --json` melaporkan 6 temuan high dari `GHSA-fc5q-39xc-vjpf` terhadap `picomatch` (CVE-2026-112366). `npm audit --omit=dev` melaporkan 0 temuan; paket rentan termasuk rantai development/build, bukan production dependencies.
- Advisory menyebut affected versions `<=4.0.7` dan belum menetapkan versi patched. `npm audit fix --dry-run` hanya menawarkan perubahan lintas major yang menurunkan Vite dan Laravel Vite plugin; perubahan itu tidak diterapkan.
- Jangan gunakan `npm audit fix --force`. Periksa ulang advisory dan kompatibilitas setelah upstream merilis versi yang telah diperbaiki.

### Kebijakan Upload dan Batas Server

- Maksimum global dan default per dokumen: **5120 KB (5 MB)**. Batas persyaratan dapat lebih kecil, tetapi tidak boleh lebih besar dari 5120 KB. Server menggunakan nilai minimum dari global dan batas persyaratan.
- Browser memberikan pemeriksaan ukuran tambahan; seluruh otorisasi, ekstensi, MIME aktual, dan ukuran diverifikasi server-side.
- Produksi wajib menetapkan `upload_max_filesize=5M`, `post_max_size` lebih besar dari 5M, dan request limit web server yang mencakup overhead multipart. Pada lingkungan verifikasi lokal, `upload_max_filesize=2M` dan `post_max_size=8M`; unggahan live 5 MB belum dapat dilakukan sampai PHP diperbarui.
- Feature test menggunakan `UploadedFile` sintetis dan telah menguji tepat 5120 KB, tetapi tidak membuktikan konfigurasi PHP/web server produksi. Verifikasi fisik MySQL juga tetap pending.
