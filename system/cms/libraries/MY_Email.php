<?php if (!defined('BASEPATH')) exit('No direct script access allowed');

/**
 * MY_Email
 * Allows for email config settings to be stored in the db.
 *
 * @package    PyroCMS\Core\Libraries
 * @author      PyroCMS Dev Team
 * @copyright   Copyright (c) 2012, PyroCMS LLC
 */
class MY_Email extends CI_Email
{

    /**
     * Constructor method
     *
     * @return void
     */
    public function __construct($config = array())
    {
        parent::__construct($config);

        // Each setting can be overridden by an env var so prod deploys can
        // flip SMTP credentials without touching the DB / admin UI. Empty
        // env falls through to Settings::get(), preserving current behaviour
        // for installs that configure mail via admin panel.
        $protocol = function_exists('env_str') ? env_str('MAIL_PROTOCOL') : '';
        if ($protocol === '') {
            $protocol = Settings::get('mail_protocol');
        }

        $config['protocol'] = $protocol;
        $config['mailtype'] = "html";
        $config['charset']  = "utf-8";
        $config['crlf']     = Settings::get('mail_line_endings') ? "\r\n" : PHP_EOL;
        $config['newline']  = Settings::get('mail_line_endings') ? "\r\n" : PHP_EOL;

        if ($protocol === 'sendmail') {
            $path = function_exists('env_str') ? env_str('MAIL_SENDMAIL_PATH') : '';
            if ($path === '') {
                $path = Settings::get('mail_sendmail_path');
            }
            $config['mailpath'] = $path !== '' ? $path : '/usr/sbin/sendmail';
        }

        if ($protocol === 'smtp') {
            $config['smtp_host'] = (function_exists('env_str') && ($v = env_str('MAIL_SMTP_HOST')) !== '') ? $v : Settings::get('mail_smtp_host');
            $config['smtp_user'] = (function_exists('env_str') && ($v = env_str('MAIL_SMTP_USER')) !== '') ? $v : Settings::get('mail_smtp_user');
            $config['smtp_pass'] = (function_exists('env_str') && ($v = env_str('MAIL_SMTP_PASS')) !== '') ? $v : Settings::get('mail_smtp_pass');
            $config['smtp_port'] = (function_exists('env_str') && ($v = env_str('MAIL_SMTP_PORT')) !== '') ? $v : Settings::get('mail_smtp_port');
        }

        $this->initialize($config);
    }
}
/* End of file MY_Email.php */
