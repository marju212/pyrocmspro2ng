<script type="text/javascript" src="<?php echo BASE_URL?>system/cms/themes/pyrocms/js/tinymce/tinymce.min.js"></script>
<script type="text/javascript">

	if (typeof SITE_URL === 'undefined') { var SITE_URL = "<?php echo site_url() ?>"; }

	var pyroEditorPlugins = {
		pyroimages: '<?php echo BASE_URL ?>system/cms/modules/wysiwyg/tinymce/plugins/pyroimages/plugin.js',
		pyrofiles:  '<?php echo BASE_URL ?>system/cms/modules/wysiwyg/tinymce/plugins/pyrofiles/plugin.js'
	};

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

			pyro.init_ckeditor = pyro.init_wysiwyg;

			pyro.init_wysiwyg();

		});
	})(jQuery);
</script>
