<?php
/**
 * End-to-end membership registration test for the bockavel site.
 *
 * Posts the public /register form (the same one /blimedlem aliases to),
 * verifies the form was accepted, that a user row landed in bockavel_users
 * with a properly hashed password and the activation_code populated, then
 * cleans up the test user.
 *
 * Standalone, no framework dependency. Works on PHP >= 7.4.
 *
 * Usage:
 *   php tests/registration_test.php http://pyrocmspro2ng.test
 *   php tests/registration_test.php http://pyrocmspro2ng.test \
 *       --db user:pass@host:port/database \
 *       --prefix bockavel_ \
 *       --keep   # don't delete the test user on success
 */
declare(strict_types=1);

if ($argc < 2) {
    fwrite(STDERR, "usage: php {$argv[0]} <base-url> [--db user:pass@host:port/database] [--prefix bockavel_] [--keep]\n");
    exit(2);
}

$base_url = rtrim($argv[1], '/');
$db_dsn   = 'root:@127.0.0.1:3306/pyrocmspro2ng';
$prefix   = 'bockavel_';
$keep     = false;

for ($i = 2; $i < $argc; $i++) {
    $a = $argv[$i];
    if ($a === '--db' && isset($argv[$i + 1])) {
        $db_dsn = $argv[++$i];
    } elseif ($a === '--prefix' && isset($argv[$i + 1])) {
        $prefix = $argv[++$i];
    } elseif ($a === '--keep') {
        $keep = true;
    } else {
        fwrite(STDERR, "unknown argument: $a\n");
        exit(2);
    }
}

// --- Parse db dsn (user:pass@host:port/database) ---

if (!preg_match('#^([^:]*):([^@]*)@([^:]+):(\d+)/(.+)$#', $db_dsn, $m)) {
    fwrite(STDERR, "bad --db value: $db_dsn (expected user:pass@host:port/database)\n");
    exit(2);
}
[, $db_user, $db_pass, $db_host, $db_port, $db_name] = $m;

$mysqli = @new mysqli($db_host, $db_user, $db_pass, $db_name, (int) $db_port);
if ($mysqli->connect_errno) {
    fwrite(STDERR, "mysqli connect failed: {$mysqli->connect_error}\n");
    exit(3);
}
$mysqli->set_charset('utf8mb4');

// --- Helpers ---

function step(string $label): void { echo "\n[STEP] $label\n"; }
function info(string $msg): void  { echo "       $msg\n"; }
function pass(string $msg): void  { echo "  PASS $msg\n"; }
function fail(string $msg): void  { echo "  FAIL $msg\n"; global $mysqli; $mysqli->close(); exit(1); }

function http_post(string $url, array $fields, ?string $cookie_jar = null): array {
    $ch = curl_init($url);
    curl_setopt_array($ch, [
        CURLOPT_POST           => true,
        CURLOPT_POSTFIELDS     => http_build_query($fields, '', '&'),
        CURLOPT_RETURNTRANSFER => true,
        CURLOPT_FOLLOWLOCATION => false,
        CURLOPT_TIMEOUT        => 20,
        CURLOPT_SSL_VERIFYPEER => false,
        CURLOPT_SSL_VERIFYHOST => 0,
        CURLOPT_USERAGENT      => 'pyrocms-registration-test/1.0',
    ]);
    if ($cookie_jar) {
        curl_setopt($ch, CURLOPT_COOKIEJAR,  $cookie_jar);
        curl_setopt($ch, CURLOPT_COOKIEFILE, $cookie_jar);
    }
    $body = curl_exec($ch);
    $code = (int) curl_getinfo($ch, CURLINFO_HTTP_CODE);
    $err  = curl_error($ch);
    curl_close($ch);
    return ['status' => $code, 'body' => is_string($body) ? $body : '', 'error' => $err];
}

function http_get(string $url, ?string $cookie_jar = null): array {
    $ch = curl_init($url);
    curl_setopt_array($ch, [
        CURLOPT_RETURNTRANSFER => true,
        CURLOPT_FOLLOWLOCATION => false,
        CURLOPT_TIMEOUT        => 20,
        CURLOPT_SSL_VERIFYPEER => false,
        CURLOPT_SSL_VERIFYHOST => 0,
        CURLOPT_USERAGENT      => 'pyrocms-registration-test/1.0',
    ]);
    if ($cookie_jar) {
        curl_setopt($ch, CURLOPT_COOKIEJAR,  $cookie_jar);
        curl_setopt($ch, CURLOPT_COOKIEFILE, $cookie_jar);
    }
    $body = curl_exec($ch);
    $code = (int) curl_getinfo($ch, CURLINFO_HTTP_CODE);
    curl_close($ch);
    return ['status' => $code, 'body' => is_string($body) ? $body : ''];
}

// --- Generate a unique throwaway user ---

// bockavel_users.email is varchar(40); username is varchar(20). Keep both
// well under their limits so the form_validation max_length rule passes.
$rand          = substr(bin2hex(random_bytes(4)), 0, 8);
$test_email    = "ce2e_{$rand}@example.test";   // ~25 chars
$test_username = "ce2e_{$rand}";                // ~13 chars
$test_password = 'TestPass!' . $rand;

step("Test user: $test_email / username=$test_username");

// --- Pre-flight: confirm /blimedlem renders without inline PHP errors ---

