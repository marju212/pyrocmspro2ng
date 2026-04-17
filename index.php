<?php

/*
 *---------------------------------------------------------------
 * .ENV LOADING
 *---------------------------------------------------------------
 *
 * Load the project's `.env` (if any) before doing anything else, so the
 * environment-name lookup below and the database config can read DB_HOST /
 * DB_USER / DB_PASS / etc. from it. The loader is a tiny no-dependency
 * parser; see system/cms/bootstrap/env.php for the shape of supported
 * values. Real environment variables (FPM SetEnv / docker --env / shell
 * export) always win over .env values.
 */
require __DIR__ . '/system/cms/bootstrap/env.php';
pyro_load_dotenv(__DIR__ . '/.env');


/*
 *---------------------------------------------------------------
 * APPLICATION ENVIRONMENT
 *---------------------------------------------------------------
 *
 * Settable via PYRO_ENV in .env (or as a real env variable). Valid values:
 * development / staging / production. Defaults to development if unset.
 */

if (!defined('PYRO_DEVELOPMENT')) {
    define('PYRO_DEVELOPMENT', 'development');
}

if (!defined('PYRO_STAGING')) {
    define('PYRO_STAGING', 'staging');
}
if (!defined('PYRO_PRODUCTION')) {
    define('PYRO_PRODUCTION', 'production');
}

if (!defined('ENVIRONMENT')) {
    define('ENVIRONMENT', pyro_env('PYRO_ENV', PYRO_DEVELOPMENT));
}


/*
 *---------------------------------------------------------------
 * ERROR REPORTING
 *---------------------------------------------------------------
 *
 * Different environments require different error visibility. In development
 * we surface everything (minus E_DEPRECATED noise). Staging / production
 * stay quiet. All knobs can be overridden via .env:
 *
 *   APP_DISPLAY_ERRORS=true|false
 *   APP_ERROR_LOG=path/to/file (relative to project root, or absolute)
 *   APP_ERROR_REPORTING=E_ALL ^ E_DEPRECATED  (literal php tokens)
 */

// Resolve a base error_reporting bitmask from an env-string like
// "E_ALL ^ E_DEPRECATED". Tokenises on bitwise operators and looks each name
// up via constant() — no eval, so a tampered .env can't run code. Unknown
// tokens fall back to the env's default mask.
$pyro_error_reporting_default = (ENVIRONMENT === PYRO_DEVELOPMENT)
    ? (E_ALL ^ E_DEPRECATED)
    : E_ALL;
$pyro_error_reporting_expr = pyro_env('APP_ERROR_REPORTING', null);
$pyro_error_reporting = $pyro_error_reporting_default;
if (is_string($pyro_error_reporting_expr) && $pyro_error_reporting_expr !== '') {
    // preg_split keeps the operator delimiters as their own tokens.
    $pyro_tokens = preg_split('/(\||\^|&)/', $pyro_error_reporting_expr, -1, PREG_SPLIT_DELIM_CAPTURE | PREG_SPLIT_NO_EMPTY);
    $pyro_acc = null;
    $pyro_op  = null;
    $pyro_ok  = true;
    foreach ($pyro_tokens as $pyro_t) {
        $pyro_t = trim($pyro_t);
        if ($pyro_t === '') {
            continue;
        }
        if ($pyro_t === '|' || $pyro_t === '^' || $pyro_t === '&') {
            if ($pyro_acc === null) { $pyro_ok = false; break; }
            $pyro_op = $pyro_t;
            continue;
        }
        // Constant (E_ALL, E_DEPRECATED, …) or bare integer.
        if (ctype_digit($pyro_t)) {
            $pyro_v = (int) $pyro_t;
        } elseif (preg_match('/^E_[A-Z_]+$/', $pyro_t) && defined($pyro_t)) {
            $pyro_v = constant($pyro_t);
            if (!is_int($pyro_v)) { $pyro_ok = false; break; }
        } else {
            $pyro_ok = false; break;
        }
        if ($pyro_acc === null) {
            $pyro_acc = $pyro_v;
        } else {
            switch ($pyro_op) {
                case '|': $pyro_acc = $pyro_acc | $pyro_v; break;
                case '^': $pyro_acc = $pyro_acc ^ $pyro_v; break;
                case '&': $pyro_acc = $pyro_acc & $pyro_v; break;
                default:  $pyro_ok = false; break 2;
            }
            $pyro_op = null;
        }
    }
    if ($pyro_ok && $pyro_acc !== null) {
        $pyro_error_reporting = $pyro_acc;
    }
    unset($pyro_tokens, $pyro_acc, $pyro_op, $pyro_ok, $pyro_t, $pyro_v);
}
error_reporting($pyro_error_reporting);

