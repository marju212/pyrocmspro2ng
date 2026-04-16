<?php

defined('BASEPATH') or exit('No direct script access allowed');


function isAjax()
{
    $CI = &get_instance();
    return $CI->input->is_ajax_request();
}




function seg($param, $default = 0)
{
    $CI = &get_instance();
    return $CI->uri->segment($param, $default);
}

function post($param)
{
    $CI = &get_instance();
    return $CI->input->post($param);
}

function user_to_string($id = 0, $format = '')
{
    $CI = &get_instance();
    $user = $CI->user_m->get(['id' => $id]);
    if ($user) {
        return ucfirst($user->username) . ', ' . $user->email;
    }
    return "User not found";


}

function row_meta_info($row, $fields = ['created', 'updated', 'created_by'], $delimiter = '<br>')
{
    if ($row) {
        $html = [];
        foreach ($fields as $field) {
            if ($row->{$field}) {
                switch ($field) {
                    case 'created':
                        $html[] = "Created: " . $row->{$field};
                        break;
                    case 'updated':
                        $html[] = "Updated: " . $row->{$field};
                        break;
                    case 'created_by':
                        $html[] = "Created by: " . user_to_string($row->{$field});
                        break;
                    default:
                        $html[] = $row->{$field};
                }
            }
        }
        return implode($delimiter, $html);
    }
    return "Error: Metadata not found";
}


function tooltip($message = '')
{
    return " original-title='{$message}'";
}

function is_admin()
{
    $CI = &get_instance();
    if (isset($CI->current_user->group)):
        return preg_match('/admin/i', $CI->current_user->group);
    else:
        return FALSE;
    endif;
}

function is_super_admin()
{
    $CI = &get_instance();

    if (isset($CI->current_user->group)):
        return $CI->current_user->group === 'admin';
    else:
        return FALSE;
    endif;
}


function getNewSelectionSessionUrl()
{
    return '?sess=' . getNewSelectionSessionId();
}

function getNewSelectionSessionId()
{
    return rand(10000, 1e9);
}

function getSelectionSessionUrl()
{
    return '?sess=' . getSelectionSessionId(false);
}

function getSelectionSessionId($new = false)
{
    $CI = &get_instance();
    if ($CI->input->get('sess', TRUE)) {
        return $CI->input->get('sess', TRUE);
    }
    return getNewSelectionSessionId();
}

function getTcrunSelectionIds()
{
    $CI = &get_instance();
    $selectionids = $CI->session->userdata(getSelectionSessionId() . 'selectionids');
    if (is_array($selectionids)) {
        return $selectionids;
    }
    return [];
}

function getTcrunBaselineId()
{
    $CI = &get_instance();
    $baselineid = $CI->session->userdata(getSelectionSessionId() . 'baselineid');
    if ($baselineid) {
        return $baselineid;
    }
    return 0;

}

function setSearchCriteria($obj, $sessid = '')
{
    $CI = &get_instance();
    $sessid = ($sessid) ? $sessid : getSelectionSessionId();
    $val = is_object($obj) ? serialize($obj) : null;
    $CI->session->set_userdata($sessid . 'searchcriteria', $val);

}

function getSearchCriteria()
{
    $CI = &get_instance();

    $searchCriteria = $CI->session->userdata(getSelectionSessionId() . 'searchcriteria');
    if ($searchCriteria) {
        return unserialize($searchCriteria);
    }
    return null;

}

function setTcrunSelectionIds($val = [], $sessid = '')
{
    $CI = &get_instance();
    $sessid = ($sessid) ? $sessid : getSelectionSessionId();
    $CI->session->set_userdata($sessid . 'selectionids', $val);
}

function setTcrunBaselineId($val = 0, $sessid = '')
{
    $CI = &get_instance();
    $sessid = ($sessid) ? $sessid : getSelectionSessionId();
    $CI->session->set_userdata($sessid . 'baselineid', $val);

}

function user_has_module($module)
{


    $group = ci()->permission_m->get_group(ci()->current_user->group_id);

    return isset($group[$module]) OR is_super_admin();


}


function logger($message, $query = FALSE)
{
    log_message('PRIVATE_DEBUG', print_log($message, $query), FALSE);
}

