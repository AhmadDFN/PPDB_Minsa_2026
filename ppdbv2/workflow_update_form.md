# Workflow Revisi Formulir Data Calon Peserta Didik

**Status:** DISETUJUI PEMILIK UNTUK EKSEKUSI — mengikuti instruksi eksekusi otonom terbaru.
**Sumber kebutuhan:** Lampiran “DATA CALON PESERTA DIDIK INPUT KETERANGAN”.
**Batas pekerjaan dokumen ini:** Workflow pelaksanaan revisi formulir pendaftar, verifikasi, dokumentasi, dan rilis.

## Keputusan implementasi yang mengikat

- Spesifikasi terlampir menjadi requirement untuk seluruh field dan wajib/opsional sebagaimana ditentukan.
- Konflik penyimpanan umur diselesaikan dengan mengikuti `AGENTS.md`: umur tampil otomatis per 1 Juli tahun ajaran, dihitung dari tanggal lahir, dan **tidak disimpan**.
- Peta: gunakan titik Kampus 1 yang disebutkan pemilik; jarak harus dihitung secara lokal dari koordinat (Haversine) agar alamat/koordinat tidak dikirim ke pihak ketiga. Izinkan pin manual dan fallback input jarak. Koordinat tempat tinggal hanya disimpan jika memang dibutuhkan untuk menghitung ulang; preferensi awal adalah tidak menyimpan koordinat, hanya jarak hasilnya.
- Wilayah cascading harus memakai dataset lokal yang dapat dibundel di aplikasi. Jangan melakukan fetch alamat ke layanan geocoding eksternal. Jika dataset sumber berlisensi/ukuran wajar tidak tersedia di repo, catat sebagai gap dan gunakan fallback isian manual yang divalidasi sampai dataset disediakan.
- Semua nomor identitas sensitif tambahan (KK dan NIK ayah/ibu/wali) harus dienkripsi at-rest dengan pola yang setara proteksi NIK yang berlaku. Jangan menampilkan nilai penuh di log, audit, UI ringkasan umum, atau PDF publik.
- No. Handphone calon siswa disimpan sebagai field terpisah dari WhatsApp akun yang menerima OTP; jika calon siswa belum memiliki nomor, UI mengarahkan pengisi memberikan nomor yang dapat dihubungi.
- Migrasi additive dan backward-compatible. Data historis yang belum melengkapi field baru tetap dapat dibaca; aturan kelengkapan baru berlaku saat draft disimpan/submit sesuai validasi baru.
- Pemilik memberi persetujuan untuk eksekusi, test, review, commit, push dan deployment sebagaimana tertulis di instruksi terlampir; tindakan eksternal tetap dilakukan hanya setelah seluruh gerbang lokal, target remote, dan status git diverifikasi.

## 1. Tujuan

Menyelaraskan formulir pendaftaran dengan kebutuhan data terbaru tanpa kehilangan kompatibilitas data pendaftar yang sudah ada, tanpa mengubah alur OTP, status DRAFT/SUBMITTED/VERIFIED, penomoran, PDF/QR, dan proteksi akses yang telah berjalan.

## 2. Prinsip wajib

- Patuhi `AI-RULES.md`: Laravel + Blade/Tailwind/Vanilla JS, Service/Action Pattern, Form Request, migrasi additive, MySQL/MariaDB dan SQLite.
- Jangan menyimpan umur; hitung saat runtime terhadap tanggal acuan aktif (lampiran menyebut 1 Juli, perlu dikonfirmasi terhadap periode aktif).
- Field sensitif (NIK anak/orang tua/wali dan nomor KK) memerlukan desain enkripsi, lookup, masking, otorisasi, retensi, audit, serta pengujian kebocoran sebelum implementasi.
- Berkas tetap di private storage dan setiap preview/download wajib terikat ke pemilik/otorisasi yang benar.
- Pendaftaran dan akun yang sudah ada harus tetap dapat dibaca setelah migrasi; field baru tidak boleh langsung merusak data historis.
- Tidak membuat integrasi peta atau mengirim data ke layanan pihak ketiga sebelum penyedia, akurasi, privasi, dan persetujuan penggunaan ditetapkan.

## 3. Alur kerja perubahan

### Tahap A — Inventarisasi dan pemetaan

1. Petakan setiap input lampiran ke model, migration, Form Request, Action/Service, Blade, PDF, laporan admin, dan test yang terkait.
2. Tandai setiap kebutuhan sebagai: sudah tersedia, perlu penyesuaian, data baru, atau belum cukup spesifik.
3. Catat semua consumer data agar perubahan tidak hanya masuk ke UI tetapi juga validasi, readiness check, submit, admin, ekspor, serta PDF bila relevan.
4. Identifikasi dampak bagi data existing dan strategi default/backfill yang aman.

