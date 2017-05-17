<?php

// Exit if accessed directly
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

class RCPGA_Groups_Actions {

	public function __construct() {

		add_action( 'init',                          array( $this, 'action_router'             ) );
		add_action( 'template_redirect',             array( $this, 'accept_invite'             ) );
		add_action( 'deleted_user',                  array( $this, 'delete_from_group'         ) );
		add_action( 'rcp_form_errors',               array( $this, 'can_user_downgrade'        ) );
		add_action( 'rcp_form_errors',               array( $this, 'require_group_name'        ) );
		add_action( 'rcp_form_processing',           array( $this, 'update_seat_count'         ), 10, 3 );
		add_action( 'rcp_set_status',                array( $this, 'remove_active_member'      ), 10, 3 );
		add_action( 'rcp_form_processing',           array( $this, 'create_pending_group'      ), 10, 3 );
		add_action( 'rcp_set_status',                array( $this, 'register_member_group'     ), 10, 4 );
		add_filter( 'rcp_can_upgrade_subscription',  array( $this, 'can_upgrade_subscription'  ), 10, 2 );
		add_filter( 'rcp_stripe_charge_create_args', array( $this, 'stripe_charge_create_args' ), 10, 2 );

		// create owner member when group is created
		add_action( 'rcpga_db_groups_post_insert', array( $this, 'create_owner_member' ), 10, 2 );
		add_action( 'rcpga_db_groups_post_delete', array( $this, 'remove_members'      ), 10, 2 );

		// Group Member actions
		add_filter( 'rcpga_db_group_members_pre_delete', array( $this, 'group_member_cleanup' ) );

		add_action( 'wp_enqueue_scripts',             array( $this, 'scripts' ) );
		add_action( 'rcp_after_register_form_fields', array( $this, 'registration_fields' ), 100 );

	}

	/**
	 * Handle group actions
	 */
	public function action_router() {

		if ( empty( $_REQUEST['rcpga-action'] ) ) {
			return;
		}

		$action  = $_REQUEST['rcpga-action'];
		$message = '';
		$view = isset( $_REQUEST['rcpga-view'] ) ? sanitize_text_field( $_REQUEST['rcpga-view'] ) : false;

		switch ( $action ) {

			case 'add-group' :
				$message = $this->add_group();
				break;

			case 'edit-group' :
				$message = $this->edit_group();
				break;

			case 'delete-group' :
				$message = $this->delete_group();
				break;

			case 'add-member' :
				$message = $this->add_member_to_group();
				break;

			case 'import-members' :
				$message = $this->import_members_to_group();
				break;

			case 'remove-member' :
				$message = $this->remove_member_from_group();
				break;

			case 'make-admin' :
			case 'make-member' :
				$message = $this->make_member_admin();
				break;

			case 'resend-invite' :
				$message = $this->resend_member_invite();
				break;
		}

		wp_redirect( add_query_arg( array(
			'rcpga-action'  => false,
			'rcpga-message' => $message,
			'rcpga-view'    => $view
		), $_SERVER['HTTP_REFERER'] ) );
		exit;

	}

	/**
	 * Handle the Group add form, front end and back end
	 *
	 * @return string - result of processing
	 */
	protected function add_group() {

		// make sure the user can add groups
		if ( ! rcpga_group_accounts()->capabilities->can( 'add_group' ) ) {
			return 'no-permission';
		}

		if ( empty( $_REQUEST['rcpga-group-name'] ) ) {
			return 'empty-group-name';
		}

		// only allow the group owner to be specified in the admin
		if ( is_admin() && current_user_can( 'manage_options' ) ) {

			if ( empty( $_REQUEST['rcpga-user-email'] ) ) {
				return 'empty-email';
			}

			if ( ! $user = get_user_by( 'email', $_REQUEST['rcpga-user-email'] ) ) {
				return 'no-user';
			}

		} else {
			$user = wp_get_current_user();
		}

		// make sure the specified user does not already belong to a group
		if ( rcpga_group_accounts()->members->get_group_id( $user->ID ) ) {
			return 'has-group';
		}

		$args = array(
			'owner_id'    => $user->ID,
			'name'        => wp_unslash( sanitize_text_field( $_REQUEST['rcpga-group-name'] ) ),
			'address'     => wp_unslash( sanitize_text_field( $_REQUEST['rcpga-group-address1'] ) ),
			'address2'    => wp_unslash( sanitize_text_field( $_REQUEST['rcpga-group-address2'] ) ),
			'city'        => wp_unslash( sanitize_text_field( $_REQUEST['rcpga-group-city'] ) ),
			'state'       => wp_unslash( sanitize_text_field( $_REQUEST['rcpga-group-state'] ) ),
			'zip'         => wp_unslash( sanitize_text_field( $_REQUEST['rcpga-group-zip'] ) ),
			'description' => ! empty( $_REQUEST['rcpga-group-description'] ) ? wp_unslash( wp_filter_post_kses( $_REQUEST['rcpga-group-description'] ) ) : '',
			'seats'       => ! empty( $_REQUEST['rcpga-group-seats'] ) ? absint( $_REQUEST['rcpga-group-seats'] ) : 0,
		);

		rcpga_group_accounts()->groups->add( $args );

		return 'group-added';

	}

