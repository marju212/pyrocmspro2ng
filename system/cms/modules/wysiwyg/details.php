<?php defined('BASEPATH') or exit('No direct script access allowed');

/**
 * Widgets Module
 *
 * @author PyroCMS Dev Team
 * @package PyroCMS\Core\Modules\Widgets
 */
class Module_WYSIWYG extends Module
{

        public $version = '2.0.0';

        public function info()
        {
                return array(
                    'name' => array(
                        'en' => 'WYSIWYG',
						'br' => 'WYSIWYG',
                        'fa' => 'WYSIWYG',
                        'fr' => 'WYSIWYG',
                        'pt' => 'WYSIWYG',
                        'se' => 'HTML-redigerare',
                        'tw' => 'WYSIWYG',
                        'cn' => 'WYSIWYG',
                        'ar' => 'المحرر الرسومي',
                        'it' => 'WYSIWYG',
                    ),
                    'description' => array(
                        'en' => 'Provides the WYSIWYG editor for PyroCMS powered by TinyMCE 6.',
						'br' => 'Provém o editor WYSIWYG para o PyroCMS fornecido pelo TinyMCE 6.',
                        'fa' => 'ویرایشگر WYSIWYG که توسط TinyMCE 6 ارائه شده است. ',
                        'fr' => 'Fournit un éditeur WYSIWYG pour PyroCMS propulsé par TinyMCE 6',
                        'pt' => 'Fornece o editor WYSIWYG para o PyroCMS, powered by TinyMCE 6.',
                        'el' => 'Παρέχει τον επεξεργαστή WYSIWYG για το PyroCMS, χρησιμοποιεί το TinyMCE 6.',
                        'se' => 'Redigeringsmodul för HTML, TinyMCE 6.',
                        'tw' => '提供 PyroCMS 所見即所得（WYSIWYG）編輯器，由 TinyMCE 6 技術提供。',
                        'cn' => '提供 PyroCMS 所见即所得（WYSIWYG）编辑器，由 TinyMCE 6 技术提供。',
                        'ar' => 'توفر المُحرّر الرسومي لـPyroCMS من خلال TinyMCE 6.',
                        'it' => 'Fornisce l\'editor WYSIWYG per PyroCMS creato con TinyMCE 6',
                    ),
                    'frontend' => false,
                    'backend' => false,
                );
        }

        public function install()
        {
                $existing = $this->db->where('slug', 'wysiwyg_config')->count_all_results('settings');

                if ($existing == 0)
                {
                        $this->db->insert('settings', array(
                            'slug'        => 'wysiwyg_config',
                            'title'       => 'WYSIWYG Editor Config',
                            'description' => 'Initialisation script for the rich-text editor (TinyMCE 6). Each <code>tinymce.init({...})</code> call binds a profile to a textarea selector. The <code>pyroEditorPlugins</code> object is supplied by the wysiwyg fragment view and contains URLs for the pyroimages / pyrofiles plugins.',
                            'type'        => 'textarea',
                            'default'     => '',
                            'value'       => $this->_default_wysiwyg_config(),
                            'options'     => '',
                            'is_required' => 1,
                            'is_gui'      => 1,
                            'module'      => 'wysiwyg',
                            'order'       => 992,
                        ));
                }

                return true;
        }

        public function uninstall()
        {
                // This is a core module, lets keep it around.
                return false;
        }

        public function upgrade($old_version)
        {
                return true;
        }

        private function _default_wysiwyg_config()
        {
                return <<<'JS'
{{# Blog 'intro' field gets a simple toolbar plus the image inserter. #}}
tinymce.init({
    selector: 'textarea#intro.wysiwyg-simple',
    menubar: false,
    branding: false,
    promotion: false,
    plugins: 'lists link autolink',
    external_plugins: pyroEditorPlugins,
    toolbar: 'pyroimages | bold italic | numlist bullist | link unlink',
    height: 200,
    width: '99%'
});

{{# Generic simple editor (everything with .wysiwyg-simple except #intro). #}}
tinymce.init({
    selector: 'textarea.wysiwyg-simple:not(#intro)',
    menubar: false,
    branding: false,
    promotion: false,
    plugins: 'lists link autolink',
    toolbar: 'bold italic | numlist bullist | link unlink',
    height: 200,
    width: '99%'
});

{{# Advanced editor — full toolbar with fullscreen, table, source view, file picker. #}}
tinymce.init({
    selector: 'textarea.wysiwyg-advanced',
    menubar: false,
    branding: false,
    promotion: false,
    plugins: 'fullscreen lists link autolink table searchreplace code visualblocks visualchars charmap pagebreak directionality',
    external_plugins: pyroEditorPlugins,
    toolbar: 'fullscreen | pyroimages pyrofiles | undo redo | searchreplace | link unlink | bold italic strikethrough | alignleft aligncenter alignright alignjustify | ltr rtl | styles fontsize subscript superscript | numlist bullist outdent indent blockquote | table pagebreak charmap | visualblocks visualchars removeformat code',
    height: 500,
    width: '99%'
});
JS;
        }

}
