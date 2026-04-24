<?php defined('BASEPATH') or exit('No direct script access allowed');

class Pyroforms_m extends MY_Model {

    private $prop_map = array(
        'fldType'           => 'type',
        'fldLabel'          => 'label',
        'trim'              => 'trim',
        'fldValidIsset'     => 'isset',
        'fldValidReq'       => 'required',
        'fldValidTrim'      => 'trim',
        // xss_clean is deprecated and was never safe to rely on: it mutates
        // input in ways that still leave room for context-dependent XSS in
        // rich-HTML fields and can break legitimate content. Views that render
        // stored form data now html_escape() it at output instead.
        'fldValidUrl'       => 'prep_url',
        'fldValidEmail'     => 'valid_email',
        'fldValidAlpha'     => 'alpha',
        'fldValidAlphaDash' => 'alpha_dash',
        'fldValidNumeric'   => 'numeric',
        'fldValidMinLen'    => 'min_length',
        'fldValidMaxLen'    => 'max_length'
    );

    function __construct()
    {
        parent::__construct();
    }

    /**
     * Save/Update form fields
     * @param  array    $data [description]
     * @param  int      $id   [description]
     * @return array
     */
    public function _save_fields($data, $id)
    {

        //collection of new ids
        $new_ids = array(); 
        foreach ($data as $order => $field)
        {
            //Collect field attributes
            $toDb['frm_id']          = $id;
            $toDb['fldName']         = $field['fldName'];
            $toDb['fldLabel']        = $field['fldLabel'];
            $toDb['fldLabelVisible'] = ($field['fldLabelVisible'] !== 'false') ? 1 : 0;
            $toDb['fldType']         = $field['fldType'];
            $toDb['fldInfo']         = $field['fldInfo'];
            $toDb['fldData']         = NULL;

            //Default field value
            $toDb['fldDefault'] = (!empty($field['fldValue'])) ? $field['fldValue'] : '';

            //Skip Prep for certain fields
            if (in_array($field['fldType'], array('dropdown', 'checkbox', 'radio', 'button', 'submit', 'file', 'captcha')))
            {
                $toDb['fldPrep'] = NULL;
            }

            //Serialize field prep
            if (!empty($field['fldPrep']))
            {
                $toDb['fldPrep'] = serialize($field['fldPrep']);
            }
            
            // Serialize validation rules
            $toDb['fldValidation'] = (!empty($field['fldValidation'])) ? serialize($field['fldValidation']) : null;
            
            // Serialize options data
            if (!empty($field['data']))
            {
                foreach ($field['data'] as $k => $d)
                {
                    if (empty($d['value']) and !empty($d['label']))
                    {
                        $field['data'][$k]['value'] = $d['label'];
                    }
                    if (empty($d['label']) and !empty($d['value']))
                    {
                        $field['data'][$k]['label'] = $d['value'];
                    }
                }
                $toDb['fldData'] = serialize($field['data']);
            }

            //Set the order for this field
            $toDb['fldOrder'] = $order;

            //Insert or Update field 
            if (empty($field['fldId']))
            {
                $this->db->insert('pyroforms_fields', $toDb);
                $new_ids[] = $this->db->insert_id();
            }
            else {
                $this->db->where('id', $field['fldId'])->set($toDb)->update('pyroforms_fields');
            }
        }
        //return only newly added IDs
        return $new_ids;
    }

