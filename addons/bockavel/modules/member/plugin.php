<?php if (!defined('BASEPATH')) exit('No direct script access allowed');


class Plugin_member extends Plugin
{
    private $newUser;

    /**
     * Return user if newer than 1 minute, else 404
     * @return object
     */
    function __construct()
    {
        $this->loadNewUserData(seg(2, null));

    }



    private function loadNewUserData($userId)
    {

        $columnPrefix = SITE_REF . "_profiles";
        $params = array(
            'stream' => 'profiles',
            'namespace' => 'users',
            'where' => "$columnPrefix.user_id=$userId AND $columnPrefix.created >= NOW() - INTERVAL 5 MINUTE"
        );

        $profiles = $this->streams->entries->get_entries($params);


        if (isset($profiles['entries'][0]) AND $user = $profiles['entries'][0]) {
            $this->newUser = (object)$user;
        } else {
            redirect('404');
        }

    }




    public function registered_name()
    {


        return $this->newUser->first_name . ' ' . $this->newUser->last_name;
    }

    public function registered_email()


    {

        ci()->load->model('users/user_m');

        if ($user = ci()->user_m->get_by('id', $this->newUser->id)) {
            return $user->email;
        };
        redirect('404');

    }

}

/* End of file plugin.php */
