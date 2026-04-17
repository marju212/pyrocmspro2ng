<?php defined('BASEPATH') OR exit('No direct script access allowed');

/**
 * Drop-in replacement for CI 3.x's deprecated CI_Encrypt that uses OpenSSL
 * instead of mcrypt (which was removed in PHP 7.2).
 *
 * Why this exists: CI_Encrypt::__construct() hard-fatals with
 * "The Encrypt library requires the Mcrypt extension" the moment it's
 * instantiated, so a normal subclass override doesn't help — parent::__construct
 * blows up before MY_Encrypt's body runs. This class deliberately does NOT
 * extend CI_Encrypt; CI's loader still treats it as the encrypt library because
 * the file lives at libraries/MY_Encrypt.php and exposes the same public API
 * surface used inside PyroCMS:
 *
 *   - $encryption_key (public)
 *   - encode($string, $key = '')
 *   - decode($string, $key = '')
 *   - set_key($key) / get_key($key = '')
 *   - hash($str)
 *
 * Cipher: AES-128-CBC with a random IV per encryption. Output is base64 of
 * IV || ciphertext, with extra base64 wrapping to keep the on-the-wire shape
 * identical to what PyroCMS's JS-side decoder expects (`base64_decode` once,
 * then opaque blob).
 *
 * Backwards compatibility: on this site no PyroStreams `encrypt` field type is
 * in use (verified at upgrade time), so we do not need to read mcrypt-era
 * ciphertext. If a future install needs that, add an `encode_from_legacy()`
 * fallback that decrypts the old format and re-encodes.
 */
class MY_Encrypt
{
	public $encryption_key = '';

	protected $_cipher = 'AES-128-CBC';
	protected $_hash_type = 'sha256';

	public function __construct()
	{
		log_message('info', 'MY_Encrypt (OpenSSL replacement for CI_Encrypt) initialised');
	}

	public function set_key($key = '')
	{
		$this->encryption_key = $key;
		return $this;
	}

	public function get_key($key = '')
	{
		if ($key === '')
		{
			if ($this->encryption_key !== '')
			{
				return $this->encryption_key;
			}

			$key = config_item('encryption_key');

			if ($key === '' OR $key === null)
			{
				show_error('In order to use the Encrypt class you must set an encryption_key in your config.');
			}
		}

		// CI_Encrypt always returned md5($key) (16 bytes hex) — preserve that
		// so set_key()-then-get_key() still produces a deterministic 16-byte key
		// that AES-128 wants.
		return md5($key);
	}

	public function encode($string, $key = '')
	{
		$key = $this->get_key($key);
		$iv_size = openssl_cipher_iv_length($this->_cipher);
		$iv = openssl_random_pseudo_bytes($iv_size);
		$ciphertext = openssl_encrypt($string, $this->_cipher, $key, OPENSSL_RAW_DATA, $iv);

		if ($ciphertext === false)
		{
			return false;
		}

		return base64_encode($iv.$ciphertext);
	}

	public function decode($string, $key = '')
	{
		// Match CI_Encrypt's input validation: reject anything that isn't valid
		// base64 (so a tampered/garbage string returns FALSE, not noisy errors).
		if (preg_match('/[^a-zA-Z0-9\/\+=]/', $string) OR base64_encode(base64_decode($string, true)) !== $string)
		{
			return false;
		}

		$blob = base64_decode($string);
		$iv_size = openssl_cipher_iv_length($this->_cipher);

		if (strlen($blob) <= $iv_size)
		{
			return false;
		}

		$iv = substr($blob, 0, $iv_size);
		$ciphertext = substr($blob, $iv_size);
		$plain = openssl_decrypt($ciphertext, $this->_cipher, $this->get_key($key), OPENSSL_RAW_DATA, $iv);

		return ($plain === false) ? false : $plain;
	}

	public function hash($str)
	{
		return hash($this->_hash_type, $str);
	}

	public function set_hash($type = 'sha256')
	{
		$this->_hash_type = in_array($type, hash_algos(), true) ? $type : 'sha256';
	}
}
