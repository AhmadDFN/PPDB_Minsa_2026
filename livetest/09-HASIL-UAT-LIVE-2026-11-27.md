# UAT situs live — 27 November 2026

Target: ahmaddanyfn.biz.id. Akun dummy disediakan pemilik; kredensial dan identitas tidak dicatat di laporan.

- PASS: sesi pendaftar aktif, status awal DRAFT, progres 0%.
- PASS: biodata, alamat, orang tua, pendidikan, dan program tersimpan; dashboard menyatakan 86% lengkap.
- PASS: pilihan wilayah bertingkat sampai kelurahan.
- PASS: PDF draf v1 dibuat; isi PDF dan unduhan belum diperiksa.
- FAIL: jumlah saudara 0 ditolak dengan pesan harus 1–9 meskipun petunjuk menyebut jumlah saudara di luar calon siswa. Gunakan 1 untuk melanjutkan; audit aturan validasi.
- PARTIAL: salin alamat mengisi teks, tetapi pilihan wilayah orang tua kosong setelah submit awal; pemilihan manual berhasil. Perlu reproduksi ulang dan audit waktu pemuatan cascade.
- PARTIAL: klik simpan alamat dari browser automation dua kali mengalami timeout; Enter kemudian menghasilkan pesan sukses. Belum cukup bukti gangguan server.
- PENDING: empat dokumen dummy, submit final, PDF final/QR. Unggahan dummy disetujui pemilik, tetapi login ulang akun uji ditolak dua kali dan sesi lama berakhir; belum dilakukan.
- WhatsApp, IDOR lintas akun, dan admin belum diuji pada sesi ini.
## Pembaruan perbaikan lokal

- Validasi jumlah saudara diselaraskan ke 0–20; feature test mencakup 0, 20, dan penolakan 21.
- Salin wilayah orang tua memakai kode atau nama alamat siswa yang tersimpan, menunggu inisialisasi wilayah, lalu mengisi empat tingkat; browser lokal berhasil menyalin data historis tanpa kode.
- Perubahan belum diverifikasi pada situs live. Login ditolak dengan pesan identitas atau sandi salah; tidak dapat menentukan penyebab spesifik tanpa akses akun.
- Composer audit masih melaporkan lima advisory pada tiga paket Symfony (ISS-011).

- Navigasi publik diperbaiki: browser lokal pada login dan registrasi dengan viewport 768 px mencatat scrollWidth 753 px dan menu ringkas tampil; uji ulang di live masih pending.
- Full suite: 390 test, 375 passed, 15 skipped, 1.790 assertions. Pint, build Vite, npm audit, view cache, dan diff check lulus. Composer audit: lima advisory pada tiga paket.

## Lanjutan live 27 November

- Sesi akun dummy kembali aktif; biodata, alamat, orang tua, pendidikan, dan program disimpan ulang.
- Empat gambar dummy berlabel UAT diunggah; dashboard dokumen menunjukkan 4/4. Kesiapan di halaman tinjau menunjukkan lengkap.
- Server live masih menolak jumlah saudara 0 setelah commit dipush; deployment versi baru belum terbukti.
- Submit final belum dilakukan karena halaman meminta persetujuan pernyataan orang tua/wali; menunggu persetujuan tindakan pada saat submit. PDF final dan QR belum diuji.

## Submit dan PDF final live

- PASS: persetujuan pemilik diberikan; pendaftaran dummy terkirim dan status SUBMITTED ditampilkan, empat dokumen dummy tetap terdaftar.
- PASS: PDF final dibuat dan diunduh; pdfinfo menunjukkan 2 halaman A4. Pemeriksaan visual kedua halaman menunjukkan foto dummy, QR, NIK tersamar, status dokumen dan pernyataan; tidak ada watermark draft yang terlihat.
- FAIL: sebelum pengesahan, baris tanggal pengesahan hanya menampilkan WIB. Penyebab adalah penggabungan string dengan tanggal null; diperbaiki di kode lokal dan diuji pada renderer payload.
- FAIL: halaman tinjau setelah submit menyatakan PDF akan tersedia pada Fase 7, padahal dashboard dapat membuat PDF final. Teks diperbaiki di kode lokal.
- PENDING: scan QR aktual dan isi respons halaman verifikasi publik, IDOR lintas akun, OTP/WhatsApp, pemeriksaan foto asli dan uji ulang perubahan pada server setelah deployment. QR yang terlihat saja belum membuktikan bisa dipindai.
- PASS: RegistrationPdfTest 9 test, 96 assertions; Pint, build, npm audit dan view cache lulus. Composer audit masih melaporkan lima advisory pada tiga paket (ISS-011).
- Full suite akhir: 390 test, 375 passed, 15 skipped, 1.793 assertions.
