<?php defined('BASEPATH') or exit('No direct script access allowed');

/**
 * Pyroforms Lib file used "Formation" as a skeleton, the license is below.
 *
 * Formation
 * @package		Formation
 * @author		Dan Horrigan <http://dhorrigan.com>
 * @license		Apache License v2.0
 * @copyright	2010 Dan Horrigan
 *
 * Licensed under the Apache License, Version 2.0 (the "License");
 * you may not use this file except in compliance with the License.
 * You may obtain a copy of the License at
 *
 *     http://www.apache.org/licenses/LICENSE-2.0
 *
 * Unless required by applicable law or agreed to in writing, software
 * distributed under the License is distributed on an "AS IS" BASIS,
 * WITHOUT WARRANTIES or CONDITIONS OF ANY KIND, either express or implied.
 * See the License for the specific language governing permissions and
 * limitations under the License.
 */


class Pyroforms {

    private static $_ci;
    private static $_config = array();
    private static $_forms = array();
    private static $_validation = array();
    private static $_assets_loaded = array();

    private static $_valid_inputs = array(
        'button', 'checkbox', 'select', 'color', 'date', 'datetime',
        'datetime-local', 'email', 'file', 'hidden', 'image',
        'month', 'number', 'password', 'radio', 'range',
        'reset', 'search', 'submit', 'tel', 'text','textarea', 'time','captcha',
        'url', 'week', 'paragraph'
    );

    private static $_valid_extras = array('paragraph');
    private static $_valid_actions = array('button', 'reset', 'search', 'submit');
	
	public static $mod_path = '';
	


    public function __construct()
    {
        self::$_ci = & get_instance();
        self::$_ci->load->library('form_validation');
        self::$_ci->load->config('pyroforms/config');

		// Load up default config
        $default_config = self::$_ci->config->item('pf_defaults');

        if (is_array($default_config))
        {
            self::add_config($default_config);
        }
        else
        {
            show_error('Pyroforms config file is missing.');
        }
		
		// Ugly (But it works) Hack to get assets working from a plugin
		self::$mod_path = str_replace('/index.php','',site_url(trim(str_replace(array(getcwd(), '\\'), array('', '/'), realpath(dirname(__FILE__) . '/../'). DIRECTORY_SEPARATOR), '/'))). '/';
    }

	public static function add_css($file)
	{
        if (!in_array('css', self::$_assets_loaded)) {
            self::$_assets_loaded[] = 'css';
            self::$_ci->template->prepend_metadata('<link href="'.self::$mod_path. 'css/'.$file.'" rel="stylesheet" type="text/css" />');
        }
	}

	public static function add_js($file)
	{
        if (!in_array('js', self::$_assets_loaded))
        {
            self::$_assets_loaded[] = 'js';
            self::$_ci->template->append_metadata('<script src="'.self::$mod_path. 'js/'.$file.'" type="text/javascript"></script>');
        }
	}

    public static function cfg($key)
    {
        if ($key == 'all') return self::$_config;
        return (isset(self::$_config[$key])) ? self::$_config[$key] : NULL;
    }
    public static function add_config($config = false)
    {
        if (is_array($config))
        {
            self::$_config = self::merge_arrays(self::$_config, $config);
            // Add the forms from the config array
            if (isset(self::$_config['forms']) and is_array(self::$_config['forms']))
            {
                unset(self::$_config['forms']);
            }
        }
    }

    static function merge_arrays($a1, $a2)
    {
        foreach ($a2 as $k => $v)
        {
            if (array_key_exists($k, $a1) and is_array($v))
                $a1[$k] = self::merge_arrays($a1[$k], $a2[$k]);
            else
                $a1[$k] = $v;
        }
        return $a1;
    }

    public static function add_form($form_name, $attributes = array(), $fields = array(), $config = false)
    {
        if (is_array($config))
            self::add_config($config);
			
        self::$_forms[$form_name]['attributes'] = (array) $attributes;
        self::add_fields($form_name, $fields);
    }

