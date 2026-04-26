<?php
// Intentionally NO `defined('BASEPATH')` guard — this class is also loaded
// directly by the standalone CLI cleanup script (system/cms/scripts/...),
// which doesn't bootstrap CodeIgniter.

/**
 * Strip CKEditor-era inline typography from rich-text bodies.
 *
 * Conservative by design: removes font/color/family/size etc. so the
 * site-wide prose.css can win the cascade, but preserves text alignment,
 * floats, image dimensions, and other deliberate layout choices.
 *
 * Idempotent — running clean() twice on the same input yields the same
 * output. Safe to call from a save hook AND from the one-shot CLI script.
 */
class Wysiwyg_sanitizer
{
	/** CSS properties that get stripped from every `style` attribute. */
	private static $strip_props = array(
		'font', 'font-size', 'font-family', 'font-weight', 'font-style',
		'font-variant', 'font-stretch', 'color', 'background', 'background-color',
		'background-image', 'line-height', 'letter-spacing', 'word-spacing',
		'text-decoration', 'text-decoration-line', 'text-decoration-color',
		'text-decoration-style', 'text-shadow', 'text-transform',
		'-webkit-text-size-adjust', 'mso-ansi-font-size', 'mso-bidi-font-size',
		'mso-fareast-font-family',
	);

	/** Tags that get unwrapped (children kept, tag itself removed). */
	private static $unwrap_tags = array('font', 'center', 'u');

	/**
	 * Cheap pre-filter: returns true only if the input shows visible signs
	 * of legacy CK-era / Word junk we'd actually strip. Lets the CLI avoid
	 * touching pristine rows where DOMDocument round-tripping would otherwise
	 * cause cosmetic-only diffs (void-tag normalization, entity decoding).
	 */
	public static function needs_cleaning($html)
	{
		if ( ! is_string($html) || $html === '') return false;
		// Inline-style typography keys, legacy tags, Word classes, vendor styles.
		return (bool) preg_match(
			'/(?:'
			. 'style\s*=\s*["\'][^"\']*(?:font|color|line-height|letter-spacing|background)'
			. '|<font\b'
			. '|<center\b'
			. '|<u>'
			. '|<\w+:\w+'        // <o:p>, <w:WordDocument>, etc.
			. '|class\s*=\s*["\'][^"\']*Mso\w*'
			. '|mso-\w+'
			. ')/i',
			$html
		);
	}

