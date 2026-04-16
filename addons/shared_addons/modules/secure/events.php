<?php defined('BASEPATH') OR exit('No direct script access allowed');

/**
 * Sample Events Class
 *
 * @package     PyroCMS
 * @subpackage  Sample Module
 * @category    events
 * @author      PyroCMS Dev Team
 */
class Events_Secure
{
    protected $ci;

    public function __construct()
    {
        $this->ci =& get_instance();
        $this->ci->load->library('session');
        $this->ci->lang->load('secure/secure');

        if (is_logged_in() OR !Settings::get('password_protect') OR $this->ci->session->userdata('securelogin')) {
            return false;
        }


        if (!in_array($this->ci->uri->segment(1), ['secure', 'admin'])) {
            Events::register('public_controller', array($this, 'check'));
        }


    }


    public function check()
    {

        redirect('secure');
    }

    public function load()
    {

    }

}
/* End of file events.php */