	/**
	 * Handle group edit forms
	 *
	 * @return string - result
	 */
	protected function edit_group() {

		// make sure that group is specified and exists
		if ( empty( $_REQUEST['rcpga-group'] ) || ! $group = rcpga_group_accounts()->groups->get( absint( $_REQUEST['rcpga-group'] ) ) ) {
			return 'no-group';
		}

		// permissions check
		if ( ! rcpga_group_accounts()->capabilities->can( 'manage_members', get_current_user_id(), $_REQUEST['rcpga-group'] ) ) {
			return 'no-permission';
		}

		// make sure group name is specified
		if ( empty( $_REQUEST['rcpga-group-name'] ) ) {
			return 'empty-group-name';
		}

		$args = array(
			'name'         => wp_unslash( sanitize_text_field( $_REQUEST['rcpga-group-name'] ) ),
			'address1'         => wp_unslash( sanitize_text_field( $_REQUEST['rcpga-group-address1'] ) ),
			'address2'         => wp_unslash( sanitize_text_field( $_REQUEST['rcpga-group-address2'] ) ),
			'city'         => wp_unslash( sanitize_text_field( $_REQUEST['rcpga-group-city'] ) ),
			'state'         => wp_unslash( sanitize_text_field( $_REQUEST['rcpga-group-state'] ) ),
			'zip'         => wp_unslash( sanitize_text_field( $_REQUEST['rcpga-group-zip'] ) ),
			'description'  => ! empty( $_REQUEST['rcpga-group-description'] ) ? wp_unslash( wp_filter_post_kses( $_REQUEST['rcpga-group-description'] ) ) : $group->description,
			'seats'        => ! empty( $_REQUEST['rcpga-group-seats'] ) ? absint( $_REQUEST['rcpga-group-seats'] ) : $group->seats,
		);

		// update the group
		rcpga_group_accounts()->groups->update( $group->group_id, $args );

		return 'group-updated';

	}


	/**
	 * Handle group delete action
	 *
	 * @return string
	 */
	protected function delete_group() {

		if ( empty( $_REQUEST['rcpga-group'] ) ) {
			return 'no-group';
		}

		if ( ! current_user_can( 'manage_options' ) ) {
			return 'no-permission';
		}

		$group = absint( $_REQUEST['rcpga-group'] );

		rcpga_group_accounts()->members->remove_all_from_group( $group );
		rcpga_group_accounts()->groups->delete( $group );

		return 'group-deleted';

	}

