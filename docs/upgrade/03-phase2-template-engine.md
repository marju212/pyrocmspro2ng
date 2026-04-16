# Phase 2: Fix Lex Template Engine for PHP 8.x

**Status:** Not started
**Prerequisites:** Phase 1 complete
**Effort:** 1 day | **Risk:** Medium

---

## Why This Phase Is Critical

The Lex parser processes **every single page render**. `{{ }}` tag syntax for variables, loops, conditionals, and plugin callbacks. All parsing is runtime. If the parser breaks, the entire site goes down.

## Implementation Tasks

### Fix 2a: `str_replace()` TypeError — CRITICAL

- [ ] Edit `system/cms/libraries/Lex/Parser.php:190`

**Before:**
```php
$text = str_replace($data_matches[0][$index], $val, $text);
```
**After:**
```php
if (is_string($val) || is_numeric($val))
{
    $text = str_replace($data_matches[0][$index], $val, $text);
}
```

**Why:** `get_variable()` can return arrays/objects/null. PHP 8.0+ throws `TypeError`.

---

### Fix 2b: `count()` on non-countable — HIGH

- [ ] Edit `system/cms/libraries/Lex/Parser.php:387-392`

**Before:**
```php
$children = Lex_Parser::$callback_data[$array_key];
$child_count = count($children);
```
**After:**
```php
if (!isset(Lex_Parser::$callback_data[$array_key]) || !is_array(Lex_Parser::$callback_data[$array_key]))
{
    return $text;
}
$children = Lex_Parser::$callback_data[$array_key];
$child_count = count($children);
```

**Why:** PHP 8.0+ throws `TypeError` if `count()` receives a non-countable.

---

### Fix 2c: Null coercion in `strpos()` — MEDIUM

- [ ] Edit `system/cms/libraries/Lex/Parser.php` — `inject_extractions()` method

Add at top of method:
```php
$text = (string) $text;
```

**Why:** PHP 8.1+ deprecates passing null to `strpos()`.

---

### Fix 2d: `eval()` ParseError handling — MEDIUM

- [ ] Edit `system/cms/libraries/Lex/Parser.php:777-789`

**Before:**
```php
ob_start();
$result = eval('?>'.$text.'<?php ');
if ($result === false)
{
    echo '<br />You have a syntax error...';
    exit(str_replace(array('?>', '<?php '), '', $text));
}
return ob_get_clean();
```
**After:**
```php
ob_start();
try {
    $result = eval('?>'.$text.'<?php ');
} catch (ParseError $e) {
    ob_end_clean();
    log_message('error', 'Lex parse error: ' . $e->getMessage());
    return '';
}
if ($result === false)
{
    ob_end_clean();
    log_message('error', 'Lex eval error in template');
    return '';
}
return ob_get_clean();
```

**Why:** PHP 8.0 throws `ParseError` instead of returning `false`. Also removes `exit()` that kills the app.

---

### Fix 2e: `eval()` in MX/Loader.php — MEDIUM

- [ ] Edit `system/cms/libraries/MX/Loader.php:316-318`

Wrap eval in try/catch:
```php
try {
    echo eval('?>'.preg_replace("/;*\s*\?>/", "; ?>", str_replace('<?=', '<?php echo ', file_get_contents($_ci_path))));
} catch (ParseError $e) {
    log_message('error', 'Parse error in view ' . $_ci_path . ': ' . $e->getMessage());
}
```

---

### Fix 2f: Unnecessary reference assignment — LOW

- [ ] Edit `system/cms/libraries/MY_Parser.php:19`

**Before:** `$this->_ci = & get_instance();`
**After:** `$this->_ci = get_instance();`

---

## Verification

- [ ] Homepage renders with `{{ }}` tags resolved
- [ ] Blog posts with template tags display correctly
- [ ] Page with conditionals (`{{ if }}`) works
- [ ] Recursive navigation menus render
- [ ] No "Lex parse error" in error logs
- [ ] Template comments (`{{ # comment # }}`) are stripped
- [ ] Plugin callback tags (`{{ plugin:method }}`) work
- [ ] Run `php tests/route_test.php --compare-baseline` — zero regressions
