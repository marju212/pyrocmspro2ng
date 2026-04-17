<?php
/**
 * Tiny dotenv loader — no composer / no dependency.
 *
 * - Reads KEY=value lines from the project root .env file (if present).
 * - Skips blank lines and lines starting with `#`.
 * - Strips matching surrounding `"…"` or `'…'` quotes from values.
 * - Populates $_SERVER, $_ENV, and putenv() so any of CI's env-lookup
 *   conventions ($_SERVER['PYRO_ENV'], getenv('DB_HOST'), …) work uniformly.
 * - **Real environment variables always win** — if a key is already set in
 *   $_SERVER (e.g. provided by FPM/Apache SetEnv / docker --env), the .env
 *   value is ignored. This lets staging/prod boxes override .env safely.
 *
 * Why not vlucas/phpdotenv: PyroCMS Pro 2.x predates composer; pulling in a
 * full dotenv lib for one tiny task isn't worth the dependency surface. The
 * limited grammar below covers what an .env file actually needs.
 */

if ( ! function_exists('pyro_load_dotenv'))
{
    function pyro_load_dotenv($path)
    {
        if ( ! is_readable($path))
        {
            return false;
        }

        $lines = @file($path, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);
        if ($lines === false)
        {
            return false;
        }

        foreach ($lines as $raw)
        {
            $line = trim($raw);

            // Comment line
            if ($line === '' || $line[0] === '#')
            {
                continue;
            }

            // export KEY=value (bash-style) — strip the prefix
            if (strncmp($line, 'export ', 7) === 0)
            {
                $line = ltrim(substr($line, 7));
            }

            $eq = strpos($line, '=');
            if ($eq === false)
            {
                continue;
            }

            $key = trim(substr($line, 0, $eq));
            $val = trim(substr($line, $eq + 1));

            if ($key === '' || ! preg_match('/^[A-Z_][A-Z0-9_]*$/i', $key))
            {
                continue;
            }

            // Strip a trailing inline comment (only if the value isn't quoted).
            if (($val === '' || ($val[0] !== '"' && $val[0] !== "'"))
                && ($hash = strpos($val, ' #')) !== false)
            {
                $val = rtrim(substr($val, 0, $hash));
            }

            // Strip matching surrounding quotes.
            $len = strlen($val);
            if ($len >= 2 && (($val[0] === '"' && $val[$len - 1] === '"')
                           || ($val[0] === "'" && $val[$len - 1] === "'")))
            {
                $quote = $val[0];
                $val = substr($val, 1, $len - 2);
                if ($quote === '"')
                {
                    // Honour \n / \r / \t / \" / \\ inside double quotes.
                    $val = strtr($val, array('\\n' => "\n", '\\r' => "\r", '\\t' => "\t", '\\"' => '"', '\\\\' => '\\'));
                }
            }

            // Real environment beats .env. Don't clobber something set by FPM
            // / Apache SetEnv / docker --env / shell export.
            if (array_key_exists($key, $_SERVER) || array_key_exists($key, $_ENV) || getenv($key) !== false)
            {
                continue;
            }

            $_SERVER[$key] = $val;
            $_ENV[$key]    = $val;
            @putenv($key . '=' . $val);
        }

        return true;
    }
}

if ( ! function_exists('pyro_env'))
{
    /**
     * Read an env value (.env-loaded OR real-environment) with a fallback.
     * Returns the literal string from the env (no type coercion); cast at the
     * call site if you need a bool/int.
     */
    function pyro_env($key, $default = null)
    {
        if (array_key_exists($key, $_SERVER) && $_SERVER[$key] !== '')
        {
            return $_SERVER[$key];
        }
        if (array_key_exists($key, $_ENV) && $_ENV[$key] !== '')
        {
            return $_ENV[$key];
        }
        $val = getenv($key);
        return ($val === false || $val === '') ? $default : $val;
    }
}

if ( ! function_exists('pyro_env_bool'))
{
    /**
     * Coerce common .env truthy/falsy spellings to a bool.
     * "1", "true", "on", "yes" → true; "0", "false", "off", "no", "" → false.
     */
    function pyro_env_bool($key, $default = false)
    {
        $val = pyro_env($key, null);
        if ($val === null)
        {
            return $default;
        }
        $val = strtolower(trim((string) $val));
        if (in_array($val, array('1', 'true', 'on', 'yes'), true))
        {
            return true;
        }
        if (in_array($val, array('0', 'false', 'off', 'no', ''), true))
        {
            return false;
        }
        return $default;
    }
}

// Auto-load if invoked via `require` from the front controller — the front
// controller defines FCPATH (project root with trailing slash). Otherwise the
// caller is responsible for invoking pyro_load_dotenv() with a path.
if (defined('FCPATH'))
{
    pyro_load_dotenv(FCPATH . '.env');
}
elseif (isset($__pyro_dotenv_path))
{
    pyro_load_dotenv($__pyro_dotenv_path);
}