    public static function get_form_array($form_name)
    {
        if (!self::form_exists($form_name))
        {
            return false;
        }
        return self::$_forms[$form_name];
    }

    private static function get_validation_array($form_name)
    {
        if (self::has_validation($form_name))
        {
            return self::$_validation[$form_name];
        }
        return false;
    }

    private static function has_validation($form_name)
    {
        return (isset(self::$_validation[$form_name])) ? TRUE : false;
    }

    public static function add_field($frm_name, $fld_name, $attr = array())
    {

        if (is_array($fld_name))
        {
            foreach ($fld_name as $name => $attributes)
            {
                self::add_field($frm_name, $name, $attributes);
            }
        }
        else
        {

            if (in_array($attr['type'], self::$_valid_inputs))
            {
                if (in_array($attr['type'], self::$_valid_actions))
                {
                    self::$_forms[$frm_name]['actions'][$fld_name] = $attr;
                }
                else
                {

                    if (isset($attr['validation']))
                    {
                        self::$_validation[$frm_name][$fld_name]['field'] = ($attr['type'] == 'checkbox') ? $fld_name . '[]':$fld_name;
                        self::$_validation[$frm_name][$fld_name]['label'] = $attr['label'];
                        self::$_validation[$frm_name][$fld_name]['rules'] = $attr['validation'];

                        unset($attr['validation']);
                    }

                    self::$_forms[$frm_name]['fields'][$fld_name] = $attr;

                    if ($attr['type'] == 'file')
                    {
                        self::$_forms[$frm_name]['attributes']['enctype'] = 'multipart/form-data';
                    }
                }
            } elseif (in_array($attr['type'], self::$_valid_extras)){

            }
        }
    }


    public static function add_fields($form_name, $fields)
    {
            self::add_field($form_name, $fields);
    }

    public static function form_exists($form_name)
    {
        return isset(self::$_forms[$form_name]);
    }

    public static function field_exists($form_name, $field_name)
    {
        return isset(self::$_forms[$form_name]['fields'][$field_name]);
    }

    public static function form($form_name, $output_array=false)
    {
 
        if ($output_array)
        {
            $return            = self::get_fields_arrays($form_name);
            $return['header']  = self::open($form_name) . $return['header'];
            $return['footer'] .= self::close();
        } else {
            $return  = self::open($form_name);
            $return .= self::get_fields($form_name);
            $return .= self::close();
        }

        self::$_ci->load->library('jquery_validation');
        if ($valid = self::get_validation_array($form_name))
        {
            self::$_ci->jquery_validation->set_rules($valid);
            if (is_array($return))
            {
                $return['footer'] .= self::$_ci->jquery_validation->run('#frm-'.$form_name);
            }
            else
            {
                $return .= self::$_ci->jquery_validation->run('#frm-'.$form_name);
            }
        }

        return $return;
    }

