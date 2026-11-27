# PPDB MINSATAMA — MASTER DOKUMENTASI TERKINI

**Tanggal audit/konsolidasi:** 27 November 2026
**Repo:** `PPDB_MINSATAMA_2026_GIT_RECOVERY`
**Commit checkout:** `1abee9d` (`main`)
**Bahasa dan zona waktu:** Bahasa Indonesia, `Asia/Jakarta` (WIB)

> Dokumen ini adalah ringkasan konsolidasi untuk manusia dan agen kerja. Ia menggabungkan status, keputusan, catatan implementasi, hasil UAT, konflik dokumentasi, serta roadmap dari `docs/` dan catatan yang telah dikonsolidasikan.
>
> Menurut `AGENTS.md`, `docs/PROJECT_STATUS.md` tetap menjadi **Single Source of Truth resmi status proyek**. File ini menjadi master praktis agar isi dokumentasi tidak perlu dibaca berulang-ulang.

## 1. Kesimpulan paling baru

Proyek sudah memiliki aplikasi Laravel fungsional yang mencakup pendaftaran pendaftar, OTP, profil/formulir, dokumen privat, submit, PDF draft/final, QR verifikasi, dashboard admin, verifikasi dokumen, reopen, laporan/CSV, audit viewer, pengaturan superadmin, Communication Center foundation, serta modul wawancara PPDB v2 pada kode lokal.

Status keseluruhan **belum boleh disebut production-ready penuh**. Alur pendaftar utama telah diuji otomatis dan sebagian live, tetapi penerimaan admin/superadmin, validasi MariaDB setelah insiden migrasi wilayah, QR scan fisik, beberapa UAT peta/GPS, foto asli/latar biru, dan verifikasi dependensi pada server masih memiliki gap.

Dokumen Gemini bukan versi terbaru. Ia berguna sebagai review dan rekomendasi, tetapi berisi angka tes, format nomor, dan klaim live yang sudah tertinggal atau bertentangan dengan kode/status terbaru.

Keputusan pemilik pada 27 November 2026 menetapkan format nomor **`YYNNNN`** dan menjadikan modul wawancara sebagai bagian resmi PPDB. Seleksi, perankingan, pengumuman, dan daftar ulang tetap di luar scope.

## 2. Hierarki sumber yang harus dipakai

Jika isi dokumen bertentangan, gunakan urutan berikut:

1. Keputusan tertulis terbaru dari pemilik proyek.
2. `AGENTS.md` untuk aturan tetap, keamanan, scope, dan larangan operasional.
3. Kode dan test aktual pada checkout, sebagai bukti implementasi.
4. `docs/PROJECT_STATUS.md` untuk status resmi proyek.
5. `docs/phases/PHASE-11-LAUNCH-READINESS-AND-DECISIONS.md` untuk perkembangan dan checklist launch terbaru.
6. Laporan audit/UAT dengan tanggal paling baru.
7. `docs/` fase dan manual untuk rincian historis.
8. Review Gemini telah diarsipkan ke dalam konsolidasi ini dan sumber fisiknya dihapus atas keputusan pemilik.

## 3. Identitas dan batasan bisnis inti

- Sistem: PPDB online calon peserta didik kelas 1 MI MIN 1 Kota Madiun, tahun ajaran 2026/2027.
- Stack: Laravel 13, PHP 8.3+, Eloquent, Spatie Permission, Blade, Tailwind CSS v4, Vanilla JavaScript, Vite, MySQL/MariaDB produksi, SQLite in-memory untuk test.
- Tidak boleh menambah Bootstrap, Inertia, Vue, React, Svelte, Angular, atau SPA framework lain.
- Satu NIK = satu akun global; nomor WhatsApp orang tua/wali boleh dipakai untuk saudara kandung.
- Status inti pendaftaran: `DRAFT` dan `SUBMITTED`.
- Umur dihitung saat runtime terhadap `age_reference_date`; umur tidak disimpan sebagai kolom database dan tidak menjadi diskualifikasi otomatis.
- Dokumen maksimal global/default 5120 KB (5 MB), disimpan privat di `storage/app/private/ppdb` dan penggantian berkas memiliki riwayat.
- PDF draft memiliki watermark `DRAFT`; PDF submitted adalah bukti pendaftaran, bukan bukti kelulusan, dan memakai QR minimal tanpa PII.
- Sistem mencakup pendaftaran, verifikasi berkas, penomoran antrean, penjadwalan, dan pelaksanaan wawancara. Seleksi, perankingan, pengumuman lulus/tolak, dan daftar ulang tetap di luar scope.
- Jam pelayanan PTSP: **07.00 – 15.00 WIB** pada hari kerja.

