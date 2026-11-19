# LEMBAR BRIEFING PANITIA PPDB MIN 1 KOTA MADIUN
## Ringkasan Eksekutif Operasional Sistem Informasi PPDB Minsatama
### Tahun Ajaran 2026/2027

Lembaran ini disusun khusus sebagai materi pengarahan (*briefing*) cepat bagi pimpinan madrasah, koordinator loket, verifikator PTSP, dan seluruh panitia pelaksana sebelum periode pendaftaran resmi dibuka.

---

## 1. LIMA POIN UTAMA ARSITEKTUR SISTEM

1. **Satu Siswa Satu Akun**:
   Setiap calon siswa wajib didaftarkan menggunakan NIK 16 digit anak sebagai username akun. Nomor WhatsApp orang tua dapat digunakan bersama jika mendaftarkan anak kembar atau saudara kandung.
2. **Kerahasiaan NIK & Dokumen**:
   NIK siswa tersimpan secara terenkripsi (AES-256-CBC) dan selalu dimasker di antarmuka publik (`3573********0001`). Seluruh berkas (KK, Pas Foto, dan PDF) disimpan di penyimpanan privat yang tidak dapat diakses umum.
3. **Nomor Registrasi Otomatis & Permanen**:
   Nomor resmi pendaftaran terbit seketika saat orang tua menekan tombol *Submit Final* dengan format baku `PPDB/2026-2027/{PROGRAM}/{URUTAN}`. Urutan nomor dijamin tidak akan pernah bentrok atau duplikat.
4. **Verifikasi Keaslian Melalui Kode QR**:
   Setiap lembar Bukti Pendaftaran Resmi (PDF Final) memuat kode QR unik. Petugas loket cukup mengarahkan kamera ponsel ke kode QR untuk memastikan berkas fisik yang dibawa orang tua adalah asli dan versi terbaru.
5. **Akses Edit Ulang Khusus (*Reopen*)**:
   Jika orang tua membutuhkan perbaikan data setelah formulir dikirim atau setelah periode ditutup, panitia dapat membuka akses edit sementara untuk pendaftar tersebut melalui menu Admin dengan mencantumkan alasan resmi.

---

## 2. BATASAN TEGAS RUANG LINGKUP APLIKASI (CRITICAL SCOPE)

Panitia wajib memahami batas akhir tugas sistem daring:

```
[PENDAFTARAN DARING] ──> [CETAK PDF BUKTI RESMI] ──> [SERAHKAN KE PTSP MADRASAH] ──> [SELESAI APLIKASI]
                                                                                             │
                                                                                             v
[SELEKSI MANUAL, UJIAN, PERANKINGAN, PENGUMUMAN & DAFTAR ULANG DILAKUKAN LURING OLEH MADRASAH]
```

- Sistem PPDB Online Minsatama **TIDAK MENGELOLA**:
  - Seleksi akademik / tes minat bakat.
  - Perankingan calon siswa.
  - Keputusan diterima atau ditolak.
  - Pengumuman kelulusan siswa baru.
  - Proses daftar ulang atau penagihan biaya pendidikan.
- Lembar Bukti Pendaftaran Resmi adalah bukti pendaftaran administrasi, **BUKAN SURAT DITERIMA SEBAGAI SISWA**.

---

## 3. CHECKLIST KESIAPAN PANITIA SEBELUM MEMBUKA PENDAFTARAN

Gunakan daftar periksa berikut dalam rapat koordinasi panitia:

- [ ] **Periode & Program Aktif**: Periode 2026/2027 telah berstatus aktif dan jalur Reguler serta Unggulan telah dibuka.
- [ ] **Akun Petugas Loket**: Seluruh petugas admin telah memiliki akun aktif dan berhasil login di `/admin/login`.
- [ ] **Koneksi Internet & Perangkat PTSP**: Komputer loket dan ponsel pemindai QR petugas telah terhubung dengan jaringan internet yang stabil.
- [ ] **Alat Pemindai QR**: Kamera ponsel petugas loket telah diuji untuk memindai kode QR contoh dan berhasil membuka tautan verifikasi resmi madrasah.
- [ ] **Buku Ekspedisi Tanda Terima Berkas**: Panitia loket telah menyiapkan format tanda terima penyerahan berkas fisik untuk diberikan kepada orang tua.
- [ ] **Kanal Layanan Bantuan (Helpdesk)**: Nomor WhatsApp resmi panitia untuk melayani pertanyaan orang tua telah disosialisasikan.

---

## 4. PROTOKOL PENANGANAN KENDALA DI LOKET (INCIDENT MANAGEMENT)

| Kendala Lapangan | Penyebab Umum | Tindakan Petugas Loket |
|---|---|---|
| **Orang tua mengaku NIK anak sudah terdaftar** | Keluarga lain sudah mendaftarkan anak tersebut. | Pandu orang tua untuk menggunakan menu *Lupa Kata Sandi*, atau periksa Kartu Keluarga asli orang tua. |
| **Orang tua tidak menerima kode OTP** | Nomor WhatsApp salah atau sinyal lemah. | Minta orang tua memeriksa kembali nomor telepon dan menekan tombol *Kirim Ulang OTP* setelah 60 detik. |
| **Hasil cetak PDF masih ada tulisan "DRAFT"** | Orang tua belum menekan tombol submit final. | Arahkan orang tua membuka menu *Tinjau & Kirim*, centang kotak persetujuan, dan klik *Kirim Pendaftaran Final*. Lalu unduh ulang PDF. |
| **Kode QR bertuliskan status "Outdated"** | Ada perubahan data yang disimpan setelah PDF dicetak. | Pandu orang tua mengklik tombol *Cetak Ulang PDF Versi Terbaru* di dasbornya agar nomor revisi cocok. |
| **Salah ketik data penting setelah submit** | Kekhilafan orang tua saat mengisi form. | Buka menu `/admin/registrations`, cari nama siswa, klik *Buka akses*, masukkan alasan dan waktu kedaluwarsa, lalu minta orang tua mengedit dan submit ulang. |

---

## 5. KODE ETIK KEAMANAN & PRIVASI PANITIA

1. **Jaga Kerahasiaan Akun**: Dilarang menggunakan akun admin milik orang lain atau meminjamkan akun kepada pihak luar.
2. **Lindungi Data Pribadi Siswa**: Dilarang memotret atau menyebarkan dokumen KK, NIK, atau nomor telepon orang tua ke media sosial atau grup WhatsApp umum.
3. **Dilarang Meminta Kata Sandi Orang Tua**: Petugas madrasah tidak pernah meminta kata sandi akun pendaftar. Jika orang tua lupa, arahkan menggunakan fitur *Lupa Password* mandiri.
4. **Disiplin Logout**: Selalu keluar dari sistem (*logout*) setiap kali meninggalkan komputer meja loket.
