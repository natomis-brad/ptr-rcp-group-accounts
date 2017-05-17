<?php

class RCPGA_Groups extends RCPGA_Groups_DB {


	/**
	 * Only make one instance of the OM_Child_Members
	 *
	 * @return RCPGA_Groups
	 */
	public static function get_instance() {
		if ( ! self::$_instance instanceof RCPGA_Groups ) {
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
		$this->db_group    = 'groups';
		$this->table_name  = $wpdb->prefix . 'rcp_groups';
		$this->primary_key = 'group_id';
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
			'group_id'      => '%d',
			'owner_id'      => '%d',
			'name'          => '%s',
			'address1'      => '%s',
			'address2'      => '%s',
			'city'          => '%s',
			'state'         => '%s',
			'zip'           => '%s',
			'description'   => '%s',
			'member_count'  => '%d',
			'seats'         => '%d',
			'date_created'  => '%s',
		);
	}

	/**
	 * Determines if the group is active.
	 *
	 * A group is active if the group owner has an active subscription
	 *
	 * @param int $group_id
	 * @access  public
	 * @since   1.0
	 * @return  bool
	 */
	public function is_group_active( $group_id = 0 ) {
		return rcp_is_active( $this->get_owner_id( $group_id ) );
	}

	/**
	 * Get the group name
	 *
	 * @param   int $group_id
	 * @access  public
	 * @since   1.0
	 * @return  string
	 */
	public function get_name( $group_id = 0 ) {
		return $this->get_column( 'name', $group_id );
	}

	/**
	 * Get the group description
	 *
	 * @param   int $group_id
	 * @access  public
	 * @since   1.0
	 * @return  string
	 */
	public function get_description( $group_id = 0 ) {
		return $this->get_column( 'description', $group_id );
	}

	/**
	 * Get the group column valid
	 *
	 * @param   int $group_id
	 * @param   string $colname
	 * @access  public
	 * @since   1.0
	 * @return  string
	 */
	public function get_colval( $group_id = 0, $colname = '' ) {
		return $this->get_column( $colname, $group_id );
	}

	/**
	 * Get the group member_count
	 *
	 * @param   int $group_id
	 * @access  public
	 * @since   1.0
	 * @return  int
	 */
	public function get_member_count( $group_id = 0 ) {
		return absint( $this->get_column( 'member_count', $group_id ) );
	}

	/**
	 * Get the number of seats
	 *
	 * @param   int $group_id
	 * @access  public
	 * @since   1.0
	 * @return  int
	 */
	public function get_seats_count( $group_id = 0 ) {
		return absint( $this->get_column( 'seats', $group_id ) );
	}

	/**
	 * Get the group owner_id
	 *
	 * @param   int $group_id
	 * @access  public
	 * @since   1.0
	 * @return  int
	 */
	public function get_owner_id( $group_id = 0 ) {
		return $this->get_column( 'owner_id', $group_id );
	}


	/**
	 * Get group by owner id
	 *
	 * @param $owner_id
	 *
	 * @return mixed|void
	 */
	public function get_group_by_owner( $owner_id ) {
		return $this->get_by( 'owner_id', absint( $owner_id ) );
	}

	/**
	 * Return group role
	 *
	 * @param $group_id
	 *
	 * @return bool
	 */
	public function get_group_role( $group_id ) {

		if ( ! $owner_id = $this->get_owner_id( $group_id ) ) {
			return false;
		}

		if ( ! $level_id = rcp_get_subscription_id( $owner_id ) ) {
			return false;
		}

		if ( ! $level = rcp_get_subscription_details( $level_id ) ) {
			return false;
		}

		return $level->role;
	}

	/**
	 * Retrieve groups from the database
	 *
	 * @access  public
	 * @since   1.0
	 */
	public function get_groups( $args = array() ) {
		global $wpdb;

		$defaults = array(
			'number'  => 20,
			'offset'  => 0,
			'status'  => '',
			'order'   => 'DESC',
			'orderby' => 'group_id',
			'where'   => '',
		);

		$args = wp_parse_args( $args, $defaults );

		if ( $args['number'] < 1 ) {
			$args['number'] = 999999999999;
		}

		$where  = $args['where'];
		$key    = sprintf( 'get_groups:%s:%s', md5( serialize( $args ) ), $this->get_last_changed() );
		$groups = wp_cache_get( $key, $this->table_name );

		if ( false === $groups ) {
			$groups = $wpdb->get_results( $wpdb->prepare( "SELECT * FROM {$this->table_name} {$where} ORDER BY {$args['orderby']} {$args['order']} LIMIT %d,%d;", absint( $args['offset'] ), absint( $args['number'] ) ) );
			wp_cache_set( $key, $groups, $this->table_name );
		}

		return apply_filters( 'rcpga_get_groups', $groups, $args );

	}

	/**
	 * Retrieve count of groups from database
	 *
	 * @access  public
	 * @since   1.0.1
	 */
	public function get_group_count() {
		global $wpdb;

		$key         = sprintf( 'get_group_count:%s', $this->get_last_changed() );
		$group_count = wp_cache_get( $key, $this->table_name );

		if ( false === $group_count ) {
			$group_count = $wpdb->get_results( "SELECT COUNT(*) AS group_count FROM {$this->table_name};" );
			$group_count = absint( $group_count[0]->group_count );

			wp_cache_set( $key, $group_count, $this->table_name );
		}

		return apply_filters( 'rcpga_get_group_count', $group_count );

	}

	/**
	 * Adds a new group
	 *
	 * @param   array $args
	 * @access  public
	 * @since   1.0
	 * @return  int|false
	 */
	public function add( $args = array() ) {

		$defaults = array(
			'owner_id'      => 0,
			'description'   => '',
			'name'          => '',
			'address1'      => '',
			'address2'      => '',
			'city'          => '',
			'state'         => '',
			'zip'           => '',
			'seats'         => 0,
			'member_count'  => 0,
			'date_created'  => current_time( 'mysql' ),
		);

		$args = wp_parse_args( $args, $defaults );

		if ( empty( $args['owner_id'] ) ) {
			return false;
		}

		return $this->insert( $args, 'group' );

	}

	/**
	 * Update the group's member count
	 *
	 * @param   int $group_id
	 * @access  public
	 * @since   1.0
	 * @return  int New count
	 */
	public function update_count( $group_id = 0 ) {

		if ( empty( $group_id ) ) {
			return;
		}

		$member_count = rcpga_group_accounts()->members->count( $group_id );

		$this->update( $group_id, array( 'member_count' => absint( $member_count ) ) );

		return $member_count;

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
			`group_id` bigint(20) NOT NULL AUTO_INCREMENT,
			`owner_id` bigint(20) NOT NULL,
			`name` mediumtext NOT NULL,
			`description` longtext NOT NULL,
			`seats` bigint(20) NOT NULL,
			`address1` varchar(100) NOT NULL,
			`address2` varchar(100) NOT NULL,
			`city` varchar(50) NOT NULL,
			`state` varchar(10) NOT NULL,
			`zip` varchar(10) NOT NULL,
			`member_count` bigint(20) NOT NULL,
			`date_created` datetime NOT NULL,
			PRIMARY KEY (group_id),
			UNIQUE KEY owner_id (owner_id)
			) CHARACTER SET utf8 COLLATE utf8_general_ci;";

		dbDelta( $sql );

		update_option( $this->table_name . '_db_version', $this->version );
	}


}