<?php

class RCPGA_Group_Capabilities {

	/**
	 * @var
	 */
	protected static $_instance;

	/**
	 * Only make one instance of the OM_Child_Members
	 *
	 * @return RCPGA_Group_Capabilities
	 */
	public static function get_instance() {
		if ( ! self::$_instance instanceof RCPGA_Group_Capabilities ) {
			self::$_instance = new self();
		}

		return self::$_instance;
	}

	protected function __construct() {

		// none of these filters should run in the admin
		if ( is_admin() && ! ( defined( 'DOING_AJAX' ) && DOING_AJAX ) ) {
			return;
		}

		add_filter( 'rcp_is_active',                  array( $this, 'group_is_active'       ), 10, 2 );
		add_filter( 'rcp_member_get_status',          array( $this, 'group_get_status'      ), 10, 2 );
		add_filter( 'rcp_member_get_subscription_id', array( $this, 'group_subscription_id' ), 10, 2 );
		add_filter( 'rcp_member_get_expiration_date', array( $this, 'group_expiration_date' ), 10, 2 );
	}

	/**
	 * Supported roles
	 *
	 * @return array
	 */
	public function get_roles() {
		return array( 'owner', 'admin', 'member', 'invited' );
	}

	/**
	 * Supported tasks
	 *
	 * @return array
	 */
	public function get_tasks() {
		return array( 'manage_billing', 'manage_members', 'view_group' );
	}

	/**
	 * Determine which task can be performed by this role
	 *
	 * @param string $role
	 *
	 * @return array
	 */
	public function get_tasks_of_role( $role = '' ) {

		$tasks = array();

		if ( empty( $role ) ) {
			return $tasks;
		}

		switch ( $role ) {

			case 'owner' :

				$tasks[] = 'manage_billing';
				$tasks[] = 'manage_members';
				$tasks[] = 'view_group';

				break;

			case 'admin' :

				$tasks[] = 'manage_members';
				$tasks[] = 'view_group';

				break;

			case 'member' :

				$tasks[] = 'view_group';

		}

		return apply_filters( 'rcpga_get_tasks_of_role', $tasks, $role );

	}

	/**
	 * Determine if this user can perform this action
	 *
	 * @param string $task
	 * @param int    $user_id
	 * @param int    $group_id
	 *
	 * @return bool
	 */
	public function can( $task = '', $user_id = 0, $group_id = 0 ) {

		if ( empty( $task ) ) {
			return false;
		}

		if ( empty( $user_id ) ) {
			$user_id = get_current_user_id();
		}

		if ( user_can( $user_id, 'manage_options' ) ) {
			return true;
		}

		if ( empty( $group_id ) ) {
			$group_id = rcpga_group_accounts()->members->get_group_id( $user_id );
		}

		// Get the member's role in the group
		$role = rcpga_group_accounts()->members->get_role( $user_id );

		// Get the tasks their role has
		$tasks = $this->get_tasks_of_role( $role );

		// Check that this user is in the specified group
		if ( ! empty( $group_id ) ) {
			$in_group = (int) rcpga_group_accounts()->members->get_group_id( $user_id ) === (int) $group_id;
		} else {
			$in_group = true;
		}

		return in_array( $task, $tasks ) && $in_group;

	}

	/**
	 * Filter rcp_is_active to allow for active groups
	 *
	 * @param $ret
	 * @param $user_id
	 *
	 * @return mixed|void
	 */
	public function group_is_active( $ret, $user_id ) {

		if ( ! $group_id = rcpga_group_accounts()->members->get_group_id( $user_id ) ) {
			return $ret;
		}

		// if this is the owner account, bale or we'll get an endless loop
		if ( rcpga_group_accounts()->members->is_group_owner( $user_id ) ) {
			return $ret;
		}

		// ignore invited members
		if ( 'invited' == rcpga_group_accounts()->members->get_role( $user_id ) ) {
			return $ret;
		}

		if ( ! $ret ) {
			$ret = rcpga_group_accounts()->groups->is_group_active( $group_id );
		}

		return apply_filters( 'rcpga_group_is_active', $ret, $group_id, $user_id );

	}

	/**
	 * Get group subscription id
	 *
	 * @param $subscription_id
	 * @param $user_id
	 *
	 * @return mixed|void
	 */
	public function group_subscription_id( $subscription_id, $user_id ) {

		if ( ! $group_id = rcpga_group_accounts()->members->get_group_id( $user_id ) ) {
			return $subscription_id;
		}

		// if this is the owner account, bale or we'll get an endless loop
		if ( rcpga_group_accounts()->members->is_group_owner( $user_id ) ) {
			return $subscription_id;
		}

		// ignore invited members
		if ( 'invited' == rcpga_group_accounts()->members->get_role( $user_id ) ) {
			return $subscription_id;
		}

		// get the parent subscription
		$subscription_id = rcp_get_subscription_id( rcpga_group_accounts()->groups->get_owner_id( $group_id ) );

		return apply_filters( 'rcpga_group_subscription_id', $subscription_id, $group_id, $user_id );

	}

	/**
	 * Get the group expiration date
	 *
	 * @param $exp_date
	 * @param $user_id
	 *
	 * @return mixed|void
	 */
	public function group_expiration_date( $exp_date, $user_id ) {

		if ( ! $group_id = rcpga_group_accounts()->members->get_group_id( $user_id ) ) {
			return $exp_date;
		}

		// if this is the owner account, bale or we'll get an endless loop
		if ( rcpga_group_accounts()->members->is_group_owner( $user_id ) ) {
			return $exp_date;
		}

		// ignore invited members
		if ( 'invited' == rcpga_group_accounts()->members->get_role( $user_id ) ) {
			return $exp_date;
		}

		$exp_date = rcp_get_expiration_date( rcpga_group_accounts()->groups->get_owner_id( $group_id ) );

		return apply_filters( 'rcpga_group_expiration_date', $exp_date, $group_id, $user_id );

	}

	/**
	 * Get the group status
	 *
	 * @param $status
	 * @param $user_id
	 *
	 * @return mixed|void
	 */
	public function group_get_status( $status, $user_id ) {

		if ( ! $group_id = rcpga_group_accounts()->members->get_group_id( $user_id ) ) {
			return $status;
		}

		// if this is the owner account, bale or we'll get an endless loop
		if ( rcpga_group_accounts()->members->is_group_owner( $user_id ) ) {
			return $status;
		}

		// ignore invited members
		if ( 'invited' == rcpga_group_accounts()->members->get_role( $user_id ) ) {
			return $status;
		}

		// get the group status
		$status = rcp_get_status( rcpga_group_accounts()->groups->get_owner_id( $group_id ) );

		return apply_filters( 'rcpga_group_status', $status, $group_id, $user_id );
	}


}