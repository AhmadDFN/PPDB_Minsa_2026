# UAT situs live — 27 November 2026

Target: ahmaddanyfn.biz.id. Akun dummy disediakan pemilik; kredensial dan identitas tidak dicatat di laporan.

- PASS: sesi pendaftar aktif, status awal DRAFT, progres 0%.
- PASS: biodata, alamat, orang tua, pendidikan, dan program tersimpan; dashboard menyatakan 86% lengkap.
- PASS: pilihan wilayah bertingkat sampai kelurahan.
- PASS: PDF draf v1 dibuat; isi PDF dan unduhan belum diperiksa.
- FAIL: jumlah saudara 0 ditolak dengan pesan harus 1–9 meskipun petunjuk menyebut jumlah saudara di luar calon siswa. Gunakan 1 untuk melanjutkan; audit aturan validasi.
- PARTIAL: salin alamat mengisi teks, tetapi pilihan wilayah orang tua kosong setelah submit awal; pemilihan manual berhasil. Perlu reproduksi ulang dan audit waktu pemuatan cascade.
- PARTIAL: klik simpan alamat dari browser automation dua kali mengalami timeout; Enter kemudian menghasilkan pesan sukses. Belum cukup bukti gangguan server.
- HISTORIS: sesi sempat berakhir dan login ulang ditolak dua kali. Sesi kemudian pulih; empat dokumen dummy, submit final dan PDF final berhasil pada lanjutan di bawah.
- WhatsApp, IDOR lintas akun, dan admin belum diuji pada sesi ini.
## Pembaruan perbaikan lokal

- Validasi jumlah saudara diselaraskan ke 0–20; feature test mencakup 0, 20, dan penolakan 21.
- Salin wilayah orang tua memakai kode atau nama alamat siswa yang tersimpan, menunggu inisialisasi wilayah, lalu mengisi empat tingkat; browser lokal berhasil menyalin data historis tanpa kode.
- Perubahan belum diverifikasi pada situs live. Login ditolak dengan pesan identitas atau sandi salah; tidak dapat menentukan penyebab spesifik tanpa akses akun.
- Composer audit masih melaporkan lima advisory pada tiga paket Symfony (ISS-012).

- Navigasi publik diperbaiki: browser lokal pada login dan registrasi dengan viewport 768 px mencatat scrollWidth 753 px dan menu ringkas tampil; uji ulang di live masih pending.
- Full suite: 390 test, 375 passed, 15 skipped, 1.790 assertions. Pint, build Vite, npm audit, view cache, dan diff check lulus. Composer audit: lima advisory pada tiga paket.

## Lanjutan live 27 November

- Sesi akun dummy kembali aktif; biodata, alamat, orang tua, pendidikan, dan program disimpan ulang.
- Empat gambar dummy berlabel UAT diunggah; dashboard dokumen menunjukkan 4/4. Kesiapan di halaman tinjau menunjukkan lengkap.
- Server live masih menolak jumlah saudara 0 setelah commit dipush; deployment versi baru belum terbukti.
- HISTORIS: submit menunggu persetujuan pernyataan. Pemilik kemudian menyetujui; hasil submit dan PDF final tercatat pada bagian berikutnya.

## Submit dan PDF final live

- PASS: persetujuan pemilik diberikan; pendaftaran dummy terkirim dan status SUBMITTED ditampilkan, empat dokumen dummy tetap terdaftar.
- PASS: PDF final dibuat dan diunduh; pdfinfo menunjukkan 2 halaman A4. Pemeriksaan visual kedua halaman menunjukkan foto dummy, QR, NIK tersamar, status dokumen dan pernyataan; tidak ada watermark draft yang terlihat.
- FAIL: sebelum pengesahan, baris tanggal pengesahan hanya menampilkan WIB. Penyebab adalah penggabungan string dengan tanggal null; diperbaiki di kode lokal dan diuji pada renderer payload.
- FAIL: halaman tinjau setelah submit menyatakan PDF akan tersedia pada Fase 7, padahal dashboard dapat membuat PDF final. Teks diperbaiki di kode lokal.
- PENDING: scan QR aktual dan isi respons halaman verifikasi publik, IDOR lintas akun, OTP/WhatsApp, pemeriksaan foto asli dan uji ulang perubahan pada server setelah deployment. QR yang terlihat saja belum membuktikan bisa dipindai.
- PASS: RegistrationPdfTest 9 test, 96 assertions; Pint, build, npm audit dan view cache lulus. Composer audit masih melaporkan lima advisory pada tiga paket (ISS-012).
- Full suite akhir: 390 test, 375 passed, 15 skipped, 1.793 assertions.

