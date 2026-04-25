/**
 * pyrofiles TinyMCE 6 plugin
 *
 * Opens admin/wysiwyg/files_wysiwyg as an iframe dialog. The iframe view
 * calls window.parent.tinymce.activeEditor.insertContent(...) via
 * system/cms/modules/wysiwyg/js/wysiwyg.js.
 */
(function () {
    'use strict';

    tinymce.PluginManager.add('pyrofiles', function (editor) {
        editor.ui.registry.addButton('pyrofiles', {
            icon: 'browse',
            tooltip: 'Upload or insert files from library',
            onAction: function () {
                if (typeof window.update_instance === 'function') {
                    window.update_instance();
                }
                editor.windowManager.openUrl({
                    title: 'Files',
                    url: (typeof SITE_URL !== 'undefined' ? SITE_URL : '/') + 'admin/wysiwyg/files_wysiwyg',
                    width: 700,
                    height: 400,
                    buttons: [
                        { type: 'cancel', name: 'cancel', text: 'Close' }
                    ]
                });
            }
        });

        return {
            getMetadata: function () {
                return {
                    name: 'PyroCMS files',
                    url: 'https://pyrocms.com/'
                };
            }
        };
    });
})();
