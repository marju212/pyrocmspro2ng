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
$pyroTheme = wysiwyg_resolve_content_css();
?>
<script>
	var pyroEditorContentCss = <?php echo json_encode($pyroTheme['css']); ?>;
	var pyroEditorBodyClass  = <?php echo json_encode($pyroTheme['body_class']); ?>;

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

			if (!opts.body_class && window.pyroEditorBodyClass) opts.body_class = window.pyroEditorBodyClass;

			// Force a readable editing surface regardless of theme: white background,
			// dark text. Theme typography (font, sizes, headings) still applies via
			// content_css — only the page chrome is overridden.
			var bgReset = 'html,body{background:#fff!important;background-image:none!important;color:#222!important;}';
			opts.content_style = opts.content_style ? (opts.content_style + ' ' + bgReset) : bgReset;

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