**Hasil tahap:** matriks field-ke-komponen dan daftar perubahan additive. Jangan mengubah kode pada tahap ini.

### Tahap B — Tetapkan aturan data

Sebelum implementasi, dokumentasikan keputusan berikut di blueprint/requirements:

1. **NISN:** format/panjang dan perilaku checkbox “belum memiliki NISN”; apakah dokumen NISN menjadi wajib hanya bila NISN dimiliki.
2. **NIK dan nomor KK:** enkripsi, HMAC lookup bila perlu, masking, aturan keunikan, akses admin, retensi, dan apakah nomor KK ditampilkan pada PDF/laporan.
3. **Agama, hobi, cita-cita, pekerjaan, pendidikan, penghasilan, tempat tinggal, waktu tempuh, transportasi:** nilai enum resmi, opsi “Lainnya”, dan kewajiban field.
4. **Alamat anak/orang tua/wali:** apakah alamat orang tua/wali dapat memilih “sama dengan alamat calon siswa” dan aturan domisili orang tua berbeda.
5. **Ayah/ibu/wali:** aturan wajib berdasarkan status hidup/tidak diketahui, keberadaan wali, serta data minimal untuk orang tua yang telah meninggal/tidak diketahui.
6. **Umur:** konfirmasi tanggal acuan per periode dan tampilan hasil; umur tidak disimpan.
7. **Wilayah otomatis:** tetapkan sumber data wilayah, lisensi, versi, mekanisme pembaruan, dan UX fallback manual.
8. **Jarak:** putuskan apakah jarak garis lurus dari koordinat atau jarak rute jalan. Tetapkan titik Kampus 1 yang resmi, izin lokasi browser, fallback manual, akurasi, dan larangan menyimpan koordinat bila tidak diperlukan.
9. **Dokumen:** ukuran/MIME tiap jenis, dokumen kondisional NISN, foto latar biru, status dokumen wajib/opsional, revisi, serta aturan submit.
10. **Nomor handphone:** diimplementasikan sebagai field No. Handphone Calon Siswa yang terpisah. Jika calon siswa belum memiliki nomor sendiri, form mengarahkan pengisi memberikan nomor yang dapat dihubungi. Nomor WhatsApp akun yang dipakai OTP tetap tersimpan terpisah pada `users.phone` dan tetap mendukung nomor bersama saudara.

**Gerbang:** perubahan data sensitif atau aturan bisnis belum boleh diimplementasikan sebelum keputusan di atas tercatat sebagai disetujui pemilik.

### Tahap C — Desain UX dan migrasi

1. Pecah formulir menjadi langkah mobile-first yang singkat: data calon peserta didik; alamat/tempat tinggal; data ayah; data ibu; data wali kondisional; pendidikan/program; dokumen dan tinjau.
2. Sediakan indikator langkah, simpan draft, validasi inline berbahasa Indonesia, state loading/error/sukses, dan konfirmasi sebelum submit.
3. Tentukan struktur tabel/kolom dan hubungan yang menjaga pemisahan data anak, alamat, orang tua, wali, serta dokumen.
4. Buat migration additive, nullable/default aman untuk record lama; hindari operasi destruktif dan jangan menjalankan `migrate:fresh` pada staging/produksi.
5. Pastikan relasi, indeks, tipe unsigned FK, panjang kolom, dan kompatibilitas SQLite/MySQL/MariaDB.

### Tahap D — Implementasi atomik

Kerjakan satu sub-task tiap siklus:

1. Schema/model/encryption/casts dan test migrasi.
2. Enum/options dan sumber data wilayah lokal beserta fallback.
3. Form Request dan Action/Service untuk penyimpanan tiap langkah.
4. Blade/Vanilla JS UX mobile-first, conditional fields, dan progressive enhancement.
5. Dokumen: validasi, private storage, preview, revisi, IDOR.
6. Readiness check dan final submit atomik/idempoten.
7. Integrasi admin, PDF/QR, ekspor, audit, dan data historis.

Setiap sub-task menjalani test relevan, PHP syntax check, route/view check, `git diff --check`, pembaruan dokumentasi, dan review sebelum langkah berikut.

### Tahap E — Verifikasi

