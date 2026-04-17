<?php
/**
 * HTTP Route Test Suite for PyroCMS Pro 2.2.4
 *
 * Standalone, no framework dependency — works on any PHP >= 5.3.
 *
 * Usage:
 *   php tests/route_test.php http://pyrocmspro2ng.test
 *   php tests/route_test.php http://pyrocmspro2ng.test --save-baseline
 *   php tests/route_test.php http://pyrocmspro2ng.test --compare-baseline
 *   php tests/route_test.php http://pyrocmspro2ng.test --auth user@example.com:secret
 *
 * The baseline file lives at tests/route_baseline.json (committed).
 */

// --- CLI argument parsing ---

if ($argc < 2) {
    fwrite(STDERR, "usage: php {$argv[0]} <base-url> [--save-baseline] [--compare-baseline] [--auth user:pass]\n");
    exit(2);
}

$base_url = rtrim($argv[1], '/');
$save_baseline = false;
$compare_baseline = false;
$auth = null;

for ($i = 2; $i < $argc; $i++) {
    $a = $argv[$i];
    if ($a === '--save-baseline') {
        $save_baseline = true;
    } elseif ($a === '--compare-baseline') {
        $compare_baseline = true;
    } elseif ($a === '--auth' && isset($argv[$i + 1])) {
        $auth = $argv[++$i];
    } else {
        fwrite(STDERR, "unknown argument: $a\n");
        exit(2);
    }
}

$baseline_file = __DIR__ . '/route_baseline.json';

// --- Route definitions ---
// Each entry: [path, expected_status, description]
// expected_status: integer OR array of acceptable integers (first is preferred).

$public_routes = array(
    array('/',                                200, 'homepage'),
    array('/nyheter/',                        200, 'news listing'),
    array('/blimedlem',                       200, 'registration alias'),
    array('/member/medlemmar',                200, 'members listing'),
    array('/users/login',                     200, 'login page'),
    array('/users/register',                  200, 'users/register'),
    array('/register',                        200, 'register alias'),
    array('/blog/rss/all.rss',                200, 'blog rss feed'),
    array('/ad/show/all',                     200, 'ad show all'),
    array('/avelsrad/mjolkgeten',             200, 'avelsrad mjolkgeten'),
    array('/avelsrad/rad-och-dad-fran-avelsradet', 200, 'avelsrad rad-och-dad'),
    array('/djurhalsa/klovatlas',             200, 'djurhalsa klovatlas'),
    array('/djurhalsa/lentivirus',            200, 'djurhalsa lentivirus'),
    array('/foder/grovfoder',                 200, 'foder grovfoder'),
    array('/foder/foderstatsberakning',       200, 'foder foderstatsberakning'),
    array('/foder/skulltorka-ho',             200, 'foder skulltorka-ho'),
    array('/gardens-byggnader',               200, 'gardens byggnader'),
    array('/nyheter/arsmote-2015',            200, 'news article arsmote-2015'),
    array('/nyheter/seminkurs',               200, 'news article seminkurs'),
    array('/om-foreningen/avelsprogram',      200, 'om foreningen avelsprogram'),
    array('/om-foreningen/styrelse',          200, 'om foreningen styrelse'),
    array('/om-foreningen/styrelse/arsmote-2026', 200, 'styrelse arsmote-2026'),
    array('/om-foreningen/styrelse/stadgar',  200, 'styrelse stadgar'),
    array('/projekt/bevarandearbete-2018',    200, 'projekt 2018'),
    array('/projekt/bevarandearbete-2025',    200, 'projekt 2025'),
    array('/projekt/oronmarkning-2017-459',   200, 'projekt oronmarkning'),
);

$redirect_routes = array(
    array('/start',                           301, 'start alias redirect'),
);

$auth_required_routes = array(
    array('/ad/',         array(302, 307), 'ad listing (login redirect)'),
    array('/ad/create',   array(302, 307), 'ad create (login redirect)'),
    array('/edit-profile', array(302, 307), 'edit-profile (login redirect)'),
);