	/**
	 * Handle member add submission
	 *
	 * @return int|string
	 */
	protected function add_member_to_group() {

		// make sure that group is specified and exists
		if ( empty( $_REQUEST['rcpga-group'] ) || ! $group = rcpga_group_accounts()->groups->get( absint( $_REQUEST['rcpga-group'] ) ) ) {
			return 'no-group';
		}

		// Check permissions
		if ( ! rcpga_group_accounts()->capabilities->can( 'manage_members', get_current_user_id(), $group->group_id ) ) {
			return 'no-permission';
		}

		// make sure the email is valid
		if ( empty( $_REQUEST['rcpga-user-email'] ) || ! is_email( $_REQUEST['rcpga-user-email'] ) ) {
			return 'empty-email';
		}

		if ( 1 > ( $group->seats - $group->member_count ) ) {
			return 'seats-maxed';
		}

		global $rcp_options;

		// do we send an invite email?
		$args = array(
			'user_email'  => sanitize_text_field( $_REQUEST['rcpga-user-email'] ),
			'first_name'  => isset( $_REQUEST['rcpga-first-name'] ) ? sanitize_text_field( $_REQUEST['rcpga-first-name'] ) : '',
			'last_name'   => isset( $_REQUEST['rcpga-last-name'] ) ? sanitize_text_field( $_REQUEST['rcpga-last-name'] ) : '',
			'user_pass'   => wp_generate_password(),
			'send_invite' => empty( $_REQUEST['rcpga-disable-invite-email'] ) && empty( $rcp_options['disable_group_invite_email'] ),
			'group_id'    => absint( $_REQUEST['rcpga-group'] ),
		);

		$user_id = rcpga_add_member_to_group( $args );

		if ( is_wp_error( $user_id ) ) {
			return $user_id->get_error_code();
		}

		return 'group-member-added';

	}

	/**
	 * Handle CSV import
	 * @return string
	 */
	protected function import_members_to_group() {

		if ( empty( $_REQUEST['rcpga-group'] ) ) {
			return 'no-group';
		}

		$group_id = absint( $_REQUEST['rcpga-group'] );

		if ( ! rcpga_group_accounts()->capabilities->can( 'manage_members', get_current_user_id(), $group_id ) ) {
			return 'no-permission';
		}

		if ( empty( $_FILES['rcpga-group-csv']['tmp_name'] ) ) {
			return 'no-csv';
		}

		if ( ! class_exists( 'parseCSV' ) ) {
			require_once dirname( __FILE__ ) . '/parsecsv.lib.php';
		}

		$import_file = ! empty( $_FILES['rcpga-group-csv'] ) ? $_FILES['rcpga-group-csv']['tmp_name'] : false;

		if ( ! $import_file ) {
			return 'default-error';
		}

		$csv         = new parseCSV( $import_file );
		$members     = $csv->data;
		$seats_count = rcpga_group_accounts()->groups->get_seats_count( $group_id );
		$mem_count   = rcpga_group_accounts()->groups->get_member_count( $group_id );
		$row_count   = count( $members );
		$seats_left  = $seats_count - $mem_count;

		if ( $row_count > $seats_left ) {
			return 'seats-maxed';
		}

		if ( ! $members ) {
			return 'default-error';
		}

		$args = $errors = array();

		global $rcp_options;

		// do we send an invite email?
		$args['send_invite'] = empty( $_REQUEST['rcpga-disable-invite-email'] ) && empty( $rcp_options['disable_group_invite_email'] );
		$args['group_id']    = $group_id;

		foreach ( $members as $member ) {

			$args['user_email'] = isset( $member['email'] ) ? sanitize_text_field( $member['email'] ) : '';
			$args['first_name'] = isset( $member['first_name'] ) ? sanitize_text_field( $member['first_name'] ) : '';
			$args['last_name']  = isset( $member['last_name'] ) ? sanitize_text_field( $member['last_name'] ) : '';
			$args['user_pass']  = ! empty( $member['password'] ) ? sanitize_text_field( $member['password'] ) : wp_generate_password();

			$user_id = rcpga_add_member_to_group( $args );

			if ( is_wp_error( $user_id ) ) {
				rcp_errors()->add( $user_id->get_error_code(), $user_id->get_error_message() );
			}

		}

		do_action( 'rcpga_import_members_to_group' );

		if ( count( rcp_errors()->get_error_codes() ) ) {
			return 'group-members-imported-errors';
		}

		return 'group-members-imported';

	}

	/**
	 * Remove a member from a group
	 * @return string - result
	 */
	protected function remove_member_from_group() {

		if ( empty( $_REQUEST['rcpga-group'] ) ) {
			return 'no-group';
		}

		if ( empty( $_REQUEST['rcpga-member'] ) ) {
			return 'no-member';
		}

		if ( ! rcpga_group_accounts()->capabilities->can( 'manage_members', get_current_user_id(), absint( $_REQUEST['rcpga-group'] ) ) ) {
			return 'no-permission';
		}

		$member_id = absint( $_REQUEST['rcpga-member'] );

		rcpga_group_accounts()->members->remove( $member_id );

		return 'group-member-removed';

	}


