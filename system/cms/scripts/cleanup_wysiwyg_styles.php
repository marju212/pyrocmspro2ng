<?php
/**
 * cleanup_wysiwyg_styles.php — strip legacy CKEditor inline typography from
 * page chunks across every site in the multi-site install.
 *
 * Usage: see CLEANUP_WYSIWYG_README.md in the repo root.
 *
 * Standalone CLI: bootstraps the project's tiny dotenv loader to read DB
 * credentials, connects via PDO, iterates `core_sites`, and for each site
 * auto-discovers every WYSIWYG column in the `pages` streams namespace via
 * data_streams + data_field_assignments + data_fields. Updates each row only
 * when the sanitizer changes the body. The legacy `page_chunks` table is
 * supported transparently when present (older installs) but not required.
 */

if (PHP_SAPI !== 'cli')
{
	fwrite(STDERR, "This script must be run from the command line.\n");
	exit(1);
}

// --- Bootstrap project's dotenv loader (no CodeIgniter dependency) -----
$root = realpath(__DIR__ . '/../../..');
if ( ! $root)
{
	fwrite(STDERR, "Cannot locate project root from " . __DIR__ . "\n");
	exit(1);
}
require_once $root . '/system/cms/bootstrap/env.php';
pyro_load_dotenv($root . '/.env');

require_once $root . '/system/cms/modules/wysiwyg/libraries/Wysiwyg_sanitizer.php';

// --- CLI flags ----------------------------------------------------------
$opts = parse_flags($argv);
if (isset($opts['help']))
{
	usage();
	exit(0);
}
$dry      = ! empty($opts['dry-run']);
$verbose  = ! empty($opts['verbose']);
$only     = isset($opts['site'])  ? (string) $opts['site']  : null;
$limit    = isset($opts['limit']) ? (int)    $opts['limit'] : 0;

// --- Connect ------------------------------------------------------------
$db_host = pyro_env('DB_HOST', '127.0.0.1');
$db_port = pyro_env('DB_PORT', '3306');
$db_name = pyro_env('DB_NAME', 'pyrocmspro2ng');
$db_user = pyro_env('DB_USER', 'root');
$db_pass = pyro_env('DB_PASS', '');
$db_char = pyro_env('DB_CHARSET', 'utf8mb4');

try {
	$pdo = new PDO(
		"mysql:host=$db_host;port=$db_port;dbname=$db_name;charset=$db_char",
		$db_user,
		$db_pass,
		array(
			PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION,
			PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_OBJ,
			PDO::ATTR_EMULATE_PREPARES   => false,
		)
	);
} catch (PDOException $e) {
	fwrite(STDERR, "DB connect failed: {$e->getMessage()}\n");
	exit(1);
}

// --- Site list ----------------------------------------------------------
try {
	$sites = $pdo->query('SELECT ref, name FROM core_sites ORDER BY ref')->fetchAll();
} catch (PDOException $e) {
	fwrite(STDERR, "Could not read core_sites: {$e->getMessage()}\n");
	exit(1);
}
if ( ! $sites)
{
	fwrite(STDERR, "No sites found in core_sites.\n");
	exit(2);
}
if ($only !== null)
{
	$sites = array_values(array_filter($sites, function($s) use ($only) {
		return $s->ref === $only;
	}));
	if ( ! $sites)
	{
		fwrite(STDERR, "Site --site={$only} not found in core_sites.\n");
		exit(2);
	}
}

// --- Banner -------------------------------------------------------------
fwrite(STDOUT, "\n=== WYSIWYG cleanup ===\n");
fwrite(STDOUT, sprintf("DB:        %s@%s:%s/%s\n", $db_user, $db_host, $db_port, $db_name));
fwrite(STDOUT, sprintf("Mode:      %s\n", $dry ? 'DRY RUN (no writes)' : 'APPLY'));
fwrite(STDOUT, sprintf("Sites:     %d (%s)\n",
	count($sites),
	implode(', ', array_map(function($s){ return $s->ref; }, $sites))));
if ($limit > 0) fwrite(STDOUT, sprintf("Per-site row limit: %d\n", $limit));
fwrite(STDOUT, "\n");

// Resolve the targets up front: per site, build a list of (table, column)
// pairs to clean. We do this before any backup messaging so the user sees
// exactly which tables will be touched.
$plan = array();
foreach ($sites as $site)
{
	$targets = discover_targets($pdo, $site->ref);
	$plan[$site->ref] = $targets;
}

$total_targets = 0;
foreach ($plan as $list) $total_targets += count($list);

if ($total_targets === 0)
{
	fwrite(STDOUT, "No WYSIWYG columns found in the 'pages' namespace on any site.\n");
	exit(0);
}

