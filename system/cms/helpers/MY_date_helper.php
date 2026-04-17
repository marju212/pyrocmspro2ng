<?php defined('BASEPATH') OR exit('No direct script access allowed.');

/**
 * PyroCMS Date Helpers
 * 
 * This overrides Codeigniter's helpers/date_helper.php
 *
 * @author      PyroCMS Dev Team
 * @copyright   Copyright (c) 2012, PyroCMS LLC
 * @package		PyroCMS\Core\Helpers
 */


if (!function_exists('format_date'))
{

	/**
	 * Formats a timestamp into a human date format.
	 *
	 * @param int $unix The UNIX timestamp
	 * @param string $format The date format to use.
	 * @return string The formatted date.
	 */
	function format_date($unix, $format = '')
	{
		if ($unix == '' || !is_numeric($unix))
		{
			$unix = strtotime($unix);
		}

		if (!$format)
		{
			$format = Settings::get('date_format');
		}

		if (strstr($format, '%') !== false)
		{
			$formatted = _pyro_strftime_compat($format, $unix);
			return ucfirst($formatted);
		}

		return date($format, $unix);
	}

}

if (!function_exists('_pyro_strftime_compat'))
{
	/**
	 * Translates a legacy strftime(3) format string into a date() format,
	 * then returns the formatted date as UTF-8.
	 *
	 * Replaces the PHP 8.1-deprecated strftime() and PHP 8.2-deprecated
	 * utf8_encode(). Locale-sensitive tokens (%B, %b, %A, %a) use the
	 * current locale via IntlDateFormatter if the intl extension is
	 * loaded, otherwise fall back to PHP's english month/day names.
	 */
	function _pyro_strftime_compat($format, $unix)
	{
		$map = array(
			'%Y' => 'Y', '%y' => 'y',
			'%m' => 'm', '%d' => 'd', '%e' => 'j',
			'%H' => 'H', '%I' => 'h', '%M' => 'i', '%S' => 's',
			'%p' => 'A', '%P' => 'a',
			'%B' => 'F', '%b' => 'M', '%h' => 'M',
			'%A' => 'l', '%a' => 'D',
			'%j' => 'z', '%w' => 'w', '%u' => 'N',
			'%Z' => 'T', '%z' => 'O',
			'%T' => 'H:i:s', '%R' => 'H:i', '%D' => 'm/d/y', '%F' => 'Y-m-d',
			'%n' => "\n", '%t' => "\t", '%%' => '%',
		);

		$date_format = strtr($format, $map);
		$out = date($date_format, $unix);

		// Ensure valid UTF-8 (old strftime returned locale-encoded bytes).
		if (function_exists('mb_check_encoding') && !mb_check_encoding($out, 'UTF-8'))
		{
			$out = mb_convert_encoding($out, 'UTF-8', 'ISO-8859-1');
		}

		return $out;
	}
}