    public static function field($name, $properties = array(), $form_name = NULL)
    {

        $return = '';

        if (!isset($properties['name']))
        {
            $properties['name'] = $name;
        }
        if (!isset($properties['value']))
        {
            $properties['value'] = '';
        }

        if (isset($properties['title']))
        {
            $_info = $properties['title'];
            $properties['title'] = strip_tags($properties['title']);
        } else {
            $_info = '';
        }

        $required = false;
        if (isset(self::$_validation[$form_name]))
        {
            foreach (self::$_validation[$form_name] as $rule)
            {
                if ($rule['field'] == $properties['name'] and $rule['rules'] and stristr($rule['rules'], 'required') !== false)
                {
                    $required = TRUE;
                }
            }
        }
        $is_action = in_array($properties['type'], self::$_valid_actions);
        if (!$is_action)
        {
            if (self::$_config['info-pos'] == 'before-field')
			{
                $return .= self::$_config['info-open'] . $_info . self::$_config['info-close'];
			}
			
            $return .= self::_open_field($properties['type'], $required);
        }
		
        $_label = (!isset($properties['label_visible']) or $properties['label_visible']) ? self::label($properties['label'], $name, $required, $_info) : '';


		unset($properties['label_visible']);

        switch ($properties['type'])
        {
            case 'submit': case 'clear': case 'cancel': case 'button': case 'hidden':
                $return .= self::input($properties);
                break;
            
            case 'captcha':
                $return .= $_label;
                $return .= '<div class="frm-captcha-img">'.self::makeCaptcha().'</div>';
                $return .= self::input($properties);
            break;

            case 'radio': case 'checkbox':

                $return .= $_label;
                if (isset($properties['items']))
                {
                    $return .= self::$_config['group-open'];

                    if ($properties['type'] == 'checkbox' and count($properties['items']) > 1)
                    {
                        if (substr($properties['name'], -2) != '[]')
                        {
                            $properties['name'] .= '[]';
                        }
                    }

                    foreach ($properties['items'] as $count => $element)
                    {

                        if (!isset($element['id']))
                        {
                            $element['id'] = str_replace('[]', '', $name) . '_' . $count;
                        }
                        $element['type']  = $properties['type'];
                        $element['name']  = $properties['name'];
                        $return          .= self::input($element) . ' ';
                        $return          .= self::label($element['label'], $element['id']);
                    }
                    $return .= self::$_config['group-close'];
                }
                else
                {
                    $return .= $_label;
                    $return .= self::input($properties);
                }
            break;
            case 'select':
                $return .= $_label;
                $return .= self::select($properties);
                break;
            case 'textarea':
                $return .= $_label;
                $return .= self::textarea($properties);
                break;
            default:
                $return .= $_label;
                $return .= self::input($properties);
                break;
        }
        if (!$is_action)
        {
            if (self::$_config['info-pos'] == 'after-field')
            {
                $return .= self::$_config['info-open'] . $_info . self::$_config['info-close'];
            }
            $return .= self::_close_field($properties['type'], $required);
        }
        return $return;
    }
//create array for custom plugin
    private static function _open_field($type, $required = false)
    {
        if ($type == 'hidden')
        {
            return '';
        }

        $return = self::$_config['input-open'];

        if ($required and self::$_config['required-pos'] == 'before-field')
        {
            $return .= self::$_config['required-tag'];
        }

        return $return;
    }

    private static function _close_field($type, $required = false)
    {
        if ($type == 'hidden')
        {
            return '';
        }

        $return = "";

        if ($required and self::$_config['required-pos'] == 'after-field')
        {
            $return .= self::$_config['required-tag'];
        }

        $return .= "\t\t" . self::$_config['input-close'];

        return $return;
    }

    public static function select($parameters)
    {
        $options = $parameters['options'];
        $selected = isset($parameters['selected']) ? $parameters['selected'] : 0;
        unset($parameters['options']);
        unset($parameters['selected']);

        $input = "<select " . self::parse_attr($parameters) . ">\n";
        foreach ($options as $key => $val)
        {
            if (is_array($val))
            {
                $input .= str_repeat("\t", $indent_amount + 1) . '<optgroup label="' . $key . '">';
                foreach ($val as $opt_key => $opt_val)
                {
                    $extra  = ($opt_key == $selected) ? ' selected="selected"' : '';
                    $input .= '<option value="' . $opt_key . '"' . $extra . '>' . self::prep_value($opt_val) . "</option>\n";
                }
                $input .= "</optgroup>\n";
            }
            else
            {
                if (is_array($selected))
                {
                    $extra = (in_array($key, $selected)) ? ' selected="selected"' : '';
                }
                else
                {
                    $extra = ($key == $selected) ? ' selected="selected"' : '';
                }
                $input .= '<option value="' . $key . '"' . $extra . '>' . self::prep_value($val) . "</option>\n";
            }
        }
        $input .= "</select>";

        return $input;
    }

