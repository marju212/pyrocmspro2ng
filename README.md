# Pyrocmspro

PyroCMS Pro 2.2.4 — upgrade to PHP 8.2 + MySQL 8.4.

See `docs/upgrade/` for the full plan. The code-side migration is complete. The
DB-side work below still has to be run against the live database, then the
server flipped to MySQL 8.4.2.

All SQL targets the `pyrocmspro2ng` database. Back up first:

```bash
mysqldump -u root pyrocmspro2ng > backup_before_mysql8.sql
```

## Phase M — MySQL 5.7 → 8.4.2

### M2: Convert character set to utf8mb4

```sql
ALTER DATABASE `pyrocmspro2ng`
    CHARACTER SET utf8mb4
    COLLATE utf8mb4_unicode_ci;
```

Convert every existing table (generates and runs the ALTERs):

```sql
-- Generate:
SELECT CONCAT(
    'ALTER TABLE `', TABLE_NAME,
    '` CONVERT TO CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;'
) AS stmt
FROM information_schema.TABLES
WHERE TABLE_SCHEMA = 'pyrocmspro2ng'
  AND TABLE_TYPE = 'BASE TABLE';

-- Run the output, then verify:
SELECT COUNT(*) AS non_utf8mb4
FROM information_schema.TABLES
WHERE TABLE_SCHEMA = 'pyrocmspro2ng'
  AND TABLE_COLLATION NOT LIKE 'utf8mb4%';
-- Expected: 0
```

### M3: Drop TEXT prefix from UNIQUE index (search_index)

Already handled in code (`entry_id` changed from `TEXT` → `VARCHAR(255)`), but
the live tables need to catch up. The install re-runs migration 098 on fresh
databases; for an already-installed site, apply per-prefix:

```sql
-- Repeat for each site prefix that actually has a search_index table.
-- The current DB has: bockavel_, default_, incore_, jagarhjalpen_,
-- justwood_, lot_gardsmejeri_.
ALTER TABLE `default_search_index`
    MODIFY `entry_id` VARCHAR(255) DEFAULT NULL,
    DROP INDEX `unique`,
    ADD UNIQUE KEY `unique` (`module`, `entry_key`, `entry_id`);
```

### M4: MyISAM → InnoDB

```sql
-- Generate (40 tables in the current DB):
SELECT CONCAT('ALTER TABLE `', TABLE_NAME, '` ENGINE=InnoDB;') AS stmt
FROM information_schema.TABLES
WHERE TABLE_SCHEMA = 'pyrocmspro2ng'
  AND ENGINE = 'MyISAM';

-- Run the output, then verify:
SELECT COUNT(*) AS remaining_myisam
FROM information_schema.TABLES
WHERE TABLE_SCHEMA = 'pyrocmspro2ng'
  AND ENGINE = 'MyISAM';
-- Expected: 0
```

### M5: Drop INT display widths

MySQL 8 deprecates `INT(11)` etc. — the warnings are harmless. Tables created
after the code changes will already be clean. For pre-existing tables the
conversion is table-by-table; skip unless the error log complains:

```sql
-- Example — repeat per column as needed.
ALTER TABLE `default_users`
    MODIFY `id` INT UNSIGNED NOT NULL AUTO_INCREMENT;
```

### M6: Switch to MySQL 8.4.2

1. Install MySQL 8.4.2.
2. Restore the (converted) dump.
3. Verify `forge` user's auth plugin is `caching_sha2_password` (PHP 8.2's
   `mysqli` supports it natively). Fallback if needed:
   ```sql
   ALTER USER 'forge'@'localhost' IDENTIFIED WITH mysql_native_password BY '<password>';
   ```
4. Run `php tests/route_test.php http://pyrocmspro2ng.test --compare-baseline`
   — expect zero regressions.

## Phase 6 — Password hash migration

The code now returns bcrypt from `hash_password()` and transparently re-hashes
SHA1 → bcrypt on first successful login. Existing sessions keep working.

Schema check (done — **no schema change required**):

| Column                      | Current      | Needed for bcrypt            |
|-----------------------------|--------------|------------------------------|
| `password` (all user tables)| `VARCHAR(255) NOT NULL` | ≥ 60 chars → already fits |
| `salt` (all user tables)    | `VARCHAR(6) NOT NULL`   | 0 chars after rehash → store `''`, which the code already does (NOT NULL means we cannot store `NULL` here) |

All 231 existing users across the 7 site prefixes (`bockavel_`, `core_`,
`default_`, `incore_`, `jagarhjalpen_`, `justwood_`, `lot_gardsmejeri_`) use
the `store_salt=true` scheme from `ion_auth.php`:
`stored_password = sha1(plaintext . salt)`, 40 hex chars, with the salt in the
`salt` column. `hash_password_db()` detects the 40-char hex hash, verifies it
with the SHA1 path, then rewrites the row with `password = password_hash(…,
PASSWORD_DEFAULT)` and `salt = ''`.

Optional — back up the legacy hashes before anyone logs in (so a rollback is
possible):

```sql
CREATE TABLE `password_backup_pre_bcrypt` AS
SELECT 'bockavel'        AS site, id, email, password, salt FROM bockavel_users
UNION ALL
SELECT 'core'            AS site, id, email, password, salt FROM core_users
UNION ALL
SELECT 'default'         AS site, id, email, password, salt FROM default_users
UNION ALL
SELECT 'incore'          AS site, id, email, password, salt FROM incore_users
UNION ALL
SELECT 'jagarhjalpen'    AS site, id, email, password, salt FROM jagarhjalpen_users
UNION ALL
SELECT 'justwood'        AS site, id, email, password, salt FROM justwood_users
UNION ALL
SELECT 'lot_gardsmejeri' AS site, id, email, password, salt FROM lot_gardsmejeri_users;
```

Verification after a user logs in:

```sql
-- Row should now start with $2y$ and have an empty salt.
SELECT id, email, LEFT(password, 4) AS algo, salt
FROM default_users
WHERE email = 'user@example.com';
```
