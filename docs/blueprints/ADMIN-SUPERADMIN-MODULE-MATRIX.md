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
| Periode penerimaan | `manage_admission_periods` | GAP | GAP | master-data UI belum ada |
| Program | `manage_programs` | GAP | GAP | master-data UI belum ada |
| Persyaratan dokumen | `manage_document_requirements` | GAP | GAP | master-data UI belum ada |
| Akun admin | `manage_admin_users` | DENIED | GAP | belum ada controller/UI |
| Pengaturan branding | `manage_system_settings` | DENIED | AKTUAL | `/admin/pengaturan` |
| Audit activity | `view_activity_logs` | DENIED | GAP | viewer/filter belum ada |

Applicant tetap terisolasi dari seluruh modul admin. Permission yang ada di seeder bukan bukti endpoint sudah tersedia.
