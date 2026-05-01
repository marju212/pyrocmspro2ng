<?php if (!defined('BASEPATH')) exit('No direct script access allowed');


class Admin extends Admin_Controller
{
    protected $section = 'items';

    public function __construct()
    {
        parent::__construct();


        // We'll set the partials and metadata here since they're used everywhere
        // $this->template->append_js('module::admin.js')
        //    ->append_css('module::admin.css');
    }

    /**
     * List all items
     */
    public function index()
    {

    }

    /**
     * Temporary diagnostic page for the "ads not visible / image empty" bug.
     * Reachable at /admin/ad/diagnose. Remove this method (and revert
     * details.php backend=>FALSE) once the bug is resolved.
     */
    public function diagnose()
    {
        $diag = array();

        $diag['env'] = array(
            'SITE_REF'                  => defined('SITE_REF') ? SITE_REF : '(undefined)',
            'PYRO_ENV'                  => defined('PYRO_ENV') ? PYRO_ENV : '(undefined)',
            'NOW()'                     => date('Y-m-d H:i:s'),
            'php upload_max_filesize'   => ini_get('upload_max_filesize'),
            'php post_max_size'         => ini_get('post_max_size'),
            'php memory_limit'          => ini_get('memory_limit'),
        );

        // All *_ads tables in the schema, regardless of SITE_REF guesses.
        $diag['ads_tables'] = array_map(function ($r) {
            return reset($r);
        }, $this->db->query("SHOW TABLES LIKE '%\\_ads'")->result_array());

        // For each ads table: schema + last 5 rows.
        $diag['per_table'] = array();
        foreach ($diag['ads_tables'] as $tbl) {
            $cols = array();
            foreach ($this->db->query("DESCRIBE `$tbl`")->result_array() as $c) {
                $cols[] = $c['Field'].' '.$c['Type'].($c['Null'] === 'NO' ? ' NOT NULL' : '');
            }

            $hasUpdated = (bool) $this->db
                ->query("SHOW COLUMNS FROM `$tbl` LIKE 'updated'")->num_rows();

            $select = 'id, ad_title, ad_image_1, ad_image_2, ad_image_3, created'
                    . ($hasUpdated ? ', updated' : '');

            $diag['per_table'][$tbl] = array(
                'columns'      => $cols,
                'has_updated'  => $hasUpdated,
                'recent'       => $this->db
                    ->query("SELECT $select FROM `$tbl` ORDER BY id DESC LIMIT 5")
                    ->result_array(),
                'oo_match'     => $this->db
                    ->query("SELECT $select FROM `$tbl` WHERE ad_title = 'öö' ORDER BY id DESC LIMIT 5")
                    ->result_array(),
            );

            // For the most recent row, resolve each image id against the matching
            // *_files table (best guess: same prefix as the ads table).
            $prefix = preg_replace('/_ads$/', '', $tbl);
            $filesTbl = $prefix.'_files';
            // Use INFORMATION_SCHEMA so we don't have to fight LIKE-escape semantics.
            $filesExists = (bool) $this->db
                ->query(
                    "SELECT 1 FROM information_schema.tables "
                    . "WHERE table_schema = DATABASE() AND table_name = ?",
                    array($filesTbl)
                )->num_rows();

            $diag['per_table'][$tbl]['files_table']        = $filesTbl;
            $diag['per_table'][$tbl]['files_table_exists'] = $filesExists;

            if ($filesExists && $diag['per_table'][$tbl]['recent']) {
                $latest = $diag['per_table'][$tbl]['recent'][0];
                $diag['per_table'][$tbl]['latest_image_lookup'] = array();
                foreach (array('ad_image_1','ad_image_2','ad_image_3') as $slot) {
                    $val = $latest[$slot] ?? null;
                    $row = null;
                    $onDisk = null;
                    if ($val && $val !== 'dummy') {
                        $row = $this->db
                            ->query("SELECT id, filename, name, filesize FROM `$filesTbl` WHERE id = ?", array($val))
                            ->row_array();
                        if ($row && !empty($row['filename'])) {
                            $candidate = FCPATH.'uploads/'.$prefix.'/files/'.$row['filename'];
                            $onDisk = file_exists($candidate) ? $candidate : '(MISSING) '.$candidate;
                        }
                    }
                    $diag['per_table'][$tbl]['latest_image_lookup'][$slot] = array(
                        'value'   => $val,
                        'file'    => $row,
                        'on_disk' => $onDisk,
                    );
                }
            }
        }

        // Confirm the events.php hooks actually registered for *this* request.
        $listeners = array();
        $ref = new ReflectionClass('Events');
        if ($ref->hasProperty('_listeners')) {
            $prop = $ref->getProperty('_listeners');
            $prop->setAccessible(true);
            $all = $prop->getValue();
            foreach (array('streams_pre_insert_entry','streams_pre_update_entry') as $evt) {
                $listeners[$evt] = isset($all[$evt]) ? array_keys($all[$evt]) : array();
            }
        }
        $diag['event_listeners'] = $listeners;

        // Whether the ad module is enabled in addons_modules (per-site).
        $diag['ad_module_row'] = $this->db
            ->query("SELECT slug, name, enabled, installed, is_core FROM ".SITE_REF."_modules WHERE slug = 'ad'")
            ->result_array();

        // -----------------------------------------------------------------
        // Upload-stage diagnostics: why does Files::upload() return false
        // for ad_image_1 specifically? (slot 1 stored as '', slots 2/3 as
        // 'dummy' on ad 138 — see field.image.php pre_save.)
        // -----------------------------------------------------------------

        $this->load->config('files/files');
        $filesPath  = config_item('files:path');     // e.g. uploads/bockavel/files/
        $cachePath  = config_item('cache_dir');      // e.g. system/cms/cache/
        $diag['files_paths'] = array(
            'files:path'                            => $filesPath,
            'cache_dir'                             => $cachePath,
            'FCPATH'                                => FCPATH,
            'is_dir(FCPATH.files:path)'             => is_dir(FCPATH.$filesPath),
            'is_writable(FCPATH.files:path)'        => is_writable(FCPATH.$filesPath),
            'is_dir(FCPATH.cache_dir.cloud_cache)'  => is_dir(FCPATH.$cachePath.'cloud_cache/'),
            'is_writable(FCPATH.cache_dir.cloud_cache)' => is_writable(FCPATH.$cachePath.'cloud_cache/'),
        );

        // file_folders: which folder ids exist on this site, where do they live.
        $folderTbl = SITE_REF.'_file_folders';
        $diag['file_folders'] = $this->db
            ->query("SELECT id, parent_id, slug, name, location, remote_container, date_added FROM `$folderTbl` ORDER BY id")
            ->result_array();

        // streams_fields config for ad_image_1/2/3: pull `field_data` (BLOB,
        // serialized) so we can see the configured `folder` id + allowed_types.
        // The streams tables are NOT site-prefixed in standard pyrostreams,
        // but the dbprefix may still apply — try both.
        $fieldsCandidates = array('data_fields', SITE_REF.'_data_fields');
        $diag['stream_fields'] = array();
        foreach ($fieldsCandidates as $ft) {
            $exists = (bool) $this->db
                ->query(
                    "SELECT 1 FROM information_schema.tables "
                    . "WHERE table_schema = DATABASE() AND table_name = ?",
                    array($ft)
                )->num_rows();
            $diag['stream_fields'][$ft] = array('exists' => $exists);
            if ( ! $exists) continue;
            $rows = $this->db
                ->query("SELECT id, field_slug, field_type, field_namespace, field_data FROM `$ft` WHERE field_slug IN ('ad_image_1','ad_image_2','ad_image_3','ad_pdf_file')")
                ->result_array();
            foreach ($rows as &$r) {
                $unser = @unserialize($r['field_data']);
                $r['field_data_unserialized'] = ($unser === false && $r['field_data'] !== 'b:0;') ? '(unserialize failed)' : $unser;
            }
            $diag['stream_fields'][$ft]['rows'] = $rows;
        }

        // Last 10 rows in *_files. If the öö save attempt actually wrote a
        // file row, we'll see one here at 2026-05-01 ~22:35.
        if ( ! empty($diag['per_table'][SITE_REF.'_ads']['files_table_exists'])) {
            $ftbl = SITE_REF.'_files';
            $diag['recent_files'] = $this->db
                ->query("SELECT id, folder_id, name, filename, mimetype, filesize, date_added, user_id FROM `$ftbl` ORDER BY date_added DESC LIMIT 10")
                ->result_array();
        }

        // Walk uploads/<site>/files/ on disk for the 10 newest files (mtime).
        // If no upload row landed in DB but a temp file exists on disk, we'll
        // see it here.
        $uploadDir = FCPATH.$filesPath;
        $diag['uploads_dir_listing'] = array('path' => $uploadDir, 'files' => array());
        if (is_dir($uploadDir)) {
            $entries = array();
            foreach (scandir($uploadDir) ?: array() as $f) {
                if ($f === '.' || $f === '..') continue;
                $full = $uploadDir.$f;
                if (is_file($full)) {
                    $entries[] = array(
                        'name'  => $f,
                        'size'  => filesize($full),
                        'mtime' => date('Y-m-d H:i:s', filemtime($full)),
                    );
                }
            }
            usort($entries, function ($a, $b) { return strcmp($b['mtime'], $a['mtime']); });
            $diag['uploads_dir_listing']['files'] = array_slice($entries, 0, 10);
        }

        // Any flashdata waiting? Files::upload sets a 'notice' on failure,
        // which usually gets displayed in admin but is easy to miss in the
        // public form chain.
        $diag['flashdata'] = array(
            'notice'  => $this->session->flashdata('notice'),
            'success' => $this->session->flashdata('success'),
            'error'   => $this->session->flashdata('error'),
        );

        $this->template
            ->title('Ad diagnostics')
            ->set('diag', $diag)
            ->build('admin/diagnose');
    }

}
