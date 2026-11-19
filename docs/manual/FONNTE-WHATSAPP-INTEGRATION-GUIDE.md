# PANDUAN INTEGRASI GATEWAY WHATSAPP OTP (FONNTE API)
## Sistem Informasi PPDB MIN 1 Kota Madiun (Minsatama)

Dokumen ini adalah panduan operasional dan teknis untuk konfigurasi, verifikasi, dan pemecahan masalah pengiriman OTP WhatsApp menggunakan Fonnte API pada lingkungan lokal, staging (Hostinger), dan produksi.

---

## 1. IKHTISAR ARSITEKTUR OTP WHATSAPP

Sistem PPDB Minsatama menggunakan WhatsApp OTP secara eksklusif untuk 2 (dua) keperluan:
1. **Aktivasi Akun Calon Siswa Baru**: Verifikasi nomor WhatsApp orang tua/wali setelah pengisian formulir pendaftaran akun awal.
2. **Pemulihan Kata Sandi Akun Siswa**: Pengiriman kode verifikasi 6 digit saat orang tua lupa kata sandi akun pendaftar.

> [!IMPORTANT]
> - Login normal pendaftar **TIDAK MENGGUNAKAN OTP**, melainkan menggunakan NIK anak dan kata sandi.
> - Login administrator/superadmin menggunakan Email dan kata sandi.
> - Plaintext OTP **TIDAK PERNAH DISIMPAN** di basis data, melainkan di-hash menggunakan HMAC-SHA256 (`OTP_HMAC_KEY`) yang mengikat `user_id`, `purpose`, `plain_otp`, dan `phone`.
> - Masa berlaku kode OTP tepat **5 menit** dengan batas maksimal **5 kali percobaan**.

---

## 2. CARA MENDAPATKAN TOKEN DARI DASHBOARD FONNTE

