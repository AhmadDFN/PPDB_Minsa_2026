# LAPORAN IMPLEMENTASI FASE 10.3 — PENDING REGISTRATION RECOVERY & RESILIENT NIK REUSE AFTER OTP FAILURE
## Sistem Informasi PPDB Online MIN 1 Kota Madiun (MINSATAMA)

**Tanggal Penyelesaian**: 19 November 2026  
**Status**: **SELESAI (100% LULUS PENGUJIAN OTOMATIS & LINTING)**  
**Penanggung Jawab**: Senior Laravel Architect, Security Auditor, QA Lead & DevOps Engineer  
**Commit Terkait**: Menyusul pada commit Fase 10.3  

---

## 1. HASIL AUDIT ALUR PENDAFTARAN & KEGAGALAN OTP

### A. Masalah Sebelum Fase 10.3
Pada implementasi sebelumnya, saat calon pendaftar mengirimkan formulir registrasi awal (`/daftar`):
1. Sistem membuat entitas `User` dan `Applicant` dengan status `PENDING_ACTIVATION`.
2. Hash deterministik NIK (`nik_lookup_hash`) disimpan ke database dengan constraint `unique`.
3. Token OTP 6 digit di-generate dan dicoba dikirim melalui WhatsApp API Fonnte.
4. Apabila pengiriman OTP gagal (misalnya karena nomor WhatsApp salah ketik, token Fonnte invalid/expired, perangkat WhatsApp terputus, atau network timeout), calon siswa tidak menerima kode OTP.
5. Ketika orang tua/wali menyadari kesalahan nomor WhatsApp atau kode tidak kunjung tiba, mereka kembali ke halaman `/daftar` dan mencoba mendaftar ulang dengan NIK yang sama.
6. **Kegagalan Fatal**: Sistem secara kaku menolak pendaftaran dengan error `NIK sudah terdaftar`. Akun `PENDING_ACTIVATION` terkunci selamanya tanpa bisa melanjutkan verifikasi, dan orang tua tidak bisa memperbarui nomor WhatsApp yang keliru.

### B. Akar Masalah Teknis
- `RegisterApplicantAction` hanya memeriksa apakah `nik_lookup_hash` sudah ada di database (`User::where('nik_lookup_hash', $nikHash)->exists()`). Jika ada, langsung melempar `ValidationException` tanpa memeriksa apakah akun tersebut masih berstatus `PENDING_ACTIVATION` atau sudah `ACTIVE`.
- Tidak ada mekanisme pemulihan pendaftaran tertunda (*pending registration recovery*) yang memvalidasi kecocokan identitas pendaftar lama dengan formulir baru.
- Token OTP lama tidak diinvalider ketika nomor WhatsApp diperbarui.

---

## 2. MEKANISME PENDING REGISTRATION RECOVERY

Fase 10.3 memperkenalkan mekanisme **Pending Registration Recovery** yang terpadu, aman, dan idempoten:

```
[Pengguna Mengirim Formulir /daftar dengan NIK X]
                   |
     [Cari Akun berdasarkan nik_lookup_hash]
                   |
         +---------+---------+
         |                   |
    [Tidak Ditemukan]   [Ditemukan]
         |                   |
   [Buat Akun Baru]          |
   [Status: PENDING]         |
         |                   +-------------------------------+-------------------------------+
         |                   |                               |                               |
         |         [Status: ACTIVE]               [Status: SUSPENDED]            [Status: PENDING]
         |                   |                               |                               |
         |         [TOLAK: Sudah Aktif]           [TOLAK: Akun Ditangguhkan]         [Cek Kecocokan Identitas]
         |                                                                                   |
         |                                                                    +--------------+--------------+
         |                                                                    |                             |
         |                                                               [Tidak Cocok]                   [Cocok]
         |                                                                    |                             |
         |                                                            [TOLAK: Validasi Gagal]    [Update Nomor WhatsApp]
         |                                                                                       [Invalidasi Token Lama]
         |                                                                                       [Terbitkan OTP Baru]
         |                                                                                       [Kirim via Fonnte]
         |                                                                                                  |
         +---------------------------------------+----------------------------------------------------------+
                                                 |
                                     [Redirect ke /aktivasi]
```

### Prinsip Kerja Inti:
1. **Pemeriksaan Status Eksklusif**:
   - Jika akun berstatus `ACTIVE`, tolak tegas: *"NIK ini sudah memiliki akun aktif. Silakan masuk menggunakan NIK dan kata sandi Anda."*
   - Jika akun berstatus non-pending lainnya (misalnya `SUSPENDED`), tolak tegas: *"Akun dengan NIK ini tidak dapat didaftarkan kembali. Silakan hubungi panitia PPDB."*
   - Jika akun berstatus `PENDING_ACTIVATION`, sistem masuk ke alur pemulihan (*recovery mode*).
