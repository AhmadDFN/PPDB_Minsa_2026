# AUTOMATED TEST EXECUTION REPORT â€” PPDB MINSATAMA
## Laporan Hasil Pengujian Otomatis Sistem PPDB MIN 1 Kota Madiun
### Tahun Ajaran 2026/2027

**Eksekusi Regresi Terbaru**: 19 November 2026
**Fase Proyek**: **FASE 8 â€” SYSTEM INTEGRATION, UAT & PRODUCTION READINESS**
**Framework Pengujian**: PHPUnit 12.5.44 / Laravel Test Runner
**Lingkungan Default**: SQLite in-memory (`:memory:`)
**Lingkungan DB Fisik Terisolasi**: MariaDB 10.4.32 / InnoDB (`ppdb_phase6_test`)
**Total Pengujian Keseluruhan**: **203 tes**
**Status Eksekusi**: **203 LULUS, 0 DILEWATI, 0 GAGAL (100% PASS)**
**Total Asersi**: **1065 asersi**
**Durasi Eksekusi**: ~8.4 detik

---

## 1. RINGKASAN HASIL PENGUJIAN FASE 8

```
PASS  Tests\Unit\ApplicantAgeCalculationTest
PASS  Tests\Unit\ExampleTest
PASS  Tests\Unit\OtpServiceSecurityTest
PASS  Tests\Unit\SecurityAndOtpTest
PASS  Tests\Feature\Admin\RegistrationReopenTest
PASS  Tests\Feature\Applicant\ApplicantAddressTest
PASS  Tests\Feature\Applicant\ApplicantBiodataTest
PASS  Tests\Feature\Applicant\ApplicantEducationTest
PASS  Tests\Feature\Applicant\ApplicantParentsTest
PASS  Tests\Feature\Applicant\ApplicantProgramSelectionTest
PASS  Tests\Feature\Applicant\DocumentManagementTest
PASS  Tests\Feature\Applicant\RegistrationPdfTest
PASS  Tests\Feature\Applicant\RegistrationWorkflowTest
PASS  Tests\Feature\Auth\AdminLoginTest
PASS  Tests\Feature\Auth\ApplicantLoginTest
PASS  Tests\Feature\Auth\ApplicantRegistrationTest
PASS  Tests\Feature\Auth\AuthorizationAndMiddlewareTest
PASS  Tests\Feature\Auth\OtpActivationTest
PASS  Tests\Feature\Auth\PasswordRecoveryTest
PASS  Tests\Feature\Database\DatabaseSchemaAndRulesTest
PASS  Tests\Feature\Database\MySqlRegistrationConcurrencyTest
PASS  Tests\Feature\Database\MySqlSchemaCompatibilityTest
PASS  Tests\Feature\Database\SpatiePermissionSecurityTest
PASS  Tests\Feature\ExampleTest
PASS  Tests\Feature\FoundationTest
PASS  Tests\Feature\Integration\EndToEndRegistrationIntegrationTest
PASS  Tests\Feature\Performance\PerformanceAndBenchmarkTest
PASS  Tests\Feature\RegistrationPdfVerificationTest

Tests:    203 passed (1065 assertions)
Duration: 8.44s
```

---

## 2. PENGUJIAN INTEGRASI END-TO-END (SCENARIO 1â€“7)
Lokasi Berkas: `tests/Feature/Integration/EndToEndRegistrationIntegrationTest.php`
Hasil: **7 Passed, 0 Failed, 0 Skipped, 121 Assertions**

