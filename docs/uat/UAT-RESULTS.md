# USER ACCEPTANCE TESTING (UAT) RESULTS
## Sistem Informasi PPDB Online MIN 1 Kota Madiun (PPDB Minsatama)
### Tahun Ajaran 2026/2027

**Versi Dokumen**: 1.0.0  
**Tanggal Evaluasi**: 19 November 2026  
**Target Pengujian**: Lingkungan Integrasi Lokal & Staging Hostinger (`sleek-forestgreen-mink-13.hostingersite.com`)  
**Referensi**: Berakar dari skenario resmi pada [docs/uat/UAT-TEST-PLAN.md](file:///D:/Dany/PPDB/PPDB_Minsa_2026/docs/uat/UAT-TEST-PLAN.md)  
**Status Evaluasi**:
- **Otomasi & E2E Integration Suite**: **203/203 PASSED (100%)**
- **Verifikasi Sintetis Staging**: **VERIFIED / READY**
- **Sesi Pengujian Fisik Bersama Panitia Madrasah**: **PENDING OWNER ACTION (Menunggu Jadwal Sesi Bersama)**

---

> [!IMPORTANT]
> **KEBIJAKAN KEJUJURAN HASIL UAT**:
> Sesuai standar tata kelola proyek `AGENTS.md`, status pada tabel di bawah ini memisahkan secara tegas antara **Hasil Verifikasi Otomasi/Sintetis Tim Teknis** dengan **Sesi Pengujian Fisik Sekolah**. Kolom tanda tangan fisik pengesahan madrasah tetap berstatus **PENDING** hingga panitia madrasah dan perwakilan wali murid mengujinya langsung di lokasi madrasah.

---

## 1. RINGKASAN HASIL EVALUASI SKENARIO PENDAFTAR (APPLICANT)

| Test ID | Skenario Pengujian | Hasil Pengujian Otomasi / Sintetis | Hasil Uji Fisik Sekolah | Status Akhir |
|:---:|---|---|:---:|:---:|
| **UAT-APP-01** | Pendaftaran Akun Baru Calon Siswa | NIK di-hash & dienkripsi, OTP dikirim via mock WhatsApp/log, redirect ke halaman OTP. | Menunggu Sesi Fisik | **READY FOR UAT** |
| **UAT-APP-02** | Verifikasi OTP WhatsApp (Aktivasi) | OTP 6-digit divalidasi via HMAC `lockForUpdate()`, batas 5 menit ditegakkan, redirect ke form password. | Menunggu Sesi Fisik | **READY FOR UAT** |
| **UAT-APP-03** | Pembuatan Kata Sandi Akun | Validasi konfirmasi password min 8 karakter, status user beralih dari `PENDING_ACTIVATION` ke `ACTIVE`. | Menunggu Sesi Fisik | **READY FOR UAT** |
| **UAT-APP-04** | Masuk (Login) Akun Calon Siswa | Autentikasi NIK plaintext dicocokkan via lookup hash, NIK ditampilkan termasker `3573********0001` pada UI. | Menunggu Sesi Fisik | **READY FOR UAT** |
| **UAT-APP-05** | Pengisian Formulir Langkah 1: Biodata Siswa | NIK terkunci read-only, nama, tempat/tgl lahir, anak ke-, dan jml saudara tersimpan rapi. Progress bar bertambah. | Menunggu Sesi Fisik | **READY FOR UAT** |
| **UAT-APP-06** | Pengisian Formulir Langkah 2: Alamat Domisili | Alamat tersimpan, RT/RW mempertahankan leading zero (misal `002`), koordinat dan jarak tersimpan. | Menunggu Sesi Fisik | **READY FOR UAT** |
| **UAT-APP-07** | Pengisian Formulir Langkah 3: Data Orang Tua | Data Ayah dan Ibu tersimpan, validasi telepon & NIK ortu, toggle wali opsional tersimpan atomik. | Menunggu Sesi Fisik | **READY FOR UAT** |
| **UAT-APP-08** | Pengisian Formulir Langkah 4: Riwayat TK/RA Asal | Jenjang RA/TK/PAUD/NONE tersimpan, NPSN opsional, status modul pendidikan "Lengkap". | Menunggu Sesi Fisik | **READY FOR UAT** |
| **UAT-APP-09** | Pemilihan Program Pendidikan | Pilihan program (Reguler/Unggulan) tersimpan berstatus `DRAFT`, badge program tampil di dasbor. | Menunggu Sesi Fisik | **READY FOR UAT** |
| **UAT-APP-10** | Unggah Dokumen Persyaratan | Berkas tersimpan ke private storage (`storage/app/private/documents`), validasi MIME finfo aktif, SHA-256 tersimpan. | Menunggu Sesi Fisik | **READY FOR UAT** |
| **UAT-APP-11** | Penerbitan dan Pengecekan PDF Draf | PDF draft memuat watermark diagonal transparan **"DRAFT - BUKAN BUKTI RESMI"**, NIK termasker. | Menunggu Sesi Fisik | **READY FOR UAT** |
| **UAT-APP-12** | Peninjauan Ringkasan & Submit Final | Persetujuan consent v1.0 disimpan, status menjadi `SUBMITTED`, nomor unik `PPDB/2026-2027/...` terbit idempoten. | Menunggu Sesi Fisik | **READY FOR UAT** |
| **UAT-APP-13** | Pengunduhan Bukti Registrasi (PDF Final) | PDF final terbit resmi tanpa watermark draft, kop madrasah, NIK lengkap, foto ter-embed, QR code valid. | Menunggu Sesi Fisik | **READY FOR UAT** |
| **UAT-APP-14** | Pemindaian Kode QR Verifikasi | Tautan publik menampilkan status valid, nomor registrasi, tanggal terbit, fingerprint SHA-256 tanpa bocoran PII. | Menunggu Sesi Fisik | **READY FOR UAT** |
| **UAT-APP-15** | Perubahan Data Pasca-Submit (Periode Terbuka) | Perubahan menaikkan `data_revision`, PDF lama ditandai Outdated, tombol cetak ulang tersedia untuk rilis PDF baru. | Menunggu Sesi Fisik | **READY FOR UAT** |

---

## 2. RINGKASAN HASIL EVALUASI SKENARIO ADMINISTRATOR / PANITIA (ADMIN)

| Test ID | Skenario Pengujian | Hasil Pengujian Otomasi / Sintetis | Hasil Uji Fisik Sekolah | Status Akhir |
|:---:|---|---|:---:|:---:|
| **UAT-ADM-01** | Masuk (Login) Akun Administrator | Login berbasis email berhasil, guard web terisolasi dari akun pendaftar, redirect ke panel admin. | Menunggu Sesi Fisik | **READY FOR UAT** |
| **UAT-ADM-02** | Pemeriksaan Hak Akses (Permission Check) | Akses route tanpa permission ditolak seketika dengan respons HTTP 403 Forbidden. | Menunggu Sesi Fisik | **READY FOR UAT** |
| **UAT-ADM-03** | Pembukaan Akses Edit Pendaftar (Reopen) | Admin berizin berhasil membuka akses edit dengan alasan dan waktu kedaluwarsa, terekam di audit trail. | Menunggu Sesi Fisik | **READY FOR UAT** |
| **UAT-ADM-04** | Penutupan Akses Edit Lebih Awal (Early Close) | Jendela reopen ditutup seketika oleh admin dengan alasan, form pendaftar langsung terkunci kembali. | Menunggu Sesi Fisik | **READY FOR UAT** |
| **UAT-ADM-05** | Penguncian Otomatis Pasca-Kedaluwarsa | Upaya simpan setelah `expires_at` ditolak server, nomor registrasi dan status submit awal tetap aman. | Menunggu Sesi Fisik | **READY FOR UAT** |
| **UAT-ADM-06** | Pemeriksaan Jejak Audit (Audit Trail Integrity) | Seluruh perubahan pendaftaran, alasan pembukaan, dan riwayat submit tersimpan di database secara immutable. | Menunggu Sesi Fisik | **READY FOR UAT** |

---

## 3. LEMBAR VALIDASI & PENGESAHAN UAT MADRASAH

Berikut adalah status lembar pengesahan resmi yang siap ditandatangani saat sesi demonstrasi langsung bersama pihak pimpinan dan panitia MIN 1 Kota Madiun:

```text
================================================================================
                    LEMBAR PENGESAHAN HASIL UJI PENERIMAAN (UAT)
                       PPDB ONLINE MIN 1 KOTA MADIUN 2026/2027
================================================================================

1. Ketua Panitia PPDB 2026/2027     : [ MENUNGGU SESI FISIK SEKOLAH ]
   Status Verifikasi                : PENDING OWNER ACTION
   Tanggal                          : ___ / ___ / 2026

2. Koordinator IT Madrasah          : [ MENUNGGU SESI FISIK SEKOLAH ]
   Status Verifikasi                : PENDING OWNER ACTION
   Tanggal                          : ___ / ___ / 2026

3. Perwakilan Tim PTSP              : [ MENUNGGU SESI FISIK SEKOLAH ]
   Status Verifikasi                : PENDING OWNER ACTION
   Tanggal                          : ___ / ___ / 2026

4. Lead QA & Tim Pengembang Sistem  : Tim Teknis PPDB Minsatama
   Status Verifikasi                : VERIFIED (203 AUTOMATED TESTS PASSED)
   Tanggal                          : 19 November 2026
================================================================================
```
