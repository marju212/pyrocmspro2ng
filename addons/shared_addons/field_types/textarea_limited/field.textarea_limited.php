<?php

defined('BASEPATH') or exit('No direct script access allowed');

/**
 * Textarea Limited Field Type
 *
 * @author    Rigo B Castro - WeDreamPro Team <@rigobcastro>
 * @copyright    Copyright (c) 2011 - 2013, Rigo B Castro
 */
#[\AllowDynamicProperties]
class Field_textarea_limited
{
    public $field_type_name = 'Textarea Limited';
    public $field_type_slug = 'textarea_limited';
    public $db_col_type = 'longtext';
    public $admin_display = 'full';
    public $version = '1.1.0';
    public $author = array('name' => 'Rigo B Castro', 'url' => 'http://rigobcastro.com');
    public $custom_parameters = array('default_text', 'allow_tags', 'content_type', 'character_limit');


    /**
     * Assets for countable remaining textarea
     *
     * @return void
     */
    public function event()
    {
        $this->CI->type->add_css('textarea_limited', 'textarea_limited.css');
        $this->CI->type->add_js('textarea_limited', 'jquery.textareaCounter.plugin.js');
    }

    /**
     * Output form input
     *
     * @param    array
     * @param    array
     * @return    string
     */
    public function form_output($data, $entry_id, $field)
    {
        // Value
        // We only use the default value if this is a new entry
        if (!$entry_id) {
            $value = (isset($field->field_data['default_text']) and $field->field_data['default_text']) ? $field->field_data['default_text'] : $data['value'];

            // If we still don't have a default value, maybe we have it in
            // the old default value string. So backwards compat.
            if (!$value and isset($field->field_data['default_value'])) {
                $value = $field->field_data['default_value'];
            }
        } else {
            $value = $data['value'];
        }


        $textarea = form_textarea(array(
            'name' => $data['form_slug'],
            'id' => $data['form_slug'],
            'value' => $value
        ));


        $view = $this->CI->type->load_view('textarea_limited', 'textarea_limited', array(
            'textarea' => $textarea,
            'character_limit' => $field->field_data['character_limit'],
            'id' => $data['form_slug']
        ));

        return $view;
    }

    // --------------------------------------------------------------------------

    public function pre_save($input)
    {
        return $input;
    }

    // --------------------------------------------------------------------------

    /**
     * Pre-Ouput content
     *
     * @access    public
     * @return    string
     */
    public function pre_output($input, $params)
    {
        $parse_tags = (!isset($params['allow_tags'])) ? 'n' : $params['allow_tags'];
        $content_type = (!isset($params['content_type'])) ? 'html' : $params['content_type'];

        // If this is the admin, show only the source
        // @TODO This is hacky, there will be times when the admin wants to see a preview or something
        if (defined('ADMIN_THEME')) {
            return $input;
        }

        // If this isn't the admin and we want to allow tags,
        // let it through. Otherwise we will escape them.
        if ($parse_tags == 'y') {
            $content = $this->CI->parser->parse_string($input, array(), true);
        } else {
            $this->CI->load->helper('text');
            $content = escape_tags($input);
        }

        // Not that we know what content is there, what format should we treat is as?
        switch ($content_type) {
            case 'md':
                $this->CI->load->helper('markdown');
                return parse_markdown($content);

            case 'html':
                return $content;

            default:
                return strip_tags($content);
        }
    }

    // --------------------------------------------------------------------------

    /**
     * Default Textarea Value
     *
     * @access    public
     * @param    [string - value]
     * @return    string
     */
    public function param_default_text($value = null)
    {
        return form_textarea(array(
            'name' => 'default_text',
            'id' => 'default_text',
            'value' => $value,
        ));
    }

    // --------------------------------------------------------------------------

    /**
     * Allow tags param.
     *
     * Should tags go through or be converted to output?
     */
    public function param_allow_tags($value = null)
    {
        $options = array(
            'n' => lang('global:no'),
            'y' => lang('global:yes')
        );

        // Defaults to No
        $value or $value = 'n';

        return form_dropdown('allow_tags', $options, $value);
    }

    // --------------------------------------------------------------------------

    /**
     * Content Type
     *
     * Is this plain text, HTML or Markdown?
     */
    public function param_content_type($value = null)
    {
        $options = array(
            'text' => lang('global:plain-text'),
            'html' => 'HTML',
            'md' => 'Markdown',
        );

        // Defaults to Plain Text
        $value or $value = 'text';

        return form_dropdown('content_type', $options, $value);
    }

    /**
     * Max length
     *
     * Is this plain text, HTML or Markdown?
     */
    public function param_character_limit($value = 0)
    {
        return form_input(array(
            'type' => 'text',
            'name' => 'character_limit'
        ), $value);
    }

}