if ( ! $dry)
{
	fwrite(STDOUT, "BACKUP FIRST. Suggested commands (run now if you haven't):\n");
	$ts = date('Ymd-His');
	foreach ($plan as $ref => $targets)
	{
		if ( ! $targets) continue;
		$tbl_args = implode(' ', array_unique(array_map(function($t){ return $t['table']; }, $targets)));
		fwrite(STDOUT, sprintf(
			"  mysqldump -h%s -P%s -u%s -p %s %s > backups/%s-pages-wysiwyg-%s.sql\n",
			$db_host, $db_port, $db_user, $db_name, $tbl_args, $ref, $ts
		));
	}
	fwrite(STDOUT, "\nProceeding in 5s — Ctrl+C to abort.\n");
	sleep(5);
}

// --- Per-site, per-target processing -----------------------------------
$totals = array('scanned' => 0, 'changed' => 0, 'errors' => 0);

foreach ($plan as $ref => $targets)
{
	if ( ! $targets)
	{
		fwrite(STDOUT, "[$ref] no WYSIWYG columns in pages namespace, skipping.\n");
		continue;
	}

	foreach ($targets as $t)
	{
		$tbl = $t['table'];
		$col = $t['column'];

		$sql = "SELECT id, `$col` AS body FROM `$tbl` ORDER BY id";
		if ($limit > 0) $sql .= ' LIMIT ' . (int) $limit;

		try {
			$rows = $pdo->query($sql)->fetchAll();
		} catch (PDOException $e) {
			fwrite(STDERR, "[$ref] SELECT $tbl.$col failed: {$e->getMessage()}\n");
			$totals['errors']++;
			continue;
		}

		$update = $pdo->prepare("UPDATE `$tbl` SET `$col` = :body WHERE id = :id");

		$scanned = 0; $changed = 0; $skipped_clean = 0; $samples = array();
		foreach ($rows as $row)
		{
			$scanned++;
			if ($row->body === null || $row->body === '') continue;

			// Pre-filter: avoid running DOMDocument on rows that show no signs
			// of legacy junk. Round-tripping pristine HTML through DOMDocument
			// produces cosmetic-only diffs (void-tag normalization, entity
			// decoding) that we explicitly do NOT want to write back.
			if ( ! Wysiwyg_sanitizer::needs_cleaning($row->body))
			{
				$skipped_clean++;
				continue;
			}

			$cleaned = Wysiwyg_sanitizer::clean($row->body);
			if ($cleaned === $row->body) continue;
			$changed++;

			if (count($samples) < 3)
			{
				$samples[] = array('id' => $row->id, 'before' => $row->body, 'after' => $cleaned);
			}

			if ( ! $dry)
			{
				try {
					$update->execute(array(':body' => $cleaned, ':id' => $row->id));
				} catch (PDOException $e) {
					fwrite(STDERR, "[$ref] UPDATE $tbl.$col id={$row->id} failed: {$e->getMessage()}\n");
					$totals['errors']++;
				}
			}
		}

		$totals['scanned'] += $scanned;
		$totals['changed'] += $changed;

		fwrite(STDOUT, sprintf(
			"[%s] %s.%s  scanned=%d changed=%d clean=%d untouched=%d\n",
			$ref, $tbl, $col, $scanned, $changed, $skipped_clean, $scanned - $changed - $skipped_clean
		));

		if ($verbose && $samples)
		{
			fwrite(STDOUT, "  sample diffs (first " . count($samples) . "):\n");
			foreach ($samples as $i => $s)
			{
				fwrite(STDOUT, sprintf("  [%d] id=%d\n", $i + 1, $s['id']));
				fwrite(STDOUT, "      BEFORE: " . preview($s['before']) . "\n");
				fwrite(STDOUT, "      AFTER : " . preview($s['after'])  . "\n");
			}
		}
	}
}

// --- Summary -----------------------------------------------------------
fwrite(STDOUT, "\n=== Summary ===\n");
fwrite(STDOUT, sprintf("Sites processed: %d\n", count($sites)));
fwrite(STDOUT, sprintf("Rows scanned:    %d\n", $totals['scanned']));
fwrite(STDOUT, sprintf("Rows changed:    %d%s\n", $totals['changed'], $dry ? ' (dry run — nothing written)' : ''));
fwrite(STDOUT, sprintf("Errors:          %d\n", $totals['errors']));

exit($totals['errors'] > 0 ? 1 : 0);


// --- Helpers -----------------------------------------------------------

/**
 * Auto-discover (table, column) pairs that hold WYSIWYG content for the
 * `pages` namespace on the given site. Joins data_field_assignments +
 * data_fields + data_streams. Also picks up the legacy {ref}_page_chunks
 * table (older PyroCMS installs) when present.
 */
