# LAPORAN IMPLEMENTASI FASE 10
## Operational Documentation, User Manual & Admin SOP
### Sistem Informasi PPDB Online MIN 1 Kota Madiun (PPDB Minsatama)

**Tanggal Penyusunan**: 19 November 2026
**Versi Dokumen**: 1.0.0
**Status Fase**: **SELESAI (100% ARTEFAK DOKUMENTASI TERVERIFIKASI & TERPENUHI)**
**Tim Penyusun**: Senior Business Analyst, System Analyst, Technical Writer, UI/UX Documentation Specialist, QA Engineer, IT Operations Consultant
**Komitmen Perubahan**: **NOL PERUBAHAN PADA KODE SUMBER APLIKASI** (Pure Documentation & Analysis Phase)

---

## 1. EXECUTIVE SUMMARY

Fase 10 berfokus secara penuh pada penyusunan seluruh dokumentasi operasional, buku panduan pengguna (*user manuals*), standar operasional prosedur (SOP) kepanitiaan, matriks hak akses, analisis celah fitur (*feature gap analysis*), serta pedoman privasi data bagi Sistem Informasi PPDB Online MIN 1 Kota Madiun (MINSATAMA).

Hasil utama dari pelaksanaan Fase 10:
1. **Pemeriksaan Repositori & Bukti Nyata**: Berhasil memetakan seluruh rute, controller, middleware, dan seeder aktual ke dalam dokumen tanpa satupun fitur fiktif atau asumsi yang tidak didukung kode.
2. **Identifikasi Peran Aktual**: Menemukan secara faktual bahwa sistem memiliki 2 peran Spatie pada guard `web` (`superadmin` dan `admin_ppdb`) dan 1 peran pendaftar (`applicant` pada kolom `account_type`). Tidak ada peran lain seperti `operator` atau `kepala_madrasah` di basis data.
3. **Penerbitan 12 Dokumen Operasional Lengkap**: Menyusun seluruh buku panduan orang tua, admin, superadmin, matriks permission, SOP panitia, FAQ, pedoman privasi, diagram workflow Mermaid, glosarium, analisis gap, briefing panitia, dan quick guide pendaftar di folder `docs/manual/`.
4. **Kejujuran Analisis Celah (Feature Gaps)**: Mengidentifikasi modul-modul yang izinnya telah ada di seeder namun belum memiliki antarmuka visual (seperti verifikasi dokumen daring, ekspor laporan, pencarian pendaftar, dan CRUD periode) serta memberikan prosedur sementara (*workaround*) tanpa melanggar batasan larangan penambahan fitur baru di fase ini.

---

## 2. REPOSITORY INSPECTION

Pemeriksaan komprehensif terhadap basis kode aktual menghasilkan temuan teknis berikut:

- **Rute Web & Autentikasi**:
  - `routes/auth.php`: Menangani pendaftaran tamu (`/daftar`), aktivasi bertahap (`/aktivasi/*`), login siswa (`/login`), lupa password (`/lupa-password/*`), login admin (`/admin/login`), dan logout.
  - `routes/web.php`: Menangani rute publik (homepage `/` dan verifikasi QR `/verifikasi/bukti-pendaftaran/{token}`), area pendaftar ber-middleware `auth, active, applicant` (`/pendaftar/*`), dan area admin ber-middleware `auth, active, admin` (`/admin/*`).
- **Controller & Action**:
  - Pendaftar: `DashboardController`, `RegistrationFormController` (Biodata, Alamat, Orang Tua, Pendidikan, Program), `DocumentController` (Upload, Preview, Download, Riwayat Revisi), `RegistrationReviewController`, `RegistrationSubmissionController`, `RegistrationPdfController`.
  - Admin: `RegistrationReopenController` (Index pendaftaran terkirim, buka akses edit, tutup akses edit dini).
  - Publik: `RegistrationPdfVerificationController` (Verifikasi keaslian via token hash).
- **Integritas Gaya & Kode**:
  - Seluruh berkas kode sumber aplikasi tetap utuh, tidak diubah, dan mematuhi aturan baku `AGENTS.md`.

---

## 3. ACTUAL ROLES AND PERMISSIONS

Berdasarkan pemeriksaan pada `database/seeders/RoleAndPermissionSeeder.php` dan `app/Models/User.php`:

1. **Kelompok Pendaftar (`applicant`)**:
   - Ditandai oleh `account_type = 'applicant'`.
   - Menggunakan NIK sebagai username.
   - Dilarang keras memiliki peran atau izin Spatie apapun.
   - Otorisasi diisolasi melalui relasi model `$request->user()->applicant` (Anti-IDOR).