## 4. Keamanan yang dianggap aktif

- NIK disimpan terenkripsi (`nik_encrypted`) dan dicari menggunakan HMAC-SHA256 (`nik_lookup_hash`) dengan `NIK_HMAC_KEY`; UI menampilkan NIK termasking.
- OTP 6 digit memakai HMAC-SHA256 dengan `OTP_HMAC_KEY`, berlaku 5 menit, maksimal 5 percobaan, konteks aktivasi/lupa password dipisah, dan verifikasi memakai `hash_equals()` serta locking transaksi.
- Satu guard `web`; applicant login dengan NIK, admin dengan email. Applicant tidak memiliki role Spatie dan diblokir dari `/admin/*`.
- Akun `PENDING_ACTIVATION` dan `SUSPENDED` tidak boleh login.
- NIK plaintext, OTP, password, token Fonnte, dan Authorization header tidak boleh masuk log/chat/respons.
- Dokumen/PDF privat dilayani melalui controller yang memeriksa ownership dan role; pengujian lintas akun menghasilkan 404 pada bukti yang tidak dimiliki.
- CSV menggunakan sanitasi anti-formula injection.

## 5. Alur fitur yang terbukti ada di kode

### Pendaftar

Registrasi awal berisi NIK, nama, nomor WhatsApp, dan persetujuan. Setelah OTP berhasil, sistem mengaktifkan akun, membuat password sementara acak, mengirim password melalui WhatsApp terpisah, dan mewajibkan penggantian password pertama kali. Pendaftar lalu melengkapi biodata, alamat, orang tua/wali, pendidikan, program, dokumen, tinjauan, consent, dan submit.

Form tambahan terbaru mencakup nomor HP calon siswa, data sensitif orang tua yang terenkripsi, dataset wilayah administratif lokal 2025 dengan cascading select, salin wilayah orang tua, serta peta offline sekitar Kampus 1 dengan pin tap/drag/keyboard dan perhitungan Haversine tanpa menyimpan koordinat.

### Panitia/admin

Tersedia dashboard data riil, daftar/detail pendaftar, preview/unduh dokumen privat, verifikasi dokumen (`PENDING`, `APPROVED`, `REJECTED`, `REVISION_REQUIRED`), reopen berbatas waktu, laporan, ekspor CSV, audit log, dan sebagian fitur modul wawancara.

### Superadmin

Tersedia atau tersedia secara lokal: manajemen periode/program/persyaratan, manajemen akun admin/permission, branding dan system settings, Communication Center provider foundation, template WhatsApp, serta audit konfigurasi. Beberapa operasi live belum diterima secara penuh.

### PDF dan QR

PDF draft/final memakai storage privat, versioning, snapshot, hash SHA-256, dan QR token minimal. PDF final live telah dibuat dan diunduh pada 27 November 2026; pemindaian QR dengan kamera/decoder fisik masih pending.

## 6. Status implementasi terbaru

### Selesai atau teruji lokal

- Foundation, autentikasi, OTP, aktivasi, password sementara, dan pending-registration recovery.
- Profil/formulir pendaftar dan readiness check.
- Dokumen privat 5 MB, replacement, revision history, ownership/IDOR protection.
- Submit atomik/idempoten, consent, audit log, reopen.
- PDF draft/final, versioning, QR verification endpoint.
- Dashboard admin, applicant management, verifikasi dokumen, reports/export, audit viewer.
- Communication Center provider foundation dan editor template.
- Modul wawancara: master batch/stage/criteria, portal pewawancara, sequential gate, penguncian nilai, rekap, koreksi ber-audit, dan CSV tersanitasi.
- Revisi form calon siswa, dataset wilayah, salin alamat, dan widget peta offline pada level automated/browser harness.
- Redesign lokal landing, login, dan dashboard pendaftar menggunakan arahan Calm Academic dan Next Atelier; tidak semua UAT visual sudah selesai.

### Sudah diuji live sebagian

