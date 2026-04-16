# Upgrade Plan: PyroCMS Pro 2.2.4 (PHP 5.3 → 8.2, MySQL 5.7 → 8.4)

## Current State

| Component       | Version           | Target     |
|-----------------|-------------------|------------|
| PyroCMS         | 2.2.4 Pro Edition |            |
| CodeIgniter     | 3.0-dev           | 3.1.13     |
| PHP             | 5.3.0 minimum     | **8.2**    |
| MySQL           | 5.7               | **8.4.2**  |
| Active site     | bockavel          |            |
| Dev machine PHP | 8.4.19            |            |

## Master Progress Tracker

Check off each item as it's completed. This is the single source of truth for progress across sessions.

### Phase 0 — Test Suite
- [ ] Create `tests/route_test.php`
- [ ] Run baseline against current working site
- [ ] Save baseline with `--save-baseline`

### Phase 1 — CodeIgniter 3.0-dev → 3.1.13
- [ ] Download CI 3.1.13
- [ ] Backup `system/codeigniter/`
- [ ] Replace `system/codeigniter/` contents
- [ ] Check for encrypted persistent data (mcrypt → OpenSSL risk)
- [ ] Verify config compatibility
- [ ] Test session persistence
- [ ] Run route tests — compare baseline

### Phase 2 — Lex Template Engine
- [ ] Fix 2a: `str_replace()` type guard in `Lex/Parser.php:190`
- [ ] Fix 2b: `count()` guard in `Lex/Parser.php:387`
- [ ] Fix 2c: Null coercion in `Lex/Parser.php` `inject_extractions()`
- [ ] Fix 2d: `eval()` ParseError catch in `Lex/Parser.php:780`
- [ ] Fix 2e: `eval()` ParseError catch in `MX/Loader.php:317`
- [ ] Fix 2f: Remove `&` reference in `MY_Parser.php:19`
- [ ] Run route tests — compare baseline

### Phase 3 — PyroCMS Core Blockers
- [ ] Fix 3a: Replace `mysql_*` calls in `search_m.php`
- [ ] Fix 3b: Replace `each()` in `MX/Modules.php:80`
- [ ] Fix 3c: Fix `Markdown_parser.php` (constructor + create_function + curly braces)
- [ ] Fix 3d: Fix `Textile.php` (constructor + magic_quotes + callbacks)
- [ ] Fix 3e: Fix curly braces in `field.choice.php`
- [ ] Fix 3f: Replace `strftime()` + `utf8_encode()` in `MY_date_helper.php`
- [ ] Fix 3g: Fix curly brace in `MY_inflector_helper.php`
- [ ] Run route tests — compare baseline

### Phase 4 — Third-Party Libraries
- [ ] Determine: Is Simplepie used by bockavel?
- [ ] Determine: Is Cloudmanic Storage spark used?
- [ ] Determine: Is Backups module used?
- [ ] Fix/replace/remove Simplepie
- [ ] Fix/remove Cloudmanic Storage spark
- [ ] Fix/skip Backups module
- [ ] Run route tests — compare baseline

### Phase 5 — Null-Coercion & Deprecation Sweep
- [ ] Install Rector standalone
- [ ] Run Rector dry-run on `system/cms/`
- [ ] Run Rector dry-run on `addons/`
- [ ] Review and apply Rector changes
- [ ] Run PHPCompatibility sniffs
- [ ] Deploy to staging with PHP 8.2 + `error_reporting(E_ALL)`
- [ ] Exercise all code paths, collect deprecation log
- [ ] Fix remaining deprecations manually
- [ ] Verify zero `E_DEPRECATED` in log
- [ ] Run route tests — compare baseline

### Phase 6 — Security Hardening (optional)
- [ ] Check password column width in DB
- [ ] Widen password column to VARCHAR(255) if needed
- [ ] Implement `password_hash()` / `password_verify()` in `ion_auth_model.php`
- [ ] Implement transparent SHA1 → bcrypt re-hash on login
- [ ] Verify CI 3.1.13 fixed session integrity (Phase 1)
- [ ] Test existing user login + re-hash

### Phase M — MySQL 5.7 → 8.4.2
- [ ] Dump database backup
- [ ] Fix M1: GROUP BY query in `user_m.php:77-80`
- [ ] Fix M1: GROUP BY query in `file_m.php:47-51`
- [ ] Fix M1: GROUP BY query in `pyroforms/admin.php:130-133`
- [ ] Fix M2: Update `database.php` charset to `utf8mb4`
- [ ] Fix M2: Convert existing DB tables to utf8mb4 (SQL)
- [ ] Fix M2: Update charset in migration files (9 files)
- [ ] Fix M2: Fix latin1 tables in pyroforms + backups details.php
- [ ] Fix M3: Change TEXT prefix UNIQUE index in `search/details.php`
- [ ] Fix M3: Change TEXT prefix UNIQUE index in migration `098`
- [ ] Fix M4: Change MyISAM → InnoDB in code (8 files, 13 locations)
- [ ] Fix M4: Convert existing MyISAM tables in DB (SQL)
- [ ] Fix M5: Remove INT display width in migrations (6+ files)
- [ ] Fix M6: Verify auth plugin after MySQL 8.4 switch
- [ ] Install MySQL 8.4.2
- [ ] Import/upgrade database
- [ ] Run route tests — compare baseline

---

## Execution Order

Phase M (MySQL) can run in parallel with PHP phases since the issues are independent.

```
Phase 0 (test suite)
    |
    +-- PHP track: Phase 1 → 2 → 3 → 4 → 5 → 6
    |
    +-- MySQL track: Phase M (DB conversion on 5.7, then switch to 8.4)
```

## Effort Estimate

| Phase | Effort   | Risk     |
|-------|----------|----------|
| 0     | 0.5 day  | None     |
| 1     | 1-2 days | Low-Med  |
| 2     | 1 day    | Medium   |
| 3     | 1-2 days | Low      |
| 4     | 1 day    | Low      |
| 5     | 2-3 days | Med-High |
| 6     | 1 day    | Medium   |
| M     | 1-2 days | Medium   |
| **Total** | **9-13 days** | |

## Detailed Plans

- [Phase 0: Test Suite](01-phase0-test-suite.md)
- [Phase 1: CodeIgniter Upgrade](02-phase1-codeigniter-upgrade.md)
- [Phase 2: Lex Template Engine](03-phase2-template-engine.md)
- [Phase 3: PyroCMS Core Blockers](04-phase3-core-blockers.md)
- [Phase 4: Third-Party Libraries](05-phase4-third-party.md)
- [Phase 5: Deprecation Sweep](06-phase5-deprecation-sweep.md)
- [Phase 6: Security Hardening](07-phase6-security-hardening.md)
- [MySQL 5.7 → 8.4 Migration](08-mysql-upgrade.md)
