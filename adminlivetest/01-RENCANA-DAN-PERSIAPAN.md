# 01 — Rencana dan Persiapan

## Tujuan dan batas

Uji akses, alur verifikasi dokumen, manajemen pendaftaran, laporan, pengaturan superadmin, dan rekap wawancara terhadap implementasi aktual. Pendaftaran hasil UAT adalah data dummy yang harus dapat dibedakan dari data siswa sebenarnya. Wawancara diuji sebagai modul operasional yang ada pada kode; hasil uji tidak boleh dipahami sebagai keputusan penerimaan, peringkat, atau pengumuman kelulusan.

## Konflik yang harus dilaporkan

Keputusan pemilik 27 November 2026 menetapkan nomor `YYNNNN` saat pengesahan dan modul wawancara resmi. Catat titik penerbitan, format, status dan konsekuensi aktual sebagai observasi; jangan menyatakan PASS/FAIL tanpa bukti live.

Dokumen lama juga memuat akun dan ekspektasi seeder yang mungkin tidak cocok dengan server. `routes/web.php` dan `RoleAndPermissionSeeder` adalah bukti checkout saat rencana ditulis, bukan bukti versi/permission yang sedang berjalan di server. Fallback placeholder di `/admin/{module}` bukan bukti modul lengkap.

## Prasyarat sebelum eksekusi

- [ ] Catat tanggal/jam WIB, URL, lingkungan (lokal/staging/live), penguji, commit lokal, versi server bila dapat dibuktikan, dan sumber bukti. Jangan klaim commit terpasang dari push saja.
- [ ] Konfirmasi akun uji terpisah `admin_ppdb`, `superadmin`, pendaftar A/B, dan pewawancara tahap 1/2/3; periksa status aktif dan permission aktual tanpa membagikan kredensial. Identifikasi fixture secara unik dengan label UAT, tidak memakai data siswa asli.
- [ ] Gunakan inventaris akun di `livetest/02-KREDENSIAL-DAN-AKUN-UJI.md`. Login superadmin pernah berhasil pada sesi live sebelumnya, tetapi sesi browser baru tidak mewarisi autentikasi. `firstOrCreate` pada seeder tidak mereset sandi lama; jangan menjalankan seeder ulang untuk memulihkan akses live.
- [ ] Siapkan fixture DRAFT, SUBMITTED, dokumen wajib dengan berbagai status, dokumen revisi, PDF final/QR, program Reguler dan Unggulan, sesi wawancara uji. Tandai fixture yang belum tersedia sebagai BLOCKED; jangan memalsukan hasil.
- [ ] Konfirmasi backup/pemulihan serta isolasi data sebelum uji yang mengubah status, jadwal, izin atau pengaturan. Insiden ISS-011 tentang migrasi dan kemungkinan data hilang wajib diperiksa; jangan menjalankan migrasi destruktif untuk menyiapkan UAT.
- [ ] Pastikan pengujian komunikasi tidak mengirim WA/SMS/email ke orang sungguhan. Untuk alur yang memang ingin mengirim pesan nyata, lakukan hanya pada penerima uji yang ditetapkan pemilik dan catat hasil tanpa isi rahasia.
- [ ] Simpan bukti di lokasi privat yang dikendalikan pemilik; laporan publik berisi hanya hasil dan referensi bukti yang telah disamarkan.

## Urutan

1. Login dan hak akses untuk tiap peran; pemeriksaan dashboard dan data daftar/detail.
2. Filter, pencarian, preview/unduh privat dan pengujian IDOR lintas fixture.
3. Uji verifikasi dan revisi dokumen, pengesahan, nomor dan audit pada fixture dummy; lanjutkan reopen dan PDF/QR.
4. Uji master wawancara, sequential gate, isolasi pewawancara, rekap dan koreksi beralasan pada fixture dummy.
5. Uji pengaturan dan master superadmin pada lingkungan terisolasi; validasi dan ekspor tanpa memasukkan data riil.
6. Uji mobile 360×800 dan 390×844, tablet 768×1024, desktop 1440×900; catat lebar halaman, akses keyboard, label, error dan waktu respon bila relevan.
7. Kumpulkan temuan, perbaiki blocker yang diizinkan, ulangi skenario gagal dan tes regresi yang relevan; sinkronkan hasil ke `docs/PROJECT_STATUS.md` dan Phase 11.

## Selesai ketika

Setiap kasus memiliki hasil dan bukti atau alasan BLOCKED/SKIPPED; temuan memiliki prioritas dan pemilik; tidak ada klaim produksi siap hanya karena halaman terbuka. Catat perubahan server serta pengaruhnya dan sampaikan pekerjaan manual yang belum terbukti. Review, commit dan push mengikuti instruksi pemilik yang berlaku untuk sesi pelaksanaan.
