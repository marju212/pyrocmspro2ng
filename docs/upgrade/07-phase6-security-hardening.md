# Phase 6: Security Hardening (Optional)

**Status:** Not started
**Prerequisites:** Phase 5 complete
**Effort:** 1 day | **Risk:** Medium

---

## Why This Phase Exists

Not PHP-version blockers — the app works without them. But since we're touching auth code, it's the right time to fix weak cryptographic choices.

## Implementation Tasks

### Fix 6a: Password Hashing (SHA1 → password_hash)

**File:** `system/cms/modules/users/models/ion_auth_model.php`

- [ ] Check current password column width:
  ```sql
  DESCRIBE bockavel_users;
  ```
- [ ] Widen password column if needed:
  ```sql
  ALTER TABLE bockavel_users MODIFY password VARCHAR(255) NOT NULL;
  ```
- [ ] Edit `hash_password()` (around line 110) — use `password_hash($password, PASSWORD_DEFAULT)`
- [ ] Edit `hash_password_db()` (around line 137) — implement dual-mode verification:
  ```php
  // Check if legacy SHA1 hash (40 hex chars)
  if (strlen($stored_hash) === 40 && ctype_xdigit($stored_hash)) {
      // Legacy verification
      if (sha1($password . $salt) === $stored_hash) {
          // Re-hash with bcrypt and update DB
          $new_hash = password_hash($password, PASSWORD_DEFAULT);
          $this->db->update('users', ['password' => $new_hash, 'salt' => null], ['id' => $id]);
          return true;
      }
      return false;
  }
  // Modern verification
  return password_verify($password, $stored_hash);
  ```
- [ ] Test login with existing SHA1-hashed user
- [ ] Verify DB shows bcrypt hash after login (starts with `$2y$`)
- [ ] Test new user registration creates bcrypt hash

### Fix 6b: Session Integrity (MD5 → HMAC)

- [ ] Verify CI 3.1.13 (Phase 1) already fixed this:
  ```
  grep -n "md5.*session\|session.*md5" system/codeigniter/libraries/Session/
  ```
- [ ] **If already fixed by Phase 1:** Mark as done
- [ ] **If NOT fixed:** Replace `md5()` with `hash_hmac('sha256', ...)` in Session_cookie.php

## Verification

- [ ] Existing users can log in (SHA1 hash verified, then re-hashed)
- [ ] After login, password hash in DB starts with `$2y$`
- [ ] New user registration creates bcrypt hash
- [ ] Password column is VARCHAR(255)
- [ ] No session integrity errors in error log
- [ ] Run `php tests/route_test.php --compare-baseline` — zero regressions
