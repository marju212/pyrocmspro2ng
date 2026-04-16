<?php defined('BASEPATH') OR exit('No direct script access allowed');

class Plugin_Pyroforms extends Plugin {

    function get()
    {
        $this->load->model('pyroforms_m');
        $this->load->library('pyroforms');
        $id = $this->attribute('id',false);
        $no_css = $this->attribute('nocss', false);
        
        if (empty($id)) return;
        
        $display = $this->attribute('display', 'all');

        $url = $this->attribute('action', current_url());
        if ($out = $this->pyroforms_m->form_display(array('action'=>$url, 'id'=>$id), ($display=='custom')))
        {

            if (!$no_css)
            {
            	 pyroforms::add_css('pf-front.css');
            }
            // Is this a response?
            if (is_string($out))
            {
            	return $out;
            }

            switch ($display){
                case 'all':
                    $out = '<h3>'.$out['form_name'].'</h3>'.'<small>'.$out['form_info'].'</small>'.$out['form_fields'];
                break;
                case 'form':
                    $out = $out['form_fields'];
                break;
                case 'custom':
                    return array($out);
                break;
            }
            return $out;
        }
    }
}

/* End of file plugin.php */