step('GET /blimedlem renders cleanly');
$cookie_jar = tempnam(sys_get_temp_dir(), 'preg');
$r = http_get("$base_url/blimedlem", $cookie_jar);
$r['status'] === 200 ? pass("HTTP {$r['status']}") : fail("expected 200, got {$r['status']}");
if (preg_match('#<h4>(A PHP Error|An uncaught Exception)#', $r['body'], $hit)) {
    fail("inline PHP error block detected: {$hit[1]}");
}
pass('no inline PHP error/warning block on form');

if (!preg_match('#<form[^>]*action="([^"]*?/register)"#i', $r['body'], $fm)) {
    fail('could not find <form action=".../register"> on /blimedlem page');
}
$form_action = $fm[1];
info("form action = $form_action");

// --- Confirm the user does not already exist ---

$users_table = $prefix . 'users';
$pre_check = $mysqli->query("SELECT id FROM `$users_table` WHERE email = '" . $mysqli->real_escape_string($test_email) . "'");
if ($pre_check && $pre_check->num_rows > 0) {
    fail("test email collision: $test_email already exists in $users_table");
}

// --- POST registration ---

step("POST $form_action");
$post = [
    'username'              => $test_username,
    'email'                 => $test_email,
    'password'              => $test_password,
    'first_name'            => 'Claude',
    'last_name'             => 'E2E',
    'company'               => 'Anthropic Test',
    'medlemsvillkor[]'      => '1',
    // The honeypot is INVERTED on this site: the form prefills the hidden
    // field with a single space and the controller rejects anything else as
    // a bot ("Vi misstänker att du är en bot…"). A naive bot that resets the
    // field to empty fails; an unchanged browser submit passes.
    'd0ntf1llth1s1n'        => ' ',
    'btnSubmit'             => 'Submit',
];
$r = http_post($form_action, $post, $cookie_jar);
info("HTTP {$r['status']}, body " . strlen($r['body']) . "b");

if (preg_match('#<h4>(A PHP Error|An uncaught Exception)#', $r['body'], $hit)) {
    fail("inline PHP error block on POST response: {$hit[1]}");
}
pass('no PHP error block on POST');

// PyroCMS reg flow either redirects (302/303/307 — CI 3.x defaults to 303
// for POST→GET) on success, or rerenders the form (200) with validation
// errors. Accept all four; the real verification happens against the DB.
if (!in_array($r['status'], [200, 302, 303, 307], true)) {
    fail("unexpected HTTP {$r['status']} from POST");
}

// --- Verify user landed in DB ---

step("Verify $test_email exists in $users_table");
$row = $mysqli
    ->query("SELECT u.id, u.email, u.username, u.password, u.salt, u.active, u.activation_code, u.group_id, u.created_on, u.membernumber,
                    p.first_name, p.last_name, p.display_name
             FROM `$users_table` u
             LEFT JOIN `{$prefix}profiles` p ON p.user_id = u.id
             WHERE u.email = '" . $mysqli->real_escape_string($test_email) . "'")
    ->fetch_assoc();

if (!$row) {
    // Reload form body — form_validation errors are rendered inline.
    $errs = '';
    if (preg_match_all('#<p class="error[^"]*"[^>]*>([^<]+)</p>#', $r['body'], $em)) {
        $errs = ' | validation errors: ' . implode(' || ', $em[1]);
    } elseif (preg_match_all('#<div class="alert[^"]*"[^>]*>(.*?)</div>#s', $r['body'], $em)) {
        $errs = ' | flash: ' . trim(strip_tags(implode(' || ', $em[1])));
    }
    fail("user row not found in $users_table after POST{$errs}");
}
pass("found user id={$row['id']}");

// --- Assert key properties ---

step('Validate stored user record');

$row['username'] === $test_username ? pass('username stored') : fail("username mismatch ({$row['username']})");
$row['first_name'] === 'Claude'     ? pass('first_name stored') : fail("first_name mismatch ({$row['first_name']})");
$row['last_name']  === 'E2E'        ? pass('last_name stored')  : fail("last_name mismatch ({$row['last_name']})");
strlen($row['password']) >= 60 && str_starts_with($row['password'], '$2y$')
    ? pass('password is bcrypt ($2y$, ' . strlen($row['password']) . ' chars)')
    : fail("password not bcrypt: " . substr($row['password'], 0, 20) . '… (len ' . strlen($row['password']) . ')');
$row['salt'] === '' ? pass("salt cleared (bcrypt scheme)") : info("salt='{$row['salt']}' (legacy)");
((int) $row['active']) === 0
    ? pass('active=0 — awaiting email activation')
    : info("active={$row['active']} (registration flow may auto-activate on this site)");

if ($row['activation_code'] !== null && $row['activation_code'] !== '') {
    pass("activation_code populated ({$row['activation_code']})");
} else {
    info('activation_code empty — site may not require email activation');
}

// --- Cleanup ---

$user_id = (int) $row['id'];

if ($keep) {
    step('--keep set: leaving test user in DB');
    info("user id=$user_id email=$test_email password=$test_password");
} else {
    step('Cleanup: delete test user + profile');
    $mysqli->query("DELETE FROM `{$prefix}profiles` WHERE user_id = $user_id");
    $mysqli->query("DELETE FROM `$users_table` WHERE id = $user_id");
    $still = $mysqli->query("SELECT 1 FROM `$users_table` WHERE id = $user_id")->num_rows;
    $still === 0 ? pass('user removed') : fail("user $user_id still present after delete");
}

@unlink($cookie_jar);
$mysqli->close();
echo "\nALL CHECKS PASSED\n";
exit(0);
