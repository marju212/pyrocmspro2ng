<?php defined('BASEPATH') OR exit('No direct script access allowed');

/**
 * Resolve the active public theme's CSS for the current site so it can be
 * injected into TinyMCE's content_css, giving editors the same look as
 * the rendered page. The active theme is per-site (Settings::get('default_theme')
 * runs against the per-site settings table because MY_Controller has already
 * set the SITE_REF db prefix).
 *
 * Themes can opt in to a manifest at <theme>/wysiwyg.php returning:
 *   return array(
 *     'css'        => array('bstr.css', 'main.css'),     // load order matters
 *     'body_class' => 'page-content',
 *     'extra_css'  => array('https://fonts.googleapis.com/...'),
 *   );
 * If absent, we fall back to globbing top-level *.css files in the theme's css/ dir.
 *
 * @return array{css: string[], body_class: string}
 */
if ( ! function_exists('wysiwyg_resolve_content_css'))
{
	function wysiwyg_resolve_content_css()
	{
		static $cache = null;
		if ($cache !== null) return $cache;

		$empty = array('css' => array(), 'body_class' => '');
		$ci    = ci();

		$slug = Settings::get('default_theme');
		if ( ! $slug) return $cache = $empty;

		$ci->load->model('addons/theme_m');
		$theme = $ci->theme_m->get($slug);
		if ( ! $theme || empty($theme->path)) return $cache = $empty;

		$body_class = '';
		$files      = array();
		$extra      = array();

		$manifest_path = rtrim($theme->path, '/').'/wysiwyg.php';
		if (is_file($manifest_path))
		{
			$manifest = include $manifest_path;
			if (is_array($manifest))
			{
				if ( ! empty($manifest['css']) && is_array($manifest['css'])) $files = $manifest['css'];
				if ( ! empty($manifest['extra_css']) && is_array($manifest['extra_css'])) $extra = $manifest['extra_css'];
				if ( ! empty($manifest['body_class'])) $body_class = (string) $manifest['body_class'];
			}
		}
		else
		{
			$css_dir = rtrim($theme->path, '/').'/css';
			if (is_dir($css_dir))
			{
				$found = glob($css_dir.'/*.css') ?: array();
				sort($found);
				foreach ($found as $abs) $files[] = basename($abs);
			}
		}

		// Match the front-end's URL-prefix logic: CDN if configured, else BASE_URL.
		// See system/cms/core/Public_Controller.php (cdn_domain handling).
		$prefix = BASE_URL;
		if ($cdn = Settings::get('cdn_domain'))
		{
			$protocol = ( ! empty($_SERVER['HTTPS']) && strtolower($_SERVER['HTTPS']) !== 'off') ? 'https' : 'http';
			$prefix   = $protocol.'://'.rtrim($cdn, '/').'/';
		}

		$theme_url = $prefix.trim($theme->web_path, '/').'/css/';

		$urls = array();
		foreach ($files as $file)
		{
			// Allow manifest entries to be absolute URLs too
			if (strpos($file, '//') !== false || strpos($file, 'http') === 0) $urls[] = $file;
			else $urls[] = $theme_url.ltrim($file, '/');
		}
		foreach ($extra as $url) $urls[] = $url;

		return $cache = array('css' => $urls, 'body_class' => $body_class);
	}
}
