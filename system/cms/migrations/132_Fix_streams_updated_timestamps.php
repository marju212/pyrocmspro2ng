<?php defined('BASEPATH') OR exit('No direct script access allowed');

/**
 * MySQL 8 strict-mode tightened defaults for TIMESTAMP/DATETIME columns
 * that were previously tolerated under 5.7's looser sql_mode. Streams
 * tables in particular rely on `updated` being set automatically — front-
 * end plugins filter on it (e.g. ad listings WHERE updated BETWEEN ... )
 * so a NULL or zero-date row just disappears from the public site even
 * though it shows up in admin streams.
 *
 * This migration:
 *   1. Walks every table in the active database that has both `id` and
 *      `updated` columns (covers every streams entries table across all
 *      site prefixes plus a few PyroCMS core tables).
 *   2. Backfills NULL / '0000-00-00 00:00:00' updated values from the
 *      row's created column when it has one, or NOW() otherwise.
 *   3. Alters `updated` to TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP
 *      ON UPDATE CURRENT_TIMESTAMP so future inserts/updates always land
 *      a sensible value regardless of sql_mode.
 *
 * Idempotent: re-running it is a no-op once columns already have the
 * right definition and there are no NULL/zero rows.
 */
class Migration_Fix_streams_updated_timestamps extends CI_Migration
{
    public function up()
    {
        $db_name = $this->db->database;

        // Tables with both id + updated columns.
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

            // Does the table also have a `created` column we can use as a
            // backfill source? Most streams tables do.
            $has_created = (bool) $this->db->query("
                SELECT 1
                FROM information_schema.COLUMNS
                WHERE TABLE_SCHEMA = ?
                  AND TABLE_NAME   = ?
                  AND COLUMN_NAME  = 'created'
                LIMIT 1
            ", array($db_name, $table))->row();

            // 1. Backfill bad updated values BEFORE altering the column,
            // because the ALTER will fail under strict mode if a NOT NULL
            // column has any NULL row.
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

            // 2. Force the column definition so future writes land cleanly.
            // We don't try to detect "already correct" — MODIFY COLUMN is a
            // no-op when the spec matches.
            $this->db->query("
                ALTER TABLE `{$table}`
                MODIFY `updated` TIMESTAMP NOT NULL
                    DEFAULT CURRENT_TIMESTAMP
                    ON UPDATE CURRENT_TIMESTAMP
            ");
        }
    }

    public function down()
    {
        // Schema-loosening rollback intentionally omitted. Restoring the
        // pre-migration column definition risks reintroducing NULL/zero
        // rows on future inserts under MySQL 8 strict mode, which is
        // exactly the bug we just fixed. If you need to roll back, do it
        // manually per-table with the previous definition you actually
        // had.
    }
}
