# PANDUAN PENGGUNA & DOKUMENTASI OPERASIONAL (SOP)
## Sistem Informasi PPDB Online MIN 1 Kota Madiun (PPDB Minsatama)
### Tahun Ajaran 2026/2027

Selamat datang di repositori dokumentasi operasional resmi Sistem Informasi Penerimaan Peserta Didik Baru (PPDB) Madrasah Ibtidaiyah Negeri (MIN) 1 Kota Madiun. Direktori ini memuat seluruh panduan penggunaan aplikasi, standar operasional prosedur (SOP) kepanitiaan, matriks hak akses, serta panduan keamanan dan pemecahan masalah bagi seluruh pemangku kepentingan.

---

## 1. IDENTITAS & PRINSIP APLIKASI

- **Nama Resmi Aplikasi**: PPDB Online MIN 1 Kota Madiun (PPDB Minsatama)
- **Instansi**: MIN 1 Kota Madiun — Kementerian Agama Republik Indonesia
- **Jenjang Pendidikan**: Calon Peserta Didik Baru Kelas 1 Madrasah Ibtidaiyah (MI)
- **Pilihan Program**: Program Unggulan dan Program Reguler
- **Bahasa Antarmuka**: Bahasa Indonesia murni
- **Zona Waktu Resmi**: Waktu Indonesia Barat (`Asia/Jakarta` / WIB)
- **Warna Identitas Visual**:
  - Hijau Toska Kemenag (Primary): `#0F7FA3`
  - Hijau Madrasah (Secondary): `#0B6E3F`
  - Emas Aksen (Accent): `#F2C94C`
  - Putih Bersih (Background): `#FFFFFF`
  - Teks Utama: `#1F2933`

> [!IMPORTANT]
> **BATASAN CAKUPAN APLIKASI (CRITICAL SCOPE BOUNDARY)**:
> Sistem PPDB Online Minsatama **BERAKHIR** pada saat pendaftar berhasil mengunduh dan mencetak dokumen **Bukti Pendaftaran Resmi (PDF Final)** dan membawanya ke loket Pelayanan Terpadu Satu Pintu (PTSP) madrasah.
> Sistem ini **TIDAK MENGELOLA** proses seleksi akademik, perankingan nilai, penentuan kelulusan/diterima/ditolak, maupun daftar ulang siswa. Dokumen bukti pendaftaran hanyalah bukti pendaftaran administrasi, **BUKAN SURAT KEPUTUSAN DITERIMA**.

---

## 2. DAFTAR INDEKS DOKUMENTASI

Seluruh dokumentasi operasional disusun ke dalam berkas-berkas terstruktur berikut:

