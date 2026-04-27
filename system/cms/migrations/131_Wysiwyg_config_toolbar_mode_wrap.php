<?php defined('BASEPATH') OR exit('No direct script access allowed');

class Migration_Wysiwyg_config_toolbar_mode_wrap extends CI_Migration
{
    public function up()
    {
        $row = $this->db->where('slug', 'wysiwyg_config')->get('settings')->row();

        if ( ! $row)
        {
            return;
        }

        $value = (string) $row->value;

        // Idempotent: skip if any tinymce.init block already sets toolbar_mode,
        // which also avoids stomping on a hand-customised config.
        if (strpos($value, 'toolbar_mode') !== false)
        {
            return;
        }

        // Insert toolbar_mode: 'wrap' on the line after `promotion: false,` —
        // every shipped block has that line, so this lands once per init().
        $updated = str_replace(
            "promotion: false,\n",
            "promotion: false,\n    toolbar_mode: 'wrap',\n",
            $value
        );

        if ($updated === $value)
        {
            return;
        }

        $this->db->where('slug', 'wysiwyg_config')->update('settings', array(
            'value' => $updated,
        ));
    }

    public function down()
    {
        $row = $this->db->where('slug', 'wysiwyg_config')->get('settings')->row();

        if ( ! $row)
        {
            return;
        }

        $value = str_replace("    toolbar_mode: 'wrap',\n", '', (string) $row->value);

        $this->db->where('slug', 'wysiwyg_config')->update('settings', array(
            'value' => $value,
        ));
    }
}