switch (ENVIRONMENT) {
    case PYRO_DEVELOPMENT:
        ini_set('display_errors', pyro_env_bool('APP_DISPLAY_ERRORS', true) ? '1' : '0');
        $pyro_error_log = pyro_env('APP_ERROR_LOG', '');
        if ($pyro_error_log !== '') {
            ini_set('log_errors', '1');
            // Allow relative paths (resolved against the project root).
            if (!preg_match('#^(/|[A-Za-z]:[/\\\\])#', $pyro_error_log)) {
                $pyro_error_log = __DIR__ . '/' . ltrim($pyro_error_log, '/');
            }
            ini_set('error_log', $pyro_error_log);
        }
        break;

    case PYRO_STAGING:
    case PYRO_PRODUCTION:
        ini_set('display_errors', pyro_env_bool('APP_DISPLAY_ERRORS', false) ? '1' : '0');
        $pyro_error_log = pyro_env('APP_ERROR_LOG', '');
        if ($pyro_error_log !== '') {
            ini_set('log_errors', '1');
            if (!preg_match('#^(/|[A-Za-z]:[/\\\\])#', $pyro_error_log)) {
                $pyro_error_log = __DIR__ . '/' . ltrim($pyro_error_log, '/');
            }
            ini_set('error_log', $pyro_error_log);
        }
        break;

    default:
        exit('The environment is not set correctly. ENVIRONMENT = ' . ENVIRONMENT . '.');
}

unset($pyro_error_reporting_default, $pyro_error_reporting_expr, $pyro_error_reporting, $pyro_error_log);

/*
|---------------------------------------------------------------
| DEFAULT INI SETTINGS
|---------------------------------------------------------------
|
| Hosts have a habit of setting stupid settings for various
| things. These settings should help provide maximum compatibility
| for PyroCMS
|
*/

// Let's hold Windows' hand and set a include_path in case it forgot
set_include_path(dirname(__FILE__));

// Some hosts (was it GoDaddy? complained without this
@ini_set('cgi.fix_pathinfo', 0);

// PHP 5.3 will BITCH without this
if (ini_get('date.timezone') == '') {
    //date_default_timezone_set('UTC');
    date_default_timezone_set("Europe/Stockholm");
}

/*
|---------------------------------------------------------------
| SYSTEM FOLDER NAME
|---------------------------------------------------------------
|
| This variable must contain the name of your "system" folder.
| Include the path if the folder is not in the same  directory
| as this file.
|
| NO TRAILING SLASH!
|
*/
$system_path = 'system/codeigniter';

/*
 *---------------------------------------------------------------
 * APPLICATION FOLDER NAME
 *---------------------------------------------------------------
 *
 * If you want this front controller to use a different "application"
 * folder then the default one you can set its name here. The folder
 * can also be renamed or relocated anywhere on your server.  If
 * you do, use a full server path. For more info please see the user guide:
 * http://codeigniter.com/user_guide/general/managing_apps.html
 *
 * NO TRAILING SLASH!
 *
 */
$application_folder = 'system/cms';

/*
 *---------------------------------------------------------------
 * ADDON FOLDER NAME
 *---------------------------------------------------------------
 *
 * If you want this front controller to use a different "application"
 * folder then the default one you can set its name here. The folder
 * can also be renamed or relocated anywhere on your server.  If
 * you do, use a full server path. For more info please see the user guide:
 * http://codeigniter.com/user_guide/general/managing_apps.html
 *
 * NO TRAILING SLASH!
 *
 */
