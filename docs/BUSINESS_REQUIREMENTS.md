# BUSINESS REQUIREMENTS — PPDB MINSATAMA
## Aturan Bisnis Resmi Sistem Penerimaan Peserta Didik Baru MIN 1 Kota Madiun

Dokumen ini berisi seluruh aturan bisnis resmi yang telah disetujui oleh Pemilik Proyek. Aturan di bawah ini bersifat mengikat dan **TIDAK BOLEH DIUBAH SECARA SEPIHAK**.

---

## 1. ATURAN AKUN & PENDAFTAR (APPLICANT ACCOUNTS)

1. **Satu Calon Siswa Satu Akun**:
   - Satu akun mewakili satu calon peserta didik baru.
   - NIK calon siswa (16 digit) adalah *username* resmi untuk masuk ke sistem.
   - NIK bersifat unik global di seluruh aplikasi. Satu NIK hanya dapat didaftarkan satu kali dan tidak boleh ada akun ganda.
2. **Dukungan Pendaftaran Saudara Kandung (*Sibling Support*)**:
   - Nomor telepon/WhatsApp orang tua **dapat digunakan kembali** untuk mendaftarkan akun anak lainnya (saudara kandung).
   - Orang tua yang memiliki lebih dari satu anak calon siswa (misal: anak kembar atau saudara) wajib membuat akun terpisah untuk masing-masing anak dengan NIK masing-masing.
3. **Penggunaan Akun Lintas Periode**:
   - Akun pendaftar yang sudah dibuat tetap valid dan dapat digunakan kembali pada periode pendaftaran berikutnya jika siswa mendaftar lagi di tahun ajaran berbeda.
4. **Keamanan & Privasi NIK**:
   - NIK calon siswa wajib disimpan dalam bentuk terenkripsi menggunakan AES-256-CBC (`nik_encrypted`).
   - Pencarian NIK di basis data wajib menggunakan hash deterministik HMAC-SHA256 (`nik_lookup_hash`) dengan kunci rahasia mandiri.
   - NIK dilarang keras dicatat dalam log sistem dalam bentuk plaintext.
   - NIK harus selalu dimasker pada antarmuka biasa (format: `3573********0001`).
   - Sistem **TIDAK MENGINTEGRASIKAN** layanan pengecekan Dukcapil tidak resmi demi menjaga kepatuhan privasi data pribadi.

---

## 2. ATURAN AUTENTIKASI & OTORISASI (AUTHENTICATION & ROLES)

1. **Struktur Guard & Tabel**:
   - Satu tabel `users` untuk seluruh jenis pengguna.
   - Satu guard `web` standar Laravel.
   - Calon siswa masuk menggunakan NIK dan kata sandi (`account_type = applicant`).
   - Administrator/panitia masuk menggunakan Email dan kata sandi (`account_type = admin`).
2. **Manajemen Hak Akses Administrator**:
   - Menggunakan Spatie Laravel Permission pada guard `web`.
   - Peran awal: `superadmin` dan `admin_ppdb`.
   - Calon siswa **DILARANG KERAS** memiliki peran administratif maupun hak akses ke area `/admin/*`.
   - Administrator dilarang mengotentikasi diri melalui formulir login calon siswa.
3. **Status Akun (`AccountStatus`)**:
   - `PENDING_ACTIVATION`: Akun pendaftar baru yang belum memverifikasi nomor telepon atau belum membuat kata sandi. Ditolak login normal dan diarahkan ke halaman aktivasi.
   - `ACTIVE`: Akun yang sah dan diizinkan masuk ke sistem.
   - `SUSPENDED`: Akun yang dinonaktifkan oleh panitia. Akses ditolak dan sesi aktif langsung diputus (*force logout*).
4. **Alur Pembuatan Kata Sandi**:
   - Saat pendaftaran awal, akun belum memiliki kata sandi aktif (disimpan hash acak tak dapat ditebak).
   - Kata sandi dibuat secara mandiri oleh pendaftar setelah verifikasi kode OTP berhasil.
   - Kata sandi tidak boleh menggunakan NIK siswa itu sendiri.

