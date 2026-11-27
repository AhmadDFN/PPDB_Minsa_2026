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

Catat blocker dan temuan yang masih terbuka ke Phase 11; status lintas proyek ke `docs/PROJECT_STATUS.md`. Hasil kasus tetap ada di `03-CHECKLIST-DAN-HASIL.md`. Bedakan uji lokal, staging dan live serta perubahan yang sudah dikomit/dipush dari yang benar-benar terpasang di server.

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

## AL-004 — Dokumen UAT memuat kredensial statis

- **Kasus / tanggal:** Persiapan SUP-01–03, 27 November 2026, 19.14 WIB.
- **Prioritas / status:** tinggi / OPEN. Dokumen yang terlacak Git memuat kata sandi uji dan identitas dummy lengkap. Jika akun/rahasia itu pernah dipakai di server live, akses berisiko karena riwayat Git tetap memuat nilainya meskipun dokumen sekarang disunting.
- **Perbaikan dokumen:** hapus rahasia dari panduan UAT dan arahkan penyimpanan kredensial ke media privat di luar Git.
- **Tindak lanjut pemilik:** pastikan akun live yang terpengaruh memakai kredensial berbeda; rotasi jika pernah memakai nilai pada dokumen, periksa log akses, dan jangan menganggap penghapusan teks dari commit baru telah menghapus riwayat Git. Jangan kirim nilai rahasia melalui chat.
- **Bukti live:** halaman `/admin/login` terbuka, sesi baru belum terautentikasi; status akun live dan versi server belum dapat dipastikan.
- **Klarifikasi seeder:** `SuperAdminSeeder` dan `InterviewAccountSeeder` masih memuat nilai bawaan statis dalam kode; `firstOrCreate` tidak mengganti sandi akun yang ada. Penghapusan nilai dari dokumen tidak memperbaiki paparan pada source/riwayat Git. Perubahan seeder dan rotasi akun live adalah tindak lanjut keamanan terpisah.
- **Uji ulang live 27 November, sekitar 19.20–19.32 WIB:** nilai bawaan dari source seeder berhasil dipakai untuk masuk sebagai superadmin dan admin PPDB setelah pemilik menyatakan telah melakukan fresh seed. Risiko kredensial bawaan pada web publik kini terbukti, bukan sekadar kemungkinan. Rotasi kredensial live melalui alur aman dan penghapusan default statis dari seeder menjadi prioritas sebelum peluncuran. Agen tidak mengubah sandi karena perubahan kredensial memerlukan pemilik mengambil alih langkah tersebut.

## AL-005 — Branding live masih PPDBM

- **Kasus / status:** SUP-01; OPEN; 27 November 2026 sekitar 19.25 WIB.
- **Prioritas:** sedang; judul portal pendaftar dan nilai pengaturan sistem masih `PPDBM MIN 1 KOTA MADIUN`, sedangkan keputusan pemilik memakai istilah resmi `PPDB`.
- **Bukti:** `/admin/pengaturan` menampilkan nilai tersebut; `/login` pendaftar juga menampilkannya.
- **Tindak lanjut:** ubah konfigurasi branding sesuai keputusan pemilik pada rilis yang disetujui, lalu periksa header, halaman login, dan pesan komunikasi. Tidak diubah selama uji baca ini.

## AL-006 — Template chat kosong pada server live

- **Kasus / status:** SUP-03; OPEN; 27 November 2026 sekitar 19.24 WIB.
- **Prioritas:** tinggi; superadmin tidak melihat contoh format yang diminta pemilik untuk OTP aktivasi, OTP pemulihan, password sementara, dan kirim ulang password sementara.
- **Bukti:** keempat textarea pada `/admin/communication` memiliki panjang nilai 0 dan panjang placeholder 0. Halaman menyatakan mode legacy environment dan belum ada channel database.
- **Tindak lanjut:** perubahan lokal editor template dan Fonnte harus melalui review/deployment terpisah; uji ulang setelah versi server terbukti. Jangan menyimpan token atau mengirim pesan ke penerima nyata selama pengujian template.

## AL-007 — Fresh seed tidak menyiapkan dokumen untuk UAT pengesahan

- **Kasus / status:** ADM-07/ADM-08; BLOCKED; 27 November 2026 sekitar 19.29 WIB.
- **Prioritas:** tinggi untuk kelengkapan UAT; panel verifikasi menunjukkan semua hitungan dokumen 0 dan kedua pendaftaran SUBMITTED tidak memiliki dokumen.
- **Bukti:** `/admin/document-verification` menampilkan `Belum ada dokumen pada pendaftaran ini` pada kedua fixture.
- **Tindak lanjut:** siapkan dokumen dummy melalui alur pendaftar uji atau fixture terisolasi, periksa penerima notifikasi dan backup, lalu uji approve/revision/reject serta pengesahan. Jangan klik pengesahan sebelum dampak nomor dan WhatsApp terkendali.

## AL-008 — Akses admin setelah logout menuju login pendaftar

- **Kasus / status:** ADM-01; OPEN; 27 November 2026 sekitar 19.31 WIB.
- **Prioritas:** rendah; proteksi sesi bekerja, tetapi staf yang kembali membuka `/admin/dashboard` setelah logout dialihkan ke `/login` pendaftar, bukan `/admin/login`.
- **Bukti:** setelah logout superadmin, navigasi langsung ke dashboard admin berakhir pada form NIK calon siswa. Tidak ada data admin yang tampil.
- **Tindak lanjut:** arahkan rute admin tanpa sesi ke login admin, lalu uji ulang logout dan akses langsung. Tidak menghalangi uji superadmin lainnya.

## Perbaikan lokal setelah UAT

- AL-004: default sandi statis seeder staf dihapus; `SUPERADMIN_PASSWORD` dan `DEFAULT_ADMIN_PASSWORD` wajib disediakan sebelum seeding. Akun yang sudah ada tidak dirotasi oleh perubahan ini; rotasi live masih diperlukan.
- AL-005: fallback nama institusi diperbarui ke PPDB. Nilai yang telah disimpan di database live harus diperbarui melalui pengaturan superadmin.
- AL-006: editor lokal menampilkan template tersimpan atau default dan placeholder; verifikasi pengiriman Fonnte live tetap pending.
- AL-008: tamu pada rute admin diarahkan ke login admin. Seluruh perbaikan masih memerlukan deployment dan uji ulang live.

## Keputusan pemilik tentang AL-004 dan lingkungan uji

- Pada 27 November 2026 pemilik menetapkan `ahmaddanyfn.biz.id` sebagai situs **uji yang dapat direset**, meski aplikasinya menggunakan mode `production`. Panel resmi MIN nanti berada di lingkungan lain.
- Penggunaan akun seeder dan kredensial uji pada domain ini diterima sementara untuk memperlancar UAT. AL-004 tetap tercatat sebagai temuan keamanan **wajib ditutup sebelum panel resmi diluncurkan**, bukan penghalang untuk melanjutkan pengujian pada domain ini.
- Jangan memindahkan database, akun, atau sandi uji ke panel resmi. Siapkan rahasia baru, verifikasi izin, dan uji login pada lingkungan resmi sebelum dibuka bagi pengguna.
