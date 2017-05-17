<?php

class RCPGA_Group_Members extends RCPGA_Groups_DB {

	/**
	 * Only make one instance of the OM_Child_Members
	 *
	 * @return RCPGA_Group_Members
	 */
	public static function get_instance() {
		if ( ! self::$_instance instanceof RCPGA_Group_Members ) {
			self::$_instance = new self();
		}

		return self::$_instance;
	}

	/**
	 * Get things started
	 *
	 * @access  public
	 * @since   1.0
	 */
	protected function __construct() {
		global $wpdb;
		$this->db_group    = 'group_members';
		$this->table_name  = $wpdb->prefix . 'rcp_group_members';
		$this->primary_key = 'user_id';
		$this->version     = '0.1';
	}

	/**
	 * Get table columns and data types
	 *
	 * @access  public
	 * @since   1.0
	 */
	public function get_columns() {
		return array(
			'user_id'    => '%d',
			'group_id'   => '%d',
			'role'       => '%s',
			'date_added' => '%s',
		);
	}

	/**
	 * Get default column values
	 *
	 * @access  public
	 * @since   1.0
	 */
	public function get_column_defaults() {
		return array(
			'user_id' => get_current_user_id()
		);
	}

	/**
	 * Get the members of the group
	 *
	 * @param int $group_id
	 * @access  public
	 * @since   1.0
	 * @return  array
	 */
	public function get_members( $group_id = 0, $args = array() ) {

		global $wpdb;

		$defaults = array(
			'number'  => 20,
			'offset'  => 0,
		);

		$args = wp_parse_args( $args, $defaults );

		if ( empty( $group_id ) ) {
			return array();
		}

		if ( $args['number'] < 1 ) {
			$args['number'] = 999999999999;
		}

		$key     = sprintf( 'get_members:%s:%s:%s', $group_id, md5(serialize( $args )), $this->get_last_changed() );
		$members = wp_cache_get( $key, $this->table_name );


		if ( false === $members ) {
			$members = $wpdb->get_results( $wpdb->prepare( "SELECT * FROM {$this->table_name} WHERE `group_id` = %d LIMIT %d OFFSET %d;", $group_id, $args['number'], $args['offset'] ) );
			wp_cache_set( $key, $members, $this->table_name );
		}

		return apply_filters( 'rcpga_get_members', $members, $group_id );
	}

	/**
	 * Count the number of members in a group
	 *
	 * @param int $group_id
	 *
	 * @access public
	 * @since 1.0.6
	 * @return int
	 */
	public function count( $group_id = 0 ) {

		global $wpdb;

		$key   = sprintf( 'count_members:%s:%s', $group_id, $this->get_last_changed() );
		$count = wp_cache_get( $key, $this->table_name );

		if ( false === $count ) {
			$count = $wpdb->get_var( $wpdb->prepare( "SELECT COUNT({$this->primary_key}) FROM {$this->table_name} WHERE `group_id` = %d", $group_id ) );
			wp_cache_set( $key, $count, $this->table_name );
		}

		return apply_filters( 'rcpga_count_members', $count, $group_id );

	}

	/**
	 * Determine if the user is a member of a group
	 *
	 * @param int $user_id
	 * @access  public
	 * @since   1.0
	 * @return  bool
	 */
	public function is_group_member( $user_id = 0 ) {
		if ( empty( $user_id ) ) {
			$user_id = get_current_user_id();
		}

		return (bool) apply_filters( 'rcpga_is_group_member', 'member' === $this->get_role( $user_id ), $user_id );
	}

	/**
	 * Determine if the user is an admin of a group
	 *
	 * @param int $user_id
	 * @access  public
	 * @since   1.0.4
	 * @return  bool
	 */
	public function is_group_admin( $user_id = 0 ) {
		if ( empty( $user_id ) ) {
			$user_id = get_current_user_id();
		}

		return (bool) apply_filters( 'rcpga_is_group_admin', 'admin' === $this->get_role( $user_id ), $user_id );
	}