function ddall($everything)
{


    ksort($everything);
    echo '<pre>';
    print_r($everything);
    echo '</pre>';

}

if ( ! function_exists('dump')) {
    function dd($message, $query = FALSE)
    {
        print "<pre>";

        print_r(print_log($message, $query));
        print "</pre> ";
        die();

        //print_r(get_defined_constants(true));
    }
}


function path()
{
    return implode('/', func_get_args());
}

function print_log($message, $query = FALSE)
{
    if (is_object($message))
        $message = "\n" . print_r((Object)$message, TRUE);
    if (is_array($message))
        $message = "\n" . print_r((Array)$message, TRUE);
    if ($query):
        $CI = &get_instance();
        $message .= "\n" . '--- LATEST QUERY ---' . "\n" . $CI->db->last_query();
    endif;
    return $message;

}

function get_postdata($validation_rules = [])
{
    $stdObj = new stdClass();
    foreach ($validation_rules as $field):
        if (strpos($field['field'], '[]') > 0)
            $field['field'] = str_replace('[]', '', $field['field']);
        $stdObj->{
        $field['field']} = post($field['field']);
    endforeach;

    return $stdObj;
}


function diff_numeric($val, $val_bas)
{

    return $val - $val_bas;
}

function color($diffoptions, $data)
{
    $color = 'black';
    if ($data > 0) {
        $color = 'positive';
    }
    if ($data < 0) {
        $color = 'negative';
    }
    if (preg_match('/inverted/i', $diffoptions) AND $data < 0) {
        $color = 'positive';
    }
    if (preg_match('/inverted/i', $diffoptions) AND $data > 0) {
        $color = 'negative';
    }


    return $color;
}

function diff_percent($val, $val_bas)
{
    if ($val_bas > 0 AND $val > 0) {

        return (($val - $val_bas) / $val_bas) * 100;
    }
    return 0;
}

function decimals($r, $num)
{
    $r = (double)$r;
    $num = ($r > 99.99 OR $r < -99.99) ? 0 : $num;
    $r = number_format($r, $num, '.', ' ');

    return $r;
}

function repl($str)
{

    return str_replace('{', '($value_array["', str_replace('}', '"]["value"])', $str));
}


function get_verdict_label($verict = null)
{
    if ($verict == 2)
        return '<span class="pass">PASS</span>';
    if ($verict == 1)
        return '<span class="fail">FAIL</span>';

    return '<span class="inconc">INCONC</span>';

}

function include_controller($module, $controller)
{
    include_once(ADDONPATH . "modules / " . $module . " / controllers / " . strtolower($controller) . EXT);
}

function axis_to_int($dimension_alias)
{
    $dimension = ['x' => 0, 'y' => 1, 'z' => 2];
    if (is_null($dimension[$dimension_alias])) {
        die($dimension_alias . ' Dimension alias does not exist');
    }
    return $dimension[strtolower($dimension_alias)];

}

function int_to_axis($dimension_integer)
{
    $dimension = [0 => 'x', 1 => 'y', 2 => 'z'];
    if (is_null($dimension[$dimension_integer])) {
        die($dimension_integer . ' Dimension integer can not be converted');
    }
    return $dimension[$dimension_integer];

}

function make_table($array)
{
    if (empty($array)) {
        $array = [['Status' => 'Container is Empty']];
    }
    $h = [];
    foreach ($array as $row) {
        foreach ($row as $key => $val) {
            if (!in_array($key, $h)) {
                $h[] = $key;
            }
        }
    }
    $html = '<table border="1"><tr>';
    foreach ($h as $key) {
        $key = ucwords($key);
        $html .= '<th>' . $key . '</th>';
    }
    $html .= '</tr>';
    $old_tcrun = 0;
    $i = 0;
    foreach ($array as $row) {
        if (!empty($row->TcRunid) AND $row->TcRunid != $old_tcrun) {
            $old_tcrun = $row->TcRunid;
            $i++;
        }
        $color = ($i % 2) ? 'D1FAFF' : 'AFD8DE';
        $html .= "<tr style = 'background-color:#{$color}'>";
        foreach ($row as $val) {
            $html .= '<td>' . strip_tags($val) . '</td>';
        }
        $html .= '</tr>';
    }
    $html .= '</table>';
    return $html;
}

/* End of file  */