- Halaman publik, login, registrasi, redirect rute terlindungi.
- Alur pendaftar hingga submit dan unduh PDF final dua halaman A4 pada fixture dummy.
- Login panitia/superadmin, pencarian/detail fixture, preview berkas, approve/revisi/tolak, riwayat, laporan, dan isolasi role sebagian.

### Masih pending atau perlu keputusan pemilik

- Penerimaan live admin/superadmin penuh: hasil terbaru hanya 2 PASS, 13 PARTIAL, 1 BLOCKED, 2 belum diuji.
- Verifikasi MariaDB setelah insiden `migrate:fresh --seed` dan foreign-key `administrative_regions`; jangan mengulang operasi destruktif.
- Pemeriksaan backup dan kemungkinan tabel wilayah parsial di server.
- Scan QR final secara fisik.
- UAT GPS/map pada form penuh, foto asli/latar biru, dan verifikasi production/staging setelah perubahan terbaru.
- Verifikasi bahwa dependensi lock terbaru sudah terpasang di server.
- PHP `gd`/batas upload hosting dan konfigurasi runtime produksi.
- Rekonsiliasi konten landing yang masih menyebut seleksi/pengumuman atau klaim proses fisik yang harus konsisten dengan scope.
- Penerimaan live admin/superadmin penuh dan UAT wawancara end-to-end.

## 7. Konflik yang ditemukan dan keputusan yang belum boleh diasumsikan

### 7.1 Nomor pendaftaran

Ada tiga representasi:

| Sumber | Format/behavior | Penilaian |
|---|---|---|
| Dokumen lama Phase 10.4 | `YYNNN`, sequence global per tahun, dialokasikan sejak registrasi/pending recovery | **Usang; digantikan keputusan pemilik 27 November 2026** |
| `config/ppdb.php` dan `RegistrationNumberGeneratorService::next()` | `PPDB/{tahun-ajaran}/{program}/{urutan-5-digit}` | Jalur generator inti/legacy yang masih aktif |
| `AGENTS.md`, `ppdbv2`, `generateQueueNumber()`, `ApproveAndAssignQueueNumberAction` | `YYNNNN`, contoh `270001`, diterbitkan saat pengesahan berkas | **Canonical terbaru** |

Implementasi aktual menunda nomor ke `ApproveAndAssignQueueNumberAction`, sesuai keputusan terbaru. Jalur generator legacy `next()` dan referensi `YYNNN` masih harus dibersihkan agar tidak ada dua kontrak yang hidup bersamaan.

### 7.2 Scope wawancara

Modul wawancara sudah menjadi bagian resmi PPDB sesuai keputusan pemilik. Modul ini mengelola batch, tahap, kriteria, akses pewawancara, nilai, rekap, dan audit; modul tidak melakukan seleksi/perankingan atau pengumuman kelulusan.

### 7.3 Klaim Gemini

Review Gemini sebelumnya menyebut 192+ test, format nomor panjang `PPDB/...`, dan live flow seolah seluruhnya lulus. Angka dan klaim itu adalah snapshot lama yang sudah dikonsolidasikan dan tidak lagi menjadi sumber aktif. Gunakan `docs/PROJECT_STATUS.md`, Phase 11, dan bukti UAT bertanggal 27 November 2026.

## 8. Isu terbuka terbaru

- **ISS-013:** kontrak lama `YYNNN` dan generator legacy masih perlu dibersihkan dari kode, test, dan dokumen agar seluruh sistem konsisten dengan `YYNNNN`.
- **ISS-011:** pemulihan/validasi migrasi wilayah MariaDB tertunda; ada risiko server memiliki tabel parsial setelah operasi `migrate:fresh --seed` gagal.
- **ISS-004/008:** peta offline dan Haversine tersedia, tetapi UAT form penuh/GPS serta cakupan visual masih terbatas sekitar Kampus 1.
- **ISS-005:** validasi warna latar biru pas foto belum otomatis; perlu keputusan apakah manual atau teknis.
- **ISS-006:** QR automated verification lulus, scan fisik belum terbukti.
- **ISS-002:** PNG pada Dompdf dapat memerlukan ekstensi PHP GD; JPG/JPEG lebih aman sebagai fallback.
- **ISS-003:** batas upload/runtime hPanel perlu diverifikasi langsung.
- **ISS-010:** catatan awal overflow tablet 768 px sudah diperbaiki lokal/live sebagian; verifikasi final pada semua halaman masih pending.
- **ISS-012:** advisory Composer sudah bersih lokal setelah update Symfony, tetapi versi lock/server tetap perlu diverifikasi pascadeploy.