    /**
     * Parse and save form data
     * @param int   $frm    [description]
     * @param array $input  [description]
     */
    public function add_entry($frm, $input)
    {
        // Grab form by ID
        $result = $this->db->from('pyroforms')->where('id', $input['form_id'])->get()->row();

        //Default response returned
        $resp = array(
            'status' => false,
            'msg' => ''
        );

        /**
         * ##! Handle uploads ##
         *
         */
        if (!empty($_FILES))
        {
            $this->load->library('upload');
            
            //Set the upload path
            $path = UPLOAD_PATH . 'form_attachments/' . $result->id;

            //Upload class config
            $cfg['allowed_types'] = str_replace(array(',','.'), array('|',''), $result->file_types);
            $cfg['max_size']      = $result->file_maxsize;
            $cfg['upload_path']   = $path;

            //Create dir when needed
            is_dir($path) OR @mkdir($path, 0777, true);

            //Loop thru all the uploads
            foreach ($_FILES AS $form => $file)
            {
                if (!empty($file['name']))
                {
                    // Make sure the upload matches a field
                    //if ( ! array_key_exists($form, $form_meta)) break;

                    $this->upload->initialize($cfg);
                    $this->upload->do_upload($form);

                    //error uploding
                    if ($this->upload->display_errors())
                    {
                        //halt and return error array
                        return array(
                            'status' => false,
                            'msg'    => pyroforms::cfg('error-open') . $this->upload->display_errors() . pyroforms::cfg('error-close')
                        );
                    }
                    else {
                        $result_data = $this->upload->data();
                        
                        //add to email attachment to send later..
                        $data['attach'][$result_data['file_name']] = $result_data['full_path'];
                        $frmData[$form] = $result_data['file_name'];
                    }
                }
            }
        }

        //loop thru the form data skipping non-data and file elements
        foreach ($frm as $name => $field)
        {
            //skip files, it gets set while processing the upload
            if ($field['type'] == 'submit' OR
                $field['type'] == 'button' OR
                $field['type'] == 'file') continue;
            
            else $frmData[$name] = $this->input->post($name);
        }

        //form data for insert
        $form_data = array(
            'form_id'    => $input['form_id'],
            'user_id'    => isset($this->current_user->id) ? $this->current_user->id : 0,
            'name'       => isset($input['name']) ? substr($input['name'], 0, 255) : '',
            'data'       => serialize($frmData),
            'uagent'     => $this->agent->browser() . ' ' . $this->agent->version(),
            'ip'         => $this->input->ip_address(),
            'os'         => $this->agent->platform(),
            'created_on' => time()
        );
        //unset($frmData);

        //Insert entry
        $return = $this->db->insert('pyroforms_entry', $form_data);
        
        //check for error and return response
        if ($return === false) return $resp;

        //All done with this after DB insert
        unset($form_data['data']);

        //Do we have a notification template set? if so use it!
        if ($result->frmNotifyTemplate != 0)
        {
            $data['template'] = $result->frmNotifyTemplate;
        } else
        {
            //Set vars for email
            $data['subject'] = $result->frmNotifyTitle;
            $data['body']    = $result->frmNotifyBody;
        }
        
        //fetch field names and labels
        $fields = $this->db->select('group_concat(fldName SEPARATOR \'^%\') AS ids, group_concat(fldLabel SEPARATOR \'^%\') AS labels')->where('fldType !=', 'submit')->or_where('fldType !=', 'button')->get('pyroforms_fields')->result_array();

        //Create arrays
        $ids    = explode('^%', $fields[0]['ids']);
        $labels = explode('^%', $fields[0]['labels']);
        
        //Combine arrays, resulting in array( id => label)
        $labels = array_combine($ids, $labels);

        foreach ($frmData as $k => $f)
        {
            // Join together multiple values (multi select and checkboxes)
            if (is_array($f))
            {
                $f = implode(', ', $f);
            }
            
            $frm_data['value'][$k] = $f;
            $frm_data['label'][$k] = $labels[$k];

            $frm_data['form_entry'][$k] = array(
                'label' => $labels[$k],
                'value' => $f
            );
        }
        
        // Prepare data for parser -------
        $form_title = $this->db->select('frmName')->where('id', $input['form_id'])->get('pyroforms')->row()->frmName;

        $frm_data['form_name']   = $form_title;
        $frm_data['username']    = isset($this->current_user->username) ? $this->current_user->username : 'guest';
        $data['to']              = $result->send_to;
        $data['from']            = $result->reply_to;
        $form_data['created_on'] = format_date($form_data['created_on']);
        $form_data               = $form_data + $frm_data;

        // Send Email --------------------
        if (!empty($result->send_to))
        {
            //Someone to reply to?
            if (!empty($result->reply_to))
            {
                $data['reply_to'] = $result->reply_to;
            }
            
            //Send the email
            $this->send_email($data, $form_data);
        }

        //Parse response
        $_msg = (empty($result->frmResponse)) ? '' : pyroforms::cfg('success-open') . $this->parser->parse_string($result->frmResponse, $form_data, true) . pyroforms::cfg('success-close');

        return array(
            'status' => true,
            'msg'    => $_msg
        );
    }

