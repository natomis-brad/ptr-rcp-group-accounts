<?php

class RCPGA_Groups_Notices {

	/**
	 * @var
	 */
	var $message;

	/**
	 * @var
	 */
	var $type;

	function __construct() {
		add_action( 'init', array( $this, 'add_notices' ) );
	}


	public function add_notices() {

		if( ! isset( $_GET['rcpga-message'] ) ) {
			return;
		}

		$message = '';
		$type    = 'success';
		$notice  = $_GET['rcpga-message'];

		switch( $notice ) {

			case 'group-member-added' :
				$message = __( 'Church member added successfully', 'rcp-group-accounts' );
				break;

			case 'group-member-removed' :
				$message = __( 'Church member removed successfully', 'rcp-group-accounts' );
				break;

			case 'group-member-updated' :
				$message = __( 'Church member updated successfully', 'rcp-group-accounts' );
				break;

			case 'group-members-imported' :
				$message = __( 'Church members imported successfully', 'rcp-group-accounts' );
				break;

			case 'group-members-imported-errors' :
				$message = __( 'Church members imported with errors', 'rcp-group-accounts' );
				$type    = 'error';
				break;

			case 'role-updated' :
				$message = __( 'Member\'s role successfully updated', 'rcp-group-accounts' );
				break;

			case 'password-updated' :
				$message = __( 'Member\'s password successfully updated', 'rcp-group-accounts' );
				break;

			case 'group-added' :
				$message = __( 'Church added successfully', 'rcp-group-accounts' );
				break;

			case 'group-updated' :
				$message = __( 'Church updated successfully', 'rcp-group-accounts' );
				break;

			case 'group-deleted' :
				$message = __( 'Church deleted successfully', 'rcp-group-accounts' );
				break;

			case 'invite-sent' :
				$message = __( 'Invite sent successfully', 'rcp-group-accounts' );
				break;

			case 'invite-accepted' :
				$message = __( 'Church membership confirmed. Please update your password now.', 'rcp-group-accounts' );
				break;

			case 'no-user' :
				$message = __( 'That email does not appear to exist in our system', 'rcp-group-accounts' );
				$type    = 'error';
				break;

			case 'empty-email' :
				$message = __( 'Please enter a valid email address', 'rcp-group-accounts' );
				$type    = 'error';
				break;

			case 'empty-group-name' :
				$message = __( 'Please enter a church name', 'rcp-group-accounts' );
				$type    = 'error';
				break;
			case 'empty-group-address1' :
				$message = __( 'Please enter a church address', 'rcp-group-accounts' );
				$type    = 'error';
				break;
			case 'empty-group-city' :
				$message = __( 'Please enter a church city', 'rcp-group-accounts' );
				$type    = 'error';
				break;
			case 'empty-group-state' :
				$message = __( 'Please enter a church state', 'rcp-group-accounts' );
				$type    = 'error';
				break;
			case 'empty-group-zip' :
				$message = __( 'Please enter a church zip code', 'rcp-group-accounts' );
				$type    = 'error';
				break;
			case 'no-permission' :
				$message = __( 'You do not have permission to perform that action', 'rcp-group-accounts' );
				$type    = 'error';
				break;

			case 'no-group' :
				$message = __( 'Oops, no group ID was specified. How did that happen? We do not know', 'rcp-group-accounts' );
				$type    = 'error';
				break;

			case 'no-member' :
				$message = __( 'Oops, no member was specified. How did that happen? We do not know', 'rcp-group-accounts' );
				$type    = 'error';
				break;

			case 'has-group' :
				$message = __( 'This user is already has a group. Users may only be part of one church at a time.', 'rcp-group-accounts' );
				$type    = 'error';
				break;

			case 'seats-maxed' :
				$message = __( 'There are not enough seats left in this church to handle this request.', 'rcp-group-accounts' );
				$type    = 'error';
				break;

			case 'active-user' :
				$message = __( 'The member you are trying to add already has an active subscription. Please contact the member directly and have them cancel their existing subscription so you can add them to the church.', 'rcp-group-accounts' );
				$type    = 'error';
				break;

			case 'no-csv' :
				$message = __( 'Please upload a CSV file', 'rcp-group-accounts' );
				$type    = 'error';
				break;

			case 'default-error' :
				$message = __( 'Something went wrong, please try again.', 'rcp-group-accounts' );
				$type    = 'error';
				break;
		}

		$message = apply_filters( 'rcpga_notice_message', $message, $notice, $type );
		$type    = apply_filters( 'rcpga_notice_type', $type, $notice, $message );

		if ( empty( $message ) ) {
			return;
		}

		$this->message = $message;
		$this->type = $type;

		add_action( 'admin_notices', array( $this, 'print_message' ) );
		add_action( 'rcpga_dashboard_notifications', array( $this, 'print_message_front' ) );
		add_action( 'rcp_profile_editor_before', array( $this, 'print_message_front' ) );

	}

	/**
	 * Notice for the admin area
	 */
	public function print_message() {
		$class = ( 'success' == $this->type ) ? 'updated' : 'error';
		printf( '<div class="%s"><p>%s</p></div>', esc_attr( $class ), esc_html( $this->message ) );
	}

	/**
	 * Notice for the dashboard
	 */
	public function print_message_front() {
		$class = ( 'success' == $this->type ) ? 'rcp_success' : 'rcp_error';
		printf( '<p class="%s"><span>%s</span></p>', $class, esc_html( $this->message ) );
	}

}