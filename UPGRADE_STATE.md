# PyroCMS Pro 2.2.4 → PHP 8.2 + MySQL 8.4 — Current State

**Status:** Code-side upgrade complete. Running clean on **PHP 8.2.30 + CI 3.1.13 + MySQL 5.7**. Login works, admin dashboard loads, 46/46 routes green, zero regressions vs. baseline.

**Plan reference:** `docs/upgrade/00-overview.md` and phase docs `01-…` through `08-…`. The written plan is authoritative for *intent*; this file is the *execution log*.

**Workspace URL:** http://pyrocmspro2ng.test/ — isolated on Herd to PHP 8.2 (`ISOLATED_PHP_VERSION=8.2` in `~/Library/Application Support/Herd/config/valet/Nginx/pyrocmspro2ng.test`). A symlink `~/Library/Application Support/Herd/config/valet/Sites/pyrocmspro2ng` → the project root was needed for Valet's server.php to resolve the site by name.

**DB:** `pyrocmspro2ng` on local MySQL 5.7 (not yet 8.4). Seven per-site prefixes: `bockavel_`, `core_`, `default_`, `incore_`, `jagarhjalpen_`, `justwood_`, `lot_gardsmejeri_`.

**Gitignored local files edited (not in git):**
`CLAUDE.md`, `index.php`, `system/cms/config/database.php`.

**Use Herd MCP to change PHP versions** (memory: `~/.claude/projects/-Users-incore--polyscope-clones-c1d6b301-happy-hedgehog/memory/herd_php_version.md`). Don't poke FPM with kill/HUP — it kills the master and `herd82.sock` orphans; `install_php_version` / `isolate_or_unisolate_site` is the safe restart path.

---

## Phase-by-phase completion

