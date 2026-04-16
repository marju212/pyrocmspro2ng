# Phase 4: Fix Third-Party Libraries & Sparks

**Status:** Not started
**Prerequisites:** Phase 3 complete
**Effort:** 1 day | **Risk:** Low

---

## Overview

Bundled third-party libraries with PHP compatibility issues. For each: decide **patch**, **replace**, or **remove**.

## Implementation Tasks

### Fix 4a: Simplepie (~12,900 lines)

**File:** `system/cms/libraries/Simplepie.php`

- [ ] Determine if Simplepie is used by bockavel:
  ```
  grep -r "simplepie\|SimplePie\|simple_pie" system/cms/ addons/ --include="*.php" -l
  ```
- [ ] **If used:** Replace with SimplePie 1.8.x (PHP 8.x compatible, same API)
- [ ] **If NOT used:** Defer (file is never loaded)

**Issues if patching instead of replacing:**
- `strftime()` on line 6106
- `mysql_connect()` / `mysql_query()` / `mysql_*` functions (13+ calls)
- `UNIQUE (id(125))` TEXT prefix index (line 319)

---

### Fix 4b: Cloudmanic Storage Spark

**Path:** `system/sparks/cloudmanic-storage/1.0.4/`

- [ ] Determine if spark is used:
  ```
  grep -r "cloudmanic\|Cloudfiles\|CloudStorage" system/cms/ addons/ --include="*.php" -l
  ```
- [ ] **If NOT used:** Remove entire `system/sparks/cloudmanic-storage/` directory
- [ ] **If used — `libraries/s3.php`:** Change `$value{0}` → `$value[0]` (line 1366)
- [ ] **If used — `libraries/s3.php`:** Remove `&` from `array(&$this, ...)` (2 occurrences)
- [ ] **If used — `libraries/cloudfiles_http.php`:** Remove `&` from `array(&$this, ...)` (4 occurrences)

---

### Fix 4c: Backups Module

**Path:** `addons/shared_addons/modules/backups/`

- [ ] Determine if Backups module is enabled in bockavel admin
- [ ] **If NOT used:** Skip
- [ ] **If used — `helpers/aws.php`:** Remove `&` from `array(&$this, ...)` (2 occurrences)
- [ ] **If used — `models/backup_m.php:233`:** Replace `utf8_decode($string)` with `mb_convert_encoding($string, 'ISO-8859-1', 'UTF-8')`

---

## Decision Matrix

| Library            | Used? | Action if YES       | Action if NO  |
|--------------------|-------|---------------------|---------------|
| Simplepie          | [ ]   | Replace with 1.8.x  | Defer         |
| Cloudmanic Storage | [ ]   | Patch 3 files        | Remove spark  |
| Backups module     | [ ]   | Patch 2 files        | Skip          |

## Verification

- [ ] RSS feeds work (if Simplepie is used)
- [ ] S3/cloud storage works (if spark is used)
- [ ] Backups can be created (if module is used)
- [ ] Run `php tests/route_test.php --compare-baseline` — zero regressions