---

## 3. ATURAN VERIFIKASI OTP (ONE-TIME PASSWORD)

1. **Kebutuhan OTP**:
   - OTP diwajibkan saat **Aktivasi Akun Baru** (langkah pendaftaran).
   - OTP diwajibkan saat **Pemulihan Kata Sandi** (lupa password).
   - OTP **TIDAK DIWAJIBKAN** saat login normal sehari-hari (cukup NIK + Password).
2. **Spesifikasi & Keamanan OTP**:
   - Panjang OTP tepat 6 digit angka numerik acak kriptografis (`random_int`).
   - Masa berlaku OTP tepat 5 menit sejak diterbitkan.
   - Batas maksimal percobaan salah adalah 5 kali. Jika batas terlampaui, token terkunci (*exhausted*).
   - Token OTP bersifat *single-use* (hanya dapat digunakan satu kali) dan dicatat waktu konsumsinya (`verified_at`).
   - Permintaan kirim ulang (*resend*) otomatis mengedaluwarsa seluruh token lama yang belum terverifikasi.
   - Hash OTP dihitung menggunakan HMAC-SHA256 dengan kunci rahasia mandiri `OTP_HMAC_KEY` dan mengikat konteks `user_id`, `purpose`, `plain_otp`, dan nomor telepon.
3. **Pernyataan Kepemilikan**:
   - Verifikasi OTP WhatsApp hanya membuktikan penguasaan terhadap nomor telepon yang didaftarkan, bukan bukti kepemilikan yuridis atas NIK anak. Validasi fisik dilakukan kemudian di loket PTSP.

---

## 4. ATURAN PENDAFTARAN & JALUR PENDIDIKAN (REGISTRATION WORKFLOW)

1. **Tingkat Kelas**:
   - Pendaftaran hanya dibuka untuk calon peserta didik baru **Kelas 1 Madrasah Ibtidaiyah (MI)**.
2. **Pilihan Program Pendidikan**:
   - Tersedia dua jalur program: **Program Unggulan** dan **Program Reguler**.
   - Pendaftar memilih salah satu program yang aktif pada periode berjalan.
3. **Batasan Kuota**:
   - **TIDAK ADA PEMBATASAN KUOTA PENDAFTARAN** pada formulir pendaftaran. Sistem tidak menolak pendaftaran atas dasar terpenuhinya kuota.
4. **Batasan Pendaftaran per Periode**:
   - Satu calon siswa hanya boleh memiliki **satu pendaftaran aktif** pada periode penerimaan yang sama.
5. **Status Pendaftaran**:
   - Hanya mengenal 2 status resmi: **`DRAFT`** dan **`SUBMITTED`**.
   - `DRAFT`: Pendaftar sedang mengisi formulir atau mengunggah berkas. Dapat diubah sewaktu-waktu. Belum memiliki nomor registrasi resmi. Tidak masuk dalam rekapitulasi statistik publik.
   - `SUBMITTED`: Pendaftaran telah dikirim secara final oleh pendaftar. Menerbitkan nomor registrasi unik resmi. Masuk ke dalam rekapitulasi statistik.
6. **Perubahan Data Setelah Submit**:
   - Pendaftar tetap diperkenankan mengubah data yang telah di-submit selama periode pendaftaran masih dibuka.
   - Setiap perubahan pada status submitted akan mencatat riwayat revisi dan menaikkan versi dokumen PDF bukti pendaftaran.
7. **Nomor Registrasi Resmi**:
   - Format nomor adalah `YYNNNN`, contoh `270001` untuk tahun penerimaan 2027.
   - Sequence global per tahun penerimaan, dipakai bersama `REGULER` dan `UNGGULAN`, maksimal 9.999.
   - Nomor diterbitkan atomik saat pendaftaran disahkan panitia setelah dokumen wajib `APPROVED`.
