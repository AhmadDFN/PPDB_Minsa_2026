# PROJECT STATUS — PPDB MINSATAMA
## Sumber Kebenaran Tunggal Progres Pengembangan Sistem

**Terakhir Diperbarui**: 18 November 2026
**Dokumentasi Versi**: 1.1.0  
**Penanggung Jawab Teknis**: Senior Full-Stack Architect & Technical Project Manager

---

## 1. STATUS FASE SAAT INI

| Parameter | Nilai Faktual |
|---|---|
| **Fase Berjalan Saat Ini** | **Fase 7 CLOSED; Fase 8 menunggu otorisasi pemilik** |
| **Implementasi Fase Terakhir** | **Fase 7 — Generator PDF Pendaftaran (CLOSED)** |
| **Status Persetujuan Fase Terakhir** | **FASE 7 SELESAI; 192 PASSED, 0 FAILED, 0 SKIPPED** |
| **Commit Baseline Sebelum Fase 7** | `54541bf` (`feat(phase-6): finalize submission and admin reopen workflow`) |
| **Fase Berikutnya yang Diusulkan** | **Fase 8 — Panel Panitia & PTSP (menunggu otorisasi)** |
| **Status Fase 7** | **SELESAI; Fase 8 belum dimulai** |

---

## 2. HASIL EKSEKUSI PENGUJIAN OTOMATIS TERAKHIR

- **Perintah Pengujian**: `php artisan test --compact`
- **Waktu Eksekusi**: 18 November 2026
- **Total Pengujian**: 192 pengujian
- **Lulus (Passed)**: **192 pengujian**
- **Gagal (Failed)**: **0 pengujian**
- **Dilewati (Skipped)**: **0 pengujian**
- **Total Asersi**: **924 asersi**
- **Durasi Eksekusi**: dicatat oleh runner pada verifikasi final
- **Kepatuhan Format Kode (Pint)**: **PASSED** (`vendor/bin/pint --dirty --format agent`)
- **Audit Dependensi Composer**: **0 kerentanan** (`composer audit`)
- **Audit Dependensi NPM**: **6 temuan high** pada rantai `picomatch` (development/build); `npm audit --omit=dev` **0 temuan**. Advisory belum menetapkan versi patched yang kompatibel.
- **Asset Build Vite**: **3 modules transformed, 86.96 kB CSS, 1.72 kB JS PASSED** (`npm.cmd run build`)

### Pemeriksaan Teknis Terbaru (18 November 2026)

- Pada awal penyelesaian gate, branch `main`, remote `origin`, dan commit terakhir `fb5924c` terverifikasi. Remote adalah `AhmadDFN/PPDB_Minsa_2026`; tidak ada rewrite sejarah Git.
- Worktree awal bersih; `package-lock.json` tidak diubah.
- Setelah pemeriksaan agregat SQLite menunjukkan tidak ada applicant/NIK tersimpan, dua key HMAC lokal dibuat secara independen dan hanya disimpan di `.env` yang di-ignore; APP_KEY tidak diubah. Runtime memverifikasi keberadaan dan perbedaan key tanpa menampilkan nilai.
- Kebijakan ukuran resmi adalah 5120 KB global/default; batas lebih rendah per dokumen diperbolehkan dan batas di atas 5120 KB ditolak. Migration tambahan menetapkan default basis data ke 5120 KB.
- Fase 5 mengimplementasikan penyimpanan privat, retrieval persyaratan, service kelengkapan, upload/replacement, revisi historis, preview/unduh terotorisasi, UI, dan integrasi dashboard.
- Fase 6 mengimplementasikan validasi kesiapan, submit transaksional/idempoten, nomor resmi, consent v1.0, audit revisi, dan admin reopen per pendaftaran dengan kedaluwarsa server-side.
- Nomor `PPDB/{tahun-ajaran}/{kode-program}/{urutan-5-digit}` menggunakan kode `REGULER`/`UNGGULAN`; satu row counter terkunci dan dibagikan per tahun ajaran-program untuk mencegah tabrakan antarperiode. Migration additive mem-backfill nilai tertinggi dari counter per-periode lama.
- Keputusan pemilik mengenai format nomor, consent, dan admin reopen telah diterapkan. Akses reopen wajib beralasan dan berkedaluwarsa, dapat ditutup lebih awal, serta seluruh tindakan diaudit.
- Verifikasi penutupan Fase 6: 179 tes lulus, 0 gagal/skip, 823 asersi. Sembilan tes fisik pada database uji MariaDB/InnoDB lulus, termasuk migrasi/backfill counter, batas default 5120 KB, submit bersamaan, rollback, dan retry.
- Perbaikan uji MySQL menemukan serta memperbaiki nama indeks unik yang melampaui batas MariaDB; migrasi dapat memulihkan tabel parsial tanpa menghapus data.
- Fase 6 ditutup setelah seluruh gate bisnis dan teknis yang disyaratkan lulus. Fase 7 kemudian diotorisasi dan diimplementasikan; Fase 8 belum dimulai.
- Fase 7 menghasilkan PDF draf ber-watermark dan PDF final setelah submit, penyimpanan privat, integritas SHA-256, token QR verifikasi publik berinformasi minimal, unduhan berotorisasi, serta status versi/outdated pada dashboard. Detail ada di `docs/phases/PHASE-7-IMPLEMENTATION-REPORT.md`.
- Verifikasi Fase 7: suite lengkap 192 tes lulus, 0 gagal/skip, 924 asersi; 13 tes PDF terarah lulus dengan 101 asersi. Pint, Vite build, dan Composer audit lulus. NPM audit tetap menunjukkan 6 temuan high development/build; audit production-only bersih.
- Migration metadata PDF dijalankan melalui rangkaian SQLite in-memory. Verifikasi fisik migration Fase 7 pada MySQL 8 belum dilakukan; hasil 9 tes MariaDB/InnoDB berasal dari gate Fase 6 dan bukan pengganti verifikasi MySQL 8.
- GD/Imagick tidak terpasang pada lingkungan lokal: foto JPG berhasil dirender oleh Dompdf; foto PNG memerlukan ekstensi GD dan bila tidak tersedia ditolak dengan pesan yang dapat ditindaklanjuti. Produksi harus menggunakan `APP_URL` HTTPS publik untuk QR final. Logo/alamat resmi belum ditemukan, sehingga kop PDF saat ini hanya menggunakan nama institusi yang terdokumentasi.
- Nilai PHP lokal saat diuji: `upload_max_filesize=2M`, `post_max_size=8M`. Pengaturan `upload_max_filesize` harus dinaikkan ke 5M untuk unggahan live ukuran maksimum; tes 5120 KB memakai file sintetis di runner Laravel.
- Temuan NPM development/build tetap terbuka; verifikasi produksi (`npm audit --omit=dev`) bersih. MariaDB lokal telah lulus; MySQL 8 produksi belum diuji terpisah.

