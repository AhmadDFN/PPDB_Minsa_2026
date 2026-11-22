# Roadmap Implementasi Admin & Superadmin

Roadmap ini usulan berurutan, bukan persetujuan implementasi.

| Fase | Deliverable | Prasyarat | DoD |
|---|---|---|---|
| A | Kontrak bisnis & permission | keputusan owner | matrix disetujui, status/retention/export ditetapkan |
| B | Verifikasi dokumen | status verifikasi + audit schema | route/service/UI, positive-negative tests, IDOR tests |
| C | Master periode/program/persyaratan | aturan periode & migrasi additive | CRUD berizin, validasi overlap, audit, rollback plan |
| D | Ekspor laporan | format redacted + privacy review | streaming/job, rate limit, audit download |
| E | Akun admin & lifecycle | SOP provisioning | create/suspend/reset, no plaintext secret, audit |
| F | Audit viewer & health | retention/alert policy | filter/pagination, redaction, observability checks |
| G | Hardening & UAT | QA accounts separate | browser acceptance Admin + Superadmin, sign-off owner |

## Urutan prioritas

P0: kontrak, verifikasi dokumen, dan audit. P1: master data serta ekspor. P2: lifecycle akun, audit viewer, health dashboard, dan polish UX.

## Stop conditions

Hentikan fase bila permission ambigu, data migration breaking, PII exposure, atau test gate gagal. Tidak boleh membuat akun QA memakai credential historis; gunakan dua akun baru yang terpisah saat fase UAT disetujui.
