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
