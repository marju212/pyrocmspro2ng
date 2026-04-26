# WYSIWYG cleanup script

A one-shot CLI that strips legacy CKEditor inline typography (font-size,
font-family, color, line-height, `<font>`, `<center>`, Word `Mso*` classes,
`<o:p>`, etc.) from rich-text page bodies stored across every site in this
multi-site PyroCMS install. Pairs with the editor-side prose typography
(`system/cms/modules/wysiwyg/css/prose.css`) and the forward-protection
sanitizer hooked into the WYSIWYG save pipeline.

The script is **idempotent** — running it twice on the same data is a no-op
on the second pass.

## Path

```
system/cms/scripts/cleanup_wysiwyg_styles.php
```

The sanitizer it uses also lives under the wysiwyg module so it can be
re-used by other modules and by the streams pre-save hook:

```
system/cms/modules/wysiwyg/libraries/Wysiwyg_sanitizer.php
```

## What gets cleaned

The script auto-discovers every WYSIWYG column in the `pages` streams
namespace by joining `{site}_data_streams` + `{site}_data_field_assignments`
+ `{site}_data_fields` (where `field_type = 'wysiwyg'`). Typically that
covers the default page body and any custom page-type bodies — exact tables
depend on your install. Run with `--dry-run` to see the resolved targets
before any write.

Legacy installs that still use the `{site}_page_chunks` table can opt in by
uncommenting the marked block at the bottom of `discover_targets()` in the
script — it's not emitted by default because (a) most modern installs no
longer have the table and (b) it stores chunks of mixed types and we want
explicit confirmation before touching it.

### Stripped (typography only)

CSS properties removed from `style` attributes anywhere they appear:

`font`, `font-size`, `font-family`, `font-weight`, `font-style`,
`font-variant`, `font-stretch`, `color`, `background`, `background-color`,
`background-image`, `line-height`, `letter-spacing`, `word-spacing`,
`text-decoration`, `text-shadow`, `text-transform`, all `mso-*` vendor
prefixes.

Tags removed (children kept):

`<font>`, `<center>`, `<u>`, every `<ns:tag>` (Word/Office namespace junk
like `<o:p>`, `<w:WordDocument>`).

Empty `<span>` elements (no remaining attributes after style cleanup) are
unwrapped. Class tokens matching `^Mso\w*` are dropped from `class`
attributes.

### Preserved

Layout and intent stays:

`text-align`, `float`, `clear`, `vertical-align`, `width`, `height`,
`max-width`, `margin*`, `padding*`, `display`, image dimensions, `<a>`
targets, `<strong>`/`<em>`/`<table>`/`<img>`/`<br>` semantics,
[Lex template tags](https://github.com/pyrocms/lex) (`{{ ... }}`) inside
attributes and text content.

Inline `style="text-align: center"` survives untouched. So does
`<img src="..." width="800" style="float: right">`.

## Prerequisites

- PHP 8+ with PDO, pdo_mysql, and DOMDocument (all standard).
- A readable `.env` at the repo root with `DB_HOST`, `DB_NAME`, `DB_USER`,
  `DB_PASS` (the same vars `system/cms/config/database.php` reads). The
  script reuses the project's existing dotenv loader at
  `system/cms/bootstrap/env.php`.
- Database user with `SELECT` and `UPDATE` on every `{site}_*` table that
  holds page content.

## Usage

```bash
# 1. See what would change, with sample diffs (no writes):
php system/cms/scripts/cleanup_wysiwyg_styles.php --dry-run --verbose

# 2. Smoke-test on one site, first 5 rows per discovered column:
php system/cms/scripts/cleanup_wysiwyg_styles.php --site=incore --limit=5

# 3. Apply across every site:
php system/cms/scripts/cleanup_wysiwyg_styles.php
```

### Flags

| Flag           | Purpose                                                              |
| -------------- | -------------------------------------------------------------------- |
| `--dry-run`    | Report what would change. No writes.                                 |
| `--site=REF`   | Process only this site (matches `core_sites.ref`).                   |
| `--limit=N`    | Process at most N rows per discovered (table, column) target.        |
| `--verbose`    | Print before/after diffs for the first 3 changed rows per target.    |
| `--help`, `-h` | Show inline help.                                                    |

### Output legend

```
[incore] incore_def_page_fields.body  scanned=16 changed=11 clean=13 untouched=2
```

- `scanned` — total rows looked at in this target.
- `changed` — rows the sanitizer rewrote (will be UPDATE'd unless `--dry-run`).
- `clean`   — rows skipped by the cheap pre-filter; no signs of legacy junk.
- `untouched` — rows the sanitizer ran on but produced no diff (already clean).

Exit code: `0` on success, `1` on errors, `2` if no sites/tables matched.

## Backup / rollback

The script prints a per-site `mysqldump` command at the start of every
non-dry-run, e.g.:

```
mysqldump -h127.0.0.1 -P3306 -uroot -p pyrocmspro2ng \
  incore_def_page_fields incore_pages_products \
  > backups/incore-pages-wysiwyg-20260426-141522.sql
```

Run that command **before** confirming the 5-second countdown the script
prints. Restore by piping the dump back into mysql for the affected tables
only:

```
mysql -h127.0.0.1 -uroot -p pyrocmspro2ng < backups/incore-pages-wysiwyg-20260426-141522.sql
```

## What's in scope (and what isn't)

| Surface                                      | In this script        |
| -------------------------------------------- | --------------------- |
| Page body (`pages` streams namespace)        | Yes (auto-discovered) |
| Legacy `{site}_page_chunks.body`             | Off by default — opt-in via the marked block in `discover_targets()` |
| Blog posts (`blog`/`blogs` namespace)        | No                    |
| Snippets, pyroforms, custom streams content  | No                    |

To extend coverage to blog or other namespaces, edit the SQL in
`discover_targets()` (in the script) — change `field_namespace = 'pages'`
to `IN ('pages', 'blogs')` (etc.). The sanitizer itself is namespace-
agnostic.

## Disabling the save-time sanitizer

The two forward-protection layers (server-side `pre_save` + TinyMCE
`valid_styles`/`invalid_elements`) are gated by a single `.env` flag:

```
WYSIWYG_SANITIZE=true   # default — both layers active
WYSIWYG_SANITIZE=false  # both layers off; saves round-trip content unchanged
```

Useful while debugging an unexpected diff after a save, or if a particular
authoring workflow needs to preserve byte-for-byte content (e.g. importing
pre-formatted HTML you don't want touched). The one-shot CLI cleanup script
**ignores this flag** — running it explicitly is always opt-in.

## How the layers work together

1. **DB cleanup** (this script) — one-shot rewrite of stored bodies.
2. **Forward protection** — `Wysiwyg_sanitizer::clean()` is also called
   from the streams WYSIWYG field's `pre_save` and the legacy chunks
   `pre_save`. New saves can't reintroduce the same junk.
3. **Editor protection** — the TinyMCE init shim
   (`system/cms/modules/wysiwyg/views/content_css_shim.php`) injects
   `valid_styles` and `invalid_elements` defaults so the editor itself
   refuses to persist `<font>` tags or stripped style properties.
4. **Render-time guard** — `prose.css` typography rules use `!important`
   on font-size, line-height, color, and link color so any straggler the
   cleanup missed still gets visually overridden on the public site and in
   the editor iframe.
