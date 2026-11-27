# Laporan Implementasi Revisi Form Calon Siswa

**Tanggal:** 26 November 2026
**Status:** PARTIAL — lokal saja, belum siap rilis
**Ruang lingkup:** revisi dari `ppdbv2/workflow_update_form.md` dan lampiran pemilik.

**Catatan 27 November 2026:** Log server menunjukkan migrasi wilayah gagal karena foreign key diproses sebelum indeks unik. Deklarasi indeks diperbaiki secara lokal dan urutan SQL dicakup regression test; MariaDB server dan pemulihan tabel/data setelah `migrate:fresh` belum diverifikasi. Lihat ISS-011 di `ppdbv2/KNOWN-ISSUES.md`.

## Perubahan yang tersedia

- Migration additive menambah data biodata, identitas sensitif terenkripsi, field alamat/wilayah, data orang tua/wali, pendidikan, dan nomor handphone calon siswa.
- NIK orang tua/wali dienkripsi dan diindeks dengan HMAC; migration backfill mengosongkan NIK plaintext historis.
- Checkbox NISN mengubah kebutuhan dokumen dan menghapus NISN lama bila calon siswa menyatakan belum memilikinya. Usia tetap runtime terhadap tanggal acuan periode; tidak disimpan.
- Dataset wilayah lokal 2025 (91.599 baris) diimpor melalui seeder, dengan API cascade, validasi hirarki, dan shortcut salin alamat. Teks lisensi/atribusi disertakan di `database/data/indonesia-regions/`.
- Koordinat Kampus 1 diambil dari direktori Kemendikdasmen untuk NPSN 60720864. Peta offline lokal dikemas dari potongan OSM berlisensi ODbL (2.805 fitur: 138 jalan dan 2.667 bangunan), disajikan dari origin aplikasi. Pin mendukung tap/klik, drag, keyboard, dan lokasi perangkat opt-in; Haversine dihitung lokal serta server-side. Koordinat tempat tinggal tidak disimpan; jarak manual tetap menjadi fallback. Harness browser widget diuji pada 360×800/390×844 tanpa overflow; cakupan visual hanya sekitar kampus dan UAT form terautentikasi masih pending.
- Readiness, review applicant, admin detail, dan PDF privat disesuaikan untuk field baru. PDF memuat data keluarga yang diperluas, NIK orang tua/wali tersamar, dan status berkas yang telah diunggah; test memastikan NIK utuh tidak muncul dalam payload.
- Layout pendaftar tetap Blade/Tailwind/Vanilla JS dan menerapkan touch target serta progressive enhancement untuk pilihan wilayah.

## Verifikasi lokal

- Migration dan seeder wilayah dijalankan pada SQLite lokal; data berjumlah 91.599 entri.
- Uji otomatis cascade/validasi wilayah, penyimpanan label katalog, nomor handphone dan checkbox NISN ditambahkan.
- Browser UAT: submit/verifikasi admin/PDF final telah berhasil sebelum perluasan cascade dan nomor handphone; recheck cascade, salin alamat, IDOR dokumen/PDF lintas akun (404), dan viewport 360×800 dilakukan.
- QR verification otomatis pernah lulus 4 test/13 assertions. Scan fisik QR, foto calon siswa asli, MySQL/MariaDB dan staging belum dibuktikan.
- `php artisan test`: **PASS, 388 tests; 373 passed, 15 skipped; 1.781 assertions**, diulang setelah menambah kalkulasi pin dan kompatibilitas snapshot PDF legacy.
- `vendor/bin/pint --test`, `npm.cmd run build`, `composer audit`, `npm.cmd audit`, route list, view cache/clear, map JavaScript syntax, map data integrity, dan `git diff --check`: **PASS**.
- Browser mobile 360×800 memastikan field handphone terlihat tanpa overflow; checkbox NISN membuat input disabled/non-required.

## Pekerjaan yang menghalangi rilis

1. Uji map melalui form pendaftar terautentikasi, termasuk penolakan izin GPS dan lokasi luar cakupan. Harness widget saja telah memeriksa tap/drag/keyboard/manual fallback serta viewport 360×800 dan 390×844.
2. Uji foto asli berlatar biru; saat ini validasi konten/latar foto masih manual.
3. Scan QR PDF final menggunakan perangkat kamera.
4. Uji migrasi/schema dan seeder pada MySQL/MariaDB target, lalu jalankan UAT staging.
5. Setelah itu baru review, commit/push, dan deployment mengikuti persetujuan yang telah diberikan serta kondisi branch/remote saat itu.

Tidak ada perubahan production, staging, commit, push, atau deployment pada sesi ini.
