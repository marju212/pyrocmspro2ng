<?php

/**
 * Created by PhpStorm.
 * User: Marcus
 * Date: 2015-04-12
 * Time: 10:17
 */
class Plugin_subnav extends Plugin
{

    public $version = '0.1.0';
    public $name = array(
        'en' => 'Subnav',
    );
    public $description = array(
        'en' => 'Sub navigation of children links'

    );


    public function links()
    {
        $group = $this->attribute('group');
        $page_id = $this->attribute('page_id', 0);
        $active_class = $this->attribute('active_class', 'active');

        return [$this->getParent($group, $page_id, $active_class)];
    }

    public function isNavChild()
    {

        $group = $this->attribute('group');
        $page_id = $this->attribute('page_id', 0);
        return count($this->getParent($group, $page_id)) > 0;
    }


    public function getNav()
    {

        $group = $this->attribute('group');
        $page_id = $this->attribute('page_id', 0);

        if (ci()->module_details['slug'] != 'pages' AND seg(1) != 'blimedlem') {
            return false;
        }


        return (count($this->getParent($group, $page_id)) > 0) ? $this->content() : '';
    }


    private function getParent($group, $page_id, $active_class = null)
    {
        foreach ($this->getGroupLinks($group) as $link) {
            foreach ($link['children'] as $i => $child) {
                if ($child['page_id'] == $page_id) {
                    $link['children'][$i]['active_class'] = $active_class;

                    return $link;
                }
            }
        }
    }


    private function getGroupLinks($group)
    {
        $params = array(
            $group,
            array(
                'user_group' => ($this->current_user and isset($this->current_user->group)) ? $this->current_user->group : false,
                'front_end' => true,
                'is_secure' => IS_SECURE,
            )
        );
        return $this->pyrocache->model('navigation_m', 'get_link_tree', $params, config_item('navigation_cache'));
    }


}
