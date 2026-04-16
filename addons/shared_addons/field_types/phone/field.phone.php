<?php defined('BASEPATH') or exit('No direct script access allowed');

/**
 * PyroStreams Email Field Type
 *
 * @package		PyroStreams
 * @author		PyroCMS Dev Team
 * @copyright	Copyright (c) 2011 - 2013, PyroCMS
 */
class Field_phone
{
	public $field_type_slug				= 'phone';
	
	public $db_col_type					= 'varchar';
	
	//public $extra_validation			= 'max_length[8]';

	public $version						= '0.0.2';

    public $custom_parameters           = ['max_length'];

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

    public function validate($value, $mode, $field)
    {

        if (preg_match("/^([+0-9])([0-9-])+$/", $value)) {
            return true;
        } else {
            return sprintf(lang('streams:phone.illegal'),$field->field_name);
        }


    }
	/**
	 * Pre Output
	 *
	 * No PyroCMS tags in phone fields.
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