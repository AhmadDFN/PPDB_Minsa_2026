# 03 — Checklist dan Hasil Live Test

**Sesi:** dua putaran, hasil parsial | **Tanggal:** 27 November 2026 | **Lingkungan:** https://ahmaddanyfn.biz.id | **Versi server terbukti:** UI Laravel 13.42.0; commit/deployment belum diverifikasi | **Penguji:** Codex

Isi setiap baris dengan PASS, FAIL, BLOCKED atau SKIPPED. Bukti cukup kode/tautan privat yang sudah disamarkan; jangan menyimpan NIK lengkap, nomor HP, kredensial, isi dokumen atau QR token. Jika tidak dilaksanakan, tulis alasannya. Jangan menandai `[x]` untuk rencana yang belum diuji.

| Status | ID | Peran | Kasus | Hasil / bukti / isu |
|---|---|---|---|---|
| [x] | ADM-01 | Admin / superadmin | Login, dashboard dan logout | PASS: kedua role masuk dan dashboard tampil; panitia dan superadmin berhasil logout |
| [ ] | ADM-02 | Pendaftar / pewawancara | Penolakan akses admin dan isolasi skor/tahap | PARTIAL: pendaftar ditolak 403 pada dashboard admin; pewawancara tahap 1/2/3 masuk tahap masing-masing dan ditolak 403 pada rekap; isolasi skor terkunci/tahap lain belum diuji |
| [ ] | ADM-03 | Admin / superadmin | Dashboard dan validitas agregat | PARTIAL: dashboard kedua role tampil, jumlah satu pendaftar sesuai satu fixture terlihat; pembandingan database belum dilakukan |
| [ ] | ADM-04 | Admin | Pencarian, filter, reset, halaman | PARTIAL: pencarian nama dummy mengembalikan satu hasil; filter/reset/paginasi belum diuji |
| [ ] | ADM-05 | Admin | Detail fixture dan masking | PARTIAL: detail fixture, program, dokumen dan NIK tersamar tampil; audit semua field belum lengkap |
| [ ] | ADM-06 | Admin / pendaftar A-B | Preview, unduh, IDOR dan salah asosiasi | PARTIAL: preview gambar dummy tampil di admin; unduh dan IDOR live antar dua fixture belum diuji |
| [x] | ADM-07 | Admin | Approve/reject/revision dokumen dan riwayat | PASS: pada fixture dummy satu dokumen disetujui, satu revisi, satu ditolak; status dan riwayat revisi tampil |
| [ ] | ADM-08 | Admin | Prasyarat pengesahan, nomor, jadwal, idempotensi | BLOCKED: pengesahan dapat mengirim WhatsApp ke nomor akun; mekanisme pengiriman tiruan belum dipastikan |
| [ ] | ADM-09 | Admin | Reopen, batas waktu, penutupan dan audit | BELUM DIUJI |
| [ ] | ADM-10 | Pendaftar / admin | PDF, QR valid/invalid, privasi publik dan versi | PARTIAL: UAT pendaftar sebelumnya membuat dan mengunduh PDF final; scan QR dan IDOR live masih pending |
| [ ] | ADM-11 | Admin / superadmin | Laporan, ekspor dan akses audit log | PARTIAL: laporan panitia dan halaman audit superadmin terbuka; ekspor dan pembatasan audit admin belum diuji |
| [ ] | INT-01 | Superadmin | Master batch, tahap dan kriteria | PARTIAL: halaman batch dan tahap terbuka; perubahan fixture belum diuji |
| [ ] | INT-02 | Pewawancara | Sequential gate, lock dan isolasi nilai | PARTIAL: ketiga role masuk tahap masing-masing dan ditolak rekap 403; sequential gate dan lock belum diuji |
| [ ] | INT-03 | Admin / superadmin | Rekap per tahap, koreksi beralasan, CSV aman | PARTIAL: rekap admin terbuka dan menampilkan empty state; koreksi dan ekspor belum diuji |
| [ ] | SUP-01 | Superadmin | Pengaturan, periode, program dan persyaratan | PARTIAL: keempat halaman terbuka; panitia ditolak 403 pada pengaturan; mutasi belum diuji |
| [ ] | SUP-02 | Superadmin | Akun staf dan permission pada lingkungan terisolasi | PARTIAL: halaman akun dan roles terbuka; mutasi belum diuji |
| [ ] | SUP-03 | Superadmin | Kanal komunikasi/provider dummy | PARTIAL: halaman terbuka; provider tiruan/pengiriman tidak diuji |
| [ ] | SUP-04 | Admin / superadmin | Responsif, aksesibilitas dan error UI | BELUM DIUJI |

