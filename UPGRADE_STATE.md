# PyroCMS Pro 2.2.4 → PHP 8.2 + MySQL 8.4 — Current State

**Status:** Code-side upgrade complete. Running clean on **PHP 8.2.30 + CI 3.1.13 + MySQL 5.7**. Login works, admin dashboard loads, 46/46 baseline routes green, **authenticated admin smoke pass green** (24 admin URIs verified — see entries 16–17 for the two fatals it surfaced and fixed). **Public-site streams content rendering verified** against the original-site baseline screenshot — slider, news list, article body, sidebars all populate correctly (see entries 18–19). **Files/image serving green** (entry 20). **Admin URL routing green for multi-segment paths** (entry 21 — was silently 404-ing past every "Edit/Manage" link). **Streams admin GUI end-to-end verified**: create stream → create field → assign field → add entry → entry appears in listing (entry 22 fixed the mcrypt-removal blocker on the entries page).

**Plan reference:** `docs/upgrade/00-overview.md` and phase docs `01-…` through `08-…`. The written plan is authoritative for *intent*; this file is the *execution log*.

**Workspace URL:** http://pyrocmspro2ng.test/ — isolated on Herd to PHP 8.2 (`ISOLATED_PHP_VERSION=8.2` in `~/Library/Application Support/Herd/config/valet/Nginx/pyrocmspro2ng.test`). The Valet site symlink `~/Library/Application Support/Herd/config/valet/Sites/pyrocmspro2ng` → `/Users/incore/code/pyrocmspro2ng` was needed for Valet's server.php to resolve the site by name. **Re-pointed 2026-04-17** from a now-removed polyscope clone (`/Users/incore/.polyscope/clones/c1d6b301/happy-hedgehog`) to this repo path — if `pyrocmspro2ng.test` ever serves stale or wrong content, check `readlink ~/Library/Application\ Support/Herd/config/valet/Sites/pyrocmspro2ng` first.

**DB:** `pyrocmspro2ng` on local MySQL 5.7 (not yet 8.4). Seven per-site prefixes: `bockavel_`, `core_`, `default_`, `incore_`, `jagarhjalpen_`, `justwood_`, `lot_gardsmejeri_`.

**Configuration via `.env`** (since 2026-04-17 — see entry 27). `index.php` and `system/cms/config/database.php` now read environment + DB credentials from a project-root `.env` file with hardcoded fallbacks; both files are committed. Copy `.env.example` to `.env` (gitignored) and edit to override defaults. Real environment variables (FPM SetEnv / docker --env / shell export) take precedence over `.env`. **Gitignored local files (still not in git):** `CLAUDE.md`, `.env`.

**Use Herd MCP to change PHP versions** (memory: `~/.claude/projects/-Users-incore--polyscope-clones-c1d6b301-happy-hedgehog/memory/herd_php_version.md`). Don't poke FPM with kill/HUP — it kills the master and `herd82.sock` orphans; `install_php_version` / `isolate_or_unisolate_site` is the safe restart path.

---

## Phase-by-phase completion

