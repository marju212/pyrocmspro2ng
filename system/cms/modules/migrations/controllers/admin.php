<?php defined('BASEPATH') OR exit('No direct script access allowed');

/**
 * Migrations admin controller — read-only status page.
 *
 * Live URL: /admin/migrations
 */
class Admin extends Admin_Controller
{
    public function __construct()
    {
        parent::__construct();
    }

    public function index()
    {
        // Current applied version. CI's migration library tracks this in
        // a single-row table called `migrations`.
        $current = 0;
        if ($this->db->table_exists('migrations'))
        {
            $row = $this->db->get('migrations')->row();
            if ($row && isset($row->version))
            {
                $current = (int) $row->version;
            }
        }

        // Configured target — CI runs up() until it reaches this.
        $target = (int) $this->config->item('migration_version');

        // Walk the migrations directory and bucket each file.
        $path  = APPPATH.'migrations/';
        $files = glob($path.'*.php') ?: array();
        sort($files);

        $migrations = array();
        foreach ($files as $file)
        {
            $base = basename($file, '.php');
            if ( ! preg_match('/^(\d+)_(.+)$/', $base, $m))
            {
                continue;
            }

            $version = (int) $m[1];
            $migrations[] = array(
                'version' => $version,
                'name'    => str_replace('_', ' ', $m[2]),
                'file'    => $base.'.php',
                'applied' => $version <= $current,
                'pending' => $version > $current && $version <= $target,
            );
        }

        // Site-ref + stream diagnostics for the streams=ads pipeline. The
        // admin → frontend invisibility issues we hit are usually one of:
        //   * the ad's `updated` column is NULL or outside the 2-month
        //     window the salelist filters by
        //   * the streams meta row for `ads` lives under the wrong site
        //     prefix or namespace
        // Surface enough state here that the user can self-diagnose
        // without shell access.

        $site_ref = defined('SITE_REF') ? SITE_REF : '';

        // Streams meta row for the `ads` stream.
        $stream = null;
        if ($this->db->table_exists('data_streams'))
        {
            $stream = $this->db
                ->where('stream_slug', 'ads')
                ->limit(1)
                ->get('data_streams')
                ->row();
        }

        // Resolve the actual table name. With no stream meta, fall back
        // to the convention <site_ref>_ads so the page still shows
        // something useful.
        $ads_table = $stream
            ? $site_ref.'_'.$stream->stream_prefix.$stream->stream_slug
            : $site_ref.'_ads';

        $ads_table_exists = $this->db->table_exists($ads_table);

        // Column definition for `updated` — confirms whether migration
        // 132 actually tightened the schema.
        $updated_column = null;
        if ($ads_table_exists)
        {
            $updated_column = $this->db->query("
                SHOW COLUMNS FROM `{$ads_table}` LIKE 'updated'
            ")->row();
        }

        // Latest 5 rows + window-membership flag.
        $recent_ads = array();
        if ($ads_table_exists)
        {
            $recent_ads = $this->db->query("
                SELECT id,
                       created,
                       updated,
                       (updated BETWEEN SUBDATE(CURDATE(), INTERVAL 2 MONTH) AND NOW())
                           AS in_window
                FROM `{$ads_table}`
                ORDER BY id DESC
                LIMIT 5
            ")->result();
        }

        $this->template
            ->title($this->module_details['name'])
            ->set('current', $current)
            ->set('target',  $target)
            ->set('migrations', $migrations)
            ->set('site_ref', $site_ref)
            ->set('stream', $stream)
            ->set('ads_table', $ads_table)
            ->set('ads_table_exists', $ads_table_exists)
            ->set('updated_column', $updated_column)
            ->set('recent_ads', $recent_ads)
            ->build('admin/index');
    }
}
