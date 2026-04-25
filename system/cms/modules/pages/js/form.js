(function($) {
	$(function(){

		// Generate a slug from the title
		if ($('#page-form').data('mode') == 'create') {
			pyro.generate_slug('input[name="title"]', 'input[name="slug"]');
		}

		// needed so that Keywords can return empty JSON
		$.ajaxSetup({
			allowEmpty: true
		});

		$('#meta_keywords').tagsInput({
			autocomplete_url: SITE_URL + 'admin/keywords/autocomplete'
		});
		
		// Hide the huge textarea
		$('#page-chunks').on('mousedown', '.sort-handle', function(e){
			$('.chunky').hide();
		});
		
		// Attach sortable to the page chunks ul
		$("#page-chunks").sortable({
			opacity:0.3,
			handle: ".sort-handle",
			placeholder: 'sort-placeholder',
			forcePlaceholderSize: true,
			items: 'li',
			cursor:"move",
			start: function () {
				if (typeof tinymce !== 'undefined') {
					tinymce.remove('textarea.wysiwyg-advanced, textarea.wysiwyg-simple');
				}
			},
			stop: function(ev,ui){
				$('.chunky').show();
				pyro.init_wysiwyg();
			}
		});
		
		// add another page chunk
		$('a.add-chunk').live('click', function(e){
			e.preventDefault();

			// The date in hexdec
			key = Number(new Date()).toString(16).substr(-5, 5);

			$('#page-chunks').append('<li class="page-chunk">' +
				'<input class="label" type="text" name="chunk_slug[' + key + ']" value="' + key + '"/>' +
				'<input class="label" type="text" name="chunk_class[' + key + ']" />' +
				'<select name="chunk_type[' + key + ']">' +
				'<option value="html">html</option>' +
				'<option value="markdown">markdown</option>' +
				'<option value="wysiwyg-simple">wysiwyg-simple</option>' +
				'<option selected="selected" value="wysiwyg-advanced">wysiwyg-advanced</option>' +
				'</select>' +
				'<div class="alignright">' +
				'<a href="javascript:void(0)" class="remove-chunk btn red">' + pyro.lang.remove + '</a>' +
				'<span class="sort-handle"></span>' +
				'</div><br style="clear:both" />' +
				'<span class="chunky"><textarea id="' + key + '" class="pages wysiwyg-advanced" rows="20" style="width:100%" name="chunk_body[' + key + ']" data-chunk-class=""></textarea>' +
				'</span></li>');

			// initialize the editor using the view from fragments/wysiwyg.php
			pyro.init_wysiwyg();
			$("#page-chunks").sortable("refresh");

			// Update Chosen
			pyro.chosen();
		});

		$('a.remove-chunk').live('click', function(e) {
			e.preventDefault();

			var removemsg = $(this).attr('title');

			if (confirm(removemsg || pyro.lang.dialog_message))
			{
				$(this).closest('li.page-chunk').slideUp('slow', function(){ $(this).remove(); });
				if ($('#page-content').find('li.page-chunk').length < 2) {
				}
			}
		});

		// Reflect the chunk's user-set CSS class onto the editor body via the
		// shim in content_css_shim.php — keeps WYSIWYG preview in sync with how
		// the chunk will render on the front-end.
		$('input[name^=chunk_class]').live('change', function() {
			var chunk    = $(this).closest('li.page-chunk');
			var textarea = $('textarea', chunk);
			textarea.attr('data-chunk-class', this.value || '');

			if (typeof tinymce !== 'undefined' &&
				(textarea.hasClass('wysiwyg-simple') || textarea.hasClass('wysiwyg-advanced'))) {
				tinymce.remove('#' + textarea.attr('id'));
				pyro.init_wysiwyg();
			}
		});

		$('select[name^=chunk_type]').live('change', function() {
			chunk = $(this).closest('li.page-chunk');
			textarea = $('textarea', chunk);

			// Destroy existing WYSIWYG instance attached to this textarea.
			if (textarea.hasClass('wysiwyg-simple') || textarea.hasClass('wysiwyg-advanced'))
			{
				textarea.removeClass('wysiwyg-simple');
				textarea.removeClass('wysiwyg-advanced');

				if (typeof tinymce !== 'undefined') {
					tinymce.remove('#' + textarea.attr('id'));
				}
			}

			// Set up the new instance
			textarea.addClass(this.value);

			pyro.init_wysiwyg();
		});

	});

})(jQuery);