## Ringkasan sesi

- Ringkasan historis sebelum fresh seed: 18 kasus; 2 PASS, 13 PARTIAL, 1 BLOCKED, 2 BELUM DIUJI. Gunakan tabel putaran setelah fresh seed di bawah untuk keadaan server sekarang.
- Fixture: satu pendaftaran dummy SUBMITTED dengan empat dokumen dummy; fixture kedua dan sesi wawancara siap nilai belum diverifikasi.
- Konflik spesifikasi dan keputusan pemilik: —
- Perubahan uji: satu dokumen DITERIMA, satu PERLU PERBAIKAN dan satu DITOLAK; dokumen keempat MENUNGGU. Pendaftaran dummy belum disahkan.
- Tindakan perbaikan dan uji ulang: —
- Pekerjaan manual pemilik: —

## Pemeriksaan lokal tambahan

- php artisan test tests/Feature/Admin: 94 passed, 448 assertions.
- php artisan test tests/Feature/Interview: 26 passed, 199 assertions.
- php artisan test tests/Feature/Auth/AdminLoginTest.php: 7 passed, 23 assertions.
- Ketiga hasil di atas memakai lingkungan test lokal; bukan bukti UAT server live.

## Putaran superadmin pertama — 27 November 2026, 19.14 WIB

- URL `/admin/login` pada situs live terbuka dan form email, kata sandi, serta tombol masuk tampil. Ini hanya smoke test halaman login, bukan bukti login berhasil atau fungsi superadmin lulus.
- Browser uji saat putaran ini belum memiliki sesi superadmin. SUP-01, SUP-02, SUP-03, INT-01, dan pemeriksaan audit ADM-11 tetap **PARTIAL** sesuai bukti sesi sebelumnya; tidak ada mutasi pada putaran ini.
- Lanjutan: pemilik masuk sendiri di tab uji yang disediakan, lalu uji menu, validasi baca, batas akses, dan perilaku pada fixture yang aman. Jangan memasukkan kredensial ke chat atau laporan.
- Versi commit server masih belum dapat dibuktikan. Perubahan kode lokal Fonnte dan nomor registrasi belum boleh dinyatakan terpasang di server.
- Klarifikasi seeder: akun superadmin pernah berhasil login pada sesi sebelumnya; `SuperAdminSeeder` hanya membuat akun bila belum ada. Inventaris dan sumber kredensial lokal kini dijelaskan di `livetest/02-KREDENSIAL-DAN-AKUN-UJI.md`. Tidak ada akun baru atau seeder yang dijalankan pada putaran ini.

## Quality gate lokal setelah klarifikasi akun (bukan bukti server)

- Dengan `APP_KEY` acak sementara khusus proses test: 394 test, 378 passed, 16 skipped, 1.804 assertions; satu ekspektasi E2E lama diperbarui agar PDF final baru terbit setelah pengesahan dan nomor `YYNNNN`.
- Tanpa `APP_KEY` pada checkout ini, test gagal karena konfigurasi enkripsi tidak tersedia. Kunci server dan `.env` tidak diubah.
- Pint dan Vite build lulus; npm audit melaporkan 0 kerentanan; `git diff --check` bersih.
- `composer audit` pada dependensi terpasang masih menemukan 5 advisory pada 3 paket Symfony; quality gate keamanan belum lulus.

## Putaran live setelah fresh seed oleh pemilik — 27 November 2026, sekitar 19.20–19.32 WIB

