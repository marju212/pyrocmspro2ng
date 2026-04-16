# Phase 3: Fix PyroCMS Core PHP 8.x Blockers

**Status:** Not started
**Prerequisites:** Phase 2 complete
**Effort:** 1-2 days | **Risk:** Low

---

## Overview

Fatal errors — the app crashes on PHP 7.0+ or 8.0+ without these fixes. All in `system/cms/` (PyroCMS code, not CI core).

## Implementation Tasks

### Fix 3a: `search_m.php` — mysql_* functions (CRITICAL)

- [ ] Edit `system/cms/modules/streams/models/search_m.php`
- [ ] Remove `mysql_connect()` call (line 84)
- [ ] Replace `mysql_real_escape_string()` with `$this->CI->db->escape_str()` (lines 112, 115, 126)

**Breaks on:** PHP 7.0+ (fatal — functions removed)

**Replace entire `build_query()` method (lines 81-134):**
```php
public function build_query($fields, $search_term, $stream, $search_type = 'keywords')
{
    $keywords = $this->CI->security->xss_clean($search_term);
    $keywords = explode(" ", $keywords);
    foreach ($keywords as $key => $keyword) {
        if (trim($keyword) == '') unset($keywords[$key]);
    }
    $likes = array();
    if ($search_type == 'keywords') {
        $keyword_build = '';
        foreach ($keywords as $keyword) {
            $keyword_build .= $keyword.' ';
            foreach ($fields as $field) {
                $likes[] = "$field LIKE '%".$this->CI->db->escape_str($keyword)."%'";
                $likes[] = "$field LIKE '%".$this->CI->db->escape_str($keyword_build)."%'";
            }
        }
    }
    if ($search_type == 'full_phrase') {
        $search_for = implode(' ', $keywords);
        foreach ($fields as $field) {
            $likes[] = "$field LIKE '%".$this->CI->db->escape_str($search_for)."%'";
        }
    }
    return 'SELECT * FROM '.$this->CI->db->dbprefix($stream->stream_prefix.$stream->stream_slug)
           .' WHERE ('.implode(' OR ', $likes).')';
}
```

---

### Fix 3b: `MX/Modules.php` — `each()` removal (CRITICAL)

- [ ] Edit `system/cms/libraries/MX/Modules.php:80`

**Before:** `(is_array($module)) ? list($module, $params) = each($module) : $params = null;`
**After:**
```php
if (is_array($module)) {
    $params = current($module);
    $module = key($module);
} else {
    $params = null;
}
```

**Breaks on:** PHP 8.0+ (fatal). Runs on **every page load**.

---

### Fix 3c: `Markdown_parser.php` — 4 issues

- [ ] Edit `system/cms/libraries/Markdown_parser.php:132` — rename `Markdown_Parser()` → `__construct()`
- [ ] Edit `system/cms/libraries/Markdown_parser.php:1543` — replace `create_function()` with closure
- [ ] Edit `system/cms/libraries/Markdown_parser.php:1120,1145,1474,1476` — change `$token{0}` → `$token[0]` (4 occurrences)

**Alternative:** Replace entire file with [PHP Markdown 2.x](https://github.com/michelf/php-markdown) (MIT, drop-in).

---

### Fix 3d: `Textile.php` — 3 issues

- [ ] Edit `system/cms/libraries/Textile.php:234` — rename `Textile()` → `__construct()`
- [ ] Edit `system/cms/libraries/Textile.php:1104` — remove `get_magic_quotes_gpc()` conditional block entirely
- [ ] Edit `system/cms/libraries/Textile.php` — remove `&` from `array(&$this, 'method')` callbacks (8 occurrences)

---

### Fix 3e: `field.choice.php` — curly-brace access

- [ ] Edit `system/cms/modules/streams_core/field_types/choice/field.choice.php`
- [ ] Line 164: `$line{0}` → `$line[0]`
- [ ] Line 184: `$line{0}` → `$line[0]`
- [ ] Line 261: `$choice_line{0}` → `$choice_line[0]`
- [ ] Line 316: `$choice_line{0}` → `$choice_line[0]`

**Breaks on:** PHP 8.0+ (fatal syntax error)

---

### Fix 3f: `MY_date_helper.php` — strftime + utf8_encode

- [ ] Check what `Settings::get('date_format')` returns in bockavel — does it use `%` format strings?
- [ ] Edit `system/cms/helpers/MY_date_helper.php:36`
- [ ] Replace `strftime()` with `IntlDateFormatter` (requires `intl` extension) or `date()`
- [ ] Replace `utf8_encode()` with `mb_convert_encoding($str, 'UTF-8', 'ISO-8859-1')`

**Deprecated in:** PHP 8.1 (`strftime`), PHP 8.2 (`utf8_encode`)

---

### Fix 3g: `MY_inflector_helper.php` — curly-brace access

- [ ] Edit `system/cms/helpers/MY_inflector_helper.php:65` — `$word{0}` → `$word[0]`

---

## Verification

- [ ] Streams search works (test search on ad listing if applicable)
- [ ] All modules load on every page (MX/Modules.php fix)
- [ ] Markdown content renders (if blog/pages use markdown)
- [ ] Textile content renders (if used)
- [ ] Choice field type works in streams forms
- [ ] Dates display correctly across the site
- [ ] Run `php tests/route_test.php --compare-baseline` — zero regressions