    public static function open($form_name = NULL, $options = array())
    {
        if (self::form_exists($form_name))
        {
            $form = self::get_form_array($form_name);

            if (!is_array($options))
            {
                $options = (array) $options;
            }
            $options = array_merge($form['attributes'], $options);
        } else return;
        
        // If there is still no action set, self-post
        if (empty($options['action']))
        {
            $options['action'] = current_url();
        }

        // Fixup URL if needed
        if (!strpos($options['action'], '://'))
        {
            $options['action'] = site_url($options['action']);
        }

        // default to POST
        isset($options['method']) or $options['method'] = 'post';

        $form = '<form ' . self::parse_attr($options) . '>';
		
		// @todo Add hidden elemnent for form_name
		// @desc this will allow multiple forms on one page
        
		return $form;
    }

/*
 * @todo create field sets
 */
    public static function get_fields($form_name)
    {
        $hidden = '';
        $fields = '';
        $form = self::get_form_array($form_name);
        if (empty($form['fields']))
            return;

        $return = self::$_config['form-open'];

        foreach ($form['fields'] as $name => $attr)
        {
            unset($attr['validation']);
            //unset($attr['label_visible']);
            if ($attr['type'] == 'hidden')
            {
                if (!isset($attr['name']))
                {
                    $attr['name'] = $name;
                }
                $hidden .= self::input($attr);
                
                //We dont need duplicate fields
                continue;
            }
            $fields .= self::field($name, $attr, $form_name);
        }
        $return .= $hidden.$fields;

        if (!empty($form['actions']))
        {
            $str = '';
            foreach ($form['actions'] as $name => $attr)
            {
                $str .= self::field($name, $attr, $form_name);
            }
            $return .=  self::$_config['actions-open'] . $str .  self::$_config['actions-close'];
        }
        $return .= self::$_config['form-close'];

        return $return;
    }

    public static function get_fields_arrays($form_name)
    {   
        $hidden = '';
        $fields = array();
        $static = array();
        $form = self::get_form_array($form_name);
        if (empty($form['fields']))
            return;

         $static['header'] = self::$_config['form-open'];

        foreach ($form['fields'] as $name => $attr)
        {
            unset($attr['validation']);
            //unset($attr['label_visible']);
            if ($attr['type'] == 'hidden')
            {
                if (!isset($attr['name']))
                {
                    $attr['name'] = $name;
                }
                $static['header'] .= self::input($attr);
                
                //We dont need duplicate fields
                continue;
            }
            $static['fields'][$name] = self::field($name, $attr, $form_name);
        }
        $static['footer']='';
        if (!empty($form['actions']))
        {
            $str = '';
            foreach ($form['actions'] as $name => $attr)
            {
                $str .= self::field($name, $attr, $form_name);
            }
             $static['footer'] = self::$_config['actions-open'] . $str .  self::$_config['actions-close'];
        }
         $static['footer'] .= self::$_config['form-close'];

        return $static;
    }


    public static function close()
    {
        return '</form>';//<script>$(document).ready(function(){$("a.close").live("click", function(e){ e.preventDefault();$(this).fadeTo(200, 0);$(this).parent().fadeTo(200, 0);$(this).parent().slideUp(400, function(){$(this).remove()})})});</script>';
    }

    public static function label($value, $for = '', $required = false, $info = false)
    {
        $str = '';
        if ($required and self::$_config['required-pos'] == 'before-label')
        {
            $str .= self::$_config['required-tag'];
        }
        $str .= sprintf(self::$_config['label-open'], $for);

		// Info position
        if ($info != false and strstr(self::$_config['info-pos'], '-label'))
        {
            if (self::$_config['info-pos'] == 'before-label')
            {
                $str .= self::$_config['info-open'] . $info . self::$_config['info-close'] . $value;
            }
            if (self::$_config['info-pos'] == 'after-label')
            {
                $str .= $value . self::$_config['info-open'] . $info . self::$_config['info-close'];
            }
        }
        else
        {
            $str .= $value;
        }
		
        $str .= self::$_config['label-close'];
        if ($required and self::$_config['required-pos'] == 'after-label')
        {
            $str .= self::$_config['required-tag'];
        }
        return $str;
    }