8. **Persetujuan Submit**:
   - Sebelum submit, orang tua/wali menyetujui pernyataan versi `v1.0`: “Saya selaku orang tua/wali menyatakan bahwa seluruh data yang diisikan adalah benar dan dapat dipertanggungjawabkan.”
   - Versi dan pernyataan yang diterima disimpan bersama pendaftaran.

---

## 4A. MODUL WAWANCARA PPDB RESMI

1. Sistem resmi mengelola batch/jadwal wawancara, tahap, kriteria, role pewawancara, sesi, nilai, rekap, koreksi ber-audit, dan ekspor CSV.
2. Sequential gate wajib ditegakkan antartahap sesuai program dan status sesi.
3. Penyelesaian wawancara tidak sama dengan kelulusan. Sistem tidak melakukan seleksi akhir, perankingan, pengumuman lulus/tolak, atau daftar ulang.

---

## 5. ATURAN DATA PROFIL PENDAFTAR (APPLICANT INFORMATION)

1. **Biodata Siswa**:
   - NIK (16 digit, valid, terenkripsi).
   - Nama lengkap (sesuai Akta Kelahiran).
   - Jenis kelamin (Laki-laki / Perempuan).
   - Tempat dan tanggal lahir.
   - NISN (opsional, jika sudah memiliki dari TK/RA).
   - Anak ke-berapa dan jumlah saudara.
2. **Data Alamat Domisili**:
   - Alamat jalan / dusun, RT, RW.
   - Kelurahan / Desa, Kecamatan, Kota / Kabupaten, Provinsi, Kode Pos.
3. **Data Orang Tua & Wali**:
   - **Data Ayah Kandung**: WAJIB (Nama, NIK, Status Hidup/Meninggal, Pendidikan Terakhir, Pekerjaan, Penghasilan, No. HP).
   - **Data Ibu Kandung**: WAJIB (Nama, NIK, Status Hidup/Meninggal, Pendidikan Terakhir, Pekerjaan, Penghasilan, No. HP).
   - **Data Wali**: OPSIONAL (diisi jika anak tinggal bersama wali selain orang tua kandung).
4. **Riwayat Pendidikan Sebelumnya**:
   - Asal sekolah anak (TK, RA, PAUD, atau Tidak Sekolah TK).
   - Nama sekolah asal dan alamat.

---

## 6. ATURAN PERHITUNGAN USIA (AGE CALCULATION)

1. **Perhitungan Otomatis**:
   - Usia dihitung secara dinamis (*runtime calculation*) menggunakan rumus selisih antara Tanggal Lahir siswa dengan **Tanggal Acuan Usia (`age_reference_date`)** yang ditentukan pada periode pendaftaran aktif (misal: 1 Juli tahun ajaran baru).
2. **Tidak Disimpan Permanen**:
   - Usia (Tahun, Bulan, Hari) **DILARANG DISIMPAN SEBAGAI KOLOM TETAP** di tabel basis data untuk mencegah inkonsistensi data seiring berjalannya waktu.
3. **Tidak Ada Diskualifikasi Otomatis**:
   - Sistem **TIDAK MENDISKUALIFIKASI** atau menolak pendaftaran secara otomatis berdasarkan umur. Seluruh validasi kesiapan sekolah dilakukan oleh pihak madrasah secara manual.

---

## 7. ATURAN DOKUMEN & PENGUNGGAHAN (DOCUMENT MANAGEMENT)

1. **Daftar Dokumen Persyaratan**:
   - Persyaratan berkas dikonfigurasi secara dinamis melalui tabel `document_requirements`.
   - Terdiri dari dokumen wajib (misal: Kartu Keluarga, Akta Kelahiran, Surat Keterangan TK) dan dokumen opsional (misal: Piagam Prestasi).
   - Ukuran maksimum global dan default per dokumen adalah **5120 KB (5 MB)**. Setiap persyaratan boleh menetapkan batas yang lebih kecil; batas efektif adalah nilai terendah antara maksimum global dan batas persyaratan. Batas persyaratan di atas 5120 KB ditolak.
