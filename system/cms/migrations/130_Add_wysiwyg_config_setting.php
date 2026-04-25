<?php defined('BASEPATH') OR exit('No direct script access allowed');

class Migration_Add_wysiwyg_config_setting extends CI_Migration
{
    public function up()
    {
        $existing = $this->db->where('slug', 'wysiwyg_config')->count_all_results('settings');

        if ($existing > 0)
        {
            return;
        }

        $this->db->insert('settings', array(
            'slug'        => 'wysiwyg_config',
            'title'       => 'WYSIWYG Editor Config',
            'description' => 'Initialisation script for the rich-text editor (TinyMCE 6). Each <code>tinymce.init({...})</code> call binds a profile to a textarea selector. The <code>pyroEditorPlugins</code> object is supplied by the wysiwyg fragment view and contains URLs for the pyroimages / pyrofiles plugins.',
            'type'        => 'textarea',
            'default'     => '',
            'value'       => $this->_default_config(),
            'options'     => '',
            'is_required' => 1,
            'is_gui'      => 1,
            'module'      => 'wysiwyg',
            'order'       => 992,
        ));
    }

    public function down()
    {
        $this->db->where('slug', 'wysiwyg_config')->delete('settings');
    }

    private function _default_config()
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
