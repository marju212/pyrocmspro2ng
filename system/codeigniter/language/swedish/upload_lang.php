<?php
/**
 * CodeIgniter — Swedish translation of file-upload error messages.
 *
 * Original strings: Copyright (c) 2008–2022 EllisLab / BCIT / CodeIgniter
 * Foundation, MIT License (see system/codeigniter/language/english/
 * upload_lang.php for the full notice).
 */
defined('BASEPATH') OR exit('No direct script access allowed');

$lang['upload_userfile_not_set']         = 'Kunde inte hitta någon POST-variabel som heter "userfile".';
$lang['upload_file_exceeds_limit']       = 'Den uppladdade filen överskrider den maximala storleken som tillåts i serverns PHP-konfiguration.';
$lang['upload_file_exceeds_form_limit']  = 'Den uppladdade filen är större än vad formuläret tillåter.';
$lang['upload_file_partial']             = 'Filen blev bara delvis uppladdad.';
$lang['upload_no_temp_directory']        = 'Den temporära mappen saknas.';
$lang['upload_unable_to_write_file']     = 'Filen kunde inte skrivas till disk.';
$lang['upload_stopped_by_extension']     = 'Filuppladdningen stoppades av ett tillägg.';
$lang['upload_no_file_selected']         = 'Du valde ingen fil att ladda upp.';
$lang['upload_invalid_filetype']         = 'Filtypen du försöker ladda upp är inte tillåten.';
$lang['upload_invalid_filesize']         = 'Filen du försöker ladda upp är större än den tillåtna storleken.';
$lang['upload_invalid_dimensions']       = 'Bilden du försöker ladda upp ryms inte inom de tillåtna måtten.';
$lang['upload_destination_error']        = 'Ett problem uppstod när den uppladdade filen skulle flyttas till slutdestinationen.';
$lang['upload_no_filepath']              = 'Uppladdningssökvägen verkar inte vara giltig.';
$lang['upload_no_file_types']            = 'Du har inte angett några tillåtna filtyper.';
$lang['upload_bad_filename']             = 'Filnamnet du skickade in finns redan på servern.';
$lang['upload_not_writable']             = 'Måldestinationen för uppladdning verkar inte vara skrivbar.';