| Phase | Status | Notes |
|-------|--------|-------|
| **0 — Test suite** | ✅ | `tests/route_test.php` + committed `tests/route_baseline.json`. Probes 46 routes, accepts 302|307 for redirects. Usage: `php tests/route_test.php http://pyrocmspro2ng.test --compare-baseline`. |
| **1 — CI 3.0-dev → 3.1.13** | ✅ | Wholesale swap of `system/codeigniter/{core,database,helpers,language,libraries}`. Downloaded from `github.com/bcit-ci/CodeIgniter/archive/refs/tags/3.1.13.tar.gz`. |
| **2 — Lex template engine** | ✅ | Six fixes in `system/cms/libraries/{Lex/Parser.php,MX/Loader.php,MY_Parser.php}`. Fix 2a later broadened to always consume the tag (arrays/objects/null → `''`). |
| **3 — PyroCMS core blockers** | ✅ | `search_m.php` (mysql_* → `$this->CI->db->escape_str`), `MX/Modules.php:80` (each()), `Markdown_parser.php` (constructor + create_function + `{0}` → `[0]`), `Textile.php`, `field.choice.php`, `MY_date_helper.php` (new `_pyro_strftime_compat()` helper replaces strftime+utf8_encode), `MY_inflector_helper.php`. |
| **4 — Third-party libs** | ✅ | Cloudmanic Storage (s3.php + cloudfiles_http.php: `array(&$this, …)` → `array($this, …)`, `$value{0}` → `$value[0]`). Backups module (aws.php + backup_m.php utf8_decode → mb_convert_encoding). **Simplepie deferred** — 12.9k-line lib only loaded by the RSS widget; leave until widget is actually placed. |
| **5 — Rector / PHPCompatibility sweep** | ⏸ **pending** | Not started. Optional — runtime is clean for the 46 routes. Would catch deeper deprecations (dynamic property creation warnings are still noisy in the log but non-fatal). |
| **6 — Password hash migration** | ✅ code / ✅ verified live | `hash_password()` returns bcrypt. `hash_password_db()` is dual-mode: detects 40-char-hex SHA1, verifies via `sha1($password . $stored_salt)` (store_salt=true scheme — confirmed for all 231 existing users), on success rewrites `password = password_hash(PASSWORD_DEFAULT)` and clears `salt = ''`. Also replaced the forgotten-password key generator with `bin2hex(random_bytes(20))` (bcrypt's `$`/`/` break URL routing). `password` columns are already `VARCHAR(255)`; `salt` is `VARCHAR(6) NOT NULL` (hence `''` not `NULL`). Verified: marcus@incore.se (bockavel_users id=1) now has `$2y$10$…` hash and empty salt after successful login. |
| **M — MySQL code-side** | ✅ | M1 GROUP BY fixes (`user_m.php`, `file_m.php`, `pyroforms/admin.php`). M2 utf8→utf8mb4 in `database.php` + 9 migration/detail files + latin1 replacements in pyroforms/backups. M3 TEXT prefix dropped from UNIQUE index. M4 MyISAM→InnoDB (5 files + `libraries/Module.php` dynamic ALTER). M5 INT display widths stripped. |
| **M — MySQL server-side** | ⏸ **pending** | See `README.md` for exact SQL to run: `ALTER DATABASE … utf8mb4`, per-table charset/engine conversion, then install MySQL 8.4.2 and re-import. **Password column widening is NOT required** — already 255 on every user table. |

---

## PHP 8.2 runtime issues discovered & fixed (beyond the plan)

Everything below emerged while flipping to 8.2 and was outside the original plan docs.

1. **`MY_Config::site_url()` signature mismatch** — CI 3.1 added `$protocol = null`. `system/cms/core/MY_Config.php:18`.
2. **`MY_Lang::line()` signature mismatch** — CI 3.1 added `$log_errors = TRUE`. `system/cms/core/MY_Lang.php:23`.
3. **`substr(null, …)` in constants** — cast to `(string)`. `system/cms/config/constants.php:132`.
4. **CI 3.1 error view layout** — CI now requires `views/errors/{html,cli}/` subdirs. Created both in `system/cms/views/errors/` (html dir copies the existing PyroCMS error views; cli dir is CI 3.1 stock).
5. **MX_Router default-controller lookup** — CI 3.1's `_set_default_controller()` only checks `APPPATH/controllers/`, not module controllers. Overridden in `system/cms/libraries/MX/Router.php` to delegate to `$this->locate()`.
6. **MX_Loader `_ci_load_class` renamed** — CI 3.1 renamed to `_ci_load_library` and swapped `_ci_classes` semantics (key is now property name, value is class name). `library()` method rewritten. `_ci_object_to_array` → `_ci_prepare_view_vars` too.
7. **Streams driver names had parent prefix** — CI 3.1 driver library expects bare names. `system/cms/libraries/Streams/Streams.php`: `'streams_entries'` → `'entries'` etc.
8. **Session collision — root cause + cookie-name mismatch** — pick_language hook opened a native PHP session as `PHPSESSID`; CI's later `session_start()` under `pyrocms_development` inherited the internal session ID from pick_language, which PHP's `use_strict_mode` rejected against the new file prefix → new session created **every request** → login state silently lost. Fix in `system/cms/hooks/pick_language.php`: call `session_name($ci_sess_name)` (and `session_id(…)` for HTTPS-admin flow) **before** `session_start()`; `session_write_close()` after so CI can reopen cleanly.
9. **Session storage config** — switched from DB sessions (CI 2.x schema mismatch: legacy tables use `session_id`, `user_agent`, `last_activity`, `user_data`; CI 3.1 wants `id`, `timestamp`, `data`) to file sessions in `system/cms/cache/sessions/` (gitignored). Disabled `sess_match_ip` + `sess_match_useragent` (they cause churn when hook-session and CI-session hand off). `sess_expiration = 7200`, `sess_expire_on_close = false`.
10. **`base_url` fell back to `SERVER_ADDR`** — CI 3.1 auto-fill uses `$_SERVER['SERVER_ADDR']` (127.0.0.1) when `$config['base_url']` is empty. Set it from `$_SERVER['HTTP_HOST']` in `system/cms/config/config.php:17`.
11. **Streams_parse / snippets/plugin null-method_exists** — `method_exists($this->...->{$type}, '…')` fatals on PHP 8 when the object is null. Added `isset(...)` guards in both files.
12. **`directory_map()` trailing slash** — CI 3.1 returns `wysiwyg/` etc.; `snippets_m::load_snippets()` was building `.../snippets/html//snip.html/.php`, so no snippet type was ever registered — that was why footer snippets rendered as HTML-escaped raw source (`pre_output()` never ran and the content decode was skipped). Fix: `rtrim($folder, '/')` in `addons/shared_addons/modules/snippets/models/snippets_m.php`.
13. **Subnav plugin `count(null)`** — `count((array) $this->getParent(...))` in `addons/bockavel/plugins/subnav.php`.
14. **Missing swedish CI language files** — `system/codeigniter/language/swedish/` created by copying from english. Admin caused a 500 on `form_validation_lang.php` otherwise.
15. **302 → 307 redirects** — CI 3.1's `redirect()` now issues 307 by default. Route test expectations accept both.

---

## How to verify

```bash
php tests/route_test.php http://pyrocmspro2ng.test --compare-baseline
# expect: Failures: 0   Regressions: 0
```

Admin login check (interactive, since CSRF-less but form-driven):

1. Visit http://pyrocmspro2ng.test/admin/login
2. Log in with a real user on `bockavel_users`. First login on an unmigrated SHA1 user will transparently rehash to bcrypt.
3. After login, `/admin` should render the dashboard (title "Dashboard - Control Panel").

Inspect the session file (proves the full-roundtrip is persisting):

```bash
ls system/cms/cache/sessions/
cat system/cms/cache/sessions/pyrocms_development*
# expect a single file containing: __ci_last_regenerate|…email|s:…marcus@incore.se…user_id|s:"1"…group|s:"admin"…
```

---

## Things to watch / known friction

- **Deprecation log is noisy but harmless.** `system/cms/logs/log-2026-*.php` fills with `Creation of dynamic property …` warnings on every request. These don't fatal; they'll need Phase 5 (Rector or manual property declarations on `CI_Controller`/`MX_Controller` subclasses) to silence.
- **Simplepie deferred.** If anyone places the RSS widget, the Simplepie library (~12.9k lines) still has mysql_* calls + strftime. Plan says replace with SimplePie 1.8.x drop-in if used.
- **`index.php` has `display_errors = false` again** (set by the env switch). For live debugging, flip it temporarily in `index.php:56`.
- **Herd MCP quirks.** `install_php_version` / `isolate_or_unisolate_site` don't always kick FPM back up cleanly; if `get_all_php_versions` shows status=`error`, remove the orphaned sock (`~/Library/Application Support/Herd/herd82.sock`) and toggle isolation off→on.
- **Valet site-name lookup.** The project lives in a dir called `happy-hedgehog`; `pyrocmspro2ng.test` resolves only because a symlink was added under `~/Library/Application Support/Herd/config/valet/Sites/pyrocmspro2ng`. If you clone elsewhere, add that symlink again or rename the dir to match the desired host.

---

## Files worth knowing when continuing

- **`docs/upgrade/`** — the authoritative plan.
- **`tests/route_test.php`** + **`tests/route_baseline.json`** — the regression gate.
- **`README.md`** — remaining DB-side SQL for Phase M and Phase 6.
- **`system/cms/config/config.php`** — session, base_url, cookie, hook config all live here.
- **`system/cms/config/database.php`** *(gitignored)* — dev+prod DB credentials; charset is already `utf8mb4`.
- **`system/cms/hooks/pick_language.php`** — session bootstrap interplay with CI 3.1.
- **`system/cms/libraries/MX/{Router,Loader,Modules}.php`** — the CI 3.1 compat glue.
- **`system/cms/modules/users/models/ion_auth_model.php`** — dual-mode bcrypt migration.

---

## Suggested next steps (in order of impact)

1. **DB-side Phase M.** Run the SQL in `README.md` against the 5.7 instance; then install MySQL 8.4.2; then `php tests/route_test.php … --compare-baseline`.
2. **Admin smoke.** Click around `/admin/pages`, `/admin/blog`, `/admin/files`, `/admin/settings`. Fix whatever crashes the first time you try a CRUD (likely more `method_exists(null, …)` or dynamic-property warnings turning into fatals in untested code paths).
3. **Phase 5 Rector sweep** — only if admin smoke reveals widespread issues. Run dry-run first; commit only per-file reviewed diffs. Don't let Rector touch `system/codeigniter/` (vendor code) or `system/cms/libraries/Simplepie.php` or `Textile.php`.
4. **Silence dynamic-property deprecations** (optional) by annotating `CI`, `MX_Controller`, `MY_Controller`, `CI_DB_forge`, etc. with `#[AllowDynamicProperties]` or declaring the properties. Bulk.
5. **Re-enable DB sessions** *only if you need shared sessions across workers* — requires migrating `*_ci_sessions` tables to CI 3.1's `id/ip_address/timestamp/data` schema.
