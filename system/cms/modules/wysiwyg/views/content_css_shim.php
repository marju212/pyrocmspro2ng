<?php
defined('BASEPATH') OR exit('No direct script access allowed');

// Shared TinyMCE content_css/body_class injector — included from every fragment
// view that loads tinymce.min.js so the editor iframe renders content with the
// active site's public theme CSS, matching what visitors see.
//
// Wrapping tinymce.init (rather than editing the admin-editable wysiwyg_config
// seed) means this works regardless of how the JSON config has been customized,
// and applies uniformly to every editor profile (intro, simple, advanced, and
// any future ones an admin adds).

ci()->load->helper('wysiwyg/wysiwyg');
$pyroTheme    = wysiwyg_resolve_content_css();
$pyroSanitize = (function_exists('pyro_env_bool') && pyro_env_bool('WYSIWYG_SANITIZE', true));

// Always append the editor-only prose stylesheet last so its typography wins
// inside the iframe regardless of what the theme set. cache-bust on filemtime.
$prose_path = APPPATH.'modules/wysiwyg/css/prose.css';
$prose_url  = BASE_URL.'system/cms/modules/wysiwyg/css/prose.css'.(is_file($prose_path) ? '?v='.filemtime($prose_path) : '');
$pyroTheme['css'][] = $prose_url;
?>
<script>
	var pyroEditorContentCss = <?php echo json_encode($pyroTheme['css']); ?>;
	var pyroEditorBodyClass  = <?php echo json_encode($pyroTheme['body_class']); ?>;
	var pyroEditorSanitize   = <?php echo $pyroSanitize ? 'true' : 'false'; ?>;

	(function(){
		if (!window.tinymce || window.tinymce.__pyroContentCssShimInstalled) return;
		var origInit = window.tinymce.init.bind(window.tinymce);
		window.tinymce.init = function(opts) {
			opts = opts || {};

			var existing = opts.content_css;
			var extra    = (window.pyroEditorContentCss || []).slice();
			if (Array.isArray(existing))                              opts.content_css = existing.concat(extra);
			else if (typeof existing === 'string' && existing.length) opts.content_css = [existing].concat(extra);
			else                                                      opts.content_css = extra;

			// Always tag the iframe body with .page-chunk so prose.css rules apply,
			// then layer on the theme manifest's body_class and the per-chunk class.
			var bodyClasses = ['page-chunk'];
			if (window.pyroEditorBodyClass) bodyClasses.push(window.pyroEditorBodyClass);
			if (opts.body_class) bodyClasses.push(opts.body_class);
			opts.body_class = bodyClasses.join(' ');

			// Editor-only page chrome: white readable surface, centered column, no
			// theme hero image. prose.css ships only element-level rules; container
			// sizing belongs here so the public stylesheet doesn't constrain layout.
			// max-width on images defends against the explicit width attrs TinyMCE
			// adds when inserting from the file picker.
			var editorChrome = [
				'html,body{background:#fff!important;background-image:none!important;}',
				'body{padding:1rem 1.25rem 3rem;}',
				'.page-chunk img,.page-chunk video{max-width:100%!important;height:auto!important;}'
			].join(' ');
			opts.content_style = opts.content_style ? (opts.content_style + ' ' + editorChrome) : editorChrome;

			// Forward protection: keep TinyMCE from persisting CK-era inline
			// typography. Only set defaults if the admin's wysiwyg_config
			// hasn't already specified them. Skipped entirely when
			// WYSIWYG_SANITIZE=false in .env.
			if (window.pyroEditorSanitize) {
				if (typeof opts.valid_styles === 'undefined') {
					opts.valid_styles = {
						'*': 'text-align,float,clear,vertical-align,width,height,max-width,margin,margin-top,margin-right,margin-bottom,margin-left,padding,padding-top,padding-right,padding-bottom,padding-left,display'
					};
				}
				if (typeof opts.invalid_elements === 'undefined') {
					opts.invalid_elements = 'font,center,o:p,u';
				}
			}

			var origSetup = opts.setup;
			opts.setup = function(editor) {
				editor.on('PreInit', function(){
					try {
						var ta = editor.getElement();
						var chunkClass = ta && ta.getAttribute('data-chunk-class');
						if (chunkClass) {
							var body = editor.getBody();
							if (body) body.className = (body.className + ' ' + chunkClass).trim();
						}
					} catch (e) {}
				});
				if (typeof origSetup === 'function') origSetup(editor);
			};

			return origInit(opts);
		};
		window.tinymce.__pyroContentCssShimInstalled = true;
	})();
</script>
