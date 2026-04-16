<?php defined('BASEPATH') OR exit('No direct script access allowed');


class Events_Member
{

    protected $ci;

    public function __construct()
    {
        $this->ci =& get_instance();

        //register the public_controller event
        Events::register('post_user_register_email_sent', array($this, 'routeToRegisteredPage'));
        Events::register('post_user_activation', array($this, 'routeToWelcomePage'));
        Events::register('post_user_update', array($this, 'routeToHome'));
        Events::register('user_created', array($this, 'createMemberNo'));

    }

    public function routeToHome()
    {
        $this->ci->session->set_flashdata('success', $this->ci->ion_auth->messages());

        redirect();
    }

    public function createMemberNo($id)
    {
            if ($memberData = $this->ci->user_m->get(['id' => $id])) {
                if (!$memberData->membernumber) {
                    $this->ci->user_m->update($memberData->id, ['membernumber' => date('y', human_to_unix($memberData->created)) . sprintf("%03d", $memberData->id)]);
                }
            }
    }

    public function routeToWelcomePage($userId)
    {


        redirect('valkommen');
    }

    /**
     * Route to registered page with email sent message, dont guess my id
     * @param $userId
     */
    public function routeToRegisteredPage($userId)
    {

        $this->sendMail($userId);

        redirect('registrerad/' . $userId );
    }


    private function sendMail($userId)
    {

        $columnPrefix = SITE_REF . "_profiles";
        $params = array(
            'stream' => 'profiles',
            'namespace' => 'users',
            'where' => "$columnPrefix.user_id=$userId AND $columnPrefix.created >= NOW() - INTERVAL 5 MINUTE"
        );
        //$this->load->driver('Streams');
        $profiles = $this->ci->streams->entries->get_entries($params);

        if (isset($profiles['entries'][0]) AND $user = $profiles['entries'][0]) {

            $this->newUser = (object)$user;
            $this->ci->load->model('users/user_m');

            if ($user = $this->ci->user_m->get_by('id', $this->newUser->id)) {

                Events::trigger('email', array(
                    'name' => (Settings::get('site_name')),
                    'to' => $user->email,
                    'username' => ($this->newUser->first_name . ' ' . $this->newUser->last_name),
                    'reply-to' => Settings::get('contact_email'),
                    'slug' => 'new_member'

                ), 'array');

            };


        }

    }

}
/* End of file events.php */
