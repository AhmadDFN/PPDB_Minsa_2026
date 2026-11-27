# Hasil Live Smoke Test Produksi — 26 November 2026

**Target:** `https://ahmaddanyfn.biz.id/`
**Metode:** Browser read-only, tanpa autentikasi dan tanpa mengubah data.
**Referensi:** `livetest/README.md`, `01-SKENARIO-PER-ROLE.md`, dan `04-CHECKLIST-FITUR-DAN-PENGUJIAN.md`.

## Hasil yang diperiksa

| Skenario | Hasil | Bukti dan batasan |
|---|---|---|
| Halaman publik | PASS | Beranda termuat dan navigasi/isi dapat dibaca. Versi/build aplikasi yang sedang tersaji tidak dapat dipastikan hanya dari halaman publik. |
| Login pendaftar | PASS | `/login` memuat form NIK dan kata sandi. NIK memakai `inputmode=numeric` dan batas 16 karakter. Tidak dilakukan login. |
| Login staf | PASS | `/admin/login` memuat form email dan kata sandi. Tidak dilakukan login. |
| Registrasi | PASS (render saja) | `/daftar` memuat NIK, nama, WA, persetujuan, token CSRF. NIK memakai `inputmode=numeric`, `maxlength=16`, dan pattern `[0-9]*`. Form tidak dikirim dan OTP tidak diminta. |
| Proteksi rute pendaftar | PASS (unauthenticated) | GET `/pendaftar/alamat` dialihkan ke `/login`. Tidak ada percobaan akses data akun lain. |
| Mobile 360×800 | PASS untuk halaman login/registrasi | `documentElement.scrollWidth` = 345 px pada viewport 360 px. |
| Mobile 390×844 | PASS untuk halaman login/registrasi | `documentElement.scrollWidth` = 375 px pada viewport 390 px. |
| Tablet 768×1024 | FAIL | Login dan registrasi masing-masing mempunyai `scrollWidth` 825 px. Header navigasi melampaui viewport hingga x=825 px; horizontal scrollbar terlihat. Detail pada ISS-010. |
| Console browser | PASS terbatas | Tidak ada console error pada halaman registrasi selama inspeksi. Tidak mencakup alur setelah login. |

## Tidak diuji

- Login akun dummy, pengisian/submit final, upload, dashboard admin, perubahan status, dan PDF.
- OTP/WhatsApp, QR fisik, uji IDOR lintas akun, GPS/map dalam form terautentikasi.

Panduan menyebut akun dummy digunakan hanya pada database lokal/test atau lingkungan yang memang disiapkan untuk live test. Ketersediaan akun dummy di basis data produksi tidak terbukti; karena itu kredensial tidak dikirim dan tidak ada record produksi yang dibuat/diubah.

## Temuan konten untuk ditinjau pemilik

Beranda menyebut hasil seleksi diumumkan di dashboard serta alur seleksi/pemetaan. Pernyataan ini perlu dicocokkan dengan batas lingkup `AGENTS.md`, yang menyatakan sistem berakhir pada PDF bukti pendaftaran dan penyerahan ke PTSP serta tidak mengelola seleksi maupun pengumuman hasil.

## Tindak lanjut

1. Perbaiki navigasi/header login dan registrasi agar tidak overflow pada lebar 768 px, kemudian ulangi tes 360/390/768 px.
2. Jalankan skenario autentikasi dan workflow hanya setelah akun dummy khusus live test dipastikan tersedia di lingkungan target yang aman.
3. Setelah itu, lanjutkan tes PDF/QR, IDOR dengan dua akun yang memang disiapkan, serta UAT peta dan GPS. Jangan memakai data calon siswa nyata sebagai fixture.
4. Minta pemilik meninjau klaim seleksi/pengumuman pada konten beranda sebelum perubahan copy.

**Status:** Partial. Smoke test publik selesai; acceptance end-to-end produksi belum dilakukan.