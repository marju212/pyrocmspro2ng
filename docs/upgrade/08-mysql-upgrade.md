# Phase M: MySQL 5.7 → 8.4.2

**Status:** Not started
**Prerequisites:** Phase 0 baseline saved (can run parallel with PHP phases)
**Effort:** 1-2 days | **Risk:** Medium

---

## Breaking Changes Summary

| Issue | Severity | Count |
|-------|----------|-------|
| ONLY_FULL_GROUP_BY default on | CRITICAL | 3 queries |
| utf8 → utf8mb4 charset change | CRITICAL | 15+ files |
| TEXT prefix in UNIQUE index | HIGH | 3 locations |
| MyISAM → InnoDB | HIGH | 13 locations |
| INT display width deprecated | MEDIUM | 20+ locations |
| caching_sha2_password auth | MEDIUM | config |

## Implementation Tasks

### Pre-flight

- [ ] Dump database backup:
  ```bash
  mysqldump -u root pyrocmspro2 > backup_before_mysql8.sql
  ```

---

### Fix M1: ONLY_FULL_GROUP_BY — 3 query failures (CRITICAL)

**Query 1: user_m.php**
- [ ] Edit `system/cms/modules/users/models/user_m.php:77-80`
  Remove `->group_by('users.id')` (unnecessary — each user has exactly one profile and group):
  ```php
  ->select($this->profile_table.'.*, g.description as group_name, users.*')
  ->join('groups g', 'g.id = users.group_id')
  ->join('profiles', 'profiles.user_id = users.id', 'left');
  ```

**Query 2: file_m.php**
- [ ] Edit `system/cms/modules/files/models/file_m.php:47-51`
  Replace `group_by` with `distinct`:
  ```php
  ->distinct()
  ->join('keywords_applied', 'keywords_applied.hash = files.keywords')
  ->join('keywords', 'keywords.id = keywords_applied.keyword_id')
  ->where_in('keywords.name', $tags);
  ```

**Query 3: pyroforms admin.php**
- [ ] Edit `addons/shared_addons/modules/pyroforms/controllers/admin.php:130-133`
  List columns explicitly instead of `f.*`:
  ```php
  ->select('f.id, f.name, f.slug, f.email, f.success_message, f.active, IFNULL(COUNT(e.id), 0) AS entry_count', FALSE)
  ->from('pyroforms f')
  ->join('pyroforms_entry e', 'f.id = e.form_id', 'left')
  ->group_by('f.id, f.name, f.slug, f.email, f.success_message, f.active')
  ```

---

### Fix M2: utf8 → utf8mb4 (CRITICAL)

**Step 1: Update config**
- [ ] Edit `system/cms/config/database.php` — development array (lines 46-47):
  ```php
  'char_set' => 'utf8mb4',
  'dbcollat' => 'utf8mb4_unicode_ci',
  ```
- [ ] Edit `system/cms/config/database.php` — production array (lines 82-83): same change

**Step 2: Convert existing database (run on MySQL 5.7 before switching)**
- [ ] Run: `ALTER DATABASE pyrocmspro2 CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;`
- [ ] Generate and run table ALTER statements:
  ```sql
  SELECT CONCAT('ALTER TABLE `', TABLE_NAME, '` CONVERT TO CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;')
  FROM information_schema.TABLES
  WHERE TABLE_SCHEMA = 'pyrocmspro2' AND TABLE_TYPE = 'BASE TABLE';
  ```

**Step 3: Update migration files (search-replace `utf8_unicode_ci` → `utf8mb4_unicode_ci`, `CHARSET=utf8` → `CHARSET=utf8mb4`)**
- [ ] `system/cms/migrations/039_Add_contact_log.php`
- [ ] `system/cms/migrations/041_Add_keywords.php`
- [ ] `system/cms/migrations/046_Changed_user_salt_length.php`
- [ ] `system/cms/migrations/047_Add_markdown_support.php`
- [ ] `system/cms/migrations/054_Comment_parsed_default.php`
- [ ] `system/cms/migrations/068_Add_streams.php`
- [ ] `system/cms/migrations/098_Add_populate_search_index.php`
- [ ] `system/cms/modules/search/details.php`
- [ ] `addons/shared_addons/modules/snippets/details.php`

