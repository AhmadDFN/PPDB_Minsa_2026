# Phase 5C — Audit Viewer & Activity Monitoring

## Status gate

| Status | Hasil |
|---|---|
| IMPLEMENTED | Viewer Superadmin read-only `/admin/audit-logs` dan service timeline terpusat selesai di branch `feature/ppdb-audit-viewer`. |
| AUTOMATED TESTED | `AdminAuditViewerTest`: 6 passed, 56 assertions; full suite: 318 passed, 11 skipped, 1.462 assertions. Quality gates PASS. |
| COMMITTED | Viewer `d51a6e6`, initial docs `5c6b311`, review fixes `f201ca1`, review docs `64a4139`, 5C.2 implementation `6305429`, and 5C.3 implementation `1aaf680`. |
| PUSHED | Pending final 5C.3 documentation push; previous remote SHA `a48f4e1c4f654bb99913c9c08210b77aafb99580`. |
| DEPLOYED | Tidak dilakukan. |
| LIVE VERIFIED | Tidak dilakukan. |

## Sumber dan deduplikasi

`AdminActivityLogService` membaca `admin_configuration_audit_logs`, `system_setting_logs`, `registration_document_verifications`, dan `registration_logs` hanya jika tabel tersedia. Semua sumber dinormalisasi dalam satu query timeline, diurutkan `occurred_at DESC` dengan tie-breaker deterministik, lalu dipaginasi 25 baris.

History `registration_document_verifications` adalah sumber canonical untuk perubahan status dokumen. Saat tabel history tersedia, event `verification_*` dari `admin_configuration_audit_logs` dikecualikan agar satu perubahan bisnis tidak tampil dua kali. Jika history belum tersedia, event audit configuration tetap menjadi fallback.

## Privacy dan authorization

Viewer hanya memakai `view_activity_logs`, sehingga Admin PPDB dan applicant ditolak server-side. Raw `before`, `after`, `changes`, reason, NIK, email, nomor telepon, IP, user-agent, path file, nama file asli, password, OTP, dan token tidak dikirim ke Blade. Ringkasan dibentuk dari dictionary event yang aman; nomor registrasi hanya ditampilkan bila tersedia.

## Filter dan source health

Filter GET tervalidasi mencakup tanggal, source allowlist, action allowlist, actor ID, dan pencarian aman maksimal 80 karakter. Jika tabel sumber belum tersedia, halaman menampilkan partial-coverage warning beserta migration terkait tanpa menjalankan migration dari HTTP request. Kegagalan koneksi database tidak diperlakukan sebagai empty state.

Review fixes memverifikasi bahwa action filter memakai normalized action (`setting_updated` dan `verification_*`) pada semua sumber. Registration activity menggunakan `users.account_type`: administrator menampilkan nama admin, applicant ditampilkan sebagai `Pendaftar`, dan actor hilang sebagai `Aktor tidak tersedia`; nama applicant tidak pernah dikirim ke Blade.

Phase 5C.2/5C.3 juga memverifikasi dropdown aktor hanya mengambil akun dengan nilai enum `admin`, Applicant tidak ditampilkan, dan action `updated_after_submission` serta `updated_draft` diterima Form Request serta dapat difilter.

Retention, penghapusan, arsip, scheduler, alert threshold, dan export audit belum diputuskan dan tidak diubah pada fase ini.

## Rilis

Phase 5C menggunakan stacked branch terhadap `feature/ppdb-admin-reports-export` karena PR #1 masih OPEN. Review fixes dipush ke branch yang sama dan PR #2 tetap digunakan. Tidak ada migration baru, perubahan audit writer, production migration, deployment, merge, atau live acceptance pada fase ini.