| Phase | Status | Notes |
|-------|--------|-------|
| **0 — Test suite** | ✅ | `tests/route_test.php` + committed `tests/route_baseline.json`. Probes 46 routes, accepts 302|307 for redirects. Body scan rejects responses with inline `<h4>A PHP Error was encountered</h4>` blocks (catches new-style PHP 8 warnings, not just status mismatches). Plus `tests/registration_test.php` for the e2e membership signup path. Usage: `php tests/route_test.php http://pyrocmspro2ng.test --compare-baseline`. |
| **1 — CI 3.0-dev → 3.1.13** | ✅ | Wholesale swap of `system/codeigniter/{core,database,helpers,language,libraries}`. Downloaded from `github.com/bcit-ci/CodeIgniter/archive/refs/tags/3.1.13.tar.gz`. |
| **2 — Lex template engine** | ✅ | Six fixes in `system/cms/libraries/{Lex/Parser.php,MX/Loader.php,MY_Parser.php}`. Fix 2a later broadened to always consume the tag (arrays/objects/null → `''`). |
| **3 — PyroCMS core blockers** | ✅ | `search_m.php` (mysql_* → `$this->CI->db->escape_str`), `MX/Modules.php:80` (each()), `Markdown_parser.php` (constructor + create_function + `{0}` → `[0]`), `Textile.php`, `field.choice.php`, `MY_date_helper.php` (new `_pyro_strftime_compat()` helper replaces strftime+utf8_encode), `MY_inflector_helper.php`. |
| **4 — Third-party libs** | ✅ | Cloudmanic Storage (s3.php + cloudfiles_http.php: `array(&$this, …)` → `array($this, …)`, `$value{0}` → `$value[0]`). Backups module (aws.php + backup_m.php utf8_decode → mb_convert_encoding). **Simplepie deferred indefinitely** — RSS widget unused on this site; revisit only if it gets placed. |
| **5 — Rector / PHPCompatibility sweep** | ✅ | Manual pass instead of full Rector run. Two parts: (a) `#[\AllowDynamicProperties]` on every class that PyroCMS attaches loaded libraries / state onto post-construct — `CI`, `MX_Controller`, `MY_Controller`, `MY_Model`, `MX_Loader`, `MX_Router`, `MY_Parser`, `Lex_Parser`, `Plugin`, `Plugins`, `Streams`, `Type`, `Fields`, `Ion_auth`, `Ion_auth_model`, the `Plugin_Streams` plugin class, **and all 25 `Field_*` field-type classes** (none extend a common base, so each gets its own attribute). One vendor file touched: `system/codeigniter/core/URI.php` (CI_URI receives `$config` injection before any MY_URI override could intercept) and `system/codeigniter/libraries/Pagination.php` (`ctype_digit(null)` guard). (b) Null-arg deprecation guards at the call sites: `unserialize(null)` in `streams_core/models/fields_m.php:111`, `strpos(null, …)` in `streams/plugin.php:73`, `str_replace(null, …)` in `MX/Controller.php:48`, `preg_match($pattern, null)`/`addcslashes(null, …)` in `Lex/Parser.php`, `explode('/', null)` in `pages/controllers/Pages.php:130`, `strtotime(null)` in `streams_core/models/row_m.php:1293`, `trim(null)` in `field.datetime.php:199` + `field.relationship.php:52`. Verified by toggling `error_reporting(E_ALL)` and probing 15 admin/public URLs: dynamic-property warnings dropped from **1418 → 0**, all null-arg deprecations gone too. |
| **6 — Password hash migration** | ✅ code / ✅ verified live | `hash_password()` returns bcrypt. `hash_password_db()` is dual-mode: detects 40-char-hex SHA1, verifies via `sha1($password . $stored_salt)` (store_salt=true scheme — confirmed for all 231 existing users), on success rewrites `password = password_hash(PASSWORD_DEFAULT)` and clears `salt = ''`. Also replaced the forgotten-password key generator with `bin2hex(random_bytes(20))` (bcrypt's `$`/`/` break URL routing). `password` columns are already `VARCHAR(255)`; `salt` is `VARCHAR(6) NOT NULL` (hence `''` not `NULL`). Verified: marcus@incore.se (bockavel_users id=1) now has `$2y$10$…` hash; **and the `tests/registration_test.php` e2e proves new registrations also land as bcrypt $2y$**. |
| **M — MySQL code-side** | ✅ | M1 GROUP BY fixes (`user_m.php`, `file_m.php`, `pyroforms/admin.php`). M2 utf8→utf8mb4 in `database.php` + 9 migration/detail files + latin1 replacements in pyroforms/backups. M3 TEXT prefix dropped from UNIQUE index. M4 MyISAM→InnoDB (5 files + `libraries/Module.php` dynamic ALTER). M5 INT display widths stripped. |
| **M — MySQL server-side** | ✅ | MySQL **8.4.2** running, default engine InnoDB, default charset utf8mb4, all bockavel_ tables on InnoDB. 46/46 baseline routes green against 8.4. Two cosmetic follow-ups not blockers: tables are still on `utf8mb3_unicode_ci` (legacy "utf8") — would only matter if you need 4-byte chars (emojis); convert with the per-table `ALTER … CONVERT TO CHARACTER SET utf8mb4` SQL in README.md. And the dead `bockavel_ci_sessions` table is still MyISAM (we run file sessions per entry 9). |

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
16. **Nested ternary without parens** — `$a ? $b : $c ? $d : $e` is a fatal in PHP 8. Hit at `system/cms/modules/files/libraries/files.php:382` (the `name` field for uploaded files). Fixed with `$replace_file ? $replace_file->name : ($name ?: $file['orig_name'])`. Symptom: 500 on `/admin/files` and `/admin/files/upload` — both green now.
17. **`AUTO_LANGUAGE` undefined on 404 paths** — PHP 8 removed bareword-as-string fallback. `AUTO_LANGUAGE` is normally defined by the `pick_language` pre_controller hook (`system/cms/hooks/pick_language.php:137`), but CI 3.x's routing fires `MY_Exceptions::show_404()` *before* pre_controller hooks when the URI doesn't match any controller method. `show_404()` then `Modules::run('pages/_remap', '404')` → loads pages controller → `Public_Controller` → `MY_Controller::__construct` references `AUTO_LANGUAGE` at lines 84/88/123 → fatal under PHP 8. Fixed with a top-of-file `defined('AUTO_LANGUAGE') OR define('AUTO_LANGUAGE', 'en')` in `system/cms/core/MY_Controller.php`, and added a matching `defined() OR` guard at `system/cms/hooks/pick_language.php:137` so the hook doesn't trigger a "Constant already defined" warning on the normal request path when the fallback ran first. Symptom: 500 on any URL whose final segment doesn't resolve to a method (e.g. `/admin/groups/create` when the real method is `add()`). After fix, those URIs serve the legitimate CMS 404 page.
18. **PyroStreams field types silently unloaded → all stream content rendered as empty.** Same root cause as entry 12 (`directory_map()` trailing slash in CI 3.1) but in a different consumer. `system/cms/modules/streams_core/libraries/Type::load_types_from_folder()` was iterating `directory_map($addon_path, 1)` and using each entry — e.g. `"text/"` — verbatim to build `$addon_path.$type.'/field.'.$type.'.php'`, producing `.../text//field.text/.php` (no such file). Result: `$this->type->types` ended up empty, so `row_m::format_column()` hit its "type missing → return null" branch for *every* field, and every stream entry value was nulled before reaching the Lex parser. UI symptom: index page (and every other streams-driven page) rendered empty `<h4>`/`<img src="">`/`<h1></h1>` blocks — slider, latest news, page body, sidebar lists all looked empty even though the iteration count was correct. Fixed with `$type = rtrim($type, '/')` at the top of the loop in `system/cms/modules/streams_core/libraries/Type.php`.
19. **`mktime()` strict-int args** — CI 3.1's `system/codeigniter/helpers/date_helper.php` `mysql_to_unix()` slices a `'YYYY-MM-DD HH:MM:SS'` with `substr()` and passes the resulting *strings* into `mktime()`. PHP 8 enforces strict int types and fatals "Argument #1 ($hour) must be of type int, string given". Hits the moment any datetime field's `pre_output()` runs (so it surfaced *immediately after* the streams field-types fix landed in entry 18 — datetime fields in news/blog/slider all triggered it). Fixed by overriding `mysql_to_unix()` in `system/cms/helpers/MY_date_helper.php` with `(int)` casts on all six args + a `strlen($time) < 14` guard for empty/short inputs.
20. **`/files/large/{filename}` returned `Attempt to assign property "width" on null`** — `Files_front::thumb()` (`system/cms/modules/files/controllers/files_front.php:67`) constructs an ad-hoc `$file` "object" by writing properties on a never-declared variable. PHP 7 silently auto-promoted the first assignment from null to `stdClass`; PHP 8 fatals. The 500 was masked because the controller's exception handler returns `{"status":false,"message":"…","data":""}` JSON with HTTP 200 — so `<img src="…">` got a successful response with `Content-Type: text/html` and 83 bytes of JSON, which browsers render as a broken image icon. Fixed with `$file = new stdClass();` before the property writes. Same edit also fixed an `end(explode('.', $id))` PHP-8.1 deprecation right above (had to materialize the array into `$parts` first because `end()` requires a variable). Verified: 9/9 actually-uploaded images on the index page now serve `image/jpeg|png` with full bytes; the remaining 7 broken-image holders genuinely don't exist on disk in this clone.
21. **CI 3.x `(:any)` only matches a single segment; PyroCMS admin URLs all 404'd past the top level.** In CI 2.x, `(:any)` expanded to `.+` (greedy, includes slashes); in CI 3.1 it expands to `[^/]+` (one URI segment). The global rewriter `$route['admin/([a-zA-Z0-9_-]+)/(:any)'] = '$1/admin/$2'` therefore stopped matching anything with two-or-more trailing segments, so `/admin/streams/manage/5`, `/admin/streams/entries/index/5`, `/admin/blog/edit/3`, `/admin/files/folder/2/9`, etc. all silently fell through to the page-not-found handler. Cosmetically the streams index loaded fine (no trailing segments), so the bug only surfaced the moment you clicked any "Edit/Manage/Entries" button. Fixed by rewriting the admin and api rewriters to use the explicit `(.+)` form in `system/cms/config/routes.php`. The same change probably needs to be audited in per-module routes.php files (`addons`, `blog`, `widgets`, `users`, `pages`, `navigation`, `wysiwyg`) — they all use `(:any)` too, but only the routes that mean "match any sub-path" are affected; the single-segment `(:any)` uses still work.
22. **`/admin/streams/entries/index/{id}` fatalled with "The Encrypt library requires the Mcrypt extension"** — `mcrypt` was removed in PHP 7.2, and CI 3.x's deprecated `CI_Encrypt` library hard-fatals in `__construct()` if the extension is missing (the new `Encryption` library is OpenSSL-based, but PyroCMS still loads the legacy one). PyroCMS calls it from two places that matter on this site: `Streams_cp.php:129` (encrypts the module slug into a JS variable for the drag-to-sort UI on every entries listing page) and `streams_core/controllers/ajax.php:201` (decrypts it on the way back). Fixed by adding `system/cms/libraries/MY_Encrypt.php` — a drop-in OpenSSL replacement (AES-128-CBC with random IV per encryption, base64-wrapped) that exposes the same `encode/decode/get_key/set_key/hash` surface CI_Encrypt did. Doesn't extend CI_Encrypt — parent constructor would fatal first. Confirmed safe on this site because **no streams use the `encrypt` field type** (verified via `SELECT COUNT(*) FROM <prefix>_data_fields WHERE field_type='encrypt'` across all site prefixes), so there is no legacy mcrypt-era ciphertext to migrate. If a future install needs to read old data, add an `encode_from_legacy()` fallback that decrypts the old format.
23. **Dashboard (`/admin`) showed a stack of "Trying to access array offset on value of type bool" warnings.** Under PHP 8 you can't subscript a `bool`. `MY_Controller::__construct()` set `$this->template->module_details = ci()->module_details = $this->module_details = false;` as a default at line 172, then in the dashboard branch (when `! $this->module`) reassigned **only `$this->module_details`** to a defaults array — the `template` and `ci()` copies stayed `false`. Admin theme partials (`themes/admin/views/admin/partials/{header,metadata}.php`) read from the template-bound copy and emit a warning per access. Fixed by mirroring the assignment-chain pattern in the dashboard branch so all three references receive the defaults array.
24. **`explode(): Argument #2 ($string) must be of type string, array given`** in `streams_core/field_types/datetime/field.datetime.php:78`. CI 3.x's `form_validation` library now stores rules as an **array** internally (CI 2.x stored them as the pipe-delimited string the caller passed). The datetime field's `validate()` did `explode('|', $field_data['rules'])` assuming string, and PHP 8's strict-typed `explode()` fatals on the array form. Symptom: opening *any* admin "add entry" form for a stream that has a datetime field threw an uncaught TypeError (e.g. `/admin/streams/entries/add/6` for the Kalender stream). Fixed with `is_array($rules) ? $rules : explode('|', (string) $rules)`. Grep confirmed datetime is the only field type with this pattern.

25. **PHP 8 dynamic-property deprecations were filling the log.** ~1400 entries per noisy request batch — every `Creation of dynamic property X::$y is deprecated`. Fixed by adding `#[\AllowDynamicProperties]` to the PyroCMS-side classes that PyroCMS attaches loaded libraries / state onto post-construct (the framework "super object" pattern), plus a single attribute on `CI_URI` (the only vendor file we touched, because `$config` is injected before any MY_URI override could intercept). 25 `Field_*` classes annotated via a perl one-liner since they don't extend a common base. Also fixed the orthogonal "passing null to scalar function param" deprecations at the call sites — `unserialize(null)`, `strpos(null,…)`, `str_replace(null,…)`, `explode(/,null)`, `strtotime(null)`, `trim(null)` in 7+ spots. Verified by toggling `error_reporting(E_ALL)` and sweeping 15 admin/public URLs: 1418 → 0 deprecation entries.
26. **`/admin/blog/edit/{id}` fataled when the post didn't exist** (`Attempt to read property "status" on null` at `system/cms/modules/blog/controllers/admin.php:287`). PHP 7 silently returned null; PHP 8 fatals. Added a `if ( ! $post) { redirect with flash; }` guard. Same pattern likely exists across other admin module `edit($id)` entrypoints — left for follow-up smoke if you want to harden them all defensively.
27. **`.env`-driven environment + DB credentials.** PyroCMS 2.x predates composer, so we ship a tiny no-dependency dotenv loader at `system/cms/bootstrap/env.php` (~100-line parser, supports `KEY=value`, `# comments`, `'…'` / `"…"` quoting with `\n \r \t \" \\` escapes inside double quotes, leading `export ` for bash compatibility). The loader is required at the very top of `index.php` and populates `$_SERVER` / `$_ENV` / `getenv()` so any of CI's lookup conventions work — and **real environment variables always win** over `.env`, so staging / production can either keep a server-local `.env` or set vars via FPM `SetEnv` / docker `--env` / shell `export`. Two helpers exposed: `pyro_env($key, $default)` (string-or-fallback) and `pyro_env_bool($key, $default)` (bool coercion for `1/true/on/yes`). Surface:
    - **`PYRO_ENV`** — application environment name (`development` / `staging` / `production`). Drives which CI env config group is active *and* the `APP_DEBUG` default.
    - **`APP_DEBUG`** *(bool, optional)* — single knob that toggles both `display_errors` and `error_reporting`. On → `display_errors=on`, `error_reporting = E_ALL ^ E_DEPRECATED`. Off → `display_errors=off`, `error_reporting=0`. When unset it auto-derives: `development` → on, anything else → off. You rarely need to set it; override only when you want non-obvious behaviour (e.g. temporarily debug on staging).
    - **`APP_ERROR_LOG`** — path to file for `error_log()`; relative to project root or absolute. Independent of `APP_DEBUG` — you typically want this on everywhere, pointed at a different path per env.
    - **`DB_HOST`**, **`DB_PORT`**, **`DB_USER`**, **`DB_PASS`**, **`DB_NAME`**, **`DB_DRIVER`**, **`DB_PREFIX`**, **`DB_CHARSET`**, **`DB_COLLATION`** — read by `system/cms/config/database.php`.

    All keys have hardcoded fallbacks in their respective config files, so a fresh checkout boots without an `.env`; verified by temporarily moving `.env` aside and re-running the route baseline (46/46 pass) and the registration e2e (PASS), and by flipping `APP_DEBUG=false` to confirm the non-debug path also serves cleanly. With debug knobs out of `index.php`, both `index.php` and `database.php` are now committed; `.gitignore` drops their entries and adds `.env` / `.env.local` / `.env.*.local`. Two committed templates: **`.env.development.example`** (ready-to-copy for local Herd/Valet/MAMP-style installs) and **`.env.production.example`** (placeholder secrets + deploy checklist comments). On prod prefer real env vars for the secrets (FPM `env[DB_PASS]`, nginx `fastcgi_param`, Apache `SetEnv`, docker `--env`) — they override `.env` so you can keep non-secret knobs in a file and pull credentials from the real env.

**Comprehensive end-to-end stream workflow verified through admin GUI on 2026-04-17.** Built a fresh test stream `ctest_*` with **6 different field types** — text, textarea, datetime, slug, choice (with dropdown options), image — each as separate POSTs to `/admin/streams/fields/add` and `/admin/streams/new_assignment/{id}`. Physical columns came back with the right MySQL types (`varchar(120)`, `longtext`, `date`, `varchar(255)`, `varchar(255) DEFAULT 'draft'`, `char(15)`). Then exercised full **CRUD on entries** via admin form POSTs:
- **Add (3 entries)** — full payload with multipart image upload, minimal payload, explicit-slug payload
- **Edit** — re-submitted entry 1 with all fields changed; row updated, `updated` timestamp set
- **Delete** — entry 2 removed
- **Image upload roundtrip** — `bockavel_files` row created, file written to `uploads/bockavel/files/`, served back via `/files/large/{hash}` as proper `image/jpeg`

Slug field stayed NULL when the form was POSTed without a value: that's expected, not a bug — `field.slug.php::form_output()` only emits a JS snippet (`pyro.generate_slug()`) that fills the slug client-side from the title field as the user types; with no JS, no server-side fallback. Submit an explicit slug or use the GUI to get one.

**Relationship field type also verified.** Created a "categories" target stream (id=16) with 3 entries (News/Events/Reviews), added a `relationship` field to the test stream pointing at id=16, picked one in the admin entry form, and confirmed end-to-end: physical column came back as `int`, DB stores the bare target id, the `pre_output_plugin` callback returns the **fully formatted joined row** through the streams entries API (driven by a CLI test that boots `index.php` and calls `$CI->streams->entries->get_entries(...)`), so `$entry['ctest_category_*']` is an array containing the related row's columns and `{{ relfield:related_col }}` resolves through Lex's `:` scope_glue. The dropdown displays target rows by their numeric id rather than a label because the target stream's `title_column` wasn't configured — a config gap, not a bug.

Admin sweep of `/admin`, `/admin/pages|blog|files|users|settings|streams|streams/manage/5|streams/entries/index/5|streams/entries/add/{5,6,7,14,15}|streams/entries/edit/15/1` plus the public homepage all return 200 with **zero inline PHP warnings** in the rendered HTML. 46/46 baseline routes still green, 0 regressions.

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

Authenticated admin smoke (curl, no browser needed) — confirms 24 admin URIs return 200 and no others fatal:

```bash
# 1. log in (asks for password interactively if not given)
curl -s -c /tmp/pyro_cookies.txt -b /tmp/pyro_cookies.txt -L -X POST \
     -d 'email=marcus%40incore.se' --data-urlencode "password=$ADMIN_PASS" \
     --data-urlencode 'remember=1' --data-urlencode 'submit=Log In' \
     -o /dev/null http://pyrocmspro2ng.test/admin/login

# 2. probe common admin URIs; anything 500 needs triage
for u in /admin /admin/pages /admin/blog /admin/blog/create /admin/blog/categories \
         /admin/files /admin/files/upload /admin/users /admin/users/create \
         /admin/groups /admin/groups/add /admin/settings /admin/widgets \
         /admin/widgets/areas /admin/navigation /admin/comments /admin/templates \
         /admin/templates/create /admin/variables /admin/variables/create \
         /admin/redirects /admin/redirects/add /admin/keywords /admin/streams \
         /admin/addons /admin/ad /admin/member /admin/maintenance; do
  printf "%-40s %s\n" "$u" \
    "$(curl -s -o /dev/null -w '%{http_code}' -b /tmp/pyro_cookies.txt \
       -c /tmp/pyro_cookies.txt --max-redirs 0 http://pyrocmspro2ng.test$u)"
done
# expect: only 200 / 307; no 500 anywhere.
```

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
- **`index.php` currently has `display_errors = true`** + `log_errors = true` writing to `system/cms/logs/php-fatals.log` (gitignored), set during the smoke pass to surface fatals that PyroCMS's own logger misses. Flip back to `false` before any production-like demo. The full block is at `index.php:54-58`.
- **Fatals don't always reach PyroCMS's `system/cms/logs/`.** PHP fatal errors (Compile/Runtime errors that abort the script) bypass CI's `log_message()` because the script never reaches the shutdown handler. They land in the file pointed to by PHP's `error_log` ini — that's why we set it explicitly in `index.php`. FPM's `php-fpm.log` and nginx's `nginx-error.log` won't show them either unless `catch_workers_output = yes` is set in `8.2-fpm.conf` (it isn't, by default in Herd).
- **PHP opcache delays edits by ~2 seconds.** `opcache.revalidate_freq=2` on Herd's PHP 8.2. After editing a file, `sleep 3` before re-curling, otherwise the FPM workers will still execute the previous bytecode and you'll waste 10 minutes wondering why nothing changed. (Lesson from the 2026-04-17 session.)
- **Herd MCP quirks.** `install_php_version` / `isolate_or_unisolate_site` don't always kick FPM back up cleanly; if `get_all_php_versions` shows status=`error`, remove the orphaned sock (`~/Library/Application Support/Herd/herd82.sock`) and toggle isolation off→on.
- **Valet site-name lookup.** `pyrocmspro2ng.test` resolves only because of the symlink at `~/Library/Application Support/Herd/config/valet/Sites/pyrocmspro2ng → /Users/incore/code/pyrocmspro2ng`. If the site ever serves stale content or 404s where it shouldn't, `readlink` that path first — earlier sessions had it pointing at a polyscope clone that got deleted out from under FPM.

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

1. **utf8mb3 → utf8mb4 table conversion** *(optional cosmetic)*. MySQL 8.4 already runs the site fine; existing tables on `utf8mb3_unicode_ci` only need converting if you want full 4-byte UTF-8 (emojis etc.). README.md has the per-table `ALTER … CONVERT TO CHARACTER SET utf8mb4` SQL.
2. **Browser-driven smoke via Playwright MCP** — drive the actual admin UI through real interactions (clicks, form submits, file pickers). Catches JS errors and CSRF/multipart issues that curl misses. The MCP is already added (`claude mcp list` shows it Connected); needs a Claude Code restart for the tools to surface in-session.
3. **Defensive `null`-guards on the rest of the admin `edit($id)` controllers.** Entry 26 added one to `blog/admin.php`; the same shape likely lives in `pages/admin.php`, `users/admin.php`, `files/admin.php`, etc. Easy bulk pass — `grep -rn "$this->.*_m->get(\\\$id);" system/cms/modules/*/controllers/admin.php` then add `if ( ! $row) redirect(...);` after each.
4. **Re-enable DB sessions** *only if you need shared sessions across workers* — requires migrating `*_ci_sessions` tables to CI 3.1's `id/ip_address/timestamp/data` schema.
5. **Push remote uploads to git LFS** — GitHub flagged the 59 MB `.mp4` in `uploads/bockavel/files/` during the first push. Either move large media to LFS or `.gitignore` `uploads/` and put the assets somewhere else (S3 / object store).
