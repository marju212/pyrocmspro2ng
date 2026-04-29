<?php defined('BASEPATH') OR exit('No direct script access allowed');

/**
 * Register the read-only `migrations` admin module so it appears in the
 * `data` menu next to Maintenance. The module's PHP/views ship with the
 * codebase; this migration just inserts the row PyroCMS's addons module
 * looks at to decide what to render in the admin sidebar.
 *
 * Multi-site: the `modules` table is per-site (prefixed). This migration
 * runs once per site as each one is hit, so each site gets its own row.
 * Idempotent — skips insert if the slug already exists.
 */
class Migration_Register_migrations_module extends CI_Migration
{
    public function up()
    {
        if ($this->db->where('slug', 'migrations')->count_all_results('modules') > 0)
        {
            return;
        }

        $this->db->insert('modules', array(
            'name'        => serialize(array(
                'en' => 'Migrations',
                'se' => 'Migreringar',
            )),
            'slug'        => 'migrations',
            'version'     => '1.0.0',
            'description' => serialize(array(
                'en' => 'View applied and pending database migrations.',
                'se' => 'Visa körda och väntande databasmigreringar.',
            )),
            'skip_xss'    => false,
            'is_frontend' => false,
            'is_backend'  => true,
            'menu'        => 'data',
            'enabled'     => true,
            'installed'   => true,
            'is_core'     => true,
            'updated_on'  => time(),
        ));
    }

    public function down()
    {
        $this->db->delete('modules', array('slug' => 'migrations'));
    }
}
