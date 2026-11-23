# Phase 5A — Document Verification & Applicant Review

## Status

| Gate | Status |
|---|---|
| IMPLEMENTED | Complete |
| AUTOMATED TESTED | Focused Phase 5A: 4 passed, 15 assertions; focused regression: 57 passed, 275 assertions; full suite: 305 passed, 11 skipped, 1,376 assertions |
| COMMITTED | `6e7f4d9`, `3b5731a` |
| PUSHED | `origin/main`, remote SHA `3b5731a04cbae382e3d9af591ef158c24cabc9d5` |
| DEPLOYED | Pending; tidak ada migration production yang dijalankan |
| LIVE VERIFIED | Pending; tidak ada live testing oleh Codex |

## Scope delivered

- Admin queue `/admin/document-verification` hanya menampilkan pendaftaran `SUBMITTED`, dengan pencarian dan status.
- Preview PDF/JPEG/PNG melalui private disk dengan ownership check dan header no-store/nosniff.
- Status dokumen terpisah dari status pendaftaran: `PENDING`, `APPROVED`, `REJECTED`, `REVISION_REQUIRED`.
- Approve, reject, dan revision; alasan diwajibkan untuk reject/revision.
- Riwayat status dan audit actor tersimpan dalam transaksi dengan row locking.
- Replacement dokumen menghapus status approval lama dan kembali ke `PENDING`.

## Security and data integrity

Route menggunakan middleware `can:verify_documents`; admin tanpa permission menerima 403. Draft atau dokumen lintas pendaftaran tidak dapat diverifikasi/di-preview. Tidak ada ranking, quota, selection, impersonation, guard, role, atau akun baru. Migration bersifat additive dan hanya membuat tabel history serta kolom verifikasi.

## Remaining gates

Quality gates pass: Pint, Vite production build, Composer audit, npm production audit, Blade cache compilation, relative-link scan, and whitespace scan. The full suite uses `APP_NAME=PPDB Minsatama` in `phpunit.xml`. Phase 5A migration remains pending for production and requires a database backup plus rollback plan before execution; live acceptance remains the owner's responsibility.
