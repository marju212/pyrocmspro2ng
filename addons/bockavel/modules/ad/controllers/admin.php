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
            $filesExists = (bool) $this->db
                ->query("SHOW TABLES LIKE '".$this->db->escape_like_str($filesTbl)."'")->num_rows();

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

        $this->template
            ->title('Ad diagnostics')
            ->set('diag', $diag)
            ->build('admin/diagnose');
    }

}
