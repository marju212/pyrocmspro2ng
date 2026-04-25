<script type="text/javascript">var SITE_URL	= "<?php echo site_url() ?>";</script>

<?php
	$this->admin_theme = $this->theme_m->get_admin();
	Asset::add_path('admin', $this->admin_theme->web_path.'/');
?>

<script type="text/javascript">pyro = {};</script>
<script src="<?php echo Asset::get_filepath_js('admin::tinymce/tinymce.min.js') ?>"></script>

<script type="text/javascript">

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
				<?php echo $this->parser->parse_string(Settings::get('wysiwyg_config'), $this, true) ?>
				if (typeof pyro.init_wysiwyg_maximize === 'function') {
					pyro.init_wysiwyg_maximize();
				}
			};

			pyro.init_ckeditor = pyro.init_wysiwyg;

			pyro.init_wysiwyg();

		});
	})(jQuery);
</script>
