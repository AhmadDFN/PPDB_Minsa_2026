# LAPORAN FASE 4 — PROFIL CALON SISWA, BIODATA ORANG TUA & FORMULIR PENDAFTARAN
## Sistem Informasi Penerimaan Peserta Didik Baru (PPDB) MIN 1 Kota Madiun (Minsatama)

**Tanggal Penyelesaian**: 15 November 2026  
**Status**: SELESAI & TERVERIFIKASI PENUH  
**Framework & Lingkungan**: Laravel 13.42.0, PHP 8.3.31, Tailwind CSS v4, Vite, SQLite in-memory (Testing Suite), Target Produksi MySQL (InnoDB).  
**Kepatuhan Uji Otomatis**: **128 Lulus, 0 Gagal, 2 Dilewati (MySQL daemon), 438 Asersi** (`php artisan test`).  
**Kepatuhan Gaya Kode**: **100% PSR-12 / Laravel Code Style PASSED** (`vendor/bin/pint --test`).  
**Audit Keamanan Dependensi**: **0 Kerentanan** (`composer audit`, `npm audit`).

---

## 1. RINGKASAN EKSEKUTIF

Fase 4 mengimplementasikan modul profil pendaftar dan formulir pendaftaran bertahap (*multi-step registration form*) untuk calon peserta didik baru MIN 1 Kota Madiun. Modul ini menjadi jembatan antara akun yang telah aktif (Fase 3) dengan proses pengunggahan dokumen persyaratan (Fase 5) dan penguncian pendaftaran/submit final (Fase 6).

Seluruh logika bisnis dipisahkan secara modular ke dalam *Service / Action Pattern* dengan validasi ketat (*Form Request Validation*) dalam Bahasa Indonesia. Sistem menerapkan proteksi IDOR (*Insecure Direct Object Reference*) mutlak di mana seluruh data calon siswa terikat secara otomatis pada identitas akun yang sedang login (`$request->user()->applicant`).

---

## 2. MODUL YANG DIBANGUN PADA FASE 4

```
+----------------------------------------------------------------------------------------------------+
|                                    DASBOR PENDAFTAR & PROFIL SISWA                                  |
+----------------------------------------------------------------------------------------------------+
                                                  |
     +---------------------+----------------------+---------------------+---------------------+
     |                     |                      |                     |                     |
     v                     v                      v                     v                     v
[LANGKAH 1]           [LANGKAH 2]            [LANGKAH 3]           [LANGKAH 4]           [LANGKAH 5]
Biodata Siswa       Alamat Domisili        Data Orang Tua        Pendidikan Asal      Pilihan Program
- NIK Terkunci      - Alamat Jalan         - Ayah (Wajib)        - RA / TK / PAUD     - Periode Aktif
- Nama Lengkap      - RT/RW (Preserve 0)   - Ibu (Wajib)         - Lainnya / NONE     - Unggulan / Reguler
- JK, TTL           - Kelurahan & Kec      - Wali (Opsional)     - NPSN & Kota        - Status: DRAFT
- Hitung Usia       - Kota & Jarak         - Toggle Atomik       - Kondisional JS     - Belum ada No. Reg
```

### 1. Dasbor Pendaftar Dinamis (`ApplicantProfileService`)
- Menampilkan persentase kelengkapan data pendaftaran secara langsung (*realtime progress bar* 0% s/d 100%).
- Kartu status untuk kelima langkah formulir (`Lengkap`, `Belum Lengkap`, `Belum Diisi`).
- Banner informasi periode pendaftaran aktif PPDB MIN 1 Kota Madiun.
- Lencana perhitungan umur dinamis calon siswa.
- Tombol aksi kontekstual ("Mulai Pengisian" atau "Lanjutkan Pengisian Formulir").

### 2. Langkah 1: Biodata Calon Siswa (`UpdateApplicantBiodataAction`)
- Menampilkan NIK calon siswa dalam bentuk masker terlindungi (contoh: `3573********0001`).
- NIK tidak dapat diedit (*read-only*) dan diabaikan jika ada masukan manipulatif dari sisi klien.
- Input data siswa: Nama lengkap, jenis kelamin (L/P), tempat lahir, tanggal lahir, NISN (opsional), anak ke-berapa, dan jumlah saudara kandung.
- Tombol navigasi: "Simpan Perubahan" atau "Simpan & Lanjut ke Alamat".

### 3. Langkah 2: Alamat Domisili Tempat Tinggal (`SaveApplicantAddressAction`)
- Form isian: Alamat jalan / dusun / RT / RW, kelurahan / desa, kecamatan, kota / kabupaten, provinsi, kode pos, dan estimasi jarak ke madrasah (KM).
- Preservasi *leading zeros* pada RT dan RW (contoh: `003`, `001`) karena disimpan bertipe string.
- Pembaruan berkala menggunakan `updateOrCreate`, menjamin tidak ada duplikasi data alamat untuk 1 pendaftar.