---

## 3. MATRIX PENYELESAIAN FASE PENGEMBANGAN

| No | Fase Pengembangan | Target Deliverables | Status | Catatan Teknis |
|:---:|---|---|:---:|---|
| 0 | **Inspeksi Lingkungan** | Validasi PHP 8.3, Node 24, Composer, Git, direktori proyek | **SELESAI** | Disetujui pada awal proyek |
| 1 | **Fondasi Proyek** | Laravel 13, Tailwind v4, Desain Token, Blade Layouts & Components | **SELESAI** | Lulus build Vite & uji fondasi |
| 2 | **Database & Model** | 16 Migrasi tabel, Model Eloquent, Relasi, PHP Enums, Seeders | **SELESAI** | Relasi normal & integritas foreign keys |
| 2.1 | **Audit Keamanan & DB** | Hash NIK HMAC-SHA256, enkripsi NIK, isolasi koneksi MySQL | **SELESAI BERSYARAT** | Audit kriptografi selesai; integrasi lokal diverifikasi pada MariaDB/InnoDB |
| 3 | **Autentikasi & OTP** | Login NIK/Email, OTP HMAC kontekstual, aktivasi bertahap, rate limiting | **SELESAI** | 91 tes lulus, 302 asersi |
| 4 | **Formulir & Profil Pendaftar** | Biodata siswa, ayah, ibu, wali, riwayat TK, pilih program, hitung umur | **SELESAI** | 128 tes lulus, 438 asersi, 2 skip (MySQL), Pint lulus |
| 5 | **Manajemen Dokumen** | Upload privat berkas pendaftaran, validasi MIME/ukuran, revisi berkas | **SELESAI** | Global/default 5120 KB; upload live maksimum perlu konfigurasi PHP/web server |
| 6 | **Alur Pendaftaran PPDB** | Validasi kesiapan, consent, submit idempoten, nomor, audit revisi, admin reopen | **CLOSED** | 179 passed; 9 tes MariaDB/InnoDB lulus; keputusan bisnis diterapkan |
| 7 | **Mesin PDF Pendaftaran** | Cetak draft ber-watermark DRAFT, bukti registrasi resmi, versioning PDF, QR verifikasi, akses privat | **CLOSED** | 192 tes lulus; batas operasional GD/APP_URL dan aset branding dicatat |
| 8 | **Panel Panitia & PTSP** | Verifikasi fisik di loket, rekapitulasi jalur pendaftaran, ekspor laporan | *Menunggu otorisasi* | Jangan mulai sebelum persetujuan eksplisit pemilik |
| 9 | **Quality Assurance & Rilis** | End-to-end testing, audit keamanan menyeluruh, panduan pengguna | *Terjadwal* | Final stage sebelum go-live |

---

## 4. STATUS LINGKUNGAN BASIS DATA (MYSQL & SQLITE)