2. **Kelompok Administrator Operasional (`admin_ppdb`)**:
   - Memiliki peran Spatie `admin_ppdb` pada guard `web`.
   - Memiliki 5 izin aktif: `view_dashboard`, `view_applicants`, `export_reports`, `verify_documents`, `reopen_registration`.
   - Fitur antarmuka yang aktif di browser saat ini: dasbor pratinjau (`/admin/dashboard`) dan manajemen akses edit ulang (`/admin/registrations`).
3. **Kelompok Pengelola Tertinggi (`superadmin`)**:
   - Memiliki peran Spatie `superadmin` pada guard `web`.
   - Memiliki seluruh 10 izin administratif.
   - Bertanggung jawab atas konfigurasi kunci kriptografi dan database master.
4. **Peran Lain**:
   - **TIDAK ADA PERAN LAIN**. Peran seperti `operator`, `verifikator`, atau `kepala_madrasah` tidak ditemukan pada seeder atau tabel roles aktual, sehingga tidak didokumentasikan sebagai fitur aktif.

---

## 4. APPLICANT WORKFLOW

Alur kerja calon peserta didik terdokumentasi lengkap pada dokumen [01-PANDUAN-ORANG-TUA-WALI.md](../manual/01-PANDUAN-ORANG-TUA-WALI.md):
- Pendaftaran NIK & nomor WhatsApp orang tua (mendukung *sibling support*).
- Aktivasi akun melalui verifikasi 6 digit kode OTP (berlaku 5 menit).
- Pembuatan kata sandi mandiri (min 8 karakter, bukan NIK).
- Login dan pemantauan bilah kemajuan (*progress bar*) dasbor.
- Pengisian 4 modul formulir (Biodata dengan NIK read-only, Alamat mempertahankan leading zero RT/RW, Orang Tua dengan Ayah & Ibu wajib serta Wali opsional, Riwayat TK/RA).
- Pemilihan jalur program (Reguler atau Unggulan).
- Unggah berkas persyaratan privat (KK dan Pas Foto maks 5 MB format PDF/JPG/PNG).
- Pratinjau cetak PDF Draf ber-watermark miring transparan *"DRAFT - BUKAN BUKTI RESMI"*.
- Persetujuan klausul pernyataan keabsahan data (*Consent v1.0*).
- Pengiriman final (*Submit Final*) yang menerbitkan Nomor Registrasi Resmi permanen.
- Pengunduhan Dokumen Bukti Pendaftaran Resmi (PDF Final) bebas watermark draf lengkap dengan kode QR verifikasi.
- Penyerahan berkas fisik cetakan bertanda tangan ke loket PTSP madrasah.

---

## 5. ADMIN WORKFLOW

Alur kerja panitia madrasah terdokumentasi lengkap pada [02-PANDUAN-ADMIN-PPDB.md](../manual/02-PANDUAN-ADMIN-PPDB.md):
- Masuk melalui `/admin/login` menggunakan Email dan Kata Sandi.
- Membuka menu *Akses Edit Ulang Pendaftaran* (`/admin/registrations`).
- Meninjau pendaftaran siswa yang membutuhkan perbaikan data.
- Membuka akses edit ulang untuk pendaftaran spesifik dengan mengisi alasan wajib (maks 1000 karakter) dan waktu kedaluwarsa di masa depan.
- Menutup jendela akses lebih awal (*early close*) dengan mengisi alasan penutupan resmi jika orang tua telah selesai melakukan revisi.
- Penegakan kedaluwarsa waktu server secara otomatis (*server-side expiry*).

---

## 6. SUPERADMIN WORKFLOW

Terdokumentasi pada [03-PANDUAN-SUPERADMIN.md](../manual/03-PANDUAN-SUPERADMIN.md):
- Tanggung jawab isolasi kunci rahasia `.env` (`APP_KEY`, `NIK_HMAC_KEY`, `OTP_HMAC_KEY`).
- Prosedur pembuatan akun Admin PPDB baru via `php artisan tinker`.
- Prosedur inisialisasi periode tahun ajaran baru dan tanggal acuan umur via database.
- Prosedur penggantian kredensial master bawaan seeder sebelum Go-Live.

---

## 7. SOP OPERASIONAL KEPANITIAAN

