/**
 * Iframe-side script for the WYSIWYG file/image picker.
 *
 * Runs inside the dialog iframe opened by the pyroimages / pyrofiles
 * TinyMCE plugins. Talks back to the parent window via
 *   window.parent.tinymce.activeEditor.insertContent(html)
 * Replaces an existing pyro-image/pyro-file element if one is selected.
 */

var img_float;
var replace_html = null;

function getParentEditor() {
    return (window.parent && window.parent.tinymce && window.parent.tinymce.activeEditor) || null;
}

function closeDialog() {
    var ed = getParentEditor();
    if (ed && ed.windowManager && typeof ed.windowManager.close === 'function') {
        ed.windowManager.close();
    }
}

function insertImage(file, alt, location, path)
{
    var editor = getParentEditor();
    if (!editor) {
        return;
    }

    if (replace_html && replace_html.parentNode) {
        replace_html.parentNode.removeChild(replace_html);
        replace_html = null;
    }

    var img_width = parseInt(document.getElementById('insert_width').value, 10);
    if (isNaN(img_width) || img_width < 0) {
        img_width = 0;
    }

    if (location == 'local') {
        path = '{{ url:site }}files/' + (img_width > 0 ? 'thumb/' + file + '/' + img_width : 'large/' + file);
    }

    var width_tag = (img_width > 0 ? ' width="' + img_width + '"' : '');
    var float_value = $('input[name=insert_float]:checked').val();
    var float_tag = (float_value && float_value !== 'none' ? ' style="float:' + float_value + '"' : '');
    var class_alignment = float_value ? ' alignment-' + float_value : '';

    editor.insertContent('<img class="pyro-image' + class_alignment + '"' + width_tag + float_tag + ' src="' + path + '" alt="' + (alt || '') + '" />');
    closeDialog();
}

function insertFile(id, title, location, path)
{
    var editor = getParentEditor();
    if (!editor) {
        return;
    }

    if (location == 'local') {
        path = '{{ url:site }}files/download/' + id;
    }

    if (replace_html && replace_html.parentNode) {
        replace_html.parentNode.removeChild(replace_html);
        replace_html = null;
    }

    editor.insertContent('<a class="pyro-file" href="' + path + '">' + title + '</a>');
    closeDialog();
}

$(function ()
{
    function detectFile()
    {
        var editor = getParentEditor();
        if (!editor || !editor.selection) {
            return false;
        }

        var node = editor.selection.getNode();
        if (!node) {
            return false;
        }

        var img = (node.tagName === 'IMG') ? node : (node.closest && node.closest('img'));
        if (!img || !img.classList || !img.classList.contains('pyro-image')) {
            return false;
        }

        replace_html = img;
        return true;
    }

    detectFile() || $('#current_document h2').hide();

    // tooltip
    $('#images-container img').hover(function () {
        $(this).attr('title', 'Click to insert image');
    });

    /**
     * left files navigation handler
     *  - handles loading of different folders
     *  - manipulates dom classes etc
     */
    $('#files-nav li a').live('click', function (e) {

        e.preventDefault();

        var href_val = $(this).attr('href');

        // remove existing 'current' classes
        $('#files-nav li').removeClass('current');

        // add class to clicked anchor parent
        $(this).parent('li').addClass('current');

        // remove any notifications
        $('div.notification').fadeOut('fast');

        if ($(this).attr('title') != 'upload')
        {
            $('#files_right_pane').load(href_val + ' #files-wrapper', function () {
                $(this).children().fadeIn('slow');
            });
        }
        else
        {
            var box = $('#upload-box');
            if (box.is(":visible"))
            {
                box.fadeOut(200);
            }
            else
            {
                box.fadeIn(200);
            }
        }
    });

    $('#upload-box span.close, #upload-box a.cancel').on('click', function (e) {
        e.preventDefault();
        $('#upload-box').fadeOut(200, function () {
            $(this).find('input[type=text], input[type=file]').val('');
        });
    });

    $('select[name=parent_id]').live('change', function () {
        var folder_id = $(this).val();
        var controller = $(this).attr('title');
        var href_val = SITE_URL + 'admin/wysiwyg/' + controller + '/index/' + folder_id;
        $('#files_right_pane').load(href_val + ' #files-wrapper', function () {
            $(this).children().fadeIn('slow');
            var class_exists = $('#folder-id-' + folder_id).html();
            $('div.notification').fadeOut('fast');
            if (class_exists !== null)
            {
                $('#files-nav li').removeClass('current');
                $('li#folder-id-' + folder_id).addClass('current');
            }
        });
    });

    // slider
    $('#slider').livequery(function () {
        $(this).fadeIn('slow');
        $(this).slider({
            value: 0,
            min: 0,
            max: 1000,
            step: 1,
            slide: function (event, ui) {
                if (ui.value > 0) {
                    $('#insert_width').val(ui.value);
                } else {
                    $('#insert_width').val($('#insert_width').attr('data-name'));
                }
            }
        });

        $('#insert_width').val($('#insert_width').attr('data-name'));
    });

    $('#radio-group').livequery(function () {
        $(this).children('.set').buttonset();
        $(this).fadeIn('slow');
    });

    $('#files_right_pane').livequery(function () {
        $(this).children().fadeIn('slow');
        $('#upload-box').hide();
    });

    // Add the close link to all alert boxes
    $('.alert').livequery(function () {
        $(this).prepend('<a href="#" class="close">x</a>');
    });

    // Close the notifications when the close link is clicked
    $('a.close').live('click', function (e) {
        e.preventDefault();
        $(this).fadeTo(200, 0);
        $(this).parent().fadeTo(200, 0);
        $(this).parent().slideUp(400, function () {
            $(window).trigger('notification-closed');
            $(this).remove();
        });
    });
});
