<?php
/**
 * CodeIgniter — Swedish translation of email error messages.
 *
 * Original strings: Copyright (c) 2008–2022 EllisLab / BCIT / CodeIgniter
 * Foundation, MIT License (see system/codeigniter/language/english/
 * email_lang.php for the full notice). The %s tokens are sprintf
 * placeholders substituted by the Email library.
 */
defined('BASEPATH') OR exit('No direct script access allowed');

$lang['email_must_be_array']         = 'E-postvalideringen måste anropas med en array.';
$lang['email_invalid_address']       = 'Ogiltig e-postadress: %s';
$lang['email_attachment_missing']    = 'Kunde inte hitta följande e-postbilaga: %s';
$lang['email_attachment_unreadable'] = 'Kunde inte öppna bilagan: %s';
$lang['email_no_from']               = 'Kan inte skicka e-post utan en "From"-rubrik.';
$lang['email_no_recipients']         = 'Du måste ange mottagare: To, Cc eller Bcc.';
$lang['email_send_failure_phpmail']  = 'Kunde inte skicka e-post med PHP mail(). Servern är kanske inte konfigurerad för att skicka e-post på det sättet.';
$lang['email_send_failure_sendmail'] = 'Kunde inte skicka e-post med PHP Sendmail. Servern är kanske inte konfigurerad för att skicka e-post på det sättet.';
$lang['email_send_failure_smtp']     = 'Kunde inte skicka e-post med PHP SMTP. Servern är kanske inte konfigurerad för att skicka e-post på det sättet.';
$lang['email_sent']                  = 'Ditt meddelande har skickats med följande protokoll: %s';
$lang['email_no_socket']             = 'Kunde inte öppna en socket till Sendmail. Kontrollera inställningarna.';
$lang['email_no_hostname']           = 'Du har inte angett ett SMTP-värdnamn.';
$lang['email_smtp_error']            = 'Följande SMTP-fel uppstod: %s';
$lang['email_no_smtp_unpw']          = 'Fel: SMTP-användarnamn och lösenord måste anges.';
$lang['email_failed_smtp_login']     = 'Misslyckades att skicka AUTH LOGIN-kommandot. Fel: %s';
$lang['email_smtp_auth_un']          = 'Misslyckades att autentisera användarnamnet. Fel: %s';
$lang['email_smtp_auth_pw']          = 'Misslyckades att autentisera lösenordet. Fel: %s';
$lang['email_smtp_data_failure']     = 'Kunde inte skicka data: %s';
$lang['email_exit_status']           = 'Felstatuskod: %s';
