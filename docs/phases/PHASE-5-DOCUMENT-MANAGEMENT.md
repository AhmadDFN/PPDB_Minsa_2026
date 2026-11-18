# PHASE 5 — MANAJEMEN BERKAS & DOKUMEN PERSYARATAN
## Rencana Kerja dan Desain Modul Unggah Berkas Digital

**Status**: DISETUJUI PEMILIK; IMPLEMENTASI FASE 5 SELESAI (lihat laporan implementasi)
**Target Pelaksanaan**: Fase 5

---

## 1. TUJUAN FASE 5

Menyediakan modul pengunggahan dokumen persyaratan legal bagi calon siswa baru secara aman, privat, terstruktur, dan memiliki riwayat revisi (*versioning*).

---

## 2. SPESIFIKASI TEKNIS & KEAMANAN UNGGAH BERKAS

1. **Penyimpanan Privat**: Seluruh berkas disimpan di `storage/app/private/ppdb/documents/`, tidak terakses publik.
2. **Validasi File Ketat**:
   - Format yang didukung: PDF, JPG, JPEG, PNG.
   - Pengecekan MIME type asli (*magic bytes* via PHP Fileinfo) untuk mencegah ekstensi palsu (misal: file `.php` dinamai `.jpg`).
   - Maksimum global dan default per dokumen **5120 KB (5 MB)**. Persyaratan boleh menentukan batas lebih kecil; batas efektif merupakan nilai terendah antara global dan nilai persyaratan.
   - Batas persyaratan di atas 5120 KB ditolak. Seluruh pemeriksaan batas ukuran wajib dilakukan di server.
   - PHP/web server harus mengizinkan unggahan 5 MB: `upload_max_filesize=5M`, `post_max_size` lebih besar dari 5M, serta request limit server cukup untuk overhead multipart.
3. **Penyimpanan Riwayat Revisi**:
   - Jika dokumen diganti oleh pendaftar, metadata berkas sebelumnya dicatat di `registration_document_revisions`; berkas historis tetap disimpan dan dapat diakses secara privat oleh pemilik.
   - SHA-256 disimpan untuk memeriksa integritas berkas.
4. **Kelengkapan Dokumen**:
   - Kelengkapan menghitung hanya persyaratan wajib yang berlaku untuk program/periode pendaftaran calon siswa.
   - Fase ini tidak melakukan persetujuan dokumen atau pengiriman final pendaftaran.
4. **Dokumen Persyaratan yang Dikelola**:
   - Kartu Keluarga (Wajib).
   - Akta Kelahiran (Wajib).
   - Pas Foto Calon Siswa (Wajib).
   - Surat Keterangan Lulus / Keterangan TK/RA (Wajib/Kondisional).
   - Piagam / Sertifikat Prestasi (Opsional).

---

## 3. ARSITEKTUR KELAS & ACTION

- `app/Services/Document/DocumentUploadService.php`
- `app/Actions/Document/UploadRegistrationDocumentAction.php`
- `app/Actions/Document/ReplaceRegistrationDocumentAction.php`
- `app/Http/Controllers/Applicant/DocumentController.php`
- `app/Http/Requests/Applicant/UploadDocumentRequest.php`
