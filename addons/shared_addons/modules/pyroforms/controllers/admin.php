<?php defined('BASEPATH') or exit('No direct script access allowed');

//Fix ajax

class Admin extends Admin_Controller
{

    /**
     * Validation rules for creating a new faq
     *
     * @var array
     * @access private
     */
    private $validation_rules = array();
    public $section = 'pyroforms';
    public $modpath = '';

    public function __construct()
    {
        // First call the parent's constructor
        parent::__construct();

        // Load all the required classes
        //$this->load->model(array());
        $this->load->library('form_validation');
        $this->lang->load('pyroforms');
        $this->modpath = $this->module_details['path'];
        $this->template->set_partial('shortcuts', 'admin/partials/shortcuts');

        $this->validation_rules['forms'] = array(
            array(
                'field' => 'frmName',
                'label' => lang('forbuilder_frm_name_label'),
                'rules' => 'required|trim'
            ),
            'slug' => array(
                'field' => 'frmSlug',
                'label' => 'lang:pf_frm_slug_label',
                'rules' => 'trim|required|alpha_dot_dash|max_length[100]|callback__check_slug'
            ),
            array(
                'field' => 'frmInfo',
                'label' => lang('forbuilder_frm_info_label'),
                'rules' => 'trim'
            ),
            array(
                'field' => 'send_to',
                'label' => 'Send To',
                'rules' => 'trim'
            ),
            array(
                'field' => 'reply_to',
                'label' => 'Reply To',
                'rules' => 'trim|valid_email'
            ),
            array(
                'field' => 'frmResponse',
                'label' => 'Form Response',
                'rules' => 'trim'
            ),
            array(
                'field' => 'frmNotifyTemplate',
                'label' => 'Form Notification Template',
                'rules' => 'trim'
            ),
            array(
                'field' => 'frmNotifyTitle',
                'label' => 'Form Notification Title',
                'rules' => 'trim'
            ),
            array(
                'field' => 'frmNotifyBody',
                'label' => 'Form Notification Body',
                'rules' => 'trim'
            ),
            array(
                'field' => 'layout',
                'label' => 'Layout',
                'rules' => 'trim'
            ),
            array(
                'field' => 'is_ajax',
                'label' => 'isAjax'
            ),
            array(
                'field' => 'file_maxsize',
                'label' => 'File Max Size'
            ),
            array(
                'field' => 'file_types',
                'label' => 'File Types'
            ),
            array(
                'field' => 'track_pages',
                'label' => 'Track Pages'
            ),
            array(
                'field' => 'track_events',
                'label' => 'Track Events'
            )
        );

        //if the request is ajax set layout to false
        $this->_is_ajax() and $this->template->set_layout(FALSE);

/*        $this->template
            ->append_css('module::general_foundicons.css')
            ->prepend_metadata('<!--[if lt IE 8]>
                                <link rel="stylesheet" href="'.site_url($this->modpath.'/css/general_foundicons_ie7.css').'">
                                <![endif]-->');*/
        $this->template
            ->append_css('module::font-awesome.min.css')
            ->prepend_metadata('<!--[if lt IE 8]>
                                <link rel="stylesheet" href="'.site_url($this->modpath.'/css/font-awesome-ie7.min.css').'">
                                <![endif]-->');

    }

    public function index($offset = 0)
    {
        $count = $this->db->count_all_results('pyroforms');

        // Pagination junk
        $per_page = '5';
        $pagination = create_pagination('admin/pyroforms/index/', $count, $per_page, 4);

        $offset = $pagination['current_page'] > 0 ? $pagination['limit'] : 0;

        $this->db
            ->select('f.*, IFNULL(COUNT(e.id), 0) AS `entry_count`',FALSE)
			->from('pyroforms f')
			->join('pyroforms_entry e','f.id = e.form_id', 'left')
			->group_by('f.id')
            ->limit($per_page, $pagination['offset']);
            
        $forms = $this->db->get()->result();


        //build output
        $this->template->set('frm', $forms)->set('pagination', $pagination)->build('admin/index');
    }

    public function newform()
    {
        $this->load->model('pyroforms_m');
        $this->form_validation->set_rules($this->validation_rules['forms']);

        //form valid lets do something with the data
        if ($this->form_validation->run())
        {
            //prep the data
            $data = array(
                'frmName'           => $this->input->post('frmName'),
                'frmSlug'           => $this->input->post('frmSlug'),
                'frmInfo'           => $this->input->post('frmInfo'),
                'frmResponse'       => $this->input->post('frmResponse'),
                'frmNotifyTemplate' => $this->input->post('frmNotifyTemplate'),
                'frmNotifyTitle'    => $this->input->post('frmNotifyTitle'),
                'frmNotifyBody'     => $this->input->post('frmNotifyBody'),
                'send_to'           => $this->input->post('send_to'),
                'reply_to'          => $this->input->post('reply_to'),
                'layout'            => serialize($this->input->post('frmWrap')),
                'is_ajax'           => $this->input->post('is_ajax'),
                'file_types'        => $this->input->post('file_types'),
                'file_maxsize'      => $this->input->post('file_maxsize') ? $this->input->post('file_maxsize') : 0,
                'track_pages'       => $this->input->post('track_pages'),
                'track_events'      => $this->input->post('track_events')
            );
            //update data
            if ($this->db->insert('pyroforms', $data))
            {
                $message = lang('pf_insert_success');
                $status = 'success';
                $this->session->set_flashdata($status, $message);
                redirect('admin/pyroforms');
            } else
            {
                $message = lang('pf_insert_error');
                $status = 'error';
            }
            $this->session->set_flashdata($status, $message);
            redirect('admin/pyroforms/newform');
        }

        $frm = new stdClass;
        
        // Required for validation
        foreach ($this->validation_rules['forms'] as $rule)
        {
            $frm->{$rule['field']} = $this->input->post($rule['field']);
        }

        if (empty($frm->reply_to))
        {
            $frm->reply_to = Settings::get('contact_email');
        }
        if (empty($frm->layout))
        {
            $this->load->config('config');

            $frm->layout = $this->config->item('pf_defaults');
        }
        $frm->layout = form_prep($frm->layout);
        //$this->session->set_flashdata('error', validation_errors());
        $this->template->set('frm', $frm)->set('templates', $this->pyroforms_m->get_email_templates())->append_metadata($this->load->view('fragments/wysiwyg', '', TRUE))->append_js('jquery/jquery.tagsinput.js')->append_css('jquery/jquery.tagsinput.css')->build('admin/form');
    }

    public function edit($id)
    {
        $this->load->model('pyroforms_m');
        $this->load->library('Pyroforms');
        $this->validation_rules['forms']['slug']['rules'] = 'trim|required|alpha_dot_dash|max_length[100]|callback__check_slug[' . $id . ']';
        $this->form_validation->set_rules($this->validation_rules['forms']);

        //form valid lets do something with the data
        if ($this->form_validation->run())
        {
            /*
             * @todo Add variables for template type.
             */
            $data = array(
                'frmName'           => $this->input->post('frmName'),
                'frmSlug'           => $this->input->post('frmSlug'),
                'frmInfo'           => $this->input->post('frmInfo'),
                'frmResponse'       => $this->input->post('frmResponse'),
                'frmNotifyTemplate' => $this->input->post('frmNotifyTemplate'),
                'frmNotifyTitle'    => $this->input->post('frmNotifyTitle'),
                'frmNotifyBody'     => $this->input->post('frmNotifyBody'),
                'send_to'           => $this->input->post('send_to'),
                'reply_to'          => $this->input->post('reply_to'),
                'layout'            => serialize($this->input->post('frmWrap')),
                'is_ajax'           => $this->input->post('is_ajax'),
                'file_types'        => $this->input->post('file_types'),
                'file_maxsize'      => $this->input->post('file_maxsize') ? $this->input->post('file_maxsize') : 0,
                'track_pages'       => $this->input->post('track_pages'),
                'track_events'      => $this->input->post('track_events')
            );

            //update data
            if ($this->db->update('pyroforms', $data, 'id = ' . $id))
            {
                $message = lang('pf_update_success');
                $status = 'success';
                $this->session->set_flashdata($status, $message);
                redirect('admin/pyroforms');
            } else
            {
                $message = lang('pf_update_error');
                $status = 'error';
                $this->session->set_flashdata($status, $message);
                //redirect('admin/pyroforms/newform');
            }
        }
        // Required for validation
        if ($_POST)
        {
            foreach ($this->validation_rules['forms'] as $rule)
            {
                $frm->{$rule['field']} = $this->input->post($rule['field']);
            }
        } else {
            $frm = $this->db->get_where('pyroforms', array('id' => $id))->row();
        }
        
        if (empty($frm->reply_to))
        {
            $frm->reply_to = Settings::get('contact_email');
        }

        if (!empty($frm->layout))
        {
            $frm->layout = unserialize($frm->layout);
        }

        $frm->layout = pyroforms::merge_arrays(pyroforms::cfg('all'), $frm->layout);

        $frm->layout = form_prep($frm->layout);
        $this->template
            ->set('frm', $frm)
            ->set('templates', $this->pyroforms_m->get_email_templates())
            ->append_metadata($this->load->view('fragments/wysiwyg', '', TRUE))
            ->append_js('jquery/jquery.tagsinput.js')
            ->append_css('jquery/jquery.tagsinput.css')
            ->build('admin/form');
    }

    public function manage($id)
    {

        $this->load->model('pyroforms_m');
		
        //Save form
        if ($this->_is_ajax() and $_POST)
        {
            die(json_encode(array(
                'status' => 'ok',
                'ids' => $this->pyroforms_m->_save_fields($this->input->post('fields'), $id)
            )));
        }

        //just show the form view
        else
        {
            $frm = $this->db
                ->order_by('fldOrder')
                ->get_where('pyroforms_fields', array('frm_id' => $id))
                ->result_array();

            if (!empty($frm))
            {
                $toView = array();
                foreach ($frm as $field)
                {
                    $field['fldID'] = $field['id'];
                    empty($field['fldPrep']) or $field['fldPrep'] = unserialize($field['fldPrep']);

                    if (!empty($field['fldValidation']))
                    {
                        $field['fldValidation'] = unserialize($field['fldValidation']);
                        foreach ($field['fldValidation'] as $key)
                        {
                            if (is_array($key))
                            {
                                $field[$key['type']] = $key['val'];
                            }
                            else
                            {
                                $field[$key] = 1;
                            }
                        }
                    }
                    if (!empty($field['fldData']))
                    {
                        $field['rows'] = unserialize($field['fldData']);
                    }
                    $toView[] = $field;
                }
                $this->template->set('frm', $toView);
            }
			$this->template->set('moduri', $this->module_details['path'].'/');

            // Load the form view
            $this->template->append_css('module::pf-global.css')->set('form_id',$id)->build('admin/create');
        }
    }

    public function remove($id)
    {

        if ($this->db->delete('pyroforms_fields', array('id' => $id)))
        {
            die(json_encode(array('status' => 'ok', 'ids' => $id)));
        }
    }

    public function delete()
    {
        $ids = $this->input->post('action_to');

        if (!empty($ids))
        {
            //counter
            $i = 0;

            $count = count($ids);

            //loop through each id and try to delete
            foreach ($ids as $id)
            {
                //delete success
                if ($this->db->delete('pyroforms', array('id' => $id)) and
                    $this->db->delete('pyroforms_fields', array('frm_id' => $id)) and
                    $this->db->delete('pyroforms_entry', array('form_id' => $id)))
                {
                    $i++;
                }
            }
            $this->session->set_flashdata('success', sprintf(lang('pf_delete_success'), $i, $count));
        } else
        {
            //oops no ids.. ids required here.
            $this->session->set_flashdata('notice', lang('pf_action_empty'));
        }
        redirect('admin/pyroforms');
    }

    public function delete_logs()
    {
        $ids = $this->input->post('action_to');

        if (!empty($ids))
        {
            //counter
            $i = 0;

            // $count = count($ids);

            //loop through each id and try to delete
            foreach ($ids as $id)
            {
                //delete success
                if ($this->db->delete('pyroforms_entry', array('id' => $id)))
                {
                    $i++;
                }
            }
            $this->session->set_flashdata('success', 'Deleted record(s)');
        }
        redirect('admin/pyroforms/logs/' . $this->input->post('form_id'));
    }

    /**
     * Helper method to allow one form to controll multiple actions
     *
     * @access public
     * @return void
     */
    public function action()
    {
        if ($this->input->post('btnAction') == 'delete')
        {
            $this->delete();
        }
    }

    public function logs_action()
    {
        if ($this->input->post('btnAction') == 'delete')
        {
            $this->delete_logs();
        }
    }



    function logs($id)
    {

        $entry = $this->db
            ->select('l.*, f.id as form_id, f.frmName as formname, f.send_to,u.username', FALSE)
            ->from('pyroforms_entry l')
            ->join('users u', 'u.id=l.user_id', 'left')
            ->join('pyroforms f', 'f.id=l.form_id', 'left')
            ->order_by('l.created_on')
            ->where('l.form_id', $id)->get()->result();


        $fields = $this->db->select('group_concat(fldName) AS ids, group_concat(fldLabel) AS labels, group_concat(fldType) AS types')->where('fldType !=', 'submit')->where('fldType !=', 'button')->where('frm_id', $id)->get('pyroforms_fields')->row();

        $row    = $fields->labels . "\n";
        $ids    = explode(',', $fields->ids);
        $labels = explode(',', $fields->labels);
        $types  = explode(',', $fields->types);
        $types  = array_combine($ids, $types);
        $fields = array_combine($ids, $labels);
        
        $this->template
            ->append_css('module::jquery.dataTables.css')
            ->append_js('module::jquery.dataTables.min.js')
            ->set('form_id', $id)->set('fields', $fields)->set('types', $types)->set('entries', $entry)->build('admin/datatables');
    }

    function export($id)
    {

        $entry = $this->db
            ->select('l.*, f.id as form_id, f.frmName as formname, f.send_to,u.username', FALSE)
            ->from('pyroforms_entry l')
            ->join('users u', 'u.id=l.user_id', 'left')
            ->join('pyroforms f', 'f.id=l.form_id', 'left')
            ->order_by('l.created_on')
            ->where('l.form_id', $id)->get()->result();


        $fields = $this->db->select('group_concat(fldName) AS ids, group_concat(fldLabel) AS labels, group_concat(fldType) AS types')->where('fldType !=', 'submit')->where('fldType !=', 'button')->where('frm_id', $id)->get('pyroforms_fields')->row();

        $row    = $fields->labels . "\n";
        $ids    = explode(',', $fields->ids);
        $labels = explode(',', $fields->labels);
        $types  = explode(',', $fields->types);
        $types  = array_combine($ids, $types);
        $fields = array_combine($ids, $labels);
        
        
        
        foreach ($entry as $e)
        {
            $e->data = unserialize($e->data);
            $new = array();
            foreach ($fields as $k => $l)
            {
                $new[$k] = (isset($e->data[$k])) ? $e->data[$k] : '';
            }
            $row .= implode(',', $new) . "\n";
        }
        echo '<pre>'.$row."</pre>";
       // $this->template->set('fields', $fields)->set('entry', $entry)->build('admin/details');
    }
    function manage_views($id)
    {
        $fields = $this->input->post('fields_v');

        if (!empty($fields))
        {
            $this->db->where('id', $id)->update('pyroforms', array('frmViewFields'=>serialize($fields)));
            redirect('admin/pyroforms');
        }
        else {
            $selected = $this->db
                ->select('frmViewFields')
                ->where('id', $id)
                ->get('pyroforms')
                ->row()->frmViewFields;

            $selected = (!empty($selected)) ? unserialize($selected) : array();
            
            $fields = $this->db
                ->from('pyroforms_fields')
                ->where_not_in('fldType', array('submit', 'button', 'captcha', 'paragraph'))
                ->where('frm_id', $id)
                ->get()->result();

            $this->template
                ->set('fields', $fields)
                ->set('selected', $selected)
                ->build('admin/form_views');
        }
      
    }

    public function _check_slug($slug, $id = false)
    {
        $this->form_validation->set_message('_check_slug', 'Slug already exists');
        $id and $this->db->where('id !=', $id);

        return ($this->db->where('frmSlug', $slug)->count_all_results('pyroforms') == 0);
    }

    protected function _is_ajax()
    {
        return (isset($_SERVER['HTTP_X_REQUESTED_WITH']) and $_SERVER['HTTP_X_REQUESTED_WITH'] == 'XMLHttpRequest')
            ? TRUE
            : FALSE;
    }

}