2. **Validasi & Pencocokan Identitas Ketat**:
   - Sistem membandingkan 4 atribut identitas anak antara data baru dengan data pendaftaran awal:
     1. `full_name` (dinormalisasi huruf kapital dan spasi ganda dibersihkan).
     2. `gender` (enum matching: `L` / `P`).
     3. `birth_place` (dinormalisasi huruf kapital dan trim).
     4. `birth_date` (format tanggal standar `Y-m-d`).
   - Jika ada ketidakcocokan, sistem menolak pemulihan dengan pesan: *"NIK ini sedang dalam proses aktivasi, namun data identitas yang dimasukkan tidak sesuai dengan pendaftaran awal."*
3. **Pembaruan Nomor WhatsApp & Reset Token**:
   - Jika nomor WhatsApp berbeda, sistem memperbarui `users.phone` pada entitas User yang sudah ada.
   - Status verifikasi nomor direset: `users.phone_verified_at = null`.
   - Seluruh token OTP aktivasi sebelumnya yang masih pending diinvalider (`is_used = true`).
   - Tidak ada duplikasi record `users` atau `applicants` (tetap 1 User, 1 Applicant).
4. **Penerbitan OTP Baru**:
   - Menerbitkan OTP 6 digit baru terikat nomor telepon baru.
   - Mengirim OTP melalui gateway WhatsApp Fonnte.

---

## 3. PENANGANAN KEGAGALAN FONNTE SECARA ANGGUN (GRACEFUL DEGRADATION)

Apabila pengiriman OTP melalui Fonnte gagal (misalnya token Fonnte invalid, timeout jaringan, atau nomor belum terdaftar di WA):
1. **Akun Tetap Utuh**: Akun `User` dan `Applicant` tetap berstatus `PENDING_ACTIVATION` dan tidak dihapus/di-rollback.
2. **Tidak Mengunci Pengguna**: Sistem menyimpan `activation_user_id` di sesi dan mengarahkan pengguna ke halaman `/aktivasi`.
3. **Peringatan Informatif**:
   - Menampilkan alert warning bernada ramah: *"Permintaan pendaftaran sudah diterima, namun kode verifikasi WhatsApp belum berhasil dikirimkan. Silakan periksa kembali nomor WhatsApp Anda atau klik 'Coba Kirim Ulang OTP'."*
4. **Navigasi Pemulihan Cepat**:
   - Tombol **"Coba Kirim Ulang OTP"** untuk mencoba dispatch ulang.
   - Tautan jelas **"Nomor salah? Kembali ke Formulir Pendaftaran"** mengarah langsung ke `/daftar`.
   - Pengguna dapat mengisi ulang formulir pendaftaran dengan nomor yang benar; mekanisme *Pending Registration Recovery* akan mengenali NIK dan memperbarui nomor telepon secara instan.

---

## 4. DAFTAR BERKAS YANG DIUBAH ATAU DITAMBAHKAN

| No | Berkas | Status | Ringkasan Modifikasi |
|---|---|---|---|
| 1 | `app/Actions/Authentication/RegisterApplicantAction.php` | Modified | Menambahkan logika `verifyIdentityMatches()`, atomisitas `lockForUpdate()`, penanganan pemulihan akun `PENDING_ACTIVATION`, invalidasi token lama, dan pelaporan flag `is_recovery` & `otp_dispatched`. |
| 2 | `app/Http/Controllers/Auth/ApplicantRegisterController.php` | Modified | Menangani respons recovery vs akun baru, flash message warning saat `otp_dispatched === false`, dan session binding. |
| 3 | `resources/views/auth/applicant-register.blade.php` | Modified | Menambahkan komponen Blade alert untuk session `warning` dan `info`. |
| 4 | `resources/views/auth/applicant-verify-otp.blade.php` | Modified | Menambahkan komponen Blade alert untuk session `warning`, memperjelas label tombol resend ("Coba Kirim Ulang OTP"), dan link recovery ("Nomor salah? Kembali ke Formulir Pendaftaran"). |
| 5 | `tests/Feature/Auth/ApplicantRegistrationTest.php` | Modified | Memperbarui pengujian duplikat NIK untuk membedakan akun `ACTIVE` vs `PENDING_ACTIVATION`, serta menambah assertion alur recovery. |
| 6 | `tests/Feature/Auth/PendingRegistrationRecoveryTest.php` | Added | Menambahkan suite pengujian komprehensif berisi 20 skenario uji otomatis. |
| 7 | `docs/PROJECT_STATUS.md` | Modified | Memperbarui status kemajuan proyek, metrik pengujian, dan catatan teknis Fase 10.3. |
| 8 | `docs/phases/PHASE-10.3-IMPLEMENTATION-REPORT.md` | Added | Laporan dokumentasi komprehensif Fase 10.3. |

