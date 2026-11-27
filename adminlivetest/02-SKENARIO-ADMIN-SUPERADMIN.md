# 02 — Skenario Admin & Superadmin

Semua kasus berstatus **BELUM DIUJI** sampai ada bukti. Gunakan fixture dummy dan catat hasil pada `03-CHECKLIST-DAN-HASIL.md`. Route dan permission mengacu pada checkout saat dokumen ditulis; hasil deployment dapat berbeda. Uji perubahan yang memengaruhi akun, data atau konfigurasi hanya pada fixture/lingkungan yang disiapkan, dan catat dampaknya.

## A. Akses dan dashboard

**ADM-01 — Login staf.** Buka `/admin/login`, masuk memakai akun uji `admin_ppdb`; pastikan `/admin/dashboard` tampil. Ulangi sebagai superadmin pada sesi terpisah. Cek logout dan akses rute terproteksi setelah logout. Catat pengalihan/status tanpa merekam kredensial.

**ADM-02 — Batas role.** Dengan akun pendaftar, coba rute admin; dengan tiap akun pewawancara coba `/admin/interview-recaps` dan rute tahap lain. Harapan: akses terlarang ditolak/diarahkan sesuai middleware tanpa membocorkan data. Pewawancara tidak bisa membuka rekap, membaca skor terkunci, maupun masuk tahap lain. Catat hasil aktual HTTP/halaman; jangan coba mengakses data nyata.

**ADM-03 — Dashboard.** Bandingkan total, status dan periode pada `/admin/dashboard` dengan fixture atau agregat yang dapat diverifikasi; coba keadaan kosong jika disiapkan. Angka sensitif tidak disalin ke laporan. Cek pemuatan, konsistensi dan tidak ada data statis yang menyesatkan.

## B. Pendaftar dan dokumen

**ADM-04 — Cari dan filter.** Buka `/admin/pendaftar`, cari nama fixture dan NIK fixture dalam form tanpa mencatat nilai, pilih filter status/program/periode yang tersedia, reset, periksa halaman berikut. Hasil cocok fixture dan hak akses. Jangan menyimpulkan pencarian HMAC hanya dari UI; pemeriksaan implementasi/otomatis terpisah.

**ADM-05 — Detail dan privasi.** Buka detail fixture, cocokkan biodata, keluarga, alamat, program, dokumen dan status dengan input dummy. NIK tersamar di layar umum; tampilkan data sensitif hanya pada peran yang memang berwenang. Identifikasi bagian tidak tampil atau tidak sesuai tanpa menyalin PII.

**ADM-06 — Preview, unduh, IDOR.** Preview/unduh dokumen fixture A sebagai admin berwenang; pastikan tipe file, konten dummy, dan link diproteksi. Gunakan dua fixture dan sesi peran terbatas untuk mencoba URL milik B dari akun A; harapan 403 atau 404 tanpa isi berkas. Uji asosiasi dokumen dengan pendaftaran yang salah pada rute admin; hasil harus ditolak. Tidak menggunakan ID nyata atau enumerasi massal.

**ADM-07 — Verifikasi dokumen.** Pada `/admin/document-verification`, uji approve, reject, revision serta history pada dokumen dummy terpisah. Cek alasan/validasi wajib sesuai UI dan aturan aktual, perubahan status, riwayat dan aktor. Uji dokumen salah asosiasi dan permission tanpa mengubah milik siswa nyata.

**ADM-08 — Pengesahan dan nomor.** Pada fixture dummy SUBMITTED dengan seluruh dokumen wajib terverifikasi, jalankan pengesahan. Amati keadaan sebelum/sesudah: status dokumen dan pendaftaran, nomor registrasi, nomor antrean, jadwal, audit, PDF dan efek notifikasi. Coba pengesahan ulang untuk konsistensi tanpa menerbitkan duplikat. Jika prasyarat belum lengkap, pengesahan ditolak dengan pesan jelas. Rekam format/titik penerbitan sebagai **konflik spesifikasi** sesuai rencana, jangan mengharuskan format lama sebagai fakta. Pastikan tidak mengirim WA ke nomor nyata.

**ADM-09 — Reopen.** Pada `/admin/registrations`, cari fixture eligible, buka `/admin/registrations/{registration}/reopen`, uji validasi alasan dan waktu, buka jendela revisi, cek tampilan pendaftar, tutup/expiry sesuai implementasi, cek audit. Jangan ubah pendaftaran nonfixture.