## 9. Hasil quality gate terakhir yang boleh dikutip

Snapshot quality gate terbaru yang dicatat sebelum konsolidasi:

- `php artisan test`: **392 test, 377 passed, 15 skipped, 1.800 assertions** pada pekerjaan palet/kontras terbaru.
- Snapshot sebelumnya sesudah rekonsiliasi UAT: **390 test, 375 passed, 15 skipped, 1.793 assertions**.
- `vendor/bin/pint --test`: lulus pada snapshot tersebut.
- `npm.cmd run build`: lulus pada snapshot tersebut.
- `composer audit`: bersih lokal setelah update Symfony pada snapshot terbaru; server belum diverifikasi.
- `npm.cmd audit`: 0 vulnerability pada snapshot terbaru.
- `git diff --check`: lulus pada snapshot pekerjaan terakhir yang dicatat.

Re-run checkout ini pada 27 November 2026: `php artisan test` lulus **376 test, 16 skipped, 1.795 assertions** dengan `APP_KEY` sementara hanya untuk proses test; Pint, Vite build, dan diff-check lulus; `npm audit` 0 vulnerability. `composer audit` gagal dengan 5 advisory Symfony dan tetap menjadi blocker launch.

Angka di atas adalah bukti historis dari log pekerjaan, bukan klaim bahwa semua perintah harus dianggap lulus pada setiap checkout berikutnya. Jika kode diubah, jalankan ulang seluruh gate sebelum menyatakan selesai.

## 10. Roadmap kerja yang aman

1. Bersihkan seluruh implementasi/referensi legacy `YYNNN` dan pastikan `YYNNNN` konsisten.
2. Audit dan pulihkan kondisi database MariaDB tanpa `migrate:fresh` atau operasi destruktif.
3. Uji ulang migration, form map/GPS, foto asli, QR scan fisik, wawancara, dan admin/superadmin live.
4. Rekonsiliasi konten landing dan manual dengan scope PPDB resmi.
5. Jalankan quality gate penuh pada checkout final.
6. Tutup checklist launch hanya setelah bukti live dan approval pemilik tersedia.

## 11. Peta sumber yang sudah digabung

### Sumber utama/status

- `AGENTS.md`
- `docs/PROJECT_STATUS.md`
- `docs/TODO_ROADMAP.md`
- `docs/BUSINESS_REQUIREMENTS.md`
- `docs/PROJECT_BLUEPRINT.md`
- `docs/SYSTEM_ARCHITECTURE.md`
- `docs/DATABASE_DESIGN.md`
- `docs/SECURITY_AND_TESTING.md`
- `docs/TECHNICAL_DECISIONS.md`

### Sumber perkembangan terbaru

- `docs/phases/PHASE-11-LAUNCH-READINESS-AND-DECISIONS.md`
- `ppdbv2/skills/calm-academic-ppdb/SKILL.md`
- `ppdbv2/skills/ppdb-next-atelier/SKILL.md`

### Sumber review historis Gemini

Materi Gemini telah dibaca, diringkas, dan dihapus atas keputusan pemilik karena isinya sudah superseded oleh kode, `docs/PROJECT_STATUS.md`, dan Phase 11.

### Bukti audit/UAT terbaru yang perlu dirujuk bila diperlukan

- `docs/audits/PROJECT-COMPLETE-REVIEW-2026-11-23.md`
- `livetest/09-HASIL-UAT-LIVE-2026-11-27.md`
- `adminlivetest/03-CHECKLIST-DAN-HASIL.md`

## 12. Aturan pemeliharaan file ini

- Tambahkan perubahan faktual terbaru di bagian status, isu, atau quality gate.
- Jangan mengubah aturan bisnis hanya untuk menghilangkan konflik; minta keputusan pemilik.
- Jangan menyalin kredensial, OTP, password, token, atau NIK plaintext.
- Jangan menghapus file sumber lama tanpa persetujuan eksplisit pemilik.
- Setelah keputusan pemilik, perbarui dokumen canonical di `docs/` dan tandai bagian konflik di master ini sebagai resolved.