    /**
     * [send_email description]
     * @param  array  $data      [description]
     * @param  array  $form_data [description]
     * @return [type]            [description]
     */
    function send_email($data = array(), $form_data = array())
    {

        $data['lang']     = isset($data['lang']) ? $data['lang'] : Settings::get('site_lang');
        $data['from']     = isset($data['from']) ? $data['from'] : Settings::get('server_email');
        $data['reply_to'] = isset($data['reply_to']) ? $data['reply_to'] : $data['from'];
        $data['to']       = isset($data['to']) ? $data['to'] : Settings::get('contact_email');

        //$this->email->from($data['from'], $data['name']);
        $this->email->from($data['from']);

        $this->email->reply_to($this->parser->parse_string($data['reply_to'], $form_data, true));
        $this->email->to($data['to']);

        // Are we using an Email Template?
        if (!empty($data['template']))
        {
            $this->load->model('templates/email_templates_m');

            //get all email templates
            $templates = $this->email_templates_m->get_templates($data['template']);
            $subject   = array_key_exists($lang, $templates) ? $templates[$lang]->subject : $templates['en']->subject;
            $body      = array_key_exists($lang, $templates) ? $templates[$lang]->body : $templates['en']->body;
        }
        else
        {
            // Nope it's either our default template or custom
            $subject = $data['subject'];
            $body    = $data['body'];
        }
        $subject = $this->parser->parse_string($subject, $form_data, true);
        $body    = $this->parser->parse_string($body, $form_data, true);

        $this->email
            ->subject($subject)
            ->message($body);

        if (isset($data['attach']))
        {
            foreach ($data['attach'] AS $attachment)
            {
                $this->email->attach($attachment);
            }
        }

        return $this->email->send();
    }

    /**
     * [form_display description]
     * @param  [type]  $attr         [description]
     * @param  boolean $custom_array [description]
     * @return [type]                [description]
     */
    function form_display($attr, $custom_array=false)
    {
        
        $id = $attr['id'];

        if (!is_numeric($id))
        {
            $slug = $id;
            if ( !($form = $this->db->where('frmSlug', $id)->get('pyroforms', 1)->row()) )
            {
                return false;
            }
            $id = $form->id;
        }
        else
        {
            if ( !($form = $this->db->from('pyroforms')->where('id', $id)->get()->row()) )
            {
                return false;
            }

            $slug = $form->frmSlug;
        }
        
        empty($form->layout) or $form->layout = unserialize($form->layout);

        // fetch fields from the db
        $frm = $this->db
            ->order_by('fldOrder')
            ->get_where('pyroforms_fields', array('frm_id' => $id))
            ->result_array();

        // generate ID attribute from slug
        $attr['id'] = 'frm-'.$form->frmSlug;

        if (!empty($frm))
        {
            $toView      = array();
            $has_captcha = false;

            // loop thru the fields
            foreach ($frm as $field)
            {
                $fld = array();

                if (!empty($field['fldPrep']))
                {
                    $_prep = unserialize($field['fldPrep']);
                    if (is_array($_prep))
                    {
                        foreach ($_prep as $p)
                        {
                            $fld['validation'][] = $this->prop_map[$p];
                        }
                    }
                }

                //Field Validation
                if (!empty($field['fldValidation']))
                {
                    $_valid = unserialize($field['fldValidation']);
                    foreach ($_valid as $v)
                    {
                        if (is_array($v))
                        {
                            $fld['validation'][] = $this->prop_map[$v['type']] . '[' . $v['val'] . ']';
                        }
                        else
                        {
                            $fld['validation'][] = $this->prop_map[$v];
                        }
                    }
                }

                // create validation string
                if (!empty($fld['validation']))
                {
                    $fld['validation'] = implode('|', $fld['validation']);

                }
                else {
                    unset($fld['validation']);
                }

                if ($field['fldType'] == 'captcha')
                {
                    $fld['validation'] .= (!empty($fld['validation'])) ? '|' : '';
                    $fld['validation'] .= 'callback_match_captcha';
                    $has_captcha = true;
                    $captcha_field = $field;
                }

                // Parse groups field data
                if (!empty($field['fldData']))
                {
                    $field['fldData'] = unserialize($field['fldData']);

                    // Radio/ Checkbox options
                    if ($field['fldType'] == 'radio' or $field['fldType'] == 'checkbox')
                    {
                        foreach ($field['fldData'] as $o)
                        {
                            $itm = array();
                            if ($o['selected'] == 'true')
                            {
                                $itm['checked'] = 'checked';
                                $fld['value']   = $o['value'];
                            }
                            $itm['label']   = $o['label'];
                            $itm['value']   = $o['value'];
                            $fld['items'][] = $itm;
                        }
                    }
                    // Select options
                    else
                    {
                        foreach ($field['fldData'] as $o)
                        {
                            if ($o['selected'] == 'true')
                            {
                                $fld['selected'][] = $o['value'];
                            }
                            $fld['options'][$o['value']] = $o['label'];
                        }
                    }
                }
                $fld['type'] = ($field['fldType'] == 'dropdown') ? 'select' : $field['fldType'];
                
                if (!empty($field['fldDefault'])) $fld['value'] = $field['fldDefault'];

                //title
                $fld['title']         = $field['fldInfo'];
                //temp flag for label visibility
                $fld['label_visible'] = (bool)$field['fldLabelVisible'];
                $fld['label']         = $field['fldLabel'];

                // set CSS class attributes for button elements
                if ($field['fldType'] == 'submit' or $field['fldType'] == 'button')
                {
                    $fld['value'] = $fld['label'];
                    if ($field['fldType'] == 'submit')
                    {
                        $fld['class'] = 'btn button primary';
                    } else
                    {
                        $fld['class'] = 'btn button secondary';
                    }

                    unset($fld['validation']);
                    unset($fld['label_visible']);
                }
                else
                {

                }
                $fld['id'] = $field['fldName'];
                $toView[$field['fldName']] = $fld;
            }
            
            // Add Javascript
            pyroforms::add_js('forms.js');

            // is_ajax
            if ($form->is_ajax)
            {
                $attr['class'] = 'pyroforms-ajax';
            }

            //#! Setup Tracking data
            $track = array('action' => 'View', 'title'=>$form->frmName);

            //add form
            pyroforms::add_form($slug, $attr, $toView, $form->layout);
            
            $re_msg = '';

            $resp = array('msg'=>'');

            //Fake vars for validating required file
            if (!empty($_FILES))
            {
                foreach($_FILES as $_fld => $_f)
                {
                    isset($_POST[$_fld]) or $_POST[$_fld] = $_f['name'];
                }
            }
            if (pyroforms::validate($slug) and $this->_not_referral())
            {
                $reply           = $this->add_entry($toView, array('form_id' => $id));             
                $resp['msg']     = $reply['msg'];
                $resp['status']  = ($reply['status'] === true) ? 'ok' : 'err';
                $track['action'] = ($reply['status'] === true) ? 'Success' : 'Error';
                $resp['track']   = $track;
                //Check for redirect page

                //Redirect to "thank you page"

                // json response
                if ($this->_is_ajax())
                {
                    die(json_encode($resp));
                }
            } else
            {
                pyroforms::repopulate($slug);
            }
        }

        if ($has_captcha){
            $resp['captcha'] = pyroforms::makeCaptcha();
        }
        // Check for errors
        if ($errors = pyroforms::all_errors())
        {
            $track['action'] = 'Error';
            if ($this->_not_referral())
            {
                $resp['msg'] .= pyroforms::cfg('error-open'). ''.$errors.pyroforms::cfg('error-close');
            }
        }
        
        $resp['track'] = $track;

        // Ajax Response
        if ($this->_is_ajax())
        {
            $resp['status'] = 'err';
            die(json_encode($resp));
        }
        // Return array for lex parser
        else
        {
            if ($custom_array)
            {
                $f = pyroforms::form($slug, true);
                return array(
                    'header'        => $f['header'] . $resp['msg'] . '<div id="frm-error"></div>',
                    'footer'        => $f['footer'],
                    'form_fields'   => $f['fields'],
                    'form_name'     => $form->frmName,
                    'form_info'     => $form->frmInfo,
                    'form_tracking' => Pyroforms::tracking_code($track, $form)
                );
            } else {
                return array(
                    'form_fields'   => $resp['msg'] . '<div id="frm-error"></div>' . pyroforms::form($slug),
                    'form_name'     => $form->frmName,
                    'form_info'     => $form->frmInfo,
                    'form_tracking' => Pyroforms::tracking_code($track, $form)
                );
            }
        }
    }