$addon_folder = 'addons';

/*
 * --------------------------------------------------------------------
 * DEFAULT CONTROLLER
 * --------------------------------------------------------------------
 *
 * Normally you will set your default controller in the routes.php file.
 * You can, however, force a custom routing by hard-coding a
 * specific controller class/function here.  For most applications, you
 * WILL NOT set your routing here, but it's an option for those
 * special instances where you might want to override the standard
 * routing in a specific front controller that shares a common CI installation.
 *
 * IMPORTANT:  If you set the routing here, NO OTHER controller will be
 * callable. In essence, this preference limits your application to ONE
 * specific controller.  Leave the function name blank if you need
 * to call functions dynamically via the URI.
 *
 * Un-comment the $routing array below to use this feature
 *
 */
// The directory name, relative to the "controllers" folder.  Leave blank
// if your controller is not in a sub-folder within the "controllers" folder
// $routing['directory'] = '';

// The controller class file name.  Example:  Mycontroller.php
// $routing['controller'] = '';

// The controller function you wish to be called.
// $routing['function']	= '';


/*
 * -------------------------------------------------------------------
 *  CUSTOM CONFIG VALUES
 * -------------------------------------------------------------------
 *
 * The $assign_to_config array below will be passed dynamically to the
 * config class when initialized. This allows you to set custom config
 * items or override any default config values found in the config.php file.
 * This can be handy as it permits you to share one application between
 * multiple front controller files, with each file containing different
 * config values.
 *
 * Un-comment the $assign_to_config array below to use this feature
 *
 */
// $assign_to_config['name_of_config_item'] = 'value of config item';


// --------------------------------------------------------------------
// END OF USER CONFIGURABLE SETTINGS.  DO NOT EDIT BELOW THIS LINE
// --------------------------------------------------------------------


/*
 * ---------------------------------------------------------------
 *  Resolve the system path for increased reliability
 * ---------------------------------------------------------------
 */
if (function_exists('realpath') AND @realpath($system_path) !== FALSE) {
    $system_path = realpath($system_path) . '/';
}

// ensure there's a trailing slash
$system_path = rtrim($system_path, '/') . '/';

// Is the sytsem path correct?
if (!is_dir($system_path)) {
    exit("Your system folder path does not appear to be set correctly. Please open the following file and correct this: " . pathinfo(__FILE__, PATHINFO_BASENAME));
}

/*
 * -------------------------------------------------------------------
 *  Now that we know the path, set the main path constants
 * -------------------------------------------------------------------
 */
// The name of THIS file
define('SELF', pathinfo(__FILE__, PATHINFO_BASENAME));

// The PHP file extension
define('EXT', '.php');

// Path to the system folder
define('BASEPATH', str_replace("\\", "/", $system_path));

// The site slug: (example.com)
define('SITE_DOMAIN', $_SERVER['HTTP_HOST']);

// This only allows you to change the name. ADDONPATH should still be used in the app
define('ADDON_FOLDER', $addon_folder . '/');

// Path to the addon folder that is shared between sites
define('SHARED_ADDONPATH', 'addons/shared_addons/');

// Path to the front controller (this file)
define('FCPATH', str_replace(SELF, '', __FILE__));

// Name of the "system folder"
$parts = explode('/', trim(BASEPATH, '/'));
define('SYSDIR', end($parts));
unset($parts);

// The path to the "application" folder
define('APPPATH', $application_folder . '/');

// Path to the views folder
define('VIEWPATH', APPPATH . 'views/');

/*
 *---------------------------------------------------------------
 * DEMO
 *---------------------------------------------------------------
 *
 * Should PyroCMS run as a demo, meaning no destructive actions
 * can be taken such as removing admins or changing passwords?
 *
 */

define('PYRO_DEMO', (file_exists(FCPATH . 'DEMO')));

/*
 * --------------------------------------------------------------------
 * LOAD THE BOOTSTRAP FILE
 * --------------------------------------------------------------------
 *
 * And away we go...
 *
 */
require_once BASEPATH . 'core/CodeIgniter' . EXT;

/* End of file index.php */
