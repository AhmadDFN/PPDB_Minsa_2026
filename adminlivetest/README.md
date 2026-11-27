# Rencana Live Test Admin & Superadmin PPDB

**Status:** rencana, belum dilaksanakan. **Tanggal:** 27 November 2026.
**Target kandidat:** `https://ahmaddanyfn.biz.id` setelah lingkungan dan fixture dikonfirmasi.
**Acuan:** `AGENTS.md`, `ppdbv2/AI-RULES.md`, `ppdbv2/blueprint-wawancara.md`, `ppdbv2/KNOWN-ISSUES.md`, `docs/PROJECT_STATUS.md`, `routes/web.php`, `RoleAndPermissionSeeder`, dan `livetest/09-HASIL-UAT-LIVE-2026-11-27.md`.

## Peta dokumen

1. [Rencana dan persiapan](01-RENCANA-DAN-PERSIAPAN.md): ruang lingkup, prasyarat, urutan, dampak, dan konflik spesifikasi.
2. [Skenario admin dan superadmin](02-SKENARIO-ADMIN-SUPERADMIN.md): langkah, hasil yang diharapkan, dan batas aman.
3. [Checklist dan matriks hasil](03-CHECKLIST-DAN-HASIL.md): catat PASS/FAIL/BLOCKED/SKIPPED per kasus.
4. [Log temuan dan perbaikan](04-LOG-TEMUAN.md): template bukti, diagnosis, perbaikan, regresi, dan status.

## Prinsip pelaksanaan

- Gunakan akun staf khusus uji dan calon siswa/dokumen dummy yang teridentifikasi; jangan menyalin data siswa, password, token, OTP, nomor HP atau NIK lengkap ke laporan, screenshot, CSV, dan chat. Gunakan ID uji internal/masking.
- Rencana ini tidak mengklaim akun, izin, server, fixture, atau backup sudah ada. Jangan menjalankan `LiveTestSeeder`/`migrate:fresh` pada produksi sebagai persiapan. Catatan insiden migrasi ISS-011 perlu dicek terlebih dahulu.
- Pemeriksaan baca dapat dilakukan sebelum tindakan yang mengubah data. Tindakan perubahan hanya pada fixture yang secara eksplisit disiapkan untuk UAT; fitur yang berdampak sistem luas atau mengirim pesan gunakan lingkungan terisolasi/driver tiruan yang telah dikonfirmasi. Jangan menonaktifkan proteksi atau mengubah kunci rahasia.
- Catat status menurut bukti: **PASS** sesuai ekspektasi, **FAIL** berbeda dari ekspektasi, **BLOCKED** tidak bisa dilanjutkan karena hambatan, **SKIPPED** tidak dilaksanakan. Status awal semua kasus **BELUM DIUJI**.
- Saat error menghambat, catat dahulu, perbaiki sesuai instruksi pemilik, uji ulang kasus dan regresi terkait; jangan menandai lulus sebelum ada bukti.
- Isi dokumen ini menyusun rencana saja. Tidak ada pembuatan akun, perubahan server, pengiriman pesan, atau eksekusi UAT saat penyusunan.