    public static function input($options,$action=false)
    {
        if (!isset($options['type']))
        {
            show_error('You must specify a type for the input');
        }
        elseif (!in_array($options['type'], self::$_valid_inputs))
        {
            show_error(sprintf('"%s" is not an acceptable element type.', $options['type']));
        }
		
        $input = '<input ' . self::parse_attr($options) . ' />';

        return $input;
    }

    public static function textarea($options)
    {
        $value = '';
        if (isset($options['value']))
        {
            $value = $options['value'];
            unset($options['value']);
        }
        $input = "<textarea " . self::parse_attr($options) . '>';
        $input .= self::prep_value($value);
        $input .= '</textarea>';

        return $input;
    }

    private static function parse_attr($attr)
    {
        $attr_str = '';
        if (!is_array($attr))
        {
            $attr = (array) $attr;
        }

        foreach ($attr as $property => $value)
        {
            if ($property == 'label')
            {
                continue;
            }
            if ($property == 'value')
            {
                $value = self::prep_value($value);
            }
            $attr_str .= $property . '="' . $value . '" ';
        }

        return substr($attr_str, 0, -1);
    }

    public static function prep_value($value)
    {
        return htmlspecialchars($value, ENT_QUOTES);
    }

    /**
     * [parse_validation description]
     * @return [type] [description]
     */
    private static function parse_validation()
    {
        foreach (self::$_forms as $form_name => $form)
        {
            if (!isset($form['fields']))
            {
                continue;
            }

            $i = 0;
            foreach ($form['fields'] as $name => $attr)
            {

                if (isset($attr['validation']))
                {
                    self::$_validation[$form_name][$i]['field'] = $name;
                    self::$_validation[$form_name][$i]['label'] = $attr['label'];
                    self::$_validation[$form_name][$i]['rules'] = $attr['validation'];

                    unset(self::$_forms[$form_name]['fields'][$name]['validation']);
                }
                ++$i;
            }
        }
    }

    /**
     * Captcha method
     * @return [type] [description]
     */
    static function makeCaptcha()
    {
        self::$_ci->load->helper('captcha');
        $data = array(
            'img_path' => UPLOAD_PATH. 'captcha' .'/',
            'img_url'  => BASE_URL . UPLOAD_PATH . 'captcha/'
        );

        if ( ! is_dir($data['img_path']))
        {
            if ( ! mkdir($data['img_path'], 0777, TRUE))
            {
                show_error('Cache Path was not found and could not be created: '.$data['img_path']);
            }
        }
        $cap = create_captcha($data);
        self::$_ci->session->set_userdata('pf_captcha', serialize(array(
            'captcha_time' => $cap['time'],
            'ip_address'   => self::$_ci->input->ip_address(),
            'word'         => $cap['word']
        )));
        return $cap['image'];
    }

    public static function validate($form_name)
    {

        if (!self::has_validation($form_name))
        {
            return TRUE;
        }


        self::$_ci->form_validation->set_rules(self::get_validation_array($form_name));
        
        self::$_ci->form_validation->set_model('pyroforms_m');

        return self::$_ci->form_validation->run();
    }

    public static function error($field_name, $prefix = '', $suffix = '')
    {
        return self::$_ci->form_validation->error($field_name, $prefix, $suffix);
    }

    public static function all_errors($prefix = '', $suffix = '')
    {
        return self::$_ci->form_validation->error_string($prefix, $suffix);
    }

    /**
     * Set Error Message
     *
     * Lets users set their own error messages on the fly.  Note:  The key
     * name has to match the function name that it corresponds to.
     *
     * @param   string
     * @param   string
     * @return  string
     */
    public function set_message($lang, $val = '')
    {
        self::$_ci->form_validation->set_message($lang, $val);
    }