1. Feature tests untuk validasi, persistensi, encryption/masking, data lama, submit, file upload, dan akses.
2. Uji negatif: NIK/KK tidak bocor; user lain tidak bisa melihat data/file; field kondisional tidak dapat dilewati via request langsung; input wilayah tidak valid ditolak.
3. Browser UAT dengan akun dummy untuk seluruh langkah dan readiness/submit.
4. Uji PDF draft/final dan QR valid; pastikan verifikasi publik tidak membocorkan PII.
5. Uji viewport 390 px, 768 px, dan desktop; periksa touch target, keyboard numerik, label, dan overflow.
6. Uji WhatsApp/OTP hanya pada nomor yang disediakan pemilik secara manual; jangan menulis OTP/secret ke laporan.
7. Jalankan quality gates yang ditentukan `AGENTS.md` dan catat passed/failed/skipped apa adanya.

### Tahap F — Sinkronisasi dan rilis

1. Perbarui `ppdbv2/todo-roadmap.md`, `ppdbv2/PROGRESS-LOG.md`, `ppdbv2/KNOWN-ISSUES.md`, serta `docs/PROJECT_STATUS.md`.
2. Tandai checklist hanya setelah bukti pengujian tersedia. Bedakan automated test, local browser UAT, staging, dan manual pemilik.
3. Tinjau `git diff`, pastikan tidak ada secret, migrasi kompatibel, dan tidak ada perubahan di luar scope.
4. Commit Conventional Commit dan push hanya sesuai persetujuan pemilik serta aturan repo yang berlaku.

## 4. Matriks kebutuhan awal dari lampiran

| Kelompok | Data yang diminta | Catatan desain awal |
|---|---|---|
| Calon siswa | Nama, NISN/checkbox belum punya, NIK, nomor KK, tempat/tanggal lahir, umur, gender, agama | Umur runtime; NIK/KK sensitif; NISN dan dokumen NISN kondisional perlu aturan eksplisit. |
| Alamat anak | Jalan, RT/RW, provinsi, kabupaten/kota, kecamatan, desa/kelurahan, kode pos | Perlu sumber dataset wilayah dan fallback; pertahankan leading zero RT/RW. |
| Kondisi siswa | Jumlah saudara, anak ke, hobi, cita-cita, HP, status tempat tinggal, jarak, waktu tempuh, transportasi, KIP/PKH/KKS | Tentukan apakah nomor bantuan perlu disimpan atau cukup status/kategori; nomor bantuan termasuk data sensitif. |
| Pendidikan | Asal TK/RA dan NPSN | NPSN harus divalidasi format; wajib atau kondisional perlu disejajarkan dengan ketersediaan asal sekolah. |
| Ayah/Ibu | Nama, NIK, tempat/tanggal lahir, status, pendidikan, pekerjaan, domisili, HP, rentang penghasilan, alamat lengkap, status tempat tinggal | NIK/HP sensitif; aturan field wajib saat meninggal/tidak diketahui belum jelas. |
| Wali | Field serupa ayah/ibu | Kondisional; tetapkan kapan wajib dan apakah wali dapat berbeda domisili. |
| Berkas | Foto latar biru, surat aktif TK, dokumen NISN kondisional, KK, akta, piagam opsional | Tetapkan MIME/ukuran, aturan validasi foto, dokumen wajib saat submit, serta akses preview privat. |

## 5. Isu terbuka yang menghalangi desain final

- Dataset wilayah dan sumber pembaruannya belum disebutkan.
- Pengukuran jarak map belum menentukan definisi jarak, titik Kampus 1, penyedia peta, izin browser, dan penyimpanan koordinat.
- Kewajiban data orang tua yang meninggal/tidak diketahui belum didefinisikan.
- Nomor KIP/PKH/KKS belum jelas apakah wajib diisi untuk semua pendaftar atau hanya jika memiliki kartu.
- Field “Domisili” dan “Alamat” pada data orang tua/wali tampak tumpang tindih; perlu definisi apakah berarti tinggal bersama anak atau alamat surat.
- Umur “wajib diisi” bertentangan dengan ketentuan umur otomatis; implementasi harus menghitung, bukan menerima input umur.
- Perlu konfirmasi apakah field baru menggantikan atau menambah field existing, serta dampaknya terhadap data yang sudah terdaftar.

## 6. Kondisi selesai workflow

Workflow ini dianggap siap menjadi task implementasi setelah matriks tahap A lengkap, semua keputusan tahap B disetujui, strategi migrasi/backfill disepakati, dokumen requirement/status diperbarui, dan sub-task implementasi ditambahkan ke roadmap dengan urutan serta kriteria penerimaan.

## 7. Eksekusi dan catatan keputusan pemilik — 26 November 2026

