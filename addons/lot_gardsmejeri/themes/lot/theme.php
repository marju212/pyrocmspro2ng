<?php defined('BASEPATH') OR exit('No direct script access allowed');

class Theme_Lot extends Theme {

    public $name			= 'Löt';
    public $author			= 'Marcus';
    public $author_website              = 'http://www.incore.se/';
    public $website			= 'http://www.incore.se/';
    public $description                 = 'Löt gårdsmejeri';
    public $version			= '0.1';
	
	public function __construct()
	{
		$supported_lang	= config_item('supported_languages');

		$cufon_enabled	= $supported_lang[CURRENT_LANGUAGE]['direction'] !== 'rtl';
		$cufon_font		= 'qk.font.js';

		// Translators, only if the default font is incompatible with the chars of your
		// language generate a new font (link: <http://cufon.shoqolate.com/generate/>) and add
		// your case in switch bellow. Important: use a licensed font and harmonic with design

		switch (CURRENT_LANGUAGE)
		{
			case 'zh':
				$cufon_enabled	= false;
				break;
			case 'ar':
				$cufon_enabled = false;
				break;
			case 'he':
				$cufon_enabled	= true;
			case 'ru':
				$cufon_font		= 'times.font.js';
				break;
		}

		//Settings::temp('theme_lot', compact('cufon_enabled', 'cufon_font'));
	}
}

/* End of file theme.php */
