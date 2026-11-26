# Hasil Live Test Lokal — 25 November 2026

## Ringkasan

Pengujian dilakukan pada lingkungan lokal (`APP_ENV=local`, SQLite) menggunakan akun dummy dari `LiveTestSeeder`. WhatsApp/Fonnte fisik tidak digunakan pada putaran ini; alur OTP nyata tetap menjadi pengujian manual terpisah.

Status keseluruhan: **PASS untuk automated test dan smoke test yang dijalankan**. Sebagian skenario UAT yang mengubah data atau memerlukan berkas fisik masih **PENDING**, bukan dianggap lulus.

## Persiapan

| Pemeriksaan | Status | Catatan |
|---|---|---|
| `php artisan migrate` | PASS | Tidak ada migrasi tertunda (`Nothing to migrate`). |
| `php artisan db:seed --class=LiveTestSeeder` | PASS | Akun/state dummy berhasil dibuat. |
| `php artisan optimize:clear` | PASS | Cache aplikasi berhasil dibersihkan. |
| Server lokal | PASS | Berhasil melayani request di `127.0.0.1:8000`; dihentikan setelah test. |

## Quality gate

| Perintah | Status | Hasil |
|---|---|---|
| `php artisan test` | PASS | 380 tests, 365 passed, 15 skipped, 1.753 assertions, exit code 0. |
| `vendor/bin/pint --test` | PASS | Lulus. |
| `npm.cmd run build` | PASS | Vite build berhasil. |
| `composer audit` | PASS | Tidak ditemukan advisory kerentanan. |
| `npm.cmd audit` | PASS | 0 vulnerabilities. |
| `git diff --check` | PASS | Tidak ada whitespace error. |
| `php artisan route:list` untuk admin/pendaftar/wawancara | PASS | Daftar route berhasil dibaca tanpa error. |

## Smoke test HTTP per role

Semua halaman publik berikut merespons HTTP 200: `/`, `/login`, `/admin/login`, dan `/daftar`. Token QR tidak valid merespons 404 sesuai proteksi.

| Role | Area | Status |
|---|---|---|
| Admin PPDB | Dashboard, daftar pendaftar, verifikasi dokumen, rekap wawancara | PASS — HTTP 200 |
| Admin PPDB | Audit log | PASS — HTTP 403 sesuai pembatasan superadmin |
| Superadmin | Batch wawancara, tahap wawancara, audit log, pengaturan | PASS — HTTP 200 |
| Pendaftar DRAFT | Dashboard, biodata, dokumen, tinjau pendaftaran | PASS — HTTP 200 |
| Pendaftar SUBMITTED/VERIFIED | Dashboard, tinjau pendaftaran | PASS — HTTP 200 |
| Pewawancara | Halaman wawancara | PASS — HTTP 200 |
| Pewawancara | Rekap wawancara admin | PASS — HTTP 403 sesuai pembatasan akses |
| Login dummy | Admin dan pendaftar | PASS — redirect ke dashboard masing-masing |

## Skenario yang belum dijalankan

Item berikut belum boleh diberi status lulus karena belum dilakukan dalam test ini:

- UAT perubahan biodata, penyimpanan persetujuan, submit final, dan idempotensi submit.
- Upload berkas KK/akta/pas foto, validasi MIME/ukuran, penggantian berkas, dan riwayat revisi.
- Pembuatan PDF DRAFT ber-watermark serta PDF SUBMITTED dengan QR yang dapat dipindai.
- Verifikasi dokumen admin, penolakan dengan catatan, assignment batch, dan notifikasi WhatsApp.
- Mutasi konfigurasi superadmin, batch wawancara, tahap, kriteria, dan aturan urutan tahap.
- Scan/lookup token wawancara, sesi penilaian, autosave, penguncian, dan pencegahan akses tahap berikutnya.
- Verifikasi QR publik dengan token valid dan pemeriksaan anti-IDOR pada seluruh variasi URL.
- Pemeriksaan visual mobile-first pada viewport 390 px, 768 px, dan desktop.
- Pengujian Fonnte/OTP menggunakan nomor WhatsApp nyata.

## Saran perbaikan dan urutan tindak lanjut

