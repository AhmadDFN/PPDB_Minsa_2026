# Login lima calon siswa dummy

Lima akun ini dibuat untuk **lingkungan lokal/test** melalui `BlankApplicantLiveTestSeeder`. Daftar nama, NIK, nomor HP dummy, dan sandi acak tersimpan dalam berkas privat lokal `storage/app/private/live-test-blank-accounts.json`. Berkas itu diabaikan Git dan jangan dibagikan atau dikirim ke repositori.

Buka `http://127.0.0.1:8000/login`, lalu gunakan pasangan `nik` dan `password` dari objek akun yang sama di berkas privat. Lima akun bernama Calon Siswa Uji 01 sampai 05; semuanya aktif dengan draft kosong. Tidak ada email untuk akun pendaftar; login staf memakai email melalui `/admin/login` dan tidak dibuat oleh seeder ini.

Untuk menyiapkan ulang di lingkungan lokal/test yang berbeda, pastikan migrasi dan periode aktif telah tersedia, lalu jalankan `php artisan db:seed --class=BlankApplicantLiveTestSeeder`. Seeder menolak dijalankan pada production serta menolak menimpa file kredensial atau identitas yang sudah ada. Jangan menghapus atau mereset data untuk mengulang tes tanpa memeriksa dampaknya. Seeder tidak mengirim OTP atau WhatsApp; nomor HP bersifat dummy.

Akun ini tidak otomatis tersedia pada `ahmaddanyfn.biz.id`; server harus memiliki lingkungan live test terisolasi dan akun yang disiapkan secara aman untuk pengujian di sana.