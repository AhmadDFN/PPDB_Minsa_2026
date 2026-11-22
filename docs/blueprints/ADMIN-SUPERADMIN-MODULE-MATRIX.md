# Matriks Modul Admin & Superadmin

Legenda: **AKTUAL** = endpoint/controller/view ada; **GAP** = permission/konsep ada tetapi implementasi belum ada; **TARGET** = rancangan fase berikutnya.

| Modul | Permission | Admin PPDB | Superadmin | Status audit |
|---|---|---:|---:|---|
| Dashboard | `view_dashboard` | AKTUAL | AKTUAL | `/admin/dashboard` |
| Lihat/cari/detail pendaftar | `view_applicants` | AKTUAL | AKTUAL | `ApplicantManagementController` |
| Preview/unduh dokumen privat | `view_applicants` | AKTUAL | AKTUAL | ownership registration check |
| Reopen/close pendaftaran | `reopen_registration` | AKTUAL | AKTUAL | action + audit log |
| Verifikasi dokumen | `verify_documents` | GAP | AKTUAL secara permission saja | controller/view belum ada |
| Ekspor laporan | `export_reports` | GAP | AKTUAL secara permission saja | format/retention belum diputuskan |
| Periode penerimaan | `manage_admission_periods` | GAP | AKTUAL | Phase 4A CRUD, activation, date protection, audit |
| Program | `manage_programs` | GAP | AKTUAL | Phase 4A CRUD, period relation, activation, audit |
| Persyaratan dokumen | `manage_document_requirements` | GAP | AKTUAL | Phase 4A CRUD, required toggle, scope, audit |

Phase 4A.2 integrity controls apply to all three configuration modules: mutations and audit entries are atomic, admission-period activation is protected by a database-level single-active invariant, historical date edits are normalized, and program-specific requirements must reference an active period/program pivot.
| Akun admin | `manage_admin_users` | DENIED | AKTUAL | Phase 4B controller/UI, lifecycle, reset, audit |
| Pengaturan branding | `manage_system_settings` | DENIED | AKTUAL | `/admin/pengaturan` |
| Audit activity | `view_activity_logs` | DENIED | GAP | viewer/filter belum ada |

Applicant tetap terisolasi dari seluruh modul admin. Permission yang ada di seeder bukan bukti endpoint sudah tersedia.