1. **Prioritas P0 — lakukan UAT berurutan memakai checklist `04-CHECKLIST-FITUR-DAN-PENGUJIAN.md`.** Mulai dari akun DRAFT, jalankan perubahan dan submit, lalu reset memakai `LiveTestSeeder` sebelum mengulang skenario lain.
2. **Prioritas P0 — verifikasi environment sebelum staging.** Pastikan `APP_URL` HTTPS, PHP GD aktif untuk PDF, batas `upload_max_filesize`/`post_max_size` sesuai 5 MB, private storage tidak terpublikasi, dan migrasi dijalankan secara additive setelah backup.
3. **Prioritas P1 — jalankan manual WhatsApp/OTP.** Gunakan panduan `03-PANDUAN-SOLUSI-FONNTE-OTP.md`; cek OTP kedaluwarsa, batas percobaan, pengiriman password sementara terpisah, dan kewajiban ganti password.
4. **Prioritas P1 — lakukan pengujian keamanan manual.** Fokus pada IDOR dokumen/pas foto, token QR, pemisahan akses admin–pewawancara–pendaftar, serta pemeriksaan bahwa NIK/OTP/token tidak masuk log plaintext.
5. **Prioritas P2 — lengkapi bukti visual.** Simpan screenshot hasil viewport mobile dan desktop pada folder laporan agar review UI/UX dapat diverifikasi, bukan hanya berdasarkan HTTP 200.

## Kesimpulan

Tidak ada kegagalan pada automated suite, quality gate, atau smoke test HTTP yang dijalankan. HTTP 403 pada audit log untuk admin PPDB dan akses rekap untuk pewawancara adalah hasil yang diharapkan dari pembatasan otorisasi. Sistem belum dinyatakan selesai UAT karena skenario mutasi data, PDF/QR valid, upload, dan WhatsApp nyata masih pending.

## Retest dan Perbaikan Blocker — 26 November 2026

Saat pemeriksaan log aplikasi, ditemukan error nyata pada agregasi laporan admin:

- **Gejala:** SQLite melaporkan `ambiguous column name: status` pada query ringkasan per periode setelah tabel `registrations` di-join dengan `admission_periods`.
- **Dampak:** Statistik ringkasan laporan dapat gagal atau masuk jalur fallback, walaupun test lama masih hijau karena error tersebut ditangani oleh controller.
- **Perbaikan:** Scope `Registration::submitted()` dan `Registration::draft()` diubah menjadi `where('registrations.status', ...)`, sehingga aman saat query memakai JOIN.
- **Verifikasi perbaikan:** `AdminReportsExportTest` dan `AdminDashboardTest` lulus 22/22 test; login admin lokal dan halaman `/admin/dashboard` merespons HTTP 200 serta statistik tampil.
- **Regression setelah perbaikan:** 380 test, 365 passed, 15 skipped, 1.753 assertions, exit code 0.

Log `ERROR` lain yang terlihat berasal dari test failure-path yang memang sengaja mensimulasikan kegagalan audit, kegagalan generator PDF, integritas PDF rusak, dan Fonnte HTTP 502. Test untuk perilaku fail-closed/graceful degradation tersebut lulus; tidak diperlakukan sebagai defect produksi pada putaran ini.

## Status Setelah Retest

- Automated test, quality gate, login/dashboard admin, login/dashboard pendaftar, draft PDF, private download, dan role restriction: **PASS**.
- Tidak ada blocker aktif yang menghentikan kelanjutan test lokal.
- Skenario UAT yang membutuhkan upload berkas nyata, submit final lengkap, QR valid hasil produksi, responsive viewport visual, dan WhatsApp fisik tetap **PENDING** dan harus diuji manual sesuai checklist.

Catatan teknis PDF: percobaan awal menunggu event download langsung setelah tombol pembuatan PDF draf menghasilkan timeout pada alat browser karena tombol tersebut membuat versi PDF terlebih dahulu dan menampilkan tautan unduh di halaman. Setelah tautan `Unduh PDF` diklik, file berhasil terunduh (`Draf-Pendaftaran-v1.pdf`, sekitar 1,6 MB). Ini bukan blocker aplikasi; prosedur test sudah disesuaikan dengan alur UI aktual.