## Rekonsiliasi dan perbaikan lanjutan

- Commit a3f1361 menangani jumlah saudara, penyalinan wilayah, dan navbar tablet. Commit b12a7e5 memperbaiki pesan Fase 7 dan tanggal PDF null. Teks pengganti pada halaman tinjau telah terlihat di server live pada pemeriksaan ulang.
- Delapan paket diperbarui melalui Composer untuk menutup lima advisory; audit terbaru tidak menemukan advisory (ISS-012). Penggunaan versi baru pada server masih perlu dipastikan setelah deployment.
- Suite setelah pembaruan: 390 test, 375 passed, 15 skipped, 1.793 assertions; Pint, Vite build, npm audit dan Composer audit lulus.

## Putaran superadmin setelah fresh seed — 27 November 2026

Pemilik menyatakan telah menjalankan `migrate:fresh --seed` dan seeder uji pada web. Agen tidak menjalankan perintah itu; server menunjukkan tiga pendaftar uji. Detail hasil dan isu ada di `adminlivetest/03-CHECKLIST-DAN-HASIL.md` serta `04-LOG-TEMUAN.md`.

- PASS: akun superadmin dan admin PPDB dari seeder berhasil login/logout; akses dashboard setelah logout dialihkan ke login.
- PASS batas akses yang dicoba: admin PPDB menerima 403 pada pengaturan, komunikasi, akun staf, dan audit.
- PARTIAL: dashboard menunjukkan total 3 pendaftar, 2 terkirim, 1 draf, cocok dengan daftar; filter DRAFT menghasilkan satu dan reset kembali tiga. Validasi database independen belum dilakukan.
- FAIL: branding live masih `PPDBM`, bukan `PPDB` resmi; keempat template pesan di Communication Center kosong tanpa contoh/placeholder. Kanal database belum ada dan halaman menunjukkan konfigurasi Fonnte legacy environment.
- BLOCKED: dua fixture SUBMITTED tidak memiliki dokumen; uji verifikasi dan pengesahan belum aman. Tidak ada nomor baru, perubahan konfigurasi, atau pesan WhatsApp yang dikirim agen.
- Risiko keamanan: sandi bawaan yang terlacak pada source/Git berhasil dipakai login live setelah fresh seed. Rotasi dan penghapusan default statis diperlukan sebelum launch.
- Versi server pada dashboard menampilkan Laravel 13.42.0; commit rilis yang terpasang belum dibuktikan. Perbaikan kode lokal Fonnte dan nomor tidak boleh dianggap sudah live.

## Uji ulang superadmin sekitar 20.04 WIB

Login/logout berhasil; setelah logout rute admin menuju login admin. Empat contoh template dan variabel tersedia terlihat di Communication Center. Nama institusi berhasil diubah melalui superadmin menjadi `PPDB MIN 1 KOTA MADIUN`, tercatat di audit, dan tampil pada login pendaftar. Menu akun, roles, master periode/program/dokumen, batch/tahap wawancara, dan audit terbuka. Petunjuk default lama `PPDBM` di form pengaturan masih terlihat; kode lokal diperbaiki dan belum diuji ulang di server. OTP/Fonnte, pengesahan berkas, serta mutasi master belum dibuktikan. Rincian: `adminlivetest/03-CHECKLIST-DAN-HASIL.md`.

## Putaran khusus pendaftar setelah fresh seed — 27 November 2026

Situs uji memakai tiga akun dari `LiveTestSeeder`; tidak ada identitas, sandi, nomor telepon, atau OTP yang disalin ke laporan. Akun DRAFT dipakai untuk pengisian; dua akun SUBMITTED hanya diperiksa tanpa mengubah datanya.