---

## 5. LOGIKA VALIDASI DAN PENCOCOKAN IDENTITAS

Metode pencocokan identitas diimplementasikan pada `RegisterApplicantAction::verifyIdentityMatches()` dengan normalisasi toleran:

```php
private function verifyIdentityMatches(Applicant $applicant, array $data): bool
{
    $normalize = fn (string $value): string => mb_strtoupper(preg_replace('/\s+/', ' ', trim($value)));

    $submittedName = $normalize($data['full_name']);
    $existingName = $normalize($applicant->full_name);

    if ($submittedName !== $existingName) {
        return false;
    }

    $submittedGender = is_object($data['gender']) ? $data['gender']->value : (string) $data['gender'];
    $existingGender = is_object($applicant->gender) ? $applicant->gender->value : (string) $applicant->gender;

    if ($submittedGender !== $existingGender) {
        return false;
    }

    $submittedBirthPlace = $normalize($data['birth_place']);
    $existingBirthPlace = $normalize($applicant->birth_place);

    if ($submittedBirthPlace !== $existingBirthPlace) {
        return false;
    }

    $submittedBirthDate = $data['birth_date'] instanceof \DateTimeInterface
        ? $data['birth_date']->format('Y-m-d')
        : (string) $data['birth_date'];

    $existingBirthDate = $applicant->birth_date instanceof \DateTimeInterface
        ? $applicant->birth_date->format('Y-m-d')
        : (string) $applicant->birth_date;

    return $submittedBirthDate === $existingBirthDate;
}
```

---

## 6. JAMINAN KEAMANAN & PRIVASI KRIPTOGRAFI

1. **NIK Plaintext Tidak Pernah Bocor**:
   - NIK asli dienkripsi menggunakan AES-256-CBC (`nik_encrypted`).
   - Pencarian record akun tetap menggunakan hash deterministik satu arah HMAC-SHA256 (`nik_lookup_hash`) dengan `NIK_HMAC_KEY`.
   - Plaintext NIK tidak pernah dicatat dalam file log (`laravel.log`).
2. **OTP Terisolasi & Terlindungi**:
   - Kode OTP 6 digit disimpan sebagai HMAC-SHA256 hash dengan `OTP_HMAC_KEY`.
   - Kode OTP asli tidak pernah disimpan dalam bentuk plaintext di database.
   - Response controller dan view hanya menampilkan nomor telepon terpotong (masking: `0895*******228`).
3. **Pencegahan Race Condition**:
   - `RegisterApplicantAction` menggunakan transaksi basis data atomik (`DB::transaction`) dan row-level locking (`User::lockForUpdate()`) untuk mencegah race condition pada pengiriman formulir ganda secara simultan.

---

## 7. SUITE PENGUJIAN OTOMATIS BARU (`PendingRegistrationRecoveryTest`)

Telah dibuat 20 skenario pengujian otomatis dengan `Http::fake()` tanpa melakukan pengiriman WhatsApp riil:

