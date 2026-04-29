<?php defined('BASEPATH') OR exit('No direct script access allowed');

/**
 * Re-run of migration 132. The original errored partway through on
 * deployments where MySQL 8 strict mode rejected the
 * `WHERE updated = '0000-00-00 00:00:00'` comparison with error 1292,
 * which left some streams tables un-tightened (and silently logged
 * users out + hid frontend entries because the affected `updated`
 * columns kept landing as NULL/zero on subsequent writes).
 *
 * Migration 132 is now patched to disable strict mode for the duration
 * of the up(), but PyroCMS won't re-execute a migration once it's
 * marked applied. So this migration repeats the same cleanup against
 * any tables 132 missed. Idempotent — re-running on a healthy DB is a
 * cheap no-op.
 */
class Migration_Re_fix_streams_updated_timestamps extends CI_Migration
{
    public function up()
    {
        $prev_mode = (string) ($this->db->query('SELECT @@SESSION.sql_mode AS m')->row()->m ?? '');
        $this->db->query("SET SESSION sql_mode = ''");

        try
        {
            $db_name = $this->db->database;

            $rows = $this->db->query("
                SELECT t.TABLE_NAME
                FROM information_schema.TABLES t
                JOIN information_schema.COLUMNS c_id
                  ON c_id.TABLE_SCHEMA = t.TABLE_SCHEMA
                 AND c_id.TABLE_NAME   = t.TABLE_NAME
                 AND c_id.COLUMN_NAME  = 'id'
                JOIN information_schema.COLUMNS c_up
                  ON c_up.TABLE_SCHEMA = t.TABLE_SCHEMA
                 AND c_up.TABLE_NAME   = t.TABLE_NAME
                 AND c_up.COLUMN_NAME  = 'updated'
                WHERE t.TABLE_SCHEMA = ?
                  AND t.TABLE_TYPE = 'BASE TABLE'
            ", array($db_name))->result();

            foreach ($rows as $row)
            {
                $table = $row->TABLE_NAME;

                $has_created = (bool) $this->db->query("
                    SELECT 1
                    FROM information_schema.COLUMNS
                    WHERE TABLE_SCHEMA = ?
                      AND TABLE_NAME   = ?
                      AND COLUMN_NAME  = 'created'
                    LIMIT 1
                ", array($db_name, $table))->row();

                if ($has_created)
                {
                    $this->db->query("
                        UPDATE `{$table}`
                        SET updated = COALESCE(NULLIF(created, '0000-00-00 00:00:00'), NOW())
                        WHERE updated IS NULL OR updated = '0000-00-00 00:00:00'
                    ");
                }
                else
                {
                    $this->db->query("
                        UPDATE `{$table}`
                        SET updated = NOW()
                        WHERE updated IS NULL OR updated = '0000-00-00 00:00:00'
                    ");
                }

                $this->db->query("
                    ALTER TABLE `{$table}`
                    MODIFY `updated` TIMESTAMP NOT NULL
                        DEFAULT CURRENT_TIMESTAMP
                        ON UPDATE CURRENT_TIMESTAMP
                ");
            }
        }
        finally
        {
            $this->db->query('SET SESSION sql_mode = '.$this->db->escape($prev_mode));
        }
    }

    public function down()
    {
        // Same reasoning as 132 — no rollback. Loosening the schema
        // would just reintroduce the bug.
    }
}