	/**
	 * Clean an HTML fragment. Returns the rewritten HTML, or the original
	 * input if parsing fails — never returns a partial/broken result.
	 */
	public static function clean($html)
	{
		if ( ! is_string($html) || $html === '' || trim($html) === '')
		{
			return $html;
		}

		// Protect Lex tags ({{ … }}) before parsing — DOMDocument otherwise
		// URL-encodes them when they appear inside attribute values, which
		// silently breaks the public-site templating. We swap them for inert
		// placeholder tokens, parse, then restore verbatim.
		$lex_tokens = array();
		$protected  = preg_replace_callback(
			'/\{\{[^{}]*\}\}/',
			function ($m) use (&$lex_tokens) {
				$i = count($lex_tokens);
				$lex_tokens[] = $m[0];
				return "PYROLEX{$i}XELORYP";
			},
			$html
		);

		// DOMDocument needs a single root element to round-trip a fragment
		// reliably. We wrap, parse, mutate, then unwrap.
		$wrapped = '<div id="__pyro_root__">'.$protected.'</div>';

		$dom = new DOMDocument('1.0', 'UTF-8');
		$prev = libxml_use_internal_errors(true);
		// UTF-8 declaration prevents loadHTML's latin1 default from mangling
		// multibyte chars. NOIMPLIED + NODEFDTD keep DOMDocument from adding
		// <html><body> wrappers around the fragment.
		$loaded = $dom->loadHTML(
			'<?xml encoding="UTF-8">'.$wrapped,
			LIBXML_HTML_NOIMPLIED | LIBXML_HTML_NODEFDTD
		);
		libxml_clear_errors();
		libxml_use_internal_errors($prev);

		if ( ! $loaded)
		{
			return $html;
		}

		// Drop the XML encoding processing instruction we added on input.
		foreach (iterator_to_array($dom->childNodes) as $child)
		{
			if ($child->nodeType === XML_PI_NODE) $dom->removeChild($child);
		}

		$root = $dom->getElementById('__pyro_root__');
		if ( ! $root)
		{
			// Fall back to first element if the id wasn't preserved.
			$root = $dom->getElementsByTagName('div')->item(0);
			if ( ! $root) return $html;
		}

		$xpath  = new DOMXPath($dom);
		$unwrap = array();

		foreach ($xpath->query('.//*', $root) as $el)
		{
			$tag = strtolower($el->nodeName);

			// Word/Office namespace junk: <o:p>, <w:*>, etc.
			if (strpos($tag, ':') !== false)
			{
				$unwrap[] = $el;
				continue;
			}

			if (in_array($tag, self::$unwrap_tags, true))
			{
				$unwrap[] = $el;
				// Don't `continue` — also clean its attrs in case the unwrap
				// queue order changes; cleaning a soon-to-be-removed element
				// is a cheap no-op.
			}

			if ($el->hasAttribute('style'))
			{
				$cleaned = self::clean_style($el->getAttribute('style'));
				if ($cleaned === '') $el->removeAttribute('style');
				else                 $el->setAttribute('style', $cleaned);
			}

			if ($el->hasAttribute('class'))
			{
				$cleaned = self::clean_class($el->getAttribute('class'));
				if ($cleaned === '') $el->removeAttribute('class');
				else                 $el->setAttribute('class', $cleaned);
			}

			// Drop CKEditor / Word data-* leftovers that nothing reads.
			foreach (array('lang', 'xml:lang') as $attr)
			{
				if ($el->hasAttribute($attr)) $el->removeAttribute($attr);
			}

			// Spans with no remaining attributes carry no meaning — unwrap.
			if ($tag === 'span' && $el->attributes->length === 0)
			{
				$unwrap[] = $el;
			}
		}

		// Unwrap children-before-parents so nested unwraps stay valid.
		// Iterate in reverse document order via depth ranking.
		usort($unwrap, function($a, $b) {
			return self::depth($b) - self::depth($a);
		});
		foreach ($unwrap as $el)
		{
			if ( ! $el->parentNode) continue; // already removed via ancestor
			while ($el->firstChild)
			{
				$el->parentNode->insertBefore($el->firstChild, $el);
			}
			$el->parentNode->removeChild($el);
		}

		// Serialize the root's children only — we don't want the wrapper div.
		$out = '';
		foreach ($root->childNodes as $child)
		{
			$out .= $dom->saveHTML($child);
		}

		// DOMDocument tends to encode &nbsp; as &#xA0; — normalize back to
		// the more readable entity to keep diffs minimal vs. CK-era content.
		$out = str_replace(array("\xc2\xa0", '&#xA0;', '&#160;'), '&nbsp;', $out);

		// Restore protected Lex tags.
		if ($lex_tokens)
		{
			$out = preg_replace_callback(
				'/PYROLEX(\d+)XELORYP/',
				function ($m) use ($lex_tokens) {
					return isset($lex_tokens[$m[1]]) ? $lex_tokens[$m[1]] : $m[0];
				},
				$out
			);
		}

		return $out;
	}

	/**
	 * Strip blacklisted CSS props from a style attribute value, return the
	 * remaining declarations as a normalized "prop:value;prop:value" string.
	 */
	private static function clean_style($style)
	{
		$kept = array();
		foreach (explode(';', $style) as $decl)
		{
			$decl = trim($decl);
			if ($decl === '') continue;
			$colon = strpos($decl, ':');
			if ($colon === false) continue;

			$prop = strtolower(trim(substr($decl, 0, $colon)));
			$val  = trim(substr($decl, $colon + 1));
			if ($prop === '' || $val === '') continue;

			// Drop our blacklist + any vendor-prefixed Mso-* that snuck in.
			if (in_array($prop, self::$strip_props, true)) continue;
			if (strpos($prop, 'mso-') === 0)               continue;

			$kept[] = $prop.': '.$val;
		}
		return implode('; ', $kept);
	}

	/** Drop Word `Mso*` class tokens; return remaining tokens space-joined. */
	private static function clean_class($class)
	{
		$kept = array();
		foreach (preg_split('/\s+/', trim($class)) as $tok)
		{
			if ($tok === '')                        continue;
			if (preg_match('/^Mso\w*/i', $tok))     continue;
			$kept[] = $tok;
		}
		return implode(' ', $kept);
	}

	private static function depth(DOMNode $n)
	{
		$d = 0;
		while ($n->parentNode) { $n = $n->parentNode; $d++; }
		return $d;
	}
}
