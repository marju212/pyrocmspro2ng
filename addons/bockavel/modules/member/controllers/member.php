<?php if (!defined('BASEPATH')) exit('No direct script access allowed');

/**
 * This is a sample module for PyroCMS
 *
 * @author        Jerel Unruh - PyroCMS Dev Team
 * @website        http://unruhdesigns.com
 * @package    PyroCMS
 * @subpackage    Sample Module
 */
class Member extends Public_Controller
{


    public function __construct()
    {
        parent::__construct();

        // Load the required classes
        // $this->load->model('sample_m');
        //  $this->lang->load('sample');

        // $this->template
        //     ->append_css('module::sample.css')
        //    ->append_js('module::sample.js');
    }
    public function medlemmar()
    {
        // set the pagination limit
//        {{ member:registered_email }}
//        {{ member:registered_name }}

        $this->template
            ->title('Medlemmar')
            ->build('medlemmar');
    }
    /**
     * All items
     */

    private function loadNewUserData($userId)
    {
        $columnPrefix = SITE_REF . "_profiles";

        $five_minutes_ago = time() - (5 * 60);
        ci()->db->select('*')->from('bockavel_users'); // Select all fields to see what's available
        $this->db->order_by('id', 'DESC'); // Order by 'created' field in descending order
        $this->db->limit(1);
        return ci()->db->get();
    }

    public function registered($userId)
    {
        
        // Load streams library if not already loaded
        $this->load->driver('Streams');


        // Get the most recent user and their profile data
        $columnPrefix = SITE_REF . "_profiles";
        $newUser = $this->loadNewUserData($userId);
        $userData = $newUser->result_array();
        
        $userEmail = '';
        $userName = '';
        
        if (!empty($userData)) {
            $mostRecentUser = $userData[0];
            $userEmail = $mostRecentUser['email'];
            $actualUserId = $mostRecentUser['id'];
            
            // Look for profile with the actual most recent user ID
            $this->db->select('*');
            $this->db->from($columnPrefix);
            $this->db->where('user_id', $actualUserId);
            $profileQuery = $this->db->get();
            
            if ($profileQuery->num_rows() > 0) {
                $profileData = $profileQuery->row_array();
                $firstName = !empty($profileData['first_name']) ? $profileData['first_name'] : '';
                $lastName = !empty($profileData['last_name']) ? $profileData['last_name'] : '';
                $userName = trim($firstName . ' ' . $lastName);
            }
            
            // If no name found, fall back to username or email
            if (empty($userName)) {
                $userName = !empty($mostRecentUser['username']) ? $mostRecentUser['username'] : $userEmail;
            }
        }

        // Create SMS message with name and email
        $smsMessage = "Ny medlemsregistrering: " . $userName;
        if (!empty($userEmail) && $userName !== $userEmail) {
            $smsMessage .= " (" . $userEmail . ")";
        }

        // SMS_ENABLED in .env is the master switch. Keep the SITE_REF guard as
        // a belt-and-braces check because this addon is bockavel-only.
        $sms_enabled = function_exists('pyro_env_bool') && pyro_env_bool('SMS_ENABLED', false);
        $sms_from    = function_exists('env_str') ? env_str('SMS_FROM', 'Medlemsreg') : 'Medlemsreg';

        if ($sms_enabled && SITE_REF === 'bockavel') {
            foreach (config_item('sms_registered') as $user => $phone) {
                $this->sendSMS(array(
                    'from'    => $sms_from,
                    'to'      => $phone,
                    'message' => $smsMessage,
                ));
            }
        }

        $this->template
            ->title('Tack, Vi har mottagit din medlemsansökan')
            ->build('registered');
    }

    public function sendSMS($sms) {
        // 46elks Basic-auth creds live in .env (SMS_API_USER / SMS_API_PASS).
        // Bail out quietly if they aren't configured — callers already gate
        // on SMS_ENABLED, but a missing cred should never reach the network.
        $username = function_exists('env_str') ? env_str('SMS_API_USER') : '';
        $password = function_exists('env_str') ? env_str('SMS_API_PASS') : '';
        if ($username === '' || $password === '') {
            log_message('error', 'sendSMS: SMS_API_USER / SMS_API_PASS not configured; skipping send.');
            return false;
        }

        $context = stream_context_create(array(
            'http' => array(
                'method'  => 'POST',
                'header'  => 'Authorization: Basic ' . base64_encode($username . ':' . $password) . "\r\n" .
                             "Content-type: application/x-www-form-urlencoded\r\n",
                'content' => http_build_query($sms),
                'timeout' => 10,
            ),
        ));
        $response = @file_get_contents('https://api.46elks.com/a1/sms', false, $context);

        if ( ! isset($http_response_header[0]) || ! strstr($http_response_header[0], '200 OK')) {
            return isset($http_response_header[0]) ? $http_response_header[0] : false;
        }
        return $response;
    }

}