$admin_routes = array(
    array('/admin/',           array(302, 307), 'admin dashboard'),
    array('/admin/pages/',     array(302, 307), 'admin pages'),
    array('/admin/blog/',      array(302, 307), 'admin blog'),
    array('/admin/users/',     array(302, 307), 'admin users'),
    array('/admin/settings/',  array(302, 307), 'admin settings'),
    array('/admin/files/',     array(302, 307), 'admin files'),
    array('/admin/navigation/',array(302, 307), 'admin navigation'),
    array('/admin/ad/',        array(302, 307), 'admin ad (bockavel)'),
    array('/admin/member/',    array(302, 307), 'admin member'),
    array('/admin/comments/',  array(302, 307), 'admin comments'),
    array('/admin/templates/', array(302, 307), 'admin templates'),
    array('/admin/variables/', array(302, 307), 'admin variables'),
    array('/admin/redirects/', array(302, 307), 'admin redirects'),
    array('/admin/groups/',    array(302, 307), 'admin groups'),
    array('/admin/keywords/',  array(302, 307), 'admin keywords'),
);

$error_routes = array(
    array('/this-page-does-not-exist-xyz',
          array(404, 200),
          'nonexistent page (404 or pages catch-all 200)'),
);

$all_routes = array_merge(
    $public_routes,
    $redirect_routes,
    $auth_required_routes,
    $admin_routes,
    $error_routes
);

// --- HTTP probe ---

function probe($url, $auth_header = null) {
    $ch = curl_init($url);
    curl_setopt_array($ch, array(
        CURLOPT_RETURNTRANSFER => true,
        CURLOPT_FOLLOWLOCATION => false,
        CURLOPT_NOBODY         => false,
        CURLOPT_TIMEOUT        => 20,
        CURLOPT_CONNECTTIMEOUT => 5,
        CURLOPT_SSL_VERIFYPEER => false,
        CURLOPT_SSL_VERIFYHOST => 0,
        CURLOPT_USERAGENT      => 'pyrocms-route-test/1.0',
    ));
    if ($auth_header) {
        curl_setopt($ch, CURLOPT_HTTPHEADER, array('Cookie: ' . $auth_header));
    }
    $body = curl_exec($ch);
    $code = (int) curl_getinfo($ch, CURLINFO_HTTP_CODE);
    $err  = curl_error($ch);
    curl_close($ch);
    $body = is_string($body) ? $body : '';

    // Detect inline PHP error/exception blocks the framework prints when
    // display_errors is on. These are the markers PyroCMS / CodeIgniter use:
    //   <h4>A PHP Error was encountered</h4>
    //   <h4>An uncaught Exception was encountered</h4>
    //   "Severity: Warning|Notice|Error" inside that block
    // Catching them here means a regression like the choice/int warning fixed
    // on 2026-04-17 fails the suite instead of slipping through.
    $php_errors = array();
    if (preg_match_all('#<h4>(A PHP Error was encountered|An uncaught Exception was encountered)</h4>.*?Filename:\s*([^<]+)</p>\s*<p>\s*Line Number:\s*([0-9]+)#s', $body, $m, PREG_SET_ORDER)) {
        foreach ($m as $hit) {
            $php_errors[] = trim($hit[2]) . ':' . trim($hit[3]);
        }
    }

    return array(
        'status'      => $code,
        'error'       => $err,
        'body_length' => strlen($body),
        'php_errors'  => $php_errors,
    );
}

function status_ok($actual, $expected) {
    if (is_array($expected)) {
        return in_array($actual, $expected, true);
    }
    return $actual === $expected;
}

function expected_str($expected) {
    return is_array($expected) ? implode('|', $expected) : (string) $expected;
}

// --- Optional auth pre-step ---