Pemilik meminta eksekusi otonom seluruh revisi, testing, sinkronisasi dokumentasi, review, commit dan push. Dengan demikian status workflow berubah menjadi disetujui untuk implementasi. Ketentuan yang bertentangan dengan tata kelola repository tetap diselesaikan mengikuti `AGENTS.md`; contoh: umur dihitung runtime dan tidak disimpan walaupun instruksi lampiran menyebut simpan nilai umur.

### Kriteria penerimaan implementasi

- Semua field lampiran tersedia pada portal pendaftar, tervalidasi server-side, dapat disimpan, dan muncul di ringkasan/PDF sesuai klasifikasi privasi.
- Checkbox NISN belum ada menghapus kewajiban nilai NISN serta dokumen NISN secara konsisten di UI dan server.
- Persyaratan upload: foto, surat aktif TK, KK, akta wajib; bukti NISN kondisional; piagam opsional. File tetap privat dan memakai kontrol akses yang sudah ada.
- Alamat anak dan orang tua/wali mendukung pilihan wilayah bertingkat; opsi manual/fallback tidak boleh menurunkan validasi dan harus menjaga nilai historis.
- Jarak ke titik MIN 1 Kota Madiun Kampus 1 dihitung tanpa mengirim koordinat ke vendor geocoding; fallback manual tersedia.
- Submit tetap atomik/idempoten, readiness mencakup field/dokumen baru, dan PDF/QR menjaga aturan tanpa PII pada halaman verifikasi publik.
- Migrasi lolos SQLite test dan dicek aman untuk MySQL/MariaDB; data lama tetap bisa dibaca.
- Status manual/staging/WhatsApp hanya dapat dinyatakan lulus jika bukti fisik tersedia; jika tidak, dicatat sebagai pending dengan langkah manual pemilik.

### Progress eksekusi

- [x] Membaca AI-RULES, roadmap, progress log, known issues, dan inspeksi model/form/readiness yang ada.
- [x] Mencatat approval pemilik serta keputusan umur dan privasi peta.
- [ ] Inventarisasi semua consumer dan kontrak database. *(Dampak admin/export dan keseluruhan field PDF masih perlu audit lanjutan.)*
- [x] Implementasi migration/model/enkripsi dasar, termasuk backfill legacy NIK orang tua dan nomor handphone calon siswa terpisah.
- [x] Implementasi cascade wilayah lokal, validasi parent-child, Action penyimpanan label resmi, dan tombol salin alamat siswa ke orang tua. Dataset 2025 berlisensi/beratribusi tersedia.
- [x] Implementasi pinpoint map offline/lokal dan uji Haversine otomatis. *(Potongan OSM lokal ODbL disajikan dari origin aplikasi; pin tap/drag/keyboard, geolocation opt-in, koordinat tidak tersimpan, fallback manual tersedia; test memastikan titik kampus 0,00 km.)*
- [x] Uji browser widget map 360×800/390×844: tap/drag/keyboard, kalkulasi di luar cakupan, dan fallback manual. *(Harness memakai markup/skrip widget yang sama, bukan sesi form terautentikasi. Peta visual hanya mencakup sekitar Kampus 1.)*
- [ ] UAT map pada form pendaftar terautentikasi dan uji GPS permission. *(Koordinat luar cakupan tetap dihitung, tetapi pin tidak tampak.)*
- [ ] Dokumen kondisional NISN, completeness, PDF, admin. *(NISN/readiness/PDF diperbarui; verifikasi consumer admin/export masih pending.)*
- [x] Test otomatis dan browser UAT yang bisa dilakukan lokal. *(Suite 388 test; 373 passed, 15 skipped, 1.781 assertions; UAT submit/PDF sebelumnya, cascade wilayah dan copy alamat; IDOR dokumen dan PDF lintas akun 404; enam langkah form 360/390/768 tanpa overflow sebelum widget map ditambahkan; harness widget map 360/390 lulus. Feature test pin/Haversine lulus.)*
- [ ] QR scan fisik, foto asli berlatar biru, uji MySQL/MariaDB dan staging. *(Manual/lingkungan eksternal pending; tidak dinyatakan lulus.)*
- [x] Sinkronisasi laporan progres, known issues, status project, dan hasil UAT.
- [ ] Final acceptance, review, commit/push/deploy. *(Ditahan karena UAT cakupan map mobile, ISS-005 foto, ISS-006 QR fisik, dan validasi MySQL/MariaDB/staging belum selesai. ISS-007 audit Composer dan ISS-009 cascade dataset resolved.)*
