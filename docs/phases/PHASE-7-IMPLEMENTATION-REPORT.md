# LAPORAN IMPLEMENTASI FASE 7 — PDF PENDAFTARAN

**Status**: CLOSED
**Cakupan**: PDF draft dan bukti pengiriman final, versioning, unduhan privat, dan QR verifikasi
**Referensi spesifikasi**: [PHASE-7-PDF.md](./PHASE-7-PDF.md)

## 1. Hasil

- PDF draft dapat dibuat selama pendaftaran berstatus `DRAFT`, memakai watermark pada setiap halaman, dan memasker NIK calon siswa.
- PDF final hanya dapat dibuat setelah status `SUBMITTED` dan pemeriksaan kelengkapan. Final memakai consent historis, menampilkan NIK calon siswa lengkap, dan tidak mencetak NIK orang tua/wali.
- Layout menggunakan ukuran A4, ringkasan identitas/alamat/orang tua/pendidikan/program, ruang tanda tangan, dan foto bila tersedia.
- PDF disimpan pada disk privat tanpa URL publik. Unduhan pendaftar memeriksa ownership, MIME, ukuran, header PDF, dan SHA-256.
- Metadata file, nomor versi, revisi data, dan hash tercatat pada `registration_pdf_versions`. Versi lama dipertahankan; perubahan data menandai PDF final terdahulu sebagai outdated.
- Token QR bersumber dari 256-bit secure randomness; hanya hash SHA-256 token disimpan. Endpoint verifikasi mencocokkan token dan nomor pendaftaran serta hanya menampilkan nomor, tanggal terbit, fingerprint, dan status. Endpoint publik tidak menyediakan PDF atau menampilkan PII.
- Kegagalan persistensi database membersihkan file baru dan mencatat kode kegagalan tanpa nilai NIK.
- Dashboard pendaftar menyediakan aksi pembuatan PDF, unduhan, dan riwayat versi.

## 2. Implementasi Teknis

- Renderer menggunakan Blade dan Dompdf; remote resources dan eksekusi PHP dinonaktifkan. QR dibuat sebagai SVG.
- Metadata PDF ditambahkan melalui migration additive; migration awal `registration_pdf_versions` tidak diubah.
- Snapshot data dicek kembali terhadap `data_revision` sebelum file dan versi disimpan. Perubahan data draft maupun submitted yang tercatat menaikkan revisi.
- Tidak ada fitur seleksi, keputusan penerimaan, persetujuan dokumen, pengiriman final ulang, maupun pelaporan admin yang ditambahkan pada Fase 7.

## 3. Verifikasi

| Pemeriksaan | Hasil |
|---|---|
| Tes PDF terarah | 13 passed, 0 failed, 101 assertions |
| Regresi lengkap `php artisan test --compact` | 192 passed, 0 failed, 0 skipped, 924 assertions |
| Laravel Pint | Passed |
| Vite build | Passed; 3 modules transformed, CSS 86.96 kB, JS 1.72 kB |
| `composer audit` | 0 advisories |
| `npm audit --json` | 6 high findings pada dependency development/build |
| `npm audit --omit=dev` | 0 vulnerabilities |
| Migration Fase 7 | Dieksekusi melalui suite SQLite in-memory; belum diuji fisik pada MySQL 8 |

Tes PDF mencakup watermark draft, masking NIK, consent historis, metadata dan integritas file, versi lama/outdated, unduhan lintas pendaftar, cleanup sesudah kegagalan DB, dan halaman verifikasi QR minimal. PDF final aktual juga berhasil dirender dengan pas foto JPG pada lingkungan lokal tanpa ekstensi GD maupun Imagick.

## 4. Prasyarat dan Batasan yang Tercatat

- Foto PNG memerlukan ekstensi PHP GD untuk dirender oleh Dompdf. Tanpa GD, pembuatan PDF mengembalikan validasi yang menyarankan mengaktifkan GD atau menggunakan JPG.
- `APP_URL` produksi harus menunjuk ke host HTTPS publik sebelum PDF final dibuat agar tautan QR dapat digunakan.
- Logo dan alamat resmi belum tersedia sebagai sumber terverifikasi di repositori. Kop saat ini memakai nama institusi yang terdokumentasi; tidak ada logo/alamat yang direka.
- Verifikasi sembilan tes MariaDB/InnoDB sebelumnya adalah hasil Fase 6. Hasil itu tidak menggantikan pengujian migration baru pada MySQL 8; kompatibilitas fisik MySQL 8 masih terbuka.
- Temuan NPM `GHSA-fc5q-39xc-vjpf` / `CVE-2026-112366` tetap terbuka pada rantai build/development. Tidak dilakukan downgrade lintas major atau `npm audit fix --force`.

Fase 7 selesai. Fase 8 tidak dimulai; pekerjaan berikutnya menunggu persetujuan eksplisit pemilik.