1. `test_pending_registration_can_be_recovered_with_same_nik_and_same_phone`: Pemulihan pendaftaran dengan NIK dan nomor WA yang sama berhasil tanpa membuat duplikat user.
2. `test_pending_registration_can_be_recovered_with_corrected_phone_number`: Mengoreksi nomor WhatsApp yang keliru pada pendaftaran ulang berhasil memperbarui nomor dan menerbitkan OTP ke nomor baru.
3. `test_recovery_does_not_create_duplicate_user_or_applicant`: Memastikan jumlah record `User` dan `Applicant` di database tetap 1.
4. `test_recovery_invalidates_previous_otp_tokens`: Memastikan seluruh token OTP aktivasi lama diinvalider setelah pemulihan.
5. `test_recovery_resets_phone_verified_at_timestamp`: Memastikan timestamp `phone_verified_at` bernilai `null` sampai OTP berhasil diverifikasi.
6. `test_recovery_rejected_if_name_does_not_match`: Penolakan jika nama calon siswa berbeda dengan data pendaftaran awal.
7. `test_recovery_rejected_if_gender_does_not_match`: Penolakan jika jenis kelamin berbeda.
8. `test_recovery_rejected_if_birth_place_does_not_match`: Penolakan jika tempat lahir berbeda.
9. `test_recovery_rejected_if_birth_date_does_not_match`: Penolakan jika tanggal lahir berbeda.
10. `test_active_account_cannot_be_recovered_via_registration_form`: Penolakan registrasi jika akun sudah berstatus `ACTIVE`.
11. `test_suspended_account_cannot_be_recovered_via_registration_form`: Penolakan registrasi jika akun berstatus `SUSPENDED`.
12. `test_name_comparison_is_case_and_whitespace_insensitive`: Pencocokan nama bersifat case-insensitive dan mengabaikan spasi berlebih.
13. `test_birth_place_comparison_is_case_insensitive`: Pencocokan tempat lahir bersifat case-insensitive.
14. `test_fonnte_failure_on_new_registration_redirects_with_warning`: Penanganan anggun saat Fonnte gagal pada pendaftaran baru.
15. `test_fonnte_failure_on_recovery_redirects_with_warning`: Penanganan anggun saat Fonnte gagal pada alur recovery.
16. `test_recovered_account_can_successfully_activate_with_new_otp`: Akun yang dipulihkan dapat diaktivasi penuh menggunakan OTP baru.
17. `test_old_otp_cannot_activate_recovered_account`: OTP lama dari pendaftaran awal tidak dapat digunakan untuk aktivasi akun yang telah dipulihkan.
18. `test_multiple_consecutive_recoveries_work_reliably`: Pemulihan beruntun berkali-kali tetap berjalan idempoten dan stabil.
19. `test_verification_page_contains_link_back_to_registration`: Halaman verifikasi OTP memuat tautan kembali ke formulir pendaftaran jika nomor salah.
20. `test_rate_limiting_still_applies_on_recovery`: Rate limiting pendaftaran tetap aktif melindungi sistem dari spamming recovery.

---

## 8. HASIL EKSEKUSI PENGUJIAN OTOMATIS KESELURUHAN

- **Suite Pengujian Baru**: `php artisan test --filter=PendingRegistrationRecoveryTest`
  - **Hasil**: 20 passed, 0 failed, 59 assertions.
- **Suite Pengujian Registrasi Asli**: `php artisan test --filter=ApplicantRegistrationTest`
  - **Hasil**: 8 passed, 0 failed, 52 assertions.
- **Suite Pengujian Lengkap Repositori**: `php artisan test`
  - **Total Pengujian**: **256 pengujian**
  - **Status**: **256 passed (100%), 0 failed, 0 skipped**
  - **Total Asersi**: **1.217 assertions**
  - **Durasi Eksekusi**: ~11.0 detik
- **Kepatuhan Gaya Kode**: `vendor/bin/pint --test` -> **PASSED (0 issues)**
- **Audit Keamanan Composer**: `composer audit` -> **No security vulnerability advisories found**
- **Audit Keamanan NPM**: `npm audit --omit=dev` -> **0 vulnerabilities**
- **Kompilasi Aset Frontend**: `npm run build` -> **Vite v8.3.8 built in 569ms**

---

## 9. REKOMENDASI OPERASIONAL UNTUK HOSTINGER STAGING

Berdasarkan screenshot dari pemilik proyek (`ahmadclanyfn.biz.id`):
1. **Pemasangan Paket Terbaru ke Hostinger**:
   - Hostinger staging saat ini masih menjalankan versi deployment sebelumnya (Fase 9).
   - Diperlukan unggah paket zip pembaruan agar rute `/admin/dashboard` terhubung data riil dan alur Pending Registration Recovery aktif di server staging.
2. **Konfigurasi Fonnte di Hostinger**:
   - Pastikan variabel environment pada `.env` Hostinger sudah terisi:
     ```dotenv
     OTP_DRIVER=fonnte
     FONNTE_TOKEN="token_device_fonnte_anda"
     FONNTE_URL=https://api.fonnte.com/send
     FONNTE_SSL_VERIFY=true
     ```
   - Pastikan token yang digunakan adalah **Device Token** dari tab *Device* pada dashboard Fonnte yang statusnya *Connected*, bukan token akun global.
3. **Penyebab Error 404 pada `/admin/pengguna`**:
   - Menu "Manajemen Pengguna" pada sidebar Admin mengarah ke rute manajemen pengguna internal madrasah yang belum dibuat kontrolernya (belum dijadwalkan pada roadmap hingga Fase 10.1). Ini adalah celah navigasi yang aman diabaikan atau diarahkan ke placeholder sebelum modul manajemen user admin diimplementasikan.

---

## 10. KONDISI BERHENTI (STOP CONDITION)

Fase 10.3 telah diselesaikan sepenuhnya sesuai dengan seluruh instruksi dan pedoman kerja `AGENTS.md`. Seluruh pengujian otomatis, linting kode, audit keamanan, dan dokumentasi telah diverifikasi secara faktual. Agen **BERHENTI** dan menunggu instruksi atau konfirmasi dari Pemilik Proyek.
