# Laporan Audit Penutupan Fase 6 — Alur Pendaftaran

**Tanggal audit**: 18 November 2026
**Commit implementasi yang diaudit**: `311e099`
**Branch**: `main` (pada awal audit sama dengan `origin/main`)
**Status akhir**: **BLOCKED — Fase 6 BELUM CLOSED**
**Keputusan fase berikutnya**: **Jangan memulai Fase 7**

## 1. Ringkasan Eksekutif

Implementasi alur submit dan tes SQLite tersedia, tetapi empat gate penutupan belum seluruhnya lulus. Format nomor dan consent masih menunggu persetujuan pemilik. Migrasi serta submit bersamaan belum dibuktikan pada MySQL/InnoDB karena server tidak tersedia. Permission `reopen_registration` belum memiliki workflow, sedangkan kebijakan bisnis reopen tidak menetapkan cakupan dan masa berlakunya. Dokumentasi audit telah diselaraskan dengan hasil yang benar-benar diverifikasi.

Laporan ini tidak mengesahkan Fase 6 sebagai CLOSED dan tidak memberikan otorisasi untuk Fase 7.

## 2. Status Gate Penutupan

| Gate | Status | Bukti / temuan |
|---|---|---|
| Format nomor registrasi | **PENDING OWNER APPROVAL** | Implementasi sementara `PPDB/{tahun-ajaran}/{kode-program}/{urutan-5-digit}` dan sequence per pasangan periode-program. Tidak ditemukan persetujuan tertulis untuk format, kode program, lingkup, atau reset sequence. |
| Teks dan versi consent | **PENDING OWNER APPROVAL** | Versi `v1.0` dan redaksi fixture bersifat provisional. Belum ditemukan pengesahan tertulis. Konfigurasi production kosong tetap gagal tertutup. |
| Migrasi pada MySQL/InnoDB | **BLOCKED** | Ekstensi `pdo_mysql` tersedia, tetapi server `127.0.0.1:3306` tidak dapat dihubungi. Test migrasi diskip; tidak ada migration yang dijalankan pada MySQL atau database persisten. |
| Submit bersamaan / sequence concurrency pada InnoDB | **BLOCKED** | Source menggunakan transaction, `lockForUpdate()` dan unique constraint; SQLite/sequential tests tidak membuktikan perilaku lock InnoDB. Tidak ada uji submit multi-connection/process yang berhasil dijalankan. |
| Kebijakan dan fitur admin reopen | **PENDING OWNER APPROVAL** | Seeder memiliki permission `reopen_registration` bagi `superadmin`, tetapi tidak ditemukan route, policy, controller, atau action pemakaiannya. Business Requirements tidak mendefinisikan reopen setelah periode tutup. |
| Konsistensi laporan dan angka tes | **PASS** | Dokumen status, roadmap, audit, serta laporan Fase 6 diperbarui memakai hasil aktual di bawah ini. |
| Kesiapan Fase 7 | **BLOCKED** | Gate Fase 6 belum lulus dan belum ada otorisasi Fase 7 dalam lingkup audit ini. |

## 3. Tinjauan Teknis Implementasi

- Submit mengunci data terkait dalam transaksi, mencatat consent dan audit, mengubah status serta menerbitkan sequence per `admission_period_program_id`.
- Unique constraint pada nomor registrasi menjadi perlindungan tambahan. Pengiriman ulang yang telah berhasil ditangani idempoten.
- Pemeriksaan source dan tes SQLite mendukung perilaku aplikasi, tetapi **bukan** bukti bahwa skema migrasi, row locking, rollback, atau konkurensi aman di InnoDB.
- Tidak ada route/action admin reopen. Permission yang disemai tidak berarti fitur reopen sudah tersedia.
- Belum ada PDF draft/final pada Fase 6; dokumen PDF tetap di luar cakupan audit dan tidak diimplementasikan.

### Keputusan pemilik yang diperlukan

