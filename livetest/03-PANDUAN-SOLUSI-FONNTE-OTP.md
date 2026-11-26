# PANDUAN PENANGANAN FONNTE WHATSAPP OTP (PANDUAN-SOLUSI-FONNTE-OTP.md)
### Penjelasan Teknis Batasan AI & Solusi Komprehensif Alur WhatsApp OTP

---

## 1. Pemahaman Masalah: Mengapa AI Tidak Bisa Menerima Pesan WhatsApp?

Pengamatan Anda **sangat tepat dan akurat**:
> *"nah tapi untuk daftar sepertinya kita tidak bisa karena memakai fontte, dimana AI tidak bisa menerima wa. apakah paham ??"*

### Penjelasan Arsitektural:
1. **Fonnte API adalah Layanan Outbound Eksternal**:
   Saat pengguna menekan tombol *"Daftar"* di `/daftar`, Laravel mengirimkan HTTP POST request ke server Fonnte (`https://api.fonnte.com/send`). Fonnte kemudian meneruskan pesan tersebut melalui jaringan WhatsApp resmi ke nomor handphone tujuan.
2. **Ketiadaan Perangkat Fisik pada AI Agent**:
   AI coding agent beroperasi di dalam lingkungan terminal dan browser terisolasi. AI **tidak memiliki kartu SIM fisik, nomor ponsel riil, maupun aplikasi WhatsApp aktif** yang terhubung ke internet.
3. **Konsekuensi Operasional**:
   - AI **bisa** membuka form `/daftar` dan mengisi data.
   - AI **bisa** men-trigger tombol kirim sehingga Fonnte mengirim pesan ke WhatsApp.
   - Namun, AI **tidak bisa membaca** isi pesan notifikasi WhatsApp yang masuk ke HP orang tua (kode OTP 6-digit dan password acak sementara) secara otomatis tanpa perantara.

---

## 2. Tiga Solusi untuk Menjalankan Live Test

Untuk mengatasi batasan di atas tanpa merusak fitur keamanan produksi, telah disiapkan 3 opsi alur pengujian:

```
┌────────────────────────────────────────────────────────────────────────┐
│                        PILIHAN STRATEGI PENGUJIAN                      │
├────────────────────────────────────────────────────────────────────────┤
│                                                                        │
│  [STRATEGI 1: REKOMENDASI TERBAIK]                                     │
│  Gunakan Akun Pre-Seeded (Bypass Registrasi Awal)                      │
│  ────────────────────────────────────────────────                      │
│  • Sudah tersedia akun NIK 3577022003990001 (DRAFT)                    │
│  • Sudah tersedia akun NIK 3577022003990002 (SUBMITTED)                │
│  • Sudah tersedia akun NIK 3577022003990003 (VERIFIED)                 │
│  • AI langsung login di /login dengan NIK + Password Pendaftar2026!    │
│  • TIDAK MEMERLUKAN WHATSAPP SAMA SEKALI.                              │
│                                                                        │
├────────────────────────────────────────────────────────────────────────┤
│                                                                        │
│  [STRATEGI 2: UJI REAL-WORLD HUMAN-IN-THE-LOOP]                        │
│  Pengujian Registrasi Baru Melibatkan Handphone Anda                   │
│  ───────────────────────────────────────────────────                   │
│  • AI atau Anda mengisi form di /daftar memakai NO. WA RIIL ANDA       │
│  • Pesan Fonnte masuk ke WhatsApp di ponsel fisik Anda                 │
│  • Anda menyalin 6 digit kode OTP dan menempelkannya ke chat AI        │
│  • AI memasukkan kode di /aktivasi                                     │
│  • Password sementara masuk ke WhatsApp Anda, lalu diberikan ke AI     │
│  • AI login di /login untuk membuktikan flow ujung-ke-ujung            │
│                                                                        │
├────────────────────────────────────────────────────────────────────────┤
│                                                                        │
│  [STRATEGI 3: INSPEKSI LOG BASIS DATA (LINGKUNGAN LOKAL)]              │
│  Membaca Kode OTP Langsung dari Server                                 │
│  ─────────────────────────────────────                                 │
│  • Pada lingkungan lokal, OTP di-hash di tabel `otp_codes`             │
│  • Dapat dibuatkan bypass log sementara di terminal jika diperlukan    │
│                                                                        │
└────────────────────────────────────────────────────────────────────────┘
```

---

## 3. Tata Cara Pelaksanaan Skema Default (Akun Dummy — Tanpa OTP)

Ini adalah skema yang digunakan untuk live test fitur aplikasi. Tidak perlu membuka `/daftar`, tidak perlu menerima OTP, dan tidak perlu mengirim data ke nomor WhatsApp.

