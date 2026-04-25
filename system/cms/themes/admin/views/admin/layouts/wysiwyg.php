<!DOCTYPE html>
<html>
<head>
	<meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
	<!-- Always force latest IE rendering engine & Chrome Frame -->
	<meta http-equiv="X-UA-Compatible" content="IE=edge,chrome=1">

	<title><?php echo $template['title']; ?></title>

	<script type="text/javascript">
		var APPPATH_URI = "<?php echo APPPATH_URI;?>";
		var BASE_URL = "<?php echo rtrim(site_url(), '/').'/';?>";
		var SITE_URL = "<?php echo rtrim(site_url(), '/').'/';?>";
		var BASE_URI = "<?php echo BASE_URI;?>";
	</script>

	<?php echo $template['metadata']; ?>

	<script type="text/javascript">

		// Closes the dialog this iframe is hosted in. Used by the
		// file-picker views via insertImage()/insertFile() in wysiwyg.js,
		// which now talks directly to window.parent.tinymce.
		function windowClose()
		{
			var ed = window.parent && window.parent.tinymce && window.parent.tinymce.activeEditor;
			if (ed && ed.windowManager && typeof ed.windowManager.close === 'function') {
				ed.windowManager.close();
			}
		}

		function insertHTML(html)
		{
			var ed = window.parent && window.parent.tinymce && window.parent.tinymce.activeEditor;
			if (ed) {
				ed.insertContent(html);
			}
		}

		(function($)
		{
			$(function()
			{
				// Fancybox modal window
				$('a[rel=modal], a.modal').livequery(function() {
					$(this).fancybox({
						overlayOpacity: 0.8,
						overlayColor: '#000',
						hideOnContentClick: false,
						onClosed: function(){ location.reload(); }
					});
				});
			});
		})(jQuery);
	</script>

	<?php echo Asset::css('admin/basic_layout.css'); ?>
</head>
<body>
	<?php $this->load->view('admin/partials/notices') ?>
	<?php echo $template['body']; ?>
</body>
</html>
