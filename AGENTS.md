# AGENTS.md — PEDOMAN TATA KELOLA & PROTOKOL TETAP CODING AGENT
## Sistem Informasi PPDB MIN 1 Kota Madiun (PPDB Minsatama)

Pedoman wajib dan instruksi permanen coding agent/developer di repositori ini. Setiap sesi kerja **WAJIB MEMATUHI** seluruh aturan ini tanpa kecuali. Rujuk berkas di `docs/` sesuai kebutuhan tugas.

---

## 1. PROJECT OVERVIEW
- **Sistem**: PPDB online calon peserta didik baru kelas 1 MI MIN 1 Kota Madiun.
- **Zona Waktu & Bahasa**: `Asia/Jakarta` (WIB). Antarmuka menggunakan **Bahasa Indonesia**.
- **Jam Layanan PTSP**: Pelayanan loket fisik dan helpdesk verifikasi pukul **07.00 – 15.00 WIB** (hari kerja).
- **Batasan Lingkup**:
  - Sistem mencakup pendaftaran, verifikasi berkas, penomoran antrean, penjadwalan, dan pelaksanaan wawancara PPDB.
  - Alur layanan pendaftar berlanjut sampai bukti pendaftaran dan proses wawancara selesai sesuai role/permission.
  - Sistem **TIDAK MENGELOLA** seleksi siswa, perankingan, pengumuman kelulusan/penolakan, maupun daftar ulang (*out-of-scope*).
  - **TIDAK ADA** pembatasan kuota pendaftaran pada sistem.
- **Referensi Detail**: `docs/PROJECT_BLUEPRINT.md`, `docs/BUSINESS_REQUIREMENTS.md`, `docs/PROJECT_STATUS.md`.

---

## 2. TECHNOLOGY STACK
- **Backend**: Laravel 13.x, PHP 8.3+.
- **Database**: MySQL/MariaDB (InnoDB, UTF8MB4) untuk produksi; SQLite `:memory:` untuk test suite.
- **ORM & Otorisasi**: Eloquent ORM & Spatie Laravel Permission 8.5+ (guard `web`).
- **Frontend**: Blade Templates + Tailwind CSS v4 + Vanilla JS murni + Vite.
- **WhatsApp Gateway**: Fonnte API (`FONNTE_TOKEN` via environment).
- **LARANGAN KERAS**: Dilarang memperkenalkan Bootstrap, Inertia.js, Vue, React, Svelte, Angular, atau framework SPA lainnya.

---

## 3. ARCHITECTURE & CODING STANDARDS
- **Pola MVC**: Terapkan *Service/Action Pattern* untuk logika bisnis inti (Thin Controllers).
- **Validasi**: Seluruh input wajib melalui *Form Request Validation* dengan pesan berbahasa Indonesia.
- **Ketahanan Sistem**: Fail-closed untuk konfigurasi keamanan. Integrasi Fonnte API wajib menerapkan timeout handling dan graceful degradation.
- **Gaya Kode**: Standar Laravel Pint (`vendor/bin/pint --test`).
- **Referensi Detail**: `docs/SYSTEM_ARCHITECTURE.md`, `docs/TECHNICAL_DECISIONS.md`.

---

## 4. BUSINESS RULES & INVARIANTS
1. **Satu Akun Satu Siswa**: Satu NIK = satu akun global (NIK adalah username). No WA orang tua dapat digunakan kembali untuk saudara kandung (*sibling support*).
2. **Status Pendaftaran**: Hanya mengenal **`DRAFT`** dan **`SUBMITTED`**. Submit formulir bersifat atomik dan idempoten.
3. **Perhitungan Usia**: Usia dihitung dinamis (*runtime*) terhadap `age_reference_date` periode aktif. **Dilarang disimpan sebagai kolom database**. Tidak ada diskualifikasi umur otomatis.
4. **Dokumen Persyaratan**: Maksimal global/default **5120 KB (5 MB)**. Tersimpan privat di `storage/app/private/ppdb/documents`. Penggantian berkas mencatat riwayat revisi.
5. **Bukti Pendaftaran PDF**: Draft ber-watermark "DRAFT". Status submitted menerbitkan Bukti Pendaftaran resmi ber-QR verifikasi minimalis (tanpa PII). Bukan bukti kelulusan.
6. **Keputusan Fase 10.4 (STATUS: `APPROVED — PENDING IMPLEMENTATION`)**:
   - Form registrasi hanya: NIK, nama lengkap, nomor WA, dan persetujuan orang tua/wali.
   - Password dibuat otomatis oleh sistem (random secure password).
   - Password sementara dikirim via WhatsApp hanya setelah OTP berhasil diverifikasi.
   - Wajib ganti password pada login pertama (`must_change_password`).
   - OTP dan password sementara dikirim dalam pesan WhatsApp terpisah.
   - Format nomor registrasi/antrean resmi: **`YYNNNN`** (contoh `270001`, sequence global tiap tahun penerimaan, bersama seluruh program, maksimal 9.999, unique business identifier, pertahankan internal PK/FK database).
   - Nomor `YYNNNN` diterbitkan secara atomik saat pendaftaran disahkan panitia setelah seluruh dokumen wajib `APPROVED`, lalu digunakan untuk pemetaan batch wawancara.
   - Nama institusi dapat diatur melalui konfigurasi Superadmin.
   - Pending registration recovery wajib mempertahankan akun dan nomor registrasi yang sama.
   *(Dilarang menandai poin di atas sebagai IMPLEMENTED sebelum kode dan pengujian membuktikannya).*
