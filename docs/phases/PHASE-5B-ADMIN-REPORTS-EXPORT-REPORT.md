# Phase 5B — Admin Reports & Redacted CSV Export

## Status

| Gate | Status |
|---|---|
| IMPLEMENTED | Complete on `feature/ppdb-admin-reports-export` |
| AUTOMATED TESTED | Phase 5B.1 focused suite: 7 passed, 29 assertions; full suite: 312 passed, 11 skipped, 1.405 assertions |
| COMMITTED | `35f6813` implementation, `471bdf9` documentation, `1465c58` hardening |
| PUSHED | Pending Phase 5B.1 hardening push; target `origin/feature/ppdb-admin-reports-export` |
| DEPLOYED | Not performed by Codex |
| LIVE VERIFIED | Not performed |

## Architecture

`AdminReportService` owns one validated registration query used by both the report summary and CSV export. The query is restricted to `SUBMITTED` registrations and supports period, program, submit-date, and document-verification filters. CSV output uses an HTTP streaming response with an Eloquent cursor, UTF-8 BOM, fixed headers, WIB timestamps, and no temporary or permanent export file. Phase 5B.1 removed the cursor/eager-loading N+1 risk by selecting applicant, period, and program labels through joins; document status counts remain SQL aggregates.

## Data contract and privacy

The CSV contains only registration number, applicant name, academic year, admission period, program, submit time, total documents, and document status counts. It never includes NIK, parent/guardian identity, phone numbers, address, birth data, income, credentials, tokens, private paths, original filenames, binary content, QR tokens, or audit PII. Text values beginning with `=`, `+`, `-`, `@`, tab, or carriage return are prefixed with a single quote before CSV quoting.

## Authorization, rate limiting, and audit

Both routes are protected by existing `auth`, `active`, `admin`, and `can:export_reports` middleware. The Form Request also authorizes the permission. Exports are limited to 10 requests per user per 10 minutes and return a safe 429 with `Retry-After`. Every accepted export request writes `admin_report_export_requested` to the existing configuration audit table with actor ID, format, normalized filter IDs/status/dates, and row count only. If audit recording fails, the stream is not sent. Rate limiting is evaluated before constructing or counting the report query; regression coverage verifies that limited requests do not query `registrations` and that streaming does not issue per-row relationship queries.

Phase 5B.1 hardening is committed as `1465c58` and is ready to update PR #1 after this documentation commit.

## Documentation and operations

The Admin and Superadmin menus expose the same single workspace route. Admins can open `/admin/reports`, apply filters, review aggregate cards, and download CSV. No migration is required for Phase 5B. Phase 5A's production migration/deployment and live acceptance remain pending and are not changed by this phase.

## Known limitations

- CSV is the first supported format; no XLSX/PDF export is included.
- Authorization is permission-based and intentionally does not introduce per-period scoping because none exists in the current RBAC model.
- Browser/live acceptance and large-production-dataset benchmarking remain owner/deployment responsibilities.
