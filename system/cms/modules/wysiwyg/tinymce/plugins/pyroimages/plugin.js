/**
 * pyroimages TinyMCE 6 plugin
 *
 * Opens admin/wysiwyg/image as an iframe dialog and rewrites
 * {{ url:site }} / {{ url:base }} to absolute URLs in image src
 * attributes when content is loaded into the editor (so previews
 * render correctly during editing).
 *
 * The iframe view calls window.parent.tinymce.activeEditor.insertContent(...)
 * via system/cms/modules/wysiwyg/js/wysiwyg.js.
 */
(function () {
    'use strict';

    function rewriteUrlTokens(html) {
        if (typeof html !== 'string') {
            return html;
        }
        var siteUrl = (typeof SITE_URL !== 'undefined') ? SITE_URL : '';
        var baseUrl = (typeof BASE_URL !== 'undefined') ? BASE_URL : '';
        return html
            .replace(/{{ url:site }}/g, siteUrl)
            .replace(/%7B%7B%20url:site%20%7D%7D/g, siteUrl)
            .replace(/{{ url:base }}/g, baseUrl)
            .replace(/%7B%7B%20url:base%20%7D%7D/g, baseUrl);
    }

    tinymce.PluginManager.add('pyroimages', function (editor) {
        editor.ui.registry.addButton('pyroimages', {
            icon: 'image',
            tooltip: 'Upload or insert images from library',
            onAction: function () {
                if (typeof window.update_instance === 'function') {
                    window.update_instance();
                }
                editor.windowManager.openUrl({
                    title: 'Image',
                    url: (typeof SITE_URL !== 'undefined' ? SITE_URL : '/') + 'admin/wysiwyg/image',
                    width: 1000,
                    height: 600,
                    buttons: [
                        { type: 'cancel', name: 'cancel', text: 'Close' }
                    ]
                });
            }
        });

        editor.on('BeforeSetContent', function (e) {
            e.content = rewriteUrlTokens(e.content);
        });

        return {
            getMetadata: function () {
                return {
                    name: 'PyroCMS images',
                    url: 'https://pyrocms.com/'
                };
            }
        };
    });
})();
