# STANDAR OPERASIONAL PROSEDUR (SOP) KEPANITIAAN PPDB
## Sistem Informasi PPDB Online MIN 1 Kota Madiun (PPDB Minsatama)
### Tahun Ajaran 2026/2027

Dokumen ini adalah pedoman operasional baku bagi seluruh panitia pelaksana PPDB MIN 1 Kota Madiun, petugas loket PTSP, verifikator berkas, serta tim IT madrasah.

---

## 1. PRINSIP DASAR & BATASAN PELAKSANAAN

1. **Prinsip Pelayanan**: Akuntabel, transparan, non-diskriminatif, dan mengutamakan kerahasiaan data pribadi anak.
2. **Kepatuhan Batasan Cakupan Sistem**:
   - Sistem daring PPDB Minsatama **BERAKHIR** pada saat pendaftar mencetak Bukti Pendaftaran Resmi (PDF Final) dan menyerahkannya ke loket PTSP madrasah.
   - Sistem **TIDAK MENGELOLA**:
     - Ujian seleksi atau pemeringkatan nilai.
     - Keputusan penerimaan atau penolakan siswa.
     - Pengumuman kelulusan siswa baru.
     - Proses daftar ulang dan penagihan biaya pendidikan.
3. Seluruh tahapan setelah penyerahan berkas di loket PTSP dilakukan secara luring (*offline*) oleh pimpinan dan panitia madrasah sesuai regulasi Kementerian Agama.

---

## 2. PROSEDUR SEBELUM PERIODE DIBUKA (FASE PRA-PENDAFTARAN)

```
[Verifikasi Periode & Program] ──> [Uji Akun Admin & OTP] ──> [Uji Alur Pendaftaran & PDF] ──> [Rilis Pengumuman]
```

### Langkah Kerja Panitia:
1. **Pemeriksaan Status Periode Aktif**:
   - Koordinator IT madrasah memeriksa tabel `admission_periods` dan memastikan periode tahun ajaran aktif (misal: `2026/2027`) memiliki status `is_active = 1` dengan rentang tanggal `start_date` dan `end_date` yang tepat.
   - Memastikan tanggal acuan umur (`age_reference_date = 2026-07-01`) telah terpasang.
2. **Pemeriksaan Jalur Program Pendidikan**:
   - Memastikan dua program aktif tersedia pada tabel `programs`: `REGULER` dan `UNGGULAN`.
3. **Pemeriksaan Persyaratan Berkas**:
   - Memastikan syarat dokumen wajib (Kartu Keluarga dan Pas Foto) telah dikonfigurasi dengan batas ukuran 5120 KB (5 MB).
4. **Verifikasi Kesiapan Akun Petugas Admin**:
   - Memastikan seluruh akun panitia telah memiliki peran `admin_ppdb` dan dapat masuk melalui `/admin/login`.
   - Mengganti kata sandi bawaan seeder demi keamanan.
5. **Uji Asap Sistem (*Smoke Testing*)**:
   - Tim IT melakukan 1 kali simulasi pendaftaran uji coba: membuat akun dummy, aktivasi OTP, pengisian modul 1–4, unggah berkas, submit, cetak PDF bukti pendaftaran, dan pemindaian kode QR via kamera ponsel.
   - Memastikan QR mengarah ke halaman verifikasi publik yang sah tanpa membocorkan data pribadi.
6. **Sosialisasi Brosur & Panduan Orang Tua**:
   - Membagikan dokumen [QUICK-GUIDE-ORANG-TUA.md](QUICK-GUIDE-ORANG-TUA.md) kepada masyarakat luas melalui kanal resmi madrasah.

---

## 3. PROSEDUR SAAT PERIODE BERLANGSUNG (FASE PELAKSANAAN)

### 3.1 Layanan Loket Informasi & Helpdesk
1. Petugas helpdesk madrasah membantu orang tua yang datang atau bertanya melalui telepon/pesan dinas madrasah.
2. Membantu kendala teknis orang tua (misal: gagal aktivasi OTP, salah ketik nomor telepon, atau kesulitan mengecilkan ukuran foto).
3. **PANTANGAN HELPDESK**:
   - Petugas dilarang meminta atau mencatat kata sandi akun pendaftar.
   - Petugas dilarang mengisi formulir menggunakan akun pribadi petugas. Bimbing orang tua agar mengisi formulir dari ponsel/perangkat mereka sendiri.

