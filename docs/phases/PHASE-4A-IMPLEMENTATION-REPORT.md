# PHASE 4A — PPDB Configuration Management

## Phase 4A.2 hardening

- Configuration mutations and their audit events now commit atomically on the same database connection; audit failure rolls back the mutation and returns an error state.
- A new additive migration adds a nullable generated `active_slot` marker and unique index, enforcing at most one active admission period without restricting inactive records. Ambiguous existing active data aborts migration rather than being modified silently.
- Historical period date checks compare normalized calendar dates, so metadata-only edits remain possible while calendar-date and academic-year changes remain protected.
- Program-specific document requirements now require an active `admission_period_programs` relationship; nullable program scope remains global-to-period.
- MySQL/MariaDB concurrency runtime validation remains pending until a disposable compatible database is available; SQLite constraint coverage is included in the focused tests.

**Status:** IMPLEMENTED; AUTOMATED TESTED — COMMIT/PUSH/DEPLOY/LIVE VERIFIED PENDING

## Release checkpoint status

| Status | Phase 4A state |
|---|---|
| IMPLEMENTED | Complete in this worktree |
| AUTOMATED TESTED | Complete: focused and regression suites passing |
| COMMITTED | Pending local commit |
| PUSHED | Pending; no push performed |
| DEPLOYED | Pending; production migration not run |
| LIVE VERIFIED | Pending acceptance test on `ahmaddanyfn.biz.id` |

The Hostinger target MySQL/MariaDB version must be confirmed before applying the generated-column migration in production. Runtime MySQL/MariaDB concurrency validation has not been claimed.

## Implemented

- Admission Period Management with date validation, single active period enforcement, historical date protection, pagination, and audit events.
- Program Management with activation controls, period relationships, pagination, and historical relationship preservation.
- Document Requirements Management with period/program scope, required/optional toggle, MIME and 5 MB limits, ordering, pagination, and used-requirement protection.
- Server-side permissions using existing `manage_admission_periods`, `manage_programs`, and `manage_document_requirements` permissions.
- One Admin Workspace; no new guard, role, dashboard, or impersonation flow.
- Additive `admin_configuration_audit_logs` migration for redacted before/after configuration history.

## Safety boundaries

- No physical delete route is exposed for periods, programs, or requirements.
- Existing registrations and uploaded documents are not deleted or rewritten.
- Registration number generation and applicant submission flow are unchanged.
- Document formats remain PDF/JPG/JPEG/PNG with a maximum of 5120 KB.

## Pending validation

- Live browser acceptance on dedicated QA accounts.
- Production deployment and migration execution.
- Document verification, reports/export, admin account management, audit viewer, and system health remain outside this phase.
