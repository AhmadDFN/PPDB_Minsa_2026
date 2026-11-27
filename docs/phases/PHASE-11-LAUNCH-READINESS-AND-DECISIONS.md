# PHASE 11 — LAUNCH READINESS & KEPUTUSAN TERKINI

**Tanggal keputusan pemilik:** 27 November 2026
**Status:** IN PROGRESS — launch hardening
**Sumber status resmi:** `docs/PROJECT_STATUS.md`
**Master ringkas:** `ppdbv2/PPDB-MASTER-CURRENT-2026-11-27.md`

## 1. Keputusan yang sudah disahkan

1. Format nomor pendaftaran/antrean resmi adalah **`YYNNNN`**, contoh `270001`.
2. Sequence bersifat global per tahun penerimaan dan dipakai bersama Program Reguler dan Unggulan.
3. Nomor diterbitkan atomik saat pendaftaran disahkan panitia setelah dokumen wajib berstatus `APPROVED`.
4. Modul wawancara adalah bagian resmi PPDB, bukan dokumentasi eksperimen terpisah.
5. Modul wawancara hanya mengelola batch, tahap, kriteria, role pewawancara, nilai, rekap, koreksi ber-audit, dan ekspor. Seleksi akhir, perankingan, pengumuman kelulusan, dan daftar ulang tetap di luar sistem.
6. Dokumentasi Gemini dan catatan kerja lama `ppdbv2` dihapus setelah keputusan ini karena telah dikonsolidasikan ke master dan `docs/`.

## 2. P0 — Wajib sebelum launching

- [ ] Hapus semua jalur generator dan validasi legacy `YYNNN`/format `PPDB/...`; tetapkan satu service canonical `YYNNNN`.
- [ ] Pastikan `SubmitRegistrationAction` tetap idempoten dan tidak menerbitkan nomor sebelum pengesahan.
- [ ] Pastikan `ApproveAndAssignQueueNumberAction` aman terhadap race condition, retry, dan pengesahan ganda.
- [ ] Tambahkan regression test untuk nomor `270001`, `270002`, batas `9999`, lintas program, lintas periode tahun ajaran, retry, dan rollback.
- [ ] Audit seluruh UI, PDF, QR lookup, admin search, CSV, test, seeder, manual, dan query agar memakai `YYNNNN`.
- [ ] Audit migration `administrative_regions` pada MariaDB tanpa `migrate:fresh`; cek backup, tabel parsial, status migration, foreign key, dan data existing.
- [ ] Jalankan migration additive pada environment target melalui prosedur yang disetujui pemilik.
- [ ] Verifikasi `APP_DEBUG=false`, HTTPS, `APP_URL`, storage privat, permission folder, queue/cron bila digunakan, dan cache Laravel.
- [ ] Verifikasi PHP `gd`, `upload_max_filesize >= 10M`, `post_max_size >= 16M`, `memory_limit >= 256M`, dan `max_execution_time >= 60` pada hosting.
- [ ] Verifikasi Fonnte: token tidak bocor, device connected, saldo memadai, timeout/graceful degradation, OTP dan temporary password terkirim terpisah.
- [ ] Jalankan full test suite dan seluruh quality gate pada checkout final.

## 3. P1 — Acceptance pendaftar dan dokumen

- [x] Putaran live 27 November: pengisian akun DRAFT sampai 100%, empat unggahan dummy, PDF draf dua halaman, dan 403/404 batas akses dasar. Rincian ada di `livetest/09-HASIL-UAT-LIVE-2026-11-27.md`.
- [x] Rilis dan verifikasi koreksi teks nomor setelah pengesahan serta bahasa tanggal acuan usia di portal pendaftar (27 November 2026).
- [x] Uji penggantian dokumen dummy dan riwayat revisi pada situs uji; kelengkapan tetap 4/4.
- [ ] Lengkapi fixture SUBMITTED yang saat ini 43% tanpa dokumen; uji alur pengesahan dan PDF final dengan data yang konsisten.
- [ ] Uji OTP memakai akun yang terhubung ke nomor uji pemilik; lanjutkan submit final ketika pernyataan data dapat disetujui.
- [ ] Uji registrasi baru, OTP benar/salah/kedaluwarsa, resend, sibling support, recovery, dan mandatory password change.
- [ ] Uji enam modul formulir pada desktop, 360, 390, dan 768 px tanpa overflow atau console error.
- [ ] Uji dataset wilayah cascading dan salin alamat orang tua.
- [ ] Uji peta offline, pin tap/drag/keyboard, GPS opt-in, fallback manual, batas cakupan, Haversine, dan fakta bahwa koordinat tidak disimpan.
- [ ] Uji upload PDF/JPG/PNG, batas 5 MB, MIME spoofing, replacement, revision history, dan private storage.
- [ ] Uji foto asli madrasah sesuai ketentuan; tentukan apakah validasi latar biru manual atau otomatis.
- [ ] Uji readiness check, consent, submit idempoten, reopen, revisi, dan stale PDF.
- [ ] Buat PDF draft dan final; verifikasi watermark, halaman, tanggal, data termasking, fingerprint, dan QR.
- [ ] Scan QR final secara fisik dengan ponsel; pastikan halaman publik tidak menampilkan PII dan status/nomor cocok.

## 4. P1 — Acceptance admin, superadmin, dan wawancara