$auth_cookie = null;
if ($auth !== null) {
    list($user, $pass) = explode(':', $auth, 2);
    $cookie_jar = tempnam(sys_get_temp_dir(), 'prc');
    $ch = curl_init($base_url . '/users/login');
    curl_setopt_array($ch, array(
        CURLOPT_POST            => true,
        CURLOPT_POSTFIELDS      => http_build_query(array('email' => $user, 'password' => $pass)),
        CURLOPT_COOKIEJAR       => $cookie_jar,
        CURLOPT_COOKIEFILE      => $cookie_jar,
        CURLOPT_FOLLOWLOCATION  => true,
        CURLOPT_RETURNTRANSFER  => true,
        CURLOPT_SSL_VERIFYPEER  => false,
        CURLOPT_SSL_VERIFYHOST  => 0,
        CURLOPT_TIMEOUT         => 20,
    ));
    curl_exec($ch);
    curl_close($ch);
    $auth_cookie = trim(file_get_contents($cookie_jar));
    unlink($cookie_jar);
    echo "[auth] logged in as $user\n";
}

// --- Run probes ---

$results = array();
$failures = array();

foreach ($all_routes as $row) {
    list($path, $expected, $desc) = $row;
    $url = $base_url . $path;
    $r = probe($url, $auth_cookie);
    $status_ok = status_ok($r['status'], $expected);
    // A 200 response with inline PHP warnings is still a regression — count
    // those as failures so the suite catches silent display_errors leaks.
    $php_clean = empty($r['php_errors']);
    $ok = $status_ok && $php_clean;

    $results[$path] = array(
        'status'      => $r['status'],
        'expected'    => $expected,
        'description' => $desc,
        'body_length' => $r['body_length'],
        'php_errors'  => $r['php_errors'],
    );
    $mark = $ok ? 'OK  ' : 'FAIL';
    $php_tag = $php_clean ? '' : '   php_err=' . implode(',', $r['php_errors']);
    printf("%s  %3d (exp %-7s)  %s%s\n", $mark, $r['status'], expected_str($expected), $path, $php_tag);
    if (!$ok) {
        $failures[] = array(
            'path'       => $path,
            'actual'     => $r['status'],
            'expected'   => $expected,
            'php_errors' => $r['php_errors'],
            'error'      => $r['error'],
        );
    }
}

echo "\n";
echo "Total: " . count($all_routes) . "   Failures: " . count($failures) . "\n";

// --- Save baseline ---

if ($save_baseline) {
    $baseline = array(
        'base_url'   => $base_url,
        'generated'  => date('c'),
        'php_version'=> PHP_VERSION,
        'results'    => $results,
    );
    file_put_contents($baseline_file, json_encode($baseline, defined('JSON_PRETTY_PRINT') ? JSON_PRETTY_PRINT : 0));
    echo "Baseline saved to $baseline_file\n";
}

// --- Compare baseline ---

$regressions = array();
if ($compare_baseline) {
    if (!file_exists($baseline_file)) {
        fwrite(STDERR, "no baseline at $baseline_file — run with --save-baseline first\n");
        exit(3);
    }
    $baseline = json_decode(file_get_contents($baseline_file), true);
    foreach ($results as $path => $r) {
        if (!isset($baseline['results'][$path])) {
            echo "NEW   $path (not in baseline)\n";
            continue;
        }
        $b = $baseline['results'][$path];
        if ((int) $r['status'] !== (int) $b['status']) {
            $regressions[] = array('path' => $path, 'was' => $b['status'], 'now' => $r['status']);
            echo "REGR  $path — baseline " . $b['status'] . " → now " . $r['status'] . "\n";
        }
        // PHP-error regression: baseline was clean (no php_errors / missing /
        // empty), but the new run surfaced one or more.
        $b_php = isset($b['php_errors']) ? $b['php_errors'] : array();
        if (empty($b_php) && !empty($r['php_errors'])) {
            $regressions[] = array(
                'path' => $path,
                'was'  => 'clean',
                'now'  => 'php_err=' . implode(',', $r['php_errors']),
            );
            echo "REGR  $path — baseline clean → now " . implode(',', $r['php_errors']) . "\n";
        }
    }
    echo "\nRegressions vs baseline: " . count($regressions) . "\n";
}

// --- Exit code ---

if (count($failures) > 0 || count($regressions) > 0) {
    exit(1);
}
exit(0);