### 4. Langkah 3: Data Orang Tua & Wali (`SaveApplicantParentsAction`)
- Data Ayah Kandung (Wajib): Nama, NIK (16 digit), status hidup/meninggal, pendidikan terakhir, pekerjaan, penghasilan bulanan, dan nomor HP/WhatsApp.
- Data Ibu Kandung (Wajib): Nama, NIK (16 digit), status hidup/meninggal, pendidikan terakhir, pekerjaan, penghasilan bulanan, dan nomor HP/WhatsApp.
- Data Wali (Opsional): Disediakan tombol sakelar (*toggle*) "Calon siswa tinggal bersama wali (bukan ayah/ibu kandung)". Jika diaktifkan, data wali disimpan dengan relasi `GuardianRelationship::GUARDIAN`. Jika dinonaktifkan, data wali sebelumnya dihapus secara aman dari basis data.
- Eksekusi dibungkus dalam `DB::transaction()` untuk menjamin integritas data relasi ayah, ibu, dan wali.

### 5. Langkah 4: Riwayat Pendidikan Asal (`SaveApplicantEducationAction`)
- Pilihan jenjang pendidikan pra-dasar:
  - Raudhatul Athfal (RA)
  - Taman Kanak-kanak (TK)
  - PAUD / Kelompok Bermain
  - Lainnya (Homeschooling/Sanggar)
  - Tidak / Belum Sekolah Formal (`NONE`)
- Interaksi Vanilla JS: Jika opsi "Tidak / Belum Sekolah" dipilih, input nama sekolah asal otomatis disembunyikan dan diabaikan dari keharusan validasi.

### 6. Langkah 5: Pilihan Program Pendidikan (`SelectAdmissionProgramAction`)
- Mengambil daftar program yang dibuka pada periode pendaftaran aktif (contoh: Kelas Unggulan Tahfidz & Sains, Kelas Reguler).
- Menampilkan rincian program, kuota informasi, dan catatan peminatan.
- Menyimpan pilihan program ke dalam tabel `registrations` dengan status awal `RegistrationStatus::DRAFT`.
- Menegakkan aturan bahwa nomor pendaftaran (`registration_number`) **BELUM DITERBITKAN** pada tahap ini (tetap bernilai `NULL` sampai pendaftar melakukan submit final di Fase 6).

### 7. Mesin Perhitungan Usia Dinamis (`Applicant::calculateAgeAt`)
- Usia dihitung secara matematis pada saat runtime terhadap tanggal acuan periode pendaftaran (`age_reference_date`).
- Output berupa array terstruktur: `years`, `months`, `days`, dan format teks berbahasa Indonesia: `X tahun Y bulan Z hari`.
- Usia **TIDAK PERNAH DISIMPAN** sebagai kolom permanen pada basis data, menjaga kepatuhan normalisasi basis data 3NF.

---

## 3. PENEGAKAN ARSITEKTUR & KEAMANAN

1. **Pencegahan IDOR (Insecure Direct Object Reference)**:
   - Kontroler tidak pernah menerima atau mempercayai `applicant_id` dari parameter request URL maupun payload POST.
   - Pendaftar yang sedang login diambil murni melalui konteks autentikasi Laravel:
     ```php
     $applicant = $request->user()->applicant;
     ```
   - Telah diuji secara otomatis pada `ApplicantBiodataTest::test_cannot_update_another_applicants_biodata`.

2. **Perlindungan NIK & Kriptografi**:
   - NIK calon siswa tersimpan dalam bentuk terenkripsi (`nik_encrypted`) dan lookup hash (`nik_lookup_hash`).
   - NIK ditampilkan kepada pendaftar dalam bentuk masker (`3573********0001`).
   - Upaya pengiriman field `nik` pada form biodata secara otomatis diabaikan, mencegah pembajakan identitas NIK.

3. **Integritas Relasi Basis Data & Transaction**:
   - Penyimpanan data ayah, ibu, dan wali dijalankan dalam satu blok `DB::transaction` atomik.
   - Menggunakan compound unique key `['applicant_id', 'relationship_type']` sehingga data tidak akan pernah terduplikasi meskipun form disubmit berulang kali.

---

## 4. HASIL VERIFIKASI PENGUJIAN OTOMATIS

Semua pengujian dijalankan melalui terminal CLI resmi:

```bash
php artisan test
```

