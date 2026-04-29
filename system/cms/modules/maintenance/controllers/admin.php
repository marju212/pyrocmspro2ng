<?php defined('BASEPATH') OR exit('No direct script access allowed');
/**
 * The Maintenance Module - currently only remove/empty cache folder(s)
 *
 * @author		Donald Myers
 * @author		PyroCMS Dev Team
 * @package	 PyroCMS\Core\Modules\Maintainance\Controllers
 */

class Admin extends Admin_Controller
{

	private $cache_path;

	public function __construct()
	{
		parent::__construct();

		$this->cache_path = FCPATH.APPPATH.'cache/'.SITE_REF.'/';

		$this->config->load('maintenance');
		$this->lang->load('maintenance');
	}


	/**
	 * List all folders
	 */
	public function index()
	{
		// Discover all the directories in the cache path.
		$cache_folders = glob($this->cache_path.'*', GLOB_ONLYDIR);

		// Get protected cache folders from module config file
		$protected = $this->config->item('maintenance.cache_protected_folders');
		$cannot_remove = $this->config->item('maintenance.cannot_remove_folders');

		$folders = array();

		foreach ($cache_folders as $key => $folder)
		{
			$basename = basename($folder);
			// If the folder is not protected
			if( ! in_array($basename, $protected))
			{
				// Store it in the array of the folders we will be doing something with.
				// Just use the filename on the front end to not expose complete paths
				$folders[] = array(
					'name' => $basename,
					'count' => count(glob($folder.'/*')),
					'cannot_remove' => in_array($basename, $cannot_remove)
				);
			}
		}

		$table_list = config_item('maintenance.export_tables');

		asort($table_list);

		$tables = array();
		foreach ($table_list as $table)
		{
			$tables[] = array(
				'name' => $table,
				'count' => $this->db->count_all($table),
			);
		}

		$this->template
			->title($this->module_details['name'])
			->set('tables', $tables)
			->set('folders', $folders)
			->build('admin/items');
	}


	/**
	 * Read-only diagnostics page: show the current migration version,
	 * list every migration file shipped in the codebase, and mark which
	 * have been applied. Useful for confirming that a freshly-deployed
	 * image actually executed the migrations it ships.
	 *
	 * Live URL: /admin/maintenance/migrations
	 */
	public function migrations()
	{
		// Current applied version. The CI migration library tracks this
		// in a table called `migrations` with a single `version` column.
		$current = 0;
		if ($this->db->table_exists('migrations'))
		{
			$row = $this->db->get('migrations')->row();
			if ($row && isset($row->version))
			{
				$current = (int) $row->version;
			}
		}

		// Configured target version. CI runs up() until it reaches this.
		$target = (int) $this->config->item('migration_version');

		// Walk the migrations directory, parse `NNN_Name.php` filenames,
		// and bucket each by applied / pending.
		$path = APPPATH.'migrations/';
		$files = glob($path.'*.php') ?: array();
		sort($files);

		$migrations = array();
		foreach ($files as $file)
		{
			$base = basename($file, '.php');
			if ( ! preg_match('/^(\d+)_(.+)$/', $base, $m))
			{
				continue;
			}

			$version = (int) $m[1];
			$migrations[] = array(
				'version' => $version,
				'name'    => str_replace('_', ' ', $m[2]),
				'file'    => $base.'.php',
				'applied' => $version <= $current,
				'pending' => $version > $current && $version <= $target,
			);
		}

		$this->template
			->title($this->module_details['name'].' :: Migrations')
			->set('current', $current)
			->set('target',  $target)
			->set('migrations', $migrations)
			->build('admin/migrations');
	}


	public function cleanup($name = '', $andfolder = 0)
	{
		if ( ! empty($name))
		{

			$andfolder = ($andfolder) ? true : false;

			$apath = $this->_refind_apath($name);

			if ( ! empty($apath))
			{
				$item_count = count(glob($apath.'/*'));
				// just empty or empty and remove?
				$which = ($andfolder) ? 'remove' : 'empty';

				if ($this->delete_files($apath, $andfolder))
				{
					$this->session->set_flashdata('success', sprintf(lang('maintenance:'.$which.'_msg'), $item_count, $name));
				}
				else
				{
					$this->session->set_flashdata('error', sprintf(lang('maintenance:'.$which.'_msg_err'), $name));
				}
			}
		}

		redirect('admin/maintenance/');
	}

	/**
	 * Delete files from a path
	 *
	 * @param string $apath The path to delete files from.
	 * @param bool $andfolder Whether to delete the folder itself or not.
	 *
	 * @return bool
	 */
	private function delete_files($apath, $andfolder = false)
	{
		$this->load->helper('file');

		if ( ! delete_files($apath, true))
		{
			return false;
		}

		if ($andfolder)
		{
			if ( ! rmdir($apath))
			{
				return false;
			}
		}

		return true;
	}

	/**
	 * Export a table into a specified data format.
	 *
	 * @param string $table The name of the table to export.
	 * @param string $type The type in which to export the data.
	 */
	public function export($table = '', $type = 'xml')
	{
		$this->load->model('maintenance_m');
		$this->load->helper('download');
		$this->load->library('format');

		$table_list = config_item('maintenance.export_tables');

		if (in_array($table, $table_list))
		{
			$this->maintenance_m->export($table, $type, $table_list);
		}
		else
		{
			redirect('admin/maintenance');
		}
	}

	/**
	 * Rediscover a path.
	 *
	 * @param string $name The name of the path.
	 * @return string The folder name.
	 */
	private function _refind_apath($name)
	{
		$folders = glob($this->cache_path.'*', GLOB_ONLYDIR);

		foreach ($folders as $folder)
		{
			if (basename($folder) === $name)
			{
				return $folder;
			}
		}
	}

}