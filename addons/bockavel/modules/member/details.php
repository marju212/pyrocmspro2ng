<?php defined('BASEPATH') or exit('No direct script access allowed');

class Module_Member extends Module
{

    public $version = '0.1';

    public function info()
    {
        return array(
            'name' => array(
                'en' => 'Medlemshantering'
            ),
            'description' => array(
                'en' => 'Medlemshantering PyroCMS modul.'
            ),
            'frontend' => TRUE,
            'backend' => TRUE,
            'menu' => 'content', // You can also place modules in their top level menu. For example try: 'menu' => 'Sample',
            'sections' => array(
                'items' => array(
                    'name' => 'sample:items', // These are translated from your language file
                    'uri' => 'admin/sample',
                    'shortcuts' => array(
                        'create' => array(
                            'name' => 'sample:create',
                            'uri' => 'admin/sample/create',
                            'class' => 'add'
                        )
                    )
                )
            )
        );
    }

    public function install()
    {

        $settings = [
            [
                'slug' => 'member_groups',
                'title' => 'Select groups',
                'description' => 'Specify groups (slug comma separated) that should be visible in member list',
                'type' => 'text',
                'default' => 'user',
                'value' => '',
                'options' => '',
                'is_required' => 0,
                'is_gui' => 1,
                'module' => 'member',
                'order' => 970,
            ]
        ];


        foreach ($settings as $setting) {
            if (!$this->db->where('slug', $setting['slug'])->count_all_results('settings')) {
                if (!$this->db->insert('settings', $setting)) {
                    return false;
                }
            }

        }


        return TRUE;

    }

    public function uninstall()
    {

        return TRUE;

    }


    public function upgrade($old_version)
    {
        // Your Upgrade Logic
        return TRUE;
    }

    public function help()
    {
        // Return a string containing help info
        // You could include a file and return it here.
        return "No documentation has been added for this module.<br />Contact the module developer for assistance.";
    }
}
/* End of file details.php */
