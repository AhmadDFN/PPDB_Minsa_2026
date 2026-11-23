# Runbook — Superadmin Settings & Admin Provisioning

Dokumen ini adalah checklist untuk pemilik server. Codex tidak menjalankan migration production, membaca credential, atau melakukan live acceptance.

## 1. Backup dan status migration

Sebelum perubahan, buat backup database dan simpan lokasi backup serta waktu pengambilannya. Jalankan read-only:

```bash
php artisan migrate:status
```

Migration yang harus diverifikasi pada production:

- `2026_11_20_080002_add_must_change_password_to_users_table`
- `2026_11_23_120000_create_admin_configuration_audit_logs_table`
- `2026_11_20_080004_create_system_settings_tables`

Jangan gunakan `migrate:fresh`, `migrate:refresh`, `db:wipe`, `DROP TABLE`, atau `TRUNCATE`.

## 2. Pemeriksaan schema phpMyAdmin

```sql
SHOW COLUMNS FROM users LIKE 'must_change_password';
SHOW COLUMNS FROM users LIKE 'password_changed_at';
SHOW TABLES LIKE 'admin_configuration_audit_logs';
SHOW TABLES LIKE 'system_settings';
SHOW TABLES LIKE 'system_setting_logs';
SELECT name, guard_name FROM roles
WHERE name IN ('superadmin', 'admin_ppdb') AND guard_name = 'web';
```

Jika objek yang diperlukan belum ada, jangan mengakali aplikasi dengan melewati audit. Setelah backup dan pemeriksaan kompatibilitas, jalankan migration additive yang sudah ada melalui prosedur deployment resmi.

## 3. Verifikasi setelah migration

1. Jalankan `php artisan migrate:status` ulang dan simpan bukti.
2. Buka `/admin/pengaturan` sebagai Superadmin, ubah nama institusi, dan pastikan satu baris baru muncul di `system_setting_logs`.
3. Buka `/admin/admin-users/create`, buat akun QA baru dengan email khusus testing, dan pastikan akun `ADMIN`, `PENDING_ACTIVATION`, role `admin_ppdb`, serta audit `created`.
4. Pastikan kegagalan pengiriman undangan tidak menghapus akun pending.
5. Hapus/bersihkan data QA hanya sesuai prosedur owner; jangan menyentuh data pendaftar existing.

Catat commit SHA, migration yang dijalankan, backup, waktu deployment, hasil verifikasi, dan rollback plan pada deployment history.
