<?php

abstract class RCPGA_Groups_DB {

	/**
	 * The name of the table
	 *
	 * @var
	 */
	public $table_name;

	/**
	 * The name of the table
	 *
	 * @var
	 */
	public $db_group;

	/**
	 * The DB version
	 *
	 * @var
	 */
	public $version;

	/**
	 * The primary key of the table
	 *
	 * @var
	 */
	public $primary_key;

	/**
	 * The instance of this class
	 *
	 * @var
	 */
	protected static $_instance;

	/**
	 * Placeholder function for __construct
	 */
	protected function __construct() {
	}

	/**
	 * Return the timestamp for the last changed event
	 *
	 * @return bool|mixed
	 */
	public function get_last_changed() {
		$last_changed = wp_cache_get( 'last_changed', $this->table_name );

		if ( ! $last_changed ) {
			$last_changed = microtime();
			wp_cache_set( 'last_changed', $last_changed, $this->table_name );
		}

		return $last_changed;
	}

	/**
	 * Update the time that this table was changed
	 */
	public function update_last_changed() {
		wp_cache_set( 'last_changed', microtime(), $this->table_name );
	}

	/**
	 * Placeholder function. Should be redefined in child instances.
	 *
	 * @return array
	 */
	public function get_columns() {
		return array();
	}

	/**
	 * Placeholder function. Should be redefined in child instances.
	 *
	 * @return array
	 */
	public function get_column_defaults() {
		return array();
	}

	/**
	 * Get a table row
	 *
	 * @param $row_id
	 *
	 * @return array|bool|mixed|null|object|void
	 */
	public function get( $row_id ) {
		global $wpdb;

		$row = wp_cache_get( $row_id, $this->table_name );

		if ( false === $row ) {
			$row = $wpdb->get_row( "SELECT * FROM $this->table_name WHERE $this->primary_key = $row_id LIMIT 1;" );
			wp_cache_set( $row_id, $this->table_name );
		}

		return apply_filters( "rcpga_db_{$this->db_group}_get", $row, $row_id );
	}

	/**
	 * Get row by column value
	 *
	 * @param $column | the column to search
	 * @param $row_id | the value to search for
	 *
	 * @return array|bool|mixed|null|object|void
	 */
	public function get_by( $column, $row_id ) {
		global $wpdb;

		if ( empty( $row_id ) ) {
			return false;
		}

		$key = sprintf( "get_by:$column:$row_id:%s", $this->get_last_changed() );
		$row = wp_cache_get( $key, $this->table_name );

		if ( false === $row ) {
			$row = $wpdb->get_row( "SELECT * FROM $this->table_name WHERE $column = '$row_id' LIMIT 1;" );
			wp_cache_set( $key, $row, $this->table_name );
		}

		return apply_filters( "rcpga_db_{$this->db_group}_get_by_{$column}", $row, $column, $row_id );
	}

	/**
	 * Get a single value from the table
	 *
	 * @param $column | the value to retrieve
	 * @param $row_id | the row id
	 *
	 * @return bool|mixed|null|string
	 */
	public function get_column( $column, $row_id ) {

		if ( empty( $row_id ) ) {
			return false;
		}

		$row = $this->get( $row_id );
		$var = ( isset( $row->$column ) ) ? $row->$column : false;

		return apply_filters( "rcpga_db_{$this->db_group}_{$column}", $var, $row_id );
	}

	/**
	 * Search for a value in the table
	 *
	 * @param $column       | The value to retrieve
	 * @param $column_where | The column to search
	 * @param $column_value | The value to search for
	 *
	 * @return bool|mixed|null|string
	 */
	public function get_column_by( $column, $column_where, $column_value ) {
		global $wpdb;

		if ( empty( $column ) || empty( $column_where ) || empty( $column_value ) ) {
			return false;
		}

		$key = sprintf( "get_column_by:$column:$column_where:$column_value:%s", $this->get_last_changed() );
		$var = wp_cache_get( $key, $this->table_name );

		if ( false === $var ) {
			$var = $wpdb->get_var( "SELECT $column FROM $this->table_name WHERE $column_where = $column_value LIMIT 1;" );
		}

		return apply_filters( "rcpga_db_{$this->db_group}_get_column_by", $var, $column, $column_where, $column_value );
	}

	/**
	 * Insert data into the table
	 *
	 * @param        $data
	 * @param string $type
	 *
	 * @return int
	 */
	public function insert( $data, $type = '' ) {
		global $wpdb;

		// Set default values
		$data = wp_parse_args( $data, $this->get_column_defaults() );

		// Initialise column format array
		$column_formats = $this->get_columns();

		// Force fields to lower case
		$data = array_change_key_case( $data );

		// White list columns
		$data = array_intersect_key( $data, $column_formats );

		// Reorder $column_formats to match the order of columns given in $data
		$data_keys      = array_keys( $data );
		$column_formats = array_merge( array_flip( $data_keys ), $column_formats );

		$data = apply_filters( "rcpga_db_{$this->db_group}_pre_insert", $data, $type );

		// insert the data
		$wpdb->insert( $this->table_name, $data, $column_formats );
		$insert_id = $wpdb->insert_id;

		// cache buster!
		$this->update_last_changed();

		do_action( "rcpga_db_{$this->db_group}_post_insert", $insert_id, $data, $type );

		return $insert_id;
	}

	/**
	 * Update data in the table
	 *
	 * @param        $row_id
	 * @param array  $data
	 * @param string $where
	 *
	 * @return bool
	 */
	public function update( $row_id, $data = array(), $where = '' ) {
		global $wpdb;

		// Row ID must be positive integer
		$row_id = absint( $row_id );
		if ( empty( $row_id ) ) {
			return false;
		}

		if ( empty( $where ) ) {
			$where = $this->primary_key;
		}

		// Initialise column format array
		$column_formats = $this->get_columns();

		// Force fields to lower case
		$data = array_change_key_case( $data );

		// White list columns
		$data = array_intersect_key( $data, $column_formats );

		// Reorder $column_formats to match the order of columns given in $data
		$data_keys      = array_keys( $data );
		$column_formats = array_merge( array_flip( $data_keys ), $column_formats );

		$data = apply_filters( "rcpga_db_{$this->db_group}_pre_update", $data );

		if ( false === $wpdb->update( $this->table_name, $data, array( $where => $row_id ), $column_formats ) ) {
			return false;
		}

		// cache buster!
		$this->update_last_changed();
		wp_cache_delete( $row_id, $this->table_name );

		do_action( "rcpga_db_{$this->db_group}_post_update", $row_id, $data, $where );

		return true;
	}

	/**
	 * Delete data in the table
	 *
	 * @param int $row_id
	 *
	 * @return bool
	 */
	public function delete( $row_id = 0 ) {

		global $wpdb;

		// Row ID must be positive integer
		$row_id = absint( $row_id );
		if ( empty( $row_id ) ) {
			return false;
		}

		$row_id = apply_filters( $this->table_name . '_pre_delete', $row_id );

		if ( false === $wpdb->query( $wpdb->prepare( "DELETE FROM $this->table_name WHERE $this->primary_key = %d", $row_id ) ) ) {
			return false;
		}

		// cache buster!
		$this->update_last_changed();
		wp_cache_delete( $row_id, $this->table_name );

		do_action( "rcpga_db_{$this->db_group}_post_delete", $row_id );

		return true;
	}

}