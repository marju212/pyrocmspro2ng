<?php defined('BASEPATH') or exit('No direct script access allowed');


class Secure extends Public_Controller
{
    function __construct()
    {
        parent::__construct();
        is_logged_in() AND redirect('');
        $this->load->library('form_validation');
        $this->template->set_layout(false);
    }

    public function index()
    {
        $this->template->build('login');
    }

    public function login()
    {

        $this->form_validation->set_rules('password', 'Password', 'trim|required');

        if ($this->form_validation->run() AND $this->input->post('password') === trim(Settings::get('password'))) {

            $this->session->set_userdata('securelogin', true);

            redirect('');
        }

        $this->template->build('login', ['message' => lang('secure:wrong_pass')]);
    }

    public function destroy()
    {
        $this->session->sess_destroy();
        redirect('');
    }


}