1. Siapkan database lokal/test:
   ```bash
   php artisan migrate
   php artisan db:seed --class=LiveTestSeeder
   php artisan optimize:clear
   ```
2. Gunakan akun dummy pada `02-KREDENSIAL-DAN-AKUN-UJI.md`.
3. Uji alur sesuai peran pada `01-SKENARIO-PER-ROLE.md`.
4. Reset kondisi data uji dengan menjalankan ulang `LiveTestSeeder` bila diperlukan.

Pendaftaran akun baru dan integrasi OTP Fonnte hanya diuji manual oleh pemilik proyek menggunakan nomor WhatsApp milik sendiri.

## 4. Tata Cara Pelaksanaan Strategi 1 Lama (Pre-Seeded — 100% Mandiri oleh AI)

Jika Anda ingin AI melakukan live test secara mandiri menggunakan browser:

1. Pastikan seeder telah dieksekusi:
   ```bash
   php artisan db:seed --class=LiveTestSeeder
   ```
2. AI langsung mengarahkan browser ke:
   - Halaman Masuk: `http://localhost:8000/login` atau `https://ahmaddanyfn.biz.id/login`
   - NIK: `3577022003990001`
   - Kata Sandi: `Pendaftar2026!`
3. AI menguji seluruh fitur inti:
   - Kelengkapan 5 modul formulir.
   - Unggah berkas dokumen (KK, Akta, Pas Foto).
   - Pratinjau draf PDF ber-watermark DRAFT.
   - Pengiriman final pendaftaran (Consent v1.0).
   - Pengecekan status pendaftaran yang terkunci.
4. AI berpindah browser ke `/admin/login`:
   - Email: `admin1@minsatama.sch.id`
   - Kata Sandi: `Minsatama2026!`
   - Membuka data siswa Budi Santoso / Siti Rahmawati, lalu menekan **"Sahkan Berkas & Terbitkan Nomor"**.

*Dengan cara ini, 95% alur sistem teruji secara live dan tuntas tanpa ketergantungan pada nomor handphone.*

---

## 5. Tata Cara Pelaksanaan Strategi 2 (Human-in-the-Loop — Uji Integrasi Fonnte Riil)

Jika Anda ingin membuktikan bahwa integrasi Fonnte di server produksi benar-benar mengirimkan WhatsApp secara live:

1. **Langkah 1 (Isi Form Registrasi)**:
   - Buka halaman `/daftar`.
   - Masukkan NIK uji baru (16 digit angka, misal: `3577022003990099`).
   - Masukkan Nama Lengkap: *"Test Fonnte Siswa"*.
   - Masukkan **Nomor WhatsApp Riil Milik Anda** (misal: `08xxxxxxxxxx`).
   - Centang persetujuan kebenaran data, lalu klik tombol **"Daftar & Kirim Kode OTP"**.
2. **Langkah 2 (Terima OTP di Handphone Anda)**:
   - Dalam 2–5 detik, bot WhatsApp MINSATAMA akan mengirim pesan ke nomor Anda:
     > *"Kode verifikasi pendaftaran PPDB MIN 1 Kota Madiun Anda adalah: **123456**. Kode berlaku selama 5 menit..."*
   - Beritahukan 6 digit kode tersebut di chat ("kodenya 123456").
3. **Langkah 3 (Verifikasi OTP)**:
   - Kode dimasukkan pada halaman `/aktivasi`.
   - Klik **"Verifikasi Akun"**.
4. **Langkah 4 (Terima Kata Sandi Masuk)**:
   - Bot WhatsApp MINSATAMA mengirim pesan kedua:
     > *"Verifikasi akun PPDB Anda berhasil.*  
     > *Berikut rincian akun untuk masuk ke aplikasi:*  
     > *• NIK: 3577022003990099*  
     > *• Password: AbCd1234Xy*  
     > *• Nomor Pendaftaran: — (Menunggu Pengesahan Berkas)*  
     > *Password ini dapat diganti di dasbor pendaftar."*
   - Beritahukan kata sandi tersebut di chat ("passwordnya AbCd1234Xy").
5. **Langkah 5 (Masuk ke Dasbor)**:
   - Masuk di `/login` dengan NIK dan kata sandi sementara tersebut.
   - Pendaftar langsung masuk ke `/pendaftar/dashboard`.

---

## 6. Ringkasan Rekomendasi untuk Sesi Live Test

- Untuk **uji fitur aplikasi**: Gunakan **Skema Default Akun Dummy** karena cepat, stabil, repeatable, dan tidak memerlukan WhatsApp.
- Untuk **uji coba khusus pengiriman WhatsApp**: Pemilik proyek menjalankan **Strategi Human-in-the-Loop** secara manual satu kali menggunakan nomor WhatsApp sendiri.
