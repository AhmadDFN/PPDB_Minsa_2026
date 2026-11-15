# PHASE 5 — MANAJEMEN BERKAS & DOKUMEN PERSYARATAN
## Rencana Kerja dan Desain Modul Unggah Berkas Digital

**Status**: TERJADWAL (SCHEDULED)  
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
   - Batas ukuran berkas maksimal 2048 KB (2 MB) per dokumen.
3. **Penyimpanan Riwayat Revisi**:
   - Jika dokumen diganti oleh wali murid sebelum submit final atau karena permintaan panitia, berkas sebelumnya dipindahkan ke riwayat `registration_document_revisions`.
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
- `app/Http/Controllers/Applicant/DocumentUploadController.php`
- `app/Http/Requests/Document/UploadDocumentRequest.php`
