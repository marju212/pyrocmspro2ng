<?php if (defined('PYRO_WYSIWYG_FRAGMENT_RENDERED')) return; define('PYRO_WYSIWYG_FRAGMENT_RENDERED', true); ?>
<script type="text/javascript" src="<?php echo BASE_URL?>system/cms/themes/admin/js/tinymce/tinymce.min.js"></script>
<script type="text/javascript">

	// Iframe-side scripts (system/cms/modules/wysiwyg/js/wysiwyg.js) read SITE_URL
	// via window.parent.SITE_URL if it's not set in their own scope, so make sure
	// it is defined on the host page.
	if (typeof SITE_URL === 'undefined') { var SITE_URL = "<?php echo site_url() ?>"; }

	// Map of custom-plugin URLs the seed config injects via external_plugins.
	var pyroEditorPlugins = {
		pyroimages: '<?php echo BASE_URL ?>system/cms/modules/wysiwyg/tinymce/plugins/pyroimages/plugin.js',
		pyrofiles:  '<?php echo BASE_URL ?>system/cms/modules/wysiwyg/tinymce/plugins/pyrofiles/plugin.js'
	};

	// Held by the iframe-side helper as the "active editor" reference at the
	// moment a dialog is opened; consumed inside wysiwyg.js' insertImage/insertFile.
	var instance = null;
	function update_instance()
	{
		instance = (window.tinymce && window.tinymce.activeEditor) || null;
	}
</script>

<?php include APPPATH.'modules/wysiwyg/views/content_css_shim.php'; ?>

<script type="text/javascript">
	(function($) {
		$(function(){

			pyro.init_wysiwyg = function(){
				<?php echo $this->parser->parse_string(Settings::get('wysiwyg_config'), $this, TRUE); ?>
				if (typeof pyro.init_wysiwyg_maximize === 'function') {
					pyro.init_wysiwyg_maximize();
				}
			};

			// Backwards-compat alias for callers that still reference init_ckeditor.
			pyro.init_ckeditor = pyro.init_wysiwyg;

			pyro.init_wysiwyg();

		});
	})(jQuery);
</script>