Pemilik menyatakan telah menjalankan `migrate:fresh --seed` dan seeder uji pada web. Agen **tidak** menjalankan migrasi/seeder itu dan tidak dapat membuktikan detail perintah atau backup dari browser. Hasil putaran sebelumnya pada tabel utama adalah **historis**; fixture dan status dokumen sebelumnya tidak boleh dianggap masih ada setelah reset. Hasil berikut berdasarkan tampilan server yang diamati langsung.

| ID | Hasil putaran ini | Bukti live / batas |
|---|---|---|
| ADM-01 | PASS dengan catatan UX | Login superadmin dan `admin_ppdb` dari akun seeder berhasil; dashboard tampil; keduanya logout. Setelah logout, akses langsung `/admin/dashboard` dialihkan ke `/login` pendaftar, bukan form staf (AL-008). |
| ADM-02 | PARTIAL | Admin biasa mendapat 403 pada `/admin/pengaturan`, `/admin/communication`, `/admin/admin-users`, dan `/admin/audit-logs`. Batas pendaftar/pewawancara belum diuji ulang setelah reset. |
| ADM-03 | PARTIAL | Dashboard superadmin menunjukkan 3 pendaftar, 2 SUBMITTED, 1 DRAFT, Reguler 1, Unggulan 2; daftar pendaftar menunjukkan total 3 dan status yang cocok. Pembandingan database independen belum dilakukan. |
| ADM-04 | PARTIAL | Filter status DRAFT menampilkan satu fixture dan reset mengembalikan tiga. Pencarian, filter gabungan, dan paginasi belum diuji pada putaran ini. |
| ADM-07 / ADM-08 | BLOCKED | Panel verifikasi menunjukkan 0 dokumen; dua fixture SUBMITTED tidak memiliki berkas. Tombol pengesahan terlihat pada fixture belum bernomor, tetapi tidak ditekan karena konsekuensi nomor/WhatsApp dan prasyarat belum tervalidasi. Hasil PASS verifikasi dokumen pada putaran lama tidak berlaku untuk fixture reset. |
| ADM-11 | PARTIAL | Halaman audit dan laporan terbuka untuk superadmin; audit menampilkan keadaan kosong. Admin biasa mendapat 403 pada audit. Ekspor CSV belum dijalankan. |
| INT-01 | PARTIAL | Halaman batch dan tahap/poin terbuka, data seed tampil. Mutasi jadwal/nilai tidak dilakukan di live. |
| SUP-01 | FAIL untuk branding; PARTIAL untuk master | Pengaturan menampilkan nama institusi `PPDBM MIN 1 KOTA MADIUN`, bertentangan dengan keputusan pemilik memakai `PPDB`. Halaman periode, program, dan persyaratan terbuka; mutasi tidak dilakukan. |
| SUP-02 | PARTIAL | Empat akun staf aktif terlihat (tiga admin PPDB dan satu superadmin); halaman role menampilkan `admin_ppdb` 3 akun/8 permission dan `superadmin` 1 akun/20 permission, role superadmin terlindungi. Pembuatan/reset akun tidak diuji. |
| SUP-03 | FAIL untuk contoh template; PARTIAL untuk gateway | Keempat textarea template pesan kosong dan tidak memiliki contoh/placeholder. Halaman menyatakan mode legacy environment, belum ada channel database. Tidak ada token yang ditampilkan, channel yang diubah, atau pesan yang dikirim. |
| SUP-04 | PARTIAL | Pada viewport browser 732 px, halaman verifikasi berkas memiliki scrollWidth 717 px; screenshot menunjukkan konten utama dapat dibaca. Ukuran 360/390/768/1440, keyboard, dan console belum diuji. |

**Isu yang perlu ditindak:** AL-004 (sandi bawaan seeder telah dipakai login live dan terlacak source/Git), AL-005 (branding), AL-006 (template kosong), AL-007 (fixture dokumen tidak tersedia), AL-008 (redirect login staf). Tidak ada perubahan data server oleh agen dalam putaran ini selain sesi login/logout dan filter GET.
