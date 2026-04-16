<?php if (!defined('BASEPATH')) exit('No direct script access allowed');

class Preview extends Public_Controller {

    function __construct()
    {
        parent::__construct();
    }
    
    function index($id=false)
    {
        $this->load->library('pyroforms');
        $this->load->model('pyroforms_m');
        if (empty($id)) return;
        $url = current_url();
        $out = $this->pyroforms_m->form_display(array('action'=>$url, 'id'=>$id));
        $page = new stdClass();
        
        $page->body = '<h3>'.$out['form_name'].'</h3>'.'<small>'.$out['form_info'].'</small>'.$out['form_fields'];
        $this->template->build('preview',array('page'=>$page));
    }
        
}
/* End of file controllers/faq.php */