<?php defined('BASEPATH') OR exit('No direct script access allowed');


class Events_Ad
{

    protected $ci;

    public function __construct()
    {
        $this->ci =& get_instance();

        Events::register('streams_save_validation_error', array($this, 'validationError'));
        Events::register('streams_pre_insert_entry', array($this, 'validSubmit'));
        Events::register('streams_pre_insert_entry', array($this, 'createdToUpdated'));

    }

    public function validationError($values)
    {
        $this->ci->template->message='Kunde inte spara. Kontrollera att du fyllt i formuläret korrekt.' ;
    }

    public function validSubmit()
    {
        $in=$this->ci->input->post();
        if(isset($in['subject']) && strlen($in['subject'])>0){
           $this->ci->session->set_flashdata('success', 'Filter activated');

           redirect();
           return false;
        }

        return true;
    }

    public function createdToUpdated($data)
    {
        if ($data['stream']->stream_slug=='ads'){
            $data['insert_data']['updated']=date('Y-m-d H:i:s');
        }
        return true;
    }



}
/* End of file events.php */
