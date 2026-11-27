# 03 — Checklist dan Hasil Live Test

**Sesi:** berlangsung, hasil parsial | **Tanggal:** 27 November 2026 | **Lingkungan:** https://ahmaddanyfn.biz.id | **Versi server terbukti:** belum diverifikasi | **Penguji:** Codex

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

- Jumlah kasus: 18; 2 PASS, 13 PARTIAL, 1 BLOCKED, 2 BELUM DIUJI. Test otomatis lokal dicatat terpisah di log temuan.
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
