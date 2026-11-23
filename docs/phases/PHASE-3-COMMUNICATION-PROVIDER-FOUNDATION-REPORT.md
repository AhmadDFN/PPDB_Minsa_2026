# PHASE 3 — COMMUNICATION CENTER PROVIDER FOUNDATION

**Status:** IMPLEMENTED LOCALLY — PENDING OWNER REVIEW
**Scope:** provider abstraction, Fonnte Device Token, encrypted channel credentials, single active channel, manual switching, Superadmin-only UI, and legacy transition.

## Architecture

- `WhatsAppProviderInterface` defines connection validation, device profile, and message sending.
- `FonnteProvider` is the only implemented adapter; endpoint URLs are fixed in code.
- Registry and resolver keep business flow independent from provider-specific API formats.
- Communication channel metadata, encrypted credentials, global settings, and safe audit records use separate tables.

## Security

- Device Token is encrypted with Laravel `Crypt` and never exposed in responses, logs, or audit metadata.
- New credentials are validated before replacing an existing credential.
- Disconnected devices cannot be activated.
- Activation is transactional and keeps only one active channel globally.
- No automatic failover or arbitrary test-recipient endpoint is provided.
- Admin PPDB and applicants are denied server-side.

## Existing flow transition

OTP activation, OTP recovery, and temporary-password delivery retain the legacy `FONNTE_TOKEN` path until a database channel is explicitly activated. Once database mode is enabled, missing or failed active channels return a safe failure and never silently fall back to `.env`.

## Validation

- Focused Communication Center tests: **14 passed, 37 assertions**.
- Existing Fonnte/authentication regression tests: **50 passed, 217 assertions**.
- Full suite: **287 passed, 11 skipped, 1,307 assertions**.
- Pint: passed.

## Phase 3B hardening

- Active channel tidak lagi menyimpan flag duplikat; pointer `active_channel_id` menjadi sumber kebenaran tunggal.
- Singleton settings dibuat idempotent dengan deterministic insert-ignore dan dikunci sebelum perubahan pointer.
- Credential update memakai validasi provider di luar lock, row lock, optimistic version check, encryption, metadata update, dan audit dalam satu transaction.
- Adapter Fonnte melakukan defensive validation target dan message tanpa mengirim payload invalid.
- Test tambahan mencakup singleton, deactivation, disconnected activation, stale credential version, dan adapter payload validation.
- Concurrent database validation terhadap MySQL/MariaDB belum dieksekusi karena database disposable belum tersedia; SQLite tidak dianggap bukti row-lock concurrency.
- Migration Communication Center dapat di-up dan di-down secara terpisah. Full historical rollback tetap terkena issue lama pada migration `2026_11_15_130000_add_ppdb_fields_to_users_table`.

## Out of scope

Additional providers, notification rules, message logs, retention policy, arbitrary test-send, broadcasts, Account API device management, and production migration/deployment remain pending roadmap work.

## Phase 3D — Editable WhatsApp message templates

- Four Superadmin-editable templates are available for activation OTP, password-recovery OTP, initial temporary password, and resent temporary password.
- Supported placeholders are validated server-side; unknown placeholders are rejected before saving.
- Templates are stored in the additive `communication_settings` migration and fall back to the approved defaults when unset.
- Temporary-password messages expose only the masked NIK; the password remains in a separate message as specified by the approved copy.