**ADM-10 — PDF/QR.** Buka PDF final fixture yang tersedia di sisi pendaftar/admin sesuai UI dan akses yang diizinkan. Cocokkan nomor/status dan data tersamar; scan QR menggunakan kamera/decoder, catat hanya apakah endpoint publik valid/invalid dan tidak membeberkan PII. Jangan menyalin URL token ke laporan. PDF versi lama/akses lintas akun diuji dengan fixture B. Uji fisik ditandai BLOCKED jika alat tidak tersedia.

## C. Wawancara dan laporan

**INT-01 — Master wawancara.** Superadmin membuka `/admin/interview-batches` dan `/admin/interview-stages`; pada lingkungan terisolasi, uji rentang batch valid dan tumpang tindih, jam, tahap, poin dan perlindungan data bernilai. Pastikan hanya peran berizin yang dapat mengubahnya, perubahan tercatat di audit. Hindari mengubah jadwal live.

**INT-02 — Sequential gate & isolasi nilai.** Dengan fixture dummy untuk tiap program dan akun pewawancara tiap tahap, verifikasi Tahap 2 ditolak sebelum 1 selesai; Tahap 3 untuk Unggulan sesuai aturan implementasi dan tahap sebelumnya. Setelah nilai 0–100 disimpan/dikunci, pewawancara tidak bisa membaca/mengubah nilai terkunci atau mengakses tahap lain. Uji status sesi dan persaingan lock dengan fixture terisolasi; jangan merekam skor nyata.

**INT-03 — Rekap, koreksi, CSV.** Admin dan superadmin membuka `/admin/interview-recaps`, filter fixture, lihat jumlah dan rata-rata terpisah per tahap. Koreksi nilai dummy tanpa alasan ditolak; dengan alasan valid tercatat dalam audit bersama aktor/waktu dan nilai dihitung ulang. Uji CSV pada fixture berawalan `=`, `+`, `-`, `@` untuk anti-formula injection; data pada ekspor harus sesuai kebijakan akses dan dimusnahkan sesuai prosedur lingkungan uji.

**ADM-11 — Laporan dan audit.** Periksa `/admin/reports`, filter dan ekspor fixture; `/admin/audit-logs` untuk peran dengan `view_activity_logs`. `admin_ppdb` standar tidak memiliki permission ini dalam seeder; pastikan penolakan kecuali izin eksplisit aktual berbeda. Audit tidak menampilkan password, token, OTP atau NIK lengkap.

## D. Administrasi superadmin

**SUP-01 — Pengaturan & master.** Buka `/admin/pengaturan`, `/admin/admission-periods`, `/admin/programs`, `/admin/document-requirements`. Di lingkungan terisolasi uji field wajib, duplikasi, rentang tanggal, perubahan aktif/nonaktif dan audit. Verifikasi dampak terhadap pendaftar dummy, tanpa mengubah periode/program/persyaratan live.

**SUP-02 — Akun & role.** Buka `/admin/admin-users` dan `/admin/roles`, periksa daftar, permission dan penolakan untuk admin biasa. Uji pembuatan/perubahan/reset hanya di lingkungan terisolasi dan akun khusus yang disiapkan; verifikasi tidak dapat mengunci superadmin terakhir atau memperluas akses tanpa otorisasi. Jangan tampilkan token/kredensial. Undangan/email termasuk efek eksternal dan harus dicegah/dijadwalkan khusus untuk penerima uji.

**SUP-03 — Komunikasi.** Periksa `/admin/communication` dan batas permission controller. Uji perubahan channel/template, aktivasi dan validasi koneksi hanya dengan provider/mock terisolasi. Pengiriman WA aktual/OTP dibuktikan terpisah oleh pemilik dengan penerima uji; jangan mengubah token atau mengirim pesan secara otomatis ke nomor nyata.

**SUP-04 — UI dan error.** Ulangi halaman utama admin/superadmin pada 360, 390, 768 dan 1440 px; cek akses navigasi, target sentuh, scroll horizontal pada level halaman, tabel dapat digulir dalam komponen, label, fokus keyboard dan pesan validasi. Sertakan network/console error yang tidak mengandung rahasia.
