# PANDUAN FAQ & PEMECAHAN MASALAH (TROUBLESHOOTING)
## Sistem Informasi PPDB Online MIN 1 Kota Madiun (PPDB Minsatama)
### Tahun Ajaran 2026/2027

Dokumen ini memuat tanya-jawab praktis dan panduan solusi kendala teknis yang dipisahkan secara tegas untuk **Kelompok Orang Tua/Wali Pendaftar** dan **Kelompok Administrator/Panitia Madrasah**.

---

## BAGIAN A: FAQ & KENDALA ORANG TUA / WALI (APPLICANT)

### 1. Kendala Akun & Pendaftaran Awal

#### Q1.1: Muncul pesan error "NIK sudah terdaftar dalam sistem".
- **Gejala**: Saat menekan tombol daftar, sistem menolak dengan pesan NIK telah digunakan.
- **Penyebab**: NIK 16 digit anak Anda sebelumnya sudah pernah didaftarkan (mungkin oleh pasangan atau anggota keluarga lain). Satu NIK hanya dapat memiliki 1 akun di seluruh sistem.
- **Solusi**:
  1. Jangan mendaftar ulang. Gunakan NIK anak tersebut langsung pada halaman **Login / Masuk**.
  2. Jika lupa kata sandi yang pernah dibuat, gunakan menu **"Lupa Kata Sandi"**.
  3. Jika merasa belum pernah mendaftar, hubungi helpdesk madrasah dengan membawa Kartu Keluarga asli untuk verifikasi data.

#### Q1.2: Kode OTP WhatsApp tidak kunjung masuk ke ponsel.
- **Gejala**: Menunggu lebih dari 2 menit tetapi belum ada pesan WhatsApp masuk.
- **Penyebab**:
  - Nomor telepon yang diketik salah atau tidak terhubung ke akun WhatsApp aktif.
  - Sinyal seluler/koneksi internet ponsel lemah.
  - *Kondisi Staging*: Pada masa pengujian staging, pengiriman OTP ke nomor riil dinonaktifkan demi privasi.
- **Solusi**:
  1. Periksa kembali nomor telepon yang dimasukkan.
  2. Tunggu hingga tombol **"Kirim Ulang OTP"** aktif (setelah 60 detik), lalu klik kirim ulang.
  3. Pastikan format penulisan nomor dimulai dengan `08...` (contoh: `081234567890`).

#### Q1.3: Muncul pesan "Kode OTP telah kedaluwarsa".
- **Gejala**: Kode OTP yang dimasukkan ditolak oleh sistem.
- **Penyebab**: Kode OTP hanya berlaku tepat selama **5 menit**. Anda memasukkannya setelah melewati batas waktu tersebut.
- **Solusi**: Klik tombol **"Kirim Ulang OTP"** untuk mendapatkan kode baru berdurasi 5 menit yang segar.

#### Q1.4: Bisakah satu nomor WhatsApp dipakai untuk anak kembar / adik kandung?
- **Jawaban**: **BISA**. Sistem mendukung pendaftaran saudara kandung (*sibling support*). Anda dapat menggunakan nomor WhatsApp yang sama, namun **NIK anak harus berbeda**. Buatlah akun baru untuk masing-masing anak.

---

### 2. Kendala Formulir & Pengisian Data

#### Q2.1: Kenapa kolom NIK calon siswa terkunci dan tidak bisa diubah di form Biodata?
- **Penyebab**: NIK adalah identitas unik dan username akun anak di database. Untuk mencegah manipulasi identitas, NIK dikunci permanen (*read-only*).
- **Solusi**: Jika terjadi kesalahan ketik NIK saat registrasi awal, Anda harus berkonsultasi ke loket PTSP madrasah untuk dibantu koreksi teknis.

#### Q2.2: Angka nol di depan RT/RW saya (contoh `002`) hilang atau terpotong?
- **Penyebab**: Sistem lama terkadang mengubah angka menjadi format matematika.
- **Solusi**: Sistem PPDB Minsatama 2026 telah dirancang khusus mempertahankan angka nol di depan (*preserves leading zeroes*). Ketikkan `002` atau `005`, data akan tersimpan sempurna.

#### Q2.3: Saya tidak memiliki Wali, apakah kolom data wali wajib diisi?
- **Solusi**: **TIDAK WAJIB**. Cukup biarkan toggle *"Siswa tinggal bersama wali"* dalam posisi nonaktif. Anda hanya diwajibkan melengkapi data Ayah Kandung dan Ibu Kandung.

---

### 3. Kendala Unggah Dokumen (Upload Berkas)

