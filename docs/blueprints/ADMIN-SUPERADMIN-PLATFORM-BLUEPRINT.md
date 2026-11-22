# Admin & Superadmin Platform Blueprint

**Status:** PROPOSED / DOCUMENTATION-ONLY
**Audit baseline:** 20 November 2026
**Scope:** portal internal PPDB untuk `admin_ppdb` dan `superadmin`.

## 1. Prinsip platform

- Satu guard `web`, satu tabel `users`, akun internal ditandai `account_type=admin`.
- Otorisasi fail-closed: middleware `admin` dan permission granular Spatie; applicant tidak boleh mengakses `/admin/*`.
- Admin menjalankan operasi pendaftar; Superadmin mengelola konfigurasi, master data, akun internal, dan audit.
- Semua perubahan sensitif harus memiliki actor, timestamp WIB, objek, before/after ter-redaksi, alasan, dan correlation/request id.
- NIK, OTP, password, token Fonnte, dan secret tidak boleh masuk UI, log, ekspor, atau dokumentasi.
- Portal hanya mengelola pendaftaran sampai bukti pendaftaran; seleksi, ranking, pengumuman, dan daftar ulang tetap di luar scope.

## 2. Baseline implementasi aktual

Tersedia: `/admin/login`, `/admin/dashboard`, `/admin/pendaftar`, detail/preview/download dokumen privat, `/admin/registrations` untuk reopen, dan `/admin/pengaturan` untuk nama institusi. `admin_ppdb` memiliki dashboard, applicant view, export/verify/reopen permissions di seeder; hanya sebagian yang mempunyai endpoint nyata. `superadmin` menerima seluruh permission seeder.

## 3. Target domain

1. **Dashboard & monitoring** — metrik periode aktif, backlog, SLA, aktivitas, dan health checks.
2. **Applicant operations** — pencarian, filter, detail, kelengkapan, verifikasi dokumen, reopen, dan export terkontrol.
3. **Master data** — periode penerimaan, program, persyaratan dokumen, branding.
4. **Internal identity** — akun admin, role, permission, status suspend, reset akses.
5. **Audit & reporting** — log immutable, filter, export redacted, retention.

## 4. Non-functional requirements

RBAC diuji pada route dan service; IDOR diuji lintas registration/document; pagination wajib; query dashboard diberi batas N+1; ekspor memakai job/streaming dan rate limit; perubahan master data additive dan backward-compatible; semua tanggal ditampilkan WIB.

## 5. Acceptance gates

Tidak ada implementasi baru sebelum business owner menyetujui permission matrix, retention, definisi status verifikasi, format ekspor, dan akun QA terpisah. Setiap fase wajib menambah feature tests positive/negative, audit event, dokumentasi aktual, serta lulus gate repositori.
