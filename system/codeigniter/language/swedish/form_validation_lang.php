<?php
/**
 * CodeIgniter — Swedish translation of form_validation messages.
 *
 * Original strings: Copyright (c) 2008–2022 EllisLab / BCIT / CodeIgniter
 * Foundation, MIT License (see system/codeigniter/language/english/
 * form_validation_lang.php for the full notice).
 *
 * The {field} and {param} placeholders are substituted at runtime by
 * MY_Form_validation. Field labels in this project are typically already
 * capitalised (e.g. "E-post", "Användarnamn"), so the surrounding text
 * uses lower-case.
 */
defined('BASEPATH') OR exit('No direct script access allowed');

$lang['form_validation_required']                = 'Fältet {field} är obligatoriskt.';
$lang['form_validation_isset']                   = 'Fältet {field} måste ha ett värde.';
$lang['form_validation_valid_email']             = 'Fältet {field} måste innehålla en giltig e-postadress.';
$lang['form_validation_valid_emails']            = 'Fältet {field} måste innehålla enbart giltiga e-postadresser.';
$lang['form_validation_valid_url']               = 'Fältet {field} måste innehålla en giltig URL.';
$lang['form_validation_valid_ip']                = 'Fältet {field} måste innehålla en giltig IP-adress.';
$lang['form_validation_valid_base64']            = 'Fältet {field} måste innehålla en giltig Base64-sträng.';
$lang['form_validation_min_length']              = 'Fältet {field} måste vara minst {param} tecken långt.';
$lang['form_validation_max_length']              = 'Fältet {field} får inte vara längre än {param} tecken.';
$lang['form_validation_exact_length']            = 'Fältet {field} måste vara exakt {param} tecken långt.';
$lang['form_validation_alpha']                   = 'Fältet {field} får endast innehålla bokstäver.';
$lang['form_validation_alpha_numeric']           = 'Fältet {field} får endast innehålla bokstäver och siffror.';
$lang['form_validation_alpha_numeric_spaces']    = 'Fältet {field} får endast innehålla bokstäver, siffror och mellanslag.';
$lang['form_validation_alpha_dash']              = 'Fältet {field} får endast innehålla bokstäver, siffror, understreck och bindestreck.';
$lang['form_validation_numeric']                 = 'Fältet {field} får endast innehålla siffror.';
$lang['form_validation_is_numeric']              = 'Fältet {field} får endast innehålla numeriska tecken.';
$lang['form_validation_integer']                 = 'Fältet {field} måste innehålla ett heltal.';
$lang['form_validation_regex_match']             = 'Fältet {field} har fel format.';
$lang['form_validation_matches']                 = 'Fältet {field} matchar inte fältet {param}.';
$lang['form_validation_differs']                 = 'Fältet {field} måste skilja sig från fältet {param}.';
$lang['form_validation_is_unique']               = 'Fältet {field} måste innehålla ett unikt värde.';
$lang['form_validation_is_natural']              = 'Fältet {field} får endast innehålla siffror.';
$lang['form_validation_is_natural_no_zero']      = 'Fältet {field} måste innehålla siffror och vara större än noll.';
$lang['form_validation_decimal']                 = 'Fältet {field} måste innehålla ett decimaltal.';
$lang['form_validation_less_than']               = 'Fältet {field} måste innehålla ett tal mindre än {param}.';
$lang['form_validation_less_than_equal_to']      = 'Fältet {field} måste innehålla ett tal som är mindre än eller lika med {param}.';
$lang['form_validation_greater_than']            = 'Fältet {field} måste innehålla ett tal större än {param}.';
$lang['form_validation_greater_than_equal_to']   = 'Fältet {field} måste innehålla ett tal som är större än eller lika med {param}.';
$lang['form_validation_error_message_not_set']   = 'Kunde inte hitta något felmeddelande för fältet {field}.';
$lang['form_validation_in_list']                 = 'Fältet {field} måste vara ett av: {param}.';