### 3.2 Prosedur Pelayanan Pembukaan Akses Edit Ulang (*Reopen Registration*)
Jika orang tua siswa yang telah melakukan submit final datang ke loket madrasah untuk memperbaiki kesalahan data penting:

1. **Pemeriksaan Permohonan Fisik**:
   - Orang tua wajib memperlihatkan Kartu Keluarga asli dan identitas resmi pemohon.
   - Petugas loket mencatat alasan spesifik yang mendasari permohonan perbaikan (contoh: *"Perbaikan ejaan nama anak sesuai Akta Kelahiran asli"*).
2. **Eksekusi Pembukaan Akses di Sistem**:
   - Admin login ke `/admin/registrations`.
   - Cari data siswa, klik tautan **"Buka akses"**.
   - Masukkan alasan pembukaan resmi secara lengkap.
   - Tetapkan batas waktu kedaluwarsa akses (misal: 6 jam ke depan).
   - Klik **"Buka akses edit ulang"**.
3. **Pemberitahuan kepada Orang Tua**:
   - Petugas menginformasikan kepada orang tua bahwa formulir pendaftaran di akun siswa telah terbuka kembali untuk diedit.
   - Petugas mengingatkan bahwa setelah data diperbaiki, orang tua **wajib menekan tombol submit final kembali dan mencetak ulang lembar Bukti Pendaftaran Resmi versi terbaru**.
4. **Penutupan Akses Dini**:
   - Jika orang tua telah menyelesaikan perbaikan di hadapan petugas, petugas segera mengklik **"Tutup akses sekarang"** pada menu admin untuk mengunci kembali formulir.

### 3.3 Pelayanan Penerimaan Berkas Fisik di Loket PTSP Madrasah
Saat orang tua menyerahkan berkas ke madrasah:
1. Petugas loket menerima map berkas pendaftaran.
2. Petugas mengambil lembar Bukti Pendaftaran Resmi (PDF Final) hasil cetakan orang tua.
3. **Pemindaian Kode QR**:
   - Petugas menggunakan kamera ponsel pintar atau alat pemindai QR untuk memindai kode QR di sudut lembar bukti cetak.
   - Buka tautan yang muncul pada layar pemindai (`https://<domain>/verifikasi/...`).
   - Periksa kecocokan **Nomor Registrasi**, **Tanggal Terbit**, dan status **Valid / Current**.
   - Jika dokumen berstatus *Outdated*, minta orang tua mencetak versi terbaru dari akunnya.
4. **Pemeriksaan Kelengkapan Berkas Fisik**:
   - Mencocokkan data pada lembar cetak dengan berkas fisik asli (KK asli, Akta Kelahiran asli, dan surat keterangan TK/RA).
   - Memastikan lembar bukti cetak telah ditandatangani asli oleh orang tua/wali murid.
5. Petugas memberikan tanda terima penyerahan berkas fisik kepada orang tua.

---

## 4. PROSEDUR SETELAH PERIODE DITUTUP (FASE PASCA-PENDAFTARAN)

1. **Penguncian Akses Pendaftaran Global**:
   - Sistem secara otomatis mengunci seluruh pendaftaran ketika waktu server melewati `end_date` periode aktif.
   - Seluruh formulir calon siswa beralih ke mode baca-saja (*read-only*).
2. **Penanganan Permohonan Reopen Pasca-Penutupan**:
   - Pembukaan akses edit pasca-penutupan hanya dapat dilakukan melalui persetujuan Ketua Panitia PPDB dengan mencatat nota dinas resmi.
3. **Penyerahan Berkas ke Tim Seleksi Luring**:
   - Panitia loket menyerahkan seluruh bundel map berkas fisik pendaftar yang telah tervalidasi ke Tim Seleksi Madrasah untuk diproses pada tahapan seleksi manual/wawancara luring madrasah.
4. **Pencadangan Data (*Backup Data*)**:
   - Tim IT melakukan pencadangan penuh database pendaftaran dan seluruh berkas fisik digital di `storage/app/private/` sebagai arsip permanen madrasah.