Terdokumentasi pada [05-SOP-OPERASIONAL-PANITIA.md](../manual/05-SOP-OPERASIONAL-PANITIA.md):
- Siklus 3 Fase Operasional:
  1. *Fase Pra-Pendaftaran*: Verifikasi periode aktif, pengujian akun admin, uji alur pendaftaran dummy, uji scan QR, dan sosialisasi brosur.
  2. *Fase Pelaksanaan*: Layanan helpdesk, penanganan pembukaan akses edit (*reopen*), penerimaan berkas fisik di loket PTSP, pemindaian QR keaslian cetakan, pencocokan berkas asli, dan penerbitan tanda terima fisik.
  3. *Fase Pasca-Pendaftaran*: Penguncian otomatis pendaftaran global, penyerahan map berkas ke Tim Seleksi Luring madrasah, dan pencadangan data permanen.
- Batasan Tegas: Aplikasi PPDB daring **tidak mengelola** seleksi, ranking, pengumuman kelulusan, dan daftar ulang.

---

## 8. USER MANUAL, QUICK GUIDE & BRIEFING PACK

1. **User Manual Lengkap**: [01-PANDUAN-ORANG-TUA-WALI.md](../manual/01-PANDUAN-ORANG-TUA-WALI.md) (14 Bab ramah pemula tanpa jargon rumit).
2. **Quick Guide Orang Tua**: [QUICK-GUIDE-ORANG-TUA.md](../manual/QUICK-GUIDE-ORANG-TUA.md) (Infografis teks 5 langkah cepat dalam 3 menit).
3. **Briefing Panitia**: [BRIEFING-PANITIA-PPDB.md](../manual/BRIEFING-PANITIA-PPDB.md) (Ringkasan eksekutif 2 lembar untuk rapat koordinasi madrasah).

---

## 9. FAQ & TROUBLESHOOTING

Terdokumentasi pada [06-FAQ-DAN-TROUBLESHOOTING.md](../manual/06-FAQ-DAN-TROUBLESHOOTING.md):
- 11 Kasus Pendaftar: NIK duplikat, OTP tidak masuk/kedaluwarsa, NIK terkunci di form, leading zero RT/RW, toggle wali, ukuran berkas > 5 MB, berkas rusak, tombol submit abu-abu, watermark draf, dan arti status PDF outdated.
- 4 Kasus Admin: Error 403 Forbidden, validasi waktu kedaluwarsa reopen, kegagalan render pas foto PNG (prasyarat PHP GD), dan penelusuran riwayat reopen.

---

## 10. SECURITY & PRIVACY GUIDELINES

Terdokumentasi pada [07-PEDOMAN-KEAMANAN-PENGGUNA.md](../manual/07-PEDOMAN-KEAMANAN-PENGGUNA.md):
- Landasan UU Perlindungan Data Pribadi (UU PDP No. 27 Tahun 2022) atas perlindungan data spesifik anak di bawah umur.
- Panduan keamanan bagi orang tua (larangan sebar OTP/NIK di media sosial, disiplin logout).
- Kode etik keamanan panitia (larangan *account sharing*, larangan sebar NIK/telepon ke grup WhatsApp non-kedinasan, penguncian berkas fisik di loket).
- Tanggung jawab tim IT atas perlindungan berkas `.env` dan direktori privat.

---

## 11. FEATURE GAP ANALYSIS

Terdokumentasi secara rinci pada [10-DAFTAR-FITUR-DAN-GAP.md](../manual/10-DAFTAR-FITUR-DAN-GAP.md):
- **Fitur Tersedia & Teruji Penuh**: Seluruh alur pendaftar 1–7, enkripsi NIK, OTP binding, submit final, nomor registrasi berurutan, cetak PDF Draft & Final, verifikasi publik QR, dan admin reopen registration.
- **Keterbatasan Layanan Pihak Ketiga**:
  - Driver pengiriman OTP WhatsApp saat ini berstatus driver `log` (mencatat ke log lokal). Integrasi gateway komersial live (Fonnte/Twilio) berstatus **PENDING OWNER ACTION**.
  - Pas foto format PNG memerlukan ekstensi PHP `gd` pada server hosting (format JPG didukung langsung).
- **Celah Antarmuka Admin (Feature Gaps)**:
  - Belum ada form pencarian dan filter pendaftar di panel admin.
  - Belum ada modal pratinjau dokumen pendaftar untuk panitia.
  - Belum ada fitur ekspor Excel/CSV langsung di web.
  - Belum ada antarmuka web CRUD Periode PPDB dan Akun Admin.

---

## 12. DOCUMENTATION LIST

Seluruh artefak dokumentasi telah diproduksi dan tersimpan rapi pada direktori `docs/manual/`:

