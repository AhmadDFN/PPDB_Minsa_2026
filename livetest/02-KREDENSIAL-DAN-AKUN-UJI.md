# Akun uji yang sudah tersedia dan cara mengaksesnya

**Status bukti:** pada 27 November 2026 pemilik menyatakan telah menjalankan fresh seed di web. Agen berhasil login lagi sebagai superadmin dan admin PPDB menggunakan akun seeder pada situs live, lalu logout. Akun tidak perlu dibuat ulang untuk uji akses baca. Dokumen ini tidak menyimpan kata sandi, NIK lengkap, OTP, token, atau nomor WhatsApp.

| Peran / fixture | Identitas login yang didefinisikan seeder | Sumber di kode | Status server live yang terbukti |
|---|---|---|---|
| Superadmin | `admin@minsatama.sch.id` melalui `/admin/login` | `SuperAdminSeeder.php`; dipanggil `LiveTestSeeder.php` | Login dan logout berhasil lagi setelah fresh seed; akun aktif |
| Admin PPDB | `admin1@minsatama.sch.id` melalui `/admin/login` | `InterviewAccountSeeder.php`; dipanggil `LiveTestSeeder.php` | Login dan logout berhasil lagi; akses pengaturan/komunikasi/akun/audit ditolak 403 |
| Pewawancara tahap 1/2/3 | `penguji1a@minsatama.sch.id`, `penguji2a@minsatama.sch.id`, `penguji3a@minsatama.sch.id` melalui `/admin/login` | `InterviewAccountSeeder.php` | Tiga role pernah berhasil login dan ditolak 403 pada rekap |
| Pendaftar DRAFT/SUBMITTED | NIK fixture yang tersimpan privat, melalui `/login` | `LiveTestSeeder.php` untuk tiga fixture deterministik | Sesi pendaftar dummy pernah dipakai; jangan menganggap seluruh fixture seeder ada di server |
| Lima pendaftar draft kosong | Pasangan NIK/sandi pada `storage/app/private/live-test-blank-accounts.json` lokal | `BlankApplicantLiveTestSeeder.php` | Lokal saja; tidak otomatis ada di live |

## Cara agen melanjutkan live test superadmin

1. Buka `https://ahmaddanyfn.biz.id/admin/login`. Akun superadmin dan admin pada tabel telah terbukti bisa login setelah fresh seed. Untuk pekerjaan berikutnya, gunakan kredensial aktif dari penyimpanan privat yang disetujui; jangan salin sandi ke dokumentasi.
2. Untuk **database lokal/test terisolasi**, periksa `SUPERADMIN_PASSWORD` atau nilai bawaan di `SuperAdminSeeder.php`. Admin/pewawancara memakai `DEFAULT_ADMIN_PASSWORD` atau nilai bawaan di `InterviewAccountSeeder.php`. Nilai tersebut hanya berlaku saat akun **pertama kali dibuat**.
3. `SuperAdminSeeder` dan `InterviewAccountSeeder` memakai `firstOrCreate`: menjalankan seeder ulang **tidak mereset sandi akun yang sudah ada**. Environment server dapat memakai sandi lain. Jangan menyimpulkan sandi live dari source code atau mencoba berkali-kali secara spekulatif.
4. Jika kredensial live yang sudah pernah dipakai tidak tersedia pada sesi browser, pemilik masuk langsung di tab handoff atau menyediakan akses melalui penyimpanan rahasia privat. Setelah sesi aktif, agen melanjutkan `adminlivetest/02-SKENARIO-ADMIN-SUPERADMIN.md` dan mencatat bukti di checklist.
5. Jangan menjalankan `LiveTestSeeder` di produksi untuk membuat akun. Seeder itu juga melakukan `updateOrCreate` pada pendaftar dan pendaftaran dummy, sehingga pengulangan dapat mengubah status/data fixture. Jika perlu membuat akun uji baru, siapkan prosedur terisolasi setelah memeriksa lingkungan dan dampaknya.

**Temuan keamanan:** nilai sandi bawaan yang terlacak pada source dan riwayat Git terbukti dapat dipakai pada situs live setelah fresh seed. Rotasi akun staf dan penghapusan default statis dari seeder diperlukan sebelum peluncuran; periksa log akses. Simpan kredensial baru di luar Git dan jangan kirim melalui chat.