- **Referensi Detail**: `docs/BUSINESS_REQUIREMENTS.md`, `docs/manual/`.

---

## 5. SECURITY REQUIREMENTS
1. **Keamanan NIK**: NIK wajib dienkripsi (`nik_encrypted` via AES-256-CBC). Lookup database wajib menggunakan HMAC-SHA256 (`nik_lookup_hash`) dengan `NIK_HMAC_KEY`. Dilarang mencari via plaintext NIK. Masking NIK pada UI umum (`3573********0001`).
2. **Keamanan OTP**: 6 digit, HMAC-SHA256 (`OTP_HMAC_KEY`), masa berlaku 5 menit, maksimal 5 percobaan salah. Konteks aktivasi dan lupa password terpisah. Verifikasi via `hash_equals()` dan `lockForUpdate()`.
3. **Autentikasi & Guard**: Satu tabel `users`, satu guard `web`. Pendaftar login via NIK (`applicant`), Admin via Email (`admin`). Calon siswa dilarang keras memiliki role Spatie atau akses `/admin/*`. Akun `PENDING_ACTIVATION` atau `SUSPENDED` ditolak login.
4. **Pencegahan Kebocoran**: Dilarang meminta/menampilkan kredensial di chat. Dilarang mencatat plaintext NIK, OTP, password, token Fonnte, atau Authorization header ke file log atau respons web/API.
- **Referensi Detail**: `docs/SECURITY_AND_TESTING.md`.

---

## 6. DATABASE & MIGRATION RULES
1. **Skema & Engine**: MySQL/MariaDB (InnoDB, UTF8MB4). Pengujian menggunakan SQLite `:memory:`.
2. **Kaidah Migrasi**: Migrasi wajib *additive* dan *backward-compatible*.
3. **Integritas Relasi**: Pertahankan unsigned big integer PK dan FK di seluruh tabel. Dilarang menonaktifkan FK checks atau unique constraint tanpa persetujuan.
4. **Identifier Indeks**: Berikan nama indeks eksplisit dan pendek guna mencegah batas panjang identifier MariaDB 10.4.
5. **Anti-Destruktif**: Dilarang menjalankan `migrate:fresh` pada basis data staging atau produksi.
- **Referensi Detail**: `docs/DATABASE_DESIGN.md`.

---

## 7. FRONTEND STANDARDS
1. **Stack UI**: Blade + Tailwind CSS v4 + Vanilla JS murni.
2. **Kualitas Desain**: Mengacu pada `.agents/skills/design-taste-frontend/SKILL.md` (tipografi modern, anti-slop, visual profesional, ramah pengguna).
3. **Isolasi Layout**: Halaman pendaftar internal wajib memakai `<x-layouts.applicant>` dan `<x-navigation.applicant-navbar>`. Dilarang mencampur komponen landing page (`#alur`, `#ptsp`) ke portal pendaftar.
4. **Jam Layanan**: Seluruh teks jam operasional PTSP menggunakan **07.00 – 15.00 WIB**.

---

## 8. TESTING & QUALITY GATES
Sebelum menyelesaikan sesi, jalankan gate pengujian wajib:
1. `php artisan test`: Seluruh automated test wajib lulus 100%.
2. `vendor/bin/pint --test`: Kepatuhan gaya kode 100%.
3. `npm.cmd run build`: Asset frontend Vite terkompilasi bersih.
4. `composer audit` & `npm.cmd audit`: Bebas celah keamanan dependensi kritis/tinggi.
5. `git diff --check`: Bebas whitespace error atau konflik merge.
*Dilarang mengklaim tes lulus tanpa menjalankannya di terminal. Laporkan hasil faktual (passed, failed, skipped, assertions).*

