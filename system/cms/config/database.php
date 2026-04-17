<?php defined('BASEPATH') OR exit('No direct script access allowed');
/*
| -------------------------------------------------------------------
| DATABASE CONNECTIVITY SETTINGS
| -------------------------------------------------------------------
|
| Connection details are read from environment variables (.env or real env)
| with sensible fallbacks. The supported keys are documented in .env.example;
| the loader lives at system/cms/bootstrap/env.php and is invoked by the
| front controller before this file is included, so pyro_env() is available.
|
|   DB_HOST DB_PORT DB_USER DB_PASS DB_NAME
|   DB_DRIVER DB_PREFIX DB_CHARSET DB_COLLATION
|
| The pyro_env() helper falls back to the second arg when the variable is
| unset OR empty. Empty-string envs are treated as "use default" so a hosting
| panel that exports blank vars doesn't accidentally clobber the password.
|
| The same env values feed both the development and production groups so the
| code shipped to production can stay identical; switch which group is active
| via PYRO_ENV in the environment, not by editing this file.
*/


$pyro_db_defaults = array(
    'hostname'   => '127.0.0.1',
    'username'   => 'root',
    'password'   => '',
    'database'   => 'pyrocmspro2ng',
    'dbdriver'   => 'mysqli',
    'dbprefix'   => '',
    'pconnect'   => FALSE,
    'db_debug'   => TRUE,
    'cache_on'   => FALSE,
    'char_set'   => 'utf8mb4',
    'dbcollat'   => 'utf8mb4_unicode_ci',
    'port'       => '3306',
    // 'Tough love': forces strict mode to test your app for best compatibility
    'stricton'   => TRUE,
);

if ( ! function_exists('pyro_env'))
{
    // Defensive: if database.php is somehow loaded before the front
    // controller's require of env.php, pull it in now. The loader is
    // idempotent (functions are guarded with function_exists).
    require_once dirname(__DIR__) . '/bootstrap/env.php';
}

$pyro_db_from_env = array(
    'hostname'   => pyro_env('DB_HOST',      $pyro_db_defaults['hostname']),
    'username'   => pyro_env('DB_USER',      $pyro_db_defaults['username']),
    'password'   => pyro_env('DB_PASS',      $pyro_db_defaults['password']),
    'database'   => pyro_env('DB_NAME',      $pyro_db_defaults['database']),
    'dbdriver'   => pyro_env('DB_DRIVER',    $pyro_db_defaults['dbdriver']),
    'dbprefix'   => pyro_env('DB_PREFIX',    $pyro_db_defaults['dbprefix']),
    'char_set'   => pyro_env('DB_CHARSET',   $pyro_db_defaults['char_set']),
    'dbcollat'   => pyro_env('DB_COLLATION', $pyro_db_defaults['dbcollat']),
    'port'       => pyro_env('DB_PORT',      $pyro_db_defaults['port']),
);

// Merge with the (mostly-bool) defaults that we don't surface via env.
$pyro_db_active = array_merge($pyro_db_defaults, $pyro_db_from_env);

// Same connection for every environment group — flip groups via PYRO_ENV
// in the environment, not by editing here.
$db[PYRO_DEVELOPMENT] = $pyro_db_active;
$db[PYRO_STAGING]     = $pyro_db_active;
$db[PYRO_PRODUCTION]  = $pyro_db_active;

unset($pyro_db_defaults, $pyro_db_from_env, $pyro_db_active);


// Check the configuration group in use exists
if ( ! array_key_exists(ENVIRONMENT, $db))
{
    show_error(sprintf(lang('error_invalid_db_group'), ENVIRONMENT));
}

// Assign the group to be used
$active_group  = ENVIRONMENT;
$query_builder = TRUE;

/* End of file database.php */
