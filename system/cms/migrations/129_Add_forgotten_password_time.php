<?php defined('BASEPATH') OR exit('No direct script access allowed');

class Migration_Add_forgotten_password_time extends CI_Migration
{
    public function up()
    {
        $table = $this->db->dbprefix('users');

        if ( ! $this->_column_exists($table, 'forgotten_password_time'))
        {
            $this->db->query('ALTER TABLE ' . $table . ' ADD COLUMN `forgotten_password_time` INT UNSIGNED NULL DEFAULT NULL AFTER `forgotten_password_code`');
        }
    }

    public function down()
    {
        $table = $this->db->dbprefix('users');

        if ($this->_column_exists($table, 'forgotten_password_time'))
        {
            $this->db->query('ALTER TABLE ' . $table . ' DROP COLUMN `forgotten_password_time`');
        }
    }

    private function _column_exists($table, $column)
    {
        $query = $this->db->query("SHOW COLUMNS FROM {$table} LIKE " . $this->db->escape($column));
        return $query->num_rows() > 0;
    }
}