    /**
     * Set The Error Delimiter
     *
     * Permits a prefix/suffix to be added to each error message
     *
     * @param   string
     * @param   string
     * @return  void
     */
    public function set_error_delimiters($prefix = '<p>', $suffix = '</p>')
    {
        self::$_ci->form_validation->set_message($prefix, $suffix);
    }

    /**
     * [set_value description]
     * @param [type] $form_name  [description]
     * @param [type] $field_name [description]
     * @param [type] $default    [description]
     */
    public static function set_value($form_name, $field_name, $default = NULL)
    {

        $post_name = str_replace('[]', '', $field_name);
        $value = isset($_POST[$post_name]) ? $_POST[$post_name] : self::prep_value($default);

        $field = & self::$_forms[$form_name]['fields'][$field_name];

        switch ($field['type'])
        {
            case 'radio': case 'checkbox':
                if (isset($field['items']))
                {
                    foreach ($field['items'] as &$element)
                    {

                        if ((is_array($value) and in_array($element['value'], $value)) or
							($element['value'] === $value) or
							(isset($element['checked']) and $element['checked'] === 'checked'))
                        {
                            $element['checked'] = 'checked';
                        }
                        else
                        {
                            if (isset($element['checked']))
                            {
                                unset($element['checked']);
                            }
                        }
                    }
                }
                else
                {
                    $field['value'] = $value;
                }
			break;

            case 'select':
                $field['selected'][] = $value;
                break;

            default:
                $field['value'] = self::prep_value($value);
        }
    }

    /**
     * [repopulate description]
     * @param  [type] $form_name [description]
     * @return [type]            [description]
     */
    public static function repopulate($form_name)
    {

        foreach (self::$_forms[$form_name]['fields'] as $field_name => $attr)
        {
            self::set_value($form_name, $field_name, (isset($attr['value']) ? $attr['value'] : NULL));
        }
    }
    
    /**
     * [getFolderDropdown description]
     * @param  [type] $name  [description]
     * @param  string $value [description]
     * @return [type]        [description]
     */
    static function getFolderDropdown($name, $value='')
    {
        self::$_ci->load->library('files/files');
        $folders = Files::folder_tree();
        foreach($folders as $folder)
        {
            $dropdown[$folder['id']] = $folder['name'];
            if (isset($folder['children']))
            {
                foreach ($folder['children'] as $child)
                {
                    $dropdown[$child['id']] = '- '. $child['name'];
                }
            }
        }
        return form_dropdown($name, $dropdown, $value);
    }
    
    /**
     * [tracking_frmView description]
     * @param  [type] $page [description]
     * @return [type]       [description]
     */
    static function tracking_frmView($page)
    {
        return "_gaq.push(['_trackPageview', '" . current_url() . $page . "']);";
    }

    /**
     * [tracking_frmEvent description]
     * @param  [type] $action [description]
     * @param  string $title  [description]
     * @return [type]         [description]
     */
    static function tracking_frmEvent($action, $title='')
    {
        return "_gaq.push(['_trackEvent', 'Forms', '".$action."', '".$title."']);";
    }

    /**
     * [tracking_code description]
     * @param  [type]  $data [description]
     * @param  boolean $form [description]
     * @return [type]        [description]
     */
    static function tracking_code($data, $form=false)
    {
        if (self::$_ci->settings->ga_tracking and ($form->track_events or $form->track_pages))
        {
            $str = '<script type="text/javascript">var _gaq = _gaq || [];';
            if (!empty($data['title']) and $form->track_pages == 1)
            {
                $str .= self::tracking_frmView($data['action']);
            }
            if (!empty($data['action']) and $form->track_events == 1)
            {
                $str .= self::tracking_frmEvent($data['action'], $data['title']);
            }
            $str .= '</script>';
            return $str;
        } else return '';
    }

}

/* End of file Pyroforms.php */