---

## 9. DOCUMENTATION RULES
1. **Single Source of Truth**: `docs/PROJECT_STATUS.md` adalah sumber kebenaran tunggal status proyek.
2. **Sinkronisasi Progres**: Setiap menyelesaikan fase atau mengubah behavior sistem, segera perbarui `docs/PROJECT_STATUS.md`, `docs/TODO_ROADMAP.md`, dan berkas di `docs/phases/`.
3. **Efisiensi Konteks**: Jangan membaca ulang seluruh berkas `docs/` jika tidak relevan. Baca hanya berkas terkait ruang lingkup aktif.

---

## 10. GIT & DEPLOYMENT SAFETY
Tanpa persetujuan tertulis Pemilik Proyek, agen **DILARANG KERAS**:
1. Menjalankan `git push` ke repositori remote.
2. Deployment ke staging (Hostinger) atau produksi (aaPanel VPS).
3. Menghapus data, tabel, atau file penting (`rm -rf`, `DROP TABLE`).
4. Mengubah konfigurasi produksi (`.env.production`, DNS, web server).
5. Membuat commit yang melompati review proyek.
Gunakan format pesan **Conventional Commits** (`feat:`, `fix:`, `docs:`, dll.).

---

## 11. AI WORKING PROTOCOL
Setiap agen AI wajib mengikuti 16 urutan kerja berikut:
1. **Baca AGENTS.md** pada awal sesi kerja.
2. **Periksa Status Git**: Jalankan `git status` dan `git log -1`.
3. **Baca Dokumentasi Relevan**: Buka berkas spesifik di `docs/` yang berkaitan dengan tugas.
4. **Audit Implementasi Aktual**: Periksa kode dan tes eksisting sebelum menulis kode baru.
5. **Analisis Dampak**: Petakan dependensi dan komponen yang berpotensi terpengaruh.
6. **Rencana Ringkas**: Susun langkah pengerjaan terarah sebelum eksekusi.
7. **Fokus Ruang Lingkup**: Kerjakan **HANYA** lingkup tugas yang diminta.
8. **Reuse Komponen**: Manfaatkan service, action, model, enum, controller, dan Blade components eksisting.
9. **Cegah Duplikasi**: Dilarang menduplikasi pola arsitektur atau fungsi yang sudah tersedia.
10. **Verifikasi Asumsi**: Dilarang berasumsi nama kolom, relasi, rute, atau parameter tanpa cek kode.
11. **Gunakan Data Riil**: Dilarang memakai dummy data menggantikan data fungsional aplikasi.
12. **Uji Bertahap**: Jalankan pengujian relevan saat mengembangkan kode.
13. **Regression Test Menyeluruh**: Jalankan `php artisan test` penuh di akhir pekerjaan.
14. **Perbarui Dokumentasi**: Catat perubahan teknis pada dokumentasi terkait jika behavior berubah.
15. **Laporan Transparan**: Berikan laporan terstruktur dengan daftar file berubah dan bukti tes.
16. **Kondisi Berhenti (Stop Condition)**: **BERHENTI (STOP)** setelah ruang lingkup selesai. Tunggu persetujuan pemilik sebelum fase berikutnya.
*Resolusi Konflik*: Jika menemukan pertentangan antara dokumentasi dan kode aktual, audit keduanya, jangan ubah aturan bisnis sepihak, laporkan ke Pemilik Proyek, dan ikuti instruksi terbaru yang disahkan pemilik.

---

## 12. OWNER APPROVAL REQUIREMENTS
Tindakan berikut **WAJIB** memperoleh persetujuan eksplisit tertulis dari Pemilik Proyek:
1. Memulai fase pengembangan baru atau melompati urutan fase.
2. Mengubah atau menambah aturan bisnis (*business requirements*).
3. Migrasi database yang mengubah struktur tabel eksisting (*breaking/destructive*).
4. Menjalankan `git push` ke repositori remote.
5. Deployment ke server staging (Hostinger) atau produksi (aaPanel).
6. Mengubah kunci rahasia (`APP_KEY`, `NIK_HMAC_KEY`, `OTP_HMAC_KEY`, `FONNTE_TOKEN`).
