# 04 — Log Temuan dan Perbaikan

Sesi live dimulai 27 November 2026. Tambahkan satu entri untuk setiap temuan aktual; jangan mencantumkan rahasia atau data siswa asli.

## Template entri

- **ID / kasus / tanggal WIB:** `AL-001` / `ADM-xx` / —
- **Lingkungan dan versi server yang terbukti:** —
- **Prioritas:** kritis / tinggi / sedang / rendah; alasan dan dampak —
- **Status:** OPEN / IN_PROGRESS / FIXED_LOCAL / VERIFIED_LIVE / BLOCKED
- **Prasyarat dan fixture dummy:** —
- **Langkah reproduksi:** —
- **Diharapkan:** —
- **Aktual / pesan tersanitasi:** —
- **Bukti privat yang telah disamarkan:** —
- **Analisis penyebab dan file terkait:** —
- **Perbaikan yang dilakukan / commit:** —
- **Tes relevan dan hasil:** —
- **Uji ulang di server / versi:** —
- **Risiko, rollback dan tindak lanjut pemilik:** —

## Aturan sinkronisasi

Catat blocker dan temuan yang masih terbuka ke `ppdbv2/KNOWN-ISSUES.md`; pekerjaan/perbaikan dan hasil tes ke `ppdbv2/PROGRESS-LOG.md`; status lintas proyek ke `docs/PROJECT_STATUS.md`. Hasil kasus tetap ada di `03-CHECKLIST-DAN-HASIL.md`. Bedakan uji lokal, staging dan live serta perubahan yang sudah dikomit/dipush dari yang benar-benar terpasang di server.

## AL-001 — Sesi staf live belum tersedia di browser Codex

- **Kasus / status:** ADM-01; BLOCKED sementara. Sesi browser Codex aktif sebagai pendaftar, sedangkan jendela Chrome staf tidak dapat dibaca pada percobaan sebelumnya karena URL tidak dapat dipastikan oleh alat.
- **Dampak:** UAT fitur admin/superadmin di server belum dapat dijalankan hingga pemilik masuk ke portal admin pada browser Codex. Tidak ada kredensial yang dicatat di laporan.
- **Bukti yang sudah didapat:** pendaftar mengakses `/admin/dashboard` dan menerima 403; bagian lain ADM-02 masih belum diuji live.
- **Verifikasi lokal:** 94 test admin/448 assertions, 26 test wawancara/199 assertions dan 7 test login admin/23 assertions lulus. Hasil lokal tidak membuktikan perilaku server.
- **Tindak lanjut:** gunakan sesi admin yang disiapkan pemilik, uji fixture yang diizinkan dan lanjutkan checklist; kemudian uji superadmin dalam sesi terpisah.
## AL-002 — Perbedaan nomor saat submit dan blueprint wawancara

- **Kasus / status:** ADM-08; OPEN untuk keputusan aturan bisnis. Pada fixture dummy yang sudah SUBMITTED, nomor registrasi lima digit sudah tampil sebelum pengesahan, sedangkan blueprint wawancara menyebut nomor urut enam digit diterbitkan pada pengesahan. Jangan menyimpulkan perilaku salah sebelum keputusan pemilik.
- **Dampak:** urutan nomor, pengesahan dan jadwal perlu direkonsiliasi. Tombol pengesahan tidak dijalankan karena nomor WhatsApp akun belum dipastikan sebagai penerima uji dan ada potensi pesan keluar.
- **Tindak lanjut:** selaraskan blueprint, AGENTS dan kode; jalankan pengesahan pada lingkungan terisolasi atau fixture dengan gateway tiruan.

## AL-003 — Kondisi dokumen setelah pengujian

- **Kasus / status:** ADM-07; pengujian PASS. Satu berkas dummy disetujui, satu ditandai perlu revisi, satu ditolak dan satu masih menunggu. Riwayat revisi memuat alasan, aktor dan waktu.
- **Tindak lanjut:** fixture live tetap dalam keadaan pengujian tersebut; jangan disamakan dengan berkas siswa nyata. Uji penggantian dan pengesahan belum dilakukan.

## AL-001 — Pembaruan

- Akses staf terselesaikan: pemilik memberikan akun uji, panitia dan superadmin berhasil masuk. Ketiga pewawancara masuk ke tahap masing-masing; akses rekap masing-masing mendapat 403. Skenario isolasi skor terkunci dan sequential gate tetap pending.