1. **Skenario 1 â€” Pendaftaran Normal (Reguler)** (`test_scenario_1_normal_registration_flow_from_account_to_pdf_and_qr`):
   - Alur utuh dari pembuatan akun, pengiriman OTP, verifikasi OTP, set kata sandi, aktivasi akun, login NIK + password.
   - Pengisian lengkap 4 form (biodata, alamat, orang tua, pendidikan asal).
   - Pemilihan program Reguler (status `DRAFT`).
   - Unggah berkas persyaratan wajib (Kartu Keluarga PDF & Pas Foto JPG).
   - Pembuatan PDF draf (watermark `DRAFT - BUKAN BUKTI RESMI`, NIK termasker).
   - Peninjauan ringkasan dan persetujuan (Consent v1.0).
   - Pengiriman pendaftaran (`SUBMITTED`), penerbitan nomor resmi `PPDB/2026-2027/REGULER/00001`.
   - Pembuatan PDF final (NIK lengkap, pas foto, klausul persetujuan historis, QR token).
   - Pengunduhan PDF final privat dan verifikasi integritas file SHA-256 serta header `%PDF-`.
   - Pemindaian QR verifikasi publik: token hash dicocokkan, status dokumen `current`, data publik minimal tanpa membocorkan PII.

2. **Skenario 2 â€” Program Unggulan & Isolasi Sequence** (`test_scenario_2_unggulan_program_sequence_isolation`):
   - Mendaftarkan pendaftar pada Program Reguler dan Program Unggulan.
   - Membuktikan nomor registrasi terbit mandiri: `PPDB/2026-2027/REGULER/00001` dan `PPDB/2026-2027/UNGGULAN/00001`.
   - Menjamin antrian nomor tidak saling tumpang tindih atau melompati urutan.

3. **Skenario 3 â€” Penolakan Berkas Tidak Lengkap** (`test_scenario_3_incomplete_submissions_are_rejected`):
   - Menolak pengiriman saat biodata belum lengkap (anak ke- / jumlah saudara kosong).
   - Menolak saat alamat, orang tua, program, atau berkas wajib belum terpenuhi.
   - Menolak saat kotak persetujuan belum dicentang.
   - Menampilkan pesan penolakan berbahasa Indonesia yang jelas.

4. **Skenario 4 â€” Perubahan Data Pasca-Submit** (`test_scenario_4_edit_after_submission_workflow`):
   - Perubahan data alamat saat periode aktif menaikkan `data_revision` dari 1 menjadi 2.
   - Nomor registrasi dan stempel waktu `submitted_at` tidak pernah berubah.
   - Dokumen PDF final versi lama secara otomatis terdeteksi berstatus `outdated`.
   - Pembuatan PDF final versi 2 berhasil, dan file PDF versi lama tetap utuh di storage privat.

5. **Skenario 5 â€” Admin Reopen & Kedaluwarsa** (`test_scenario_5_admin_reopen_and_expiration`):
   - Periode ditutup: pendaftar terkunci dari perubahan data.
   - Administrator berizin `reopen_registration` membuka akses untuk satu pendaftaran tertentu dengan alasan dan waktu kedaluwarsa.
   - Siswa terkait berhasil mengedit data dan revisi data bertambah.
   - Pendaftar lain tetap terkunci rapat.
   - Setelah waktu kedaluwarsa terlampaui, formulir siswa kembali terkunci secara otomatis.
   - Seluruh jejak audit tercatat di tabel `registration_logs` (`reopen_window_opened`, `updated_after_submission`).

6. **Skenario 6 â€” Isolasi Kepemilikan & Keamanan Multi-Tenant** (`test_scenario_6_security_ownership_and_data_isolation`):
   - Applicant A ditolak mengakses, melihat, atau mengunduh berkas dan PDF milik Applicant B (404 Scoped).
   - Injeksi manipulasi parameter `registration_number` atau `status` pada request submit diabaikan oleh sistem.
   - Endpoint publik verifikasi QR tidak memuat data sensitif pendaftar.

7. **Skenario 7 â€” Kegagalan & Pemulihan (Failure & Recovery)** (`test_scenario_7_failure_and_recovery_scenarios`):
   - Berkas teks yang dipalsukan ekstensinya menjadi `.pdf` ditolak oleh validasi MIME `finfo`.
   - Tidak ada berkas setengah jadi tersisa di storage.
   - Pengiriman ganda (*double-click submit*) ditangani secara idempoten tanpa duplikasi baris database atau pemborosan nomor urut.