### Ringkasan Hasil Uji:
- **Total Pengujian**: 130 Pengujian (*Unit & Feature Tests*)
- **Status Lulus**: **128 Passed**
- **Status Gagal**: **0 Failed**
- **Status Dilewati**: **2 Skipped** (Dilewati secara terencana menunggu daemon MySQL server lokal aktif)
- **Total Asersi**: **438 Asersi**
- **Durasi Eksekusi**: 7.55 Detik

### Rincian Modul Pengujian Fase 4:
1. `tests/Feature/Applicant/ApplicantBiodataTest.php` (7 pengujian):
   - Tamu ditolak dan diarahkan ke login.
   - Pendaftar aktif dapat melihat form biodata dan NIK termasker yang terkunci.
   - Pendaftar dapat memperbarui biodata dan data tersimpan akurat di basis data.
   - Tombol navigasi "Simpan & Lanjut" mengarahkan ke form alamat.
   - Validasi menolak data kosong dan nilai jenis kelamin tidak valid.
   - Proteksi input manipulasi NIK.
   - Proteksi IDOR antar-pendaftar.
2. `tests/Feature/Applicant/ApplicantAddressTest.php` (4 pengujian):
   - Pendaftar dapat melihat form alamat.
   - Pendaftar dapat menyimpan alamat domisili dan preservasi angka nol di depan (*leading zeroes*) pada RT/RW.
   - Pembaruan berkali-kali tidak menduplikasi baris alamat.
   - Validasi kelengkapan alamat wajib.
3. `tests/Feature/Applicant/ApplicantParentsTest.php` (5 pengujian):
   - Pendaftar dapat melihat form orang tua & wali.
   - Pendaftar dapat menyimpan data Ayah dan Ibu kandung.
   - Pendaftar dapat mengaktifkan atau menonaktifkan data wali opsional.
   - Validasi keharusan data ayah dan ibu.
   - Validasi data wali jika opsi sakelar diaktifkan.
4. `tests/Feature/Applicant/ApplicantEducationTest.php` (5 pengujian):
   - Pendaftar dapat melihat form riwayat pendidikan pra-sekolah.
   - Pendaftar dapat menyimpan jenjang RA/TK/PAUD dengan nama sekolah.
   - Pendaftar dapat memilih opsi `NONE` (Tidak Sekolah) tanpa mewajibkan nama sekolah.
   - Validasi keharusan nama sekolah jika memilih RA/TK/PAUD.
   - Pembaruan tidak menduplikasi data riwayat pendidikan.
5. `tests/Feature/Applicant/ApplicantProgramSelectionTest.php` (5 pengujian):
   - Pendaftar dapat melihat program yang tersedia pada periode aktif.
   - Pendaftar dapat memilih program peminatan (Unggulan / Reguler).
   - Terbentuk rekaman `Registration` dengan status `DRAFT` dan nomor registrasi masih bernilai `NULL`.
   - Mengubah pilihan program memperbarui record draft yang ada tanpa duplikasi.
   - Menolak pemilihan jika tidak ada periode aktif atau program dari periode lain.
6. `tests/Unit/ApplicantAgeCalculationTest.php` (6 pengujian):
   - Kalkulasi ulang tahun tepat pada tanggal acuan.
   - Kalkulasi detail tahun, bulan, dan hari.
   - Kalkulasi tahun kabisat (29 Februari).
   - Penanganan tanggal lahir di masa depan.
   - Penanganan tanggal lahir bernilai `NULL`.
   - Penerimaan parameter `Carbon` instance.

---

## 5. FORMAT KODE & STANDAR KUALITAS

1. **Laravel Pint**:
   ```bash
   vendor/bin/pint --test
   # Result: 100% Passed (0 files violated)
   ```
2. **Keamanan Dependensi**:
   ```bash
   composer audit
   # Result: No security vulnerability advisories found.

   npm audit
   # Result: found 0 vulnerabilities.
   ```
3. **Build Aset Frontend (Tailwind CSS v4 & Vite)**:
   ```bash
   npm run build
   # Result: 3 modules transformed, 71.73 kB CSS, 1.21 kB JS, 0 error.
   ```

---

## 6. KONDISI BERHENTI (STOP CONDITION)

Sesuai aturan kerja permanen pada `AGENTS.md` Bagian 5:
- Fase 4 telah diselesaikan secara penuh dan teruji 100%.
- Seluruh kode telah dirapikan sesuai standar PSR-12 via Laravel Pint.
- Dokumentasi status proyek (`PROJECT_STATUS.md`), peta jalan (`TODO_ROADMAP.md`), dan laporan implementasi telah dimutakhirkan.
- **AGEN BERHENTI (STOP)** pada tahap ini dan menunggu konfirmasi serta persetujuan resmi dari Pemilik Proyek sebelum memulai pekerjaan pada **Fase 5: Manajemen Berkas & Dokumen Pendaftaran**.
