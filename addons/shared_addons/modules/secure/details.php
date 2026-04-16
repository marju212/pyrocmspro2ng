<?php defined('BASEPATH') or exit('No direct script access allowed');

class Module_Secure extends Module
{

    public $version = '0.1';


    function __construct()
    {

    }


    public function info()
    {
        return [
            'name' => [
                'en' => 'Secure Site'
            ],
            'description' => [
                'en' => 'Protect your site front-end'
            ],
            'frontend' => true,
            'backend' => false,
        ];
    }


    public function install()
    {

        $settings = array(
            array(
                'slug' => 'password',
                'title' => 'Site password',
                'description' => 'Password for site front-end',
                'type' => 'text',
                'default' => 'hidden',
                'value' => 'hidden',
                'options' => '',
                'is_required' => 0,
                'is_gui' => 1,
                'module' => 'secure',
                'order' => 999,
            ),
            array(
                'slug' => 'password_protect',
                'title' => 'Secure',
                'description' => 'Password protect the site front-end',
                'type' => 'radio',
                'default' => '0',
                'value' => '0',
                'options' => '1=Enabled|0=Disabled',
                'is_required' => 0,
                'is_gui' => 1,
                'module' => 'secure',
                'order' => 1000,
            )
        );
        foreach ($settings as $setting) {
            if (!$this->db->insert('settings', $setting)) {
                return false;
            }
        }
        return true;
    }


    public
    function uninstall()
    {
        $this->db->where('module', 'secure');

        if ($this->db->delete('settings')) {
            return true;
        }
        return false;

    }


    public
    function upgrade($old_version)
    {
        return true;
    }


    public
    function help()
    {
        return '<p></p>';

    }
}

/* End of file details.php */