---

## 3. PENGUJIAN PERFORMA & BENCHMARK
Lokasi Berkas: `tests/Feature/Performance/PerformanceAndBenchmarkTest.php`
Hasil: **3 Passed, 0 Failed, 10 Assertions**

1. **Pemeriksaan Query N+1 pada Dasbor Pendaftar**:
   - Total kueri database pada dasbor lengkap terukur 24 kueri (di bawah ambang batas ketat 30 kueri).
   - Eager loading bekerja optimal memuat relasi alamat, orang tua/wali, pendidikan asal, dokumen, dan riwayat versi PDF.
2. **Benchmark Memori dan Ukuran PDF Dompdf**:
   - Waktu render PDF: < 1.5 detik per dokumen.
   - Ukuran berkas PDF bukti pendaftaran resmi tanpa foto: 1.65 MB (karena penanaman subset font Unicode DejaVu Sans untuk keterbacaan aksara dan konsistensi lintas platform).
   - Puncak alokasi memori render: < 32 MB per proses render.
3. **Uji Beban Sintetis Melalui Runner Pengujian**:
   - Total request: 40 request bergantian antara Dasbor dan Tinjau Pendaftaran.
   - Tingkat kegagalan (*error rate*): **0.0%**.
   - Rata-rata latensi respons di runner: < 25 ms per request.

---

## 4. PENGUJIAN BASIS DATA MYSQL / MARIADB (INNODB)
Lokasi Berkas: `tests/Feature/Database/`
Hasil: **10 Passed, 0 Failed, 122 Assertions**

- Basis Data Pengujian: MariaDB 10.4.32 / InnoDB (`ppdb_phase6_test` pada `127.0.0.1:3306`).
- Kompatibilitas skema seluruh 18 tabel utama dan seluruh migration Fase 1 hingga Fase 7.
- Seluruh tabel menggunakan storage engine `InnoDB`.
- Kolom tabel `registration_pdf_versions` (`storage_disk`, `mime_type`, `file_size_bytes`, `verification_token_hash`) dan indeks unik `reg_pdf_verification_token_hash_unique` terverifikasi valid.
- Uji konkurensi multi-proses transaksi `SELECT ... FOR UPDATE` pada nomor registrasi dan counter submit multi-user lulus tanpa tabrakan atau dead-lock tak tertangani.
- **Catatan Status MySQL 8.x**: Hasil pengujian di atas berasal dari MariaDB 10.4.32/InnoDB. Verifikasi fisik mandiri pada MySQL 8.x resmi ditandai **BLOCKED** menunggu penyediaan server staging MySQL 8.x.

---

## 5. AUDIT KEAMANAN KODE & DEPENDENSI

| Alat Uji / Audit | Perintah Eksekusi | Hasil Aktual | Keterangan |
|---|---|:---:|---|
| **Laravel Pint** | `vendor/bin/pint --test` | **PASSED** | 100% kepatuhan standar gaya kode PSR-12 / Laravel Code Style. |
| **Composer Audit** | `composer audit` | **0 advisories** | Tidak ditemukan kerentanan keamanan pada paket dependensi PHP. |
| **NPM Audit (Produksi)** | `npm.cmd audit --omit=dev` | **0 vulnerabilities** | Dependensi runtime bersih tanpa celah keamanan. |
| **NPM Audit (Penuh)** | `npm.cmd audit` | **0 vulnerabilities** | Seluruh dependensi build/development bersih (0 temuan). |
| **Kompilasi Aset Vite** | `npm.cmd run build` | **PASSED** | 3 modul terkompilasi, CSS 95.99 kB, JS 1.72 kB tanpa peringatan. |
| **Pemeriksaan Git Diff** | `git diff --check` | **PASSED** | Tidak ada spasi liar atau konflik whitespace. |