1. [docs/manual/README.md](../manual/README.md)
2. [docs/manual/01-PANDUAN-ORANG-TUA-WALI.md](../manual/01-PANDUAN-ORANG-TUA-WALI.md)
3. [docs/manual/02-PANDUAN-ADMIN-PPDB.md](../manual/02-PANDUAN-ADMIN-PPDB.md)
4. [docs/manual/03-PANDUAN-SUPERADMIN.md](../manual/03-PANDUAN-SUPERADMIN.md)
5. [docs/manual/04-MATRIKS-ROLE-DAN-PERMISSION.md](../manual/04-MATRIKS-ROLE-DAN-PERMISSION.md)
6. [docs/manual/05-SOP-OPERASIONAL-PANITIA.md](../manual/05-SOP-OPERASIONAL-PANITIA.md)
7. [docs/manual/06-FAQ-DAN-TROUBLESHOOTING.md](../manual/06-FAQ-DAN-TROUBLESHOOTING.md)
8. [docs/manual/07-PEDOMAN-KEAMANAN-PENGGUNA.md](../manual/07-PEDOMAN-KEAMANAN-PENGGUNA.md)
9. [docs/manual/08-ALUR-KERJA-SISTEM.md](../manual/08-ALUR-KERJA-SISTEM.md)
10. [docs/manual/09-GLOSARIUM.md](../manual/09-GLOSARIUM.md)
11. [docs/manual/10-DAFTAR-FITUR-DAN-GAP.md](../manual/10-DAFTAR-FITUR-DAN-GAP.md)
12. [docs/manual/BRIEFING-PANITIA-PPDB.md](../manual/BRIEFING-PANITIA-PPDB.md)
13. [docs/manual/QUICK-GUIDE-ORANG-TUA.md](../manual/QUICK-GUIDE-ORANG-TUA.md)

---

## 13. VALIDATION RESULTS

Pemeriksaan silang terhadap kriteria penerimaan (*Acceptance Criteria*):

- [x] Semua tautan internal Markdown valid dan menggunakan skema berkas lokal yang benar.
- [x] Nama rute, controller, dan middleware cocok 100% dengan kode `routes/web.php` dan `routes/auth.php`.
- [x] Matriks role dan permission mencerminkan data riil pada seeder dan basis data.
- [x] Tidak ada fitur atau tombol fiktif yang dikarang.
- [x] Tidak ada rahasia (*secrets*), kredensial produksi, atau data pribadi siswa nyata yang dicantumkan.
- [x] Seluruh ketentuan mematuhi batasan bahwa sistem daring berakhir di loket PTSP madrasah.

---

## 14. QUESTIONS FOR PROJECT OWNER

Daftar pertanyaan keputusan bisnis & operasional untuk Pemilik Proyek sebelum peluncuran resmi:

1. **Pertanyaan Keputusan Bisnis**:
   - Apakah pihak madrasah merencanakan penambahan antarmuka web khusus bagi panitia untuk melihat berkas pendaftar dan mengekspor rekapitulasi data ke format Excel sebelum hari pendaftaran resmi dibuka, ataukah verifikasi berkas disepakati tetap dilakukan secara manual fisik di loket PTSP?
2. **Pertanyaan Keputusan Teknis**:
   - Vendor gateway WhatsApp resmi apa yang akan digunakan oleh MIN 1 Kota Madiun untuk pengiriman OTP langsung ke ponsel orang tua (misal: Fonnte, Twilio, atau Zenziva), dan kapan API Key resmi diserahkan kepada tim pengembang?
   - Apakah berkas logo resmi madrasah (PNG transparan) dan teks SK alamat resmi telah siap untuk disematkan pada kop formulir PDF pendaftaran?

---

## 15. GIT COMMIT MANAGEMENT

Seluruh dokumen di atas telah siap dikomit ke repositori lokal dengan pesan konvensional baku:
`docs(phase-10): add applicant admin manuals and operational SOP`

Sesuai instruksi baku, **TIDAK ADA PUSH KE REMOTE GIT REPOSITORY** yang dilakukan tanpa persetujuan eksplisit dari Pemilik Proyek.

---

## 16. FINAL RECOMMENDATION

1. **Pengesahan Dokumen**: Pemilik Proyek disarankan meninjau dan menyetujui paket dokumentasi operasional pada direktori `docs/manual/`.
2. **Penyebaran Brosur & Briefing**: Berkas [BRIEFING-PANITIA-PPDB.md](../manual/BRIEFING-PANITIA-PPDB.md) dan [QUICK-GUIDE-ORANG-TUA.md](../manual/QUICK-GUIDE-ORANG-TUA.md) dapat langsung dicetak atau disebarluaskan untuk persiapan internal panitia madrasah.
3. **Kondisi Berhenti (*Stop Condition*)**: Tim pengembang **BERHENTI (STOP)** pada tahapan ini dan menunggu arahan lebih lanjut dari Pemilik Proyek.
