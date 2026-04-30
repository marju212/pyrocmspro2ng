<?php defined('BASEPATH') OR exit('No direct script access allowed');
/**
 * The User model.
 *
 * @author PyroCMS Dev Team
 * @package PyroCMS\Core\Modules\Users\Models
 */
class User_m extends MY_Model
{
	public function __construct()
	{
		parent::__construct();

		$this->profile_table = $this->db->dbprefix('profiles');
	}

	// --------------------------------------------------------------------------

	/**
	 * Get a specified (single) user
	 *
	 * @param array $params
	 *
	 * @return object
	 */
	public function get($params)
	{
		if (isset($params['id']))
		{
			$this->db->where('users.id', $params['id']);
		}

		if (isset($params['email']))
		{
			$this->db->where('LOWER('.$this->db->dbprefix('users.email').')', strtolower($params['email']));
		}

		if (isset($params['role']))
		{
			$this->db->where('users.group_id', $params['role']);
		}

		$this->db
			->select($this->profile_table.'.*, users.*')
			->limit(1)
			->join('profiles', 'profiles.user_id = users.id', 'left');

		return $this->db->get('users')->row();
	}

	// --------------------------------------------------------------------------

	/**
	 * Get recent users
	 *
	 * @param     int  $limit defaults to 10
	 *
	 * @return     object
	 */
	public function get_recent($limit = 10)
	{
		$this->db->order_by('users.created_on', 'desc');
		$this->db->limit($limit);
		return $this->get_all();
	}

	// --------------------------------------------------------------------------

	/**
	 * Get all user objects
	 *
	 * @return object
	 */
	public function get_all()
	{
		$this->db
			->select($this->profile_table.'.*, g.description as group_name, users.*')
			->join('groups g', 'g.id = users.group_id')
			->join('profiles', 'profiles.user_id = users.id', 'left');

		return parent::get_all();
	}

	// --------------------------------------------------------------------------

	/**
	 * Create a new user
	 *
	 * @param array $input
	 *
	 * @return int|true
	 */
	public function add($input = array())
	{
		$this->load->helper('date');

		return parent::insert(array(
			'email' => $input->email,
			'password' => $input->password,
			'salt' => $input->salt,
			'role' => empty($input->role) ? 'user' : $input->role,
			'active' => 0,
			'lang' => $this->config->item('default_language'),
			'activation_code' => $input->activation_code,
			'created_on' => now(),
			'last_login' => now(),
			'ip' => $this->input->ip_address()
		));
	}

	// --------------------------------------------------------------------------

	/**
	 * Update the last login time
	 *
	 * @param int $id
	 */
	public function update_last_login($id)
	{
		$this->db->update('users', array('last_login' => now()), array('id' => $id));
	}

	// --------------------------------------------------------------------------

	/**
	 * Activate a newly created user
	 *
	 * @param int $id
	 *
	 * @return bool
	 */
	public function activate($id)
	{
		return parent::update($id, array('active' => 1, 'activation_code' => ''));
	}

	// --------------------------------------------------------------------------

	/**
	 * Count by
	 *
	 * @param array $params
	 * @param bool  $skip_admin Exclude users in the 'admin' group from the count.
	 *
	 * @return int
	 */
	public function count_by($params = array(), $skip_admin = false)
	{
		$this->_apply_filters($params, $skip_admin);

		return $this->db->count_all_results();
	}

	// --------------------------------------------------------------------------

	/**
	 * Get by many
	 *
	 * @param array $params
	 * @param bool  $skip_admin Exclude users in the 'admin' group.
	 * @param int|null $limit Pagination limit.
	 * @param int   $offset Pagination offset.
	 *
	 * @return array
	 */
	public function get_many_by($params = array(), $skip_admin = false, $limit = null, $offset = 0)
	{
		$this->_apply_filters($params, $skip_admin);

		$this->db
			->select($this->profile_table.'.*, g.description as group_name, users.*')
			->order_by('users.active', 'desc')
			->order_by('users.created_on', 'desc');

		if ($limit !== null)
		{
			$this->db->limit($limit, (int) $offset);
		}

		return $this->db->get()->result();
	}

	// --------------------------------------------------------------------------

	/**
	 * Build the shared query state for both count and listing so pagination
	 * totals always match the rendered rows.
	 *
	 * Recognised filter keys (all optional):
	 *   active            int   0=all, 1=active only, 2=inactive only
	 *   group_id          int   exact group match
	 *   name              str   matches profiles.display_name
	 *   email             str   matches users.email
	 *   joined_preset     str   one of: 7days, 30days, this_year, all
	 *   last_visit_preset str   one of: 7days, 30days, this_year, all
	 *
	 * @param array $params
	 * @param bool  $skip_admin Exclude users in the 'admin' group.
	 */
	protected function _apply_filters($params = array(), $skip_admin = false)
	{
		$this->db
			->from($this->_table)
			->join('groups g', 'g.id = users.group_id')
			->join('profiles', 'profiles.user_id = users.id', 'left');

		if ($skip_admin)
		{
			$this->db->where('g.name !=', 'admin');
		}

		if ( ! empty($params['active']))
		{
			$active_val = ((int) $params['active'] === 2) ? 0 : 1;
			$this->db->where('users.active', $active_val);
		}

		if ( ! empty($params['group_id']))
		{
			$this->db->where('users.group_id', (int) $params['group_id']);
		}

		if ( ! empty($params['name']))
		{
			$this->db->like('profiles.display_name', trim($params['name']));
		}

		if ( ! empty($params['email']))
		{
			$this->db->like('users.email', trim($params['email']));
		}

		if ( ! empty($params['joined_preset']))
		{
			$range = $this->_resolve_date_preset($params['joined_preset']);
			if ($range !== null)
			{
				$this->db
					->where('users.created_on >=', $range['start'])
					->where('users.created_on <=', $range['end']);
			}
		}

		if ( ! empty($params['last_visit_preset']))
		{
			$range = $this->_resolve_date_preset($params['last_visit_preset']);
			if ($range !== null)
			{
				$this->db
					->where('users.last_login >=', $range['start'])
					->where('users.last_login <=', $range['end']);
			}
		}
	}

	// --------------------------------------------------------------------------

	/**
	 * Resolve a date-range preset to a unix-timestamp window. Returns null for
	 * the "all" preset (or any unknown value), which means "no date filter".
	 *
	 * @param string $preset
	 *
	 * @return array|null  ['start' => int, 'end' => int] or null
	 */
	protected function _resolve_date_preset($preset)
	{
		$now = time();

		switch ($preset)
		{
			case '7days':
				return array('start' => $now - 7 * 86400, 'end' => $now);
			case '30days':
				return array('start' => $now - 30 * 86400, 'end' => $now);
			case 'this_year':
				return array('start' => mktime(0, 0, 0, 1, 1, (int) date('Y')), 'end' => $now);
			case 'all':
			default:
				return null;
		}
	}

}