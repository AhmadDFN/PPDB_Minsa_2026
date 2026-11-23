# Phase 3C.1 — GitHub Actions MySQL Validation

**Status:** CI HARNESS READY — AWAITING GITHUB EXECUTION

Workflow: `.github/workflows/communication-center-mysql-validation.yml`

## Environment

- GitHub-hosted `ubuntu-24.04` runner.
- MySQL `8.4` service container with InnoDB.
- Disposable database: `ppdb_minsatama_cc_concurrency_test`.
- Dedicated CI user with no production or staging credentials.
- PHP 8.3 with `pdo_mysql`, Composer dependencies, and Laravel migrations.

## Triggers

- Manual `workflow_dispatch`.
- Pull requests targeting `main`.
- Pushes to `ci/phase-3-mysql-validation`.

The workflow does not add a push trigger for `main` and does not modify Hostinger deployment workflows.

## Test order

1. Verify the disposable MySQL service and database identity.
2. Run migrations only against the CI database.
3. Run Communication Center feature tests.
4. Require and run the dedicated MySQL Communication Center concurrency harness.
5. Run existing registration MySQL concurrency tests.
6. Run the full Feature suite.

The harness uses independent Symfony Process workers, filesystem barriers, explicit MySQL connection checks, and the real Communication Center services. It covers singleton initialization, simultaneous activation, lock contention, credential version conflict, rollback after audit failure, and bounded worker failure handling. The workflow sets `COMMUNICATION_CENTER_MYSQL_CONCURRENCY=1`; ordinary local SQLite runs skip these tests safely.

No GitHub Actions execution has been claimed from local inspection.

## Local validation

The workflow YAML was inspected locally and `git diff --check` passes. GitHub-hosted execution remains pending.