2. **Keamanan Penyimpanan**:
   - Seluruh berkas yang diunggah disimpan pada disk privat lokal di `storage/app/private/ppdb/documents`, tidak dapat diakses secara publik via URL langsung tanpa otorisasi.
   - Validasi ukuran, ekstensi, MIME aktual, kepemilikan, dan SHA-256 dilakukan di sisi server; validasi frontend hanya bantuan tambahan.
3. **Riwayat Revisi**:
   - Jika dokumen diperbarui/diganti, metadata dan lokasi berkas lama dicatat pada tabel `registration_document_revisions`; berkas lama tetap disimpan untuk audit integritas data.

---

## 8. ATURAN BUKTI PENDAFTARAN PDF (PDF GENERATION)

1. **Dokumen Sementara (Draft PDF)**:
   - Jika formulir pendaftaran masih berstatus `DRAFT`, pendaftar dapat membuat dan mengunduh pratinjau yang memuat watermark **"DRAFT - BUKAN BUKTI RESMI"**.
   - NIK calon siswa pada PDF draft harus dimasker.
2. **Dokumen Resmi (Final PDF)**:
   - Diterbitkan setelah pendaftaran berstatus `SUBMITTED`.
   - Memuat Nomor Registrasi Resmi, Barcode/QR Verifikasi, Pas Foto, Biodata Lengkap, dan Kolom Tanda Tangan Orang Tua.
   - NIK calon siswa pada bukti final boleh ditampilkan lengkap untuk keperluan verifikasi dokumen yang diserahkan ke PTSP; NIK orang tua/wali tidak dicetak.
   - Dicatat penomoran versinya pada tabel `registration_pdf_versions`.
   - Setiap PDF terikat pada revisi data. Jika data berubah, PDF final sebelumnya tetap tersimpan sebagai arsip/outdated dan pendaftar harus membuat versi baru.
3. **Bukan Surat Kelulusan**:
   - Lembar PDF secara eksplisit menyatakan bahwa dokumen ini adalah **Tanda Bukti Pendaftaran**, bukan tanda bukti diterima sebagai siswa.
4. **Privasi dan Verifikasi**:
   - File PDF disimpan privat dan hanya dapat diunduh oleh pemilik pendaftaran terautentikasi; tidak ada URL file publik.
   - QR mengarah ke halaman verifikasi publik dengan token acak. Halaman hanya menampilkan nomor pendaftaran, waktu terbit, fingerprint SHA-256, dan status versi; tidak menampilkan nama/NIK atau menyediakan unduhan PDF.

---

## 9. PROTOKOL PERUBAHAN ATURAN BISNIS

Jika di masa mendatang tim teknis atau panitia madrasah mengusulkan perubahan terhadap aturan bisnis ini, wajib mengikuti tahapan berikut:
1. **Analisis Kebutuhan**: Menjelaskan urgensi dan latar belakang perubahan.
2. **Analisis Dampak (*Impact Assessment*)**: Mengidentifikasi modul, migrasi basis data, dan alur antarmuka yang terdampak.
3. **Pengajuan Proposal Tertulis**: Menyampaikan opsi solusi ke Pemilik Proyek.
4. **Persetujuan Eksplisit**: Aturan baru hanya boleh diimplementasikan setelah disetujui secara tertulis oleh Pemilik Proyek.

---

## 10. KEBIJAKAN ADMINISTRATOR MEMBUKA AKSES EDIT ULANG

1. Admin PPDB atau superadmin yang memiliki permission `reopen_registration` dapat membuka akses edit untuk **satu pendaftaran** yang sudah `SUBMITTED` pada periode yang telah ditutup.
2. Alasan pembukaan dan waktu kedaluwarsa wajib diisi; waktu kedaluwarsa harus berada di masa depan.
3. Admin yang berwenang dapat menutup akses lebih awal dengan alasan wajib.
4. Akses kedaluwarsa ditegakkan server-side pada setiap operasi, tanpa bergantung pada cron atau scheduler.
5. Pembukaan dan penutupan dicatat dalam `registration_logs` beserta aktor, waktu, alasan, dan referensi pendaftaran. Pembukaan tidak mengubah status, nomor registrasi, atau waktu submit.