    /**
     * Captcha validation
     * @param   string   $word   string to match the stored captcha
     * @return  bool
     */
    function match_captcha($word)
    {
        $pf_captcha = $this->session->userdata('pf_captcha');
        $pf_captcha = unserialize($pf_captcha);
        if (is_array($pf_captcha))
        {
            if ($pf_captcha['word'] === $word)
            {
                $this->session->unset_userdata('pf_captcha');
                return true;
            }
        }

        $this->form_validation->set_message('match_captcha', 'The %s field must match the captcha image.');
        return false;
    }
    
    /**
     * Email templates dropdown array
     * @param  string $lang template language
     * @return array
     */
    public function get_email_templates($lang = 'en')
    {
        $templates = array(lang('global:select-none'));
        $results   = $this->db
                        ->select('slug, name')
                        ->from('email_templates')
                        ->where('lang', $lang)
                        ->get()
                        ->result();


        foreach ($results as $row)
        {
            $templates[$row->slug] = $row->name;
        }

        return $templates;
    }

    private function _is_ajax()
    {
        return (isset($_SERVER['HTTP_X_REQUESTED_WITH']) and $_SERVER['HTTP_X_REQUESTED_WITH'] == 'XMLHttpRequest') ? true : false;
    }

    private function _not_referral(){
        return !$this->agent->is_referral() or $_SERVER['HTTP_REFERER'] == current_url();
    }
}

/* End of file pyroforms_m.php */
