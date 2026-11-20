# PROJECT STATUS RECONCILIATION — 20 NOVEMBER 2026

## Ringkasan

Hostinger online/testing telah dikonfirmasi pada `https://ahmaddanyfn.biz.id`, dengan GitHub Auto Deploy, database aktif, dan manual applicant test. Status tersebut tidak sama dengan persetujuan Go-Live produksi.

## Matriks bukti modul

| Modul | Kode | Automated test | Live applicant | Live admin | Status |
|---|---|---|---|---|---|
| Registrasi dan OTP | Implemented | Tested | Fonnte/OTP dikonfirmasi berhasil | N/A | OWNER CONFIRMED + LIVE TESTED |
| Profil applicant | Implemented | Tested | Biodata, alamat, ayah, ibu, pendidikan | N/A | LIVE TESTED |
| Dokumen privat | Implemented | Tested | KK, akta, pas foto berhasil diunggah | Belum diuji | APPLICANT LIVE TESTED |
| PDF draft | Implemented | Tested | Berhasil dibuat/diunduh | N/A | APPLICANT LIVE TESTED |
| Consent dan Final Submit | Implemented | Tested | Belum terbukti | N/A | PENDING VERIFICATION |
| PDF final dan QR | Implemented | Tested | Belum terbukti | N/A | PENDING VERIFICATION |
| Admin dashboard | Implemented | Tested | N/A | Belum diuji memadai | ADMIN LIVE TEST PENDING |
| Daftar/detail pendaftar | Implemented | Tested | N/A | Belum diuji memadai | ADMIN LIVE TEST PENDING |
| Reopen registration | Implemented | Tested | N/A | Belum diuji memadai | ADMIN LIVE TEST PENDING |
| System settings | Implemented | Tested | N/A | Belum diuji memadai | SUPERADMIN LIVE TEST PENDING |
| Seleksi, ranking, pengumuman, daftar ulang | Not in scope | N/A | N/A | N/A | NOT IN SCOPE |

## Matriks Admin/Superadmin

| Kemampuan | Admin PPDB | Superadmin | Implementasi | Live test |
|---|---|---|---|---|
| Login | Ya | Ya | Ada | Pending |
| Dashboard | Ya | Ya | Ada | Pending |
| Daftar/detail pendaftar | Ya | Ya | Ada | Pending |
| Preview/download dokumen | Sesuai permission | Ya | Ada | Pending |
| Reopen registration | Sesuai permission | Ya | Ada | Pending |
| System settings | Tidak | Ya | Ada | Pending |
| Verifikasi dokumen workflow | Belum dipastikan | Belum dipastikan | Gap/pending audit | Pending |
| Periode/program/persyaratan CRUD | Belum tersedia penuh | Belum tersedia penuh | Gap | Pending |
| Laporan/export | Belum tersedia penuh | Belum tersedia penuh | Gap | Pending |
| Manajemen akun/role | Belum tersedia penuh | Belum tersedia penuh | Gap | Pending |

## Automated test aktual

Checkout lokal 20 November 2026:

- 289 tests.
- 278 passed.
- 0 failed.
- 11 skipped.
- 1.273 assertions.

Skipped berasal dari test integrasi MySQL/pdo_mysql dan satu skenario yang bergantung pada GD/PNG sesuai kondisi environment test. Pint lulus setelah perbaikan route, Vite build lulus, npm audit lulus, dan Composer audit lulus.

## Temuan keamanan

- Endpoint HTTP GET migrasi produksi dengan token statis ditemukan dan dihapus dari source lokal.
- Regression test memastikan URL tersebut tidak tersedia melalui HTTP.
- Tidak ada endpoint itu yang diakses di Hostinger.
- Credential QA yang pernah dibagikan harus dianggap terekspos dan diganti sebelum operasi resmi.
- Tidak ada perubahan production, push, atau deployment dilakukan dalam audit ini.

## Keputusan yang masih dibutuhkan

1. Verifikasi consent dan Final Submit pada Hostinger.
2. Verifikasi PDF final dan QR.
3. Sesi live Admin/Superadmin dengan akun QA terpisah.
4. Konfirmasi branding/alamat resmi dan backup/restore.
5. Persetujuan eksplisit untuk langkah Go-Live produksi.