| No | Berkas Dokumentasi | Sasaran Pembaca | Ringkasan Isi |
|:---:|---|---|---|
| 01 | [01-PANDUAN-ORANG-TUA-WALI.md](file:///D:/Dany/PPDB/PPDB_Minsa_2026/docs/manual/01-PANDUAN-ORANG-TUA-WALI.md) | Orang Tua / Wali Murid | Panduan lengkap langkah demi langkah dari pendaftaran akun, pengisian 4 modul formulir, unggah berkas, penerbitan PDF Draf & Final, hingga persiapan berkas ke PTSP. |
| 02 | [02-PANDUAN-ADMIN-PPDB.md](file:///D:/Dany/PPDB/PPDB_Minsa_2026/docs/manual/02-PANDUAN-ADMIN-PPDB.md) | Panitia PPDB / Staf Loket | Panduan operasional administrator: login admin, pemantauan pendaftaran, tata kelola pembukaan akses edit ulang (*reopen*), dan penegakan keamanan. |
| 03 | [03-PANDUAN-SUPERADMIN.md](file:///D:/Dany/PPDB/PPDB_Minsa_2026/docs/manual/03-PANDUAN-SUPERADMIN.md) | Superadmin / Tim IT Madrasah | Panduan pengelola tingkat tertinggi: manajemen peran Spatie, pengawasan audit trail teknis, dan isolasi konfigurasi. |
| 04 | [04-MATRIKS-ROLE-DAN-PERMISSION.md](file:///D:/Dany/PPDB/PPDB_Minsa_2026/docs/manual/04-MATRIKS-ROLE-DAN-PERMISSION.md) | Seluruh Pengguna & Auditor | Matriks hak akses detail modul per role (`Applicant`, `Admin PPDB`, `Superadmin`) berdasarkan backend Laravel Spatie Permission aktual. |
| 05 | [05-SOP-OPERASIONAL-PANITIA.md](file:///D:/Dany/PPDB/PPDB_Minsa_2026/docs/manual/05-SOP-OPERASIONAL-PANITIA.md) | Panitia PPDB Madrasah | Standar Operasional Prosedur pelaksanaan PPDB dalam 3 fase waktu (Sebelum Dibuka, Saat Periode Berlangsung, dan Pasca Penutupan). |
| 06 | [06-FAQ-DAN-TROUBLESHOOTING.md](file:///D:/Dany/PPDB/PPDB_Minsa_2026/docs/manual/06-FAQ-DAN-TROUBLESHOOTING.md) | Orang Tua & Admin | Tanya jawab praktis kendala umum (NIK duplikat, OTP, format gambar, berkas rusak, PDF outdated, dan kendala login). |
| 07 | [07-PEDOMAN-KEAMANAN-PENGGUNA.md](file:///D:/Dany/PPDB/PPDB_Minsa_2026/docs/manual/07-PEDOMAN-KEAMANAN-PENGGUNA.md) | Seluruh Pengguna | Petunjuk perlindungan privasi data pribadi anak (NIK, berkas KK/Akta) dan kepatuhan terhadap Undang-Undang Perlindungan Data Pribadi (UU PDP). |
| 08 | [08-ALUR-KERJA-SISTEM.md](file:///D:/Dany/PPDB/PPDB_Minsa_2026/docs/manual/08-ALUR-KERJA-SISTEM.md) | Tim Teknis & Panitia | Diagram alur bisnis komprehensif menggunakan diagram Mermaid (pemisahan alur pendaftar, proses internal server, dan loket fisik PTSP). |
| 09 | [09-GLOSARIUM.md](file:///D:/Dany/PPDB/PPDB_Minsa_2026/docs/manual/09-GLOSARIUM.md) | Seluruh Pengguna | Kamus istilah teknis dan istilah madrasah (NIK Masking, OTP, Consent v1.0, Watermark Draft, Reopen Window, dan Fingerprint SHA-256). |
| 10 | [10-DAFTAR-FITUR-DAN-GAP.md](file:///D:/Dany/PPDB/PPDB_Minsa_2026/docs/manual/10-DAFTAR-FITUR-DAN-GAP.md) | Pimpinan & Developer | Analisis jujur komparasi fitur yang telah terimplementasi di kode sumber vs fitur yang berstatus celah (*gap*) / belum ada antarmuka UI. |
| 11 | [BRIEFING-PANITIA-PPDB.md](file:///D:/Dany/PPDB/PPDB_Minsa_2026/docs/manual/BRIEFING-PANITIA-PPDB.md) | Panitia & Pimpinan | Lembar ringkasan eksekutif cepat untuk materi pengarahan (*briefing*) rapat koordinasi panitia madrasah. |
| 12 | [QUICK-GUIDE-ORANG-TUA.md](file:///D:/Dany/PPDB/PPDB_Minsa_2026/docs/manual/QUICK-GUIDE-ORANG-TUA.md) | Orang Tua / Wali Murid | Panduan kilat 5 menit: infografis teks alur pendaftaran dari pendaftaran akun hingga cetak berkas PDF. |

---

## 3. PANDUAN NAVIGASI & HAK CIPTA

Seluruh naskah panduan di atas disusun berdasarkan implementasi kode aktual repositori pada **Fase 9 / Fase 10**. Jika Anda menemukan ketidaksesuaian antara panduan dengan antarmuka layar, silakan merujuk pada [10-DAFTAR-FITUR-DAN-GAP.md](file:///D:/Dany/PPDB/PPDB_Minsa_2026/docs/manual/10-DAFTAR-FITUR-DAN-GAP.md) untuk melihat fitur mana yang telah didukung antarmuka web dan prosedur mana yang saat ini masih memerlukan bantuan teknis administrator.