1. Sahkan format nomor, prefix/kode program, cakupan sequence dan apakah/reset sequence dilakukan.
2. Sahkan redaksi consent, versi yang berlaku, dan proses perubahan/versi berikutnya.
3. Tetapkan apakah admin reopen berlaku untuk satu pendaftaran atau satu periode, role yang berwenang, masa berlaku, alasan wajib, pencatatan audit, serta cara menutup kembali akses.
4. Sediakan MySQL/InnoDB test database yang terpisah dan disposable untuk uji migrasi serta submit bersamaan; sebelum tes, periksa bahwa koneksi `mysql_test` benar-benar menunjuk ke database tersebut. Jangan arahkan tes ke database produksi atau data pendaftar.

## 4. Hasil Verifikasi Aktual

### Automated tests

- Full regression `php artisan test --compact`: **161 tests; 159 passed, 0 failed, 2 skipped; 618 assertions**.
- Workflow `php artisan test --compact tests/Feature/Applicant/RegistrationWorkflowTest.php`: **18 passed; 114 assertions**.
- Dua test yang diskip adalah migrasi MySQL dan pemeriksaan engine InnoDB. Keduanya tidak berjalan karena MySQL pada `127.0.0.1:3306` tidak tersedia.

### Kualitas dan dependency

- `vendor/bin/pint --dirty --format agent`: **PASS**.
- `npm.cmd run build`: **PASS**; Vite 8.3.8, 3 modules, CSS 85.88 kB, JS 1.72 kB.
- `composer audit`: **0 advisories**.
- `npm.cmd audit --omit=dev`: **0 vulnerabilities**.
- `npm.cmd audit --json`: **6 temuan high** pada dependency build/development terkait `GHSA-fc5q-39xc-vjpf` / `CVE-2026-112366`. Advisory belum menetapkan versi patch; tidak ada dependency/lockfile yang diubah. Jangan mengklaim temuan telah diperbaiki.

## 5. Status Git dan Riwayat

- Pada awal audit, worktree bersih dan `main` serta `origin/main` sama-sama menunjuk `311e099`.
- `311e099` adalah commit implementasi Fase 6 yang diaudit.
- Riwayat lokal yang tersedia: `e2a8bb3` → `4e70b5d` → `d970b33` → `311e099`.
- Referensi historis `4c64724` yang tercatat pada dokumentasi lama tidak ditemukan pada riwayat branch saat audit. Bukti yang tersedia tidak cukup untuk menyimpulkan apakah penyebabnya squash, migrasi repository, atau hal lain.
- Tidak ada rewrite history, force push, atau penghapusan perubahan.

## 6. Risiko yang Tetap Terbuka

1. Jangan menerbitkan nomor produksi sebelum format dan scope sequence disahkan.
2. Jangan mengaktifkan submit production tanpa consent resmi yang dikonfigurasi.
3. Jangan menyatakan migrasi/concurrency MySQL lulus berdasarkan SQLite.
4. Jangan menganggap permission reopen sebagai fitur; implementasi memerlukan kebijakan bisnis tertulis.
5. Enam temuan NPM high pada dependency build/development masih terbuka menunggu patch yang kompatibel. Hindari memproses pola glob tak tepercaya dalam build.
6. Konfigurasi PHP lokal yang tercatat masih `upload_max_filesize=2M`; kebijakan dokumen 5 MB memerlukan `upload_max_filesize=5M`, `post_max_size` lebih besar dari 5M, dan request limit web server yang mencakup multipart overhead sebelum upload maksimum dapat dipakai secara live.

## 7. Rekomendasi dan Stop Condition

**Rekomendasi: jangan lanjut ke Fase 7.** Fase 7 baru dapat dipertimbangkan setelah keputusan bisnis di atas disahkan, test migration dan submit bersamaan dijalankan dengan hasil lulus pada MySQL/InnoDB disposable, kebijakan reopen diimplementasikan dan diuji sesuai keputusan, serta status dokumentasi diperbarui.

Setelah laporan ini, berhenti pada batas Fase 6. Jangan membuat fitur PDF, migration, dependency, atau implementasi Fase 7 sampai gate selesai dan pemilik memberi persetujuan eksplisit.