	/**
	 * Updates a member's role to either member or admin
	 *
	 * @return string - result
	 */
	public function make_member_admin() {

		if ( empty( $_REQUEST['rcpga-action'] ) || empty( $_REQUEST['rcpga-member'] ) ) {
			return 'no-member';
		}

		if ( ! rcpga_group_accounts()->capabilities->can( 'manage_members', get_current_user_id(), absint( $_REQUEST['rcpga-group'] ) ) ) {
			return 'no-permission';
		}

		$member_id = absint( $_REQUEST['rcpga-member'] );

		if ( 'make-admin' == $_REQUEST['rcpga-action'] ) {
			rcpga_group_accounts()->members->update( $member_id, array( 'role' => 'admin' ) );
		} else {
			rcpga_group_accounts()->members->update( $member_id, array( 'role' => 'member' ) );
		}

		if ( ! empty( $_REQUEST['rcpga-update-role'] ) ) {
			rcpga_maybe_update_member_role( $member_id );
		}

		return 'group-member-updated';

	}

	/**
	 * Handle invite resend invite
	 *
	 * @return string
	 */
	public function resend_member_invite() {

		if ( empty( $_REQUEST['rcpga-group'] ) ) {
			return 'no-group';
		}

		if ( empty( $_REQUEST['rcpga-member'] ) ) {
			return 'no-member';
		}

		if ( ! rcpga_group_accounts()->capabilities->can( 'manage_members', get_current_user_id(), absint( $_REQUEST['rcpga-group'] ) ) ) {
			return 'no-permission';
		}

		if ( ! rcpga_send_group_invite( absint( $_REQUEST['rcpga-member'] ) ) ) {
			return 'default-error';
		}

		return 'invite-sent';
	}

	/**
	 * Create the owner member for the group when it is created
	 *
	 * @param $group_id
	 * @param $data
	 */
	public function create_owner_member( $group_id, $data ) {
		rcpga_group_accounts()->members->add( array(
			'user_id'  => absint( $data['owner_id'] ),
			'group_id' => absint( $group_id ),
			'role'     => 'owner'
		) );
	}

	/**
	 * Remove all members from a group once it is deleted
	 *
	 * @param $group_id
	 */
	public function remove_members( $group_id ) {
		rcpga_group_accounts()->members->remove_all_from_group( $group_id );
	}

	/**
	 * Handle member invite
	 */
	public function accept_invite() {
		global $rcp_options;

		if ( empty( $_GET['rcpga-invite-key'] ) || empty( $_GET['rcpga-user'] ) ) {
			return;
		}

		if ( ! $user = get_user_by( 'email', rawurldecode( $_GET['rcpga-user'] ) ) ) {
			return;
		}

		if ( rawurldecode( $_GET['rcpga-invite-key'] ) != $user->user_pass ) {
			return;
		}

		if ( 'invited' != rcpga_group_accounts()->members->get_role( $user->ID ) ) {
			return;
		}

		rcpga_maybe_update_member_role( $user->ID );

		// update the rcp group role
		rcpga_group_accounts()->members->update( $user->ID, array( 'role' => 'member' ) );

		do_action( 'rcpga_member_invite_accepted', $user );

		wp_set_auth_cookie( $user->ID, false );
		wp_set_current_user( $user->ID, $user->user_login );
		do_action( 'wp_login', $user->user_login, $user );

		global $rcp_options;

		$edit_profile_page = $rcp_options['edit_profile'];
		if ( ! $redirect = add_query_arg( array( 'rcpga-message' => 'invite-accepted' ), get_post_permalink( $edit_profile_page ) ) ) {
			return;
		}

		wp_safe_redirect( $redirect );
		die();

	}

	/**
	 * Remove deleted user from group
	 *
	 * @param $user_id
	 */
	public function delete_from_group( $user_id ) {

		// if this is a group owner, delete the whole group, otherwise just remove
		// the user from the group, if applicable.
		if ( rcpga_group_accounts()->members->is_group_owner( $user_id ) ) {
			rcpga_group_accounts()->groups->delete( rcpga_group_accounts()->members->get_group_id( $user_id ) );
		} else {
			rcpga_group_accounts()->members->remove( $user_id );
		}

	}