**Step 4: Fix latin1 tables**
- [ ] Edit `addons/shared_addons/modules/pyroforms/details.php` — change `CHARSET=latin1` → `CHARSET=utf8mb4 COLLATE utf8mb4_unicode_ci` (lines 62, 82, 102)
- [ ] Edit `addons/shared_addons/modules/backups/details.php` — same change (lines 72, 87)

---

### Fix M3: TEXT prefix in UNIQUE index (HIGH)

- [ ] Edit `system/cms/modules/search/details.php:67` — change `entry_id` from TEXT to VARCHAR(255), remove `(190)` prefix:
  ```sql
  UNIQUE KEY `unique` (`module`,`entry_key`,`entry_id`)
  ```
- [ ] Edit `system/cms/migrations/098_Add_populate_search_index.php:94,107` — same: change column to VARCHAR(255), remove prefix
- [ ] If Simplepie is kept (Phase 4): fix `UNIQUE (id(125))` in `Simplepie.php:319`

---

### Fix M4: MyISAM → InnoDB (HIGH)

**Code changes (change `ENGINE=MyISAM` → `ENGINE=InnoDB`):**
- [ ] `system/cms/config/rest.php` (lines 148, 211, 246)
- [ ] `system/cms/migrations/098_Add_populate_search_index.php` (line 109)
- [ ] `system/cms/modules/search/details.php` (line 69)
- [ ] `system/cms/modules/sites/models/user_m.php` (line 137)
- [ ] `system/cms/modules/sites/libraries/Module_import.php` (lines 99, 114)
- [ ] `system/cms/libraries/Module.php` (line 259) — dynamic ALTER TABLE
- [ ] `addons/shared_addons/modules/snippets/details.php` (line 114)
- [ ] `addons/shared_addons/modules/pyroforms/details.php` (lines 62, 82, 102)

**Database conversion (run on MySQL 5.7 before switching):**
- [ ] Generate and run ALTER statements:
  ```sql
  SELECT CONCAT('ALTER TABLE `', TABLE_NAME, '` ENGINE=InnoDB;')
  FROM information_schema.TABLES
  WHERE TABLE_SCHEMA = 'pyrocmspro2' AND ENGINE = 'MyISAM';
  ```

---

### Fix M5: INT display width (MEDIUM)

Not breaking but generates deprecation warnings. Change `INT(11)`, `INT(7)`, etc. to `INT`:

- [ ] `system/cms/migrations/068_Add_streams.php`
- [ ] `system/cms/migrations/098_Add_populate_search_index.php`
- [ ] `system/cms/migrations/039_Add_contact_log.php`
- [ ] `system/cms/modules/streams_core/config/streams.php` (remove `'constraint' => 11` for INT type)
- [ ] `addons/shared_addons/modules/pyroforms/details.php`
- [ ] `addons/shared_addons/modules/snippets/details.php`
- [ ] `addons/shared_addons/modules/backups/details.php`

---

### Fix M6: Authentication plugin (MEDIUM)

- [ ] After MySQL 8.4 install, verify auth plugin:
  ```sql
  SELECT user, host, plugin FROM mysql.user WHERE user = 'forge';
  ```
- [ ] If `caching_sha2_password` — PHP 8.2 supports it natively, no action needed
- [ ] If connection fails — fallback:
  ```sql
  ALTER USER 'forge'@'localhost' IDENTIFIED WITH mysql_native_password BY 'password';
  ```

---

### Switch to MySQL 8.4

- [ ] Install MySQL 8.4.2
- [ ] Import/upgrade database
- [ ] Verify connection from PHP works

---

## Verification

- [ ] Admin user listing works (GROUP BY fix)
- [ ] File listing by tags works (GROUP BY fix)
- [ ] Pyroforms admin list works (GROUP BY fix)
- [ ] Search module works (UNIQUE index + InnoDB + utf8mb4)
- [ ] All JOINs work — no `Illegal mix of collations` errors
- [ ] Database dump/restore works cleanly
- [ ] No INT display width warnings in MySQL error log
- [ ] Run `php tests/route_test.php --compare-baseline` — zero regressions
