# CURRENT HOSTINGER ENVIRONMENT

## Status per 20 November 2026

- Peran: lingkungan aplikasi online/testing untuk PPDB Minsatama.
- Domain kanonis yang dikonfirmasi pemilik proyek: `https://ahmaddanyfn.biz.id`.
- HTTPS: aktif menurut pengujian pemilik proyek.
- Deployment: GitHub Auto Deploy dari branch `main` telah berjalan.
- Database: telah dipakai untuk menyimpan data uji dan upload dokumen.
- Pemisahan lingkungan: Hostinger tetap dianggap staging/online testing sampai persetujuan Go-Live diberikan.
- Tidak ada push, deployment, atau perubahan `.env` Hostinger yang dilakukan dalam audit ini.

## Operasional aman

- Gunakan `php artisan migrate --force` melalui SSH setelah backup dan review migrasi.
- Jangan gunakan `migrate:fresh`, `migrate:refresh`, `db:wipe`, atau endpoint migrasi HTTP.
- Jika cache konfigurasi bermasalah, gunakan `php artisan optimize:clear` melalui SSH.
- Pastikan `APP_DEBUG=false`, HTTPS aktif, `pdo_mysql` dan `gd` aktif, serta storage privat tidak dapat diakses langsung.
- Atur `upload_max_filesize` minimal 10M, `post_max_size` minimal 16M, dan `memory_limit` minimal 256M.
- Backup database dan `storage/app/private` sebelum migrasi atau rilis.

## Verifikasi yang masih diperlukan

- Catat versi PHP, database engine/version, dan ekstensi Hostinger dari panel/SSH tanpa menyimpan credential.
- Verifikasi Final Submit, PDF final, QR, login Admin/Superadmin, permission boundary, reopen, dan system settings.
- Jangan menggunakan data siswa nyata pada staging.