	/**
	 * Make sure that this member doesn't have more seats than the
	 * selected subscription allows
	 *
	 * @param $data
	 */
	public function can_user_downgrade( $data ) {
		if ( empty( $data['rcp_level'] ) ) {
			return;
		}

		if ( ! rcpga_group_accounts()->members->is_group_owner() ) {
			return;
		}

		$group_id = rcpga_group_accounts()->members->get_group_id();

		$member_count = rcpga_group_accounts()->groups->get_member_count( $group_id );
		$member_count -= 1; // don't count the group_owner

		if ( rcpga_get_level_group_seats_allowed( absint( $data['rcp_level'] ) ) < $member_count ) {
			rcp_errors()->add( 'remove_children', __( 'You have too many members in your group to change to this level.', 'rcp-group-accounts' ), 'register' );
		}

	}

	/**
	 * Update a group's seat count when a member's subscription becomes active
	 *
	 * @param $post
	 * @param $user_id
	 * @param $price
	 */
	public function update_seat_count( $post, $user_id, $price ) {

		// make sure we have a subscription
		if ( ! $subscription_id = rcp_get_registration()->get_subscription() ) {
			return;
		}

		// only applies to group owners
		if ( ! rcpga_group_accounts()->members->is_group_owner( $user_id ) ) {
			return;
		}

		// make sure we already have a group, otherwise, this doesn't matter
		if ( ! $group_id = rcpga_group_accounts()->members->get_group_id( $user_id ) ) {
			return;
		}

		$seats = rcpga_get_level_group_seats_allowed( $subscription_id );
		rcpga_group_accounts()->groups->update( $group_id, array( 'seats' => $seats ) );

	}

	/**
	 * Check if group name is required and throw error if it is not provided
	 *
	 * @param $data
	 */
	public function require_group_name( $data ) {

		// make sure we have a subscription
		if ( ! $subscription_id = rcp_get_registration()->get_subscription() ) {
			return;
		}

		// make sure this member is not already a group owner
		if ( rcpga_group_accounts()->members->is_group_owner() ) {
			return;
		}

		// make sure this level supports group accounts
		if ( ! rcpga_is_level_group_accounts_enabled( $subscription_id ) ) {
			return;
		}

		if ( empty( $data['rcpga-group-name'] ) ) {
			rcp_errors()->add( 'group_name_required', __( 'Please enter a group name.', 'rcp-group-accounts' ), 'register' );
		}

	}

	/**
	 * Set pending user meta during registration so we know to create the group later
	 *
	 * @param array $post    Posted data.
	 * @param int   $user_id ID of the user who's registering.
	 * @param float $price   Price of the subscription.
	 *
	 * @access public
	 * @since 1.1.2
	 * @return void
	 */
	public function create_pending_group( $post, $user_id, $price ) {

		// make sure we have a subscription
		if ( ! $subscription_id = rcp_get_registration()->get_subscription() ) {
			return;
		}

		// make sure this member is not already a group owner
		if ( rcpga_group_accounts()->members->is_group_owner( $user_id ) ) {
			return;
		}

		// make sure this level supports group accounts
		if ( ! $seats = rcpga_get_level_group_seats_allowed( $subscription_id ) ) {
			return;
		}

		// finally, make sure we have a group name
		if ( empty( $post['rcpga-group-name'] ) ) {
			return;
		}

		$args = array(
			'owner_id'    => absint( $user_id ),
			'seats'       => absint( $seats ),
			'name'        => wp_unslash( sanitize_text_field( $post['rcpga-group-name'] ) ),
			'address1'        => wp_unslash( sanitize_text_field( $post['rcpga-group-address1'] ) ),
			'address2'        => wp_unslash( sanitize_text_field( $post['rcpga-group-address2'] ) ),
			'city'        => wp_unslash( sanitize_text_field( $post['rcpga-group-city'] ) ),
			'state'        => wp_unslash( sanitize_text_field( $post['rcpga-group-state'] ) ),
			'zip'        => wp_unslash( sanitize_text_field( $post['rcpga-group-zip'] ) ),
			'description' => ! empty( $post['rcpga-group-description'] ) ? wp_unslash( wp_filter_post_kses( $post['rcpga-group-description'] ) ) : '',
		);

		update_user_meta( absint( $user_id ), 'rcpga_pending_group', $args );

	}

