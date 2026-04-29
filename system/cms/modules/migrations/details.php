<?php defined('BASEPATH') or exit('No direct script access allowed');

/**
 * Migrations admin module.
 *
 * Read-only diagnostics: shows the currently applied migration version
 * (from the migrations table), the configured target version, and a
 * full list of migration files marked applied / pending / future.
 *
 * Sits in the "data" admin menu next to Maintenance.
 */
class Module_Migrations extends Module
{
    public $version = '1.0.0';

    public function info()
    {
        return array(
            'name' => array(
                'en' => 'Migrations',
                'se' => 'Migreringar',
            ),
            'description' => array(
                'en' => 'View applied and pending database migrations.',
                'se' => 'Visa körda och väntande databasmigreringar.',
            ),
            'frontend' => false,
            'backend'  => true,
            'menu'     => 'data',
        );
    }

    public function install()
    {
        return true;
    }

    public function uninstall()
    {
        return false;
    }

    public function upgrade($old_version)
    {
        return true;
    }
}