- [ ] Jalankan seluruh skenario login role: applicant, admin PPDB, superadmin, pewawancara tahap 1/2/3.
- [ ] Verifikasi applicant dan interviewer mendapat 403 pada area yang tidak boleh diakses.
- [ ] Uji dashboard, daftar/detail pendaftar, filter, pencarian `YYNNNN`, NIK HMAC, pagination, dan IDOR.
- [ ] Uji verifikasi dokumen: approve, revision dengan alasan, reject dengan alasan, row locking, audit, dan replacement kembali ke pending.
- [ ] Uji reopen: alasan, expiry, close early, data revision, nomor tetap, dan audit trail.
- [ ] Uji master periode/program/persyaratan dan single active period.
- [ ] Uji manajemen akun admin, permission, password reset/invitation, dan isolasi applicant.
- [ ] Uji Communication Center dan template WhatsApp tanpa test-send sembarang ke nomor nyata.
- [ ] Uji batch wawancara, rentang sequence, stage/criteria, sequential gate, role isolation, session locking, dan score validation 0–100.
- [ ] Uji rekap nilai, koreksi dengan alasan, before/after audit, CSV streaming, dan formula-injection sanitization.
- [ ] Lengkapi checklist live admin/superadmin yang sebelumnya menghasilkan 2 PASS, 13 PARTIAL, 1 BLOCKED, 2 belum diuji.
- [ ] Lanjutkan uji superadmin dari sesi browser baru: halaman login live terbukti terbuka pada 27 November 2026 pukul 19.14 WIB, tetapi sesi terautentikasi belum tersedia. Uji SUP-01–03, master wawancara dan audit setelah login pemilik.
- [ ] Tangani AL-004: kredensial statis pernah terlacak pada dokumentasi Git. Dokumen aktif sudah disanitasi; pastikan kredensial live berbeda atau dirotasi dan tinjau log akses.
- [ ] **Temuan live setelah fresh seed 27 November:** nilai bawaan seeder terbukti bisa login pada web publik. Rotasi akun staf dan hapus default statis sebelum launch (AL-004).
- [ ] **Temuan live SUP-01/SUP-03:** branding masih `PPDBM`, empat template chat kosong, Fonnte mode legacy tanpa channel database. Selesaikan AL-005/AL-006 dan uji ulang setelah deployment yang dapat dibuktikan.
- [ ] **Fixture UAT:** dua pendaftaran terkirim tanpa dokumen; siapkan dokumen dummy untuk verifikasi/pengesahan yang aman (AL-007). Uji batas akses admin biasa 403 telah dilakukan; lanjutkan skenario belum teruji pada `adminlivetest/03-CHECKLIST-DAN-HASIL.md`.

## 5. P1 — Operasional dan keamanan

- [ ] Pastikan backup database harian dan backup storage privat berjalan serta dapat direstore pada lingkungan uji.
- [ ] Siapkan SOP insiden Fonnte, upload, PDF, database, dan pemulihan migration.
- [ ] Pastikan log tidak berisi NIK plaintext, OTP, password, token, Authorization header, atau file sensitif.
- [ ] Jalankan security review untuk IDOR, CSRF, XSS, SQL injection, upload abuse, rate limiting, session, authorization, dan QR privacy.
- [ ] Audit konten landing/manual agar tidak mengklaim kelulusan, ranking, atau keputusan seleksi.
- [ ] Pastikan jam PTSP di seluruh UI/manual konsisten: **07.00 – 15.00 WIB**.
- [ ] Pastikan branding resmi, logo, nama institusi, alamat, consent, dan kontak helpdesk sudah disahkan pemilik.

## 6. Quality gates final

- [ ] `php artisan test` — 0 failed; laporkan passed/skipped/assertions faktual.
- [ ] `vendor/bin/pint --test` — lulus.
- [ ] `npm.cmd run build` — lulus.
- [ ] `composer audit` — tidak ada advisory kritis/tinggi.
- [ ] `npm.cmd audit` — tidak ada vulnerability kritis/tinggi.
- [ ] `git diff --check` — lulus.
- [ ] Browser smoke test publik, pendaftar, admin, superadmin, dan pewawancara.
- [ ] Review owner atas bukti UAT dan checklist launch.

## 7. Stop conditions

### Bukti tambahan superadmin, 27 November 2026 sekitar 20.26 WIB

Login/logout, redirect tamu admin, branding PPDB, penyimpanan template, dan unduhan CSV berhasil di situs uji. Audit komunikasi belum masuk ke Audit Aktivitas (AL-009). Panel superadmin belum menyediakan kirim pesan uji pada mode gateway Fonnte legacy (AL-010); izin pemilik untuk satu pesan ke HP pribadinya belum digunakan dan penerimaan belum terbukti. Lihat `adminlivetest/03-CHECKLIST-DAN-HASIL.md`.

Tindak lanjut lokal AL-010: aksi kirim uji superadmin ditambahkan dan diuji otomatis; bukti live menunggu deployment dan satu pengiriman yang diizinkan pemilik.

Hasil live berikutnya: satu pesan uji superadmin diterima di HP pemilik, namun UI menyatakan gateway belum menerima (AL-011). Pengiriman fisik lulus; penafsiran respons gateway dan OTP pemulihan belum lulus. Tidak ada pesan kedua.

Perbaikan lokal AL-011: parser respons Fonnte menerima `id` array sebagaimana dokumentasi resmi; reproduksi otomatis lulus. Verifikasi respons sukses live menunggu satu pengiriman baru yang disetujui pada putaran lain.

Setelah push `4040f07`, berkas adapter di Hostinger sudah memuat parser baru. Pengiriman ulang tidak dilakukan.

Jangan menyatakan launching siap jika salah satu kondisi ini belum tertutup:

- format nomor masih memiliki dua generator/kontrak;
- migration MariaDB belum diverifikasi aman;
- backup/pemulihan belum dibuktikan;
- QR final belum dapat dipindai;
- OTP/password sementara live belum terbukti;
- role admin/superadmin/pewawancara belum melewati acceptance;
- quality gate final gagal;
- pemilik belum menyetujui checklist launch.
