# Phase 1: Upgrade CodeIgniter 3.0-dev → 3.1.13

**Status:** Not started
**Prerequisites:** Phase 0 baseline saved
**Effort:** 1-2 days | **Risk:** Low-Med

---

## What CI 3.1.13 Fixes Automatically

| Issue                    | Files Fixed                            | PHP Version |
|--------------------------|----------------------------------------|-------------|
| `each()` function        | Xmlrpc.php, Xmlrpcs.php, Security.php  | 8.0         |
| `mcrypt` extension       | Encrypt.php (replaced w/ OpenSSL)      | 7.2         |
| `get_magic_quotes_gpc()` | Input.php, Email.php                   | 7.4         |
| `magic_quotes_runtime`   | CodeIgniter.php                        | 5.4         |
| `HTTP_RAW_POST_DATA`     | Xmlrpcs.php, Input.php                 | 7.0         |
| `strftime()`             | Xmlrpc.php                             | 8.1         |

## Implementation Tasks

### Pre-flight checks

- [ ] Check for persistent encrypted data that uses mcrypt:
  ```
  grep -r "encrypt->encode\|encrypt->decode\|this->encrypt" system/cms/ addons/
  ```
  If found: plan a data migration step before replacing Encrypt.php.

### Upgrade steps

- [ ] Download CI 3.1.13:
  ```bash
  cd /tmp && wget https://github.com/bcit-ci/CodeIgniter/archive/refs/tags/3.1.13.tar.gz && tar xzf 3.1.13.tar.gz
  ```
- [ ] Backup current CI directory:
  ```bash
  cp -r system/codeigniter system/codeigniter.bak-$(date +%Y%m%d)
  ```
- [ ] Replace `system/codeigniter/` contents with CI 3.1.13 `system/` directory:
  ```
  CodeIgniter-3.1.13/system/core/       → system/codeigniter/core/
  CodeIgniter-3.1.13/system/database/   → system/codeigniter/database/
  CodeIgniter-3.1.13/system/helpers/    → system/codeigniter/helpers/
  CodeIgniter-3.1.13/system/language/   → system/codeigniter/language/
  CodeIgniter-3.1.13/system/libraries/  → system/codeigniter/libraries/
  ```
- [ ] Confirm PyroCMS customizations in `system/cms/` are NOT overwritten
- [ ] Verify config: `database.php` uses `mysqli` (line 41) and `$query_builder = TRUE` (line 97)
- [ ] Review session config in `system/cms/config/config.php` for CI 3.1.x compatibility

## Risks

| Risk | Impact | Mitigation |
|------|--------|------------|
| Session library rewrite | Users logged out, session data lost | Test login/logout immediately. Adjust session config if needed. |
| Encrypt library (mcrypt → OpenSSL) | Can't decrypt old encrypted DB data | Check pre-flight. If encrypted data exists, add migration step. |
| Database driver API changes | CRUD failures | Test all CRUD operations after upgrade. |

## Verification

- [ ] App starts without fatal errors
- [ ] Admin login works
- [ ] Session persists across page loads (navigate multiple pages while logged in)
- [ ] Page CRUD works (create, edit, delete)
- [ ] Blog posts display correctly
- [ ] File uploads work
- [ ] Run `php tests/route_test.php --compare-baseline` — zero regressions