1. **Target Produksi (Production Target)**: MySQL 8.x / MariaDB (InnoDB Engine, UTF8MB4).
2. **Lingkungan Pengujian Terisolasi**: SQLite in-memory (`:memory:`) digunakan untuk continuous test suite agar pengujian berjalan cepat, atomik, dan tidak mengotori database fisik.
3. **Status Verifikasi Fisik MySQL/MariaDB**:
   - Pengujian menggunakan MariaDB 10.4.32/InnoDB melalui koneksi khusus ke database `ppdb_phase6_test`.
   - Sembilan tes migrasi/backfill, skema, default ukuran dokumen, konkurensi multi-proses, rollback, dan retry lulus. Database aplikasi tidak digunakan.
   - Kompatibilitas MySQL 8.x target produksi belum diuji terpisah; hasil MariaDB tidak diklaim sebagai tes fisik MySQL 8.x.
4. **SQLite persisten lokal**:
   - Migration additive untuk default `document_requirements.max_size_kb` menjadi 5120 telah dijalankan setelah status migration dan kesiapan database diperiksa; tidak ada migration destruktif.

---

## 5. CAPAIAN FITUR FASE 4–6

1. **Service Profil Terpusat (`ApplicantProfileService`)**:
   - Menghitung kelengkapan profil pendaftar secara realtime (`biodata`, `address`, `father`, `mother`, `guardian`, `education`, `program`).
   - Kalkulasi persentase kelengkapan 0-100% dan status terintegrasi.
   - Mengambil data periode pendaftaran yang berstatus aktif.
2. **Kalkulasi Usia Dinamis (`Applicant::calculateAgeAt`)**:
   - Usia dihitung secara matematis pada saat runtime terhadap tanggal acuan periode pendaftaran (`age_reference_date`).
   - Tidak pernah disimpan sebagai kolom permanen pada basis data, menjaga normalisasi 3NF.
3. **Pemisahan Logika Bisnis Bersih (Action Pattern)**:
   - `UpdateApplicantBiodataAction`: Pembaruan data siswa dan perlindungan NIK permanen.
   - `SaveApplicantAddressAction`: Pembaruan alamat dan preservasi leading zeros pada RT/RW.
   - `SaveApplicantParentsAction`: Penyimpanan atomik ayah, ibu (wajib), dan wali (opsional) menggunakan database transaction.
   - `SaveApplicantEducationAction`: Riwayat TK/RA/PAUD/NONE.
   - `SelectAdmissionProgramAction`: Pemilihan program pendidikan (Unggulan / Reguler) yang tersimpan sebagai `RegistrationStatus::DRAFT`.
4. **Keamanan & Validasi Masukan**:
   - 5 Form Requests independen dengan validasi berbahasa Indonesia yang bersahabat.
   - Proteksi IDOR ketat: Semua operasi mengikat context `$request->user()->applicant`.
   - Enkripsi NIK tetap aman, input NIK tidak dapat dimanipulasi melalui request form.
5. **Manajemen Dokumen (Fase 5)**:
   - Persyaratan hanya diambil untuk periode/program pendaftaran pendaftar; `DocumentCompletenessService` menghitung status dokumen wajib.
   - Upload memvalidasi ekstensi, MIME Fileinfo, SHA-256, maksimum global/default 5120 KB, dan batas per dokumen yang lebih kecil.
   - Replacement mencatat metadata revisi dan mempertahankan file historis. File baru dibersihkan jika persistensi database gagal.
   - Preview, download, dan revisi historis dilayani oleh route terotorisasi dengan pembatasan ownership; tidak ada public URL.
   - Halaman pengelolaan dokumen dan progress dashboard tersedia. Tidak ada persetujuan dokumen atau pengiriman final.
6. **Tinjauan dan Submit Pendaftaran (Fase 6)**:
   - Service kesiapan menggabungkan biodata, alamat, orang tua, pendidikan, periode/program, dokumen wajib, dan persetujuan.
   - Submit ulang idempoten; transaksi mengunci periode, pendaftaran, program, dan sequence tahun ajaran-program, lalu menyimpan consent, nomor, status `SUBMITTED`, waktu submit, serta log.
   - Setelah submit, perubahan saat periode masih terbuka menaikkan `data_revision` hanya jika ada perubahan dan menyimpan nama field tanpa nilai pribadi. Perubahan setelah periode ditutup ditolak.
   - Halaman tinjauan memasker NIK dan tidak menampilkan path file privat. PDF draf dan bukti final sekarang dihasilkan pada Fase 7 melalui disk privat; PDF final memiliki token QR verifikasi dan versioning.

---

## 6. PROTOKOL STOP CONDITION

Fase 6 **CLOSED** setelah keputusan format nomor, consent, dan admin reopen diterapkan serta seluruh 179 tes lulus, termasuk sembilan tes MariaDB/InnoDB. Fase 7 **CLOSED** dengan 192 tes regresi lulus. Fase 8 menunggu otorisasi tertulis pemilik. Lihat `docs/phases/PHASE-6-FINAL-CLOSURE-REPORT.md` dan `docs/phases/PHASE-7-IMPLEMENTATION-REPORT.md`.
