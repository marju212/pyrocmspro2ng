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

            // Encrypt the SMTP submission. Default to STARTTLS ('tls') because
            // we'd otherwise send AUTH LOGIN credentials in cleartext. Allow
            // 'ssl' for providers that require implicit TLS on port 465 / 8465,
            // or '' to opt out (only for explicit local-relay debugging).
            $crypto = function_exists('env_str') ? env_str('MAIL_SMTP_CRYPTO', 'tls') : 'tls';
            if ($crypto !== '') {
                $config['smtp_crypto'] = $crypto;
            }

            // CI's default smtp_timeout is 5s — too tight for cross-region SMTP
            // on flaky connections. 15s is comfortable without hanging the
            // request thread for too long if the relay is down.
            $timeout = function_exists('env_str') ? env_str('MAIL_SMTP_TIMEOUT', '15') : '15';
            $config['smtp_timeout'] = (int) $timeout;
        }

        // Catch syntactically invalid recipients before they hit the relay so
        // a typo surfaces as a CI validation error instead of an SMTP bounce.
        $config['validate'] = true;

        $this->initialize($config);
    }
}
/* End of file MY_Email.php */
