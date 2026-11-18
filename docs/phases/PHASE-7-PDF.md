# PHASE 7 — GENERATOR DOKUMEN PDF PENDAFTARAN
## Rencana Kerja dan Desain Lembar Bukti Pendaftaran Resmi

**Status**: IMPLEMENTASI SELESAI (CLOSED)
**Target Pelaksanaan**: Fase 7

---

## 1. TUJUAN FASE 7

Membangun modul pembuat dokumen PDF resmi untuk lembar pendaftaran siswa yang dapat diunduh dan dicetak mandiri oleh orang tua/wali untuk diserahkan ke loket PTSP MIN 1 Kota Madiun.

---

## 2. SPESIFIKASI DOKUMEN PDF

1. **Format Lembar Resmi**:
   - Ukuran kertas A4 / F4 standar perkantoran madrasah.
   - Kop surat resmi Kementerian Agama RI dan MIN 1 Kota Madiun.
   - Pas foto berwarna calon siswa (3x4).
   - Tabel ringkasan data calon siswa, orang tua/wali, riwayat sekolah, dan jalur program.
   - QR Code / Barcode berisi nomor registrasi dan URL verifikasi validasi berkas.
   - Kolom tanda tangan orang tua/wali murid bermaterai/bertanggal.
2. **Kondisi Status Dokumen**:
   - Jika status `DRAFT`: Diberi watermark diagonal transparan **"DRAFT — BUKAN BUKTI RESMI"**.
   - Jika status `SUBMITTED`: Tanda bukti sah registrasi tanpa watermark draft.
3. **Pencatatan Versi Dokumen**:
   - Dicatat pada tabel `registration_pdf_versions` lengkap dengan `version_number` dan checksum berkas.

---

## 3. STATUS PELAKSANAAN DAN BATAS OPERASIONAL

- Implementasi Fase 7 selesai dan hasil terperinci dicatat pada `PHASE-7-IMPLEMENTATION-REPORT.md`.
- PDF menggunakan ukuran A4, watermark draft, penyimpanan privat, checksum SHA-256, versioning, unduhan berotorisasi, serta QR bertoken dengan halaman verifikasi data-minimal.
- Logo dan alamat resmi belum tersedia sebagai sumber terverifikasi; kop sementara hanya menggunakan nama institusi. Jangan menambah identitas visual/alamat hasil tebakan.
- Foto JPG telah dirender pada lingkungan lokal tanpa GD/Imagick. Foto PNG memerlukan ekstensi PHP GD; tanpa GD sistem mengembalikan pesan validasi yang dapat ditindaklanjuti.
- QR pada production mensyaratkan `APP_URL` HTTPS publik.
- Tes regresi lengkap: 192 passed, 0 failed, 0 skipped; 924 assertions. Migration Fase 7 belum diverifikasi secara fisik pada MySQL 8.
