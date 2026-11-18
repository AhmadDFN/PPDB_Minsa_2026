# SECURITY AUDIT REPORT — PPDB MINSATAMA
## Laporan Hasil Audit Keamanan Sistem Informasi PPDB MIN 1 Kota Madiun

**Tanggal Audit**: 18 November 2026
**Auditor**: Senior Security Engineer & Laravel Specialist  
**Status Audit**: TEMUAN NPM HIGH PADA DEPENDENCY BUILD TERBUKA; FASE 6 BELUM CLOSED (MYSQL/INNODB BELUM DIVERIFIKASI)

---

## 1. HASIL AUDIT DEPENDENSI (DEPENDENCY VULNERABILITY AUDIT)

### 1.1 Backend Dependencies (Composer)
- **Perintah**: `composer audit`
- **Hasil**: `No security vulnerability advisories found.`
- **Hasil pemeriksaan terbaru**: Tidak ada security vulnerability advisories yang dilaporkan Composer.

### 1.2 Frontend Dependencies (NPM)
- **Perintah**: `npm audit`
- **Hasil aktual**: `npm audit --json` melaporkan 6 temuan high pada 6 paket (`picomatch`, `fdir`, `tinyglobby`, `vite-plugin-full-reload`, `laravel-vite-plugin`, dan `vite`) yang terhubung ke advisory `GHSA-fc5q-39xc-vjpf` / `CVE-2026-112366`. `npm audit --omit=dev` melaporkan 0 temuan.
- **Versi dan dependency path**:
  - `picomatch` 4.0.7 (root) dan 2.3.2 (`vite-plugin-full-reload` nested): advisory menyatakan affected `<=4.0.7`; audit package range `*`.
  - `fdir` 6.5.0 (`fdir -> tinyglobby -> Vite / Laravel Vite plugin`), audit range `>=5.2.1`.
  - `tinyglobby` 0.2.17 (`tinyglobby -> Vite / Laravel Vite plugin`), audit range `*`.
  - `vite-plugin-full-reload` 1.2.0 (`laravel-vite-plugin -> vite-plugin-full-reload`), audit range `>=0.2.0`.
  - `vite` 8.3.8 (direct dev dependency), audit range `>=6.3.0-beta.0`.
  - `laravel-vite-plugin` 3.2.1 (direct dev dependency), audit range `>=0.3.0`; finding is propagated through `tinyglobby`, Vite, and full-reload plugin.
- **Dampak**: seluruh paket terpasang sebagai development/build tools, bukan runtime production dependencies. Risiko berfokus pada proses build/CI yang memproses pola glob yang tidak tepercaya.
- **Perbaikan dan kompatibilitas**: Advisory belum menetapkan patched version. Audit menyarankan downgrade lintas major untuk beberapa dependency; tidak diterapkan. Lockfile dipertahankan dan tidak menjalankan `npm audit fix --force`.
- **Status**: isu terbuka; audit ulang setelah rilis patch upstream.

### 1.3 Secret Configuration Readiness
- `.env.example` berisi placeholder kosong. Key lokal telah dibuat dengan CSPRNG hanya pada `.env` yang di-ignore setelah pemeriksaan agregat database menunjukkan tidak ada applicant/NIK tersimpan. Nilai tidak ditampilkan.
- Runtime mendeteksi APP_KEY, NIK_HMAC_KEY, dan OTP_HMAC_KEY; kedua key HMAC independen satu sama lain dan berbeda dari APP_KEY. APP_KEY tidak diubah. Test suite memakai key test-only dari `phpunit.xml`.
- Operasi NIK/OTP gagal tertutup bila key kosong atau saling sama. Jangan mengganti APP_KEY atau NIK_HMAC_KEY pada instalasi yang memiliki data tergantung key tersebut tanpa rencana migrasi.

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

## 4. AUDIT FASE 5 — PENGELOLAAN DOKUMEN

   - Disk `ppdb_private` menggunakan root `storage/app/private` dengan base path `ppdb/documents`; tidak ada route `storage` publik untuk dokumen.
   - Upload membatasi format PDF/JPG/JPEG/PNG, membandingkan ekstensi dengan MIME Fileinfo aktual, menerapkan nilai minimum global/per-persyaratan, dan menyimpan SHA-256.
   - Route preview, unduh, dan revisi membatasi query pada applicant pemilik; feature test cross-applicant memperoleh 404.
   - Replacement menulis metadata/path berkas lama ke riwayat tanpa menghapus file historis. Jika transaksi database gagal setelah file baru tersimpan, action menghapus file baru dan mengembalikan error.
   - Risiko konfigurasi operasional: PHP lokal masih `upload_max_filesize=2M` (di bawah kebijakan 5 MB), sementara `post_max_size=8M`. Atur `upload_max_filesize=5M`, `post_max_size` >5M, dan request limit web server yang mencakup multipart overhead sebelum menerima berkas 5 MB.

## 5. AUDIT FASE 6 — SUBMIT DAN REVISI PENDAFTARAN

- Endpoint submit memperoleh pendaftaran hanya dari relasi pendaftar yang sedang masuk; ID dan status kiriman tidak dipercaya dari request. Uji lintas akun menolak akses.
- Submit membungkus validasi, lock row, sequence, consent, status, timestamp, dan audit dalam transaksi. Pengiriman ulang mempertahankan nomor/timestamp serta tidak menggandakan consent/log.
- Sequence menggunakan row lock per `admission_period_program_id` dan unique constraint nomor pada registration. Semantik lock InnoDB belum diuji pada server MySQL fisik; uji SQLite bukan bukti concurrency production.
- NIK siswa/orang tua dimasker pada halaman tinjauan; path penyimpanan privat tidak dikirim ke Blade. Log revisi hanya berisi nama field yang berubah, bukan nilai pribadi.
- Konfigurasi consent kosong menyebabkan submit gagal. Redaksi dan format nomor saat ini provisional dan memerlukan persetujuan sekolah sebelum produksi.
- Tidak ada PDF, seleksi siswa, persetujuan dokumen, atau pelaporan administrator yang diimplementasikan pada fase ini.
- Review penutupan: izin `reopen_registration` hanya tersedia bagi `superadmin` di seeder; tidak ditemukan route/action/policy untuk menjalankan reopen. Ruang lingkup dan masa berlaku belum menjadi aturan tertulis, sehingga implementasi tidak boleh diasumsikan tersedia.
- **Batas verifikasi**: MySQL PDO tersedia, tetapi server `127.0.0.1:3306` tidak dapat dihubungi. Dua test MySQL diskip; tidak ada bukti fisik migrasi maupun submit bersamaan di InnoDB. Status audit database/concurrency adalah BLOCKED, bukan PASS.
- **Gerbang rilis**: format nomor dan redaksi/versi consent masih menunggu persetujuan pemilik. Fase 6 belum CLOSED; Fase 7 tetap BLOCKED sampai semua gate selesai dan ada otorisasi.

## 6. KESIMPULAN & REKOMENDASI AUDIT

1. **Status risiko**: audit ini tidak menyatakan seluruh stack bebas risiko; terdapat enam temuan NPM high pada dependency build/development yang menunggu patch kompatibel.
2. **Rekomendasi Operasional Produksi**:
   - Pastikan variabel `APP_KEY`, `NIK_HMAC_KEY`, dan `OTP_HMAC_KEY` pada server produksi digenerate menggunakan random string berentropi tinggi (minimal 64 karakter hex/base64) dan disimpan secara aman.
   - Aktifkan HTTPS/SSL penuh dan pastikan cookie diset dengan atribut `Secure` dan `HttpOnly`.
