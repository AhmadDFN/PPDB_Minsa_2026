# Phase 5A — Document Verification & Applicant Review

## Status

| Gate | Status |
|---|---|
| IMPLEMENTED | Selesai lokal pada branch `feature/ppdb-document-verification` |
| AUTOMATED TESTED | Focused Phase 5A: 4 passed, 15 assertions; focused regression: 57 passed, 275 assertions; full suite: 305 passed, 11 skipped, 1,376 assertions |
| COMMITTED | Pending owner approval |
| PUSHED | Pending |
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

Quality gates currently pass: Pint, Vite production build, Composer audit, npm production audit, Blade cache compilation, relative-link scan, and whitespace scan. The full suite requires explicit test-process `APP_NAME=PPDB Minsatama` (without it, the pre-existing FoundationTest falls back to Laravel). After owner approval, commit and push to `main`, apply the additive migration with backup/rollback procedure, then perform controlled live acceptance testing and update this report with commit SHA, deployment time, migration result, and live evidence.
