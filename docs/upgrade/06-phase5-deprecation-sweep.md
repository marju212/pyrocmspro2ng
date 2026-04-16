# Phase 5: Null-Coercion & Deprecation Sweep

**Status:** Not started
**Prerequisites:** Phase 4 complete
**Effort:** 2-3 days | **Risk:** Med-High

---

## Why This Is the Hardest Phase

PHP 8.0+ emits `E_DEPRECATED` when `null` is passed to internal string functions (`strlen`, `trim`, `strpos`, `strtolower`, `substr`). Not fatal in 8.2, but will be in 9.0. Estimated 50-100 files affected.

**Root cause:** CI's `$this->input->post('key')` returns `NULL` when key doesn't exist.

## Implementation Tasks

### Step 1: Automated scanning with Rector

- [ ] Install Rector standalone:
  ```bash
  cd /tmp && composer require rector/rector --dev
  ```
- [ ] Dry-run on `system/cms/`:
  ```bash
  /tmp/vendor/bin/rector process /path/to/pyrocmspro2/system/cms/ --set php82 --dry-run
  ```
- [ ] Dry-run on `addons/`:
  ```bash
  /tmp/vendor/bin/rector process /path/to/pyrocmspro2/addons/ --set php82 --dry-run
  ```
- [ ] Review dry-run output
- [ ] Apply Rector changes selectively (do NOT run on `system/codeigniter/`)

### Step 2: Static analysis with PHPCompatibility

- [ ] Install PHP_CodeSniffer + PHPCompatibility:
  ```bash
  composer global require squizlabs/php_codesniffer phpcompatibility/php-compatibility
  ```
- [ ] Scan codebase:
  ```bash
  phpcs --standard=PHPCompatibility --runtime-set testVersion 8.2 -p system/cms/ addons/
  ```
- [ ] Fix all reported errors

### Step 3: Runtime testing

- [ ] Deploy to staging with PHP 8.2 + `error_reporting(E_ALL)` + `log_errors = On`
- [ ] Exercise all code paths:
  - [ ] Homepage, news, member listing
  - [ ] Login/logout
  - [ ] Ad create/edit/delete
  - [ ] Admin: pages CRUD
  - [ ] Admin: blog CRUD
  - [ ] Admin: file uploads
  - [ ] Admin: navigation editor
  - [ ] Admin: settings page
  - [ ] Search
  - [ ] User registration
- [ ] Collect deprecation log
- [ ] Fix remaining deprecations by category

### Common fix patterns

**Pattern 1:** `strlen/trim/strpos` with null
```php
strlen($val)   →   strlen($val ?? '')
```

**Pattern 2:** `strtolower/strtoupper` on nullable
```php
strtolower($name)   →   strtolower($name ?? '')
```

**Pattern 3:** Type cast alternative
```php
strlen((string) $value)
```

### Files most likely affected

| Directory | Why |
|-----------|-----|
| `system/cms/core/MY_Controller.php` | Reads input/config values |
| `system/cms/libraries/Template.php` | Processes template data |
| `system/cms/modules/pages/` | Heavy input processing |
| `system/cms/modules/blog/` | Heavy input processing |
| `system/cms/modules/users/` | Form handling |
| `system/cms/modules/files/` | Upload handling |
| `system/cms/modules/streams_core/` | Dynamic field processing |
| `addons/bockavel/modules/ad/` | Custom input handling |
| `addons/bockavel/modules/member/` | Custom input handling |
| `addons/bockavel/modules/news/` | Custom input handling |

## Verification

- [ ] PHP error log has ZERO `E_DEPRECATED` after exercising all code paths
- [ ] No behavioral changes (null becomes empty string, not error)
- [ ] PHPCompatibility sniffs report zero errors for PHP 8.2
- [ ] PHPStan level 0 — zero errors
- [ ] Run `php tests/route_test.php --compare-baseline` — zero regressions
