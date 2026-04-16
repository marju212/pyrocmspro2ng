<?php

/**
 * Created by PhpStorm.
 * User: Marcus
 * Date: 2015-04-12
 * Time: 10:17
 */
class Plugin_members extends Plugin
{

    public $version = '0.1.0';
    public $name = array(
        'en' => 'Members',
    );
    public $description = array(
        'en' => 'Members'

    );
    public function boolean_to_human()
    {
        return strlen($this->attribute('data', ''))>0?'Ja':'Nej';

    }

    /**
     * Return true if page id exists in calendar and the iten is in the future
     * @return bool
     */
    public function ids()
    {
        $groups = $this->attribute('groups', Settings::get('member_groups'));


        $CI = ci();
        $CI->load->model('users/user_m');
        $this->db->where('group_id !=', 1);
        $this->db->where('active', 1);
        $this->db->join('groups', 'groups.id = users.group_id');
        $this->db->where_in('groups.name', explode(',', $groups));

        return $CI->user_m->get_all();
    }


}
