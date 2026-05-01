<?php defined('BASEPATH') or exit('No direct script access allowed');

class Module_Ad extends Module
{

    public $version = '0.1';

    public function info()
    {
        return array(
            'name' => array(
                'en' => 'Annonshantering'
            ),
            'description' => array(
                'en' => 'Annonshantering PyroCMS modul.'
            ),
            'frontend' => TRUE,
            'backend' => TRUE,
            'menu' => 'none', // You can also place modules in their top level menu. For example try: 'menu' => 'Sample',
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
