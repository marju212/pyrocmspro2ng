<?php defined('BASEPATH') or exit('No direct script access allowed');

/**
 * PyroStreams Email Field Type
 *
 * @package		PyroStreams
 * @author		PyroCMS Dev Team
 * @copyright	Copyright (c) 2011 - 2013, PyroCMS
 */
class Field_regex
{
	public $field_type_slug				= 'regex';
	
	public $db_col_type					= 'varchar';
	
	//public $extra_validation			= 'max_length[8]';

	public $version						= '0.0.1';

    public $custom_parameters           = ['max_length','expression','errormessage'];

    public $author						= array('name'=>'Incore', 'url'=>'http://incore.se');
	
	// --------------------------------------------------------------------------

	/**
	 * Output form input
	 *
	 * @param	array
	 * @param	array
	 * @return	string
	 */
	public function form_output($data)
	{
		$options['name'] 	= $data['form_slug'];
		$options['id']		= $data['form_slug'];
		$options['value']	= $data['value'];
		
		return form_input($options);
	}

	// --------------------------------------------------------------------------
    public function param_expression($value)
    {

        return [
            'input'     => form_input('expression', $value),
            'instructions'  => lang('streams:regex.expression_description')
        ];
    }
    public function param_errormessage($value)
    {

        return [
            'input'     => form_input('errormessage', $value),
            'instructions'  => lang('streams:regex.errormessage_description')
        ];
    }
    public function validate($value, $mode, $field)
    {

        if (preg_match($field->field_data['expression'], $value)) {
            return true;
        } else {

            return sprintf('%s '.$field->field_data['errormessage'],$field->field_name);
        }


    }
	/**
	 * Pre Output
	 *
	 * No PyroCMS tags in regex fields.
	 *
	 * @return string
	 */
	public function pre_output($input)
	{
		$this->CI->load->helper('text');
		return escape_tags($input);
	}

	// --------------------------------------------------------------------------




}