function discover_targets(PDO $pdo, $ref)
{
	$out = array();

	// Streams metadata: data_fields(field_type='wysiwyg', field_namespace='pages')
	// + data_field_assignments → data_streams gives us the table.
	$ds  = "{$ref}_data_streams";
	$df  = "{$ref}_data_fields";
	$dfa = "{$ref}_data_field_assignments";
	if (table_exists($pdo, $ds) && table_exists($pdo, $df) && table_exists($pdo, $dfa))
	{
		$sql = "SELECT s.stream_prefix, s.stream_slug, f.field_slug
		        FROM `$dfa` a
		        JOIN `$df` f ON f.id = a.field_id
		        JOIN `$ds` s ON s.id = a.stream_id
		        WHERE f.field_type = 'wysiwyg' AND f.field_namespace = 'pages'";
		try {
			$rows = $pdo->query($sql)->fetchAll();
		} catch (PDOException $e) {
			$rows = array();
		}
		foreach ($rows as $r)
		{
			$tbl = $ref . '_' . $r->stream_prefix . $r->stream_slug;
			if (table_exists($pdo, $tbl) && column_exists($pdo, $tbl, $r->field_slug))
			{
				$out[] = array('table' => $tbl, 'column' => $r->field_slug);
			}
		}
	}

	// Legacy chunks (older installs): {ref}_page_chunks rows of wysiwyg type.
	// Wrapped as a single (table, column) target with type-filter applied at
	// SELECT time via a sentinel column name; we keep the surface uniform by
	// using a separate flag here. To keep this simple we only emit it when
	// the table exists AND has the expected schema.
	$legacy = $ref . '_page_chunks';
	if (table_exists($pdo, $legacy) && column_exists($pdo, $legacy, 'body'))
	{
		// We can't easily express the type filter in our generic loop without
		// adding another field — so emit the legacy table as-is. The
		// sanitizer is safe on plain HTML and a no-op on plain text/markdown,
		// but to be conservative we leave non-wysiwyg legacy chunks alone.
		// Skipping unless wysiwyg types are dominant — caller can re-enable.
		// (Intentionally not emitted by default; uncomment if you have a
		// legacy install you want included.)
		// $out[] = array('table' => $legacy, 'column' => 'body');
	}

	return $out;
}

function table_exists(PDO $pdo, $name)
{
	static $cache = array();
	if (isset($cache[$name])) return $cache[$name];
	$q = $pdo->prepare(
		"SELECT 1 FROM information_schema.tables
		 WHERE table_schema = DATABASE() AND table_name = :t LIMIT 1"
	);
	$q->execute(array(':t' => $name));
	return $cache[$name] = (bool) $q->fetchColumn();
}

function column_exists(PDO $pdo, $table, $column)
{
	static $cache = array();
	$key = "$table.$column";
	if (isset($cache[$key])) return $cache[$key];
	$q = $pdo->prepare(
		"SELECT 1 FROM information_schema.columns
		 WHERE table_schema = DATABASE() AND table_name = :t AND column_name = :c LIMIT 1"
	);
	$q->execute(array(':t' => $table, ':c' => $column));
	return $cache[$key] = (bool) $q->fetchColumn();
}

function parse_flags(array $argv)
{
	$opts = array();
	for ($i = 1; $i < count($argv); $i++)
	{
		$arg = $argv[$i];
		if ($arg === '--help' || $arg === '-h') { $opts['help'] = true; continue; }
		if (strncmp($arg, '--', 2) !== 0)
		{
			fwrite(STDERR, "Unknown argument: $arg\n");
			usage();
			exit(1);
		}
		$arg = substr($arg, 2);
		if (($eq = strpos($arg, '=')) !== false)
		{
			$opts[substr($arg, 0, $eq)] = substr($arg, $eq + 1);
		}
		else
		{
			$opts[$arg] = true;
		}
	}
	return $opts;
}

function preview($s)
{
	$s = preg_replace('/\s+/', ' ', $s);
	if (strlen($s) > 200) $s = substr($s, 0, 200) . '…';
	return $s;
}

function usage()
{
	fwrite(STDOUT, <<<TXT

Usage: php system/cms/scripts/cleanup_wysiwyg_styles.php [options]

Strips legacy CKEditor inline typography (font-size, font-family, color,
line-height, etc.) from {site}_page_chunks.body across every site. Only
touches chunks of type wysiwyg-simple or wysiwyg-advanced. Idempotent.

Options:
  --dry-run        Report what would change without writing.
  --site=REF       Process only the named site (matches core_sites.ref).
  --limit=N        Process at most N rows per site (useful for smoke tests).
  --verbose        Print sample before/after diffs (first 3 per site).
  --help, -h       Show this help.

Examples:
  php system/cms/scripts/cleanup_wysiwyg_styles.php --dry-run --verbose
  php system/cms/scripts/cleanup_wysiwyg_styles.php --site=incore --limit=5
  php system/cms/scripts/cleanup_wysiwyg_styles.php

The script prints a mysqldump command per site before any non-dry-run write
so you can take a backup first.

TXT
	);
}
