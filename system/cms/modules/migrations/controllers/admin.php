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

        $this->template
            ->title($this->module_details['name'])
            ->set('current', $current)
            ->set('target',  $target)
            ->set('migrations', $migrations)
            ->build('admin/index');
    }
}