	/**
	 * Determine if the user is an owner of a group
	 *
	 * @param int $user_id
	 * @access  public
	 * @since   1.0
	 * @return  bool
	 */
	public function is_group_owner( $user_id = 0 ) {
		if ( empty( $user_id ) ) {
			$user_id = get_current_user_id();
		}

		return apply_filters( 'rcpga_is_group_owner', 'owner' === $this->get_role( $user_id ), $user_id );
	}

	/**
	 * Get the member role
	 *
	 * @param int $user_id
	 * @access  public
	 * @since   1.0
	 * @return  string
	 */
	public function get_role( $user_id = 0 ) {

		if ( empty( $user_id ) ) {
			$user_id = get_current_user_id();
		}

		$role = $this->get_column( 'role', $user_id );
		$role = $role ? $role : false;

		return apply_filters( 'rcpga_get_role', $role, $user_id );
	}

	/**
	 * Get the group name for this member
	 *
	 * @param int $user_id
	 * @access  public
	 * @since   1.0
	 * @return  string
	 */
	public function get_group_name( $user_id = 0 ) {

		if ( empty( $user_id ) ) {
			$user_id = get_current_user_id();
		}

		$group_id = $this->get_column( 'group_id', $user_id );

		if ( empty( $group_id ) ) {
			return false;
		}

		return rcpga_group_accounts()->groups->get_name( $group_id );
	}

	/**
	 * Get the group ID this memer belongs to
	 *
	 * @param int $user_id
	 * @access  public
	 * @since   1.0
	 * @return  int
	 */
	public function get_group_id( $user_id = 0 ) {

		if ( empty( $user_id ) ) {
			$user_id = get_current_user_id();
		}

		return $this->get_column( 'group_id', $user_id );
	}

	/**
	 * Adds a new member to a group
	 *
	 * @param array $args
	 * @access  public
	 * @since   1.0
	 * @return  int|false
	 */
	public function add( $args = array() ) {

		$defaults = array(
			'user_id'    => 0,
			'group_id'   => 0,
			'role'       => 'member',
			'date_added' => current_time( 'mysql' ),
		);

		$args = wp_parse_args( $args, $defaults );

		if ( empty( $args['user_id'] ) || empty( $args['group_id'] ) ) {
			return false;
		}

		$group_member_id = $this->insert( $args, 'member' );

		rcpga_group_accounts()->groups->update_count( $args['group_id'] );

		return $group_member_id;

	}

	/**
	 * Removes a user from any group they belong to
	 *
	 * @param int $user_id
	 * @access  public
	 * @since   1.0
	 * @return  int|false
	 */
	public function remove( $user_id = 0 ) {

		if ( empty( $user_id ) ) {
			return false;
		}

		$group_id = $this->get_group_id( $user_id );

		$this->delete( $user_id );

		do_action( 'rcpga_remove_member', $user_id, $group_id );

		rcpga_group_accounts()->groups->update_count( $group_id );

		return true;

	}

	/**
	 * Removes all members from a specific group. This is for when we delete a group
	 *
	 * @param int $group_id
	 * @access  public
	 * @since   1.0
	 * @return  int|false
	 */
	public function remove_all_from_group( $group_id = 0 ) {

		if ( empty( $group_id ) ) {
			return false;
		}

		$members = $this->get_members( $group_id );

		if ( empty( $members ) ) {
			return false;
		}

		foreach( $members as $member ) {
			$this->remove( $member->user_id );
		}

		return true;

	}

	/**
	 * Create the table
	 *
	 * @access  public
	 * @since   1.0
	 */
	public function create_table() {
		require_once( ABSPATH . 'wp-admin/includes/upgrade.php' );

		$sql = "CREATE TABLE {$this->table_name} (
			`user_id` bigint(20) NOT NULL,
			`group_id` bigint(20) NOT NULL,
			`role` tinytext NOT NULL,
			`date_added` datetime NOT NULL,
			PRIMARY KEY  (user_id)
			) CHARACTER SET utf8 COLLATE utf8_general_ci;";

		dbDelta( $sql );

		update_option( $this->table_name . '_db_version', $this->version );
	}

}
