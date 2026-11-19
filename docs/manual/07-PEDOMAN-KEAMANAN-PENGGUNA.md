# PEDOMAN KEAMANAN & PRIVASI DATA PENGGUNA
## Sistem Informasi PPDB Online MIN 1 Kota Madiun (PPDB Minsatama)
### Tahun Ajaran 2026/2027

Dokumen ini memuat standar perlindungan privasi dan pedoman keamanan siber bagi seluruh pemangku kepentingan (Orang Tua Pendaftar, Panitia PPDB, dan Superadmin) sesuai prinsip kepatuhan **Undang-Undang Perlindungan Data Pribadi (UU PDP No. 27 Tahun 2022)**.

---

## 1. MENGAPA DATA PPDB HARUS DILINDUNGI SECARA KETAT?

Data pendaftaran siswa memuat informasi kependudukan anak di bawah umur yang tergolong sebagai **Data Pribadi Spesifik** menurut perundang-undangan, meliputi:
- Nomor Induk Kependudukan (NIK) anak dan orang tua.
- Nomor Kartu Keluarga (KK).
- Tanggal dan tempat lahir.
- Alamat tempat tinggal dan koordinat domisili.
- Data penghasilan dan pekerjaan orang tua.
- Berkas pindaian Kartu Keluarga dan Pas Foto anak.

Kebocoran data kependudukan ini dapat berakibat pada penyalahgunaan identitas, penipuan daring, dan pelanggaran privasi keluarga. Seluruh pengguna sistem wajib memahami peran masing-masing dalam menjaga kerahasiaan ini.

---

## 2. PEDOMAN KEAMANAN BAGI ORANG TUA / WALI PENDAFTAR

Sebagai wali sah calon peserta didik, Anda bertanggung jawab menjaga keamanan akun pendaftaran anak:

1. **Jaga Kerahasiaan Kata Sandi (Password)**:
   - Buat kata sandi yang tidak mudah ditebak orang lain.
   - Jangan gunakan tanggal lahir atau NIK anak sebagai kata sandi.
   - Jangan pernah memberitahukan kata sandi Anda kepada orang lain, termasuk kepada petugas loket madrasah (petugas madrasah tidak pernah meminta kata sandi Anda).
2. **Kerahasiaan Kode OTP WhatsApp**:
   - Kode OTP adalah kunci pengaman akses verifikasi akun Anda.
   - Jangan pernah membagikan atau membacakan 6 digit kode OTP kepada siapapun yang menghubungi Anda melalui telepon atau pesan yang mengatasnamakan panitia.
3. **Waspadai Situs Palsu (Phishing)**:
   - Selalu periksa bilah alamat (*address bar*) browser Anda.
   - Pastikan terdapat ikon gembok aman (**HTTPS**) dan alamat domain yang diakses adalah domain resmi madrasah.
4. **Hindari Menyebarkan NIK di Media Sosial**:
   - Jangan mengunggah foto formulir pendaftaran, Kartu Keluarga, atau lembar bukti registrasi ke media sosial (Facebook, Instagram, status WhatsApp) tanpa menutup/menyensor NIK dan nomor telepon.
5. **Disiplin Keluar Akun (*Logout*)**:
   - Jika Anda mendaftar melalui komputer umum (seperti di warnet, kantor, atau ponsel pinjaman), selalu klik tombol **"Keluar / Logout"** setelah selesai dan hapus riwayat unduhan pada browser tersebut.

---

## 3. PEDOMAN KEAMANAN BAGI ADMINISTRATOR PPDB (PANITIA)

Panitia madrasah bertindak sebagai **Pengendali Data Pribadi (*Data Controller*)** di garis depan:

1. **Integritas Akun Panitia**:
   - Dilarang keras berbagi pakai (*account sharing*) satu akun admin untuk banyak orang. Setiap petugas wajib memiliki akun atas nama pribadi.
   - Segera lakukan logout saat meninggalkan meja loket pendaftaran.
2. **Larangan Menyebarkan Data ke Saluran Tidak Aman**:
   - Dilarang keras menyalin, memotret layar (*screenshot*), atau mengirim data pendaftar (terutama rekap nama, NIK, dan nomor HP orang tua) ke grup WhatsApp non-kedinasan atau perangkat pribadi yang tidak terenkripsi.
3. **Disiplin Pembukaan Akses Edit Ulang (*Reopen*)**:
   - Setiap pembukaan akses edit wajib dilandasi permohonan resmi dan alasan kedinasan yang jelas.
   - Berikan batas waktu kedaluwarsa sewajarnya (maksimal 24 jam).
   - Segera tutup akses jika revisi data telah selesai.
4. **Penanganan Berkas Fisik**:
   - Map berkas fisik cetakan yang diserahkan orang tua ke loket PTSP harus disimpan dalam lemari arsip terkunci di ruangan panitia, tidak diletakkan sembarangan di area yang dapat diakses publik.

---

## 4. PEDOMAN KEAMANAN BAGI SUPERADMIN & TIM IT

Sebagai penanggung jawab infrastruktur dan keamanan teknis:

1. **Manajemen Kunci Kriptografi Mandiri**:
   - Lindungi berkas konfigurasi `.env`.
   - Pastikan `APP_KEY`, `NIK_HMAC_KEY`, dan `OTP_HMAC_KEY` bersifat acak menggunakan CSPRNG 256-bit dan tidak pernah di-commit ke Git repositori publik.
2. **Pengawasan Hak Akses Spatie**:
   - Terapkan prinsip hak akses terendah (*Principle of Least Privilege*).
   - Jangan memberikan peran `superadmin` kepada staf operasional biasa. Berikan peran `admin_ppdb` sesuai kebutuhan tugas harian.
3. **Penyimpanan Berkas Privat**:
   - Pastikan direktori `storage/app/private/` selalu tertutup rapat dari akses HTTP langsung oleh web server (Nginx/Apache/LiteSpeed).
4. **Pencadangan Terenkripsi**:
   - Otomatisasi pencadangan harian basis data dan berkas dokumen pendaftar.
   - Pastikan berkas cadangan disimpan pada media penyimpanan yang terisolasi dan terenkripsi.
