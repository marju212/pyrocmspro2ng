<?php defined('BASEPATH') or exit('No direct script access allowed');

class Module_Pyroforms extends Module {

    public $version = '1.6.3';

    public function info()
    {
        return array(
            'name' => array(
                'en' => 'Pyroforms'
            ),
            'description' => array(
                'en' => 'Build forms and collect results'
            ),
            'frontend' => TRUE,
            'backend' => TRUE,
            'menu' => 'content',
            'author' => 'Ryun Shofner',
            'sections' => array(
                'pyroforms' => array(
                    'name' => 'pf_index_title',
                    'uri' => 'admin/pyroforms',
                    'shortcuts' => array(
                        array(
                            'name' => 'pf_new_title',
                            'uri' => 'admin/pyroforms/newform',
                            'class' => 'add'
                        ),
                    ),
                ),
            )
        );
    }

    public function install()
    {
        $this->dbforge->drop_table('pyroforms');
        $this->dbforge->drop_table('pyroforms_fields');
        $this->dbforge->drop_table('pyroforms_entry');
        
        $pf_forms = "
            CREATE TABLE `".$this->db->dbprefix('pyroforms')."` (
              `id` int(7) NOT NULL AUTO_INCREMENT,
              `frmName` varchar(200) DEFAULT NULL,
              `frmSlug` varchar(255) DEFAULT NULL,
              `frmInfo` text,
              `frmResponse` text,
              `frmNotifyTemplate` varchar(25) DEFAULT NULL,
              `frmNotifyTitle` varchar(100) DEFAULT NULL,
              `frmNotifyBody` longtext,
              `send_to` text,
              `reply_to` varchar(50) DEFAULT NULL,
              `layout` text,
              `is_ajax` char(1) DEFAULT '0',
              `file_maxsize` INT(16) DEFAULT NULL,
              `file_types` VARCHAR(200) DEFAULT NULL,
              `track_pages` char(1) DEFAULT '0',
              `track_events` char(1) DEFAULT '0',
              PRIMARY KEY (`id`),
              UNIQUE KEY `frmSlug` (`frmSlug`)
            ) ENGINE=MyISAM DEFAULT CHARSET=latin1;";

        $pf_fields = "
            CREATE TABLE `".$this->db->dbprefix('pyroforms_fields')."` (
              `id` int(11) NOT NULL AUTO_INCREMENT,
              `frm_id` int(11) DEFAULT NULL,
              `fldName` varchar(255) DEFAULT NULL,
              `fldLabel` varchar(255) DEFAULT NULL,
              `fldLabelVisible` char(1) DEFAULT '1',
              `fldInfo` varchar(255) DEFAULT NULL,
              `fldType` varchar(25) DEFAULT NULL,
              `fldDefault` varchar(25) DEFAULT NULL,
              `frmViewFields` text,
              `fldData` text,
              `fldPrep` varchar(255) DEFAULT NULL,
              `fldValidation` varchar(255) DEFAULT NULL,
              `fldAttr` varchar(255) DEFAULT NULL,
              `fldOrder` int(2) DEFAULT NULL,
              PRIMARY KEY (`id`),
              KEY `frm_id` (`frm_id`)
            ) ENGINE=MyISAM DEFAULT CHARSET=latin1;";
            
        $pf_entries = "   
            CREATE TABLE `".$this->db->dbprefix('pyroforms_entry')."` (
              `id` int(11) NOT NULL AUTO_INCREMENT,
              `name` varchar(255) DEFAULT NULL,
              `ip` text,
              `form_id` int(11) DEFAULT NULL,
              `page_id` int(11) DEFAULT NULL,
              `user_id` int(11) DEFAULT NULL,
              `sent_to` varchar(255) DEFAULT NULL,
              `created_on` int(11) NOT NULL,
              `updated_on` int(11) DEFAULT NULL,
              `uagent` varchar(50) DEFAULT NULL,
              `os` varchar(50) DEFAULT NULL,
              `data` longtext,
              PRIMARY KEY (`id`),
              KEY `form_id` (`form_id`),
              KEY `page_id` (`page_id`),
              KEY `user_id` (`user_id`)
            ) ENGINE=MyISAM DEFAULT CHARSET=latin1;";
        
        $this->db->trans_begin();
        
        $this->db->query($pf_forms);
        $this->db->query($pf_fields);
        $this->db->query($pf_entries);
        
        if ($this->db->trans_status() === FALSE)
        {
            $this->db->trans_rollback();
            return FALSE;
        }
        else
        {
            $this->db->trans_commit();
            return TRUE;
        }
    }

    public function uninstall()
    {
        if ($this->dbforge->drop_table('pyroforms') and
            $this->dbforge->drop_table('pyroforms_fields') and
            $this->dbforge->drop_table('pyroforms_entry'))
        {
            return TRUE;
        }
        return FALSE;
    }

    public function upgrade($old_version)
    {
      //add fields for uploads
      if( ! $this->db->field_exists('file_maxsize', 'pyroforms') )
      {
        $fields = array(
          'frmViewFields' => array('type' => 'TEXT'),
          'file_maxsize'  => array('type' => 'INT','constraint' => 16, 'null' => TRUE),
          'file_types'    => array('type' => 'VARCHAR','constraint' => 200, 'null' => TRUE)
        );
        $this->dbforge->add_column('pyroforms', $fields);
        $this->dbforge->drop_column('pyroforms', 'upload_folder');
      }
      
      if( ! $this->db->field_exists('track_pages', 'pyroforms') )
      {
        $fields = array(
          'track_pages'  => array('type' => 'CHAR','constraint' => 1, 'default' => 0),
          'track_events' => array('type' => 'CHAR','constraint' => 1, 'default' => 0)
        );
        $this->dbforge->add_column('pyroforms', $fields);
      }

      if ($this->version > '1.2.2')
      {
        //Modify a few pyroforms_fielda lengths
        $fields = array(
          'fldName'  => array('type' => 'VARCHAR', 'constraint' => 255),
          'fldLabel' => array('type' => 'VARCHAR', 'constraint' => 255),
          'fldInfo'  => array('type' => 'VARCHAR', 'constraint' => 255),
          'fldPrep'  => array('type' => 'VARCHAR', 'constraint' => 255),
        );
        $this->dbforge->modify_column('pyroforms_fields', $fields);

        //Modify a few pyroforms field lengths
        $fields = array(
          'frmName'     => array('type' => 'VARCHAR', 'constraint' => 200),
          'frmSlug'     => array('type' => 'VARCHAR', 'constraint' => 255),
          'frmResponse' => array('type' => 'TEXT'),
        );
        $this->dbforge->modify_column('pyroforms', $fields);
      }
      
      return TRUE;
    }

    public function help()
    {
        return "Coming Soon!!";
    }

}

/* End of file details.php */