	/**
	 * Create a group for this member when their account is activated
	 *
	 * @param string     $status     New status being set.
	 * @param int        $user_id    ID of the user.
	 * @param string     $old_status Previous status.
	 * @param RCP_Member $member     Member object.
	 *
	 * @access public
	 * @return void
	 */
	public function register_member_group( $status, $user_id, $old_status, $member ) {

		if ( ! in_array( $status, array( 'active', 'free' ) ) ) {
			return;
		}

		$args = get_user_meta( $user_id, 'rcpga_pending_group', true );

		if ( empty( $args ) || ! is_array( $args ) ) {
			return;
		}

		// Make sure the pending meta has all the required arguments.
		$required = array( 'owner_id', 'seats', 'name', 'description', 'address1', 'city','state','zip' );
		if ( count( array_intersect_key( array_flip( $required ), $args ) ) !== count( $required ) ) {
			return;
		}

		rcpga_group_accounts()->groups->add( $args );

		delete_user_meta( $user_id, 'rcpga_pending_group' );
	}

	/**
	 * When a user becomes active, make sure they are not still tied to a group
	 *
	 * @param $status
	 * @param $user_id
	 * @param $old_status
	 */
	public function remove_active_member( $status, $user_id, $old_status ) {

		// make sure this is a new status and the status is active
		if ( 'active' !== $status || $status == $old_status ) {
			return;
		}

		// does not apply to group owners
		if ( rcpga_group_accounts()->members->is_group_owner( $user_id ) ) {
			return;
		}

		// make sure the user has a group, otherwise, this doesn't matter
		if ( ! $group_id = rcpga_group_accounts()->members->get_group_id( $user_id ) ) {
			return;
		}

		rcpga_group_accounts()->members->remove( $user_id );

	}

	/**
	 * Enqueue frontend scripts
	 */
	public function scripts() {
		wp_register_script( 'rcp-group-accounts', RCPGA_GROUPS_PLUGIN_URL . 'assets/js/group-accounts.js', array( 'jquery' ), RCPGA_GROUPS_VERSION, true );
		wp_localize_script( 'rcp-group-accounts', 'rcpgaLevelMap', rcpga_get_group_enabled_levels() );
	}

	/**
	 * Output registration fields for Group Accounts
	 */
	public function registration_fields() {
		rcp_get_template_part( 'group', 'register' );
		wp_enqueue_script( 'rcp-group-accounts' );
	}

	/*
	 * Handle cleanup when a member is removed from a group.
	 *
	 * @param $user_id
	 *
	 * @return mixed
	 */
	public function group_member_cleanup( $user_id ) {

		$user = new WP_User( $user_id );

		// get the group id for this user
		$group_id = rcpga_group_accounts()->members->get_group_id( $user_id );

		// if this group does not have the default site role, make sure to reset
		// the role for this user before removing
		$default_role = get_option( 'default_role', 'subscriber' );
		$group_role   = rcpga_group_accounts()->groups->get_group_role( $group_id );

		if ( $default_role !== $group_role ) {
			$user->remove_role( $group_role );
			$user->add_role( $default_role );
		}

		return $user_id;

	}

	/**
	 * Prevents non-owner group members from changing their subscription levels.
	 *
	 * @param bool $ret     Whether the member can upgrade their subscription.
	 * @param int  $user_id The user ID
	 *
	 * @return bool True if the member is a group owner, false if the member is not a group owner.
	 */
	public function can_upgrade_subscription( $ret, $user_id ) {

		if ( rcpga_group_accounts()->members->get_group_id( $user_id ) && ! rcpga_group_accounts()->members->is_group_owner( $user_id ) ) {
			$ret = false;
		}

		return $ret;

	}

	/**
	 * Add group information to Stripe charge meta data
	 *
	 * @param array                      $args
	 * @param RCP_Payment_Gateway_Stripe $stripe_gateway
	 *
	 * @access public
	 * @since  1.1
	 * @return array
	 */
	public function stripe_charge_create_args( $args, $stripe_gateway ) {

		if ( ! $group_id = rcpga_group_accounts()->members->get_group_id( $args['metadata']['user_id'] ) ) {
			return $args;
		}

		$group_name = rcpga_group_accounts()->groups->get_name( $group_id );

		if ( ! empty( $group_name ) ) {
			$args['metadata']['rcp_group_name'] = $group_name;
		}

		return $args;

	}

}