#### Q3.1: Muncul pesan "Ukuran berkas melebihi batas maksimal".
- **Gejala**: Berkas gagal terunggah ke sistem.
- **Penyebab**: Ukuran berkas dokumen Anda lebih besar dari **5 MB (5120 KB)**.
- **Solusi**:
  1. Gunakan aplikasi pengecil ukuran gambar (*image compressor*) atau situs online gratis untuk mengompres ukuran foto.
  2. Turunkan resolusi kamera ponsel sebelum memotret dokumen.
  3. Pastikan ukuran akhir berkas berada di antara 300 KB – 2 MB agar tetap tajam namun tidak melampaui batas server.

#### Q3.2: Muncul pesan "Format berkas tidak didukung atau berkas rusak".
- **Penyebab**: Anda mengunggah format selain PDF, JPG, JPEG, atau PNG, atau berkas mengalami kerusakan struktur biner.
- **Solusi**: Sistem memeriksa isi biner (*magic bytes*) asli berkas. Jangan mengubah ekstensi file secara manual (misal mengubah `.doc` menjadi `.pdf` dengan rename teks). Simpan ulang dokumen Anda ke format PDF atau JPG asli.

---

### 4. Kendala Pengiriman Final & Cetak PDF

#### Q4.1: Tombol "Kirim Pendaftaran Final" tidak bisa diklik / berwarna abu-abu.
- **Penyebab**: Salah satu modul pendaftaran atau dokumen persyaratan wajib belum berstatus "Lengkap" (Progress bar belum mencapai 100%).
- **Solusi**: Masuk ke dasbor, periksa modul mana yang masih bertanda *"Belum Lengkap"*. Lengkapi isian data atau berkas yang kurang.

#### Q4.2: Mengapa pada cetakan PDF saya ada tulisan miring besar "DRAFT - BUKAN BUKTI RESMI"?
- **Penyebab**: Anda mengunduh dokumen sebelum menekan tombol Kirim Pendaftaran Final (status masih `DRAFT`).
- **Solusi**: Kirimkan pendaftaran final terlebih dahulu di menu *Tinjau & Kirim*. Setelah status berubah menjadi `SUBMITTED`, klik tombol cetak kembali. Dokumen resmi akan terbit bersih tanpa tulisan DRAFT.

#### Q4.3: Dokumen PDF saya bertuliskan status "Outdated" (Perlu Diterbitkan Ulang).
- **Penyebab**: Anda pernah melakukan pembaruan data setelah pendaftaran final dikirim. Sistem mencatat revisi baru sehingga PDF cetakan lama dinyatakan usang.
- **Solusi**: Klik tombol **"Cetak Ulang PDF Versi Terbaru"** di dasbor Anda. Nomor registrasi Anda tidak akan berubah.

---

## BAGIAN B: FAQ & KENDALA ADMINISTRATOR PPDB (PANITIA)

#### Q5.1: Muncul pesan HTTP 403 Forbidden saat membuka menu edit ulang.
- **Penyebab**: Akun admin yang digunakan tidak memiliki izin (*permission*) `reopen_registration`.
- **Solusi**: Hubungi Superadmin madrasah untuk memastikan peran akun Anda adalah `admin_ppdb` atau `superadmin` yang memiliki hak akses tersebut.

#### Q5.2: Gagal membuka akses edit ulang dengan pesan "Expires at harus waktu di masa depan".
- **Penyebab**: Petugas memilih jam atau tanggal kedaluwarsa yang sudah lewat dari jam server saat ini.
- **Solusi**: Pilih tanggal dan jam minimal 1 jam atau beberapa jam ke depan dari waktu sekarang.

#### Q5.3: Pas foto berformat PNG gagal tampil saat generate PDF dan memunculkan error server.
- **Penyebab**: Ekstensi PHP `gd` belum aktif pada server web hosting. Mesin Dompdf membutuhkan ekstensi GD untuk merender gambar PNG.
- **Solusi**:
  - Solusi Sementara: Minta orang tua mengunggah pas foto dalam format JPG (format JPG didukung langsung tanpa ekstensi GD).
  - Solusi Permanen: Hubungi tim IT/hosting untuk mengaktifkan ekstensi PHP `gd` di control panel server (hPanel / aaPanel).

#### Q5.4: Pendaftar mengaku sudah bisa mengedit padahal periode pendaftaran telah ditutup, apakah sistem bocor?
- **Penjelasan**: Periksa apakah ada panitia lain yang telah membuka akses edit ulang (*reopen*) untuk pendaftar tersebut. Buka detail pendaftaran siswa di menu admin dan lihat riwayat pembukaan pada kartu *Riwayat Pembukaan dan Penutupan*. Jika diperlukan, klik tombol **"Tutup akses sekarang"** untuk mengunci kembali formulir.
