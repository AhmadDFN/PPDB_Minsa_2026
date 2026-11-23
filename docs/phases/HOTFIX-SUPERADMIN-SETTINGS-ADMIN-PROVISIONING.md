# Hotfix — Superadmin Settings & Admin Provisioning

## Status

| Gate | Status |
|---|---|
| IMPLEMENTED | Complete locally on `fix/superadmin-settings-admin-provisioning` |
| AUTOMATED TESTED | Hotfix focused: 4 passed, 22 assertions; AdminUserAccessManagement: 11 passed, 48 assertions; RegistrationSimplification: 11 passed, 89 assertions; full suite: 309 passed, 11 skipped, 1.398 assertions |
| COMMITTED | Pending owner review |
| PUSHED | Pending; target `origin/fix/superadmin-settings-admin-provisioning` |
| DEPLOYED | Not performed by Codex |
| LIVE VERIFIED | Not performed |

## Root-cause assessment

Admin creation already uses one transaction around user creation, `ADMIN`/`PENDING_ACTIVATION` state, random password generation, fixed `admin_ppdb` role, and `admin_configuration_audit_logs` creation. A generic failure therefore means one of the required schema/role dependencies is unavailable or a database operation failed. The repository cannot prove the Hostinger schema state without server evidence. Required production checks are listed in the runbook; no production migration was executed.

## Implemented hardening

- `SystemSetting::set()` now wraps setting upsert and `system_setting_logs` creation in one transaction with row locking. Cache invalidation occurs only after commit; audit failure rolls the setting back.
- `SystemSettingController` returns a safe error message when persistence fails.
- Admin provisioning keeps its atomic transaction and now writes only exception class/code to the additional diagnosis log; no password, token, NIK, email, or phone is included.
- New tests verify provisioning rollback, identity/role/password state, settings audit/cache behavior, rollback on log failure, and HTML sanitization.

## Production prerequisites

The owner must take a database backup and run the read-only checks in `docs/deployment/HOTFIX-SUPERADMIN-SETTINGS-ADMIN-PROVISIONING-RUNBOOK.md`. If migrations are pending, apply only the existing additive migrations after backup and rollback review. Do not bypass audit, create manual columns, or run destructive commands.