1. Buka peramban dan akses portal resmi Fonnte di [https://fonnte.com](https://fonnte.com).
2. Masuk (*login*) ke akun Fonnte Anda.
3. Pada menu navigasi utama, pilih menu **Device**.
4. Jika belum ada perangkat:
   - Klik tombol **Add Device**.
   - Berikan nama perangkat (misal: `PPDB-Minsatama-Gateway`).
5. Pada kartu perangkat tersebut, klik **Scan QR Code**:
   - Buka aplikasi WhatsApp pada ponsel resmi madrasah / panitia PPDB.
   - Buka menu **Perangkat Tertaut (Linked Devices)** &rarr; **Tautkan Perangkat (Link a Device)**.
   - Pindai kode QR yang muncul pada layar dashboard Fonnte hingga status berubah menjadi **Connected** (berwarna hijau).
6. Salin kode rahasia pada kolom **API Token / Token**.
   > [!CAUTION]
   > Token API adalah kredensial berharga. **JANGAN PERNAH** membagikan token melalui chat publik, pesan tiket, atau mengunggahnya ke repositori Git.

---

## 3. PANDUAN PENGISIAN ENVIRONMENT VARIABLE (`.env`)

### A. Konfigurasi Lingkungan Lokal (Pengembangan)
Pada berkas `.env` lokal Anda di komputer pengembangan:
```env
# Aktifkan driver Fonnte
OTP_DRIVER=fonnte

# Kredensial Gateway Fonnte (Isi token Anda sendiri di baris ini)
FONNTE_TOKEN=masukkan_token_fonnte_anda_di_sini
FONNTE_URL=https://api.fonnte.com/send
```

*(Catatan: Untuk pengujian otomatis unit/feature, sistem otomatis menggunakan driver `array` tanpa memotong kuota Fonnte).*

### B. Konfigurasi Lingkungan Staging (Hostinger)
1. Masuk ke **hPanel Hostinger**.
2. Buka menu **File Manager** &rarr; direktori root aplikasi (di luar `public_html`).
3. Buka dan edit berkas `.env`.
4. Sesuaikan baris berikut:
```env
OTP_DRIVER=fonnte
FONNTE_TOKEN=masukkan_token_fonnte_resmi_di_sini
FONNTE_URL=https://api.fonnte.com/send
```
5. Simpan berkas `.env`.
6. Buka terminal SSH atau menu **PHP / Advanced** dan jalankan pembaruan cache konfigurasi:
```bash
php artisan config:clear
php artisan config:cache
```

> [!WARNING]
> Laravel mengabaikan perubahan berkas `.env` jika `config:cache` masih aktif. **WAJIB** menjalankan `php artisan config:cache` setiap kali Anda memperbarui token di server!

---

## 4. CARA MEMASTIKAN PERANGKAT WHATSAPP FONNTE TERHUBUNG

Sebelum melakukan pengujian, pastikan:
1. Ponsel gateway madrasah terhubung ke jaringan internet stabil (WiFi/data seluler).
2. Status perangkat di dashboard Fonnte menunjukkan **Connected**.
3. Kuota pesan WhatsApp pada akun Fonnte masih mencukupi (*active balance/quota*).

---

## 5. CARA PENGUJIAN OTP

### A. Uji Aktivasi Akun
1. Buka formulir registrasi calon siswa pada browser: `/daftar`.
2. Isi data pendaftaran calon siswa dengan nomor WhatsApp aktif yang Anda pegang.
3. Klik tombol **Daftar Sekarang**.
4. Periksa WhatsApp pada nomor yang Anda daftarkan:
   - Pesan resmi berformat standar PPDB Minsatama akan masuk dalam beberapa detik berisi kode 6 digit.
5. Masukkan 6 digit kode OTP pada layar verifikasi `/aktivasi/verifikasi-otp`.
6. Pastikan sistem mengarahkan ke pembuatan kata sandi dan akun berhasil aktif.

### B. Uji Pemulihan Kata Sandi
1. Buka halaman lupa kata sandi: `/lupa-password`.
2. Masukkan NIK calon siswa yang akunnya telah aktif dan nomor WhatsApp-nya terverifikasi.
3. Klik **Kirim Kode OTP Pemulihan**.
4. Periksa pesan WhatsApp yang masuk:
   - Judul pesan memuat *"Kode OTP untuk pemulihan kata sandi akun Anda"*.
5. Masukkan kode pada layar verifikasi dan ubah kata sandi dengan yang baru.

---

## 6. PEMERIKSAAN LOG & PENANGANAN KEGAGALAN PENGIRIMAN

Jika kode OTP tidak masuk ke ponsel, periksa log aplikasi di `storage/logs/laravel.log`:

1. **Token Tidak Dikonfigurasi**:
   - Indikasi log: `Fonnte OTP delivery failed: FONNTE_TOKEN is not configured.`
   - Solusi: Periksa kembali pengisian `FONNTE_TOKEN` pada `.env` dan jalankan `php artisan config:cache`.
2. **Perangkat Terputus (*Device Disconnected*)**:
   - Indikasi log: `Fonnte OTP delivery rejected by provider. reason: device disconnected`
   - Solusi: Buka dashboard Fonnte, tautkan ulang (*scan QR*) ponsel WhatsApp gateway.
3. **Kuota Pesan Habis (*Insufficient Quota*)**:
   - Indikasi log: `Fonnte OTP delivery rejected by provider. reason: insufficient quota`
   - Solusi: Lakukan isi ulang kuota pesan WhatsApp pada dashboard Fonnte.
4. **Nomor Penerima Tidak Valid**:
   - Indikasi log: `Fonnte OTP delivery rejected: Invalid phone number format.`
   - Solusi: Pastikan nomor dimulai dengan 08 / 628 dengan panjang 10-15 digit.
5. **Koneksi Timeout / Terputus**:
   - Indikasi log: `Fonnte OTP delivery connection timed out or unreachable. Message status ambiguous.`
   - Sistem **TIDAK** membatalkan token OTP di database karena pesan mungkin sudah diterima Fonnte. Jika pesan terlambat sampai, pengguna tetap dapat memverifikasinya dalam jendela 5 menit.

---

## 7. PROSEDUR ROTASI TOKEN JIKA BOCOR

Apabila token Fonnte tidak sengaja terungkap ke pihak yang tidak berwenang:
1. Buka dashboard Fonnte &rarr; menu **Device**.
2. Klik tombol **Regenerate Token** / buat token baru. Token lama akan langsung nonaktif seketika.
3. Buka berkas `.env` pada server hosting/VPS dan gantikan `FONNTE_TOKEN` lama dengan token baru.
4. Jalankan perintah terminal:
```bash
php artisan config:clear
php artisan config:cache
```
5. Pengiriman OTP akan langsung beralih ke token baru tanpa *downtime*.
