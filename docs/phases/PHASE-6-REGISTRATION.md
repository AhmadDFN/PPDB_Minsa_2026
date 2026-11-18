# PHASE 6 — ALUR PENDAFTARAN (DRAFT & SUBMIT FINAL)
## Rencana Kerja dan Desain Siklus Hidup Pendaftaran Siswa

**Status**: IMPLEMENTASI SELESAI; MENUNGGU REVIEW PEMILIK
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

---

## CATATAN EKSEKUSI FASE 6

- Implementasi mencakup ringkasan tinjauan, validasi kesiapan server-side, submit atomik/idempoten, consent, nomor unik, dan audit revisi data.
- Pembuatan PDF draft/final tidak termasuk implementasi fase ini dan tetap menjadi cakupan Fase 7.
- Format nomor sementara `PPDB/{tahun-ajaran}/{kode-program}/{urutan-5-digit}` dengan sequence per periode-program. Format dan ruang lingkup sequence belum disahkan sekolah.
- Redaksi consent dari fixture tes dan versi `v1.0` hanya asumsi sementara. Production menolak submit saat teks/versi belum diisi; sekolah harus mengesahkan keduanya sebelum rilis.
- Dua migration additive menambahkan tabel sequence dan metadata consent. Pengujian memakai SQLite; MySQL/InnoDB dan perilaku konkurensi fisik belum diverifikasi.
- Detail perubahan, hasil tes, dan isu terbuka dicatat di `PHASE-6-IMPLEMENTATION-REPORT.md`.