- PASS: login/logout pendaftar; dashboard DRAFT naik dari 14% menjadi 100% setelah biodata, alamat, ayah/ibu, pendidikan, program Reguler, dan empat dokumen dummy lengkap. Jumlah saudara 0 diterima pada server live.
- PASS: pilihan wilayah bertingkat sampai kelurahan tersimpan. Tombol salin alamat untuk ayah dan ibu menyalin wilayah, RT/RW, serta kode pos. RT/RW dengan nol di depan tetap tampil benar di tinjau.
- PASS: empat PNG sintetis yang jelas berlabel bukan dokumen asli berhasil diunggah; statusnya menunggu pemeriksaan. Pratinjau privat berhasil dibuka oleh pemilik berkas.
- PASS: PDF draf dibuat dan diunduh. `pdfinfo` menunjukkan dua halaman A4; pemeriksaan visual kedua halaman menunjukkan watermark DRAFT, data sensitif tersamar, foto dummy, dan status berkas tanpa bagian terpotong.
- PASS: pendaftar mendapat 403 pada `/admin/dashboard`. Akun pendaftar kedua mendapat 404 saat membuka pratinjau dokumen akun pertama. Setelah logout, dashboard pendaftar dialihkan ke login.
- PASS: NIK dengan panjang salah pada form lupa password ditolak dengan pesan validasi 16 digit; tidak ada OTP dikirim.
- PASS: akun SUBMITTED yang belum lengkap ditolak saat mencoba membuat PDF final, disertai daftar persyaratan yang kurang.
- FAIL teks: tinjau DRAFT menyatakan nomor diperoleh saat submit, bertentangan dengan keputusan nomor setelah pengesahan. Teks dikoreksi lokal; verifikasi server setelah rilis masih pending.
- FAIL bahasa: acuan usia pada dashboard/form biodata tampil `01 July 2026`. Format diterjemahkan ke bahasa Indonesia di kode lokal; verifikasi server masih pending.
- GAP fixture: dua akun SUBMITTED hasil seeder hanya sekitar 43% lengkap tanpa dokumen; satu sudah memiliki nomor dari data awal. Kondisi ini membatasi pembuktian alur submit → verifikasi → pengesahan → PDF final dan tidak boleh dianggap sebagai hasil alur normal aplikasi.
- BELUM DIUJI: registrasi baru, OTP aktivasi/pemulihan, resend, ganti sandi pertama, submit final pada akun DRAFT, dan PDF final setelah pengesahan. Akun seeder memakai nomor dummy lain; nomor uji pemilik belum terhubung ke akun. Submit memerlukan pernyataan kebenaran data pada halaman tinjau, sehingga agen tidak menyetujuinya untuk data sintetis.

Data UAT tersimpan di situs uji: akun pertama tetap DRAFT dengan empat dokumen menunggu pemeriksaan. Pengujian hanya mencakup portal pendaftar dan dua pemeriksaan batas akses.

Gate checkout setelah koreksi lokal: `php artisan test --compact` 399 test (383 passed, 16 skipped, 1.825 assertions); Pint, Vite build, Composer audit, npm audit (0 vulnerability), dan `git diff --check` lulus. APP_KEY sementara hanya dipakai pada proses test.

Koreksi dan laporan UAT dipush ke `origin/main` pada commit `8c7de86`. Pembacaan berkas Hostinger setelah push masih menunjukkan kode tanggal dan teks tinjau lama; push belum berarti deployment. Verifikasi UI live untuk dua koreksi tersebut menunggu deployment terpisah.

## Uji ulang khusus pendaftar — 27 November 2026 sekitar 20.57 WIB

- PASS: koreksi tanggal acuan usia `01 Juli 2026` dan teks nomor setelah pengesahan terlihat pada dashboard/tinjau situs uji. Pembacaan kedua berkas Hostinger juga cocok dengan commit `8c7de86`; deployment terjadi setelah pemeriksaan awal di atas.
- PASS: NIK sintetis yang tidak terdaftar pada lupa password mendapat pesan umum tanpa membuka identitas akun. Pengisian kode dummy pada layar berikutnya kembali ke form dengan pesan sesi pemulihan tidak ditemukan. Tidak ada OTP nyata dikirim.
- PASS: satu dokumen Akta dummy diganti dengan berkas sintetis; halaman menunjukkan sukses, kelengkapan tetap 4/4, dan riwayat penggantian (1) memuat alasan serta tautan pratinjau/unduh revisi.
- PASS lokal: suite terarah `PasswordRecoveryTest`, `FonnteOtpDeliveryTest`, dan `tests/Feature/Applicant` menghasilkan 108 test, 107 passed, 1 skipped, 495 assertions. Pengiriman provider pada suite menggunakan simulasi, bukan WhatsApp nyata.
- BELUM DIUJI: penerimaan OTP pemulihan/aktivasi di HP pemilik. Akun aktif seeder masih terhubung ke nomor dummy lain; tidak ada akun pendaftar yang diketahui terhubung ke nomor uji pemilik. Alur ganti sandi setelah OTP dan submit final belum dibuktikan.

Gate akhir uji ulang: `php artisan test --compact` 399 test (383 passed, 16 skipped, 1.825 assertions); Pint, Vite build, Composer audit, npm audit (0 vulnerability), dan `git diff --check` lulus. APP_KEY sementara hanya dipakai pada proses test.
