# PHASE 6 — ALUR PENDAFTARAN (DRAFT & SUBMIT FINAL)
## Rencana Kerja dan Desain Siklus Hidup Pendaftaran Siswa

**Status**: TERJADWAL (SCHEDULED)  
**Target Pelaksanaan**: Fase 6

---

## 1. TUJUAN FASE 6

Mengelola siklus hidup pendaftaran dari status `DRAFT` hingga `SUBMITTED`, memvalidasi kelengkapan berkas pra-pengiriman, menerbitkan nomor registrasi unik, serta mengunci data yang telah disubmit.

---

## 2. ATURAN BISNIS SIKLUS HIDUP PENDAFTARAN

1. **Status Draft**:
   - Pendaftar dapat menyimpan perubahan berkali-kali tanpa dibatasi waktu selama periode pendaftaran aktif.
   - Belum memiliki nomor registrasi resmi.
   - Pendaftar dapat mengunduh Bukti Draft PDF ber-watermark.
2. **Validasi Kelengkapan Pra-Submit**:
   - Sistem memeriksa kelengkapan: Biodata siswa, Ayah, Ibu, Alamat, Pendidikan TK, Pilihan Program, Berkas Dokumen Wajib, dan Pernyataan Kebenaran Data (`registration_consents`).
3. **Submit Final**:
   - Transaksi atomik mengubah status menjadi `SUBMITTED`.
   - Mengisi timestamp `submitted_at`.
   - Menghasilkan nomor registrasi unik berformat resmi (contoh: `REG-2026-0001`).
   - Memicu pembuatan versi final PDF.
4. **Perubahan Pasca Submit**:
   - Jika pendaftar mengubah data setelah submit (selama periode buka), sistem mencatat log audit pada `registration_logs` dan menaikkan nomor versi PDF pada `registration_pdf_versions`.
