# SECURITY AUDIT REPORT — PPDB MINSATAMA
## Laporan Hasil Audit Keamanan Sistem Informasi PPDB MIN 1 Kota Madiun

**Tanggal Audit**: 15 November 2026  
**Auditor**: Senior Security Engineer & Laravel Specialist  
**Status Audit**: TERVERIFIKASI AMAN (NO CRITICAL VULNERABILITIES)

---

## 1. HASIL AUDIT DEPENDENSI (DEPENDENCY VULNERABILITY AUDIT)

### 1.1 Backend Dependencies (Composer)
- **Perintah**: `composer audit`
- **Hasil**: `No security vulnerability advisories found.`
- **Jumlah Paket**: 82 paket produksi dan pengembangan. Seluruh paket terdaftar pada versi stabil terbaru yang bebas dari advisory keamanan CVE publik.

### 1.2 Frontend Dependencies (NPM)
- **Perintah**: `npm audit`
- **Hasil**: `found 0 vulnerabilities`
- **Jumlah Paket**: 40 paket npm (Vite 8, Tailwind CSS v4, plugin). Tidak ada celah keamanan yang terdeteksi.

---

## 2. AUDIT KRIPTOGRAFI DATA PRIBADI (NIK & OTP)

### 2.1 Enkripsi NIK (`NikManager.php`)
- **Algoritma**: AES-256-CBC via Laravel Crypt.
- **Lookup Hash**: HMAC-SHA256 dengan kunci rahasia mandiri `NIK_HMAC_KEY`.
- **Temuan & Verifikasi**:
  - Validasi panjang tepat 16 digit angka numerik.
  - Fail-closed runtime check: melempar `RuntimeException` jika kunci belum dikonfigurasi atau identik dengan `APP_KEY`.
  - Tidak ada data plaintext NIK yang tersimpan di basis data atau dicatat dalam log sistem.
  - NIK dimasker pada representasi antarmuka publik (`3573********0001`).

### 2.2 Verifikasi OTP Berikat Konteks (`OtpService.php`)
- **Algoritma**: HMAC-SHA256 mengikat `user_id`, `purpose`, `plain_otp`, dan nomor telepon.
- **Kunci Rahasia**: `OTP_HMAC_KEY` mandiri.
- **Temuan & Verifikasi**:
  - Menghilangkan risiko serangan tabel pelangi (*rainbow table*).
  - Evaluasi kode menggunakan fungsi perbandingan waktu konstan `hash_equals()`.
  - Database row locking via `lockForUpdate()` mencegah pemanfaatan kembali secara serentak (*race condition replay*).
  - Pembatasan maksimal 5 kali percobaan gagal sebelum token terkunci permanen.
  - Masa kedaluwarsa tepat 5 menit.
  - Driver pengiriman log diatur agar **TIDAK MENCATAT KODE OTP** saat berada pada lingkungan produksi (`production`).

---

## 3. AUDIT OTORISASI & PROTEKSI ROUTE

1. **Pemisahan Peran**:
   - Middleware `EnsureApplicant` memverifikasi `account_type === 'applicant'`.
   - Middleware `EnsureAdmin` memverifikasi `account_type === 'admin'` dan peran Spatie `superadmin`/`admin_ppdb`.
   - Seluruh rute pendaftar terlindungi dari akses administrator, dan seluruh rute admin terlindungi dari akses calon siswa.
2. **Penanganan Status Akun**:
   - Middleware `EnsureAccountIsActive` memutus sesi (*force logout*) seketika jika status akun pengguna beralih ke `SUSPENDED` atau belum menyelesaikan aktivasi (`PENDING_ACTIVATION`).
3. **Pencegahan Enumerasi Akun**:
   - Respons pada halaman `/login` dan `/lupa-password` bersifat generik, mencegah penyerang memetakan daftar NIK yang terdaftar di MIN 1 Kota Madiun.
4. **Proteksi Sesi**:
   - Panggilan `$request->session()->regenerate()` dilakukan setiap kali login berhasil untuk menangkal serangan fiksasi sesi (*session fixation*).
   - Seluruh rute formulir terlindungi oleh token CSRF bawaan Laravel.

---

## 4. KESIMPULAN & REKOMENDASI AUDIT

1. **Tingkat Risiko Keseluruhan**: **RENDAH (LOW RISK)**.
2. **Rekomendasi Operasional Produksi**:
   - Pastikan variabel `APP_KEY`, `NIK_HMAC_KEY`, dan `OTP_HMAC_KEY` pada server produksi digenerate menggunakan random string berentropi tinggi (minimal 64 karakter hex/base64) dan disimpan secara aman.
   - Aktifkan HTTPS/SSL penuh dan pastikan cookie diset dengan atribut `Secure` dan `HttpOnly`.
